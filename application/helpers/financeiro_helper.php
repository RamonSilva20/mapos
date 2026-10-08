<?php

/**
 * Monta a cláusula WHERE da listagem de lançamentos financeiros.
 *
 * Os filtros chegam pela URL e nenhum fragmento deles pode entrar no SQL como
 * texto: datas e tipos são validados, e os demais são escapados pelo Query
 * Builder. Extrair a montagem para cá deixa a regra testável isoladamente.
 *
 * @param  object $db       Conexão do Query Builder do CodeIgniter
 * @param  array  $filtros  Chaves aceitas: vencimento_de, vencimento_ate,
 *                          cliente, tipo e status. Todas opcionais.
 * @return string
 */
function financeiroLancamentosWhere($db, array $filtros = [])
{
    $vencimento_de = $filtros['vencimento_de'] ?? null;
    $vencimento_ate = $filtros['vencimento_ate'] ?? null;
    $cliente = $filtros['cliente'] ?? null;
    $tipo = $filtros['tipo'] ?? null;
    $status = $filtros['status'] ?? null;

    // Datas inválidas caem para o dia atual em vez de gerar erro fatal
    $dataDe = DateTime::createFromFormat('d/m/Y', (string) $vencimento_de) ?: new DateTime();
    $dataAte = DateTime::createFromFormat('d/m/Y', (string) $vencimento_ate) ?: new DateTime();

    $conditions = [
        'data_vencimento >= ' . $db->escape($dataDe->format('Y-m-d')),
        'data_vencimento <= ' . $db->escape($dataAte->format('Y-m-d')),
    ];

    if (in_array($status, ['0', '1'], true)) {
        $conditions[] = 'baixado = ' . (int) $status;
    }

    if (! empty($cliente)) {
        $conditions[] = 'cliente_fornecedor LIKE ' . $db->escape('%' . $db->escape_like_str($cliente) . '%') . " ESCAPE '!'";
    }

    if (in_array($tipo, ['receita', 'despesa'], true)) {
        $conditions[] = 'tipo = ' . $db->escape($tipo);
    }

    return implode(' AND ', $conditions);
}

/**
 * Converte dd/mm/aaaa em aaaa-mm-dd, que é o formato das colunas date.
 *
 * @param  string|null  $valor        Data vinda do formulário
 * @param  bool         $fallbackHoje Devolve a data de hoje quando o campo não
 *                                   veio preenchido ou não é uma data válida
 */
function financeiroDataBrada($valor, $fallbackHoje = true)
{
    $data = DateTime::createFromFormat('!d/m/Y', trim((string) $valor));

    // createFromFormat só devolve false quando não dá para interpretar. Data
    // com estouro de campo volta normalizada e com warning: 31/02 viraria
    // 2027-03-03 e a gravação usaria uma data que ninguém digitou. Por isso o
    // getLastErrors também é conferido.
    $erros = DateTime::getLastErrors();
    $estourou = is_array($erros) && ($erros['warning_count'] > 0 || $erros['error_count'] > 0);

    if ($data === false || $estourou) {
        return $fallbackHoje ? date('Y-m-d') : '';
    }

    return $data->format('Y-m-d');
}
