<?php

require_once APPPATH . 'models/Arquivos_model.php';

if (! function_exists('site_url')) {
    function site_url($uri = '')
    {
        return 'http://mapos.test/index.php/' . ltrim((string) $uri, '/');
    }
}

/**
 * Arquivos migrados (#2845): tamanho na tela, listagem filtrada por texto e
 * período, o controller dentro dos padrões e as views novas.
 */
final class ArquivosTest extends MaposTestCase
{
    public function testTamanhoDeArquivoEmKbEMb(): void
    {
        $this->assertSame('0,08 KB', tamanhoDeArquivo('0.08'));
        $this->assertSame('9,50 KB', tamanhoDeArquivo(9.5));
        $this->assertSame('512 KB', tamanhoDeArquivo('512.3'));
        $this->assertSame('1,5 MB', tamanhoDeArquivo(1536));
        $this->assertSame('0,00 KB', tamanhoDeArquivo(null));
        $this->assertSame('0,00 KB', tamanhoDeArquivo(-4));
    }

    private function model(): Arquivos_model
    {
        $this->db->query('CREATE TABLE documentos (idDocumentos INTEGER PRIMARY KEY AUTOINCREMENT, documento TEXT, descricao TEXT, file TEXT, path TEXT, url TEXT, cadastro TEXT, tipo TEXT, tamanho TEXT)');
        foreach ([['Contrato', 'Cliente Ana', '2026-09-10'], ['Foto do balcão', 'Loja', '2026-10-05'], ['Nota fiscal', 'contrato de ana', '2026-10-20']] as [$nome, $descricao, $data]) {
            $this->db->query('INSERT INTO documentos (documento, descricao, cadastro) VALUES (?, ?, ?)', [$nome, $descricao, $data]);
        }

        $model = $this->makeInstance(Arquivos_model::class);
        $this->setPrivateProperty($model, 'db', $this->db);

        return $model;
    }

    public function testListagemFiltraPorTextoEPeriodo(): void
    {
        $model = $this->model();

        $this->assertSame(3, $model->contar([]));
        $this->assertSame(['Nota fiscal', 'Contrato'], array_column($model->listar(['pesquisa' => 'contrato'], 10, 0), 'documento'), 'Nome ou descrição, do mais novo para o mais antigo.');
        $this->assertSame(2, $model->contar(['de' => '2026-10-01']));
        $this->assertSame(['Foto do balcão'], array_column($model->listar(['de' => '2026-10-01', 'ate' => '2026-10-10'], 10, 0), 'documento'));
        $this->assertSame(1, $model->contar(['ate' => '2026-09-30']));
        $this->assertSame(['Foto do balcão'], array_column($model->listar([], 1, 1), 'documento'));
        $this->assertSame(0, $model->contar(['pesquisa' => "x' OR '1'='1"]));
    }

    public function testControllerSegueOsPadroes(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Arquivos.php');

        $this->assertStringContainsString("\$this->validarFormulario('arquivos')", $codigo);
        $this->assertStringContainsString("'idDocumentos', (int) \$arquivo->idDocumentos", $codigo);
        $this->assertStringNotContainsString("post('idDocumentos')", $codigo, 'O id editado vem da URL.');
        $this->assertStringContainsString('dataIsoParaYmd', $codigo);
        // O arquivo só é gravado depois da validação: o envio vem depois do if de erros vazios.
        $this->assertLessThan(strpos($codigo, '$this->do_upload()'), strpos($codigo, "validarFormulario('arquivos')"));
        $this->assertStringContainsString('armazenamentoLocalizar((string) $file->path', $codigo, 'Excluir e baixar conferem se o arquivo existe (e está na pasta de armazenamento).');

        $mapa = (string) file_get_contents(APPPATH . 'config/permissions_map.php');
        preg_match("/'Arquivos' => \\[.*?\\n    \\],/s", $mapa, $bloco);
        $this->assertNotEmpty($bloco);
        $this->assertStringNotContainsString('do_upload', $bloco[0]);
    }

    private function renderizar(string $view, array $variaveis): string
    {
        $contexto = new class() {
            public object $security;

            public function __construct()
            {
                $this->security = new class() {
                    public function get_csrf_token_name()
                    {
                        return 'MAPOS_TOKEN';
                    }

                    public function get_csrf_hash()
                    {
                        return 'hash-csrf';
                    }
                };
            }

            public function mostrar(string $__caminho, array $__variaveis): string
            {
                return (function () use ($__caminho, $__variaveis) {
                    extract($__variaveis);
                    ob_start();

                    try {
                        include $__caminho;
                    } catch (Throwable $erro) {
                        ob_end_clean();

                        throw $erro;
                    }

                    return (string) ob_get_clean();
                })();
            }
        };

        return $contexto->mostrar(APPPATH . 'views/' . $view . '.php', $variaveis);
    }

