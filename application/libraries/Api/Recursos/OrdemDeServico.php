<?php

namespace Libraries\Api\Recursos;

use Libraries\Api\Schema\Comuns;
use Libraries\Api\Schema\Schema;

final class OrdemDeServico
{
    private static array $cache = [];

    public static function status(): array
    {
        return array_keys(OS_STATUS_VARIANTES);
    }

    public static function schemaDesconto(): Schema
    {
        return self::$cache['desconto'] ??= Schema::objeto([
            'tipo' => Schema::enum(['real', 'porcento'])->obrigatorio(),
            'valor' => Schema::decimal()->obrigatorio()->descricao('Em reais ou em porcentagem, conforme o tipo.'),
        ])->nome('Desconto');
    }

    public static function schemaTotais(): Schema
    {
        return self::$cache['totais'] ??= Schema::objeto([
            'produtos' => Schema::decimal()->obrigatorio(),
            'servicos' => Schema::decimal()->obrigatorio(),
            'bruto' => Schema::decimal()->obrigatorio()->descricao('Produtos + serviços, já com o desconto de cada item.'),
            'desconto' => Schema::decimal()->obrigatorio()->descricao('Desconto geral, em reais.'),
            'total' => Schema::decimal()->obrigatorio(),
            'desconto_geral' => self::schemaDesconto()->anulavel()->obrigatorio()->descricao('Como o desconto geral foi informado. Nulo: sem desconto.'),
            'desconto_itens' => Schema::decimal()->obrigatorio()->descricao('Soma dos descontos dados item a item (informativo).'),
        ])->nome('Totais');
    }

