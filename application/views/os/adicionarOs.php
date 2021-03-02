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
</style>

<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="icon-tags"></i>
                </span>
                <h5>Cadastro de OS</h5>
            </div>
                <div class="span12" id="divProdutosServicos" style=" margin-left: 0">
<div class="widget_box_Painel2">
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
                                <form action="<?php echo current_url(); ?>" method="post" id="formOs">
                                    <div class="span12" style="padding: 1%">
                                        <div class="span6">
                                            <label for="cliente">Cliente<span class="required">*</span></label>
                                            <input id="cliente" class="span12" type="text" name="cliente" value="" />
                                            <input id="clientes_id" class="span12" type="hidden" name="clientes_id" value="" />
                                        </div>
                                        <div class="span6">
                                            <label for="tecnico">Técnico / Responsável<span class="required">*</span></label>
                                            <input id="tecnico" class="span12" type="text" name="tecnico" value="<?= $this->session->userdata('nome'); ?>" />
                                            <input id="usuarios_id" class="span12" type="hidden" name="usuarios_id" value="<?= $this->session->userdata('id'); ?>" />
                                        </div>
                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span3">
                                            <label for="status">Status<span class="required">*</span></label>
                                            <select class="span12" name="status" id="status" value="">
<option <?php if($result->status == 'Orçamento'){echo 'selected';} ?> value="Orçamento">Orçamento</option>
<option <?php if($result->status == 'Orçamento Concluido'){echo 'selected';} ?> value="Orçamento Concluido">Orçamento Concluido</option>
<option <?php if($result->status == 'Orçamento Aprovado'){echo 'selected';} ?> value="Orçamento Aprovado">Orçamento Aprovado</option>
<option <?php if($result->status == 'Aguardando Peças'){echo 'selected';} ?> value="Aguardando Peças">Aguardando Peças</option>
<option <?php if($result->status == 'Em Andamento'){echo 'selected';} ?> value="Em Andamento">Em Andamento</option>
<option <?php if($result->status == 'Serviço Concluido'){echo 'selected';} ?> value="Serviço Concluido">Serviço Concluido</option>
<option <?php if($result->status == 'Sem Reparo'){echo 'selected';} ?> value="Sem Reparo">Sem Reparo</option>
<option <?php if($result->status == 'Não Autorizado'){echo 'selected';} ?> value="Não Autorizado">Não Autorizado</option>
<option <?php if($result->status == 'Contato sem Sucesso'){echo 'selected';} ?> value="Contato sem Sucesso">Contato sem Sucesso</option>
<option <?php if($result->status == 'Cancelado'){echo 'selected';} ?> value="Cancelado">Cancelado</option>
<option <?php if($result->status == 'Pronto-Despachar'){echo 'selected';} ?> value="Pronto-Despachar">Pronto-Despachar</option>
<option <?php if($result->status == 'Enviado'){echo 'selected';} ?> value="Enviado">Enviado</option>
<option <?php if($result->status == 'Aguardando Envio'){echo 'selected';} ?> value="Aguardando Envio">Aguardando Envio</option>
<option <?php if($result->status == 'Aguardando Entrega Correio'){echo 'selected';} ?> value="Aguardando Entrega Correio">Aguardando Entrega Correio</option>
<option <?php if($result->status == 'Entregue - A Receber'){echo 'selected';} ?> value="Entregue - A Receber">Entregue - A Receber</option>
<option <?php if($result->status == 'Garantia'){echo 'selected';} ?> value="Garantia">Garantia</option>
<option <?php if($result->status == 'Abandonado'){echo 'selected';} ?> value="Abandonado">Abandonado</option>
<option <?php if($result->status == 'Comprado pela Loja'){echo 'selected';} ?> value="Comprado pela Loja">Comprado pela Loja</option>
<option <?php if($result->status == 'Entregue - Faturado'){echo 'selected';} ?> value="Entregue - Faturado">Entregue - Faturado</option>
                                            </select>
                                            <label for="rastreio">Rastreio</label>
                                          <input name="rastreio" type="text" class="span12" id="rastreio" maxlength="13" value="<?php echo $result->rastreio ?>"  />
										  <button class="btn btn-success" id="btnContinuar"><i class="fas fa-plus"></i> Continuar</button>
                                        </div>
                                        
                                        <div class="span3">
                                            <label for="dataInicial">Data de Entrtada<span class="required">*</span></label>
                                            <input id="dataInicial" autocomplete="off" class="span12 datepicker" type="text" name="dataInicial" value="<?php echo date('d/m/Y'); ?>" />
                                            <label for="dataFinal">Data Final<span class="required">*</span></label>
                                            <input id="dataFinal" autocomplete="off" class="span12 datepicker"
                                                   type="text" name="dataFinal"
                                                   value="<?php echo date('d/m/Y'); ?>"/>
                                      </div>
                                      
                                      <div class="span3">Nº Série
                                        <input id="serial" type="text" class="span12" name="serial" maxlength="30" value="<?php echo $result->serial ?>" />
                                        <label for="dataSaida">Data de Saida</label>
                                            <input id="dataSaida" autocomplete="off" class="span12 datepicker" type="text" name="dataSaida" value="<?php echo $result->dataSaida ?>" />
                                        </div>
                                        
                                        <div class="span3">
                                        <label for="marca">Marca</label>
                                          <input id="marca" type="text" class="span12" name="marca" maxlength="30" value="<?php echo $result->marca ?>" />
                                        <label for="garantia">Garantia até</label>
                                            <input id="garantia" type="text" class="span12 datepicker" name="garantia" value="<?php echo $result->garantia ?>" />
                                        </div>
                                        
                                  </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="descricaoProduto">
                                            <h4>Descrição Produto/Serviço</h4>
                                        </label>
                                        <textarea class="span12 editor" name="descricaoProduto" id="descricaoProduto" cols="30" rows="5"></textarea>
                                    </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="defeito">
                                            <h4>Problema Informado</h4>
                                        </label>
                                        <textarea class="span12 editor" name="defeito" id="defeito" cols="30" rows="5"></textarea>
                                    </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="observacoes">
                                            <h4>Observações</h4>
                                        </label>
                                        <textarea class="span12 editor" name="observacoes" id="observacoes" cols="30" rows="5"></textarea>
                                    </div>
                                    
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span6 offset3" style="text-align: center">
                                            <button class="btn btn-success" id="btnContinuar"><i class="fas fa-plus"></i> Continuar</button>
                                            <a href="<?php echo base_url() ?>index.php/os" class="btn btn-warning"><i class="fas fa-backward"></i> Voltar</a>
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

        $("#formOs").validate({
            rules: {
                cliente: {
                    required: true
                },
                tecnico: {
                    required: true
                },
                dataInicial: {
                    required: true
                },
                dataFinal: {
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
