<?php

require_once APPPATH . 'models/Produtos_model.php';

/**
 * Produtos migrados (#2841): dados salvos, unidades, estoque baixo, filtros
 * e o fluxo do controller.
 */
final class ProdutosTest extends MaposTestCase
{
    public function testDadosDoFormulario(): void
    {
        $dados = produtoDadosDoFormulario([
            'codDeBarra' => ' 789 ', 'descricao' => ' Tela ', 'unidade' => 'UNID', 'estoque' => '-3', 'estoqueMinimo' => '',
            'entrada' => '1', 'idProdutos' => '99', 'precoVenda' => '999999',
        ], '300.00', '450.00');

        $this->assertSame('789', $dados['codDeBarra']);
        $this->assertSame('Tela', $dados['descricao']);
        $this->assertSame('300.00', $dados['precoCompra']);
        $this->assertSame('450.00', $dados['precoVenda']);
        $this->assertSame(0, $dados['estoque']);
        $this->assertNull($dados['estoqueMinimo']);
        $this->assertSame(1, $dados['entrada']);
        $this->assertSame(0, $dados['saida']);
        $this->assertArrayNotHasKey('idProdutos', $dados);
    }

    public function testUnidadesDaTabelaDeMedidas(): void
    {
        $unidades = unidadesDeMedida();

        $this->assertArrayHasKey('UNID', $unidades);
        $this->assertSame('UNID — Unidade', $unidades['UNID']);
        $this->assertGreaterThan(20, count($unidades));
    }

    public function testEstoqueBaixo(): void
    {
        $this->assertTrue(produtoEstoqueBaixo((object) ['estoque' => 5, 'estoqueMinimo' => 5]));
        $this->assertTrue(produtoEstoqueBaixo((object) ['estoque' => 0, 'estoqueMinimo' => 1]));
        $this->assertFalse(produtoEstoqueBaixo((object) ['estoque' => 6, 'estoqueMinimo' => 5]));
        $this->assertFalse(produtoEstoqueBaixo((object) ['estoque' => 0, 'estoqueMinimo' => null]));
        $this->assertFalse(produtoEstoqueBaixo((object) ['estoque' => 0, 'estoqueMinimo' => 0]));
    }

    public function testListagemComBuscaEEstoqueBaixo(): void
    {
        $this->db->query('CREATE TABLE produtos (idProdutos INTEGER PRIMARY KEY AUTOINCREMENT, codDeBarra TEXT, descricao TEXT, estoque INTEGER, estoqueMinimo INTEGER)');
        foreach ([['789001', 'Tela notebook', 2, 5], ['789002', 'Teclado', 10, 5], ['111', 'Bateria notebook', 1, null], ['222', 'Tela celular', 0, 0]] as $linha) {
            $this->db->query('INSERT INTO produtos (codDeBarra, descricao, estoque, estoqueMinimo) VALUES (?, ?, ?, ?)', $linha);
        }

        $model = $this->makeInstance(Produtos_model::class);
        $this->setPrivateProperty($model, 'db', $this->db);

        $this->assertSame(4, $model->contar([]));
        $this->assertSame(2, $model->contar(['pesquisa' => 'notebook']));
        $this->assertSame(2, $model->contar(['pesquisa' => '7890']));
        $this->assertSame(['Tela notebook'], array_column($model->listar(['estoque' => 'baixo'], 10, 0), 'descricao'));
        $this->assertSame(1, $model->contar(['pesquisa' => 'Tela', 'estoque' => 'baixo']));
    }

    public function testControllerSegueOsPadroesECalculaOEstoqueNoBanco(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Produtos.php');

        $this->assertStringContainsString("\$this->validarFormulario('produtos')", $codigo);
        $this->assertStringContainsString("'idProdutos', (int) \$produto->idProdutos", $codigo);
        $this->assertStringNotContainsString("post('idProdutos')", $codigo);
        $this->assertStringNotContainsString("post('estoqueAtual')", $codigo);
        $this->assertStringContainsString('updateEstoque((int) $produto->idProdutos, abs($quantidade)', $codigo);
        $this->assertSame(3, substr_count($codigo, "\$this->data['legacy_assets'] = false;"));
    }
}
