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

if (! defined('PAINEL_OS_ABAS')) {
    // Status mostrados como abas na seção de OS do painel (#3036), nesta ordem.
    define('PAINEL_OS_ABAS', ['Aberto', 'Aprovado', 'Em Andamento', 'Aguardando Peças', 'Finalizado']);
}

if (! defined('PAINEL_VENDAS_ABAS')) {
    // Status mostrados como abas na seção de Vendas do painel (#3036), nesta ordem.
    define('PAINEL_VENDAS_ABAS', ['Aberto', 'Aprovado', 'Aguardando Peças', 'Finalizado']);
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
     * O valor por status (#3037), quando informado, entra em cada fatia para o
     * modo R$ do gráfico; sem ele, a fatia só tem a quantidade (valor 0).
     *
     * @param  array<string, int>    $porStatus
     * @param  array<string, float>  $valores  status => valor somado
     * @return list<array{status: string, total: int, valor: float, variante: string}>
     */
    function painelOsPorStatus(array $porStatus, array $valores = []): array
    {
        $fatias = [];
        foreach (OS_STATUS_VARIANTES as $status => $variante) {
            if ((int) ($porStatus[$status] ?? 0) > 0) {
                $fatias[] = ['status' => $status, 'total' => (int) $porStatus[$status], 'valor' => round((float) ($valores[$status] ?? 0), 2), 'variante' => $variante];
            }
        }

        foreach ($porStatus as $status => $total) {
            if (! array_key_exists($status, OS_STATUS_VARIANTES) && (int) $total > 0) {
                $fatias[] = ['status' => (string) $status !== '' ? (string) $status : 'Sem status', 'total' => (int) $total, 'valor' => round((float) ($valores[$status] ?? 0), 2), 'variante' => 'neutral'];
            }
        }

        return $fatias;
    }
}

if (! function_exists('painelAbasStatus')) {
    /**
     * Subconjunto de OS_STATUS_VARIANTES (status => variante) na ordem de
     * $status, para montar só as abas escolhidas das seções de OS/Vendas do
     * painel (#3036). Status fora de OS_STATUS_VARIANTES vai em neutral.
     *
     * @param  list<string>  $status
     * @return array<string, string>
     */
    function painelAbasStatus(array $status): array
    {
        $variantes = [];
        foreach ($status as $s) {
            $variantes[$s] = OS_STATUS_VARIANTES[$s] ?? 'neutral';
        }

        return $variantes;
    }
}

if (! function_exists('painelStatusAtivo')) {
    /**
     * Status da aba aberta nas seções de OS/Vendas do painel (#3036): o pedido
     * (query string) quando é um status válido; senão o primeiro status que tem
     * registro; senão o primeiro da lista. Mantém a seção sempre com uma aba
     * marcada, mesmo sem nada cadastrado.
     *
     * @param  array<string, string>  $variantes  status => variante (OS_STATUS_VARIANTES)
     * @param  array<string, int>     $contagem   status => quantidade
     * @param  mixed                  $pedido     ?status= da URL
     */
    function painelStatusAtivo(array $variantes, array $contagem, $pedido): string
    {
        $status = array_keys($variantes);
        $texto = is_scalar($pedido) ? trim((string) $pedido) : '';

        if ($texto !== '' && in_array($texto, $status, true)) {
            return $texto;
        }

        foreach ($status as $s) {
            if ((int) ($contagem[$s] ?? 0) > 0) {
                return (string) $s;
            }
        }

        return (string) ($status[0] ?? '');
    }
}

if (! function_exists('painelStatusAbas')) {
    /**
     * Abas por status das seções de OS/Vendas do painel (#3036): uma aba por
     * status na ordem do fluxo (OS_STATUS_VARIANTES), com a contagem (0 quando
     * não há registro) e qual está aberta.
     *
     * @param  array<string, string>  $variantes  status => variante
     * @param  array<string, int>     $contagem   status => quantidade
     * @return list<array{status: string, variante: string, count: int, active: bool}>
     */
    function painelStatusAbas(array $variantes, array $contagem, string $ativo): array
    {
        $abas = [];
        foreach ($variantes as $status => $variante) {
            $abas[] = [
                'status' => (string) $status,
                'variante' => (string) $variante,
                'count' => (int) ($contagem[$status] ?? 0),
                'active' => (string) $status === $ativo,
            ];
        }

        return $abas;
    }
}

if (! function_exists('painelMesIntervalo')) {
    /**
     * Início e fim do mês escolhido no gráfico de OS por status (#3037):
     * 'passado' é o mês anterior, qualquer outro valor é o mês de $hoje. O fim
     * inclui o último dia até 23:59:59 (os.dataInicial costuma ter hora).
     *
     * @return array{inicio: string, fim: string}
     */
    function painelMesIntervalo(string $mes, string $hoje): array
    {
        $ts = strtotime($hoje) ?: time();
        $base = $mes === 'passado' ? strtotime('first day of last month', $ts) : strtotime('first day of this month', $ts);

        return ['inicio' => date('Y-m-01', $base), 'fim' => date('Y-m-t', $base) . ' 23:59:59'];
    }
}

if (! function_exists('painelMesValido')) {
    /** O mês pedido quando é 'atual' ou 'passado'; senão 'atual'. */
    function painelMesValido($mes): string
    {
        return (is_scalar($mes) ? (string) $mes : '') === 'passado' ? 'passado' : 'atual';
    }
}

if (! function_exists('painelOsGrafico')) {
    /**
     * Fatias do gráfico de OS por status a partir do resumo (qtd + valor) do
     * Mapos_model::osResumoPorStatus() (#3037).
     *
     * @param  array<string, array{qtd: int, valor: float}>  $resumo
     * @return list<array{status: string, total: int, valor: float, variante: string}>
     */
    function painelOsGrafico(array $resumo): array
    {
        $contagens = [];
        $valores = [];
        foreach ($resumo as $status => $dados) {
            $contagens[$status] = (int) $dados['qtd'];
            $valores[$status] = (float) $dados['valor'];
        }

        return painelOsPorStatus($contagens, $valores);
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
