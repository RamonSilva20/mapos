<?php

require_once APPPATH . 'helpers' . DIRECTORY_SEPARATOR . 'seguranca_cabecalhos_helper.php';
require_once APPPATH . 'controllers' . DIRECTORY_SEPARATOR . 'Csp.php';

/**
 * Hardening do servidor (#2879): chaves do .env.example recusadas, o mPDF sem
 * acesso à rede, o HTML dos relatórios sem recursos externos, o log de
 * violações da CSP fora de application/ e as regras de servidor web entregues.
 */
final class HardeningServidorTest extends MaposTestCase
{
    /** @var resource|null */
    private $servidor;

    private string $pasta = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->pasta = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mapos-hardening-' . bin2hex(random_bytes(4));
        mkdir($this->pasta, 0777, true);
    }

    protected function tearDown(): void
    {
        if (is_resource($this->servidor)) {
            proc_terminate($this->servidor);
            proc_close($this->servidor);
        }
        $this->removerPasta($this->pasta);
        unset($_ENV['STORAGE_PATH']);
        parent::tearDown();
    }

    private function removerPasta(string $pasta): void
    {
        if (! is_dir($pasta)) {
            return;
        }
        foreach (array_diff(scandir($pasta), ['.', '..']) as $item) {
            $caminho = $pasta . DIRECTORY_SEPARATOR . $item;
            is_dir($caminho) ? $this->removerPasta($caminho) : unlink($caminho);
        }
        rmdir($pasta);
    }

    public function testChavesDoEnvExemploSaoInseguras(): void
    {
        $this->assertTrue(chaveInsegura(null, 'enter_jwt_key', 32));
        $this->assertTrue(chaveInsegura('', 'enter_jwt_key', 32));
        $this->assertTrue(chaveInsegura('   ', 'enter_jwt_key', 32));
        $this->assertTrue(chaveInsegura('enter_jwt_key', 'enter_jwt_key'));
        $this->assertTrue(chaveInsegura(str_repeat('a', 31), 'enter_jwt_key', 32));
        $this->assertFalse(chaveInsegura(str_repeat('a', 32), 'enter_jwt_key', 32));
        $this->assertFalse(chaveInsegura(base64_encode(random_bytes(32)), 'enter_jwt_key', 32));
        $this->assertFalse(chaveInsegura('curta-mas-propria', 'enter_encryption_key'));
    }

    public function testChaveJwtLidaDoEnv(): void
    {
        $anterior = $_ENV['API_JWT_KEY'] ?? null;

        try {
            unset($_ENV['API_JWT_KEY']);
            $this->assertTrue(apiChaveJwtInsegura());

            $_ENV['API_JWT_KEY'] = 'enter_jwt_key';
            $this->assertTrue(apiChaveJwtInsegura());

            $_ENV['API_JWT_KEY'] = base64_encode(random_bytes(32));
            $this->assertFalse(apiChaveJwtInsegura());
        } finally {
            if ($anterior === null) {
                unset($_ENV['API_JWT_KEY']);
            } else {
                $_ENV['API_JWT_KEY'] = $anterior;
            }
        }
    }

    public function testRelatorioRemoveRecursosExternos(): void
    {
        $html = '<p>Texto</p><img src="https://atacante.example/p.png" alt="x"><img src="/assets/img/logo.png" alt="y">'
            . '<a href="ftp://atacante.example/a">ftp</a><a href="https://ok.example/">https</a>';

        $relatorio = printSafeHtml($html, true);
        $this->assertStringNotContainsString('atacante.example/p.png', $relatorio);
        $this->assertStringContainsString('/assets/img/logo.png', $relatorio);
        $this->assertStringNotContainsString('ftp://', $relatorio);
        $this->assertStringContainsString('https://ok.example/', $relatorio);

        // Telas do painel mantêm a imagem remota (a CSP do navegador decide), mas sem esquemas exóticos.
        $painel = printSafeHtml($html);
        $this->assertStringContainsString('https://atacante.example/p.png', $painel);
        $this->assertStringNotContainsString('ftp://', $painel);
    }

    /**
     * Sobe um servidor local que registra cada requisição; o mPDF configurado
     * pelo Map-OS não pode chegar nele, e um mPDF comum chega (controle).
     */
    public function testMpdfDoMaposNaoBuscaRecursosRemotos(): void
    {
        $registro = $this->pasta . DIRECTORY_SEPARATOR . 'acessos.log';
        $roteador = $this->pasta . DIRECTORY_SEPARATOR . 'roteador.php';
        file_put_contents($roteador, '<?php file_put_contents(' . var_export($registro, true) . ', $_SERVER["REQUEST_URI"] . "\n", FILE_APPEND);'
            . ' header("Content-Type: image/png"); readfile(' . var_export(MAPOS_ROOT . '/assets/img/favicon.png', true) . ');');

        $porta = $this->subirServidor($roteador);
        if ($porta === null) {
            $this->markTestSkipped('Não foi possível subir o servidor local de teste.');
        }

        $temp = FCPATH . 'assets/uploads/temp/';
        if (! is_dir($temp) && ! @mkdir($temp, 0777, true) && ! is_dir($temp)) {
            $this->markTestSkipped('Sem permissão para criar assets/uploads/temp.');
        }

        require_once APPPATH . 'helpers' . DIRECTORY_SEPARATOR . 'mpdf_helper.php';

        $html = '<html><body><p>Relatório</p><img src="http://127.0.0.1:' . $porta . '/imagem.png">'
            . '<div style="background-image: url(http://127.0.0.1:' . $porta . '/fundo.png)">x</div></body></html>';

        // Controle: o mPDF de fábrica faz a requisição, então o teste detecta um vazamento.
        $comum = new \Mpdf\Mpdf(['tempDir' => $temp]);
        $comum->WriteHTML($html);
        $this->assertFileExists($registro, 'o mPDF de fábrica deveria buscar a imagem remota (controle do teste)');
        unlink($registro);

        $mapos = mpdfCriar();
        $mapos->WriteHTML($html);
        $pdf = $mapos->Output('', 'S');

        $this->assertFileDoesNotExist($registro, 'o mPDF do Map-OS não pode requisitar URL remota');
        $this->assertStringStartsWith('%PDF', $pdf);
    }

    public function testMpdfDoMaposAindaGeraPdfComImagemLocal(): void
    {
        $temp = FCPATH . 'assets/uploads/temp/';
        if (! is_dir($temp) && ! @mkdir($temp, 0777, true) && ! is_dir($temp)) {
            $this->markTestSkipped('Sem permissão para criar assets/uploads/temp.');
        }

        require_once APPPATH . 'helpers' . DIRECTORY_SEPARATOR . 'mpdf_helper.php';

        $imagem = MAPOS_ROOT . '/assets/img/favicon.png';
        $local = $this->pasta . DIRECTORY_SEPARATOR . 'logo.png';
        $dataUri = 'data:image/png;base64,' . base64_encode((string) file_get_contents($imagem));

        $mpdf = mpdfCriar(true);
        $mpdf->WriteHTML('<p>Local</p><img src="' . $imagem . '" width="20"><img src="' . $dataUri . '" width="20">');
        $pdf = $mpdf->Output('', 'S');

        $this->assertStringStartsWith('%PDF', $pdf);
        // A imagem entrou no PDF (XObject), não foi descartada.
        $this->assertStringContainsString('/Subtype /Image', $pdf);
        $this->assertFileDoesNotExist($local);
    }

    public function testViolacoesDaCspSaemDeApplicationLogs(): void
    {
        $_ENV['STORAGE_PATH'] = $this->pasta . DIRECTORY_SEPARATOR . 'storage';

        $csp = $this->makeInstance('Csp');
        $arquivo = $this->invokeMethod($csp, 'arquivoDeViolacoes');

        $this->assertSame($_ENV['STORAGE_PATH'] . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'csp-violacoes.json', $arquivo);
        $this->assertDirectoryExists(dirname($arquivo));
        $this->assertStringNotContainsString(rtrim(APPPATH, '/\\') . DIRECTORY_SEPARATOR . 'logs', $arquivo);

        $novas = segurancaCspRegistrar(
            segurancaCspLerRelatorio(json_encode(['csp-report' => [
                'effective-directive' => 'img-src',
                'blocked-uri' => 'https://cdn.example/x.png',
                'document-uri' => 'https://mapos.example/os',
            ]])),
            $arquivo,
            time()
        );
        $this->assertNotEmpty($novas);
        $this->assertFileExists($arquivo);
    }

    public function testViolacoesAntigasSaoMovidasParaOStorage(): void
    {
        $_ENV['STORAGE_PATH'] = $this->pasta . DIRECTORY_SEPARATOR . 'storage';
        $antigo = APPPATH . 'logs' . DIRECTORY_SEPARATOR . 'csp-violacoes.json';
        if (file_exists($antigo)) {
            $this->markTestSkipped('Já existe application/logs/csp-violacoes.json local.');
        }
        if (! is_writable(dirname($antigo))) {
            $this->markTestSkipped('application/logs não é gravável.');
        }

        file_put_contents($antigo, '{"x":{"total":3}}');

        try {
            $arquivo = $this->invokeMethod($this->makeInstance('Csp'), 'arquivoDeViolacoes');
            $this->assertFileDoesNotExist($antigo);
            $this->assertSame('{"x":{"total":3}}', file_get_contents($arquivo));
        } finally {
            @unlink($antigo);
        }
    }

    public function testRegrasDeServidorBloqueiamArquivosSensiveis(): void
    {
        $nginx = (string) file_get_contents(MAPOS_ROOT . '/docker/etc/nginx/default.conf');
        $this->assertStringContainsString('location ^~ /application/', $nginx);
        $this->assertStringContainsString('location ^~ /updates/', $nginx);
        $this->assertStringContainsString('sql|json', $nginx);

        $aplicacao = (string) file_get_contents(MAPOS_ROOT . '/application/.htaccess');
        $this->assertStringContainsString('Require all denied', $aplicacao);
        $this->assertStringNotContainsString('RewriteRule', $aplicacao);

        $raiz = (string) file_get_contents(MAPOS_ROOT . '/.htaccess');
        $this->assertStringContainsString('updates|scripts|tests|docker|docs', $raiz);

        $this->assertFileExists(MAPOS_ROOT . '/docs/servidor-web.md');
    }

    /**
     * O .htaccess bloqueava arquivos com ponto, mas não pastas como .git
     * (#2858). A regra de reescrita é aplicada aqui a caminhos reais; a prova
     * no Apache de verdade (403 em /.git/HEAD) está na descrição do PR.
     */
    public function testRegraDePastasOcultasPegaGitEOutrasEDeixaOWellKnown(): void
    {
        $raiz = (string) file_get_contents(MAPOS_ROOT . '/.htaccess');
        $this->assertSame(1, preg_match('/^RewriteRule (\S+) - \[F,L\]$/m', preg_replace('/^RewriteRule \^\(updates.*$/m', '', $raiz), $achado), 'A regra de pastas ocultas está no .htaccess.');

        $padrao = '#' . $achado[1] . '#';
        foreach (['.git/HEAD', '.git/config', '.git', '.github/workflows/ci.yml', '.idea/x.xml', '.claude/worktrees/a/b', 'assets/.git/HEAD', 'application/.env', '.env', '.htaccess'] as $caminho) {
            $this->assertSame(1, preg_match($padrao, $caminho), $caminho . ' deve ser negado.');
        }
        foreach (['.well-known/acme-challenge/token', '.well-known', 'assets/dist/app.css', 'index.php', 'os/anexo/1', 'assets/img/logo.png', 'a.git/x'] as $caminho) {
            $this->assertSame(0, preg_match($padrao, $caminho), $caminho . ' deve seguir liberado.');
        }

        // Os caminhos do nginx do Docker e as receitas documentadas seguem a mesma regra.
        $nginx = (string) file_get_contents(MAPOS_ROOT . '/docker/etc/nginx/default.conf');
        $this->assertStringContainsString('location ~ /\.(?!well-known)', $nginx);
        $docs = (string) file_get_contents(MAPOS_ROOT . '/docs/servidor-web.md');
        // A receita do Apache parte da raiz do site: sem a âncora, um caminho de instalação
        // com componente iniciado por ponto (/home/u/.local/www/mapos) dava 403 em tudo.
        $this->assertStringContainsString('<DirectoryMatch "^/caminho/do/mapos/(.*/)?\.(?!well-known)">', $docs);
        $this->assertStringNotContainsString('<DirectoryMatch "(^|/)\.(?!well-known)">', $docs);
        $this->assertStringContainsString('curl -I https://seu-site/.git/HEAD', $docs);
    }

    private function subirServidor(string $roteador): ?int
    {
        for ($tentativa = 0; $tentativa < 5; $tentativa++) {
            $porta = random_int(20000, 60000);
            $this->servidor = proc_open(
                [PHP_BINARY, '-S', '127.0.0.1:' . $porta, $roteador],
                [['pipe', 'r'], ['file', '/dev/null', 'w'], ['file', '/dev/null', 'w']],
                $pipes
            );

            for ($i = 0; $i < 30; $i++) {
                $socket = @fsockopen('127.0.0.1', $porta, $erro, $mensagem, 0.2);
                if ($socket !== false) {
                    fclose($socket);

                    return $porta;
                }
                usleep(100000);
            }

            proc_terminate($this->servidor);
            proc_close($this->servidor);
            $this->servidor = null;
        }

        return null;
    }
}
