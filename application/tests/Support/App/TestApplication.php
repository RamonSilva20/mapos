<?php

namespace Tests\Support\App;

use Tests\Support\Database\TestDatabase;

/**
 * A metade da suíte que é sobre o CodeIgniter, separada da que é sobre o banco.
 *
 * TestDatabase resolve credenciais, guarda o nome '_test' e fala PDO. Isso é
 * uma coisa só, e ela fica naquele nome. O require() do index.php, o conserto do
 * estado reentrante e a corrida das migrações são outra coisa, com um ciclo de
 * vida diferente, e viviam na mesma classe — de modo que quem procurava a
 * configuração do banco encontrava um require() do index.php no meio, e vice
 *versa.
 *
 * A ordem entre as duas importa e é a mesma nos dois scripts: o banco precisa
 * existir antes do boot, porque o autoloader de 'database' conecta durante o
 * boot do index.php. Por isso TestApplication depende de TestDatabase e não o
 * contrário.
 */
final class TestApplication
{
    /**
     * Classes que o Codeigniter.php carrega pelo load_class() e que o
     * CI_Controller reencontra já instanciadas. Ver resetSharedState().
     */
    private const CI_CORE_CLASSES = [
        'benchmark' => true,
        'hooks' => true,
        'config' => true,
        'log' => true,
        'utf8' => true,
        'uri' => true,
        'router' => true,
        'output' => true,
        'security' => true,
        'input' => true,
        'lang' => true,
        'loader' => true,
    ];

    /**
     * O objeto super do CI3, montado uma vez por boot().
     */
    private static ?object $super = null;

    /**
     * O objeto super do CI3: o que Codeigniter.php constrói no boot.
     *
     * Enquanto um controller está em construção, get_instance() devolve o
     * controller, não este objeto. O harness chama isto para poder falar com o
     * super de forma estável, inclusive entre dois controllers seguidos.
     */
    public static function superObject(): object
    {
        if (self::$super === null) {
            throw new \LogicException('TestApplication::boot() precisa rodar antes de superObject().');
        }

        return self::$super;
    }

