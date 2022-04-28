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
                    <i class="fas fa-cash-register"></i>
                </span>
                <h5>Cadastro de venda</h5>
            </div>
            <div class="widget-content nopadding tab-content">
                <div class="span12" id="divProdutosServicos" style=" margin-left: 0">
                    <ul class="nav nav-tabs">
                        <li class="active" id="tabDetalhes"><a href="#tab1" data-toggle="tab">Detalhes da venda</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab1">
                            <div class="span12" id="divCadastrarOs">
                                <?php if ($custom_error == true) { ?>
                                    <div class="span12 alert alert-danger" id="divInfo" style="padding: 1%;">Dados incompletos, verifique os campos com asterisco ou se selecionou corretamente cliente e responsável.</div>
                                <?php } ?>
                                <form action="<?php echo current_url(); ?>" method="post" id="formVendas">
                                    <div class="span12" style="padding: 1%">
                                        <div class="span2">
                                            <label for="dataInicial">Data da Venda<span class="required">*</span></label>
                                            <input id="dataVenda" class="span12 datepicker" type="text" name="dataVenda" value="<?php echo date('d/m/Y'); ?>" />
                                        </div>
                                        <div class="span5">
                                            <label for="cliente">Cliente<span class="required">*</span></label>
                                            <input id="cliente" class="span12" type="text" name="cliente" value="" />
                                            <input id="clientes_id" class="span12" type="hidden" name="clientes_id" value="" />
                                            <div class="addclient"><?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aCliente')) { ?>
                                              <a href="<?php echo base_url(); ?>index.php/clientes/adicionar" class="btn btn-success"><i class="fas fa-plus"></i> Adicionar Cliente</a><?php } ?></div>
                                        </div>
                                        <div class="span5">
                                            <label for="tecnico">Vendedor<span class="required">*</span></label>
                                            <input id="tecnico" class="span12" type="text" name="tecnico" value="<?= $this->session->userdata('nome'); ?>" />
                                            <input id="usuarios_id" class="span12" type="hidden" name="usuarios_id" value="<?= $this->session->userdata('id'); ?>" />
                                        </div>
                                    </div>

                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="observacoes">
                                            <h4>Observações</h4>
                                        </label>
                                        <textarea class="editor" name="observacoes" id="observacoes" cols="30" rows="5"></textarea>
                                    </div>

                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="observacoes_cliente">
                                            <h4>Observações para o Cliente</h4>
                                        </label>
                                        <textarea class="editor" name="observacoes_cliente" id="observacoes_cliente" cols="30" rows="5"></textarea>
                                    </div>

                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span6 offset3" style="display:flex;justify-content: center">
                                          <button class="button btn btn-success" id="btnContinuar"><span class="button__icon"><i class='bx bx-chevrons-right'></i></span><span class="button__text2">Continuar</span></button>
                                            <a href="<?php echo base_url() ?>index.php/vendas" class="button btn btn-mini btn-warning"><span class="button__icon"><i class="bx bx-undo"></i></span> <span class="button__text2">Voltar</span></a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.addclient').hide();
        $("#cliente").autocomplete({
            source: "<?php echo base_url(); ?>index.php/vendas/autoCompleteCliente",
            minLength: 1,
            close: function(ui) { if(ui.label == 'Adicionar cliente...')ui.target.value = '';},
            select: function(event, ui) {
                if(ui.item.label == 'Adicionar cliente...')
                    $('.addclient').show();
                else
                    {
                        $("#clientes_id").val(ui.item.id);
                        $('.addclient').hide();
                    }
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
        $('.editor').trumbowyg({
            lang: 'pt_br'
        });
        $('.addclient').hide();
    });
</script>
