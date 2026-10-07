<?php

namespace Tests\Controllers;

use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Tests\Support\App\ChildEnvironment;
use Tests\Support\Database\TestDatabase;

/**
 * O que o servidor RESPONDE depois da troca de `redirect()` por exceção.
 *
 * {@see \Tests\Controllers\AuthorizationGuardControllerTest} roda os guards
 * in-process e afirma a exceção; nada ali diz o que sai do servidor, e essa é a
 * metade que este arquivo mede. O servidor é um PROCESSO FILHO porque
 * `header()` não existe em CLI: a fronteira sob teste é o `index.php` dos dois
 * lados, do guard ao `Location`, e ela só existe num ciclo de requisição real.
 *
 * Fixa o 401 web, o 401 da API e a página de erro do 404 do Router. O 403 web
 * NÃO é medido aqui: montá-lo por HTTP exigiria um POST de login com token CSRF
 * válido, que a suíte já cobre in-process. O 404 é a única resposta que o CI3
 * renderiza antes de existir um controller, e portanto antes de o `Loader`
 * carregar o helper `general` — por isso as views de erro escapam com
 * `htmlspecialchars()` e não com `esc()`.
 *
 * As chaves de ambiente do filho e os limites in-process estão em AGENTS.md.
 */
final class FrontendBoundaryTest extends TestCase
{
    /**
     * O processo filho: `null` enquanto não subiu, e o motivo da falha em
     * `$serverError` para o skip dizer algo além de "não foi possível".
     */
    private static mixed $process = null;

    private static array $pipes = [];

    private static ?int $port = null;

    private static string $serverError = '';

    /**
     * Sobe o servidor e espera ele aceitar conexão.
     *
     * A espera é por conexão, e não por um sleep fixo: o `php -S` já escuta
     * antes de compilar o `index.php`, então um sleep curto passaria e o
     * primeiro request bateria no meio do boot. Conectar é a única coisa que
     * prova que dá para falar com o processo.
     */
    #[BeforeClass]
    public static function startServer(): void
    {
        TestDatabase::fromEnvironment();

        $port = self::freePort();

        if ($port === null) {
            self::$serverError = 'Nenhuma porta local pôde ser reservada para o servidor de teste.';

            return;
        }

        $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];

        // `variables_order=EGPCS` não é decoração: o `config/database.php` lê as
        // credenciais de `$_ENV`, e o default do PHP numa CLI basta a `E` para
        // não popular `$_ENV` de nada. Sem esta flag o filho sobe com
        // 'enter_db_username' e morre de "Access denied" — que é o mesmo erro
        // que a suíte já teve ao ler `$_ENV` num worker do ParaTest, e pela mesma
        // razão: Dotenv não liga o putenv em toda instalação.
        $process = proc_open(
            [PHP_BINARY, '-d', 'variables_order=EGPCS', '-S', "127.0.0.1:{$port}", 'index.php'],
            $descriptors,
            $pipes,
            dirname(__DIR__, 3),
            self::childEnvironment($port)
        );

        if (! is_resource($process)) {
            self::$serverError = 'proc_open() não devolveu um processo para o servidor de teste.';

            return;
        }

        self::$process = $process;
        self::$pipes = $pipes;
        self::$port = $port;

        for ($attempt = 0; $attempt < 100; $attempt++) {
            if (self::canConnect($port)) {
                return;
            }

            usleep(50_000);
        }

