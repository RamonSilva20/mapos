<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aVenda')) { ?>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
    <script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
    <script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>


    <!-- Inicio do PDV -->
    <div class="row-fluid" style="margin-top:0">
        <div class="span12">
            <div class="widget-box">
                <div class="widget-title">
                    <span class="icon">
                        <i class="fas fa-cash-register"></i>
                    </span>
                    <h5>Efetuar venda rápida</h5>
                </div>
                <div class="widget-content nopadding">
                    <div class="span12" id="divProdutosServicos" style=" margin-left: 0">

                        <div class="tab-content">
                            <div class="tab-pane active" id="tab1">
                                <div class="span12" id="divCadastrarOs">
                                    <?php if ($custom_error == true) { ?>
                                        <div class="span12 alert alert-danger" id="divInfo" style="padding: 1%;">Dados incompletos, verifique os campos com asterisco ou se selecionou corretamente cliente e responsável.</div>
                                    <?php } ?>
                                    <form action="<?php echo current_url() . "/adicionar"; ?>" method="post" id="formVendas">
                                        <div class="span12" style="padding: 1%">
                                            <div class="span">

                                                <input type="hidden" id="dataVenda" class="span12 datepicker" type="text" name="dataVenda" value="<?php echo date('d/m/Y'); ?>" />
                                            </div>
                                            <div class="span5">
                                                <label for="cliente">Cliente<span class="required">*</span></label>
                                                <input id="cliente" class="span12" type="text" name="cliente" value="Consumidor padrão" />
                                                <input id="clientes_id" class="span12" type="hidden" name="clientes_id" value="1" />
                                            </div>
                                            <div class="span5">
                                                <label for="tecnico">Vendedor<span class="required">*</span></label>
                                                <input id="tecnico" class="span12" type="text" name="tecnico" value="<?= $this->session->userdata('nome'); ?>" />
                                                <input id="usuarios_id" class="span12" type="hidden" name="usuarios_id" value="<?= $this->session->userdata('id'); ?>" />
                                            </div>
                                        </div>
                                        <div class="span12" style="padding: 1%; margin-left: 0">
                                            <div class="span6 offset3" style="text-align: center">
                                                <button class="btn btn-success" id="btnContinuar" autofocus><i class="fas fa-share"></i>&nbsp; Iniciar a venda (ENTER)</button>

                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    &nbsp
                </div>
            </div>
        </div>
    </div>


    <!-- fim do pdv -->



<?php } ?>

<script type="text/javascript">
    $(document).ready(function() {
        
        $("#cliente").autocomplete({
            source: "<?php echo base_url(); ?>index.php/vendas/autoCompleteCliente",
            minLength: 1,
            select: function(event, ui) {
                $("#clientes_id").val(ui.item.id);
            }
        });
        $("#tecnico").autocomplete({
            source: "<?php echo base_url(); ?>index.php/vendas/autoCompleteUsuario",
            minLength: 1,
            select: function(event, ui) {
                $("#usuarios_id").val(ui.item.id);
            }
        });
        $("#formVendas").validate({
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
    });
   
</script>