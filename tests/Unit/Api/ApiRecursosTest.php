<?php

use Libraries\Api\Modulos\Lancamentos;
use Libraries\Api\Modulos\Produtos;
use Libraries\Api\Recursos\Cliente;
use Libraries\Api\Recursos\OrdemDeServico;
use Libraries\Api\Recursos\Usuario;
use Libraries\Api\Recursos\Venda;
use Libraries\Api\Schema\Schema;

final class ApiRecursosTest extends MaposTestCase
{
    private function assertSegueOSchema(Schema $schema, array $saida): void
    {
        $this->assertSame([], $schema->conferirResposta($saida), 'A resposta não bate com o schema documentado.');
    }

    public function testUsuarioNuncaExpoeSenhaNemSegredoDo2fa(): void
    {
        $saida = Usuario::de((object) [
            'idUsuarios' => '3', 'nome' => 'Ana', 'email' => 'ana@x.com', 'cpf' => '529.982.247-25', 'rg' => '', 'telefone' => '119', 'celular' => null,
            'cep' => '01000-000', 'rua' => 'Rua A', 'numero' => '1', 'bairro' => 'Centro', 'cidade' => 'SP', 'estado' => 'SP',
            'situacao' => '1', 'permissoes_id' => '2', 'permissao' => 'Técnicos', 'dataCadastro' => '2026-01-01', 'dataExpiracao' => null,
            'senha' => '$2y$10$hash', 'totp_secreto' => 'segredo', 'totp_ativado_em' => '2026-01-02 10:00:00', 'url_image_user' => '', 'tema_modo' => 'escuro', 'tema_cor' => 'azul',
        ]);

        $this->assertSegueOSchema(Usuario::schema(), $saida);
        $json = (string) json_encode($saida);
        $this->assertStringNotContainsString('hash', $json);
        $this->assertStringNotContainsString('segredo', $json);
        $this->assertTrue($saida['dois_fatores_ativo']);
        $this->assertSame(['id' => 2, 'nome' => 'Técnicos'], $saida['grupo']);
    }

    public function testClienteDeduzTipoDePessoaENaoExpoeSenha(): void
    {
        $saida = Cliente::de((object) [
            'idClientes' => '7', 'nomeCliente' => 'Empresa X', 'pessoa_fisica' => '0', 'documento' => '11.222.333/0001-81', 'fornecedor' => '1',
            'contato' => '', 'email' => 'x@x.com', 'telefone' => '', 'celular' => '', 'cep' => '', 'rua' => '', 'numero' => '', 'bairro' => '',
            'cidade' => '', 'estado' => '', 'complemento' => '', 'dataCadastro' => '0000-00-00', 'senha' => '$2y$hash',
        ]);

        $this->assertSegueOSchema(Cliente::schema(), $saida);
        $this->assertSame('juridica', $saida['tipo_pessoa']);
        $this->assertTrue($saida['fornecedor']);
        $this->assertNull($saida['data_cadastro'], 'Data zerada do MySQL vira nulo.');
        $this->assertStringNotContainsString('hash', (string) json_encode($saida));
    }

