<?php

/**
 * Regras dos lançamentos financeiros usadas pelas telas migradas (#2844), em
 * funções puras para serem testadas sem banco nem sessão.
 *
 * Como o banco guarda um lançamento:
 *
 * - valor: o valor cheio, antes do desconto;
 * - desconto: o desconto em reais;
 * - valor_desconto: o valor líquido (valor - desconto). Linhas antigas, de
 *   antes da v5, às vezes têm 0 aqui; financeiroLiquido() cobre os dois casos.
 */
if (! defined('FINANCEIRO_TIPOS')) {
    define('FINANCEIRO_TIPOS', ['receita' => 'Receita', 'despesa' => 'Despesa']);
}

if (! defined('FINANCEIRO_STATUS')) {
    // Filtro de situação da listagem. "vencido" é pendente com o vencimento
    // antes de hoje.
    define('FINANCEIRO_STATUS', ['pendente' => 'Pendente', 'pago' => 'Pago', 'vencido' => 'Vencido']);
}

if (! defined('FINANCEIRO_PERIODOS')) {
    define('FINANCEIRO_PERIODOS', [
        'dia' => 'Dia',
        'semana' => 'Semana',
        'mes_anterior' => 'Mês anterior',
        'mes' => 'Mês',
        'mes_posterior' => 'Mês posterior',
        'ano' => 'Ano',
        'personalizado' => 'Personalizado',
    ]);
}

if (! defined('FINANCEIRO_FORMAS_PAGAMENTO')) {
    define('FINANCEIRO_FORMAS_PAGAMENTO', [
        'Dinheiro', 'Pix', 'Boleto', 'Cartão de Crédito', 'Cartão de Débito', 'Cheque', 'Cheque Pré-datado',
        'Depósito', 'Transferência DOC', 'Transferência TED', 'Promissória',
    ]);
}

if (! defined('FINANCEIRO_PARCELAS_MAX')) {
    define('FINANCEIRO_PARCELAS_MAX', 12);
}

if (! function_exists('financeiroPeriodo')) {
    /**
     * Intervalo [de, ate] (AAAA-MM-DD) de um período predefinido da listagem,
     * a partir de $hoje. A semana vai de domingo a sábado. "personalizado" e
     * nomes desconhecidos não têm intervalo (null): as datas vêm do usuário.
     *
     * @return array{0: string, 1: string}|null
     */
    function financeiroPeriodo(string $periodo, DateTimeInterface $hoje): ?array
    {
        $dia = DateTimeImmutable::createFromInterface($hoje)->setTime(0, 0);
        $formato = static fn (DateTimeImmutable $data): string => $data->format('Y-m-d');

        switch ($periodo) {
            case 'dia':
                return [$formato($dia), $formato($dia)];
            case 'semana':
                $domingo = $dia->modify('-' . (int) $dia->format('w') . ' days');

                return [$formato($domingo), $formato($domingo->modify('+6 days'))];
            case 'mes_anterior':
                return [$formato($dia->modify('first day of last month')), $formato($dia->modify('last day of last month'))];
            case 'mes':
                return [$formato($dia->modify('first day of this month')), $formato($dia->modify('last day of this month'))];
            case 'mes_posterior':
                return [$formato($dia->modify('first day of next month')), $formato($dia->modify('last day of next month'))];
            case 'ano':
                return [$dia->format('Y') . '-01-01', $dia->format('Y') . '-12-31'];
        }

        return null;
    }
}

if (! function_exists('financeiroLiquido')) {
    /**
     * Valor líquido de um lançamento (o que entra ou sai de fato). Usa
     * valor_desconto quando preenchido; senão, valor - desconto. É a mesma
     * regra do SQL de Financeiro_model::LIQUIDO.
     *
     * @param  object|array<string, mixed>  $lancamento
     */
    function financeiroLiquido($lancamento): float
    {
        $campo = static fn (string $nome) => is_array($lancamento) ? ($lancamento[$nome] ?? 0) : ($lancamento->{$nome} ?? 0);
        $valor = (float) $campo('valor');
        $comDesconto = (float) $campo('valor_desconto');

        return round($comDesconto > 0 ? $comDesconto : $valor - (float) $campo('desconto'), 2);
    }
}

