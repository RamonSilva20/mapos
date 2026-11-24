<?php

namespace Libraries\Api\Recursos;

use Libraries\Api\Schema\Comuns;
use Libraries\Api\Schema\Schema;

final class Venda
{
    private static array $cache = [];

    public static function schema(): Schema
    {
        return self::$cache['venda'] ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio()->exemplo(15),
            'status' => Schema::enum(OrdemDeServico::status())->obrigatorio(),
            'cliente' => Cliente::referencia()->obrigatorio(),
            'vendedor' => Comuns::pessoa()->obrigatorio(),
            'data_venda' => Schema::data()->anulavel()->obrigatorio(),
            'garantia_dias' => Schema::inteiro()->anulavel()->obrigatorio(),
            'garantia_ate' => Schema::data()->anulavel()->obrigatorio(),
            'observacoes' => Schema::texto()->obrigatorio()->descricao('Observações internas.'),
            'observacoes_cliente' => Schema::texto()->obrigatorio()->descricao('Saem na impressão para o cliente.'),
            'faturado' => Schema::booleano()->obrigatorio(),
            'lancamento_id' => Schema::inteiro()->anulavel()->obrigatorio(),
            'editavel' => Schema::booleano()->obrigatorio()->descricao('Venda faturada ou cancelada só é alterada com a configuração "editar venda faturada" ligada.'),
            'totais' => OrdemDeServico::schemaTotais()->obrigatorio(),
            'produtos' => Schema::lista(OrdemDeServico::schemaItemProduto())->obrigatorio(),
            'cobranca_id' => Schema::inteiro()->anulavel()->obrigatorio(),
        ])->nome('Venda');
    }

    public static function schemaResumo(): Schema
    {
        return self::$cache['resumo'] ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio(),
            'status' => Schema::texto()->obrigatorio(),
            'cliente' => Comuns::pessoa()->obrigatorio(),
            'vendedor' => Comuns::pessoa()->anulavel()->obrigatorio(),
            'data_venda' => Schema::data()->anulavel()->obrigatorio(),
            'faturado' => Schema::booleano()->obrigatorio(),
            'garantia_ate' => Schema::data()->anulavel()->obrigatorio(),
            'total' => Schema::decimal()->obrigatorio(),
        ])->nome('VendaResumo');
    }

    public static function consultaResumo(object $db): object
    {
        return $db
            ->select('vendas.idVendas, vendas.dataVenda, vendas.status, vendas.faturado, vendas.garantia, vendas.valor_desconto, vendas.clientes_id, vendas.usuarios_id, clientes.nomeCliente, usuarios.nome AS vendedor')
            ->select('(SELECT COALESCE(SUM(itens_de_vendas.subTotal), 0) FROM itens_de_vendas WHERE itens_de_vendas.vendas_id = vendas.idVendas) AS totalItens', false)
            ->from('vendas')
            ->join('clientes', 'clientes.idClientes = vendas.clientes_id', 'left')
            ->join('usuarios', 'usuarios.idUsuarios = vendas.usuarios_id', 'left');
    }

    public static function resumoDe(object $linha): array
    {
        $bruto = (float) ($linha->totalItens ?? 0);
        $comDesconto = (float) ($linha->valor_desconto ?? 0);

        return [
            'id' => (int) $linha->idVendas,
            'status' => (string) $linha->status,
            'cliente' => ['id' => (int) $linha->clientes_id, 'nome' => (string) ($linha->nomeCliente ?? '')],
            'vendedor' => $linha->usuarios_id ? ['id' => (int) $linha->usuarios_id, 'nome' => (string) ($linha->vendedor ?? '')] : null,
            'data_venda' => Valor::data($linha->dataVenda ?? null),
            'faturado' => (int) ($linha->faturado ?? 0) === 1,
            'garantia_ate' => vendaGarantiaAte($linha->dataVenda ?? null, $linha->garantia ?? null),
            'total' => Valor::decimal($comDesconto > 0 && $comDesconto < $bruto ? $comDesconto : $bruto),
        ];
    }

    /** @param  array<string, mixed>  $partes  produtos, totais, editavel, cobranca_id */
    public static function de(object $venda, array $partes): array
    {
        return [
            'id' => (int) $venda->idVendas,
            'status' => (string) $venda->status,
            'cliente' => Cliente::referenciaDe((int) $venda->clientes_id, $venda->nomeCliente ?? '', $venda->documento ?? null, $venda->emailCliente ?? null, $venda->telefone ?? null, $venda->celular ?? null),
            'vendedor' => ['id' => (int) $venda->usuarios_id, 'nome' => (string) ($venda->nome ?? '')],
            'data_venda' => Valor::data($venda->dataVenda),
            'garantia_dias' => Valor::inteiroOuNulo($venda->garantia),
            'garantia_ate' => vendaGarantiaAte($venda->dataVenda, $venda->garantia),
            'observacoes' => Valor::textoDoEditor($venda->observacoes),
            'observacoes_cliente' => Valor::textoDoEditor($venda->observacoes_cliente),
            'faturado' => (int) $venda->faturado === 1,
            'lancamento_id' => Valor::inteiroOuNulo($venda->lancamentos_id ?? null),
            'editavel' => (bool) $partes['editavel'],
            'totais' => OrdemDeServico::totaisDe($partes['totais']),
            'produtos' => array_map([OrdemDeServico::class, 'produtoDe'], $partes['produtos']),
            'cobranca_id' => $partes['cobranca_id'],
        ];
    }
}
