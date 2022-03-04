<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>

<link rel="stylesheet" href="<?php echo base_url() ?>assets/trumbowyg/ui/trumbowyg.css">
<script type="text/javascript" src="<?php echo base_url() ?>assets/trumbowyg/trumbowyg.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/trumbowyg/langs/pt_br.js"></script>

<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-book"></i>
                </span>
                <h5>Editar Termo de Garantia</h5>
            </div>
            <div class="widget-content">

                <?php if ($custom_error) { ?>
                    <div class="span12 alert alert-danger" id="divInfo" style="padding: 1%;">Dados incompletos, verifique os campos com asterisco ou se selecionou corretamente cliente e responsável.</div>
                <?php  } ?>

                <form action="<?php echo current_url(); ?>" method="post" id="formGarantia">

                    <div class="span12">
                        <div class="span2">
                            <label for="dataGarantia">Data</label>
                            <?php echo form_hidden('idGarantias', $result->idGarantias) ?>
                            <input id="dataGarantia" class="span12 datepicker" type="text" name="dataGarantia" value="<?php echo date('d/m/Y', strtotime($result->dataGarantia)); ?>" disabled />
                        </div>
                        <div class="span5">
                            <label for="usuarios_id">Responsável</label>
                            <input id="usuarios_id" class="span12" type="text" name="usuarios_id" value="<?php echo $result->nome ?>" disabled />

                        </div>
                        <div class="span5">
                            <label for="refGarantia">Ref. Garantia</label>
                            <input id="refGarantia" class="span12" type="text" name="refGarantia" value="<?php echo $result->refGarantia ?>" />
                        </div>
                        <div class="span12" style="margin-left: 0">
                            <label for="textoGarantia">
                                <h4 class="text-center">Termo de Garantia</h4>
                            </label>
                            <textarea required class="span10 editor" name="textoGarantia" id="textoGarantia" cols="30" rows="5"><?php echo $result->textoGarantia ?></textarea>
                        </div>
                    </div>

                    <div class="span12" style="padding: 1%; margin-left: 0">
                        <div class="span6 offset5" style="display:flex;justify-content: center">
                            <button type="submit" class="button btn btn-primary"><span class="button__icon"><i class="bx bx-sync"></i></span><span class="button__text2">Atualizar</span></button>
                            <a href="<?php echo base_url() ?>index.php/garantias" id="" class="button btn btn-mini btn-warning"><span class="button__icon"><i class="bx bx-undo"></i></span> <span class="button__text2">Voltar</span></a>
                        </div>
                    </div>
                </form>
                .
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $("#cliente").autocomplete({
            source: "<?php echo base_url(); ?>index.php/garantias/autoCompleteCliente",
            minLength: 1,
            select: function(event, ui) {
                $("#clientes_id").val(ui.item.id);
            }
        });
        $("#tecnico").autocomplete({
            source: "<?php echo base_url(); ?>index.php/garantias/autoCompleteUsuario",
            minLength: 1,
            select: function(event, ui) {
                $("#usuarios_id").val(ui.item.id);
            }
        });
        $("#formGarantia").validate({
            rules: {
                cliente: {
                    required: true
                },
                tecnico: {
                    required: true
                },
                dataVenda: {
                    required: true
                }
            },
            messages: {
                cliente: {
                    required: 'Campo Requerido.'
                },
                tecnico: {
                    required: 'Campo Requerido.'
                },
                dataVenda: {
                    required: 'Campo Requerido.'
                }
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
        $(".datepicker").datepicker({
            dateFormat: 'dd/mm/yy'
        });
        $('.editor').trumbowyg({
            lang: 'pt_br'
        });
    });
</script>
