<?php

require_once APPPATH . 'models/Relatorios_model.php';
require_once APPPATH . 'controllers/Relatorios.php';
require_once APPPATH . 'helpers/relatorios_helper.php';

if (! function_exists('site_url')) {
    function site_url($uri = '')
    {
        return 'http://mapos.test/index.php/' . ltrim((string) $uri, '/');
    }
}

/**
 * Telas de relatórios (#2845): a definição de cada tela aponta para ações que
 * existem, as faixas de preço e estoque valem sozinhas e a tela única.
 */
final class RelatoriosTest extends MaposTestCase
{
    private const TELAS = ['clientes', 'produtos', 'servicos', 'os', 'vendas', 'financeiro', 'sku', 'receitasBrutasMei'];

    public function testTodaTelaTemDefinicaoCompleta(): void
    {
        foreach (self::TELAS as $chave) {
            $definicao = relatorioDefinicao($chave);

            $this->assertIsArray($definicao, $chave);
            foreach (['titulo', 'descricao', 'icone', 'rapidos', 'acao', 'campos'] as $campo) {
                $this->assertArrayHasKey($campo, $definicao, "{$chave}: {$campo}");
            }
            $this->assertNotEmpty($definicao['rapidos'], $chave);
            $this->assertNotEmpty($definicao['campos'], $chave);
            $this->assertContains($definicao['icone'], iconesDisponiveis(), "{$chave}: ícone do sprite");
            foreach ($definicao['rapidos'] as $rapido) {
                $this->assertContains($rapido['icone'], iconesDisponiveis(), "{$chave}: ícone do atalho");
            }
        }

        $this->assertNull(relatorioDefinicao('inexistente'));
    }

    public function testAtalhosEFormulariosApontamParaAcoesQueExistemNoMapaDePermissoes(): void
    {
        require APPPATH . 'config/permissions_map.php';
        $mapa = $config['permissions_map'];
        $metodos = array_map(static fn ($m) => strtolower($m->getName()), (new ReflectionClass(Relatorios::class))->getMethods(ReflectionMethod::IS_PUBLIC));

        foreach (self::TELAS as $chave) {
            $definicao = relatorioDefinicao($chave);
            $rotas = array_merge([$definicao['acao']], array_column($definicao['rapidos'], 'url'));

            foreach ($rotas as $rota) {
                $this->assertMatchesRegularExpression('#^relatorios/(\w+)(\?format=\w+)?$#', $rota, "{$chave}: {$rota}");
                $acao = explode('?', substr($rota, strlen('relatorios/')))[0];

                $this->assertContains(strtolower($acao), $metodos, "{$chave}: {$rota} é uma ação do controller");
                $this->assertArrayHasKey($acao, $mapa['Relatorios'], "{$chave}: {$rota} tem regra de permissão");
            }

            $this->assertArrayHasKey($chave, $mapa['Relatorios'], "{$chave}: a tela tem regra de permissão");
        }
    }

    public function testCamposTemOsNomesQueAsAcoesLem(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Relatorios.php');

        foreach (self::TELAS as $chave) {
            foreach (relatorioDefinicao($chave)['campos'] as $campo) {
                $this->assertStringContainsString("input->get('{$campo['name']}'", $codigo, "{$chave}: o controller lê {$campo['name']}");
            }
        }
    }

    public function testFaixaAceitaDinheiroENumeroEDescartaOResto(): void
    {
        $this->assertSame(['1000.00', null], relatorioFaixa('1.000,00', '', true));
        $this->assertSame([null, '100.00'], relatorioFaixa('', '100,00', true));
        $this->assertSame(['50.00', '1500.50'], relatorioFaixa('50', '1500.50', true));
        $this->assertSame([null, null], relatorioFaixa('abc', "1' OR 1=1", true));
        $this->assertSame([null, null], relatorioFaixa(null, ['1'], true));

        $this->assertSame(['10', null], relatorioFaixa('10', '', false));
        $this->assertSame(['0', '5'], relatorioFaixa('000', '5', false));
        $this->assertSame([null, null], relatorioFaixa('1;drop', '-3', false));
        $this->assertSame([null, null], relatorioFaixa('1.5', '99999999999', false));
    }

    private function model(): Relatorios_model
    {
        $this->db->query('CREATE TABLE produtos (idProdutos INTEGER PRIMARY KEY AUTOINCREMENT, descricao TEXT, precoCompra REAL, precoVenda REAL, estoque INTEGER)');
        $this->db->query('CREATE TABLE servicos (idServicos INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, preco REAL)');
        foreach ([['A', 50, 5], ['B', 1500.5, 20], ['C', 3000, 0]] as [$nome, $preco, $estoque]) {
            $this->db->query('INSERT INTO produtos (descricao, precoCompra, precoVenda, estoque) VALUES (?, 1, ?, ?)', [$nome, $preco, $estoque]);
        }
        foreach ([['barato', 40], ['caro', 1200]] as [$nome, $preco]) {
            $this->db->query('INSERT INTO servicos (nome, preco) VALUES (?, ?)', [$nome, $preco]);
        }

        $model = $this->makeInstance(Relatorios_model::class);
        $this->setPrivateProperty($model, 'db', $this->db);

        return $model;
    }

