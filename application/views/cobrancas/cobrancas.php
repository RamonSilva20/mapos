<div class="new122">
    <div class="widget-box">
        <div class="widget-title" style="margin: -20px 0 0">
            <span class="icon">
                <i class="fas fa-cash-register"></i>
            </span>
            <h5>Cobranças</h5>
        </div>
        <div class="widget-content nopadding tab-content">
            <table id="tabela" class="table table-bordered ">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Gateway</th>
                        <th>Tipo</th>
                        <th>Data de Vencimento</th>
                        <th>Referência</th>
                        <th>Status</th>
                        <th>Valor</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!$results) {
                        echo '<tr>
                                <td colspan="8">Nenhuma cobrança Cadastrada</td>
                            </tr>';
                    }
                    foreach ($results as $r) {
                        $dataVenda = date(('d/m/Y'), strtotime($r->expire_at));
                        $cobrancaStatus = getCobrancaTransactionStatus(
                            $this->config->item('payment_gateways'),
                            $r->payment_gateway,
                            $r->status
                        );

                        echo '<tr>';
                        echo '<td>' . $r->idCobranca . '</td>';
                        echo '<td>' . $r->payment_gateway . '</td>';
                        echo '<td>' . $r->payment_method . '</td>';
                        echo '<td>' . $dataVenda . '</td>';

                        if ($r->os_id != '') {
                            echo '<td><a href="' . base_url() . 'index.php/os/visualizar/' . $r->os_id . '"> Ordem de Serviço: #' . $r->os_id . '</a></td>';
                        }
                        if ($r->vendas_id != '') {
                            echo '<td><a href="' . base_url() . 'index.php/vendas/visualizar/' . $r->vendas_id . '"> Venda: #' . $r->vendas_id . '</a></td>';
                        }

                        echo '<td>' .  $cobrancaStatus . '</td>';
                        echo '<td>R$ ' . number_format($r->total / 100, 2, ',', '.') . '</td>';
                        echo '<td>';
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCobranca')) {
                            echo '<a style="margin-right: 1%" href="#modal-cancelar" role="button" data-toggle="modal" cancela_id="' . $r->idCobranca . '" class="btn-nwe4" title="Cancelar Cobrança"><i class="bx bx-x" ></i></a>';
                            echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/cobrancas/atualizar/' . $r->idCobranca . '" class="btn-nwe" title="Atualizar Cobrança"><i class="bx bx-refresh"></i></a>';
                            echo '<a style="margin-right: 1%" href="#modal-confirmar" role="button" data-toggle="modal" confirma_id="' . $r->idCobranca . '" class="btn-nwe3" title="Confirmar pagamento"><i class="bx bx-check"></i></a>';
                            echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/cobrancas/visualizar/' . $r->idCobranca . '" class="btn-nwe2" title="Ver mais detalhes"><i class="bx bx-show" ></i></a>';
                            echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/cobrancas/enviarEmail/' . $r->idCobranca . '" class="btn-nwe5" title="Enviar por E-mail"><i class="bx bx-envelope" ></i></a>';
                        }
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eCobranca') && $r->barcode != '') {
                            echo '<a style="margin-right: 1%" href="' . $r->link . '" target="_blank" class="btn-nwe" title="Visualizar boleto"><i class="bx bx-barcode" ></i></a>';
                        }
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dCobranca')) {
                            echo '<a href="#modal-excluir" role="button" data-toggle="modal" excluir_id="' . $r->idCobranca . '" class="btn-nwe4" title="Excluir Cobrança"><i class="bx bx-trash-alt"></i></a>';
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
        <form action="<?php echo base_url() ?>index.php/cobrancas/excluir" method="post">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h5 id="myModalLabel">Excluir cobrança</h5>
            </div>
            <div class="modal-body">
                <input type="hidden" id="excluir_id" name="excluir_id" value="" />
                <h5 style="text-align: center">Deseja realmente excluir esta cobrança? A cobrança será cancelada.</h5>
            </div>
            <div class="modal-footer" style="display:flex;justify-content: center">
                <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true"><span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
                <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-trash'></i></span> <span class="button__text2">Excluir</span></button>
            </div>
        </form>
    </div>

    <div id="modal-confirmar" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <form action="<?php echo base_url() ?>index.php/cobrancas/confirmarpagamento" method="post">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h5 id="myModalLabel">Confirmar pagamento</h5>
            </div>
            <div class="modal-body">
                <input type="hidden" id="confirma_id" name="confirma_id" value="" />
                <h5 style="text-align: center">Deseja realmente confirmar pagamento desta cobrança?</h5>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
                <button class="btn btn-success">Confirmar</button>
            </div>
        </form>
    </div>

    <div id="modal-cancelar" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <form action="<?php echo base_url() ?>index.php/cobrancas/cancelar" method="post">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h5 id="myModalLabel">Cancelar cobrança</h5>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cancela_id" name="cancela_id" value="" />
                <h5 style="text-align: center">Deseja realmente Cancelar esta cobrança?</h5>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
                <button class="btn btn-danger">Confirmar</button>
            </div>
        </form>
    </div>
</div>
    <script type="text/javascript">
        $(document).ready(function() {
            $(document).on('click', 'a', function(event) {
                var cobranca = $(this).attr('excluir_id');
                $('#excluir_id').val(cobranca);
            });

            $(document).on('click', 'a', function(event) {
                var cobranca = $(this).attr('confirma_id');
                $('#confirma_id').val(cobranca);
            });

            $(document).on('click', 'a', function(event) {
                var cobranca = $(this).attr('cancela_id');
                $('#cancela_id').val(cobranca);
            });
        });
    </script>