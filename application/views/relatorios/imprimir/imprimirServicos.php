<?php $this->load->view('relatorios/imprimir/partes/inicio', ['tituloDoc' => ucfirst($title)]); ?>
    <table class="rel-tabela">
        <thead>
            <tr>
                <th class="rel-th">Nome</th>
                <th class="rel-th">Descrição</th>
                <th class="rel-th">Preço</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($servicos as $s) : ?>
                <tr>
                    <td class="rel-td"><?= e($s->nome) ?></td>
                    <td class="rel-td"><?= e($s->descricao) ?></td>
                    <td class="rel-td text-center">R$: <?= e($s->preco) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php $this->load->view('relatorios/imprimir/partes/fim'); ?>
