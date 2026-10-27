<?php
$porCliente = isset($porCliente) && $porCliente;
$this->load->view('relatorios/imprimir/partes/inicio', ['tituloDoc' => ucfirst($title)]);
?>
    <table class="rel-tabela">
        <thead>
            <tr>
                <?php if ($porCliente) : ?>
                    <th class="rel-th">N. da OS</th>
                    <th class="rel-th">Cliente</th>
                <?php endif; ?>
                <th class="rel-th">Nome</th>
                <th class="rel-th">Descrição</th>
                <th class="rel-th">Preço</th>
                <?php if ($porCliente) : ?>
                    <th class="rel-th">Qtd.</th>
                    <th class="rel-th">Subtotal</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($servicos as $s) : ?>
                <tr>
                    <?php if ($porCliente) : ?>
                        <td class="rel-td text-center"><?= e($s->idOs) ?></td>
                        <td class="rel-td"><?= e($s->nomeCliente) ?></td>
                    <?php endif; ?>
                    <td class="rel-td"><?= e($s->nome) ?></td>
                    <td class="rel-td"><?= e($s->descricao) ?></td>
                    <td class="rel-td text-center">R$: <?= e($s->preco) ?></td>
                    <?php if ($porCliente) : ?>
                        <td class="rel-td text-center"><?= e($s->quantidade) ?></td>
                        <td class="rel-td text-center">R$: <?= e($s->subTotal) ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php $this->load->view('relatorios/imprimir/partes/fim'); ?>
