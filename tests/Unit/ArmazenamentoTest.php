<?php

/**
 * Armazenamento fora do webroot (#2872): pasta configurável, caminhos que não
 * saem da raiz, MIME conferido e cabeçalhos de download.
 */
final class ArmazenamentoTest extends MaposTestCase
{
    private string $raiz;

    private ?string $envAnterior;

    protected function setUp(): void
    {
        parent::setUp();
        $this->raiz = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mapos-storage-' . bin2hex(random_bytes(4));
        $this->envAnterior = $_ENV['STORAGE_PATH'] ?? null;
        $_ENV['STORAGE_PATH'] = $this->raiz;
    }

    protected function tearDown(): void
    {
        if ($this->envAnterior === null) {
            unset($_ENV['STORAGE_PATH']);
        } else {
            $_ENV['STORAGE_PATH'] = $this->envAnterior;
        }
        unset($_ENV['UPLOAD_MAX_KB']);
        $this->apagar($this->raiz);
        parent::tearDown();
    }

    private function apagar(string $caminho): void
    {
        if (is_file($caminho) || is_link($caminho)) {
            unlink($caminho);

            return;
        }
        foreach (is_dir($caminho) ? scandir($caminho) : [] as $item) {
            if ($item !== '.' && $item !== '..') {
                $this->apagar($caminho . DIRECTORY_SEPARATOR . $item);
            }
        }
        if (is_dir($caminho)) {
            rmdir($caminho);
        }
    }

    public function testRaizVemDoEnvOuDaPastaProtegidaDaAplicacao(): void
    {
        $this->assertSame($this->raiz, armazenamentoRaiz());

        $_ENV['STORAGE_PATH'] = 'armazem/privado/';
        $this->assertSame(rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'armazem/privado', armazenamentoRaiz());

        unset($_ENV['STORAGE_PATH']);
        $this->assertSame(rtrim(APPPATH, '/\\') . DIRECTORY_SEPARATOR . 'storage', armazenamentoRaiz());
    }

    public function testLimiteConfiguravel(): void
    {
        $this->assertSame(10240, armazenamentoLimiteKb());

        $_ENV['UPLOAD_MAX_KB'] = '2048';
        $this->assertSame(2048, armazenamentoLimiteKb());
        $this->assertSame(2048, armazenamentoConfigUpload('/x', ['pdf', 'png'])['max_size']);
        $this->assertSame('pdf|png', armazenamentoConfigUpload('/x', ['pdf', 'png'])['allowed_types']);

        $_ENV['UPLOAD_MAX_KB'] = 'abc';
        $this->assertSame(10240, armazenamentoLimiteKb());
    }

    public function testPastaProtegidaEFechadaParaOWeb(): void
    {
        $pasta = armazenamentoPasta('anexos/10-2026/OS-5/thumbs');

        $this->assertSame($this->raiz . '/anexos/10-2026/OS-5/thumbs', str_replace('\\', '/', $pasta));
        $this->assertFileExists($this->raiz . '/.htaccess');
        $this->assertStringContainsString('Require all denied', (string) file_get_contents($this->raiz . '/.htaccess'));
        $this->assertFileExists($this->raiz . '/anexos/index.html');
        $this->assertNull(armazenamentoPasta('anexos/../../fora'));
    }

    public function testLocalizarSoDevolveArquivosDentroDaRaiz(): void
    {
        $pasta = armazenamentoPasta('arquivos/01-01-2026');
        file_put_contents($pasta . '/a.txt', 'ok');
        $fora = tempnam(sys_get_temp_dir(), 'fora');

        $this->assertSame(realpath($pasta . '/a.txt'), armazenamentoLocalizar('arquivos/01-01-2026/a.txt'));
        $this->assertSame(realpath($pasta . '/a.txt'), armazenamentoLocalizar($pasta . '/a.txt'));
        $this->assertNull(armazenamentoLocalizar('arquivos/01-01-2026/nao-existe.txt'));
        $this->assertNull(armazenamentoLocalizar('../' . basename($fora)), 'Caminho relativo que sai da raiz.');
        $this->assertNull(armazenamentoLocalizar($fora), 'Caminho absoluto fora da raiz.');
        $this->assertNull(armazenamentoLocalizar(''));
        $this->assertSame(realpath($fora), armazenamentoLocalizar($fora, [dirname($fora)]), 'Raiz legada permitida.');

        unlink($fora);
    }

