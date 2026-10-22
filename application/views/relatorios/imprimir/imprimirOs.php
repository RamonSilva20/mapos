<?php $this->load->view('relatorios/imprimir/partes/inicio', ['tituloDoc' => ucfirst($title)]); ?>
    <table class="rel-tabela">
        <thead>
            <tr>
                <th class="rel-th">OS</th>
                <th class="rel-th">Cliente</th>
                <th class="rel-th">Status</th>
                <th class="rel-th">Data</th>
                <th class="rel-th">Descrição</th>
                <th class="rel-th">Total produtos</th>
                <th class="rel-th">Total serviços</th>
                <th class="rel-th">Total</th>
                <th class="rel-th">Desconto</th>
                <th class="rel-th">Total com desconto</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($os as $c) : ?>
                <tr>
                    <td class="rel-td text-center"><?= e($c->idOs) ?></td>
                    <td class="rel-td"><?= e($c->nomeCliente) ?></td>
                    <td class="rel-td text-center"><?= e($c->status) ?></td>
                    <td class="rel-td text-center"><?= e(date('d/m/Y', strtotime($c->dataInicial))) ?></td>
                    <td class="rel-td"><?= printSafeHtml($c->descricaoProduto, true) ?></td>
                    <td class="rel-td text-center">R$: <?= number_format($c->total_produto, 2, ',', '.') ?></td>
                    <td class="rel-td text-center">R$: <?= number_format($c->total_servico, 2, ',', '.') ?></td>
                    <td class="rel-td text-center">R$: <?= number_format($c->total_produto + $c->total_servico, 2, ',', '.') ?></td>
                    <td class="rel-td text-center"><?= e(($c->tipo_desconto == 'real' ? 'R$ ' : '') . $c->desconto . ($c->tipo_desconto == 'porcento' ? ' %' : '')) ?></td>
                    <td class="rel-td text-center">R$: <?= number_format($c->valor_desconto != 0 ? $c->valor_desconto : $c->total_produto + $c->total_servico, 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td class="rel-total" colspan="5"></td>
                <td class="rel-total text-center">R$: <?= number_format($total_produtos, 2, ',', '.') ?></td>
                <td class="rel-total text-center">R$: <?= number_format($total_servicos, 2, ',', '.') ?></td>
                <td class="rel-total text-center">R$: <?= number_format($total_produtos + $total_servicos, 2, ',', '.') ?></td>
                <td class="rel-total"></td>
                <td class="rel-total text-center">R$: <?= number_format($total_geral, 2, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>
<?php $this->load->view('relatorios/imprimir/partes/fim'); ?>
