<style>
    select {
        width: 70px;
    }
</style>
<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-tags"></i>
        </span>
        <h5>Categorias</h5>
    </div>
    <div class="span12" style="margin-left: 0">
        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aLancamento')) { ?>
            <div class="span3">
                <a href="<?= base_url() ?>index.php/categorias/adicionar" class="button btn btn-mini btn-success"
                    style="max-width: 165px">
                    <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">
                        Nova Categoria
                    </span>
                </a>
            </div>
        <?php } ?>
        <form class="span9" method="get" action="<?= base_url() ?>index.php/categorias"
            style="display: flex; justify-content: flex-end;">
            <div class="span2">
                <select name="tipo" class="span12">
                    <option value="">Todos os tipos</option>
                    <option value="receita" <?= $this->input->get('tipo') == 'receita' ? 'selected' : '' ?>>Receita</option>
                    <option value="despesa" <?= $this->input->get('tipo') == 'despesa' ? 'selected' : '' ?>>Despesa</option>
                </select>
            </div>
            <div class="span3">
                <input type="text" name="pesquisa" id="pesquisa"
                    placeholder="Buscar categoria..." class="span12"
                    value="<?= $this->input->get('pesquisa') ?>">
            </div>
            <div class="span1">
                <button class="button btn btn-mini btn-warning" style="min-width: 30px">
                    <span class="button__icon"><i class='bx bx-search-alt'></i></span></button>
            </div>
        </form>
    </div>

    <div class="widget-box">
        <h5 style="padding: 3px 0"></h5>
        <div class="widget-content nopadding tab-content">
            <table id="tabela" class="table table-bordered ">
                <thead>
                    <tr>
                        <th>Cod.</th>
                        <th>Categoria</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Cadastro</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!$results) {
                        echo '<tr>
                    <td colspan="6">Nenhuma Categoria Cadastrada</td>
                  </tr>';
                    }
                    foreach ($results as $r) {
                        // Filtrar por tipo se selecionado
                        if ($this->input->get('tipo') && $r->tipo != $this->input->get('tipo')) {
                            continue;
                        }
                        
                        echo '<tr>';
                        echo '<td>' . $r->idCategorias . '</td>';
                        echo '<td>' . $r->categoria . '</td>';
                        if ($r->tipo == 'receita') {
                            echo '<td><span class="label label-success">Receita</span></td>';
                        } else {
                            echo '<td><span class="label label-important">Despesa</span></td>';
                        }
                        if ($r->status == 1) {
                            echo '<td><span class="label label-success">Ativo</span></td>';
                        } else {
                            echo '<td><span class="label label-important">Inativo</span></td>';
                        }
                        echo '<td>' . ($r->cadastro ? date('d/m/Y', strtotime($r->cadastro)) : '-') . '</td>';

                        echo '<td>';
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eLancamento')) {
                            echo '<a href="' . base_url() . 'index.php/categorias/editar/' . $r->idCategorias . '" style="margin-right: 1%" class="btn-nwe3" title="Editar Categoria"><i class="bx bx-edit bx-xs"></i></a>';
                        }
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dLancamento')) {
                            echo '<a href="#modal-excluir" role="button" data-toggle="modal" categoria="' . $r->idCategorias . '" style="margin-right: 1%" class="btn-nwe4" title="Excluir Categoria"><i class="bx bx-trash-alt bx-xs"></i></a>';
                        }
                        echo '</td>';
                        echo '</tr>';
                    } ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php echo $this->pagination->create_links(); ?>

    <!-- Modal -->
    <div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <form action="<?php echo base_url() ?>index.php/categorias/excluir" method="post">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h5 id="myModalLabel">Excluir Categoria</h5>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id" name="id" value="" />
                <h5 style="text-align: center">Deseja realmente excluir esta categoria?</h5>
                <p style="text-align: center; color: #999;">Atenção: Não será possível excluir categorias que possuem lançamentos vinculados.</p>
            </div>
            <div class="modal-footer" style="display:flex;justify-content: center">
                <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true">
                    <span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
                <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-trash'></i></span> <span class="button__text2">Excluir</span></button>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', 'a[href="#modal-excluir"]', function(event) {
            var categoria = $(this).attr('categoria');
            $('#id').val(categoria);
        });
    });
</script>