        self::$serverError = self::readServerOutput();
    }

    /**
     * Derruba o servidor, mesmo que a classe tenha falhado no meio.
     *
     * Sem isto, uma falha de asserção deixa o `php -S` vivo segurando a porta, e
     * a próxima execução pega outra porta — o teste passa a custar um processo
     * órfão por falha, e o sintoma disso aparece longe daqui.
     */
    #[AfterClass]
    public static function stopServer(): void
    {
        if (is_resource(self::$process)) {
            proc_terminate(self::$process);
            proc_close(self::$process);
        }

        foreach (self::$pipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }

        self::$process = null;
        self::$pipes = [];
        self::$port = null;
    }

    public function testTheServerIsRunning(): void
    {
        $this->requireServer();

        $this->assertTrue(self::canConnect(self::$port), 'O servidor aceitou a conexão na espera do setUpBeforeClass e agora não aceita.');
    }

    /**
     * Sem sessão, a página web responde 307 para o login.
     *
     * O 307 e não o 302 porque o request é GET e o `redirect_status_for()`
     * preserva o método: é o mesmo número que saía antes da exceção, e é o
     * número que este arquivo existe para provar que continua saindo.
     */
    #[Depends('testTheServerIsRunning')]
    public function testAnUnauthenticatedPageRedirectsToLogin(): void
    {
        $this->requireServer();

        $response = $this->request('/index.php/clientes/gerenciar');

        $this->assertSame(307, $response['status'], 'Uma página sem sessão deixou de responder 307. Corpo: ' . $response['body']);
        $this->assertStringEndsWith(
            '/index.php/login',
            $response['headers']['location'] ?? '',
            'O 401 web precisa mandar para o login, e não para a home: quem não entrou ainda pode entrar.'
        );
    }

    /**
     * A API continua respondendo o 401 JSON dela, sem passar pela exceção nova.
     *
     * `REST_Controller::logged_user()` não foi migrado — ele já respondia 401 e
     * 403 e não chamava `redirect()`. Este caso existe para travar esse "não foi
     * migrado" como fato, e não como intenção: se alguém trocar
     * `REST_Controller` para lançar `AuthenticationRequired`, a mensagem muda de
     * "Faça login para acessar a API." para a padrão, e só esta asserção percebe.
     */
    #[Depends('testTheServerIsRunning')]
    public function testAnUnauthenticatedApiCallKeepsItsJsonResponse(): void
    {
        $this->requireServer();

        $response = $this->request('/index.php/api/v1/clientes');

        $this->assertSame(401, $response['status'], 'A API mudou de status. Corpo: ' . $response['body']);
        $this->assertStringContainsString('application/json', $response['headers']['content-type'] ?? '');
        $this->assertStringNotContainsString(
            'Location',
            implode('|', array_keys($response['headers'])),
            'A API respondeu com redirect. Um Location em um 401 de API vira um loop de login no cliente.'
        );
        $this->assertSame(
            ['status' => false, 'message' => 'Faça login para acessar a API.'],
            json_decode($response['body'], true),
            'O corpo JSON da API mudou.'
        );
    }

    /**
     * A asserção do corpo importa tanto quanto a do status: um 404 com a página
     * certa passa, e um 404 com HTML de erro do PHP também passa, então é o texto
     * "404 Page Not Found" que distingue os dois.
     */
    #[Depends('testTheServerIsRunning')]
    public function testAnUnknownRouteRendersTheErrorPage(): void
    {
        $this->requireServer();

        $response = $this->request('/index.php/rota-que-nao-existe-' . self::$port);

        $this->assertSame(404, $response['status'], 'Uma rota inexistente deixou de responder 404. Corpo: ' . $response['body']);
        $this->assertStringContainsString(
            '404 Page Not Found',
            $response['body'],
            'A resposta não é a página de erro do CI3, o que significa que ela não chegou a ser renderizada.'
        );
        $this->assertStringNotContainsString(
            'undefined function',
            $response['body'],
            'A página de erro morreu dentro dela mesma: um dos escapadores não existia ainda quando a view foi incluída.'
        );
    }

    /**
     * A referência é tirada aqui, e não no `startServer()`, para o caso medir o
     * que as PRÓPRIAS requisições escreveram: o 404 acima grava em ERROR de
     * propósito, e um teste que mede "nada foi escrito" não pode ter uma escrita
     * legítima de outro teste como linha de base.
     *
     * As seis chaves que o filho precisa, e o porquê de cada uma, estão em
     * AGENTS.md, em `FrontendBoundaryTest`.
     */
    #[Depends('testTheServerIsRunning')]
    public function testTheChildLeavesNoLogFileInTheSourceTree(): void
    {
        $this->requireServer();

        $before = self::logFiles();

        $this->request('/index.php/clientes/gerenciar');
        $this->request('/index.php/api/v1/clientes');

        $this->assertSame(
            $before,
            self::logFiles(),
            'O processo filho escreveu em application/logs/. O bootstrap roda como production para ter o roteamento de verdade, e é por isso que esta verificação existe: apague o arquivo, e se o log for legítimo, aponte o log_path do filho para o diretório temporário em vez de relaxar a verificação.'
        );
    }

    private function requireServer(): void
    {
        if (! is_resource(self::$process) || self::$port === null) {
            $this->markTestSkipped('O servidor de teste não subiu: ' . (self::$serverError ?: 'sem saída do processo.'));
        }
    }

    /**
     * Faz um GET e devolve status, headers e corpo, sem seguir redirect.
     *
     * Socket cru em vez de `file_get_contents()` ou cURL porque nenhum dos dois
     * serve aqui: o wrapper HTTP do PHP segue 3xx por conta própria, e seguir o
     * Location mediria a página de login em vez do 307 — que é justamente o que
     * este arquivo mede. O `Connection: close` existe para o servidor fechar a
     * conexão ao terminar a resposta, e `stream_get_contents()` devolver o corpo
     * inteiro em vez de bloquear esperando um EOF que não vem.
     *
     * @return array{status: int, headers: array<string, string>, body: string}
     */
    private function request(string $path): array
    {
        $socket = fsockopen('127.0.0.1', self::$port, $errno, $errstr, 2.0);

        if ($socket === false) {
            $this->fail("Falha ao conectar em 127.0.0.1:{$this->port} para o GET {$path}: {$errstr} ({$errno}).");
        }

        fwrite($socket, "GET {$path} HTTP/1.1\r\nHost: 127.0.0.1:" . self::$port . "\r\nConnection: close\r\n\r\n");

        $raw = stream_get_contents($socket);
        fclose($socket);

        $parts = explode("\r\n\r\n", $raw, 2);
        $head = $parts[0];
        $body = $parts[1] ?? '';

        $lines = explode("\r\n", $head);
        $status = (int) (explode(' ', $lines[0])[1] ?? 0);

        $headers = [];

        foreach (array_slice($lines, 1) as $line) {
            $pair = explode(':', $line, 2);

            if (count($pair) === 2) {
                $headers[strtolower(trim($pair[0]))] = trim($pair[1]);
            }
        }

        return ['status' => $status, 'headers' => $headers, 'body' => $body];
    }

    /**
     * As variáveis que o filho precisa, e nada mais.
     *
     * O `proc_open()` recebe o ambiente inteiro, e não só o que este teste quer:
     * um `$_ENV` com meia dúzia de chaves faz o `config/config.php` cair no
     * placeholder de `base_url` e o `index.php` abrir a página de instalação em
     * vez de responder 401.
     *
     * A metade compartilhada — os caminhos do ambiente e as cinco chaves de
     * configuração — vem de ChildEnvironment::base(); aqui ficam só as do
     * servidor: a resposta `APP_*` e as credenciais `DB_*`.
     *
     * Por que cada uma das cinco chaves de configuração é necessária — e por que
     * a ausência delas passa num teste e falha no CI — está em AGENTS.md, na
     * seção `FrontendBoundaryTest`.
     */
    private static function childEnvironment(int $port): array
    {
        return ChildEnvironment::base() + [
            'APP_ENVIRONMENT' => 'production',
            'APP_BASEURL' => "http://127.0.0.1:{$port}/",
            'API_ENABLED' => 'true',
            'DB_HOSTNAME' => $_ENV['DB_HOSTNAME'] ?? '127.0.0.1',
            'DB_PORT' => $_ENV['DB_PORT'] ?? '',
            'DB_DATABASE' => $_ENV['DB_DATABASE'] ?? '',
            'DB_USERNAME' => $_ENV['DB_USERNAME'] ?? '',
            'DB_PASSWORD' => $_ENV['DB_PASSWORD'] ?? '',
        ];
    }

    /**
     * Reserva uma porta liberando uma na sequência.
     *
     * Pede a porta 0 para o sistema escolher e fecha o socket antes de devolver:
     * não existe "reservar" uma porta no Unix, e o caminho é o mesmo dos testes
     * que precisam de uma porta efêmera. A janela entre o `fclose()` e o
     * `php -S` assumir é pequena, e a falha de alguém ter ficado com a porta no
     * intervalo aparece como bind, e o bind está na saída do filho, que o skip
     * mostra.
     */
    private static function freePort(): ?int
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);

        if ($socket === false) {
            return null;
        }

        $name = stream_socket_get_name($socket, false);
        fclose($socket);

        $port = (int) substr((string) $name, (int) strrpos((string) $name, ':') + 1);

        return $port > 0 ? $port : null;
    }

    private static function canConnect(int $port): bool
    {
        $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.25);

        if ($socket === false) {
            return false;
        }

        fclose($socket);

        return true;
    }

    private static function readServerOutput(): string
    {
        $output = '';

        foreach (self::$pipes as $pipe) {
            if (is_resource($pipe)) {
                $output .= (string) stream_get_contents($pipe);
            }
        }

        return trim($output) ?: 'o processo não produziu saída.';
    }

    /**
     * @return list<string>
     */
    private static function logFiles(): array
    {
        $path = dirname(__DIR__, 2) . '/logs';

        if (! is_dir($path)) {
            return [];
        }

        $files = array_values(array_diff(scandir($path) ?: [], ['.', '..']));

        sort($files);

        return $files;
    }
}
