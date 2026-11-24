<?php

namespace Libraries\Api\Servicos;

use Libraries\Api\Contexto;
use Libraries\Api\Http\HttpErro;
use Libraries\Api\Recursos\OrdemDeServico;
use Libraries\Api\Schema\Schema;

final class Comercial
{
    public function __construct(private readonly Contexto $ctx)
    {
    }

    public function controleDeEstoque(): bool
    {
        return (bool) $this->ctx->configuracao('control_estoque', false);
    }

    /** @param  iterable<object>  $produtos  linhas com produtos_id e quantidade */
    public function movimentarEstoque(iterable $produtos, string $operacao, string $motivo): void
    {
        if (! $this->controleDeEstoque()) {
            return;
        }
        $modelo = $this->ctx->modelo('Produtos_model');
        foreach ($produtos as $p) {
            if ($operacao === '-') {
                $db = $this->ctx->db();
                if (! $db->query(
                    'UPDATE produtos SET estoque = estoque - ? WHERE idProdutos = ? AND estoque >= ?',
                    [$p->quantidade, (int) $p->produtos_id, $p->quantidade]
                )) {
                    throw HttpErro::interno('Não foi possível atualizar o estoque. Tente novamente.');
                }
                if ($db->affected_rows() !== 1) {
                    throw HttpErro::regraDeNegocio('Estoque insuficiente. Atualize os dados e tente novamente.', 'estoque_insuficiente');
                }
            } else {
                $modelo->updateEstoque((int) $p->produtos_id, $p->quantidade, $operacao);
            }
            $this->ctx->auditar('ESTOQUE: Produto id ' . (int) $p->produtos_id . ($operacao === '+' ? ' voltou ao estoque' : ' baixa do estoque') . '. Quantidade: ' . $p->quantidade . '. ' . $motivo);
        }
    }

    public static function schemaItemEntrada(bool $produto): Schema
    {
        return Schema::objeto([
            ($produto ? 'produto_id' : 'servico_id') => Schema::inteiro()->obrigatorio()->min(1),
            'quantidade' => ($produto ? Schema::inteiro()->min(1)->max(999999) : Schema::numero()->min(0.01)->max(999999))->padrao(1),
            'preco' => Schema::decimal()->min(0)->descricao('Sem preço, vale o preço do cadastro.'),
            'desconto' => OrdemDeServico::schemaDesconto()->anulavel(),
        ]);
    }

    public static function schemaDescontoEntrada(): Schema
    {
        return Schema::objeto([
            'tipo' => Schema::enum(['real', 'porcento'])->obrigatorio(),
            'valor' => Schema::decimal()->obrigatorio()->min(0)->descricao('0 tira o desconto.'),
        ]);
    }

    public static function schemaFaturaEntrada(): Schema
    {
        return Schema::objeto([
            'descricao' => Schema::texto()->obrigatorio()->max(255)->exemplo('Fatura da OS #42'),
            'vencimento' => Schema::data()->obrigatorio(),
            'forma_pagamento' => Schema::enum(OS_FORMAS_PAGAMENTO)->obrigatorio(),
            'recebido' => Schema::booleano()->padrao(false),
            'data_recebimento' => Schema::data()->descricao('Obrigatória quando recebido é true.'),
            'observacoes' => Schema::texto()->max(2000),
        ]);
    }

    /**
     * @param  array<string, mixed>  $entrada
     * @return array<string, mixed>  linha para produtos_os, servicos_os ou itens_de_vendas
     */
    public function item(array $entrada, bool $produto): array
    {
        $idCadastro = (int) $entrada[$produto ? 'produto_id' : 'servico_id'];
        $cadastro = $produto
            ? ($this->ctx->db()->select('idProdutos, descricao, precoVenda, estoque')->where('idProdutos', $idCadastro)->get('produtos', 1)->row() ?: null)
            : ($this->ctx->db()->select('idServicos, nome, preco')->where('idServicos', $idCadastro)->get('servicos', 1)->row() ?: null);

        $preco = $entrada['preco'] ?? null;
        if ($preco === null && $cadastro !== null) {
            $preco = $produto ? $cadastro->precoVenda : $cadastro->preco;
        }

        $post = [
            'quantidade' => (string) ($entrada['quantidade'] ?? 1),
            'preco' => (string) ($preco ?? ''),
            'tipoDescontoItem' => $entrada['desconto']['tipo'] ?? null,
            'descontoItem' => isset($entrada['desconto']) ? (string) $entrada['desconto']['valor'] : '',
        ];

        [$dados, $erros] = osItemDoFormulario($post, $produto ? 'produto' : 'servico', $cadastro, $this->controleDeEstoque());
        if ($erros !== []) {
            throw HttpErro::validacao(self::errosDoItem($erros));
        }

        return $dados;
    }

    /** @return array{tipo_desconto: ?string, desconto: float, subTotal: float} */
    public function descontoDoItem(object $item, array $entrada): array
    {
        [$desconto, $erros] = osDescontoDoItem(osValorBrutoDoItem($item), $entrada['tipo'], (string) $entrada['valor']);
        if ($erros !== []) {
            throw HttpErro::validacao(self::errosDoItem($erros));
        }

        return $desconto;
    }

    /** @return array{tipo_desconto: ?string, desconto: float, valor_desconto: float} */
    public function descontoGeral(float $bruto, array $entrada, string $documento, string $itens): array
    {
        [$dados, $erros] = osCalcularDesconto($bruto, $entrada['tipo'], (string) $entrada['valor'], $documento, $itens);
        if ($erros !== []) {
            throw HttpErro::validacao(self::traduzir($erros, ['tipoDesconto' => 'tipo', 'desconto' => 'valor']));
        }

        return $dados;
    }

    /** @return array<string, string> */
    public static function errosDoItem(array $erros): array
    {
        return self::traduzir($erros, [
            'produto' => 'produto_id',
            'servico' => 'servico_id',
            'tipoDescontoItem' => 'desconto.tipo',
            'descontoItem' => 'desconto.valor',
        ]);
    }

    /** @return array<string, string> */
    public static function errosDaFatura(array $erros): array
    {
        return self::traduzir($erros, ['formaPgto' => 'forma_pagamento', 'recebimento' => 'data_recebimento']);
    }

    /** @return array<string, string> */
    public static function postDaFatura(array $entrada): array
    {
        return [
            'descricao' => (string) $entrada['descricao'],
            'vencimento' => (string) $entrada['vencimento'],
            'formaPgto' => (string) $entrada['forma_pagamento'],
            'recebido' => ! empty($entrada['recebido']) ? '1' : '',
            'recebimento' => (string) ($entrada['data_recebimento'] ?? ''),
            'observacoes' => (string) ($entrada['observacoes'] ?? ''),
        ];
    }

    /** @return array<string, string> */
    private static function traduzir(array $erros, array $mapa): array
    {
        $saida = [];
        foreach ($erros as $campo => $mensagem) {
            $saida[$mapa[$campo] ?? $campo] = (string) $mensagem;
        }

        return $saida;
    }
}