    public static function schemaItemProduto(): Schema
    {
        return self::$cache['produto'] ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio()->descricao('Id do item na OS (use nas rotas do item).'),
            'produto_id' => Schema::inteiro()->obrigatorio(),
            'descricao' => Schema::texto()->obrigatorio(),
            'unidade' => Schema::texto()->anulavel()->obrigatorio(),
            'quantidade' => Schema::inteiro()->obrigatorio(),
            'preco' => Schema::decimal()->obrigatorio(),
            'desconto' => self::schemaDesconto()->anulavel()->obrigatorio(),
            'subtotal' => Schema::decimal()->obrigatorio(),
        ])->nome('ItemDeProduto');
    }

    public static function schemaItemServico(): Schema
    {
        return self::$cache['servico'] ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio(),
            'servico_id' => Schema::inteiro()->obrigatorio(),
            'nome' => Schema::texto()->obrigatorio(),
            'quantidade' => Schema::numero()->obrigatorio(),
            'preco' => Schema::decimal()->obrigatorio(),
            'desconto' => self::schemaDesconto()->anulavel()->obrigatorio(),
            'subtotal' => Schema::decimal()->obrigatorio(),
        ])->nome('ItemDeServico');
    }

    public static function schemaAnexo(): Schema
    {
        return self::$cache['anexo'] ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio(),
            'nome' => Schema::texto()->obrigatorio(),
            'imagem' => Schema::booleano()->obrigatorio(),
            'url' => Schema::url()->obrigatorio()->descricao('Download autenticado (Authorization: Bearer).'),
            'miniatura_url' => Schema::url()->anulavel()->obrigatorio(),
        ])->nome('Anexo');
    }

    public static function schemaAnotacao(): Schema
    {
        return self::$cache['anotacao'] ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio(),
            'texto' => Schema::texto()->obrigatorio()->descricao('O autor vem no início, entre colchetes, como no painel.'),
            'data_hora' => Schema::dataHora()->obrigatorio(),
        ])->nome('Anotacao');
    }

    public static function schemaHistorico(): Schema
    {
        return self::$cache['historico'] ??= Schema::objeto([
            'status_anterior' => Schema::texto()->anulavel()->obrigatorio(),
            'status_novo' => Schema::texto()->obrigatorio(),
            'autor' => Comuns::pessoa()->anulavel()->obrigatorio(),
            'data_hora' => Schema::dataHora()->obrigatorio(),
        ])->nome('HistoricoDeStatus');
    }

    public static function schema(): Schema
    {
        return self::$cache['os'] ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio()->exemplo(42),
            'status' => Schema::enum(self::status())->obrigatorio(),
            'cliente' => Cliente::referencia()->obrigatorio(),
            'tecnico' => Comuns::pessoa()->obrigatorio(),
            'data_inicial' => Schema::data()->anulavel()->obrigatorio(),
            'data_final' => Schema::data()->anulavel()->obrigatorio(),
            'garantia_dias' => Schema::inteiro()->anulavel()->obrigatorio(),
            'garantia_ate' => Schema::data()->anulavel()->obrigatorio()->descricao('Fim da garantia: só para OS finalizada ou faturada com garantia em dias.'),
            'termo_garantia' => Schema::objeto([
                'id' => Schema::inteiro()->obrigatorio(),
                'referencia' => Schema::texto()->anulavel()->obrigatorio(),
            ])->anulavel()->obrigatorio(),
            'descricao_produto' => Schema::texto()->obrigatorio()->descricao('Texto puro; quebras de linha preservadas.'),
            'defeito' => Schema::texto()->obrigatorio(),
            'observacoes' => Schema::texto()->obrigatorio(),
            'laudo_tecnico' => Schema::texto()->obrigatorio(),
            'faturado' => Schema::booleano()->obrigatorio(),
            'lancamento_id' => Schema::inteiro()->anulavel()->obrigatorio()->descricao('Receita criada no faturamento.'),
            'editavel' => Schema::booleano()->obrigatorio()->descricao('OS faturada ou cancelada só é alterada com a configuração "editar OS faturada" ligada.'),
            'totais' => self::schemaTotais()->obrigatorio(),
            'produtos' => Schema::lista(self::schemaItemProduto())->obrigatorio(),
            'servicos' => Schema::lista(self::schemaItemServico())->obrigatorio(),
            'anexos' => Schema::lista(self::schemaAnexo())->obrigatorio(),
            'anotacoes' => Schema::lista(self::schemaAnotacao())->obrigatorio(),
            'historico' => Schema::lista(self::schemaHistorico())->obrigatorio(),
            'cobranca_id' => Schema::inteiro()->anulavel()->obrigatorio(),
        ])->nome('OrdemDeServico');
    }

    public static function schemaResumo(): Schema
    {
        return self::$cache['resumo'] ??= Schema::objeto([
            'id' => Schema::inteiro()->obrigatorio(),
            'status' => Schema::texto()->obrigatorio(),
            'cliente' => Comuns::pessoa()->obrigatorio(),
            'tecnico' => Comuns::pessoa()->anulavel()->obrigatorio(),
            'data_inicial' => Schema::data()->anulavel()->obrigatorio(),
            'data_final' => Schema::data()->anulavel()->obrigatorio(),
            'descricao_produto' => Schema::texto()->obrigatorio(),
            'faturado' => Schema::booleano()->obrigatorio(),
            'total' => Schema::decimal()->obrigatorio(),
        ])->nome('OrdemDeServicoResumo');
    }

    public static function consultaResumo(object $db): object
    {
        $produtos = '(SELECT COALESCE(SUM(produtos_os.subTotal), 0) FROM produtos_os WHERE produtos_os.os_id = os.idOs)';
        $valorServico = 'CASE WHEN servicos_os.desconto > 0 THEN servicos_os.subTotal ELSE COALESCE(NULLIF(servicos_os.preco, 0), servicos.preco, 0) * COALESCE(NULLIF(servicos_os.quantidade, 0), 1) END';
        $servicos = "(SELECT COALESCE(SUM({$valorServico}), 0) FROM servicos_os LEFT JOIN servicos ON servicos.idServicos = servicos_os.servicos_id WHERE servicos_os.os_id = os.idOs)";

        return $db
            ->select('os.idOs, os.dataInicial, os.dataFinal, os.descricaoProduto, os.status, os.faturado, os.valor_desconto, os.clientes_id, os.usuarios_id, clientes.nomeCliente, usuarios.nome AS responsavel')
            ->select($produtos . ' AS totalProdutos', false)
            ->select($servicos . ' AS totalServicos', false)
            ->from('os')
            ->join('clientes', 'clientes.idClientes = os.clientes_id', 'left')
            ->join('usuarios', 'usuarios.idUsuarios = os.usuarios_id', 'left');
    }

    public static function resumoDe(object $linha): array
    {
        $bruto = (float) ($linha->totalProdutos ?? 0) + (float) ($linha->totalServicos ?? 0);
        $comDesconto = (float) ($linha->valor_desconto ?? 0);

        return [
            'id' => (int) $linha->idOs,
            'status' => (string) $linha->status,
            'cliente' => ['id' => (int) $linha->clientes_id, 'nome' => (string) ($linha->nomeCliente ?? '')],
            'tecnico' => $linha->usuarios_id ? ['id' => (int) $linha->usuarios_id, 'nome' => (string) ($linha->responsavel ?? '')] : null,
            'data_inicial' => Valor::data($linha->dataInicial ?? null),
            'data_final' => Valor::data($linha->dataFinal ?? null),
            'descricao_produto' => mb_substr(Valor::textoDoEditor($linha->descricaoProduto ?? ''), 0, 200),
            'faturado' => (int) ($linha->faturado ?? 0) === 1,
            'total' => Valor::decimal($comDesconto > 0 && $comDesconto < $bruto ? $comDesconto : $bruto),
        ];
    }

    /**
     * @param  array<string, mixed>  $partes  produtos, servicos, anexos, anotacoes, historico, totais, editavel, cobranca_id
     */
    public static function de(object $os, array $partes, string $urlBase): array
    {
        $id = (int) $os->idOs;
        $totais = $partes['totais'];

        return [
            'id' => $id,
            'status' => (string) $os->status,
            'cliente' => Cliente::referenciaDe((int) $os->clientes_id, $os->nomeCliente ?? '', $os->documento ?? null, $os->email ?? null, $os->telefone_cliente ?? null, $os->celular_cliente ?? null),
            'tecnico' => ['id' => (int) $os->usuarios_id, 'nome' => (string) ($os->nome ?? '')],
            'data_inicial' => Valor::data($os->dataInicial),
            'data_final' => Valor::data($os->dataFinal),
            'garantia_dias' => Valor::inteiroOuNulo($os->garantia),
            'garantia_ate' => osVencimentoGarantia($os->dataFinal, $os->garantia, $os->status),
            'termo_garantia' => $os->garantias_id ? ['id' => (int) $os->garantias_id, 'referencia' => Valor::textoOuNulo($os->refGarantia ?? null)] : null,
            'descricao_produto' => Valor::textoDoEditor($os->descricaoProduto),
            'defeito' => Valor::textoDoEditor($os->defeito),
            'observacoes' => Valor::textoDoEditor($os->observacoes),
            'laudo_tecnico' => Valor::textoDoEditor($os->laudoTecnico),
            'faturado' => (int) $os->faturado === 1,
            'lancamento_id' => Valor::inteiroOuNulo($os->lancamento ?? null),
            'editavel' => (bool) $partes['editavel'],
            'totais' => self::totaisDe($totais),
            'produtos' => array_map([self::class, 'produtoDe'], $partes['produtos']),
            'servicos' => array_map([self::class, 'servicoDe'], $partes['servicos']),
            'anexos' => array_map(static fn (object $a): array => self::anexoDe($a, $urlBase . '/anexos/' . (int) $a->idAnexos), $partes['anexos']),
            'anotacoes' => array_map([self::class, 'anotacaoDe'], $partes['anotacoes']),
            'historico' => array_map([self::class, 'historicoDe'], $partes['historico']),
            'cobranca_id' => $partes['cobranca_id'],
        ];
    }

    /** @param  array<string, mixed>  $totais  saída de osTotais() */
    public static function totaisDe(array $totais): array
    {
        return [
            'produtos' => Valor::decimal($totais['produtos']),
            'servicos' => Valor::decimal($totais['servicos']),
            'bruto' => Valor::decimal($totais['bruto']),
            'desconto' => Valor::decimal($totais['desconto']),
            'total' => Valor::decimal($totais['total']),
            'desconto_geral' => $totais['tipo'] !== null ? ['tipo' => $totais['tipo'], 'valor' => Valor::decimal($totais['informado'])] : null,
            'desconto_itens' => Valor::decimal($totais['descontoItens'] ?? 0),
        ];
    }

    public static function descontoDoItem(object $item): ?array
    {
        if ((float) ($item->desconto ?? 0) <= 0) {
            return null;
        }

        return ['tipo' => ($item->tipo_desconto ?? null) === 'porcento' ? 'porcento' : 'real', 'valor' => Valor::decimal($item->desconto)];
    }

    public static function produtoDe(object $item): array
    {
        return [
            'id' => (int) ($item->idProdutos_os ?? $item->idItens),
            'produto_id' => (int) $item->produtos_id,
            'descricao' => (string) ($item->descricao ?? ''),
            'unidade' => Valor::textoOuNulo($item->unidade ?? null),
            'quantidade' => (int) $item->quantidade,
            'preco' => Valor::decimal($item->preco ?: ($item->precoVenda ?? 0)),
            'desconto' => self::descontoDoItem($item),
            'subtotal' => Valor::decimal($item->subTotal),
        ];
    }

    public static function servicoDe(object $item): array
    {
        return [
            'id' => (int) $item->idServicos_os,
            'servico_id' => (int) $item->servicos_id,
            'nome' => (string) ($item->nome ?? $item->servico ?? ''),
            'quantidade' => (float) ($item->quantidade ?: 1),
            'preco' => Valor::decimal((float) $item->preco > 0 ? $item->preco : ($item->precoVenda ?? 0)),
            'desconto' => self::descontoDoItem($item),
            'subtotal' => Valor::decimal(osValorDoItem($item)),
        ];
    }

    public static function anexoDe(object $anexo, string $url): array
    {
        $temMiniatura = (string) ($anexo->thumb ?? '') !== '';

        return [
            'id' => (int) $anexo->idAnexos,
            'nome' => (string) $anexo->anexo,
            'imagem' => $temMiniatura || in_array(strtolower(pathinfo((string) $anexo->anexo, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'], true),
            'url' => $url,
            'miniatura_url' => $temMiniatura ? $url . '?miniatura=true' : null,
        ];
    }

    public static function anotacaoDe(object $anotacao): array
    {
        return [
            'id' => (int) $anotacao->idAnotacoes,
            'texto' => (string) $anotacao->anotacao,
            'data_hora' => Valor::dataHora($anotacao->data_hora),
        ];
    }

    public static function historicoDe(object $linha): array
    {
        return [
            'status_anterior' => Valor::textoOuNulo($linha->status_anterior),
            'status_novo' => (string) $linha->status_novo,
            'autor' => $linha->usuarios_id ? ['id' => (int) $linha->usuarios_id, 'nome' => (string) ($linha->autor ?? '')] : null,
            'data_hora' => Valor::dataHora($linha->data_hora),
        ];
    }
}
