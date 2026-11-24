<?php

namespace Libraries\Api\Modulos;

use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Modulo;
use Libraries\Api\Recursos\Valor;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Schema\Comuns;
use Libraries\Api\Schema\Schema;

final class Produtos extends Modulo
{
    public static function rotas(): array
    {
        $tag = 'Produtos';

        return [
            Rota::get('/produtos', [self::class, 'listar'])->permissao('vProduto')->tag($tag)
                ->resumo('Lista os produtos')
                ->consulta(Comuns::paginacaoConsulta([
                    'busca' => Schema::texto()->max(100)->descricao('Descrição ou código de barras.'),
                    'estoque_baixo' => Schema::booleano()->descricao('Só os que estão no estoque mínimo ou abaixo.'),
                ]))
                ->lista(self::schema()),

            Rota::post('/produtos', [self::class, 'criar'])->permissao('aProduto')->tag($tag)
                ->resumo('Cadastra um produto')
                ->corpo(self::entrada())
                ->criado(self::schema()),

            Rota::get('/produtos/unidades', [self::class, 'unidades'])->permissao('vProduto', 'aProduto', 'eProduto')->tag($tag)
                ->resumo('Unidades de medida aceitas')
                ->dados(Schema::lista(Schema::objeto([
                    'sigla' => Schema::texto()->obrigatorio()->exemplo('UN'),
                    'nome' => Schema::texto()->obrigatorio()->exemplo('UN — Unidade'),
                ]))),

            Rota::get('/produtos/{id}', [self::class, 'ver'])->permissao('vProduto')->tag($tag)
                ->resumo('Mostra um produto')
                ->dados(self::schema())
                ->erros(404),

            Rota::patch('/produtos/{id}', [self::class, 'atualizar'])->permissao('eProduto')->tag($tag)
                ->resumo('Altera um produto')
                ->corpo(self::entrada()->semObrigatorios())
                ->dados(self::schema())
                ->erros(404),

            Rota::post('/produtos/{id}/estoque', [self::class, 'ajustarEstoque'])->permissao('eProduto')->tag($tag)
                ->resumo('Ajusta o estoque', 'Soma a quantidade ao estoque atual; use número negativo para retirar. A conta é feita no banco e o estoque não fica negativo.')
                ->corpo(Schema::objeto([
                    'quantidade' => Schema::inteiro()->obrigatorio()->min(-1000000)->max(1000000)->exemplo(5)
                        ->regra(static fn (int $v): ?string => $v === 0 ? 'Informe uma quantidade diferente de zero.' : null),
                ]))
                ->dados(self::schema())
                ->erros(404),

            Rota::delete('/produtos/{id}', [self::class, 'excluir'])->permissao('dProduto')->tag($tag)
                ->resumo('Exclui um produto', 'Como no painel, o produto sai também das OS e vendas em que foi usado.')
                ->semConteudo()
                ->erros(404),
        ];
    }

