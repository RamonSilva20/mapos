<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />

<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.validate.js"></script>
<script src="<?php echo base_url(); ?>assets/js/jquery.mask.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/sweetalert2.all.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/funcoes.js"></script>

<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="far fa-car"></i>
                </span>
                <h5>Cadastro de Veículo</h5>
            </div>
            <div class="widget-content nopadding">
                <?php
                if ($custom_error != '') {
                    echo '<div class="alert alert-danger">' . $custom_error . '</div>';
                }
                ?>
                <form action="<?= current_url(); ?>" id="formVeiculo" method="post" class="form-horizontal">
                    <div class="control-group">
                        <label for="cliente" class="control-label">Cliente:*</label>
                        <div class="controls">
                            <input id="cliente" type="text" name="cliente" value="" />
                            <input id="clientes_id" class="span12" type="hidden" name="clientes_id" value="" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="placa" class="control-label">Placa:*</label>
                        <div class="controls">
                            <input id="placa" type="text" name="placa" value="<?= set_value('placa'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="anoModelo" class="control-label">Ano do Modelo:</label>
                        <div class="controls">
                            <input id="anoModelo" type="text" name="anoModelo" value="<?= set_value('anoModelo'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="anoFabricacao" class="control-label">Ano de Fabricação:</label>
                        <div class="controls">
                            <input id="anoFabricacao" type="text" name="anoFabricacao" value="<?= set_value('anoFabricacao'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="marcaFabricante" class="control-label">Marca / Fabricante:*</label>
                        <div class="controls">
                            <input id="marcaFabricante" type="text" name="marcaFabricante" value="<?= set_value('marcaFabricante'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="modelo" class="control-label">Modelo:*</label>
                        <div class="controls">
                            <input id="modelo" type="text" name="modelo" value="<?= set_value('modelo'); ?>"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="chassi" class="control-label">Chassi:</label>
                        <div class="controls">
                            <input id="chassi" type="text" name="chassi" value="<?= set_value('chassi'); ?>"/>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="span12">
                            <div class="">
                                <button type="submit" class="btn btn-primary"><i class="far fa-plus"></i> Adicionar</button>
                                <a href="<?= base_url(); ?>index.php/veiculos" id="" class="btn"><i class="far fa-backward"></i> Voltar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url(); ?>assets/js/jquery.validate.js" type="text/javascript"></script>
<script src="<?= base_url(); ?>assets/js/customValidation.js" type="text/javascript"></script>

<script type="text/javascript">
    $(document).ready(function () {
        $("#cliente").autocomplete({
            source: "<?= base_url(); ?>index.php/os/autoCompleteCliente",
            minLength: 1,
            select: function (event, ui) {
                $("#clientes_id").val(ui.item.id);
            }
        });
    });
</script>