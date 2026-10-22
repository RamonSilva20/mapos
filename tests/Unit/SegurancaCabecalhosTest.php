<?php

use PHPUnit\Framework\Attributes\DataProvider;

require_once APPPATH . 'helpers' . DIRECTORY_SEPARATOR . 'seguranca_cabecalhos_helper.php';

/**
 * Cabeçalhos de segurança, CSP em enforce (ou report-only) e o endpoint de relatórios.
 */
final class SegurancaCabecalhosTest extends MaposTestCase
{
    private string $arquivo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->arquivo = tempnam(sys_get_temp_dir(), 'csp');
        unlink($this->arquivo);
    }

    protected function tearDown(): void
    {
        @unlink($this->arquivo);
        parent::tearDown();
    }

    // --- Cabeçalhos ----------------------------------------------------------

    public function testCabecalhosBasicosSaemEmHttp(): void
    {
        $h = segurancaCabecalhos([], []);

        $this->assertSame('SAMEORIGIN', $h['X-Frame-Options']);
        $this->assertSame('nosniff', $h['X-Content-Type-Options']);
        $this->assertSame('strict-origin-when-cross-origin', $h['Referrer-Policy']);
        $this->assertStringContainsString('camera=()', $h['Permissions-Policy']);
        $this->assertStringContainsString('geolocation=()', $h['Permissions-Policy']);
        $this->assertArrayHasKey('Content-Security-Policy', $h, 'Padrão da v5: CSP em enforce (#2878).');
        $this->assertArrayNotHasKey('Content-Security-Policy-Report-Only', $h);
    }

    /**
     * HSTS em HTTP é ignorado pelo navegador e não deve sair: mandá-lo só
     * confunde quem lê os cabeçalhos.
     */
    public function testHstsNaoSaiEmHttp(): void
    {
        $this->assertArrayNotHasKey('Strict-Transport-Security', segurancaCabecalhos([], []));
    }

    public function testHstsSaiEmHttps(): void
    {
        $h = segurancaCabecalhos([], ['HTTPS' => 'on']);

        $this->assertSame('max-age=31536000', $h['Strict-Transport-Security']);
    }

    public function testHstsComSubdominiosEMaxAgeConfiguraveis(): void
    {
        $h = segurancaCabecalhos(['APP_HSTS_MAX_AGE' => '600', 'APP_HSTS_INCLUDE_SUBDOMAINS' => 'true'], ['HTTPS' => 'on']);

        $this->assertSame('max-age=600; includeSubDomains', $h['Strict-Transport-Security']);
    }

    public function testHstsComMaxAgeZeroFicaDesligado(): void
    {
        $this->assertArrayNotHasKey('Strict-Transport-Security', segurancaCabecalhos(['APP_HSTS_MAX_AGE' => '0'], ['HTTPS' => 'on']));
    }

    /**
     * Em desenvolvimento o navegador guardaria o HSTS por um ano e passaria a
     * forçar HTTPS num domínio local que também é usado por HTTP.
     */
    public function testHstsDesligadoPorPadraoEmDesenvolvimento(): void
    {
        $this->assertArrayNotHasKey(
            'Strict-Transport-Security',
            segurancaCabecalhos(['APP_ENVIRONMENT' => 'development'], ['HTTPS' => 'on'])
        );
    }

    public function testHstsExplicitoValeEmDesenvolvimento(): void
    {
        $h = segurancaCabecalhos(['APP_ENVIRONMENT' => 'development', 'APP_HSTS_MAX_AGE' => '600'], ['HTTPS' => 'on']);

        $this->assertSame('max-age=600', $h['Strict-Transport-Security']);
    }

    public function testHstsLigadoPorPadraoEmProducao(): void
    {
        $h = segurancaCabecalhos(['APP_ENVIRONMENT' => 'production'], ['HTTPS' => 'on']);

        $this->assertSame('max-age=31536000', $h['Strict-Transport-Security']);
    }

    public function testMaxAgeInvalidoUsaOPadrao(): void
    {
        $h = segurancaCabecalhos(['APP_HSTS_MAX_AGE' => 'um ano'], ['HTTPS' => 'on']);

        $this->assertSame('max-age=31536000', $h['Strict-Transport-Security']);
    }

    /**
     * O X-Forwarded-Proto pode ser forjado por qualquer cliente. Só vale vindo
     * de um proxy listado em APP_PROXY_IPS, como nos cookies.
     */
    public function testForwardedProtoDeProxyConfiavelLigaHsts(): void
    {
        $server = ['HTTP_X_FORWARDED_PROTO' => 'https', 'REMOTE_ADDR' => '10.0.0.5'];

        $h = segurancaCabecalhos(['APP_PROXY_IPS' => '10.0.0.0/8'], $server);

        $this->assertArrayHasKey('Strict-Transport-Security', $h);
    }

    public function testForwardedProtoDeIpNaoConfiavelEhIgnorado(): void
    {
        $server = ['HTTP_X_FORWARDED_PROTO' => 'https', 'REMOTE_ADDR' => '203.0.113.9'];

        $this->assertArrayNotHasKey('Strict-Transport-Security', segurancaCabecalhos(['APP_PROXY_IPS' => '10.0.0.0/8'], $server));
        $this->assertArrayNotHasKey('Strict-Transport-Security', segurancaCabecalhos([], $server));
    }

    public function testDesligarTudo(): void
    {
        $this->assertSame([], segurancaCabecalhos(['APP_SECURITY_HEADERS' => 'false'], ['HTTPS' => 'on']));
    }

    public function testCspPodeSerDesligada(): void
    {
        $h = segurancaCabecalhos(['APP_CSP' => 'off'], []);

        $this->assertArrayNotHasKey('Content-Security-Policy', $h);
        $this->assertArrayNotHasKey('Content-Security-Policy-Report-Only', $h);
        $this->assertArrayHasKey('X-Frame-Options', $h);
    }

    public function testModosDaCsp(): void
    {
        $this->assertSame('enforce', segurancaModoCsp([]));
        $this->assertSame('enforce', segurancaModoCsp(['APP_CSP' => 'Enforce']));
        $this->assertSame('enforce', segurancaModoCsp(['APP_CSP' => 'lixo']), 'Valor inválido cai no padrão seguro.');
        $this->assertSame('report-only', segurancaModoCsp(['APP_CSP' => 'report-only']));
        $this->assertSame('off', segurancaModoCsp(['APP_CSP' => 'off']));

        $h = segurancaCabecalhos(['APP_CSP' => 'report-only'], []);
        $this->assertArrayHasKey('Content-Security-Policy-Report-Only', $h);
        $this->assertArrayNotHasKey('Content-Security-Policy', $h);
    }

    public function testReportOnlyDaFaseAnteriorContinuaValendo(): void
    {
        $this->assertSame('report-only', segurancaModoCsp(['APP_CSP_REPORT_ONLY' => 'true']));
        $this->assertSame('off', segurancaModoCsp(['APP_CSP_REPORT_ONLY' => 'false']));
        $this->assertSame('enforce', segurancaModoCsp(['APP_CSP_REPORT_ONLY' => '']));
        $this->assertSame('enforce', segurancaModoCsp(['APP_CSP' => 'enforce', 'APP_CSP_REPORT_ONLY' => 'true']), 'APP_CSP vence o antigo.');
    }

    // --- Política CSP ------------------------------------------------------

    public function testNonceLiberaSoOStyleSrcENuncaOUnsafeInline(): void
    {
        $nonce = 'abc123==';
        $csp = segurancaPoliticaCsp('', $nonce);

        $this->assertStringContainsString("style-src 'self' 'nonce-abc123=='", $csp);
        $this->assertStringContainsString("script-src 'self';", $csp, 'O script-src não ganha nonce: o FullCalendar só precisa do estilo.');
        $this->assertStringNotContainsString('unsafe-inline', $csp);
        $this->assertStringNotContainsString('unsafe-eval', $csp);
        $this->assertStringContainsString("style-src 'self';", segurancaPoliticaCsp(''), 'Sem nonce, a política é a de antes.');
    }

    public function testCabecalhosLevamONonceDaRequisicao(): void
    {
        $h = segurancaCabecalhos([], [], 'N0nce==');

        $this->assertStringContainsString("'nonce-N0nce=='", $h['Content-Security-Policy']);
    }

    public function testNonceEUnicoPorRequisicaoEImprevisivel(): void
    {
        $primeiro = segurancaGerarNonce();
        $this->assertSame($primeiro, segurancaNonceAtual());
        $this->assertSame(24, strlen($primeiro), '16 bytes em base64.');

        $segundo = segurancaGerarNonce();
        $this->assertNotSame($primeiro, $segundo);
        $this->assertSame($segundo, segurancaNonceAtual());

        unset($GLOBALS['mapos_csp_nonce']);
        $this->assertSame('', segurancaNonceAtual());
    }

    public function testHookSoGeraNonceComCspLigadaEOLayoutPublicaAMeta(): void
    {
        $hook = (string) file_get_contents(APPPATH . 'hooks/security_headers.php');
        $this->assertStringContainsString("segurancaModoCsp(\$_ENV) === 'off' ? '' : segurancaGerarNonce()", $hook);

        $topo = (string) file_get_contents(APPPATH . 'views/tema/topo.php');
        $this->assertStringContainsString('<meta name="csp-nonce" content="<?= e(segurancaNonceAtual()) ?>">', $topo);
        $this->assertStringContainsString("function_exists('segurancaNonceAtual')", $topo);

        // O FullCalendar lê o nonce dessa meta.
        $fc = (string) file_get_contents(MAPOS_ROOT . '/assets/vendor/fullcalendar/fullcalendar.global.min.js');
        $this->assertStringContainsString('csp-nonce', $fc);
    }

    public function testPoliticaNaoLiberaScriptOuEstiloInline(): void
    {
        $csp = segurancaPoliticaCsp('/index.php/csp/report');

        $this->assertStringNotContainsString('unsafe-inline', $csp);
        $this->assertStringNotContainsString('unsafe-eval', $csp);
        $this->assertStringContainsString("script-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
    }

    public function testConnectSrcLiberaSoAsApisDeCepECnpjDoFormularioDeCliente(): void
    {
        $csp = segurancaPoliticaCsp('');

        $this->assertStringContainsString(
            "connect-src 'self' https://viacep.com.br https://brasilapi.com.br https://minhareceita.org https://publica.cnpj.ws;",
            $csp,
            'CEP: ViaCEP. CNPJ: BrasilAPI, com Minha Receita e CNPJ.ws de alternativa (#2996).'
        );
        $this->assertStringNotContainsString('receitaws', $csp, 'A ReceitaWS não manda CORS e não é usada por fetch.');
        $this->assertDoesNotMatchRegularExpression('/connect-src[^;]*\*/', $csp, 'Nada de curinga no connect-src.');
    }

    public function testCspApontaParaOEndpointNaSubpastaDaInstalacao(): void
    {
        $h = segurancaCabecalhos(['APP_BASEURL' => 'https://exemplo.com/mapos/'], []);

        $this->assertStringEndsWith('report-uri /mapos/index.php/csp/report', $h['Content-Security-Policy']);
    }

    public function testCspNaRaizDoDominio(): void
    {
        $this->assertSame('/index.php/csp/report', segurancaCspReportUri('http://mapos.test/'));
        $this->assertSame('/index.php/csp/report', segurancaCspReportUri(''));
    }

    public function testCspSemRelatorio(): void
    {
        $h = segurancaCabecalhos(['APP_CSP_REPORT' => 'false'], []);

        $this->assertStringNotContainsString('report-uri', $h['Content-Security-Policy']);
    }

    // --- Endpoint de relatórios -------------------------------------------

    public function testRelatorioValidoEhAceito(): void
    {
        $this->assertSame(0, segurancaCspRecusa('POST', 'application/csp-report', '{}'));
        $this->assertSame(0, segurancaCspRecusa('post', 'application/reports+json; charset=utf-8', '[]'));
    }

    public function testMetodoDiferenteDePostEhRecusado(): void
    {
        $this->assertSame(405, segurancaCspRecusa('GET', 'application/csp-report', ''));
    }

    public function testContentTypeQueNaoEhRelatorioEhRecusado(): void
    {
        $this->assertSame(415, segurancaCspRecusa('POST', 'application/x-www-form-urlencoded', 'a=1'));
        $this->assertSame(415, segurancaCspRecusa('POST', '', '{}'));
    }

    public function testCorpoGrandeEhRecusado(): void
    {
        $corpo = str_repeat('a', SEGURANCA_CSP_RELATORIO_MAX + 1);

        $this->assertSame(413, segurancaCspRecusa('POST', 'application/csp-report', $corpo));
        $this->assertSame(0, segurancaCspRecusa('POST', 'application/csp-report', substr($corpo, 1)));
    }

    public function testLeFormatoReportUri(): void
    {
        $corpo = json_encode(['csp-report' => [
            'document-uri' => 'http://mapos.test/index.php/os/visualizar/3?x=1',
            'violated-directive' => "script-src-elem 'self'",
            'effective-directive' => 'script-src-elem',
            'blocked-uri' => 'https://cdn.rawgit.com/cozmo/jsQR/master/dist/jsQR.js',
            'source-file' => 'http://mapos.test/index.php/os/visualizar/3',
            'line-number' => 390,
        ]]);

        $this->assertSame([[
            'diretiva' => 'script-src-elem',
            'bloqueado' => 'https://cdn.rawgit.com',
            'pagina' => '/index.php/os/visualizar/3',
            'fonte' => 'http://mapos.test/index.php/os/visualizar/3:390',
        ]], segurancaCspLerRelatorio($corpo));
    }

    public function testLeFormatoReportingApi(): void
    {
        $corpo = json_encode([
            ['type' => 'csp-violation', 'url' => 'http://mapos.test/index.php/mapos', 'body' => [
                'documentURL' => 'http://mapos.test/index.php/mapos',
                'effectiveDirective' => 'script-src-elem',
                'blockedURL' => 'inline',
            ]],
            ['type' => 'deprecation', 'body' => ['id' => 'x']],
        ]);

        $violacoes = segurancaCspLerRelatorio($corpo);

        $this->assertCount(1, $violacoes);
        $this->assertSame('inline', $violacoes[0]['bloqueado']);
        $this->assertSame('/index.php/mapos', $violacoes[0]['pagina']);
    }

    #[DataProvider('payloadsInvalidos')]
    public function testPayloadMalformadoNaoGeraViolacao($corpo): void
    {
        $this->assertSame([], segurancaCspLerRelatorio($corpo));
    }

    public static function payloadsInvalidos(): array
    {
        return [
            'vazio' => [''],
            'nao json' => ['<script>alert(1)</script>'],
            'json escalar' => ['42'],
            'objeto sem csp-report' => ['{"foo":1}'],
            'csp-report string' => ['{"csp-report":"x"}'],
            'diretiva ausente' => ['{"csp-report":{"blocked-uri":"inline"}}'],
            'campos com tipo errado' => ['{"csp-report":{"effective-directive":["a"],"blocked-uri":1}}'],
            'lista sem body' => ['[{"type":"csp-violation"}]'],
        ];
    }

    /**
     * Um relatório forjado não pode quebrar linha no log nem gravar texto
     * arbitrariamente grande.
     */
    public function testCamposSaoLimpos(): void
    {
        $corpo = json_encode(['csp-report' => [
            'effective-directive' => "script-src\nFALSO",
            'blocked-uri' => str_repeat('x', 1000),
            'document-uri' => '/a',
        ]]);

        $v = segurancaCspLerRelatorio($corpo)[0];

        $this->assertSame('script-srcFALSO', $v['diretiva']);
        $this->assertSame(200, mb_strlen($v['bloqueado']));
    }

    // --- Agregação ---------------------------------------------------------

    public function testViolacoesIguaisSaoAgregadas(): void
    {
        $v = ['diretiva' => 'script-src-elem', 'bloqueado' => 'inline', 'pagina' => '/index.php/mapos', 'fonte' => ''];

        $this->assertSame(['script-src-elem | inline | /index.php/mapos'], segurancaCspRegistrar([$v], $this->arquivo, 1000));
        $this->assertSame([], segurancaCspRegistrar([$v, $v], $this->arquivo, 2000));

        $dados = json_decode((string) file_get_contents($this->arquivo), true);

        $this->assertCount(1, $dados);
        $registro = $dados['script-src-elem | inline | /index.php/mapos'];
        $this->assertSame(3, $registro['total']);
        $this->assertSame(date('Y-m-d H:i:s', 1000), $registro['primeira']);
        $this->assertSame(date('Y-m-d H:i:s', 2000), $registro['ultima']);
    }

    public function testLimiteDeViolacoesDistintas(): void
    {
        $lote = [];
        for ($i = 0; $i < SEGURANCA_CSP_CHAVES_MAX + 10; $i++) {
            $lote[] = ['diretiva' => 'img-src', 'bloqueado' => 'https://h' . $i . '.com', 'pagina' => '/', 'fonte' => ''];
        }

        $novas = segurancaCspRegistrar($lote, $this->arquivo, 1000);

        $this->assertCount(SEGURANCA_CSP_CHAVES_MAX, $novas);
        $this->assertCount(SEGURANCA_CSP_CHAVES_MAX, json_decode((string) file_get_contents($this->arquivo), true));
    }

    public function testArquivoCorrompidoRecomecaAContagem(): void
    {
        file_put_contents($this->arquivo, 'isto não é json');
        $v = ['diretiva' => 'img-src', 'bloqueado' => 'data:', 'pagina' => '/', 'fonte' => ''];

        $this->assertCount(1, segurancaCspRegistrar([$v], $this->arquivo, 1000));
    }

    public function testSemViolacaoNaoCriaArquivo(): void
    {
        $this->assertSame([], segurancaCspRegistrar([], $this->arquivo, 1000));
        $this->assertFileDoesNotExist($this->arquivo);
    }

    /**
     * O caminho da página vai para o log sem query string e sem tokens: o link
     * de recuperação de senha deixaria o token em claro (#2875).
     */
    public function testPaginaDaViolacaoNaoGuardaToken(): void
    {
        $token = str_repeat('ab12', 16);

        $this->assertSame('/index.php/mine/verifyTokenSenha/token/{token}', segurancaCspPagina("http://mapos.test/index.php/mine/verifyTokenSenha/token/{$token}"));
        $this->assertSame('/index.php/mine/verifyTokenSenha/token/{token}', segurancaCspPagina("http://mapos.test/index.php/mine/verifyTokenSenha/token/{$token}?x=1"));
        $this->assertSame('/index.php/os/editar/15', segurancaCspPagina('http://mapos.test/index.php/os/editar/15?tab=1'));
        $this->assertSame('/index.php/clientes', segurancaCspPagina('http://mapos.test/index.php/clientes'));

        $violacoes = segurancaCspLerRelatorio(json_encode(['csp-report' => [
            'document-uri' => "http://mapos.test/index.php/mine/verifyTokenSenha/token/{$token}",
            'violated-directive' => 'script-src-elem',
            'blocked-uri' => 'inline',
        ]]));
        $this->assertStringNotContainsString($token, json_encode($violacoes));
    }
}
