<a href="<?php echo base_url(); ?>index.php/permissoes/adicionar" class="btn btn-success"><i class="fas fa-plus"></i> Adicionar Permissão</a>

<div class="widget-box">
    <div class="widget-title">
        <span class="icon">
            <i class="fas fa-lock"></i>
        </span>
        <h5>Permissões</h5>
    </div>

    <div class="widget_content nopadding">
        <table id="tabela" class="table_p">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Data de Criação</th>
                    <th>Situação</th>
                    <th>Ação</th>
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
                        echo '<td><div align="center">' . $r->idPermissao . '</td>';
                        echo '<td>' . $r->nome . '</td>';
                        echo '<td><div align="center">' . date('d/m/Y', strtotime($r->data)) . '</td>';
                        echo '<td><div align="center">' . $situacao . '</td>';
                        echo '<td><div align="center"><a href="' . base_url() . 'index.php/permissoes/editar/' . $r->idPermissao . '" class="btn btn-info tip-top" title="Editar Permissão"><i class="fas fa-edit"></i></a>
                                <a href="#modal-excluir" role="button" data-toggle="modal" permissao="' . $r->idPermissao . '" class="btn btn-danger tip-top" title="Desativar Permissão"><i class="fas fa-trash-alt"></i></a>
                              </td>';
                        echo '</tr>';
                    } ?>
            </tbody>
        </table>
    </div>
</div>
<?php echo $this->pagination->create_links(); ?>

<!-- Modal -->
<div id="modal-excluir" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/permissoes/desativar" method="post">
        <div class="modal_header_anexos">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Desativar Permissão</h3>
        </div>
        <div class="modal-body">
            <input type="hidden" id="idPermissao" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente desativar esta permissão?</h5>
        </div>
        <div class="modal-footer">
            <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button class="btn btn-danger">Excluir</button>
        </div>
    </form>
</div>


<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', 'a', function(event) {
            var permissao = $(this).attr('permissao');
            $('#idPermissao').val(permissao);
        });
    });
</script>
