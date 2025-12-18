<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-exchange-alt"></i>
                </span>
                <h5>Transferência entre Contas</h5>
            </div>
            <?php if ($custom_error != '') {
                echo '<div class="alert alert-danger">' . $custom_error . '</div>';
            } ?>
            <form action="<?php echo current_url(); ?>" id="formTransferencia" method="post" class="form-horizontal">
                <div class="widget-content nopadding tab-content">
                    <div class="span6">
                        <div class="control-group">
                            <label for="conta_origem" class="control-label">Conta de Origem<span class="required">*</span></label>
                            <div class="controls">
                                <select id="conta_origem" name="conta_origem">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($contas as $c) { ?>
                                        <option value="<?php echo $c->idContas; ?>"><?php echo $c->conta; ?> (Saldo: R$ <?php echo number_format($c->saldo ?? 0, 2, ',', '.'); ?>)</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="conta_destino" class="control-label">Conta de Destino<span class="required">*</span></label>
                            <div class="controls">
                                <select id="conta_destino" name="conta_destino">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($contas as $c) { ?>
                                        <option value="<?php echo $c->idContas; ?>"><?php echo $c->conta; ?> (Saldo: R$ <?php echo number_format($c->saldo ?? 0, 2, ',', '.'); ?>)</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="valor" class="control-label">Valor<span class="required">*</span></label>
                            <div class="controls">
                                <input id="valor" type="text" name="valor" class="money" placeholder="0,00" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="data" class="control-label">Data<span class="required">*</span></label>
                            <div class="controls">
                                <input id="data" type="text" name="data" autocomplete="off" class="datepicker" value="<?php echo date('d/m/Y'); ?>" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="observacoes" class="control-label">Observações</label>
                            <div class="controls">
                                <textarea id="observacoes" name="observacoes" rows="3" placeholder="Observações sobre a transferência..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <div class="span12">
                        <div class="span6 offset3" style="display:flex;justify-content: center">
                            <button type="submit" class="button btn btn-mini btn-success"><span class="button__icon"><i class='bx bx-transfer'></i></span> <span class="button__text2">Transferir</span></button>
                            <a title="Voltar" class="button btn btn-warning" href="<?php echo site_url() ?>/contas"><span class="button__icon"><i class="bx bx-undo"></i></span> <span class="button__text2">Voltar</span></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.money').mask('#.##0,00', {reverse: true});
        $(".datepicker").datepicker({
            dateFormat: 'dd/mm/yy'
        });
        
        $('#formTransferencia').validate({
            rules: {
                conta_origem: {
                    required: true
                },
                conta_destino: {
                    required: true,
                    different: '#conta_origem'
                },
                valor: {
                    required: true
                },
                data: {
                    required: true
                },
            },
            messages: {
                conta_origem: {
                    required: 'Campo Requerido.'
                },
                conta_destino: {
                    required: 'Campo Requerido.',
                    different: 'A conta de destino deve ser diferente da conta de origem.'
                },
                valor: {
                    required: 'Campo Requerido.'
                },
                data: {
                    required: 'Campo Requerido.'
                },
            },
            errorClass: "help-inline",
            errorElement: "span",
            highlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').addClass('error');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error');
                $(element).parents('.control-group').addClass('success');
            }
        });
    });
</script>