    public function testArquivoDoAnexoUsaCaminhoRelativoEIgnoraNomeComBarras(): void
    {
        $pasta = armazenamentoPasta('anexos/10-2026/OS-5/thumbs');
        file_put_contents(dirname($pasta) . '/abc.png', 'x');
        file_put_contents($pasta . '/thumb_abc.png', 'x');
        $anexo = (object) ['path' => 'anexos/10-2026/OS-5', 'anexo' => 'abc.png', 'thumb' => 'thumb_abc.png'];

        $this->assertSame(realpath(dirname($pasta) . '/abc.png'), armazenamentoArquivoDoAnexo($anexo));
        $this->assertSame(realpath($pasta . '/thumb_abc.png'), armazenamentoArquivoDoAnexo($anexo, true));
        $this->assertNull(armazenamentoArquivoDoAnexo((object) ['path' => 'anexos/10-2026/OS-5', 'anexo' => '../../../../etc/passwd', 'thumb' => '']));
        $this->assertNull(armazenamentoArquivoDoAnexo((object) ['path' => '/etc', 'anexo' => 'passwd', 'thumb' => '']));
    }

    public function testMimeConfereComAExtensao(): void
    {
        $pasta = armazenamentoPasta('teste');
        $png = $pasta . '/a.png';
        file_put_contents($png, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR4nGP4z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg=='));
        $php = $pasta . '/b.png';
        file_put_contents($php, '<?php echo 1;');
        $pdf = $pasta . '/c.pdf';
        file_put_contents($pdf, "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n");

        $this->assertTrue(armazenamentoMimePermitido($png, 'png'));
        $this->assertFalse(armazenamentoMimePermitido($png, 'pdf'));
        $this->assertFalse(armazenamentoMimePermitido($php, 'png'), 'Script com extensão de imagem.');
        $this->assertTrue(armazenamentoMimePermitido($pdf, '.PDF'));
        $this->assertFalse(armazenamentoMimePermitido($png, 'php'), 'Extensão fora da lista.');

        $this->assertNull(armazenamentoValidarEnvio(['full_path' => $png, 'file_ext' => '.png']));
        $this->assertSame('O conteúdo do arquivo não confere com a extensão.', armazenamentoValidarEnvio(['full_path' => $php, 'file_ext' => '.png']));
        $this->assertFileDoesNotExist($php, 'Arquivo com conteúdo divergente é apagado.');
    }

    public function testImagemComPixelsDemaisEhRejeitadaEApagada(): void
    {
        $pasta = $this->raiz . '/imagens';
        mkdir($pasta, 0750, true);
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR4nGP4z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg==');

        // Arquivo de poucos bytes que declara 10000 x 10000 px (100 megapixels): o GD
        // descompactaria a imagem inteira ao gerar a miniatura.
        $grande = $pasta . '/grande.png';
        file_put_contents($grande, substr($png, 0, 16) . pack('NN', 10000, 10000) . substr($png, 24));
        $this->assertLessThan(200, filesize($grande));

        $pequeno = $pasta . '/pequena.png';
        file_put_contents($pequeno, $png);

        $this->assertTrue(armazenamentoImagemGrandeDemais($grande, 'png'));
        $this->assertFalse(armazenamentoImagemGrandeDemais($pequeno, 'png'));
        $this->assertFalse(armazenamentoImagemGrandeDemais($grande, 'pdf'), 'Só imagens.');

        $this->assertStringContainsString('pixels demais', (string) armazenamentoValidarEnvio(['full_path' => $grande, 'file_ext' => '.png']));
        $this->assertFileDoesNotExist($grande);
        $this->assertNull(armazenamentoValidarEnvio(['full_path' => $pequeno, 'file_ext' => '.png']));

        // O limite é configurável.
        $_ENV['UPLOAD_MAX_PIXELS'] = '1';
        try {
            $this->assertSame(1, armazenamentoLimitePixels());
            $this->assertFalse(armazenamentoImagemGrandeDemais($pequeno, 'png'), '1 x 1 px cabe em 1 pixel.');
            $_ENV['UPLOAD_MAX_PIXELS'] = '0';
            $this->assertSame(40000000, armazenamentoLimitePixels());
        } finally {
            unset($_ENV['UPLOAD_MAX_PIXELS']);
        }
    }

    public function testPastaPublicaDeImagensNaoExecutaScripts(): void
    {
        $pasta = $this->raiz . '/publica';
        mkdir($pasta, 0750, true);

        armazenamentoProtegerPastaPublica($pasta);

        $this->assertFileExists($pasta . '/index.html');
        $regras = (string) file_get_contents($pasta . '/.htaccess');
        $this->assertStringContainsString('php[0-9]*|phtml|phar', $regras);
        $this->assertStringContainsString('Deny from all', $regras);
        // Mesma regra dos anexos versionados em assets/.
        $this->assertSame(
            preg_replace('/^#.*\n/', '', (string) file_get_contents(FCPATH . 'assets/anexos/.htaccess')),
            preg_replace('/^#.*\n/', '', $regras),
        );

        // Chamar de novo não sobrescreve nem falha; pasta inexistente é ignorada.
        armazenamentoProtegerPastaPublica($pasta);
        armazenamentoProtegerPastaPublica($this->raiz . '/nao-existe');
        $this->assertDirectoryDoesNotExist($this->raiz . '/nao-existe');
    }

    public function testLogoEFotoDePerfilConferemOConteudoDaImagem(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Mapos.php');
        preg_match('/private function enviarImagem\(.*?\n    }\n/s', $codigo, $metodo);

        $this->assertNotEmpty($metodo);
        $this->assertStringContainsString('armazenamentoProtegerPastaPublica($pasta)', $metodo[0]);
        $this->assertStringContainsString('armazenamentoValidarEnvio($this->upload->data())', $metodo[0]);

        $nginx = (string) file_get_contents(MAPOS_ROOT . '/docker/etc/nginx/default.conf');
        $this->assertStringContainsString('(anexos|arquivos|userImage|uploads)', $nginx);
        $this->assertFileExists(FCPATH . 'assets/userImage/.htaccess');
    }

    public function testCabecalhosDeDownload(): void
    {
        $imagem = armazenamentoCabecalhos('anexo-1.png', 'png', 10);
        $this->assertSame('image/png', $imagem['Content-Type']);
        $this->assertStringStartsWith('inline;', $imagem['Content-Disposition']);
        $this->assertSame('nosniff', $imagem['X-Content-Type-Options']);
        $this->assertStringContainsString("default-src 'none'", $imagem['Content-Security-Policy']);

        $this->assertStringStartsWith('attachment;', armazenamentoCabecalhos('anexo-1.png', 'png', 10, true)['Content-Disposition']);

        $texto = armazenamentoCabecalhos('a.txt', 'txt', 3);
        $this->assertStringStartsWith('attachment;', $texto['Content-Disposition'], 'Só imagens e PDF abrem na página.');

        $estranho = armazenamentoCabecalhos("x\r\nSet-Cookie: a=b.html", 'html', 3);
        $this->assertSame('application/octet-stream', $estranho['Content-Type']);
        $this->assertStringNotContainsString("\n", $estranho['Content-Disposition'], 'Sem quebra de linha no nome (header injection).');
    }

    public function testAnexosDaApiNaoExpoemOCaminhoEApontamParaOEndpoint(): void
    {
        require_once APPPATH . 'helpers/os_helper.php';

        $anexos = osAnexosParaApi([
            (object) ['idAnexos' => 4, 'anexo' => 'a.jpg', 'thumb' => 'thumb_a.jpg', 'path' => '/var/www/x', 'url' => 'http://x/assets/anexos/y'],
            (object) ['idAnexos' => 5, 'anexo' => 'b.pdf', 'thumb' => '', 'path' => 'anexos/z', 'url' => ''],
        ], 'http://mapos.test/api/v1/os/1/anexos/');

        $this->assertSame('http://mapos.test/api/v1/os/1/anexos/4', $anexos[0]->url);
        $this->assertSame('http://mapos.test/api/v1/os/1/anexos/4?miniatura=1', $anexos[0]->thumb_url);
        $this->assertNull($anexos[1]->thumb_url);
        $this->assertFalse(isset($anexos[0]->path));
    }

    public function testControllersSoServemPeloArmazenamentoComPermissao(): void
    {
        $os = (string) file_get_contents(APPPATH . 'controllers/Os.php');
        $this->assertStringNotContainsString("'assets/anexos/'", $os);
        $this->assertStringContainsString('armazenamentoPasta(', $os);

        $mine = (string) file_get_contents(APPPATH . 'controllers/Mine.php');
        $this->assertStringContainsString('$os->idClientes == $idCliente', $mine, 'Anexo só da OS do cliente logado.');

        $arquivos = (string) file_get_contents(APPPATH . 'controllers/Arquivos.php');
        $this->assertStringNotContainsString("'./assets/arquivos/", $arquivos);

        include APPPATH . 'config/permissions_map.php';
        $this->assertSame('vOs', $config['permissions_map']['Os']['anexo']);
    }

    /**
     * A API não carrega o controller Os (erro fatal "Class Os not found" no
     * upload): a lista de formatos vem do helper e os controllers que sobem
     * arquivo carregam as mensagens de upload em pt-br.
     */
    public function testUploadDaApiNaoDependeDeOutroControllerEUsaMensagensEmPortugues(): void
    {
        $formatos = armazenamentoExtensoesAnexo();
        $this->assertSame(['jpg', 'jpeg', 'png', 'gif', 'pdf', 'cdr', 'docx', 'txt'], $formatos);
        $this->assertSame(implode('|', $formatos), armazenamentoConfigUpload('/x', $formatos)['allowed_types']);

        $api = (string) file_get_contents(APPPATH . 'controllers/api/v1/OsController.php');
        $this->assertDoesNotMatchRegularExpression('/\bOs::/', $api);
        $this->assertStringContainsString('armazenamentoConfigUpload($directory, armazenamentoExtensoesAnexo())', $api);
        $this->assertStringNotContainsString('Os::', (string) file_get_contents(APPPATH . 'controllers/Os.php'));

        foreach (['api/v1/OsController.php', 'Os.php', 'Arquivos.php'] as $controller) {
            $codigo = (string) file_get_contents(APPPATH . 'controllers/' . $controller);
            $this->assertStringContainsString('armazenamentoReceber($this->upload', $codigo, $controller);
            $this->assertStringNotContainsString('$this->upload->do_upload(', $codigo, $controller);
        }
    }

    /**
     * Roda a CI_Lang e a CI_Upload reais (processo à parte) e lê a mensagem
     * de tipo não permitido. O lang->load('upload', 'pt-br') antes do envio
     * não vale: set_error() recarrega o upload_lang em inglês e sobrescreve.
     */
    public function testMensagemDeUploadRejeitadoSaiEmPortuguesComOHelper(): void
    {
        $executar = function (string $modo): array {
            $saida = [];
            exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(MAPOS_ROOT . '/tests/Support/upload_idioma.php') . ' ' . escapeshellarg(MAPOS_ROOT) . ' ' . $modo . ' 2>&1', $saida);

            return $saida;
        };

        $this->assertSame('The filetype you are attempting to upload is not allowed.', $executar('antigo')[0], 'A carga prévia em pt-br é desfeita pelo set_error().');

        $helper = $executar('armazenamentoReceber');
        $this->assertSame('O tipo de arquivo que você está tentando fazer upload não é permitido.', $helper[0]);
        $this->assertSame('idioma depois: english', $helper[1], 'O idioma global volta ao que era.');
    }

    public function testRollbackDaMigrationUsaAUrlPublicaDoEnv(): void
    {
        $migration = (string) file_get_contents(APPPATH . 'database/migrations/20261014030000_move_uploads_to_storage.php');
        $this->assertStringContainsString("\$_ENV['APP_BASEURL']", $migration);
    }
}
