<?php

require_once APPPATH . 'database/migrations/20261014030000_move_uploads_to_storage.php';

/**
 * move_uploads_to_storage (#2872, #2858): a migration executa de verdade sobre
 * um SQLite e arquivos reais. Sem STORAGE_PATH gravável ou com arquivo que não
 * se move ela falha com mensagem (e não conclui); arquivo ausente só vai para o
 * log; rodar de novo termina o que faltou.
 */
final class MigracaoUploadsTest extends MaposTestCase
{
    private string $raiz;

    private string $marca;

    private string $anexosAntigos;

    private string $arquivosAntigos;

    private ?string $envAnterior;

    private ?string $baseUrlAnterior;

    protected function setUp(): void
    {
        parent::setUp();

        $this->marca = '_t2858-' . bin2hex(random_bytes(4));
        $this->raiz = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mapos-mig-' . bin2hex(random_bytes(4));
        $this->anexosAntigos = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR . $this->marca;
        $this->arquivosAntigos = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . $this->marca;

        $this->envAnterior = $_ENV['STORAGE_PATH'] ?? null;
        $this->baseUrlAnterior = $_ENV['APP_BASEURL'] ?? null;
        $_ENV['STORAGE_PATH'] = $this->raiz;
        $_ENV['APP_BASEURL'] = 'https://mapos.test/';

        $this->db->query('CREATE TABLE anexos (idAnexos INTEGER PRIMARY KEY AUTOINCREMENT, anexo TEXT, thumb TEXT, url TEXT, path TEXT, os_id INTEGER)');
        $this->db->query('CREATE TABLE documentos (idDocumentos INTEGER PRIMARY KEY AUTOINCREMENT, documento TEXT, url TEXT, path TEXT)');
    }

    protected function tearDown(): void
    {
        $this->apagar($this->raiz);
        $this->apagar($this->anexosAntigos);
        $this->apagar($this->arquivosAntigos);

        if ($this->envAnterior === null) {
            unset($_ENV['STORAGE_PATH']);
        } else {
            $_ENV['STORAGE_PATH'] = $this->envAnterior;
        }
        if ($this->baseUrlAnterior === null) {
            unset($_ENV['APP_BASEURL']);
        } else {
            $_ENV['APP_BASEURL'] = $this->baseUrlAnterior;
        }

        parent::tearDown();
    }

    private function apagar(string $caminho): void
    {
        if (is_file($caminho) || is_link($caminho)) {
            @chmod(dirname($caminho), 0755);
            unlink($caminho);

            return;
        }
        if (is_dir($caminho)) {
            @chmod($caminho, 0755);
            foreach (scandir($caminho) as $item) {
                if ($item !== '.' && $item !== '..') {
                    $this->apagar($caminho . DIRECTORY_SEPARATOR . $item);
                }
            }
            rmdir($caminho);
        }
    }

    private function migration(): object
    {
        $load = new class() {
            public function __call($nome, $args)
            {
                return null;
            }
        };

        return new class($this->db, $load) extends Migration_move_uploads_to_storage {
            public $db;

            public $load;

            public $config;

            public function __construct($db, $load)
            {
                $this->db = $db;
                $this->load = $load;
                $this->config = new class() {
                    public function item($nome)
                    {
                        return 'http://localhost/';
                    }
                };
            }
        };
    }

    /** Anexo no formato da v4: arquivo e miniatura em assets/anexos, path absoluto, url pública. */
    private function criarAnexoAntigo(string $os, string $arquivo, string $conteudo = 'conteudo', bool $comArquivo = true): int
    {
        $pasta = $this->anexosAntigos . DIRECTORY_SEPARATOR . $os;
        mkdir($pasta . DIRECTORY_SEPARATOR . 'thumbs', 0755, true);

        if ($comArquivo) {
            file_put_contents($pasta . DIRECTORY_SEPARATOR . $arquivo, $conteudo);
            file_put_contents($pasta . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . 'thumb_' . $arquivo, 'mini');
        }

        $this->db->insert('anexos', [
            'anexo' => $arquivo,
            'thumb' => 'thumb_' . $arquivo,
            'url' => 'http://antigo/assets/anexos/' . $this->marca . '/' . $os . '/',
            'path' => $pasta,
            'os_id' => 1,
        ]);

        return (int) $this->db->insert_id();
    }

