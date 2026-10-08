<?php

use PHPUnit\Framework\Attributes\DataProvider;

require_once APPPATH . 'helpers' . DIRECTORY_SEPARATOR . 'cookie_seguro_helper.php';

/**
 * Padrões de cookie e sessão.
 *
 * O ponto sensível é decidir quando a requisição está em HTTPS: errar para
 * menos deixa o cookie sem Secure, e errar para mais faz o navegador descartar
 * o cookie em HTTP e trava o login. O X-Forwarded-Proto é a parte delicada,
 * porque qualquer cliente pode mandá-lo.
 */
final class CookieSeguroTest extends MaposTestCase
{
    // --- Detecção de HTTPS -------------------------------------------------

    public function testHttpsDiretoEhDetectado(): void
    {
        $this->assertTrue(cookieRequisicaoHttps(['HTTPS' => 'on'], ''));
        $this->assertTrue(cookieRequisicaoHttps(['HTTPS' => '1'], ''));
    }

    public function testHttpPuroNaoEhHttps(): void
    {
        $this->assertFalse(cookieRequisicaoHttps([], ''));
        $this->assertFalse(cookieRequisicaoHttps(['HTTPS' => 'off'], ''));
        $this->assertFalse(cookieRequisicaoHttps(['HTTPS' => ''], ''));
    }

    /**
     * Sem proxy confiável configurado, o cabeçalho é ignorado. Senão qualquer
     * cliente faria o servidor achar que está em HTTPS.
     */
    public function testForwardedProtoSemProxyConfiavelEhIgnorado(): void
    {
        $server = ['HTTP_X_FORWARDED_PROTO' => 'https', 'REMOTE_ADDR' => '203.0.113.9'];

        $this->assertFalse(cookieRequisicaoHttps($server, ''));
        $this->assertFalse(cookieRequisicaoHttps($server, '10.0.0.1'));
    }

    public function testForwardedProtoDeProxyConfiavelEhAceito(): void
    {
        $server = ['HTTP_X_FORWARDED_PROTO' => 'https', 'REMOTE_ADDR' => '10.0.0.1'];

        $this->assertTrue(cookieRequisicaoHttps($server, '10.0.0.1'));
        $this->assertTrue(cookieRequisicaoHttps($server, '192.168.0.1, 10.0.0.0/8'));
        $this->assertTrue(cookieRequisicaoHttps($server, ['10.0.0.1']));
    }

    public function testForwardedProtoHttpDeProxyConfiavelNaoEhHttps(): void
    {
        $server = ['HTTP_X_FORWARDED_PROTO' => 'http', 'REMOTE_ADDR' => '10.0.0.1'];

        $this->assertFalse(cookieRequisicaoHttps($server, '10.0.0.1'));
    }

    /**
     * Em cadeia de proxies o primeiro valor é o protocolo do cliente.
     */
    public function testForwardedProtoEmCadeiaUsaOPrimeiroValor(): void
    {
        $proxy = '10.0.0.1';

        $this->assertTrue(cookieRequisicaoHttps(['HTTP_X_FORWARDED_PROTO' => 'HTTPS, http', 'REMOTE_ADDR' => $proxy], $proxy));
        $this->assertFalse(cookieRequisicaoHttps(['HTTP_X_FORWARDED_PROTO' => 'http, https', 'REMOTE_ADDR' => $proxy], $proxy));
    }

    // --- Lista de proxies ------------------------------------------------

    #[DataProvider('ipsNaLista')]
    public function testIpConfiavel(string $ip, string $lista, bool $esperado): void
    {
        $this->assertSame($esperado, cookieIpConfiavel($ip, $lista));
    }

    public static function ipsNaLista(): array
    {
        return [
            'ip exato' => ['10.0.0.1', '10.0.0.1', true],
            'ip diferente' => ['10.0.0.2', '10.0.0.1', false],
            'sub-rede /24 dentro' => ['192.168.5.77', '192.168.5.0/24', true],
            'sub-rede /24 fora' => ['192.168.6.1', '192.168.5.0/24', false],
            'mascara nao multipla de 8 dentro' => ['172.16.31.1', '172.16.0.0/12', true],
            'mascara nao multipla de 8 fora' => ['172.32.0.1', '172.16.0.0/12', false],
            'lista com espacos' => ['10.0.1.200', ' 192.168.0.1 , 10.0.1.200 ', true],
            'ipv6 exato' => ['::1', '::1', true],
            'ipv6 em sub-rede' => ['2001:db8::42', '2001:db8::/32', true],
            'ipv6 fora' => ['2001:db9::1', '2001:db8::/32', false],
            'ipv4 contra rede ipv6' => ['10.0.0.1', '::/0', false],
            'lista vazia' => ['10.0.0.1', '', false],
            'ip invalido' => ['nao-e-ip', '10.0.0.0/8', false],
            'mascara invalida' => ['10.0.0.1', '10.0.0.0/40', false],
        ];
    }

    // --- cookie_secure -----------------------------------------------------