if (! function_exists('financeiroDescontoEmReais')) {
    /** Desconto do lançamento em reais (valor cheio menos o líquido), nunca negativo. */
    function financeiroDescontoEmReais($lancamento): float
    {
        $valor = (float) (is_array($lancamento) ? ($lancamento['valor'] ?? 0) : ($lancamento->valor ?? 0));

        return max(0.0, round($valor - financeiroLiquido($lancamento), 2));
    }
}

if (! function_exists('financeiroStatusPill')) {
    /**
     * Props do pill-status de um lançamento: Pago, Vencido (pendente com o
     * vencimento antes de $hoje) ou Pendente.
     *
     * @return array{label: string, variant: string}
     */
    function financeiroStatusPill($lancamento, string $hoje): array
    {
        $campo = static fn (string $nome) => is_array($lancamento) ? ($lancamento[$nome] ?? null) : ($lancamento->{$nome} ?? null);

        if ((int) $campo('baixado') === 1) {
            return ['label' => 'Pago', 'variant' => 'success'];
        }

        $vencimento = substr((string) $campo('data_vencimento'), 0, 10);

        return $vencimento !== '' && $vencimento < $hoje
            ? ['label' => 'Vencido', 'variant' => 'danger']
            : ['label' => 'Pendente', 'variant' => 'warning'];
    }
}

if (! function_exists('financeiroCentavos')) {
    /** "1234.56" (valorDecimal()) em centavos inteiros. */
    function financeiroCentavos(string $decimal): int
    {
        return (int) round(((float) $decimal) * 100);
    }
}

if (! function_exists('financeiroDecimal')) {
    /** Centavos inteiros em "1234.56", o formato das colunas decimal. */
    function financeiroDecimal(int $centavos): string
    {
        return number_format($centavos / 100, 2, '.', '');
    }
}

if (! function_exists('financeiroDividirCentavos')) {
    /**
     * Divide um total em centavos em $partes iguais; os centavos que sobram
     * vão um a um para as primeiras partes (100,00 em 3 = 33,34 + 33,33 +
     * 33,33), de modo que a soma é sempre exatamente o total.
     *
     * @return list<int>
     */
    function financeiroDividirCentavos(int $total, int $partes): array
    {
        $partes = max(1, $partes);
        $base = intdiv($total, $partes);
        $resto = $total - $base * $partes;

        $resultado = [];
        for ($i = 0; $i < $partes; $i++) {
            $resultado[] = $base + ($i < $resto ? 1 : 0);
        }

        return $resultado;
    }
}

if (! function_exists('financeiroAdicionarMeses')) {
    /**
     * Data (AAAA-MM-DD) $meses meses depois de $data, mantendo o dia do mês
     * ou, quando o mês de destino é mais curto, o último dia dele: 31/01 + 1
     * = 28/02 (ou 29), e 31/01 + 2 = 31/03.
     */
    function financeiroAdicionarMeses(string $data, int $meses): string
    {
        $base = new DateTimeImmutable($data);
        $primeiro = $base->modify('first day of this month')->modify("+{$meses} months");
        $dia = min((int) $base->format('j'), (int) $primeiro->format('t'));

        return $primeiro->setDate((int) $primeiro->format('Y'), (int) $primeiro->format('n'), $dia)->format('Y-m-d');
    }
}