    private function criarDocumentoAntigo(string $dia, string $arquivo): int
    {
        mkdir($this->arquivosAntigos . DIRECTORY_SEPARATOR . $dia, 0755, true);
        file_put_contents($this->arquivosAntigos . DIRECTORY_SEPARATOR . $dia . DIRECTORY_SEPARATOR . $arquivo, 'doc');

        $this->db->insert('documentos', [
            'documento' => $arquivo,
            'url' => 'http://antigo/assets/arquivos/' . $this->marca . '/' . $dia . '/' . $arquivo,
            'path' => $this->arquivosAntigos . DIRECTORY_SEPARATOR . $dia . DIRECTORY_SEPARATOR . $arquivo,
        ]);

        return (int) $this->db->insert_id();
    }

    public function testMoveOsArquivosParaOArmazenamentoEGravaCaminhoRelativo(): void
    {
        $idAnexo = $this->criarAnexoAntigo('OS-1', 'a1b2.png');
        $idDocumento = $this->criarDocumentoAntigo('10-10-2026', 'doc.pdf');

        $this->migration()->up();

        $anexo = $this->db->where('idAnexos', $idAnexo)->get('anexos')->row();
        $this->assertSame('anexos/' . $this->marca . '/OS-1', $anexo->path);
        $this->assertSame('', $anexo->url);
        $this->assertFileExists($this->raiz . '/anexos/' . $this->marca . '/OS-1/a1b2.png');
        $this->assertFileExists($this->raiz . '/anexos/' . $this->marca . '/OS-1/thumbs/thumb_a1b2.png');
        $this->assertFileDoesNotExist($this->anexosAntigos . '/OS-1/a1b2.png', 'O original sai de assets/.');

        $documento = $this->db->where('idDocumentos', $idDocumento)->get('documentos')->row();
        $this->assertSame('arquivos/' . $this->marca . '/10-10-2026/doc.pdf', $documento->path);
        $this->assertFileExists($this->raiz . '/arquivos/' . $this->marca . '/10-10-2026/doc.pdf');
    }

    public function testRodarDeNovoDepoisDeConcluirNaoFazNada(): void
    {
        $this->criarAnexoAntigo('OS-1', 'a1b2.png');
        $this->migration()->up();
        $depois = $this->db->get('anexos')->result_array();

        $this->migration()->up();

        $this->assertSame($depois, $this->db->get('anexos')->result_array());
    }

    public function testPastaDeArmazenamentoQueNaoPodeSerCriadaInterrompeSemMoverNada(): void
    {
        // STORAGE_PATH dentro de um arquivo comum: nem existe nem dá para criar.
        file_put_contents($this->raiz = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mapos-mig-arquivo-' . bin2hex(random_bytes(4)), 'x');
        $_ENV['STORAGE_PATH'] = $this->raiz . DIRECTORY_SEPARATOR . 'dentro';

        $idAnexo = $this->criarAnexoAntigo('OS-1', 'a1b2.png');

        try {
            $this->migration()->up();
            $this->fail('A migration devia parar quando o STORAGE_PATH não existe nem pode ser criado.');
        } catch (RuntimeException $erro) {
            $this->assertStringContainsString('não existe', $erro->getMessage());
            $this->assertStringContainsString('dentro', $erro->getMessage());
        }

        $this->assertFileExists($this->anexosAntigos . '/OS-1/a1b2.png', 'Nada foi movido.');
        $this->assertSame($this->anexosAntigos . DIRECTORY_SEPARATOR . 'OS-1', $this->db->where('idAnexos', $idAnexo)->get('anexos')->row()->path);
    }

    public function testPastaSemPermissaoDeEscritaInterrompeComMensagemClara(): void
    {
        if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
            $this->markTestSkipped('Como root o PHP grava em qualquer pasta.');
        }

        mkdir($this->raiz, 0755, true);
        chmod($this->raiz, 0555);
        $this->criarAnexoAntigo('OS-1', 'a1b2.png');

        try {
            $this->migration()->up();
            $this->fail('A migration devia parar quando o PHP não grava no STORAGE_PATH.');
        } catch (RuntimeException $erro) {
            $this->assertStringContainsString('não consegue gravar', $erro->getMessage());
            $this->assertStringContainsString($this->raiz, $erro->getMessage());
        }

        $this->assertFileExists($this->anexosAntigos . '/OS-1/a1b2.png');
    }

