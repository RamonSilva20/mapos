<?php $this->load->view('relatorios/imprimir/partes/inicio', ['tituloDoc' => ucfirst($title)]); ?>
    <table class="rel-tabela">
        <thead>
            <tr>
                <th class="rel-th">Nome</th>
                <th class="rel-th">Documento</th>
                <th class="rel-th">Telefone</th>
                <th class="rel-th">E-mail</th>
                <th class="rel-th">Cadastro</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clientes as $c) : ?>
                <tr>
                    <td class="rel-td"><?= e($c->nomeCliente) ?></td>
                    <td class="rel-td text-center"><?= e($c->documento) ?></td>
                    <td class="rel-td text-center"><?= e($c->telefone) ?></td>
                    <td class="rel-td text-center"><?= e($c->email) ?></td>
                    <td class="rel-td text-center"><?= e(date('d/m/Y', strtotime($c->dataCadastro))) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php $this->load->view('relatorios/imprimir/partes/fim'); ?>