    #[DataProvider('valoresAuto')]
    public function testSecureAutoSegueOProtocolo($valor): void
    {
        $this->assertTrue(cookieSecure($valor, ['HTTPS' => 'on'], ''));
        $this->assertFalse(cookieSecure($valor, [], ''));
    }

    public static function valoresAuto(): array
    {
        return [
            'ausente' => [null],
            'vazio' => [''],
            'auto' => ['auto'],
            'AUTO' => ['AUTO'],
            'valor nao reconhecido' => ['talvez'],
        ];
    }

    /**
     * Valor explícito no .env vale como está, inclusive o false que as
     * instalações antigas trazem.
     */
    public function testSecureExplicitoEhRespeitado(): void
    {
        $this->assertTrue(cookieSecure('true', [], ''));
        $this->assertFalse(cookieSecure('false', ['HTTPS' => 'on'], ''));
    }

    // --- Flags e SameSite -------------------------------------------------

    public function testFlagAusenteUsaOPadrao(): void
    {
        $this->assertTrue(cookieEnvFlag(null, true));
        $this->assertTrue(cookieEnvFlag('', true));
        $this->assertTrue(cookieEnvFlag('nao-e-booleano', true));
        $this->assertFalse(cookieEnvFlag('false', true));
        $this->assertTrue(cookieEnvFlag('true', false));
    }

    #[DataProvider('valoresSameSite')]
    public function testSameSite($valor, bool $secure, string $esperado): void
    {
        $this->assertSame($esperado, cookieSameSite($valor, $secure));
    }

    public static function valoresSameSite(): array
    {
        return [
            'ausente vira Lax' => [null, false, 'Lax'],
            'invalido vira Lax' => ['qualquer', true, 'Lax'],
            'strict normalizado' => ['strict', false, 'Strict'],
            'none com secure' => ['None', true, 'None'],
            'none sem secure cai para Lax' => ['None', false, 'Lax'],
        ];
    }

    // --- config.php --------------------------------------------------------

    /**
     * Instalação nova, com o .env.example atual, acessando por HTTP.
     */
    public function testConfigComEnvNovoEmHttp(): void
    {
        $config = $this->carregarConfig($this->envDoExemplo(), ['REMOTE_ADDR' => '127.0.0.1']);

        $this->assertFalse($config['cookie_secure'], 'Em HTTP o cookie não pode sair com Secure.');
        $this->assertTrue($config['cookie_httponly']);
        $this->assertSame('Lax', $config['cookie_samesite']);
        $this->assertSame('Lax', $config['sess_samesite']);
        $this->assertTrue($config['sess_regenerate_destroy']);
    }

    public function testConfigComEnvNovoEmHttps(): void
    {
        $config = $this->carregarConfig($this->envDoExemplo(), ['HTTPS' => 'on', 'REMOTE_ADDR' => '127.0.0.1']);

        $this->assertTrue($config['cookie_secure']);
    }

    /**
     * .env sem nenhuma das variáveis de cookie: os padrões seguros valem.
     */
    public function testConfigSemAsVariaveisUsaOsPadroesSeguros(): void
    {
        $config = $this->carregarConfig(['GLOBAL_XSS_FILTERING' => 'true', 'APP_ENCRYPTION_KEY' => 'x'], ['HTTPS' => 'on']);

        $this->assertTrue($config['cookie_secure']);
        $this->assertTrue($config['cookie_httponly']);
        $this->assertSame('Lax', $config['cookie_samesite']);
        $this->assertTrue($config['sess_regenerate_destroy']);
    }

    /**
     * .env de uma instalação 4.x: os valores explícitos continuam valendo.
     */
    public function testConfigComEnvAntigoRespeitaOsValores(): void
    {
        $env = $this->envDoExemplo();
        $env['APP_COOKIE_SECURE'] = 'false';
        $env['APP_COOKIE_HTTPONLY'] = 'false';
        $env['APP_SESS_REGENERATE_DESTROY'] = 'false';
        unset($env['APP_COOKIE_SAMESITE']);

        $config = $this->carregarConfig($env, ['HTTPS' => 'on']);

        $this->assertFalse($config['cookie_secure']);
        $this->assertFalse($config['cookie_httponly']);
        $this->assertFalse($config['sess_regenerate_destroy']);
        $this->assertSame('Lax', $config['cookie_samesite'], 'SameSite não existia na 4.x, então vale o padrão.');
    }

    private function envDoExemplo(): array
    {
        $env = parse_ini_file(APPPATH . '.env.example', false, INI_SCANNER_RAW);

        return array_map(fn ($v) => trim((string) $v, '"'), $env);
    }

    /**
     * Avalia o config.php com o $_ENV e o $_SERVER informados.
     */
    private function carregarConfig(array $env, array $server): array
    {
        $envAntes = $_ENV;
        $serverAntes = $_SERVER;
        $fusoAntes = date_default_timezone_get();

        try {
            $_ENV = $env;
            $_SERVER = $server;

            return (static function () {
                $config = [];
                require APPPATH . 'config' . DIRECTORY_SEPARATOR . 'config.php';

                return $config;
            })();
        } finally {
            $_ENV = $envAntes;
            $_SERVER = $serverAntes;
            date_default_timezone_set($fusoAntes);
        }
    }
}