    public function testSemArquivoParaMoverNaoExigeOArmazenamento(): void
    {
        $_ENV['STORAGE_PATH'] = '/proc/mapos-inexistente/storage';

        $this->migration()->up();

        $this->addToAssertionCount(1);
    }

    public function testRegistroSemArquivoNoDiscoNaoImpedeAMigration(): void
    {
        $orfao = $this->criarAnexoAntigo('OS-1', 'sumiu.png', comArquivo: false);
        $normal = $this->criarAnexoAntigo('OS-2', 'ok.png');

        $this->migration()->up();

        $registro = $this->db->where('idAnexos', $orfao)->get('anexos')->row();
        $this->assertSame($this->anexosAntigos . DIRECTORY_SEPARATOR . 'OS-1', $registro->path, 'O órfão fica como estava.');
        $this->assertSame('anexos/' . $this->marca . '/OS-2', $this->db->where('idAnexos', $normal)->get('anexos')->row()->path);
    }

    public function testArquivoQueNaoSeMoveFazAMigrationFalharMasOsDemaisSeMovem(): void
    {
        $conflito = $this->criarAnexoAntigo('OS-1', 'igual.png', 'novo');
        $normal = $this->criarAnexoAntigo('OS-2', 'ok.png');

        // O destino já tem outro arquivo com o mesmo nome: não se sobrescreve.
        mkdir($this->raiz . '/anexos/' . $this->marca . '/OS-1/thumbs', 0750, true);
        file_put_contents($this->raiz . '/anexos/' . $this->marca . '/OS-1/igual.png', 'diferente');

        try {
            $this->migration()->up();
            $this->fail('A migration devia falhar com o arquivo que não se move.');
        } catch (RuntimeException $erro) {
            $this->assertStringContainsString('1 arquivo(s)', $erro->getMessage());
            $this->assertStringContainsString('igual.png', $erro->getMessage());
        }

        $this->assertSame($this->anexosAntigos . DIRECTORY_SEPARATOR . 'OS-1', $this->db->where('idAnexos', $conflito)->get('anexos')->row()->path);
        $this->assertSame('anexos/' . $this->marca . '/OS-2', $this->db->where('idAnexos', $normal)->get('anexos')->row()->path, 'O que deu para mover ficou movido.');

        // Corrigida a causa, rodar de novo termina o que faltou.
        unlink($this->raiz . '/anexos/' . $this->marca . '/OS-1/igual.png');
        $this->migration()->up();

        $this->assertSame('anexos/' . $this->marca . '/OS-1', $this->db->where('idAnexos', $conflito)->get('anexos')->row()->path);
        $this->assertSame('novo', file_get_contents($this->raiz . '/anexos/' . $this->marca . '/OS-1/igual.png'));
    }

    public function testSobraDeExecucaoInterrompidaIdenticaELimpaSemFalhar(): void
    {
        $idAnexo = $this->criarAnexoAntigo('OS-1', 'copiado.png', 'mesmo');

        // Cópia feita, original não apagado.
        mkdir($this->raiz . '/anexos/' . $this->marca . '/OS-1/thumbs', 0750, true);
        file_put_contents($this->raiz . '/anexos/' . $this->marca . '/OS-1/copiado.png', 'mesmo');

        $this->migration()->up();

        $this->assertSame('anexos/' . $this->marca . '/OS-1', $this->db->where('idAnexos', $idAnexo)->get('anexos')->row()->path);
        $this->assertFileDoesNotExist($this->anexosAntigos . '/OS-1/copiado.png');
    }

    public function testRollbackDevolveOsArquivosEGravaCaminhoAbsolutoDaPastaDaVersaoAtual(): void
    {
        $idAnexo = $this->criarAnexoAntigo('OS-1', 'a1b2.png');
        $this->migration()->up();

        $this->migration()->down();

        $anexo = $this->db->where('idAnexos', $idAnexo)->get('anexos')->row();
        $this->assertSame($this->anexosAntigos . DIRECTORY_SEPARATOR . 'OS-1', $anexo->path, 'O down grava a pasta da instalação onde o comando roda (docs/armazenamento-de-arquivos.md).');
        $this->assertSame('https://mapos.test/assets/anexos/' . $this->marca . '/OS-1', $anexo->url);
        $this->assertFileExists($this->anexosAntigos . '/OS-1/a1b2.png');
    }
}
