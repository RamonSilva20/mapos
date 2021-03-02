<a href="<?php echo base_url() ?>index.php/usuarios/adicionar" class="btn btn-success"><i class="fas fa-user-plus"></i> Adicionar Usuário</a>

<div class="widget-box">
    <div class="widget-title">
        <span class="icon">
            <i class="fas fa-user"></i>
        </span>
        <h5>Usuários</h5>

    </div>
    <div class="widget-content nopadding">
    <!--
    <div class="widget_box_Painel2">
    -->
        <table id="tabela" width="100%" class="table_p">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Telefone</th>
                    <th>Nível</th>
                    <th>Validade</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if (!$results) {
                        echo '<tr>
                                <td colspan="5">Nenhum Usuário Cadastrado</td>
                            </tr>';
                    }
                    foreach ($results as $r) {
                        echo '<tr>';
                        echo '<td><div align="center">' . $r->idUsuarios . '</td>';
                        echo '<td><div align="center">' . $r->nome . '</td>';
                        echo '<td><div align="center">' . $r->cpf . '</td>';
                        echo '<td><div align="center">' . $r->telefone . '</td>';
                        echo '<td><div align="center">' . $r->permissao . '</td>';
                        echo '<td><div align="center">' . $r->dataExpiracao . '</td>';
                        echo '<td><div align="center">
						<a href="' . base_url() . 'index.php/usuarios/editar/' . $r->idUsuarios . '" class="btn btn-info tip-top" title="Editar Usuário"><i class="fas fa-edit"></i></a>
                                </td>';
                        echo '</tr>';
                    } ?>
            </tbody>
        </table>
    </div>
</div>

<?php echo $this->pagination->create_links(); ?>
