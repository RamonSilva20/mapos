<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-university"></i>
                </span>
                <h5>Adicionar Conta</h5>
            </div>
            <?php if ($custom_error != '') {
                echo '<div class="alert alert-danger">' . $custom_error . '</div>';
            } ?>
            <form action="<?php echo current_url(); ?>" id="formConta" method="post" class="form-horizontal">
                <div class="widget-content nopadding tab-content">
                    <div class="span6">
                        <div class="control-group">
                            <label for="conta" class="control-label">Nome da Conta<span class="required">*</span></label>
                            <div class="controls">
                                <input id="conta" type="text" name="conta" value="<?php echo set_value('conta'); ?>" placeholder="Ex: Conta Corrente, Caixa, Poupança..." />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="banco" class="control-label">Banco</label>
                            <div class="controls">
                                <input id="banco" type="text" name="banco" value="<?php echo set_value('banco'); ?>" placeholder="Ex: Banco do Brasil, Itaú..." />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="numero" class="control-label">Número da Conta</label>
                            <div class="controls">
                                <input id="numero" type="text" name="numero" value="<?php echo set_value('numero'); ?>" placeholder="Ex: 12345-6" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="tipo" class="control-label">Tipo</label>
                            <div class="controls">
                                <input id="tipo" type="text" name="tipo" value="<?php echo set_value('tipo'); ?>" placeholder="Ex: Conta Corrente, Poupança, Caixa..." />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="saldo" class="control-label">Saldo Inicial</label>
                            <div class="controls">
                                <input id="saldo" type="text" name="saldo" value="<?php echo set_value('saldo', '0,00'); ?>" class="money" placeholder="0,00" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <div class="span12">
                        <div class="span6 offset3" style="display:flex;justify-content: center">
                            <button type="submit" class="button btn btn-mini btn-success"><span class="button__icon"><i class='bx bx-save'></i></span> <span class="button__text2">Salvar</span></button>
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
        $("#conta").focus();
        $('.money').mask('#.##0,00', {reverse: true});
        
        $('#formConta').validate({
            rules: {
                conta: {
                    required: true
                },
            },
            messages: {
                conta: {
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

