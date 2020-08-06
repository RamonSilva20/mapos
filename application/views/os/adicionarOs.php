<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>

<link rel="stylesheet" href="<?php echo base_url() ?>assets/trumbowyg/ui/trumbowyg.css">
<script type="text/javascript" src="<?php echo base_url() ?>assets/trumbowyg/trumbowyg.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/trumbowyg/langs/pt_br.js"></script>

<style>
    .ui-datepicker {
        z-index: 9999 !important;
    }

    .trumbowyg-box {
        margin-top: 0;
        margin-bottom: 0;
    }
    .alert {
    display:block !important;
}
</style>

<form action="<?php echo current_url(); ?>" method="post" id="formOs">
<div class="alert alert-success" role="alert">
  <h3 class="alert-heading"><strong><i class="fas fa-wrench"></i>Nova OS</strong></h3>
  <hr>
  <p class="mb-0"><strong><h4><i class="fas fa-user"></i> Cliente: </strong><small><?php echo $result->nomeCliente ?> (<a href="<?php echo base_url() ?>index.php/clientes/visualizar/<?php echo $result->idClientes ?>" class="alert-link" target="_blank"><strong>Cod. <?php echo $result->idClientes ?></a>) </strong></small></h4></p>
  <input id="clientes_id" class="span12" type="hidden" name="clientes_id" value="<?php echo $result->idClientes ?>" />
  <input id="cliente" class="span12" type="hidden" name="cliente" value="<?php echo $result->idClientes ?>" />
  <p class="mb-0"><strong><h4><i class="fas fa-home"></i> Endereço: </strong><small><?php echo $result->rua ?>, nº<?php echo $result->numero ?> - <?php echo $result->bairro ?> - <?php echo $result->cidade ?> - <?php echo $result->estado ?></small></h4></p>
  <p class="mb-0"><strong><h4><i class="fas fa-phone-square-alt"></i> Telefones: </strong><small><?php echo $result->telefone ?>, <?php echo $result->celular ?></small></h4></p>
</div>
<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-diagnoses"></i>
                </span>
                <h5>Cadastro de OS</h5>
            </div>
            <div class="widget-content nopadding">
                <div class="span12" id="divProdutosServicos" style=" margin-left: 0">

                    <ul class="nav nav-tabs">
                        <li class="active" id="tabDetalhes"><a href="#tab1" data-toggle="tab">Detalhes da OS</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab1">
                            <div class="span12" id="divCadastrarOs">
                                <?php if ($custom_error == true) { ?>
                                    <div class="span12 alert alert-danger" id="divInfo" style="padding: 1%;">Dados incompletos, verifique os campos com asterisco ou se selecionou corretamente cliente, responsável e garantia.<br />Ou se tem um cliente e um termo de garantia cadastrado.</div>
                                <?php
                                } ?>
                                    <div class="span12" style="padding: 1%">
                                        <div class="span6">
                                            <label for="tecnico">Técnico / Responsável<span class="required">*</span></label>
                                            <input id="tecnico" class="span12" type="text" name="tecnico" value="<?= $this->session->userdata('nome'); ?>" />
                                            <input id="usuarios_id" class="span12" type="hidden" name="usuarios_id" value="<?= $this->session->userdata('id'); ?>" />
                                        </div>
                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span4">
                                            <label for="status">Status<span class="required">*</span></label>
                                            <select class="span12" name="status" id="status" value="">
                                                <option value="Orçamento">Aguardando avaliação do técnico</option>
                                                <option value="Aberto">Aguardando autorização do orçamento</option>
                                                <option value="Em Andamento">Em Andamento</option>
                                                <option value="Finalizado">Finalizado</option>
                                                <option value="Cancelado">Cancelado</option>
                                                <option value="Aguardando Peças">Aguardando Peças</option>
                                                <option value="whatsapp">Orçamento enviado via Whatsapp</option>
                                            </select>
                                        </div>
                                        <div class="span2">
                                            <label for="dataInicial">Data Inicial<span class="required">*</span></label>
                                            <input id="dataInicial" autocomplete="off" class="span12 datepicker" type="text" name="dataInicial" value="<?php echo date('d/m/Y'); ?>" />
                                            <input id="dataFinal" autocomplete="off" class="span12 datepicker" type="hidden" name="dataFinal" value="<?php echo date('d/m/Y', strtotime('+1 days')); ?>" />  
                                        </div>
                                        </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span3">
                                            <label for="aparelho">Aparelho<span class="required">*</span></label>
                                            <input id="aparelho" class="span12" type="text" name="aparelho" value="" />
                                        </div>
                                        <div class="span3">
                                            <label for="marca">Marca<span class="required">*</span></label>
                                            <input id="marca" class="span12" type="text" name="marca" value="" />
                                            <input id="marca_id" class="span12" type="hidden" name="marca_id" value="" />
                                        </div>
                                        <div class="span3">
                                            <label for="modelo">Modelo<span class="required">*</span></label>
                                            <input id="modelo" class="span12" type="text" name="modelo" value="" />
                                        </div>
                                        <div class="span3">
                                            <label for="serie">Nro. Série<span class="required">*</span></label>
                                            <input id="serie" class="span12" type="text" name="serie" value="" />
                                        </div>
                                        <div class="span3">
                                            <label for="cor">Cor<span class="required">*</span></label>
                                            <input id="cor" class="span12" type="text" name="cor" value="" />
                                        </div>
                                        <div class="span3">
                                            <label for="acessorio">Acessórios<span class="required">*</span></label>
                                            <textarea class="span12" name="acessorio" id="acessorio" cols="30" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="defeito">
                                            <h4>Defeito Reclamado</h4>
                                        </label>
                                        <textarea class="span12" name="defeito" id="defeito" cols="30" rows="5"></textarea>
                                    </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="observacoes">
                                            <h4>Observações</h4>
                                        </label>
                                        <textarea class="span12" name="observacoes" id="observacoes" cols="30" rows="5"></textarea>
                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span6 offset3" style="text-align: center">
                                            <button class="btn btn-success" id="btnContinuar"><i class="fas fa-plus"></i> Continuar</button>
                                            <a href="<?php echo base_url() ?>index.php/os" class="btn"><i class="fas fa-backward"></i> Voltar</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                .
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $("#cliente").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteCliente",
            minLength: 1,
            select: function(event, ui) {
                $("#clientes_id").val(ui.item.id);
            }
        });
        $("#tecnico").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteUsuario",
            minLength: 1,
            select: function(event, ui) {
                $("#usuarios_id").val(ui.item.id);
            }
        });
        $("#termoGarantia").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteTermoGarantia",
            minLength: 1,
            select: function(event, ui) {
                $("#garantias_id").val(ui.item.id);
            }
        });
        $("#marca").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteMarca",
            minLength: 1,
            select: function(event, ui) {
                $("#marca_id").val(ui.item.id);
            }
        });

        $("#formOs").validate({
            rules: {
                cliente: {
                    required: false
                },
                tecnico: {
                    required: false
                },
                dataInicial: {
                    required: false
                },
                dataFinal: {
                    required: false
                }

            },
            messages: {
                cliente: {
                    required: 'Campo Requerido.'
                },
                tecnico: {
                    required: 'Campo Requerido.'
                },
                dataInicial: {
                    required: 'Campo Requerido.'
                },
                dataFinal: {
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
