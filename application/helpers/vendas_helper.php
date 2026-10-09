<?php

/**
 * Regras da venda usadas pelas telas migradas (#2843), em funções puras para
 * serem testadas sem banco nem sessão. O que é igual ao da OS (status, texto
 * livre, itens, desconto, fatura) vem do os_helper.
 */
if (! function_exists('vendaDescricoesDaFatura')) {
    /**
     * Descrições com que o lançamento da fatura de uma venda pode ter sido
     * gravado. O formulário de faturar da v4 sugeria "Fatura de Venda Nº: X "
     * (com espaço no fim), e a exclusão procurava "Fatura de Venda - #X", que
     * nunca casava.
     *
     * @return list<string>
     */
    function vendaDescricoesDaFatura(int $idVenda): array
    {
        return ["Fatura de Venda Nº: {$idVenda} ", "Fatura de Venda Nº: {$idVenda}", "Fatura de Venda - #{$idVenda}"];
    }
}

if (! function_exists('vendaGarantiaAte')) {
    /**
     * Fim da garantia (data da venda + dias de garantia) em AAAA-MM-DD, ou
     * null sem garantia (vazia, zero ou inválida) e sem data da venda válida.
     */
    function vendaGarantiaAte(?string $dataVenda, $dias): ?string
    {
        $base = dataIsoParaYmd(substr((string) $dataVenda, 0, 10));
        if ($base === null || ! is_numeric($dias) || (int) $dias <= 0) {
            return null;
        }

        return date('Y-m-d', strtotime($base . ' +' . (int) $dias . ' days'));
    }
}

if (! function_exists('vendaGarantiaPill')) {
    /**
     * Props do pill-status da garantia: vigente (success) ou vencida
     * (neutral, não é erro), sempre com a data. Sem garantia, null.
     *
     * @return array{label: string, variant: string}|null
     */
    function vendaGarantiaPill(?string $garantiaAte, ?string $hoje = null): ?array
    {
        if ($garantiaAte === null) {
            return null;
        }

        $vigente = $garantiaAte >= ($hoje ?? date('Y-m-d'));

        return ['label' => ($vigente ? 'Até ' : 'Venceu em ') . dataBr($garantiaAte), 'variant' => $vigente ? 'success' : 'neutral'];
    }
}
