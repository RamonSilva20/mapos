<script src="<?= base_url(); ?>assets/js/jquery.mask.min.js"></script>
<script src="<?= base_url(); ?>assets/js/sweetalert2.all.min.js"></script>
<script src="<?= base_url(); ?>assets/js/funcoes.js"></script>

<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title"><span class="icon"><i class="far fa-car"></i></span>
                <h5>Editar Veículo</h5>
            </div>
            <div class="widget-content nopadding">
                <?php
                if ($custom_error != '') {
                    echo '<div class="alert alert-danger">' . $custom_error . '</div>';
                }
                ?>
                <form action="<?php echo current_url(); ?>" id="formVeiculo" method="post" class="form-horizontal">
                    <div class="control-group">
                        <label for="cliente" class="control-label">Cliente<span class="required">*</span></label>
                        <div class="controls">
                            <input id="cliente" type="text" name="cliente" value="<?php echo $result->nomeCliente; ?>" />
                            <input id="clientes_id" type="hidden" name="clientes_id" value="<?php echo $result->clientes_id; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <?php echo form_hidden('idVeiculos', $result->idVeiculos); ?>
                        <label for="placa" class="control-label">Placa<span class="required">*</span></label>
                        <div class="controls">
                            <input id="placa" type="text" name="placa" value="<?php echo $result->placa; ?>"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="anoModelo" class="control-label">Ano do Modelo<span class="required"></span></label>
                        <div class="controls">
                            <input id="anoModelo" type="text" name="anoModelo" value="<?php echo $result->anoModelo; ?>"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="anoFabricacao" class="control-label">Ano de Fabricação<span class="required"></span></label>
                        <div class="controls">
                            <input id="anoFabricacao" type="text" name="anoFabricacao" value="<?php echo $result->anoFabricacao; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="marcaFabricante" class="control-label">Marca / Fabricante<span class="required">*</span></label>
                        <div class="controls">
                            <input id="marcaFabricante" type="text" name="marcaFabricante" value="<?php echo $result->marcaFabricante; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="modelo" class="control-label">Modelo<span class="required">*</span></label>
                        <div class="controls">
                            <input id="modelo" type="text" name="modelo" value="<?php echo $result->modelo; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="chassi" class="control-label">Chassi<span class="required"></span></label>
                        <div class="controls">
                            <input id="chassi" type="text" name="chassi" value="<?php echo $result->chassi; ?>" />
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="span12">
                            <div class="span6 offset3">
                                <button type="submit" class="btn btn-primary"><i class="far fa-sync-alt"></i> Atualizar</button>
                                <a href="<?php echo base_url(); ?>index.php/veiculos" id="" class="btn"><i class="far fa-backward"></i> Voltar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url(); ?>assets/js/jquery.validate.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $("#cliente").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteCliente",
            minLength: 1,
            select: function (event, ui) {
                $("#clientes_id").val(ui.item.id);
            }
        });
        $('#formVeiculo').validate({
            rules: {
                placa: {
                    required: true
                },
                anoModelo: {
                    required: false
                },
                anoFabricacao: {
                    required: false
                },
                marcaFabricante: {
                    required: true
                },
                modelo: {
                    required: true
                },
                chassi: {
                    required: false
                }
            },
            messages: {
                placa: {
                    required: 'Campo Requerido.'
                },
                anoModelo: {
                    required: 'Campo Requerido.'
                },
                anoFabricacao: {
                    required: 'Campo Requerido.'
                },
                marcaFabricante: {
                    required: 'Campo Requerido.'
                },
                modelo: {
                    required: 'Campo Requerido.'
                },
                chassi: {
                    required: 'Campo Requerido.'
                }
            },
            errorClass: "help-inline",
            errorElement: "span",
            highlight: function (element, errorClass, validClass) {
                $(element).parents('.control-group').addClass('error');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error');
                $(element).parents('.control-group').addClass('success');
            }
        });
    });
</script>



