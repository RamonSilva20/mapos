<div class="new122">
  <a href="<?php echo base_url() ?>index.php/usuarios/adicionar" class="button btn btn-success" style="max-width: 160px">
  <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">Adicionar Usuário</span></a>

<div class="widget-box">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-user"></i>
        </span>
        <h5>Usuários</h5>

    </div>
    <div class="widget-content nopadding tab-content">
        <table id="tabela" class="table table-bordered ">
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
                        echo '<td>' . $r->idUsuarios . '</td>';
                        echo '<td>' . $r->nome . '</td>';
                        echo '<td>' . $r->cpf . '</td>';
                        echo '<td>' . $r->telefone . '</td>';
                        echo '<td>' . $r->permissao . '</td>';
                        echo '<td>' . $r->dataExpiracao . '</td>';
                        echo '<td>
                                <a href="' . base_url() . 'index.php/usuarios/editar/' . $r->idUsuarios . '" class="btn-nwe3" title="Editar OS"><i class="bx bx-edit"></i></a>
                                </td>';
                        echo '</tr>';
                    } ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<?php echo $this->pagination->create_links(); ?>
