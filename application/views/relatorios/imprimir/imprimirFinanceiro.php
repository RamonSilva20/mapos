<?php
$totalReceita = 0;
$totalDespesa = 0;
foreach ($lancamentos as $l) {
    $valor = $l->valor_desconto != 0 ? $l->valor_desconto : $l->valor;
    if ($l->tipo == 'receita') {
        $totalReceita += $valor;
    } else {
        $totalDespesa += $valor;
    }
}
$this->load->view('relatorios/imprimir/partes/inicio', ['tituloDoc' => 'Relatório Financeiro']);
?>
    <table class="rel-tabela">
        <thead>
            <tr>
                <th class="rel-th">Cliente/Fornecedor</th>
                <th class="rel-th">Tipo</th>
                <th class="rel-th">Valor</th>
                <th class="rel-th">Desconto</th>
                <th class="rel-th">Valor total</th>
                <th class="rel-th">Vencimento</th>
                <th class="rel-th">Pagamento</th>
                <th class="rel-th">Forma de pgto.</th>
                <th class="rel-th">Situação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lancamentos as $l) : ?>
                <tr>
                    <td class="rel-td"><?= e($l->cliente_fornecedor) ?></td>
                    <td class="rel-td"><?= e($l->tipo) ?></td>
                    <td class="rel-td">R$ <?= number_format($l->valor, 2, ',', '.') ?></td>
                    <td class="rel-td"><?= e(($l->tipo_desconto == 'real' ? 'R$ ' : '') . number_format($l->desconto, 2, ',', '.') . ($l->tipo_desconto == 'porcento' ? ' %' : '')) ?></td>
                    <td class="rel-td">R$ <?= number_format($l->valor_desconto != 0 ? $l->valor_desconto : $l->valor, 2, ',', '.') ?></td>
                    <td class="rel-td"><?= e(date('d/m/Y', strtotime($l->data_vencimento))) ?></td>
                    <td class="rel-td"><?= e(date('d/m/Y', strtotime($l->data_pagamento))) ?></td>
                    <td class="rel-td"><?= e($l->forma_pgto) ?></td>
                    <td class="rel-td"><?= e($l->baixado == 1 ? 'Pago' : 'Pendente') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td class="rel-total text-right text-success" colspan="5">Total receitas:</td>
                <td class="rel-total text-success" colspan="4">R$ <?= number_format($totalReceita, 2, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="rel-total text-right text-danger" colspan="5">Total despesas:</td>
                <td class="rel-total text-danger" colspan="4">R$ <?= number_format($totalDespesa, 2, ',', '.') ?></td>
            </tr>
            <tr>
                <td class="rel-total text-right" colspan="5">Saldo:</td>
                <td class="rel-total" colspan="4">R$ <?= number_format($totalReceita - $totalDespesa, 2, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>
<?php $this->load->view('relatorios/imprimir/partes/fim'); ?>