if (! function_exists('financeiroLancamentoDoFormulario')) {
    /**
     * Confere o POST do formulário de lançamento e devolve os dados de uma
     * linha de lancamentos (sem usuarios_id) e os erros por campo.
     *
     * valor é o valor cheio e desconto é em reais: o líquido (valor_desconto)
     * é calculado aqui, nunca vem do navegador. O vínculo com o cadastro de
     * clientes só vale quando $cliente (a linha de clientes do id postado,
     * conferida no banco pelo controller) tem o mesmo nome do texto digitado.
     *
     * $contexto: hoje (AAAA-MM-DD), controle_baixa (config: a baixa só pode
     * ser feita com a data de hoje) e pagamento_atual (a data de pagamento já
     * gravada, ao editar: ela pode ser mantida mesmo com o controle ligado).
     *
     * @param  array<string, mixed>  $post
     * @param  array{hoje: string, controle_baixa?: bool, pagamento_atual?: string|null}  $contexto
     * @return array{0: array<string, mixed>, 1: array<string, string>}  [dados, erros]
     */
    function financeiroLancamentoDoFormulario(array $post, array $contexto, ?object $cliente = null): array
    {
        $texto = static fn (string $campo): string => is_scalar($post[$campo] ?? null) ? trim((string) $post[$campo]) : '';
        $erros = [];

        $tipo = $texto('tipo');
        if (! array_key_exists($tipo, FINANCEIRO_TIPOS)) {
            $erros['tipo'] = 'Escolha receita ou despesa.';
        }

        $descricao = $texto('descricao');
        if ($descricao === '') {
            $erros['descricao'] = 'Informe a descrição.';
        } elseif (mb_strlen($descricao) > 255) {
            $erros['descricao'] = 'A descrição tem até 255 caracteres.';
        }

        $nome = $texto('cliente_fornecedor');
        if ($nome === '') {
            $erros['cliente_fornecedor'] = 'Informe o cliente ou fornecedor.';
        } elseif (mb_strlen($nome) > 255) {
            $erros['cliente_fornecedor'] = 'O nome tem até 255 caracteres.';
        }

        $valorCentavos = null;
        $valor = valorDecimal($texto('valor'));
        if ($valor === null || financeiroCentavos($valor) <= 0) {
            $erros['valor'] = 'Informe um valor maior que zero, como 1.250,00.';
        } else {
            $valorCentavos = financeiroCentavos($valor);
        }

        $descontoCentavos = 0;
        if ($texto('desconto') !== '') {
            $desconto = valorDecimal($texto('desconto'));
            if ($desconto === null) {
                $erros['desconto'] = 'Informe o desconto em reais, como 50,00.';
            } else {
                $descontoCentavos = financeiroCentavos($desconto);
                if ($valorCentavos !== null && $descontoCentavos >= $valorCentavos) {
                    $erros['desconto'] = 'O desconto tem de ser menor que o valor.';
                }
            }
        }

        $vencimento = dataIsoParaYmd($texto('data_vencimento'));
        if ($vencimento === null) {
            $erros['data_vencimento'] = 'Informe a data de vencimento.';
        }

        $baixado = in_array($texto('baixado'), ['1', 'on', 'true'], true);
        $pagamento = null;
        if ($baixado) {
            $pagamento = dataIsoParaYmd($texto('data_pagamento'));
            if ($pagamento === null) {
                $erros['data_pagamento'] = 'Informe a data do pagamento.';
            } elseif (! empty($contexto['controle_baixa']) && $pagamento !== $contexto['hoje'] && $pagamento !== ($contexto['pagamento_atual'] ?? null)) {
                $erros['data_pagamento'] = 'A baixa só pode ser feita com a data de hoje (controle de baixa ligado em Configurações).';
            }
        }

        $forma = $texto('forma_pgto');
        if ($forma !== '' && ! in_array($forma, FINANCEIRO_FORMAS_PAGAMENTO, true)) {
            $erros['forma_pgto'] = 'Escolha uma forma de pagamento da lista.';
        } elseif ($baixado && $forma === '') {
            $erros['forma_pgto'] = 'Escolha a forma de pagamento.';
        }

        $observacoes = is_scalar($post['observacoes'] ?? null) ? trim((string) $post['observacoes']) : '';

        $liquido = ($valorCentavos ?? 0) - $descontoCentavos;
        $vinculado = $cliente !== null && mb_strtolower(trim((string) $cliente->nomeCliente)) === mb_strtolower($nome);

        return [[
            'tipo' => $tipo,
            'descricao' => $descricao,
            'cliente_fornecedor' => $nome,
            'clientes_id' => $vinculado ? (int) $cliente->idClientes : null,
            'valor' => financeiroDecimal($valorCentavos ?? 0),
            'desconto' => financeiroDecimal($descontoCentavos),
            'valor_desconto' => financeiroDecimal($liquido),
            'tipo_desconto' => 'real',
            'data_vencimento' => $vencimento,
            'data_pagamento' => $pagamento,
            'baixado' => $baixado ? 1 : 0,
            'forma_pgto' => $forma !== '' ? $forma : null,
            'observacoes' => $observacoes,
        ], $erros];
    }
}

