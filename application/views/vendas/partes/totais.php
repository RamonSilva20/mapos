<?php
/**
 * Resumo dos valores da venda (#2843) em kpi-card. O total é a soma dos
 * produtos menos o desconto (Vendas_model::totais()).
 *
 * @var array{produtos: float, servicos: float, bruto: float, desconto: float, total: float, tipo: string|null, informado: float} $totais
 * @var list<object> $produtos
 */
$itens = count($produtos);
$descricaoDesconto = match (true) {
    $totais['desconto'] <= 0 => 'Sem desconto',
    $totais['tipo'] === 'porcento' => rtrim(rtrim(number_format($totais['informado'], 2, ',', '.'), '0'), ',') . '% sobre ' . dinheiro($totais['bruto']),
    default => 'Sobre ' . dinheiro($totais['bruto']),
};
?>
<div class="grid gap-4 sm:grid-cols-3">
    <?= component('kpi-card', ['label' => 'Produtos', 'value' => dinheiro($totais['produtos']), 'icon' => 'package', 'caption' => $itens === 0 ? 'Nenhum item' : $itens . ' ' . ($itens === 1 ? 'item' : 'itens')]) ?>
    <?= component('kpi-card', ['label' => 'Desconto', 'value' => dinheiro($totais['desconto']), 'icon' => 'percent', 'caption' => $descricaoDesconto]) ?>
    <?= component('kpi-card', ['label' => 'Total', 'value' => dinheiro($totais['total']), 'icon' => 'circle-dollar-sign', 'caption' => 'Calculado pelos produtos']) ?>
</div>
