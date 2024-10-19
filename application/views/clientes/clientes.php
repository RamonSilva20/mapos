<?php if (!$results): ?>
    <div class="sem_registro" id="info_cadastro" style="display: block;">
        <p class="titulo_sr">Clientes / Fornecedores</p>
        <p class="corpo_sr">Não há clientes ou fornecedores registrados no sistema. Este procedimento é essencial para criação de vínculo na inclusão de vendas e na abertura de ordens de serviço.</p>
            <a class="botao_sr" href="<?= base_url() ?>index.php/clientes/adicionar" role="button">
                Novo cliente ou fornecedor
            </a>
    </div>
<?php else: ?>

    <div class="widget-box">
        <div class="widget-title">
            <div class="topo_modulo_tabela">
                <h5>Clientes / Fornecedores</h5>
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
                        <th>Contato</th>
                        <th>CPF/CNPJ</th>
                        <th>Telefone</th>
                        <th>Celular</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                        <tr>
                            <td><?= $r->idClientes; ?></td>
                            <td>
                                <a href="<?= base_url('index.php/clientes/visualizar/' . $r->idClientes); ?>" style="margin-right: 1%;">
                                    <?= $r->nomeCliente; ?>
                                </a>
                            </td>
                            <td><?= $r->contato; ?></td>
                            <td><?= $r->documento; ?></td>
                            <td><?= $r->telefone; ?></td>
                            <td><?= $r->celular; ?></td>
                            <td><?= $r->email; ?></td>
                            <td>
                                <?= $r->fornecedor == 1 ? '<span class="label label-primary">Fornecedor</span>' : '<span class="label label-success">Cliente</span>'; ?>
                            </td>
                            <td>
                                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')): ?>
                                    <a href="<?= base_url('index.php/clientes/visualizar/' . $r->idClientes); ?>" style="margin-right: 1%" class="btn-nwe" title="Ver mais detalhes">
                                        <i class="bx bx-show bx-xs"></i>
                                    </a>
                                    <a href="<?= base_url('index.php/mine?e=' . $r->email); ?>" target="new" style="margin-right: 1%" class="btn-nwe2" title="Área do cliente">
                                        <i class="bx bx-key bx-xs"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')): ?>
                                    <a href="<?= base_url('index.php/clientes/editar/' . $r->idClientes); ?>" style="margin-right: 1%" class="btn-nwe3" title="Editar Cliente">
                                        <i class="bx bx-edit bx-xs"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dCliente')): ?>
                                    <a href="#modal-excluir" role="button" data-toggle="modal" cliente="<?= $r->idClientes; ?>" style="margin-right: 1%" class="btn-nwe4" title="Excluir Cliente">
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
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aCliente')): ?>
                <div class="span2">
                    <a href="<?= base_url('index.php/clientes/adicionar'); ?>" class="button botao_modulo_tabela btn btn-success">
                        <span class="button__icon"><i class="bx bx-plus-circle"></i></span>
                        <span class="button__text2">Cliente / Fornecedor</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php echo $this->pagination->create_links(); ?>

<!-- Modal -->
<div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/clientes/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Excluir Cliente</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="idCliente" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir este cliente e os dados associados a ele (OS,
                Vendas, Receitas)?</h5>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true"><span class="button__icon"><i
                        class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
            <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-trash'></i></span> <span
                    class="button__text2">Excluir</span></button>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', 'a', function(event) {
            var cliente = $(this).attr('cliente');
            $('#idCliente').val(cliente);
        });
    });
</script>
