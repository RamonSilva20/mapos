<?php

require_once APPPATH . 'libraries/Github_updater.php';

/**
 * Atualizador por releases com verificação de integridade (#2871). O GitHub é
 * simulado por uma função HTTP; o projeto, por uma pasta temporária.
 */
final class AtualizadorTest extends MaposTestCase
{
    private string $raiz;

    /** @var array<string, string> url => corpo (JSON, .sha256) ou conteúdo do zip */
    private array $respostas = [];

    /** @var list<string> */
    private array $chamadas = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->raiz = sys_get_temp_dir() . '/mapos-atualizador-' . bin2hex(random_bytes(4));
        foreach (['application/cache', 'application/config', 'application/controllers', 'assets', 'install'] as $pasta) {
            mkdir($this->raiz . '/' . $pasta, 0777, true);
        }
        file_put_contents($this->raiz . '/index.php', "<?php // antigo\n");
        chmod($this->raiz . '/index.php', 0755);
        file_put_contents($this->raiz . '/application/.env', "APP_SECRET=nao-mexer\n");
        file_put_contents($this->raiz . '/application/config/config.php', "<?php\n\$config['app_version'] = '4.55.1';\n");
        file_put_contents($this->raiz . '/application/controllers/Os.php', "<?php // os antigo\n");
        file_put_contents($this->raiz . '/install/index.php', "instalador antigo\n");
        $this->respostas = [];
        $this->chamadas = [];
    }

    protected function tearDown(): void
    {
        Github_updater::apagarPasta($this->raiz);

        parent::tearDown();
    }

    private function atualizador(string $versao = '4.55.1', bool $prereleases = false): Github_updater
    {
        return new Github_updater([
            'raiz' => $this->raiz,
            'usuario' => 'dono',
            'repositorio' => 'mapos',
            'versao_atual' => $versao,
            'prereleases' => $prereleases,
            'ignorados' => [],
            'http' => function (string $url, ?string $destino = null): string {
                $this->chamadas[] = $url;
                if (! isset($this->respostas[$url])) {
                    throw new RuntimeException('URL inesperada: ' . $url);
                }
                if ($destino !== null) {
                    file_put_contents($destino, $this->respostas[$url]);

                    return '';
                }

                return $this->respostas[$url];
            },
        ]);
    }

    /** @param array<string, string> $arquivos */
    private function zip(array $arquivos): string
    {
        $caminho = tempnam(sys_get_temp_dir(), 'mapos-zip');
        $zip = new ZipArchive();
        $zip->open($caminho, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($arquivos as $nome => $conteudo) {
            $zip->addFromString($nome, $conteudo);
        }
        $zip->close();
        $conteudo = (string) file_get_contents($caminho);
        unlink($caminho);

        return $conteudo;
    }

    private function url(string $tag, string $arquivo): string
    {
        return 'https://github.com/dono/mapos/releases/download/' . $tag . '/' . $arquivo;
    }

    /**
     * Publica uma release no GitHub simulado.
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function release(string $tag, string $zip, array $extra = [], ?string $sha = null): array
    {
        $this->respostas[$this->url($tag, $tag . '-release.zip')] = $zip;
        $this->respostas[$this->url($tag, $tag . '-release.zip.sha256')] = ($sha ?? hash('sha256', $zip)) . '  ' . $tag . '-release.zip';

        return $extra + [
            'tag_name' => $tag,
            'name' => 'Versão ' . $tag,
            'body' => "## O que mudou\n- Coisa nova",
            'draft' => false,
            'prerelease' => false,
            'published_at' => '2026-10-14T10:00:00Z',
            'assets' => [
                ['name' => $tag . '-release.zip', 'browser_download_url' => $this->url($tag, $tag . '-release.zip')],
                ['name' => $tag . '-release.zip.sha256', 'browser_download_url' => $this->url($tag, $tag . '-release.zip.sha256')],
            ],
        ];
    }

    /** @param list<array<string, mixed>> $releases */
    private function publicar(array $releases): void
    {
        $this->respostas['https://api.github.com/repos/dono/mapos/releases?per_page=20'] = json_encode($releases);
    }

    private function zipDaRelease(): string
    {
        return $this->zip([
            'index.php' => "<?php // novo\n",
            'application/controllers/Os.php' => "<?php // os novo\n",
            'application/controllers/Nova.php' => "<?php // nova\n",
            'application/config/config.php' => "<?php\n\$config['app_version'] = '4.0.0';\n",
            'application/.env' => "APP_SECRET=do-zip\n",
            'install/index.php' => "instalador do zip\n",
            'assets/uploads/x.png' => 'png',
        ]);
    }

    public function testEscolheAMaisNovaPelaVersaoIgnorandoRascunhoEPrerelease(): void
    {
        $zip = $this->zipDaRelease();
        $this->publicar([
            $this->release('v5.0.0-beta.2', $zip, ['prerelease' => true]),
            $this->release('v4.56.0', $zip),
            $this->release('v4.57.0', $zip, ['draft' => true]),
            $this->release('v4.55.2', $zip),
            $this->release('semversao', $zip),
        ]);

        $this->assertSame('v4.56.0', $this->atualizador()->ultimaRelease()['tag']);
        $this->assertSame('v5.0.0-beta.2', $this->atualizador('4.55.1', true)->ultimaRelease()['tag']);
    }

    public function testDevolveNullSemReleasesEErroComRespostaInesperada(): void
    {
        $this->publicar([]);
        $this->assertNull($this->atualizador()->ultimaRelease());
        $this->assertFalse($this->atualizador()->temAtualizacao(null));

        $this->respostas['https://api.github.com/repos/dono/mapos/releases?per_page=20'] = '{"message":"rate limit"}';
        $this->expectException(RuntimeException::class);
        $this->atualizador()->ultimaRelease();
    }

    public function testSoHaAtualizacaoSeAVersaoForMaiorQueAInstalada(): void
    {
        $this->publicar([$this->release('v4.55.1', $this->zipDaRelease())]);
        $this->assertFalse($this->atualizador('4.55.1')->temAtualizacao($this->atualizador()->ultimaRelease()));
        $this->assertTrue($this->atualizador('4.55.0')->temAtualizacao($this->atualizador()->ultimaRelease()));
        $this->assertFalse($this->atualizador('5.0.0')->temAtualizacao($this->atualizador()->ultimaRelease()));
        $this->assertTrue(version_compare('5.0.0', '5.0.0-beta.2', '>'), 'A estável vale mais que a beta.');
    }

    public function testAssetForaDoEnderecoOficialDaReleaseNaoEAceito(): void
    {
        $release = $this->release('v4.56.0', $this->zipDaRelease());
        $release['assets'][0]['browser_download_url'] = 'https://evil.example.com/v4.56.0-release.zip';
        $this->publicar([$release]);

        $info = $this->atualizador()->ultimaRelease();
        $this->assertSame('', $info['zip_url']);
        $this->expectExceptionMessage('não tem o zip e o arquivo .sha256');
        $this->atualizador()->baixarEConferir($info, $this->raiz . '/x.zip');
    }

    public function testHashQueNaoConfereDescartaOZip(): void
    {
        $this->publicar([$this->release('v4.56.0', $this->zipDaRelease(), [], str_repeat('a', 64))]);
        $destino = $this->raiz . '/baixado.zip';

        try {
            $this->atualizador()->baixarEConferir($this->atualizador()->ultimaRelease(), $destino);
            $this->fail('Devia recusar o zip.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('SHA256 do zip baixado não confere', $e->getMessage());
            $this->assertFileDoesNotExist($destino);
        }
    }

    public function testArquivoSha256DeOutroZipOuMalFormadoERecusado(): void
    {
        $zip = $this->zipDaRelease();
        $this->publicar([$this->release('v4.56.0', $zip)]);

        $this->respostas[$this->url('v4.56.0', 'v4.56.0-release.zip.sha256')] = hash('sha256', $zip) . '  outro.zip';
        try {
            $this->atualizador()->baixarEConferir($this->atualizador()->ultimaRelease(), $this->raiz . '/a.zip');
            $this->fail();
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('outro arquivo', $e->getMessage());
        }

        $this->respostas[$this->url('v4.56.0', 'v4.56.0-release.zip.sha256')] = '<html>404</html>';
        $this->expectExceptionMessage('formato inesperado');
        $this->atualizador()->baixarEConferir($this->atualizador()->ultimaRelease(), $this->raiz . '/b.zip');
    }

    public function testDigestDaApiQueDiscordaDoSha256PublicadoCancela(): void
    {
        $zip = $this->zipDaRelease();
        $release = $this->release('v4.56.0', $zip);
        $release['assets'][0]['digest'] = 'sha256:' . str_repeat('b', 64);
        $this->publicar([$release]);

        $this->expectExceptionMessage('não conferem');
        $this->atualizador()->baixarEConferir($this->atualizador()->ultimaRelease(), $this->raiz . '/a.zip');
    }

    public function testDigestDaApiIgualAoShaPassa(): void
    {
        $zip = $this->zipDaRelease();
        $release = $this->release('v4.56.0', $zip);
        $release['assets'][0]['digest'] = 'sha256:' . hash('sha256', $zip);
        $this->publicar([$release]);

        $this->atualizador()->baixarEConferir($this->atualizador()->ultimaRelease(), $this->raiz . '/a.zip');
        $this->assertFileExists($this->raiz . '/a.zip');
    }

    public static function caminhosInseguros(): array
    {
        return [
            'sobe pastas' => ['../fora.php'],
            'no meio' => ['application/../../fora.php'],
            'absoluto' => ['/etc/cron.d/x'],
            'barra invertida' => ['application\\..\\x.php'],
            'unidade do Windows' => ['C:/x.php'],
            'byte nulo' => ["a\0b.php"],
        ];
    }

    #[PHPUnit\Framework\Attributes\DataProvider('caminhosInseguros')]
    public function testCaminhoInseguroEReconhecido(string $nome): void
    {
        $this->assertTrue(Github_updater::caminhoInseguro($nome));
    }

    public function testCaminhosNormaisPassam(): void
    {
        foreach (['index.php', 'application/controllers/Os.php', 'assets/dist/app.css', 'a..b/c.php'] as $nome) {
            $this->assertFalse(Github_updater::caminhoInseguro($nome), $nome);
        }
    }

    public function testExtracaoRecusaZipComCaminhoQueSaiDaPasta(): void
    {
        $zip = $this->raiz . '/mau.zip';
        $arquivo = new ZipArchive();
        $arquivo->open($zip, ZipArchive::CREATE);
        $arquivo->addFromString('ok.php', 'ok');
        $arquivo->addFromString('../fora.php', 'mau');
        $arquivo->close();

        try {
            $this->atualizador()->extrair($zip, $this->raiz . '/extraido');
            $this->fail();
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('caminho inválido', $e->getMessage());
            $this->assertFileDoesNotExist($this->raiz . '/fora.php');
            $this->assertFileDoesNotExist(dirname($this->raiz) . '/fora.php');
        }
    }

    public function testExtracaoRecusaLinkSimbolicoNoZip(): void
    {
        $zip = $this->raiz . '/link.zip';
        $arquivo = new ZipArchive();
        $arquivo->open($zip, ZipArchive::CREATE);
        $arquivo->addFromString('atalho', '/etc/passwd');
        $arquivo->setExternalAttributesName('atalho', ZipArchive::OPSYS_UNIX, 0120777 << 16);
        $arquivo->close();

        $this->expectExceptionMessage('link simbólico');
        $this->atualizador()->extrair($zip, $this->raiz . '/extraido');
    }

    public function testExtracaoRecusaZipVazioEArquivoQueNaoEZip(): void
    {
        file_put_contents($this->raiz . '/lixo.zip', 'isto não é um zip');

        $this->expectExceptionMessage('Não foi possível abrir o zip');
        $this->atualizador()->extrair($this->raiz . '/lixo.zip', $this->raiz . '/extraido');
    }

    public function testNaoSobrescreveEnvUploadsNemInstall(): void
    {
        $atualizador = $this->atualizador();

        foreach (['application/.env', '.env', 'install/index.php', 'assets/uploads/x.png', 'assets/anexos/a/b.pdf', 'assets/arquivos/c.txt', 'assets/userImage/u.png', 'application/logs/log.php', 'application/cache/atualizacao/x'] as $caminho) {
            $this->assertTrue($atualizador->protegido($caminho), $caminho);
        }
        foreach (['index.php', 'application/config/config.php', 'assets/dist/app.css', 'assets/uploads2/x.png', 'installer.php', 'application/.env.example'] as $caminho) {
            $this->assertFalse($atualizador->protegido($caminho), $caminho);
        }
    }

    public function testAtualizacaoCompletaAplicaGravaAVersaoEGuardaBackup(): void
    {
        $this->publicar([$this->release('v4.56.0', $this->zipDaRelease())]);

        $resultado = $this->atualizador()->atualizar('v4.56.0');

        $this->assertSame('4.56.0', $resultado['versao']);
        $this->assertSame("<?php // novo\n", file_get_contents($this->raiz . '/index.php'));
        $this->assertSame("<?php // os novo\n", file_get_contents($this->raiz . '/application/controllers/Os.php'));
        $this->assertFileExists($this->raiz . '/application/controllers/Nova.php');
        $this->assertSame(0755, fileperms($this->raiz . '/index.php') & 0777, 'Quem já existia mantém o modo.');
        $this->assertStringContainsString("'4.56.0'", (string) file_get_contents($this->raiz . '/application/config/config.php'));
        // Intocados:
        $this->assertSame("APP_SECRET=nao-mexer\n", file_get_contents($this->raiz . '/application/.env'));
        $this->assertSame("instalador antigo\n", file_get_contents($this->raiz . '/install/index.php'));
        $this->assertFileDoesNotExist($this->raiz . '/assets/uploads/x.png');
        // Backup do que foi trocado, e pasta de trabalho limpa:
        $this->assertSame("<?php // antigo\n", file_get_contents($this->raiz . '/application/cache/atualizacao/' . $resultado['backup'] . '/index.php'));
        $this->assertSame([], glob($this->raiz . '/application/cache/atualizacao/*/release.zip') ?: []);
        $this->assertFileDoesNotExist($this->raiz . '/index.php.atualizando');
    }

    public function testFalhaNoMeioRestauraTudo(): void
    {
        $this->publicar([$this->release('v4.56.0', $this->zipDaRelease())]);
        // Um arquivo que o zip quer criar dentro de uma "pasta" que é um arquivo: a cópia falha.
        file_put_contents($this->raiz . '/application/controllers/Nova.php', 'era arquivo');
        mkdir($this->raiz . '/application/controllers/Nova.php.atualizando');

        try {
            $this->atualizador()->atualizar('v4.56.0');
            $this->fail('Devia falhar.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('restaurados', $e->getMessage());
        }

        $this->assertSame("<?php // antigo\n", file_get_contents($this->raiz . '/index.php'));
        $this->assertSame("<?php // os antigo\n", file_get_contents($this->raiz . '/application/controllers/Os.php'));
        $this->assertStringContainsString("'4.55.1'", (string) file_get_contents($this->raiz . '/application/config/config.php'));
    }

    public function testSoAtualizaParaAUltimaReleaseECancelaComHashRuim(): void
    {
        $this->publicar([$this->release('v4.56.0', $this->zipDaRelease())]);

        try {
            $this->atualizador()->atualizar('v4.50.0');
            $this->fail();
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('não é mais a versão mais recente', $e->getMessage());
        }

        $this->publicar([$this->release('v4.56.0', $this->zipDaRelease(), [], str_repeat('0', 64))]);
        try {
            $this->atualizador()->atualizar('v4.56.0');
            $this->fail();
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('SHA256', $e->getMessage());
        }
        $this->assertSame("<?php // antigo\n", file_get_contents($this->raiz . '/index.php'), 'Nada foi alterado.');
        $this->assertSame([], glob($this->raiz . '/application/cache/atualizacao/*/release.zip') ?: []);
    }

    public function testNaoAtualizaQuemJaEstaNaVersao(): void
    {
        $this->publicar([$this->release('v4.55.1', $this->zipDaRelease())]);

        $this->expectExceptionMessage('já está na versão');
        $this->atualizador('4.55.1')->atualizar('v4.55.1');
    }

    public function testUmaAtualizacaoPorVez(): void
    {
        $this->publicar([$this->release('v4.56.0', $this->zipDaRelease())]);
        mkdir($this->raiz . '/application/cache/atualizacao', 0777, true);
        $outra = fopen($this->raiz . '/application/cache/atualizacao/atualizacao.lock', 'c');
        flock($outra, LOCK_EX);

        try {
            $this->atualizador()->atualizar('v4.56.0');
            $this->fail();
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('em andamento', $e->getMessage());
        } finally {
            fclose($outra);
        }
    }

    public function testHttpSoAceitaHttps(): void
    {
        $this->expectExceptionMessage('Endereço de atualização inválido');
        (new Github_updater(['raiz' => $this->raiz, 'usuario' => 'dono', 'repositorio' => 'mapos', 'versao_atual' => '1.0.0', 'prereleases' => false, 'ignorados' => []]))->requisitar('http://github.com/x');
    }

    public function testTelaEControllerUsamAReleaseNaoOBranch(): void
    {
        $biblioteca = (string) file_get_contents(APPPATH . 'libraries/Github_updater.php');
        $this->assertStringNotContainsString('zipball', $biblioteca);
        $this->assertStringNotContainsString('/branches', $biblioteca);
        $this->assertStringNotContainsString('shell_exec', $biblioteca);
        $this->assertStringNotContainsString('current_commit', (string) file_get_contents(APPPATH . 'config/github_updater.php'));

        $controller = (string) file_get_contents(APPPATH . 'controllers/Mapos.php');
        $this->assertStringContainsString("github_updater->atualizar((string) \$this->input->post('versao'))", $controller);

        $tela = (string) file_get_contents(APPPATH . 'views/mapos/atualizacao.php');
        $this->assertStringContainsString("e(mb_strimwidth(\$notas", $tela, 'O changelog sai escapado.');
        $this->assertStringContainsString('<pre class=', $tela, 'O pre guarda as quebras de linha do changelog (o hook de compressão do HTML preserva só pre, textarea e script).');
        $this->assertStringContainsString('O que mudou', $tela);

        $configurar = (string) file_get_contents(APPPATH . 'views/mapos/configurar.php');
        $this->assertStringNotContainsString('são apagadas', $configurar);
    }
}
