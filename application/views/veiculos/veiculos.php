<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aVeiculo')): ?>
    <a href="<?= base_url(); ?>index.php/veiculos/adicionar" class="btn btn-success"><i class="fas fa-plus">&nbsp;</i>Adicionar Veículo</a>
<?php endif; ?>

<div class="widget-box">
    <div class="widget-title">
        <span class="icon">
            <i class="fas fa-car"></i>
        </span>
        <h5>Veículos</h5>
        <span class="label label-info"></span>
    </div>
    <div class="widget-content nopadding">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Placa</th>
                    <th>Ano do Modelo</th>
                    <th>Ano de Fabricação</th>
                    <th>Marca / Fabricante</th>
                    <th>Modelo</th>
                    <th>Chassi</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($results != null): foreach ($results as $r): ?>
                        <tr>
                            <td style="text-align: center"><?= $r->idVeiculo; ?></td>
                            <td style="text-align: center"><?= $r->nomeCliente; ?></td>
                            <td style="text-align: center"><?= $r->placa; ?></td>
                            <td style="text-align: center"><?= $r->anoModelo; ?></td>
                            <td style="text-align: center"><?= $r->anoFabricacao; ?></td>
                            <td style="text-align: center"><?= $r->marcaFabricante; ?></td>
                            <td style="text-align: center"><?= $r->modelo; ?></td>
                            <td style="text-align: center"><?= $r->chassi; ?></td>
                            <td style="text-align: center">
                                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vVeiculo')): ?>
                                    <a href="<?= base_url(); ?>index.php/veiculos/visualizar/<?= $r->idVeiculos; ?>" class="btn tip-top" title="Visualizar Veículo"><i class="far fa-eye"></i></a>
                                <?php endif; ?>

                                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eVeiculo')): ?>
                                    <a href="<?= base_url(); ?>index.php/veiculos/editar/<?= $r->idVeiculos; ?>" class="btn btn-info tip-top" title="Editar Veículo"><i class="far fa-edit"></i></a>
                                <?php endif; ?>

                                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dVeiculo')): ?>
                                    <a href="#modal-excluir" role="button" data-toggle="modal" veiculo="<?= $r->idVeiculos; ?>" class="btn btn-danger tip-top" title="Excluir Veículo"><i class="far fa-trash-alt"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center">Nenhum veículo registrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->pagination->create_links(); ?>

<div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?= base_url(); ?>index.php/veiculos/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel"><i class="fas fa-trash-alt">&nbsp;</i>Excluir Veículo</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="idProduto" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir este veículo?</h5>
        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button class="btn btn-danger">Excluir</button>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $(document).on('click', 'a', function (event) {
            var produto = $(this).attr('veiculo');
            $('#idVeiculo').val(produto);
        });
    });
</script>
