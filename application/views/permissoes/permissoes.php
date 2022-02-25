<div class="new122" style="margin-top: 0; min-height: 100vh">
  <a href="<?php echo base_url(); ?>index.php/permissoes/adicionar" class="button btn btn-success"style="max-width: 150px">
  <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">Adicionar</span></a>

<div class="widget-box">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-lock"></i>
        </span>
        <h5>Permissões</h5>
    </div>

    <div class="widget-content nopadding tab-content">
        <table id="tabela" class="table table-bordered ">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Data de Criação</th>
                    <th>Situação</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php

                    if (!$results) {
                        echo '<tr>
                                <td colspan="5">Nenhuma Permissão foi cadastrada</td>
                                </tr>';
                    }
                    foreach ($results as $r) {
                        if ($r->situacao == 1) {
                            $situacao = 'Ativo';
                        } else {
                            $situacao = 'Inativo';
                        }
                        echo '<tr>';
                        echo '<td>' . $r->idPermissao . '</td>';
                        echo '<td>' . $r->nome . '</td>';
                        echo '<td>' . date('d/m/Y', strtotime($r->data)) . '</td>';
                        echo '<td>' . $situacao . '</td>';
                        echo '<td>
                                <a href="' . base_url() . 'index.php/permissoes/editar/' . $r->idPermissao . '" class="btn-nwe3" title="Editar permissões"><i class="bx bx-edit"></i></a>
                                <a href="#modal-excluir" role="button" data-toggle="modal" permissao="' . $r->idPermissao . '" class="btn-nwe4" title="Desativar Permissão"><i class="bx bx-notification-off" ></i></a>
                              </td>';
                        echo '</tr>';
                    } ?>
            </tbody>
        </table>
    </div>
</div>
<?php echo $this->pagination->create_links(); ?>

<!-- Modal -->
<div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/permissoes/desativar" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Desativar Permissão</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="idPermissao" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente desativar esta permissão?</h5>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true"><span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
            <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-trash'></i></span> <span class="button__text2">Excluir</span></button>
        </div>
    </form>
</div>
</div>


<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', 'a', function(event) {
            var permissao = $(this).attr('permissao');
            $('#idPermissao').val(permissao);
        });
    });
</script>
