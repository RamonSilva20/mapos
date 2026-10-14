<?php
/**
 * Resumo dos valores da venda (#2843) em kpi-card. O total é a soma dos
 * produtos menos o desconto (Vendas_model::totais()).
 *
 * @var array{produtos: float, servicos: float, bruto: float, desconto: float, total: float, tipo: string|null, informado: float, descontoItens: float} $totais
 * @var list<object> $produtos
 */
$itens = count($produtos);
$descricaoGeral = match (true) {
    $totais['desconto'] <= 0 => null,
    $totais['tipo'] === 'porcento' => rtrim(rtrim(number_format($totais['informado'], 2, ',', '.'), '0'), ',') . '% sobre ' . dinheiro($totais['bruto']),
    default => 'Sobre ' . dinheiro($totais['bruto']),
};
// Desconto geral no valor do cartão (Produtos + Serviços - Desconto = Total). O
// desconto individual dos itens (#2880) já está abatido nos cartões de valores
// e só aparece na legenda.
$descricaoDesconto = match (true) {
    $totais['descontoItens'] > 0 && $descricaoGeral !== null => $descricaoGeral . ' · Nos itens: ' . dinheiro($totais['descontoItens']),
    $totais['descontoItens'] > 0 => 'Sem desconto geral · Nos itens: ' . dinheiro($totais['descontoItens']),
    $descricaoGeral !== null => $descricaoGeral,
    default => 'Sem desconto',
};
?>
<div class="grid gap-4 sm:grid-cols-3">
    <?= component('kpi-card', ['label' => 'Produtos', 'value' => dinheiro($totais['produtos']), 'icon' => 'package', 'caption' => $itens === 0 ? 'Nenhum item' : $itens . ' ' . ($itens === 1 ? 'item' : 'itens')]) ?>
    <?= component('kpi-card', ['label' => 'Desconto', 'value' => dinheiro($totais['desconto']), 'icon' => 'percent', 'caption' => $descricaoDesconto]) ?>
    <?= component('kpi-card', ['label' => 'Total', 'value' => dinheiro($totais['total']), 'icon' => 'circle-dollar-sign', 'caption' => 'Calculado pelos produtos']) ?>
</div>
