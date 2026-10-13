<?php

require_once APPPATH . 'models/Garantias_model.php';

if (! function_exists('site_url')) {
    function site_url($uri = '')
    {
        return 'http://mapos.test/index.php/' . ltrim((string) $uri, '/');
    }
}

/**
 * Termos de garantia migrados (#2845): listagem filtrada, o controller dentro
 * dos padrões e as views novas.
 */
final class GarantiasTest extends MaposTestCase
{
    private function criarBanco(): void
    {
        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, telefone TEXT, email TEXT)');
        $this->db->query('CREATE TABLE garantias (idGarantias INTEGER PRIMARY KEY AUTOINCREMENT, dataGarantia TEXT, refGarantia TEXT, textoGarantia TEXT, usuarios_id INTEGER)');
        $this->db->query("INSERT INTO usuarios (nome) VALUES ('Técnico')");
        foreach ([['30 dias', 'Cobre defeitos de fábrica'], ['90 dias', 'Cobre a mão de obra'], ['Sem termo', 'Sem garantia, vendido no estado']] as [$ref, $texto]) {
            $this->db->query("INSERT INTO garantias (dataGarantia, refGarantia, textoGarantia, usuarios_id) VALUES ('2026-10-01', ?, ?, 1)", [$ref, $texto]);
        }
    }

    private function model(): Garantias_model
    {
        $model = $this->makeInstance(Garantias_model::class);
        $this->setPrivateProperty($model, 'db', $this->db);

        return $model;
    }

    public function testListagemFiltraPorReferenciaOuTexto(): void
    {
        $this->criarBanco();
        $model = $this->model();

        $this->assertSame(3, $model->contar([]));
        $this->assertSame(1, $model->contar(['pesquisa' => '90 dias']));
        $this->assertSame(2, $model->contar(['pesquisa' => 'cobre']));
        $this->assertSame(['90 dias', '30 dias'], array_column($model->listar(['pesquisa' => 'cobre'], 10, 0), 'refGarantia'), 'Do mais novo para o mais antigo.');
        $this->assertSame('Técnico', $model->listar([], 1, 0)[0]->nome);
        $this->assertSame(['90 dias'], array_column($model->listar([], 1, 1), 'refGarantia'));
    }

    public function testOsVinculadasContaSoAsOsDoTermo(): void
    {
        $this->criarBanco();
        $this->db->query('CREATE TABLE os (idOs INTEGER PRIMARY KEY AUTOINCREMENT, garantias_id INTEGER)');
        $this->db->query('INSERT INTO os (garantias_id) VALUES (2), (2), (1), (NULL)');
        $model = $this->model();

        $this->assertSame(2, $model->osVinculadas(2));
        $this->assertSame(1, $model->osVinculadas(1));
        $this->assertSame(0, $model->osVinculadas(3));
    }

    public function testExcluirConferePeloModelEmVezDeConfiarNoBanco(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Garantias.php');
        preg_match('/function excluir\(\).*?\n    }\n/s', $codigo, $trecho);

        $this->assertNotEmpty($trecho);
        $this->assertLessThan(strpos($trecho[0], "->delete('garantias'"), strpos($trecho[0], 'osVinculadas('), 'A checagem vem antes do delete.');
    }

    public function testBuscaTrataCaracteresEspeciaisComoTexto(): void
    {
        $this->criarBanco();
        $model = $this->model();

        $this->assertSame(0, $model->contar(['pesquisa' => "x' OR '1'='1"]));
        $this->assertSame(0, $model->contar(['pesquisa' => '%']), 'O % é escapado pelo like().');
    }

    public function testControllerSegueOsPadroes(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Garantias.php');

        $this->assertStringContainsString("\$this->validarFormulario('garantias')", $codigo);
        $this->assertStringContainsString("osTextoParaGravar((string) \$this->input->post('textoGarantia'))", $codigo);
        $this->assertStringContainsString("'idGarantias', \$id", $codigo);
        $this->assertStringNotContainsString("post('idGarantias')", $codigo, 'O id editado vem da URL.');
        $this->assertStringNotContainsString('autoComplete', $codigo);

        $mapa = (string) file_get_contents(APPPATH . 'config/permissions_map.php');
        preg_match("/'Garantias' => \\[.*?\\n    \\],/s", $mapa, $bloco);
        $this->assertNotEmpty($bloco);
        $this->assertStringNotContainsString('autoComplete', $bloco[0], 'Os autocompletes sem uso saíram do mapa.');
    }

    /**
     * Renderiza uma view como o CI_Loader: variáveis compartilhadas e
     * $this->security para o token CSRF.
     */
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

            public function mostrar(string $arquivo, array $variaveis): string
            {
                return (function () use ($arquivo, $variaveis) {
                    extract($variaveis);
                    ob_start();

                    try {
                        include $arquivo;
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

    public function testListagemMostraAcoesPelaPermissaoEEscapaOsDados(): void
    {
        $xss = '<img src=x onerror=alert(1)>';
        $termo = (object) ['idGarantias' => 4, 'refGarantia' => $xss, 'textoGarantia' => '<p>Texto <b>do</b> termo</p>', 'nome' => 'Técnico ' . $xss, 'dataGarantia' => '2026-10-01'];
        $variaveis = ['results' => [$termo], 'filtros' => [], 'total' => 1, 'paginacao' => paginacaoProps(['base_url' => 'http://mapos.test/index.php/garantias/gerenciar', 'total_rows' => 1])];

        $completo = $this->renderizar('garantias/garantias', $variaveis + ['pode' => ['adicionar' => true, 'editar' => true, 'excluir' => true]]);
        $this->assertStringNotContainsString($xss, $completo);
        $this->assertStringContainsString('href="http://mapos.test/index.php/garantias/visualizar/4"', $completo);
        $this->assertStringContainsString('href="http://mapos.test/index.php/garantias/editar/4"', $completo);
        $this->assertStringContainsString('href="http://mapos.test/index.php/garantias/imprimir/4"', $completo);
        $this->assertStringContainsString('data-modal-abrir="excluir-garantia"', $completo);
        $this->assertStringContainsString('Texto do termo', $completo, 'O texto curto sai sem tags.');
        $this->assertSame(0, preg_match('/<script(?![^>]*\bsrc=)[^>]*>/', $completo));

        $leitura = $this->renderizar('garantias/garantias', $variaveis + ['pode' => ['adicionar' => false, 'editar' => false, 'excluir' => false]]);
        $this->assertStringNotContainsString('garantias/editar/4', $leitura);
        $this->assertStringNotContainsString('excluir-garantia', $leitura);

        $vazio = $this->renderizar('garantias/garantias', ['results' => [], 'filtros' => ['pesquisa' => 'x'], 'total' => 0, 'paginacao' => paginacaoProps(['base_url' => 'http://mapos.test/index.php/garantias/gerenciar', 'total_rows' => 0]), 'pode' => ['adicionar' => true, 'editar' => true, 'excluir' => true]]);
        $this->assertStringContainsString('Nenhum termo encontrado', $vazio);
    }

    public function testFormularioMantemValoresEMostraErroNoCampo(): void
    {
        $html = $this->renderizar('garantias/formulario', [
            'garantia' => null,
            'valores' => ['refGarantia' => 'Ref "A"', 'textoGarantia' => ''],
            'erros' => ['textoGarantia' => 'O campo Termo de garantia é obrigatório.'],
        ]);

        $this->assertStringContainsString('action="http://mapos.test/index.php/garantias/adicionar"', $html);
        $this->assertStringContainsString('data-module="formulario/padrao"', $html);
        $this->assertStringContainsString('name="MAPOS_TOKEN" value="hash-csrf"', $html);
        $this->assertStringContainsString('value="Ref &quot;A&quot;"', $html);
        $this->assertStringContainsString('O campo Termo de garantia é obrigatório.', $html);
        $this->assertMatchesRegularExpression('/<textarea[^>]*name="textoGarantia"[^>]*required/', $html);
        $this->assertStringNotContainsString('name="idGarantias"', $html);

        $edicao = $this->renderizar('garantias/formulario', [
            'garantia' => (object) ['idGarantias' => 7, 'dataGarantia' => '2026-10-01', 'nome' => 'Técnico'],
            'valores' => ['refGarantia' => '90 dias', 'textoGarantia' => "Linha 1\n\nLinha 2"],
            'erros' => [],
        ]);
        $this->assertStringContainsString('action="http://mapos.test/index.php/garantias/editar/7"', $edicao);
        $this->assertStringContainsString('Termo #7', $edicao);
        $this->assertStringContainsString("Linha 1\n\nLinha 2</textarea>", $edicao);
    }

    public function testFichaMostraOTextoFormatadoSemScript(): void
    {
        $html = $this->renderizar('garantias/visualizar', [
            'result' => (object) ['idGarantias' => 7, 'refGarantia' => '90 dias', 'dataGarantia' => '2026-10-01', 'nome' => 'Técnico', 'telefone' => '', 'email' => 'a@x.com',
                'textoGarantia' => 'Primeira<br>linha <b>forte</b><script>alert(1)</script>'],
            'pode' => ['editar' => true, 'excluir' => false],
        ]);

        $this->assertStringContainsString('<b>forte</b>', $html);
        $this->assertStringNotContainsString('alert(1)', $html);
        $this->assertStringContainsString('href="http://mapos.test/index.php/garantias/imprimir/7"', $html);
        $this->assertStringNotContainsString('Telefone', $html, 'Campo vazio não aparece.');
    }
}