if (! function_exists('financeiroParcelamentoDoFormulario')) {
    /**
     * Parcelamento pedido no formulário de novo lançamento: quantidade de
     * parcelas (1 = à vista), entrada opcional (paga na data da entrada) e a
     * conferência contra o lançamento já validado ($dados).
     *
     * @param  array<string, mixed>  $post
     * @param  array<string, mixed>  $dados  Saída de financeiroLancamentoDoFormulario()
     * @return array{0: array{parcelas: int, entrada: int, data_entrada: string|null}, 1: array<string, string>}
     */
    function financeiroParcelamentoDoFormulario(array $post, array $dados): array
    {
        $texto = static fn (string $campo): string => is_scalar($post[$campo] ?? null) ? trim((string) $post[$campo]) : '';
        $erros = [];

        $parcelas = $texto('parcelas') === '' ? 1 : $texto('parcelas');
        if (! ctype_digit((string) $parcelas) || (int) $parcelas < 1 || (int) $parcelas > FINANCEIRO_PARCELAS_MAX) {
            $erros['parcelas'] = 'Escolha de 1 a ' . FINANCEIRO_PARCELAS_MAX . ' parcelas.';
            $parcelas = 1;
        }
        $parcelas = (int) $parcelas;

        $entrada = 0;
        $dataEntrada = null;
        if ($texto('entrada') !== '') {
            $valorEntrada = valorDecimal($texto('entrada'));
            if ($valorEntrada === null) {
                $erros['entrada'] = 'Informe a entrada em reais, como 200,00.';
            } else {
                $entrada = financeiroCentavos($valorEntrada);
            }
        }

        if ($parcelas === 1 && $entrada > 0) {
            $erros['entrada'] = 'A entrada só vale para lançamentos parcelados.';
        }

        if ($parcelas > 1 && (int) ($dados['baixado'] ?? 0) === 1) {
            $erros['baixado'] = 'Lançamento parcelado nasce em aberto: dê baixa em cada parcela depois.';
        }

        if ($entrada > 0 && ! isset($erros['entrada'])) {
            $liquido = financeiroCentavos((string) ($dados['valor_desconto'] ?? '0'));
            if ($entrada >= $liquido) {
                $erros['entrada'] = 'A entrada tem de ser menor que o valor a pagar (' . dinheiro($liquido / 100) . ').';
            }

            $dataEntrada = dataIsoParaYmd($texto('data_entrada'));
            if ($dataEntrada === null) {
                $erros['data_entrada'] = 'Informe a data da entrada.';
            }
        }

        return [['parcelas' => $parcelas, 'entrada' => $entrada, 'data_entrada' => $dataEntrada], $erros];
    }
}

