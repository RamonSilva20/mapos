<?php $this->load->view('relatorios/imprimir/partes/inicio', ['tituloDoc' => ucfirst($title)]); ?>
    <table class="rel-tabela">
        <thead>
            <tr>
                <th class="rel-th">Nome</th>
                <th class="rel-th">UN</th>
                <th class="rel-th">Preço compra</th>
                <th class="rel-th">Preço venda</th>
                <th class="rel-th">Estoque</th>
                <th class="rel-th">Valor (venda)</th>
                <th class="rel-th">Valor (compra)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $p) : ?>
                <tr>
                    <td class="rel-td"><?= e($p->descricao) ?></td>
                    <td class="rel-td text-center"><?= e($p->unidade) ?></td>
                    <td class="rel-td text-center">R$: <?= e($p->precoCompra) ?></td>
                    <td class="rel-td text-center">R$: <?= e($p->precoVenda) ?></td>
                    <td class="rel-td text-center"><?= e($p->estoque) ?></td>
                    <td class="rel-td text-center">R$: <?= number_format($p->valorEstoque, 2, ',', '.') ?></td>
                    <td class="rel-td text-center">R$: <?= number_format(floatval($p->estoque) * floatval($p->precoCompra), 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="rel-tabela mt-3">
        <tr>
            <th class="rel-th">Itens em estoque</th>
            <th class="rel-th">Valor do estoque (venda)</th>
            <th class="rel-th">Valor do estoque (compra)</th>
        </tr>
        <tr>
            <td class="rel-total text-center"><?= e(array_sum(array_column($produtos, 'estoque'))) ?></td>
            <td class="rel-total text-center">R$: <?= number_format(array_sum(array_column($produtos, 'valorEstoque')), 2, ',', '.') ?></td>
            <td class="rel-total text-center">R$: <?= number_format(array_sum(array_column($produtos, 'valorEstoqueR')), 2, ',', '.') ?></td>
        </tr>
    </table>
<?php $this->load->view('relatorios/imprimir/partes/fim'); ?>