    /**
     * Sobe o app do CI3 no ambiente de testes. Só pode ser chamado uma vez por
     * processo.
     *
     * O CI3 não foi feito para ser reentrante: incluir o index.php executa o
     * ciclo completo da requisição. Por isso o app é inicializado uma única vez e
     * quem vier depois instancia os controllers diretamente sobre a instância
     * viva.
     *
     * @param string|null $database Banco a apontar antes do boot. Null usa o
     *                             que o bootstrap já definiu. check-schema-parity
     *                             precisa do seu, porque ele constrói um segundo
     *                             banco — o de migrações — e compara com o
     *                             que veio do banco.sql. Passar o nome por
     *                             parâmetro deixa a dependência visível; o
     *                             script escrevendo em $_ENV por conta própria
     *                             dependia de a ordem das linhas ficar certa.
     */
    public static function boot(?string $database = null): void
    {
        if ($database !== null) {
            $_ENV['DB_DATABASE'] = $database;
        }

        $sessionPath = sys_get_temp_dir() . '/mapos-test-sessions';

        if (! is_dir($sessionPath)) {
            mkdir($sessionPath, 0700, true);
        }

        ini_set('session.save_path', $sessionPath);
        ini_set('session.use_cookies', '0');
        ini_set('session.cache_limiter', '');

        // Nada pode imprimir antes disto: qualquer saída marca os headers como
        // enviados e o session_start() passa a reclamar.
        session_start();

        // Sob CLI o Codeigniter tira a rota de $_SERVER['argv'], e não do
        // REQUEST_URI (ver URI::_set_uri_string). Sem isto, opções do próprio
        // PHPUnit viram rota, caem em 404 e o exit() do show_404() mata o
        // processo.
        $_SERVER['argv'] = ['index.php'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['HTTP_HOST'] ??= 'localhost';
        $_SERVER['SCRIPT_NAME'] ??= '/index.php';

        // O boot despacha o controller inerte e a saída é enviada em
        // Codeigniter.php::_display(). Descartar para não poluir a saída.
        ob_start();
        require TestDatabase::rootPath() . '/index.php';
        ob_end_clean();

        // O objeto super do CI3, o que Codeigniter.php constrói no boot. A partir
        // de agora get_instance() passa a devolver controllers, porque
        // CI_Controller::__construct() faz self::$instance =& $this, e é por
        // isso que o harness precisa guardar esta referência: é ela que continua
        // sendo a fonte de $ci->db, $ci->output e $ci->load para o resto do
        // processo.
        self::$super = get_instance();

        // Estado limpo para quem vier a seguir; cada caso recomeça daqui.
        $_SESSION = [];
    }

    /**
     * Mantém o registro de classes do CI3 consistente antes de construir outro
     * controller.
     *
     * O CI3 não é reentrante, e isto só aparece quando o processo constrói mais de
     * um controller. No primeiro, CI_Controller::__construct percorre is_loaded()
     * ANTES de $this->load->initialize() rodar o autoloader, então o registro ainda
     * está vazio e nada quebra. Ao construir um segundo, o registro já está cheio, e
     * o foreach chama load_class() com o nome cru — e load_class() só procura um
     * libraries/<nome-minusculo>.php em APPPATH e BASEPATH, o que estoura para
     * qualquer classe que o Loader tenha instanciado por outro caminho: o core
     * `Session`, o `Permission` da aplicação (sem o prefixo CI_) e o
     * `Form_validation` (que via load_class() chega com $this->CI nulo).
     *
     * As classes core não têm esse problema: o Codeigniter.php as carrega pelo
     * load_class(), que as memoriza no cache estático da função.
     *
     * O mesmo vale para models, por um motivo diferente. Loader::model()
     * (Loader.php:268) retorna cedo em in_array($name, $this->_ci_models, TRUE),
     * ANTES de $CI->$name = $model — ou seja, sem anexar nada. Como o Loader é
     * singleton e a lista sobrevive entre os casos, do segundo controller em diante
     * todo model já consta como "carregado" e $this->Mapos_model fica null.
     *
     * As escritas que isso exige estão em Ci3Introspection, e AGENTS.md traz a
     * lista das classes afetadas.
     */
    public static function resetSharedState(): void
    {
        $loaded = &is_loaded();

        foreach (array_keys($loaded) as $name) {
            if (! isset(self::CI_CORE_CLASSES[$name])) {
                unset($loaded[$name]);
            }
        }

        Ci3Introspection::clearModelRegistry(self::superObject()->load);
        Ci3Introspection::restoreControllerInstance(self::superObject());
    }

    /**
     * Roda a cadeia de migrações e devolve o erro, ou string vazia se deu certo.
     *
     * O Tools::migrate() era chamado em dois scripts com a mesma sequência ao
     * redor — reset do estado reentrante, ob_start, migrate, ob_end, leitura do
     * error_string(). Essa sequência é o que permite rodar Tools no mesmo
     * processo em que o app já foi bootado, então ela não é um detalhe de cada
     * script: ficar aqui é o que impede as duas cópias de divergirem de novo.
     *
     * Exige boot() antes, porque instancia Tools, que é um CI_Controller.
     */
    public static function migrate(): string
    {
        require_once APPPATH . 'controllers/Tools.php';

        self::resetSharedState();

        $tools = new \Tools();

        // O Migrator imprime o relatório do que rodou; é saída, não resultado.
        //
        // O `finally` não é preciosismo: `migrate()` é justamente a chamada que
        // estoura quando a cadeia de migrações está quebrada, que é a falha
        // que este método existe para diagnosticar. Sem o `finally`, a exceção
        // deixaria o buffer aberto e todo o PHPUnit passaria a escrever ali
        // dentro — o relatório de erro sumiria junto, e uma migration
        // quebrada viraria um travamento silencioso em vez de um erro legível.
        ob_start();

        try {
            $tools->migrate();
        } finally {
            ob_end_clean();
        }

        return get_instance()->migration->error_string();
    }
}
