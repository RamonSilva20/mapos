<?php

/**
 * Regras do painel inicial (#2847), em funções puras para serem testadas sem
 * banco nem sessão.
 */
if (! defined('PAINEL_OS_ANDAMENTO')) {
    // OS que ainda estão com a oficina (o card "OS em andamento" e a lista).
    define('PAINEL_OS_ANDAMENTO', ['Aberto', 'Aprovado', 'Em Andamento', 'Aguardando Peças']);
}

if (! defined('PAINEL_OS_ORCAMENTO')) {
    // OS esperando o cliente aprovar.
    define('PAINEL_OS_ORCAMENTO', ['Orçamento', 'Negociação']);
}

if (! defined('PAINEL_VENDAS_ABERTAS')) {
    // Vendas que ainda não foram faturadas, finalizadas nem canceladas.
    define('PAINEL_VENDAS_ABERTAS', ['Orçamento', 'Negociação', 'Aberto', 'Aprovado', 'Em Andamento', 'Aguardando Peças']);
}

if (! defined('PAINEL_MESES')) {
    define('PAINEL_MESES', ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']);
}

if (! function_exists('painelAno')) {
    /**
     * Ano do balanço (?ano=): de 2000 até o ano que vem. Fora disso, ou
     * inválido, o ano atual.
     */
    function painelAno($valor, int $atual): int
    {
        $texto = is_scalar($valor) ? trim((string) $valor) : '';

        return ctype_digit($texto) && (int) $texto >= 2000 && (int) $texto <= $atual + 1 ? (int) $texto : $atual;
    }
}

if (! function_exists('painelSomar')) {
    /**
     * Soma das contagens dos status pedidos.
     *
     * @param  array<string, int>  $porStatus  status => quantidade
     * @param  list<string>        $status
     */
    function painelSomar(array $porStatus, array $status): int
    {
        return array_sum(array_map(static fn ($s) => (int) ($porStatus[$s] ?? 0), $status));
    }
}

if (! function_exists('painelBalanco')) {
    /**
     * Balanço mês a mês de um ano a partir das linhas agrupadas do banco
     * (mes = AAAA-MM, tipo = receita|despesa, total), com zero nos meses sem
     * movimento. Linhas de outro ano ou de outro tipo são ignoradas.
     *
     * @param  iterable<object|array>  $linhas
     * @return array{meses: list<string>, receitas: list<float>, despesas: list<float>, saldo: list<float>}
     */
    function painelBalanco(iterable $linhas, int $ano): array
    {
        $receitas = array_fill(0, 12, 0.0);
        $despesas = array_fill(0, 12, 0.0);

        foreach ($linhas as $linha) {
            $linha = (array) $linha;
            if (! preg_match('/^(\d{4})-(\d{2})$/', (string) ($linha['mes'] ?? ''), $partes) || (int) $partes[1] !== $ano) {
                continue;
            }

            $indice = (int) $partes[2] - 1;
            if ($indice < 0 || $indice > 11) {
                continue;
            }

            $total = round((float) ($linha['total'] ?? 0), 2);
            if (($linha['tipo'] ?? null) === 'receita') {
                $receitas[$indice] = round($receitas[$indice] + $total, 2);
            } elseif (($linha['tipo'] ?? null) === 'despesa') {
                $despesas[$indice] = round($despesas[$indice] + $total, 2);
            }
        }

        return [
            'meses' => PAINEL_MESES,
            'receitas' => $receitas,
            'despesas' => $despesas,
            'saldo' => array_map(static fn ($r, $d) => round($r - $d, 2), $receitas, $despesas),
        ];
    }
}

if (! function_exists('painelOsPorStatus')) {
    /**
     * Fatias do gráfico de OS por status, na ordem do fluxo da OS
     * (OS_STATUS_VARIANTES), só com os status que têm OS. Status fora da lista
     * vai no fim, em neutral.
     *
     * @param  array<string, int>  $porStatus
     * @return list<array{status: string, total: int, variante: string}>
     */
    function painelOsPorStatus(array $porStatus): array
    {
        $fatias = [];
        foreach (OS_STATUS_VARIANTES as $status => $variante) {
            if ((int) ($porStatus[$status] ?? 0) > 0) {
                $fatias[] = ['status' => $status, 'total' => (int) $porStatus[$status], 'variante' => $variante];
            }
        }

        foreach ($porStatus as $status => $total) {
            if (! array_key_exists($status, OS_STATUS_VARIANTES) && (int) $total > 0) {
                $fatias[] = ['status' => (string) $status !== '' ? (string) $status : 'Sem status', 'total' => (int) $total, 'variante' => 'neutral'];
            }
        }

        return $fatias;
    }
}

if (! function_exists('painelEventoDaOs')) {
    /**
     * Evento do FullCalendar para uma OS da agenda (data final). Só texto e
     * URLs: o módulo painel/painel monta o modal com textContent.
     *
     * O total segue a regra das telas de OS: o valor com desconto, quando há,
     * senão produtos + serviços.
     *
     * @param  object  $os  Linha de Mapos_model::calendario()
     */
    function painelEventoDaOs(object $os, string $urlVer, ?string $urlEditar): array
    {
        $bruto = (float) ($os->totalProdutos ?? 0) + (float) ($os->totalServicos ?? 0);
        $total = (float) ($os->valor_desconto ?? 0) > 0 ? (float) $os->valor_desconto : $bruto;
        $pill = osStatusPill($os->status ?? null);

        return [
            'id' => (string) $os->idOs,
            'title' => 'OS ' . $os->idOs . ' · ' . ($os->nomeCliente ?? 'Cliente removido'),
            'start' => substr((string) $os->dataFinal, 0, 10),
            'allDay' => true,
            'extendedProps' => [
                'numero' => (string) $os->idOs,
                'cliente' => (string) ($os->nomeCliente ?? 'Cliente removido'),
                'status' => $pill['label'],
                'variante' => $pill['variant'],
                'dataInicial' => dataBr($os->dataInicial ?? null),
                'dataFinal' => dataBr($os->dataFinal ?? null),
                'equipamento' => osTextoCurto($os->descricaoProduto ?? null, 120),
                'total' => dinheiro($total),
                'faturado' => (int) ($os->faturado ?? 0) === 1,
                'url' => $urlVer,
                'urlEditar' => $urlEditar,
            ],
        ];
    }
}

if (! function_exists('painelDataPorExtenso')) {
    /** "quinta-feira, 9 de outubro de 2026" (sem depender da extensão intl). */
    function painelDataPorExtenso(string $ymd): string
    {
        $instante = strtotime($ymd);
        if ($instante === false) {
            return '';
        }

        $dias = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'];
        $meses = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];

        return $dias[(int) date('w', $instante)] . ', ' . (int) date('j', $instante) . ' de ' . $meses[(int) date('n', $instante) - 1] . ' de ' . date('Y', $instante);
    }
}
