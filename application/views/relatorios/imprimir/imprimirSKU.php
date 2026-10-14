<?php $this->load->view('relatorios/imprimir/partes/inicio', ['tituloDoc' => 'Produtos']); ?>
    <table class="rel-tabela">
        <thead>
            <tr>
                <th class="rel-th">ID cliente</th>
                <th class="rel-th">Nome cliente</th>
                <th class="rel-th">ID produto</th>
                <th class="rel-th">Descrição produto</th>
                <th class="rel-th">Quantidade</th>
                <th class="rel-th">ID relacionado</th>
                <th class="rel-th">Data</th>
                <th class="rel-th">Preço unitário</th>
                <th class="rel-th">Preço total</th>
                <th class="rel-th">Origem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resultados as $r) : ?>
                <tr>
                    <td class="rel-td"><?= e($r->idClientes) ?></td>
                    <td class="rel-td"><?= e($r->nomeCliente) ?></td>
                    <td class="rel-td"><?= e($r->idProdutos) ?></td>
                    <td class="rel-td"><?= e($r->descricao) ?></td>
                    <td class="rel-td"><?= e($r->quantidade) ?></td>
                    <td class="rel-td"><?= e($r->idRelacionado) ?></td>
                    <td class="rel-td"><?= e(date('d/m/Y', strtotime($r->dataOcorrencia))) ?></td>
                    <td class="rel-td">R$ <?= number_format($r->preco, 2, ',', '.') ?></td>
                    <td class="rel-td">R$ <?= number_format($r->precoTotal, 2, ',', '.') ?></td>
                    <td class="rel-td"><?= e($r->origem) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php $this->load->view('relatorios/imprimir/partes/fim'); ?>
