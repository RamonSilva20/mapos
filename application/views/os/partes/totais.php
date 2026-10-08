<?php
/**
 * Resumo dos valores da OS (#2842) em kpi-card. O total é calculado pelos
 * produtos e serviços menos o desconto (os_model->totais()).
 *
 * @var array{produtos: float, servicos: float, bruto: float, desconto: float, total: float, tipo: string|null, informado: float} $totais
 * @var list<object> $produtos
 * @var list<object> $servicos
 */
$itens = static fn (int $n, string $um, string $varios) => $n === 0 ? 'Nenhum' : $n . ' ' . ($n === 1 ? $um : $varios);
$descricaoDesconto = match (true) {
    $totais['desconto'] <= 0 => 'Sem desconto',
    $totais['tipo'] === 'porcento' => rtrim(rtrim(number_format($totais['informado'], 2, ',', '.'), '0'), ',') . '% sobre ' . dinheiro($totais['bruto']),
    default => 'Sobre ' . dinheiro($totais['bruto']),
};
?>
<div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
    <?= component('kpi-card', ['label' => 'Produtos', 'value' => dinheiro($totais['produtos']), 'icon' => 'package', 'caption' => $itens(count($produtos), 'item', 'itens')]) ?>
    <?= component('kpi-card', ['label' => 'Serviços', 'value' => dinheiro($totais['servicos']), 'icon' => 'wrench', 'caption' => $itens(count($servicos), 'serviço', 'serviços')]) ?>
    <?= component('kpi-card', ['label' => 'Desconto', 'value' => dinheiro($totais['desconto']), 'icon' => 'percent', 'caption' => $descricaoDesconto]) ?>
    <?= component('kpi-card', ['label' => 'Total', 'value' => dinheiro($totais['total']), 'icon' => 'circle-dollar-sign', 'caption' => 'Calculado pelos produtos e serviços']) ?>
</div>
