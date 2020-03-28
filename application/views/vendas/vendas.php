<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aVenda')) { ?>
    <a href="<?php echo base_url(); ?>index.php/vendas/adicionar" class="btn btn-success"><i class="fas fa-plus"></i> Adicionar Venda</a>
<?php } ?>

<div class="widget-box">
    <div class="widget-title">
        <span class="icon">
            <i class="fas fa-cash-register"></i>
        </span>
        <h5>Vendas</h5>
    </div>
    <div class="widget-content nopadding">
        <table class="table table-bordered ">
            <thead>
                <tr style="background-color: #2D335B">
                    <th>#</th>
                    <th>Data da Venda</th>
                    <th>Cliente</th>
                    <th>Faturado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                
                    if (!$results) {
                        echo '<tr>
                                <td colspan="5">Nenhuma Venda Cadastrada</td>
                            </tr>';
                    }
                    foreach ($results as $r) {
                        $dataVenda = date(('d/m/Y'), strtotime($r->dataVenda));
                        if ($r->faturado == 1) {
                            $faturado = 'Sim';
                        } else {
                            $faturado = 'Não';
                        }
                        echo '<tr>';
                        echo '<td>' . $r->idVendas . '</td>';
                        echo '<td>' . $dataVenda . '</td>';
                        echo '<td><a href="' . base_url() . 'index.php/clientes/visualizar/' . $r->idClientes . '">' . $r->nomeCliente . '</a></td>';
                        echo '<td>' . $faturado . '</td>';
                        echo '<td>';
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) {
                            echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/vendas/visualizar/' . $r->idVendas . '" class="btn tip-top" title="Ver mais detalhes"><i class="fas fa-eye"></i></a>';
                            echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/vendas/imprimir/' . $r->idVendas . '" target="_blank" class="btn btn-inverse tip-top" title="Imprimir A4"><i class="fas fa-print"></i></a>';
                            echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/vendas/imprimirTermica/' . $r->idVendas . '" target="_blank" class="btn btn-inverse tip-top" title="Imprimir Não Fiscal"><i class="fas fa-print"></i></a>';
                        }
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
                            echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/vendas/editar/' . $r->idVendas . '" class="btn btn-info tip-top" title="Editar venda"><i class="fas fa-edit"></i></a>';
                        }
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dVenda')) {
                            echo '<a href="#modal-excluir" role="button" data-toggle="modal" venda="' . $r->idVendas . '" class="btn btn-danger tip-top" title="Excluir Venda"><i class="fas fa-trash-alt"></i></a>';
                        }
                        echo '</td>';
                        echo '</tr>';
                    } ?>
                <tr>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php echo $this->pagination->create_links(); ?>

<!-- Modal -->
<div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/vendas/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Excluir Venda</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="idVenda" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir esta Venda?</h5>
        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button class="btn btn-danger">Excluir</button>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', 'a', function(event) {
            var venda = $(this).attr('venda');
            $('#idVenda').val(venda);
        });
    });
</script>
