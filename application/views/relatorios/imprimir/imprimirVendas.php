<?php
$total = 0;
foreach ($vendas as $v) {
    $total += $v->valor_desconto != 0 ? $v->valor_desconto : $v->valorTotal;
}
$this->load->view('relatorios/imprimir/partes/inicio', ['tituloDoc' => ucfirst($title)]);
?>
    <table class="rel-tabela">
        <thead>
            <tr>
                <th class="rel-th">#</th>
                <th class="rel-th">Cliente</th>
                <th class="rel-th">Vendedor</th>
                <th class="rel-th">Data</th>
                <th class="rel-th">Total</th>
                <th class="rel-th">Desconto</th>
                <th class="rel-th">Total com desconto</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vendas as $v) : ?>
                <tr>
                    <td class="rel-td text-center"><?= e($v->idVendas) ?></td>
                    <td class="rel-td"><?= e($v->nomeCliente) ?></td>
                    <td class="rel-td text-center"><?= e($v->nome) ?></td>
                    <td class="rel-td text-center"><?= e(date('d/m/Y', strtotime($v->dataVenda))) ?></td>
                    <td class="rel-td text-center">R$: <?= number_format($v->valorTotal, 2, ',', '.') ?></td>
                    <td class="rel-td text-center"><?= e(($v->tipo_desconto == 'real' ? 'R$ ' : '') . $v->desconto . ($v->tipo_desconto == 'porcento' ? ' %' : '')) ?></td>
                    <td class="rel-td text-center">R$: <?= number_format($v->valor_desconto != 0 ? $v->valor_desconto : $v->valorTotal, 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td class="rel-total text-right" colspan="6">TOTAL</td>
                <td class="rel-total text-center">R$: <?= number_format($total, 2, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>
<?php $this->load->view('relatorios/imprimir/partes/fim'); ?>