    public function testProdutosCustomFiltraPorFaixaComLimitesIndependentes(): void
    {
        $model = $this->model();
        $nomes = static fn (array $linhas) => array_column($linhas, 'descricao');

        $this->assertSame(['A', 'B', 'C'], $nomes($model->produtosCustom()));
        $this->assertSame(['B', 'C'], $nomes($model->produtosCustom('1000.00', null)));
        $this->assertSame(['A'], $nomes($model->produtosCustom(null, '100.00')));
        $this->assertSame(['A', 'B'], $nomes($model->produtosCustom('50.00', '1500.50')));
        $this->assertSame(['B'], $nomes($model->produtosCustom(null, null, '10', null)));
        $this->assertSame(['A', 'C'], $nomes($model->produtosCustom(null, null, null, '5')));
    }

    public function testServicosCustomFiltraPorFaixaComLimitesIndependentes(): void
    {
        $model = $this->model();
        $nomes = static fn (array $linhas) => array_column($linhas, 'nome');

        $this->assertSame(['barato', 'caro'], $nomes($model->servicosCustom()));
        $this->assertSame(['caro'], $nomes($model->servicosCustom('1000.00', null)));
        $this->assertSame(['barato'], $nomes($model->servicosCustom(null, '100.00')));
        $this->assertSame(['barato'], $nomes($model->servicosCustom('40.00', '100.00')));
    }

    private function renderizar(array $variaveis): string
    {
        return (function () use ($variaveis) {
            extract($variaveis);
            ob_start();

            try {
                include APPPATH . 'views/relatorios/relatorio.php';
            } catch (Throwable $erro) {
                ob_end_clean();

                throw $erro;
            }

            return (string) ob_get_clean();
        })();
    }

    public function testTelaUnicaMontaCamposAtalhosEFormularioQueAbreEmOutraAba(): void
    {
        foreach (self::TELAS as $chave) {
            $html = $this->renderizar(['relatorio' => relatorioDefinicao($chave)]);

            $this->assertStringContainsString('data-module="relatorios/filtros"', $html, $chave);
            $this->assertMatchesRegularExpression('#<form method="get" action="http://mapos.test/index.php/relatorios/\w+" target="_blank"#', $html, $chave);
            $this->assertStringContainsString('rel="noopener"', $html, $chave);
            $this->assertSame(1, preg_match_all('/<button[^>]*bg-primary/', $html), "{$chave}: um único button-primary");
            $this->assertStringContainsString('type="reset"', $html, $chave);
            $this->assertSame(0, preg_match('/<script(?![^>]*\bsrc=)[^>]*>/', $html), $chave);
            $this->assertStringNotContainsString('bx-', $html, $chave);
        }
    }

    public function testCamposDeCadaTipo(): void
    {
        $os = $this->renderizar(['relatorio' => relatorioDefinicao('os')]);
        $this->assertMatchesRegularExpression('/<input[^>]*type="date"[^>]*name="dataInicial"|<input[^>]*name="dataInicial"[^>]*type="date"/', $os);
        $this->assertStringContainsString('data-autocomplete="http://mapos.test/index.php/os/autoCompleteCliente"', $os);
        $this->assertStringContainsString('data-autocomplete-alvo="campo-cliente"', $os);
        $this->assertMatchesRegularExpression('/<input type="hidden" id="campo-cliente" name="cliente"/', $os);
        $this->assertMatchesRegularExpression('/<input type="hidden" id="campo-responsavel" name="responsavel"/', $os);
        $this->assertStringContainsString('<option value="Aguardando Peças">', $os);
        $this->assertStringContainsString('<option value="xls">', $os);

        $produtos = $this->renderizar(['relatorio' => relatorioDefinicao('produtos')]);
        $this->assertSame(2, substr_count($produtos, 'data-mascara="dinheiro"'));
        $this->assertMatchesRegularExpression('/<input[^>]*name="estoqueInicial"[^>]*/', $produtos);
        $this->assertStringContainsString('Deixe em branco', $produtos);

        $sku = $this->renderizar(['relatorio' => relatorioDefinicao('sku')]);
        $this->assertMatchesRegularExpression('/<input type="hidden" id="campo-clientes_id" name="clientes_id"/', $sku);
        $this->assertStringContainsString('<option value="venda">', $sku);

        $mei = $this->renderizar(['relatorio' => relatorioDefinicao('receitasBrutasMei')]);
        $this->assertStringContainsString('<option value="docx">', $mei);
        $this->assertStringNotContainsString('<option value="xls">', $mei);
    }

    public function testControllerUsaATelaUnicaSemAsViewsAntigas(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Relatorios.php');

        $this->assertSame(8, substr_count($codigo, "'relatorios/relatorio'"));
        $this->assertStringNotContainsString("'relatorios/rel_", $codigo);
        $this->assertSame([], glob(APPPATH . 'views/relatorios/rel_*.php'));
    }
}