if (! function_exists('financeiroLinhasDoParcelamento')) {
    /**
     * Linhas de lancamentos de um lançamento parcelado.
     *
     * O valor líquido, menos a entrada, é dividido em parcelas mensais a
     * partir do vencimento informado (financeiroAdicionarMeses()); o desconto
     * é dividido do mesmo jeito, e o valor cheio de cada parcela é o líquido
     * mais o desconto dela. A entrada é um lançamento à parte, já pago na data
     * da entrada.
     *
     * @param  array<string, mixed>  $dados        Saída de financeiroLancamentoDoFormulario()
     * @param  array{parcelas: int, entrada: int, data_entrada: string|null}  $parcelamento
     * @return list<array<string, mixed>>
     */
    function financeiroLinhasDoParcelamento(array $dados, array $parcelamento, ?int $usuario): array
    {
        $parcelas = $parcelamento['parcelas'];
        $liquido = financeiroCentavos((string) $dados['valor_desconto']);
        $desconto = financeiroCentavos((string) $dados['desconto']);
        $entrada = $parcelamento['entrada'];

        $comum = [
            'tipo' => $dados['tipo'],
            'cliente_fornecedor' => $dados['cliente_fornecedor'],
            'clientes_id' => $dados['clientes_id'],
            'forma_pgto' => $dados['forma_pgto'],
            'observacoes' => $dados['observacoes'],
            'tipo_desconto' => 'real',
            'usuarios_id' => $usuario,
        ];
        // O sufixo entra na descrição sem passar dos 255 caracteres da coluna.
        $descricao = static fn (string $sufixo): string => mb_substr((string) $dados['descricao'], 0, 255 - mb_strlen($sufixo)) . $sufixo;

        $linhas = [];
        if ($entrada > 0) {
            $linhas[] = $comum + [
                'descricao' => $descricao(' - Entrada'),
                'valor' => financeiroDecimal($entrada),
                'desconto' => financeiroDecimal(0),
                'valor_desconto' => financeiroDecimal($entrada),
                'data_vencimento' => $parcelamento['data_entrada'],
                'data_pagamento' => $parcelamento['data_entrada'],
                'baixado' => 1,
            ];
        }

        $liquidos = financeiroDividirCentavos($liquido - $entrada, $parcelas);
        $descontos = financeiroDividirCentavos($desconto, $parcelas);
        foreach ($liquidos as $i => $parte) {
            $linhas[] = $comum + [
                'descricao' => $descricao(' - Parcela ' . ($i + 1) . '/' . $parcelas),
                'valor' => financeiroDecimal($parte + $descontos[$i]),
                'desconto' => financeiroDecimal($descontos[$i]),
                'valor_desconto' => financeiroDecimal($parte),
                'data_vencimento' => financeiroAdicionarMeses((string) $dados['data_vencimento'], $i),
                'data_pagamento' => null,
                'baixado' => 0,
            ];
        }

        return $linhas;
    }
}

