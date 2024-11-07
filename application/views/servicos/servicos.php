<?php if (!$results): ?>
    <div class="sem_registro" id="info_cadastro" style="display: block;">
        <p class="titulo_sr">Serviços</p>
        <p class="corpo_sr">Não há serviços cadastrados no sistema. O pré cadastro de serviços é essencial para agilizar a criação de Ordens de Serviço.</p>
        <a class="botao_sr" href="<?= base_url() ?>index.php/servicos/adicionar" role="button">
            Novo Serviço
        </a>
    </div>
<?php else: ?>

    <div class="widget-box">
        <div class="widget-title">
            <div class="topo_modulo_tabela">
                <h5>Serviços</h5>
                <div class="form-group">
                    <div class="input-group">
                        <input type="text" id="customSearch" class="form-control" placeholder="Buscar...">
                        <select id="customLength" name="tabela_length" class="form-control select_modulo_tabela" style="border-left: none;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="widget-content nopadding tab-content">
            <table id="tabela" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Cod.</th>
                        <th>Nome</th>
                        <th>Preço</th>
                        <th>Descrição</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                        <tr>
                            <td><?= $r->idServicos; ?></td>
                            <td><?= $r->nome; ?></td>
                            <td><?= number_format($r->preco, 2, ',', '.'); ?></td>
                            <td><?= $r->descricao; ?></td>
                            <td>
                                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eServico')): ?>
                                    <a href="<?= base_url('index.php/servicos/editar/' . $r->idServicos); ?>" class="btn-nwe3" title="Editar Serviço">
                                        <i class="bx bx-edit bx-xs"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dServico')): ?>
                                    <a href="#modal-excluir" role="button" data-toggle="modal" servico="<?= $r->idServicos; ?>" class="btn-nwe4" title="Excluir Serviço">
                                        <i class="bx bx-trash-alt bx-xs"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="span12" style="margin-left: 0;">
        <div style="display: flex; justify-content: flex-end;">
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aServico')): ?>
                <div class="span2">
                    <a href="<?= base_url('index.php/servicos/adicionar'); ?>" class="button botao_modulo_tabela btn btn-success">
                        <span class="button__icon"><i class="bx bx-plus-circle"></i></span>
                        <span class="button__text2">Novo Serviço</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php endif; ?>

<?php echo $this->pagination->create_links(); ?>

<!-- Modal -->
<div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/servicos/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Excluir Serviço</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="idServico" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir este serviço?</h5>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true"><span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
            <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-trash'></i></span> <span class="button__text2">Excluir</span></button>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', 'a', function(event) {
            var servico = $(this).attr('servico');
            $('#idServico').val(servico);
        });
    });
</script>
