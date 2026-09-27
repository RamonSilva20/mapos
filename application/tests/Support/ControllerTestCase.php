<?php

namespace Tests\Support;

use PHPUnit\Framework\TestCase;

/**
 * Base dos testes que exercitam controllers.
 *
 * O app é inicializado uma única vez, em tests/bootstrap.php, e o PHPUnit
 * mantém esse estado entre os casos. Por isso cada teste precisa restaurar os
 * superglobais e a saída do CI, que são compartilhados.
 *
 * Um detalhe importante para quem estende esta base: não registre error handler
 * com set_error_handler($anterior) para restaurar. Isso empilha no stack em vez
 * de desempilhar, e o PHPUnit acusa o vazamento marcando todos os testes como
 * risky. Use restore_error_handler(), como em ignoringCliHeaderWarnings().
 */
abstract class ControllerTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetApplicationState();
    }

    protected function tearDown(): void
    {
        $this->resetApplicationState();

        parent::tearDown();
    }

    protected function ci(): object
    {
        return TestApplication::superObject();
    }

    protected function resetApplicationState(): void
    {
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
        $_COOKIE = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // Um teste que exercite um logout destrói a sessão nativa, e o bootstrap
        // só a abre uma vez. Reabrindo aqui, os casos seguintes não herdam a
        // sessão morta e o sess_regenerate() do próximo login não volta a emitir
        // E_WARNING.
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $ci = get_instance();

        if (isset($ci->output)) {
            $ci->output->set_output('');
            // Cabeçalhos de uma resposta anterior (o Location de um logout, por
            // exemplo) não podem vazar para o caso seguinte.
            $ci->output->headers = [];
        }
    }

    /**
     * Monta um POST de login com um token CSRF válido.
     *
     * A verificação do CI3 (Security::csrf_verify) compara o valor do POST com o
     * do cookie por hash_equals, sem armazenamento no servidor. Como setcookie()
     * é no-op em CLI, o cookie é escrito à mão.
     */
    protected function postLogin(string $email, string $senha): void
    {
        $hash = (string) $this->ci()->security->get_csrf_hash();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_COOKIE[config_item('csrf_cookie_name')] = $hash;
        $_POST[config_item('csrf_token_name')] = $hash;
        $_POST['email'] = $email;
        $_POST['senha'] = $senha;
    }

    /**
     * Executa um callback ignorando o aviso de header do setcookie().
     *
     * Em CLI não existe resposta HTTP, então o aviso "Cannot modify header
     * information" não significa nada. Quem dispara é o Security do próprio CI3:
     * o csrf_verify() chama csrf_set_cookie() por dentro, e o setcookie() dele
     * abortaria a suíte, porque o Whoops converte o aviso em exceção.
     *
     * Nenhum código do app precisa mais deste caminho. Os cabeçalhos de CORS do
     * Login::verificarLogin() saem por $this->output->set_header(), que só
     * emite no fim da requisição e por isso não reclama em CLI.
     *
     * O handler é trocado apenas durante o callback, e só o aviso de header é
     * engolido. Qualquer outro erro é repassado ao handler anterior em vez de
     * ser devolvido como `false`: devolver `false` entregaria a decisão ao
     * handler interno do PHP, que não é o Whoops, e um erro de verdade dentro do
     * controller passaria a ser um aviso silencioso no meio de um teste verde.
     * O nível também é conferido, porque a mensagem do aviso do PHP é a mesma
     * em qualquer contexto e o que faz sentido silenciar é o aviso, não o
     * erro.
     *
     * A volta usa restore_error_handler(), e não set_error_handler($previous).
     * set_error_handler empilha no stack, então restaurar assim deixava duas
     * entradas extras e o PHPUnit detectava isso como handler vazado do teste,
     * marcando todos os casos como risky. restore_error_handler() desempilha
     * exatamente o que foi empilhado.
     */
    protected function ignoringCliHeaderWarnings(callable $callback): mixed
    {
        $previous = set_error_handler(
            static function (int $level, string $message) use (&$previous): bool {
                if ($level === E_WARNING && str_contains($message, 'Cannot modify header information')) {
                    return true;
                }

                return $previous !== null ? (bool) $previous($level, $message) : false;
            }
        );

        try {
            return $callback();
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Chama um controller diretamente e devolve o corpo da resposta em JSON.
     *
     * Instanciar o controller, e não despachar a requisição, é o que torna o
     * teste in-process: despachar exigiria reiniciar o ciclo de vida do CI3 a
     * cada caso, e o framework não é reentrante.
     */
    protected function callController(string $class, string $method): array
    {
        $body = $this->callControllerRaw($class, $method);

        $decoded = json_decode($body, true);

        $this->assertIsArray(
            $decoded,
            "A resposta de {$class}::{$method}() não é JSON válido: " . var_export($body, true)
        );

        return $decoded;
    }

    /**
     * Igual a callController(), mas devolve o corpo cru em vez de decodificar.
     *
     * Para o que não responde JSON, como uma view renderizada.
     */
    protected function callControllerRaw(string $class, string $method): string
    {
        $ci = $this->ci();
        $ci->output->set_output('');

        // O CI3 não tem autoloader de controllers: eles são carregados por
        // caminho de arquivo, pelo Loader.
        $path = APPPATH . 'controllers' . DIRECTORY_SEPARATOR . $class . '.php';

        if (! is_file($path)) {
            $this->fail("Controller não encontrado: {$path}");
        }

        require_once $path;

        $this->assertTrue(class_exists($class), "A classe {$class} não foi declarada por {$path}.");

        TestApplication::resetSharedState();

        $controller = new $class();

        // O autoloader roda dentro de CI_Controller::__construct(), uma vez por
        // controller construído, e ele carrega o banco. Loader::database()
        // deveria devolver a conexão já aberta, mas a guarda dele testa
        // isset($CI->db) com $CI = get_instance() — e, no meio do construtor,
        // get_instance() é o controller que está nascendo, que ainda não tem a
        // propriedade $db. A guarda falha, o Loader abre uma conexão nova e a
        // guarda mesmo, que é a que o processo inteiro usa.
        //
        // Numa requisição web isso é inofensivo: um request, um controller, uma
        // conexão. Na suíte não é, porque a conexão é única do processo e é nela
        // que a transação do caso é aberta: trocá-la no meio do teste deixa a
        // transação órfã e faz toda escrita do teste ser commitada na hora.
        // Por isso o controller passa a apontar para a conexão do processo, e a
        // sobra é fechada em vez de vazar.
        if (isset($controller->db) && $controller->db !== $ci->db) {
            $sobra = $controller->db;
            $controller->db = $ci->db;
            $sobra->close();
        }

        ob_start();

        try {
            $this->ignoringCliHeaderWarnings(static function () use ($controller, $method): void {
                $controller->{$method}();
            });
        } finally {
            $printed = ob_get_clean();
        }

        // A view pode escrever direto na saída, enquanto um controller JSON usa
        // $this->output. Nos dois casos o corpo é o que estiver disponível.
        $body = $ci->output->get_output();

        if ($body === '' || $body === null) {
            $body = $printed;
        }

        return (string) $body;
    }
}
