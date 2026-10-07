<?php

namespace Tests\Support;

use PHPUnit\Framework\TestCase;

use Tests\Support\App\TestApplication;

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
 * risky. Use restore_error_handler(), como em ignoringCliHeaderWarnings(), e
 * ver a nota em AGENTS.md.
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

        $this->resetUriSegments();

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
            // Limpa os cabeçalhos acumulados, para o caso seguinte não ler um
            // Location de um logout anterior. A propriedade é pública no CI_Output e
            // não tem método que a zere, porque em requisição web cada resposta
            // nasce de um Output novo; aqui o mesmo objeto serve a todos os casos.
            //
            // Apendar outro Location não resolveria: get_header() varre de trás
            // para frente e devolve a última ocorrência, então quem só escreve o
            // header que espera continuaria lendo o valor certo por acidente — e
            // um caso que afirma a AUSÊNCIA de um header leria o antigo.
            $ci->output->headers = [];
        }
    }

    /**
     * Deixa a sessão no estado de quem já entrou, com um papel escolhido.
     *
     * `CI_Session::userdata()` lê `$_SESSION[$key]` direto (Session.php:798), e
     * não uma cópia interna, então escrever o superglobal é o bastante — é o
     * mesmo caminho que o Login usa e que os testes de logout já observam.
     *
     * O `permissao` é o `idPermissao` que o guard repete em
     * `checkPermission()`. O padrão é 1, que é o papel administrativo do banco
     * de teste; para exercitar a NEGAÇÃO, passe um id que não exista, e não um
     * ao qual falte uma atividade — assim o caso não depende de quais atividades
     * o seed deixou ligadas. O cache do `Permission` é zerado em
     * `TestApplication::resetSharedState()` justamente para que o id escolhido
     * aqui seja o id avaliado.
     */
    protected function loginAs(int $permissionId = 1): void
    {
        $_SESSION['logado'] = true;
        $_SESSION['permissao'] = $permissionId;
    }

    /**
     * Aponta a URI para um caminho, para o controller ler segmentos e
     * parâmetros nomeados como leria numa requisição web.
     *
     * O `CI_URI` é singleton e foi construído uma vez, no boot do
     * bootstrap.php, com `$_SERVER['argv'] = ['index.php']` — ou seja, sem
     * segmento nenhum. Escrever `$_SERVER['REQUEST_URI']` aqui não resolveria:
     * o objeto já está pronto e ninguém o repassa pela URI.
     *
     * `segments` e `keyval` são públicos no CI3, então não precisa de
     * reflexão. O `keyval` é limpo junto porque é um cache por índice, preenchido
     * por `uri_to_assoc($n)` na primeira chamada e reutilizado depois: sem o
     * reset, o segundo caso que lê `uri_to_assoc(3)` receberia o mapa do
     * caminho do caso anterior.
     *
     * Só `segments` e `keyval`: nenhum controller da suíte usa `ruri_*()`, que
     * vive em `rsegments` e que nada aqui escreve.
     */
    protected function resetUriSegments(string $path = ''): void
    {
        $uri = $this->ci()->uri;

        $uri->uri_string = trim($path, '/');
        $uri->segments = [];
        $uri->keyval = [];

        foreach (explode('/', trim($path, '/')) as $index => $segment) {
            if ($segment !== '') {
                $uri->segments[$index + 1] = $segment;
            }
        }
    }

    /**
     * Monta um POST de login com um token CSRF válido.
     *
     * A verificação do CI3 (Security::csrf_verify) compara o valor do POST com o
     * do cookie por hash_equals, sem armazenamento no servidor. Como setcookie()
     * é no-op em CLI, o cookie é escrito à mão.
     */
    protected function postLogin(string $email, string $password): void
    {
        $this->postWithCsrfToken(['email' => $email, 'senha' => $password]);
    }

    /**
     * Monta um POST qualquer com um token CSRF válido.
     *
     * O `postLogin()` é a porta da tela de login e não serve para os outros
     * endpoints: ele preenche `email` e `senha`, e um `Mine::senhaSalvar()` que
     * recebesse esse corpo aceitaria uma senha vazia de novo. Aqui os campos
     * vêm do argumento, e `null` no lugar de um campo significa que ele não é
     * enviado — que é como se distingue "campo ausente" de "campo vazio".
     *
     * Chamar isto duas vezes no mesmo caso renova o par cookie/POST, porque
     * `csrf_regenerate` está ligado e um token já usado não passa na segunda
     * tentativa.
     *
     * @param array<string, string|null> $fields
     */
    protected function postWithCsrfToken(array $fields): void
    {
        $hash = (string) $this->ci()->security->get_csrf_hash();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_COOKIE[config_item('csrf_cookie_name')] = $hash;
        $_POST = [config_item('csrf_token_name') => $hash];

        foreach ($fields as $field => $value) {
            if ($value !== null) {
                $_POST[$field] = $value;
            }
        }
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
     * Os quatro argumentos do handler são repassados inteiros porque o
     * _error_handler() do CI3 exige os quatro; encaminhar só o nível e a
     * mensagem lançava ArgumentCountError no meio de um teste.
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
            static function (int $level, string $message, string $file = '', int $line = 0) use (&$previous): bool {
                if ($level === E_WARNING && str_contains($message, 'Cannot modify header information')) {
                    return true;
                }

                return $previous !== null ? (bool) $previous($level, $message, $file, $line) : false;
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
     * Constrói um controller e devolve a instância, sem chamar método nenhum.
     *
     * Porta separada de callControllerRaw() porque nem todo teste chega a um
     * método: o que exercita um GUARD de construtor precisa só do construtor,
     * já que o guard lança antes de qualquer método rodar. Sem esta porta, esse
     * teste teria de escolher um método qualquer do controller só para ter o que
     * chamar — e um método escolhido à toa é um método que grava no banco e faz
     * o caso depender de dado que nada tem a ver com o guard.
     *
     * A ordem dentro do método importa: resetSharedState() ANTES do `new`, porque
     * é ele que resolve o cache de permissões e o registro de classes, e o guard
     * lê os dois no primeiro statement do construtor.
     */
    protected function constructController(string $class): object
    {
        $ci = $this->ci();

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
            $left = $controller->db;
            $controller->db = $ci->db;
            $left->close();
        }

        return $controller;
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

        $controller = $this->constructController($class);

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
