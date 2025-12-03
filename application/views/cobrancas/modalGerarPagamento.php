<?php
$this->load->config('payment_gateways');
?>

<script>
    var paymentGatewaysConfig = JSON.parse("<?php echo addslashes(json_encode($this->config->item('payment_gateways'))); ?>");
</script>

<div class="modal fade" id="modal-gerar-pagamento" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="form-gerar-cobranca" name="cobranca" method="post" action="<?php echo base_url() . 'index.php/cobrancas/adicionar'; ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" >Escolher Forma de Pagamento</h4>
                </div>
                <div class="modal-body">
                    <div id="forma-pag" class="">
                        <div class="form-group">
                            <input value="<?php echo $id ?>" name="id" hidden>
                            <input value="<?php echo $tipo ?>" name="tipo" hidden>
                            <label for="gateway_de_pagamento">Gateway de Pagamento: </label>
                            <select id="gateway_de_pagamento" class="form-control span12" name="gateway_de_pagamento" required>
                                <option value="" selected>Escolha o gateway de pagamento</option>
                                <?php foreach ($this->config->item('payment_gateways') as $paymentGateway) : ?>
                                    <option value="<?php echo $paymentGateway['library_name']; ?>"><?php echo $paymentGateway['name']; ?></option>
                                <?php endforeach ?>
                            </select>
                            <label id="label_forma_pagamento" for="forma_pagamento" hidden>Forma de Pagamento: </label>
                            <select id="forma_pagamento" class="form-control span12" name="forma_pagamento" required hidden>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div>
                        <button id="payment" type="submit" class="button btn btn-mini btn-info" style="float: right;">
                        <span class="button__icon"><i class='bx bx-qr'></i></span><span class="button__text">Gerar Pagamento</span></i></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $("#forma_pagamento").hide();
    $("#label_forma_pagamento").hide();
</script>
<script src="<?= base_url('assets/js/script-payments.js'); ?>"></script>
