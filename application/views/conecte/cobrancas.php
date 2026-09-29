<div class="widget-box">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-cash-register"></i>
        </span>
        <h5>Cobranças</h5>
    </div>
    <div class="widget-content nopadding tab-content">
        <div class="c-table-responsive">
        <table id="tabela" class="table table-bordered ">
            <thead>
                <tr>
                    <th>#</th>
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
                                <td colspan="5">Nenhuma cobrança Cadastrada</td>
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
                    echo '<td>' . esc($r->charge_id) . '</td>';
                    echo '<td>' . esc($dataVenda) . '</td>';

                    if ($r->os_id != '') {
                        echo '<td><a href="' . esc_url(base_url() . 'index.php/os/visualizar/' . rawurlencode((string) $r->os_id)) . '">  Ordem de Serviço: #' . esc($r->os_id) . '</a></td>';
                    }
                    if ($r->vendas_id != '') {
                        echo '<td><a href="' . esc_url(base_url() . 'index.php/vendas/visualizar/' . rawurlencode((string) $r->vendas_id)) . '">  Venda: #' . esc($r->vendas_id) . '</a></td>';
                    }

                    echo '<td>' . esc($cobrancaStatus) . '</td>';
                    echo '<td>R$ ' . number_format($r->total / 100, 2, ',', '.') . '</td>';
                    echo '<td>';
                    if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCobranca')) {
                        echo '<a style="margin-right: 1%" href="' . esc_url(base_url() . 'index.php/mine/atualizarcobranca/' . rawurlencode((string) $r->idCobranca)) . '"  class="btn-nwe" title="Atualizar Cobrança"><i class="bx bx-refresh"></i></a>';
                    }

                    if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eCobranca')) {
                        echo '<a style="margin-right: 1%" href="' . esc_url($r->link) . '"  target="_blank" rel="noopener noreferrer" class="btn-nwe" title="Visualizar boleto"><i class="bx bx-barcode" ></i></a>';
                        echo '<a style="margin-right: 1%" href="' . esc_url(base_url() . 'index.php/mine/enviarcobranca/' . rawurlencode((string) $r->idCobranca)) . '" class="btn-nwe2" title="Reenviar por email"><i class="bx bx-mail-send" ></i></a>';
                    }
                    echo '<a style="margin-right: 1%" href="' . esc_url($r->link) . '"  target="_blank" rel="noopener noreferrer" class="btn-nwe" title="Visualizar boleto"><i class="bx bx-barcode" ></i></a>';
                    echo '</td>';
                    echo '</tr>';
                } ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?= $this->pagination->create_links() ?>

<!-- Modal -->
<div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?= base_url() ?>index.php/cobrancas/excluir" method="post">
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
    <form action="<?= base_url() ?>index.php/cobrancas/confirmarpagamento" method="post">
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
    <form action="<?= base_url() ?>index.php/cobrancas/cancelar" method="post">
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
