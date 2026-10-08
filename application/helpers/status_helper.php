<?php

/**
 * Status de OS e vendas como pill-status (DESIGN.md: estado sempre com a
 * palavra, na paleta semântica, nunca em laranja).
 *
 *     component('pill-status', osStatusPill($os->status))
 */
if (! defined('OS_STATUS_VARIANTES')) {
    // Os status da v4, na ordem do fluxo de uma OS.
    define('OS_STATUS_VARIANTES', [
        'Orçamento' => 'neutral',
        'Negociação' => 'warning',
        'Aberto' => 'info',
        'Aprovado' => 'info',
        'Em Andamento' => 'progress',
        'Aguardando Peças' => 'warning',
        'Finalizado' => 'success',
        'Faturado' => 'success',
        'Cancelado' => 'danger',
    ]);
}

if (! function_exists('osStatusPill')) {
    /**
     * Props do pill-status para o status de uma OS. Status desconhecido
     * aparece como está, em neutral.
     *
     * @return array{label: string, variant: string}
     */
    function osStatusPill(?string $status): array
    {
        $status = trim((string) $status);

        return ['label' => $status !== '' ? $status : 'Sem status', 'variant' => OS_STATUS_VARIANTES[$status] ?? 'neutral'];
    }
}

if (! function_exists('vendaFaturadaPill')) {
    /**
     * @return array{label: string, variant: string}
     */
    function vendaFaturadaPill($faturado): array
    {
        return (int) $faturado === 1 ? ['label' => 'Faturada', 'variant' => 'success'] : ['label' => 'Em aberto', 'variant' => 'warning'];
    }
}

if (! function_exists('dinheiro')) {
    /** Valor em reais: "R$ 1.234,50". */
    function dinheiro($valor): string
    {
        return 'R$ ' . number_format((float) $valor, 2, ',', '.');
    }
}

if (! function_exists('dataBr')) {
    /** Data AAAA-MM-DD (ou data e hora) em DD/MM/AAAA; vazio para data vazia ou zerada. */
    function dataBr(?string $data): string
    {
        $data = trim((string) $data);
        if ($data === '' || str_starts_with($data, '0000-00-00')) {
            return '';
        }

        $instante = strtotime($data);

        return $instante === false ? '' : date('d/m/Y', $instante);
    }
}

if (! function_exists('valorDecimal')) {
    /**
     * Valor digitado ("1.234,56", "1234,56" ou "1234.56") em decimal para o
     * banco ("1234.56"), ou null se não for um valor válido. Com vírgula, o
     * ponto é separador de milhar; sem vírgula, o ponto é o decimal.
     */
    function valorDecimal($valor): ?string
    {
        if (! is_string($valor) && ! is_numeric($valor)) {
            return null;
        }

        $texto = str_replace(['R$', ' ', "\u{00A0}"], '', trim((string) $valor));
        if (str_contains($texto, ',')) {
            $texto = str_replace(['.', ','], ['', '.'], $texto);
        }

        if (! preg_match('/^\d{1,13}(\.\d{1,2})?$/', $texto)) {
            return null;
        }

        return number_format((float) $texto, 2, '.', '');
    }
}