    public function testListagemEscapaOsDadosEMostraAcoesPelaPermissao(): void
    {
        $xss = '<img src=x onerror=alert(1)>';
        $imagem = (object) ['idDocumentos' => 5, 'documento' => 'Foto ' . $xss, 'descricao' => 'Desc ' . $xss, 'url' => 'javascript:alert(1)', 'cadastro' => '2026-10-05', 'tipo' => '.png', 'tamanho' => '0.08'];
        $texto = (object) ['idDocumentos' => 6, 'documento' => 'Notas', 'descricao' => '', 'url' => 'http://mapos.test/a.txt', 'cadastro' => '2026-10-06', 'tipo' => '.txt', 'tamanho' => '2048'];
        $variaveis = ['results' => [$imagem, $texto], 'filtros' => [], 'total' => 2, 'paginacao' => paginacaoProps(['base_url' => 'http://mapos.test/index.php/arquivos/gerenciar', 'total_rows' => 2])];

        $html = $this->renderizar('arquivos/arquivos', $variaveis + ['pode' => ['adicionar' => true, 'editar' => true, 'excluir' => true]]);

        $this->assertStringNotContainsString($xss, $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('href="http://mapos.test/index.php/arquivos/download/5"', $html);
        $this->assertStringContainsString('href="http://mapos.test/index.php/arquivos/editar/6"', $html);
        $this->assertStringContainsString('data-modal-abrir="excluir-arquivo"', $html);
        $this->assertStringContainsString('0,08 KB', $html);
        $this->assertStringContainsString('2,0 MB', $html);
        $this->assertSame(0, preg_match('/<script(?![^>]*\bsrc=)[^>]*>/', $html));

        $leitura = $this->renderizar('arquivos/arquivos', $variaveis + ['pode' => ['adicionar' => false, 'editar' => false, 'excluir' => false]]);
        $this->assertStringNotContainsString('arquivos/editar', $leitura);
        $this->assertStringNotContainsString('excluir-arquivo', $leitura);
        $this->assertStringContainsString('arquivos/download/6', $leitura, 'Baixar continua com a permissão de ver.');

        $filtrado = $this->renderizar('arquivos/arquivos', ['results' => [], 'filtros' => ['de' => '2026-10-01'], 'total' => 0, 'paginacao' => paginacaoProps(['base_url' => 'http://mapos.test/index.php/arquivos/gerenciar', 'total_rows' => 0]), 'pode' => ['adicionar' => true, 'editar' => true, 'excluir' => true]]);
        $this->assertStringContainsString('Nenhum arquivo encontrado', $filtrado);
        $this->assertMatchesRegularExpression('/<input[^>]*name="de"[^>]*value="2026-10-01"/', $filtrado);
    }

    public function testFormularioDeEnvioTemMultipartEOErroNoCampoDoArquivo(): void
    {
        $html = $this->renderizar('arquivos/formulario', [
            'arquivo' => null,
            'valores' => ['nome' => 'Contrato "A"', 'descricao' => '', 'data' => '2026-10-13'],
            'erros' => ['userfile' => 'Escolha o arquivo.'],
        ]);

        $this->assertStringContainsString('action="http://mapos.test/index.php/arquivos/adicionar"', $html);
        $this->assertStringContainsString('enctype="multipart/form-data"', $html);
        $this->assertStringContainsString('name="MAPOS_TOKEN" value="hash-csrf"', $html);
        $this->assertStringContainsString('value="Contrato &quot;A&quot;"', $html);
        $this->assertMatchesRegularExpression('/<input type="file"[^>]*name="userfile"[^>]*required/', $html);
        $this->assertStringContainsString('Escolha o arquivo.', $html);
        $this->assertStringContainsString('Confira o campo destacado.', $html);
    }

    public function testFormularioDeEdicaoNaoEnviaArquivo(): void
    {
        $html = $this->renderizar('arquivos/formulario', [
            'arquivo' => (object) ['idDocumentos' => 9, 'documento' => 'Foto', 'file' => 'abc.png', 'tamanho' => '120'],
            'valores' => ['nome' => 'Foto', 'descricao' => 'x', 'data' => '2026-10-05'],
            'erros' => [],
        ]);

        $this->assertStringContainsString('action="http://mapos.test/index.php/arquivos/editar/9"', $html);
        $this->assertStringNotContainsString('multipart', $html);
        $this->assertStringNotContainsString('type="file"', $html);
        $this->assertStringContainsString('abc.png · 120 KB', $html);
        $this->assertStringContainsString('href="http://mapos.test/index.php/arquivos/download/9"', $html);
    }
}