    public function testOsConverteOTextoGravadoEmTextoPuroETotaisEmDinheiro(): void
    {
        $os = (object) [
            'idOs' => '42', 'status' => 'Finalizado', 'clientes_id' => '7', 'nomeCliente' => 'Ana', 'documento' => '', 'email' => 'a@x.com',
            'telefone_cliente' => '119', 'celular_cliente' => '', 'usuarios_id' => '3', 'nome' => 'Técnico', 'dataInicial' => '2026-11-01',
            'dataFinal' => '2026-11-10', 'garantia' => '90', 'garantias_id' => null, 'descricaoProduto' => "iPhone<br>\nTela &lt;quebrada&gt;",
            'defeito' => '<p>Não liga</p>', 'observacoes' => '', 'laudoTecnico' => '', 'faturado' => '0', 'lancamento' => null,
        ];
        $produto = (object) ['idProdutos_os' => '1', 'produtos_id' => '9', 'descricao' => 'Tela', 'unidade' => 'UN', 'quantidade' => '2', 'preco' => '100.00', 'subTotal' => '180.00', 'desconto' => '10', 'tipo_desconto' => 'porcento'];
        $servico = (object) ['idServicos_os' => '2', 'servicos_id' => '4', 'nome' => 'Troca', 'quantidade' => '1', 'preco' => '0', 'precoVenda' => '50.00', 'subTotal' => '0', 'desconto' => '0', 'tipo_desconto' => null];

        $saida = OrdemDeServico::de($os, [
            'totais' => osTotais(180.0, 50.0, 200.0, 'real', 30, 20.0),
            'editavel' => true,
            'produtos' => [$produto],
            'servicos' => [$servico],
            'anexos' => [(object) ['idAnexos' => '5', 'anexo' => 'foto.jpg', 'thumb' => 'thumb_foto.jpg', 'path' => '/servidor/secreto']],
            'anotacoes' => [(object) ['idAnotacoes' => '1', 'anotacao' => '[Ana] Ligou', 'data_hora' => '2026-11-02 10:00:00']],
            'historico' => [(object) ['status_anterior' => null, 'status_novo' => 'Aberto', 'usuarios_id' => null, 'data_hora' => '2026-11-01 08:00:00']],
            'cobranca_id' => null,
        ], 'http://mapos.test/api/v2/os/42');

        $this->assertSegueOSchema(OrdemDeServico::schema(), $saida);
        $this->assertSame("iPhone\nTela <quebrada>", $saida['descricao_produto']);
        $this->assertSame('Não liga', $saida['defeito']);
        $this->assertSame('2027-02-08', $saida['garantia_ate']);
        $this->assertSame('200.00', $saida['totais']['total']);
        $this->assertSame(['tipo' => 'porcento', 'valor' => '10.00'], $saida['produtos'][0]['desconto']);
        $this->assertSame('50.00', $saida['servicos'][0]['preco'], 'Serviço sem preço na OS usa o do cadastro.');
        $this->assertSame('http://mapos.test/api/v2/os/42/anexos/5?miniatura=true', $saida['anexos'][0]['miniatura_url']);
        $this->assertStringNotContainsString('/servidor/secreto', (string) json_encode($saida), 'O caminho do arquivo no servidor não sai.');
    }

    public function testResumosDeOsEVendaUsamOTotalComDesconto(): void
    {
        $os = OrdemDeServico::resumoDe((object) ['idOs' => '1', 'status' => 'Aberto', 'clientes_id' => '7', 'nomeCliente' => 'Ana', 'usuarios_id' => null, 'responsavel' => null,
            'dataInicial' => '2026-11-01', 'dataFinal' => null, 'descricaoProduto' => 'x', 'faturado' => '0', 'valor_desconto' => '90', 'totalProdutos' => '60', 'totalServicos' => '40']);
        $this->assertSegueOSchema(OrdemDeServico::schemaResumo(), $os);
        $this->assertSame('90.00', $os['total']);

        $venda = Venda::resumoDe((object) ['idVendas' => '3', 'status' => 'Faturado', 'clientes_id' => '7', 'nomeCliente' => 'Ana', 'usuarios_id' => '2', 'vendedor' => 'Bia',
            'dataVenda' => '2026-11-01', 'faturado' => '1', 'garantia' => '30', 'valor_desconto' => '0', 'totalItens' => '120.5']);
        $this->assertSegueOSchema(Venda::schemaResumo(), $venda);
        $this->assertSame('120.50', $venda['total']);
        $this->assertSame('2026-12-01', $venda['garantia_ate']);
    }

    public function testProdutoELancamento(): void
    {
        $produto = Produtos::de((object) ['idProdutos' => '1', 'codDeBarra' => '', 'descricao' => 'Cabo', 'unidade' => 'UN', 'precoCompra' => '1', 'precoVenda' => '2.5',
            'estoque' => '2', 'estoqueMinimo' => '5', 'entrada' => '1', 'saida' => '0']);
        $this->assertSegueOSchema(Produtos::schema(), $produto);
        $this->assertTrue($produto['estoque_baixo']);

        $lancamento = Lancamentos::de((object) ['idLancamentos' => '9', 'tipo' => 'receita', 'descricao' => 'Fatura', 'cliente_fornecedor' => 'Ana', 'clientes_id' => '7',
            'valor' => '100', 'desconto' => '10', 'valor_desconto' => '90', 'data_vencimento' => '2000-01-01', 'data_pagamento' => null, 'baixado' => '0',
            'forma_pgto' => 'Pix', 'observacoes' => '', 'vendas_id' => null, 'usuarios_id' => '2', 'modificado_por' => 'Bia']);
        $this->assertSegueOSchema(Lancamentos::schema(), $lancamento);
        $this->assertSame('vencido', $lancamento['situacao']);
        $this->assertSame('90.00', $lancamento['valor_liquido']);
        $this->assertSame('10.00', $lancamento['desconto']);
    }
}
