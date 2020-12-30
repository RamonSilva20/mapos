 <div class="widget-box">
    <div class="widget-title">
        <span class="icon">
            <i class="fas fa-cash-register"></i>
        </span>
        <h5>Cobranças</h5>
    </div>
    <div class="widget-content nopadding tab-content">
        <table class="table table-bordered ">
            <thead>
                <tr style="background-color: #2D335B">
                    <th>#</th>
                    <th>Data de Vencimento</th>
                    <th>Referência</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php

                    if (!$results) {
                        echo '<tr>
                                <td colspan="5">Nenhuma cobrança Cadastrada</td>
                            </tr>';
                    }
                    foreach ($results as $r) {
                        $dataVenda = date(('d/m/Y'), strtotime($r->expire_at));
                        

                        $transactions_status = array(
                            "new" => "Cobrança / Assinatura gerada.",
                            "waiting" => "Aguardando a confirmação do pagamento.",
                            "paid" => "Pagamento confirmado.",
                            "unpaid" => "Não foi possível confirmar o pagamento da cobrança.",
                            "refunded" => "Pagamento devolvido pelo lojista ou pelo intermediador Gerencianet. ",
                            "contested" => "Pagamento em processo de contestação.",
                            "canceled" => "Cobrança/Assinatura cancelada pelo vendedor ou pelo pagador. ",
                            "settled" => "Cobrança/Pagamento foi confirmada manualmente. ",
                            "link" => "Link de pagamento.",
                            "expired" => "Link/Assinatura de pagamento expirado.",
                            "active" => "Assinatura ativa. Todas as cobranças estão sendo geradas.",
                            "finished" => "Carnê está finalizado.",
                            "up_to_date" => "Carnê encontra-se em dia.",
                        );
                      
                        echo '<tr>';
                        echo '<td>' . $r->charge_id . '</td>';
                        echo '<td>' . $dataVenda . '</td>';
                        echo '<td><a href="' . base_url() . 'index.php/vendas/visualizar/' . $r->vendas_id . '">  Venda: #' . $r->vendas_id . '</a></td>';
                        echo '<td>' .  $transactions_status[$r->status] . '</td>';
                        echo '<td>';
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) {
                            echo '<a style="margin-right: 1%" href="#modal-cancelar" role="button" data-toggle="modal" cancela_id="' . $r->charge_id . '" class="btn tip-top" title="Cancelar cobrança"><i class="fas fa-power-off"></i></a>';
                            echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/cobrancas/atualizar/' . $r->charge_id . '" class="btn btn-inverse tip-top" title="Atualizar Cobrança"><i class="fas fa-sync"></i></a>';
                            echo '<a style="margin-right: 1%" href="#modal-confirmar" role="button" data-toggle="modal" confirma_id="' . $r->charge_id . '" class="btn btn-inverse tip-top" title="Confirmar pagamento"><i class="fas fa-check-circle"></i></a>';
                        }
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda') && $r->barcode != '') {
                            echo '<a style="margin-right: 1%" href="' . $r->payment_url . '" class="btn btn-info tip-top" title="Visualizar boleto"><i class="fas fa-barcode"></i></a>';
                        }
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dVenda')) {
                            echo '<a href="#modal-excluir" role="button" data-toggle="modal" charge_id="' . $r->charge_id . '" class="btn btn-danger tip-top" title="Excluir cobrança"><i class="fas fa-trash-alt"></i></a>';
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
    <form action="<?php echo base_url() ?>index.php/cobrancas/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Excluir cobrança</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="charge_id" name="charge_id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir esta cobrança? A cobrança será cancelada.</h5>
        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button class="btn btn-danger">Excluir</button>
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

<script type="text/javascript">
    $(document).ready(function() {
        
        $(document).on('click', 'a', function(event) {
            var cobranca = $(this).attr('charge_id');
            $('#charge_id').val(cobranca);
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