    public static function schema(): Schema
    {
        static $schema = null;

        return $schema ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio(),
            'codigo_barras' => Schema::texto()->anulavel()->obrigatorio(),
            'descricao' => Schema::texto()->obrigatorio()->exemplo('Tela LCD 6.1"'),
            'unidade' => Schema::texto()->anulavel()->obrigatorio()->exemplo('UN'),
            'preco_compra' => Schema::decimal()->obrigatorio(),
            'preco_venda' => Schema::decimal()->obrigatorio(),
            'estoque' => Schema::inteiro()->obrigatorio(),
            'estoque_minimo' => Schema::inteiro()->anulavel()->obrigatorio(),
            'estoque_baixo' => Schema::booleano()->obrigatorio(),
            'entrada' => Schema::booleano()->obrigatorio()->descricao('Aparece nas compras (entrada de estoque).'),
            'saida' => Schema::booleano()->obrigatorio()->descricao('Aparece nas OS e vendas.'),
        ])->nome('Produto');
    }

    private static function entrada(): Schema
    {
        return Schema::objeto([
            'codigo_barras' => Schema::texto()->max(70),
            'descricao' => Schema::texto()->obrigatorio()->max(80),
            'unidade' => Schema::texto()->obrigatorio()->max(10)->exemplo('UN'),
            'preco_compra' => Schema::decimal()->obrigatorio()->min(0)->max(99999999.99),
            'preco_venda' => Schema::decimal()->obrigatorio()->min(0)->max(99999999.99),
            'estoque' => Schema::inteiro()->obrigatorio()->min(0)->max(99999999),
            'estoque_minimo' => Schema::inteiro()->min(0)->max(99999999)->anulavel(),
            'entrada' => Schema::booleano()->padrao(true),
            'saida' => Schema::booleano()->padrao(true),
        ])->nome('ProdutoEntrada');
    }

    public static function de(object $p): array
    {
        return [
            'id' => (int) $p->idProdutos,
            'codigo_barras' => Valor::textoOuNulo($p->codDeBarra),
            'descricao' => (string) $p->descricao,
            'unidade' => Valor::textoOuNulo($p->unidade),
            'preco_compra' => Valor::decimal($p->precoCompra),
            'preco_venda' => Valor::decimal($p->precoVenda),
            'estoque' => (int) $p->estoque,
            'estoque_minimo' => Valor::inteiroOuNulo($p->estoqueMinimo),
            'estoque_baixo' => produtoEstoqueBaixo($p),
            'entrada' => (int) $p->entrada === 1,
            'saida' => (int) $p->saida === 1,
        ];
    }

    public function listar(): Resposta
    {
        $consulta = $this->ctx->consulta();

        return $this->listarPeloModelo($this->ctx->modelo('Produtos_model'), [
            'pesquisa' => $consulta['busca'] ?? '',
            'estoque' => ! empty($consulta['estoque_baixo']) ? 'baixo' : '',
        ], [self::class, 'de']);
    }

    public function unidades(): Resposta
    {
        $lista = [];
        foreach (unidadesDeMedida() as $sigla => $nome) {
            $lista[] = ['sigla' => (string) $sigla, 'nome' => $nome];
        }

        return Resposta::dados($lista);
    }

    public function ver(): Resposta
    {
        return Resposta::dados(self::de($this->produto()));
    }

    public function criar(): Resposta
    {
        $id = $this->inserirLinha('produtos', $this->paraBanco($this->ctx->corpo()));
        $this->ctx->auditar('Adicionou um produto');

        return Resposta::criado(self::de($this->produto($id)), $this->ctx->url('produtos/' . $id));
    }

    public function atualizar(): Resposta
    {
        $produto = $this->produto();
        [$dados] = $this->ctx->corpoParcial([
            'codigo_barras' => (string) $produto->codDeBarra,
            'descricao' => (string) $produto->descricao,
            'unidade' => (string) $produto->unidade,
            'preco_compra' => Valor::decimal($produto->precoCompra),
            'preco_venda' => Valor::decimal($produto->precoVenda),
            'estoque' => (int) $produto->estoque,
            'estoque_minimo' => Valor::inteiroOuNulo($produto->estoqueMinimo),
            'entrada' => (int) $produto->entrada === 1,
            'saida' => (int) $produto->saida === 1,
        ]);
        $id = (int) $produto->idProdutos;
        $this->atualizarLinha('produtos', 'idProdutos', $id, $this->paraBanco($dados));
        $this->ctx->auditar('Alterou um produto. ID: ' . $id);

        return Resposta::dados(self::de($this->produto($id)));
    }

    public function ajustarEstoque(): Resposta
    {
        $produto = $this->produto();
        $quantidade = (int) $this->ctx->corpo()['quantidade'];
        $id = (int) $produto->idProdutos;

        $this->db->set('estoque', 'estoque + ' . $quantidade, false)->where('idProdutos', $id)->where('estoque + ' . $quantidade . ' >=', 0, false)->update('produtos');
        if ($this->db->affected_rows() !== 1) {
            throw HttpErro::regraDeNegocio('O estoque não pode ficar negativo: há ' . (int) $produto->estoque . ' em estoque.', 'estoque_insuficiente');
        }
        $this->ctx->auditar('Atualizou estoque de um produto. ID: ' . $id . ' (' . sprintf('%+d', $quantidade) . ')');

        return Resposta::dados(self::de($this->produto($id)));
    }

    public function excluir(): Resposta
    {
        $id = (int) $this->produto()->idProdutos;
        $this->transacao(function () use ($id): void {
            $this->removerLinha('produtos_os', 'produtos_id', $id);
            $this->removerLinha('itens_de_vendas', 'produtos_id', $id);
            $this->removerLinha('produtos', 'idProdutos', $id);
        });
        $this->ctx->auditar('Removeu um produto. ID: ' . $id);

        return Resposta::semConteudo();
    }

    private function produto(?int $id = null): object
    {
        return $this->encontrar('produtos', 'idProdutos', $id ?? $this->ctx->id(), 'Produto não encontrado.');
    }

    private function paraBanco(array $dados): array
    {
        return produtoDadosDoFormulario([
            'codDeBarra' => (string) ($dados['codigo_barras'] ?? ''),
            'descricao' => (string) $dados['descricao'],
            'unidade' => (string) $dados['unidade'],
            'estoque' => (string) $dados['estoque'],
            'estoqueMinimo' => ($dados['estoque_minimo'] ?? null) === null ? '' : (string) $dados['estoque_minimo'],
            'entrada' => ! empty($dados['entrada']) ? '1' : '',
            'saida' => ! empty($dados['saida']) ? '1' : '',
        ], (string) $dados['preco_compra'], (string) $dados['preco_venda']);
    }
}