if (! function_exists('financeiroValoresDoFormulario')) {
    /**
     * Valores dos campos do formulário: o POST quando a tela volta com erro, o
     * lançamento ao editar ou os padrões ao cadastrar. Dinheiro sai no formato
     * da máscara ("1.234,56") e datas em AAAA-MM-DD (input type=date).
     *
     * @param  array<string, mixed>|null  $post
     * @param  array<string, string>      $padrao  Valores de um lançamento novo (tipo, hoje...)
     * @return array<string, string>
     */
    function financeiroValoresDoFormulario(?array $post, ?object $lancamento, array $padrao = []): array
    {
        $campos = ['tipo', 'descricao', 'cliente_fornecedor', 'clientes_id', 'valor', 'desconto', 'data_vencimento', 'baixado', 'data_pagamento', 'forma_pgto', 'observacoes', 'parcelas', 'entrada', 'data_entrada'];

        if ($post !== null) {
            $valores = [];
            foreach ($campos as $campo) {
                $valores[$campo] = is_scalar($post[$campo] ?? null) ? trim((string) $post[$campo]) : '';
            }

            return $valores;
        }

        if ($lancamento !== null) {
            $desconto = financeiroDescontoEmReais($lancamento);

            return array_merge(array_fill_keys($campos, ''), [
                'tipo' => (string) $lancamento->tipo,
                'descricao' => (string) $lancamento->descricao,
                'cliente_fornecedor' => (string) $lancamento->cliente_fornecedor,
                'clientes_id' => (string) ($lancamento->clientes_id ?? ''),
                'valor' => number_format((float) $lancamento->valor, 2, ',', '.'),
                'desconto' => $desconto > 0 ? number_format($desconto, 2, ',', '.') : '',
                'data_vencimento' => dataIsoParaYmd(substr((string) $lancamento->data_vencimento, 0, 10)) ?? '',
                'baixado' => (int) $lancamento->baixado === 1 ? '1' : '',
                'data_pagamento' => dataIsoParaYmd(substr((string) $lancamento->data_pagamento, 0, 10)) ?? '',
                'forma_pgto' => (string) ($lancamento->forma_pgto ?? ''),
                'observacoes' => (string) $lancamento->observacoes,
            ]);
        }

        return array_merge(array_fill_keys($campos, ''), ['parcelas' => '1'], $padrao);
    }
}

/*
 * Cobranças (#2844).
 */

if (! function_exists('cobrancaStatusPill')) {
    /**
     * Props do pill-status de uma cobrança. Os gateways devolvem o status em
     * vocabulários diferentes (paid, approved, RECEIVED...), e a descrição
     * deles é uma frase longa demais para a pílula: aqui cada status vira uma
     * palavra e uma variante. Status desconhecido aparece como veio, em neutral.
     *
     * @return array{label: string, variant: string}
     */
    function cobrancaStatusPill(?string $status): array
    {
        $grupos = [
            ['Paga', 'success', ['paid', 'approved', 'settled', 'received', 'confirmed', 'received_in_cash', 'dunning_received']],
            ['Gerada', 'info', ['new', 'link', 'active', 'up_to_date', 'finished']],
            ['Aguardando', 'warning', ['waiting', 'pending', 'identified', 'in_process', 'authorized', 'awaiting_risk_analysis', 'dunning_requested']],
            ['Vencida', 'danger', ['overdue', 'expired', 'unpaid', 'rejected']],
            ['Cancelada', 'neutral', ['canceled', 'cancelled', 'deleted']],
            ['Estornada', 'neutral', ['refunded', 'refund_requested']],
            ['Em disputa', 'danger', ['contested', 'in_mediation', 'charged_back', 'chargeback_requested', 'chargeback_dispute', 'awaiting_chargeback_reversal']],
        ];

        $chave = strtolower(trim((string) $status));
        foreach ($grupos as [$rotulo, $variante, $chaves]) {
            if (in_array($chave, $chaves, true)) {
                return ['label' => $rotulo, 'variant' => $variante];
            }
        }

        return ['label' => $chave !== '' ? (string) $status : 'Sem status', 'variant' => 'neutral'];
    }
}

if (! function_exists('cobrancaDescricaoDoStatus')) {
    /** Descrição do status no config dos gateways, ou o próprio status quando não houver. */
    function cobrancaDescricaoDoStatus($gateways, ?string $gateway, ?string $status): string
    {
        $descricao = is_array($gateways) ? ($gateways[(string) $gateway]['transaction_status'][(string) $status] ?? null) : null;

        return trim((string) ($descricao ?? $status));
    }
}

if (! function_exists('cobrancaUrlSegura')) {
    /** URL de boleto, link ou PDF que vem do gateway: só http(s) vira link; o resto, null. */
    function cobrancaUrlSegura(?string $url): ?string
    {
        $url = trim((string) $url);

        return preg_match('#^https?://\S+$#i', $url) ? $url : null;
    }
}
