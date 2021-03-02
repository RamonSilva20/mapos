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
                    <i class="fas fa-diagnoses"></i>
                </span>
                <h5>Cadastro de OS</h5>
            </div>
            <div class="widget_box_Painel2">


                <div class="span12" id="divProdutosServicos" style=" margin-left: 0">
                    <ul class="nav nav-tabs">
                        <li class="active" id="tabDetalhes"><a href="#tab1" data-toggle="tab">Detalhes da OS</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab1">

                            <div class="span12" id="divCadastrarOs">

                                <form action="<?php echo current_url(); ?>" method="post" id="formOs">
                                <div class="span12" style="padding: 1%;">
                                        <div class="span12 alert alert-info">
                                            <h5 class="text-center">Preencha os campos abaixo detalhando o que você precisa. Campos com asterisco são obrigatórios.</h5>
                                        </div>

                                    </div>
                                    <!-- EXTRA -->
									<div class="span12" style="padding: 1%; margin-left: 0">
                                    <div class="span3">
                                      	<label for="serial">Nº Série</label>
                                        <input id="serial" type="text" class="span12" name="serial" maxlength="30" value="<?php echo $result->serial ?>" />
									</div>
                                    <div class="span3">
                                        <label for="marca">Marca</label>
                                        <input id="marca" type="text" class="span12" name="marca" maxlength="30" value="<?php echo $result->marca ?>" />
									</div>
                                    <div class="span3">
                                        <label for="rastreio">Rastreio</label>
                                        <input name="rastreio" type="text" class="span12" id="rastreio" maxlength="13" value="<?php echo $result->rastreio ?>"  />
									</div>
                                        </div>
                                  <!-- FIM EXTRA -->
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
                                            <button class="btn btn-success" id="btnContinuar"><i class="fas fa-plus"></i> Cadastrar</button>
                                            <a href="<?php echo base_url() ?>index.php/mine/os" class="btn btn-warning"><i class="fas fa-backward"></i> Voltar</a>
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


        $("#formOs").validate({
            rules: {
                descricaoProduto: {
                    required: true
                }
            },
            messages: {
                descricaoProduto: {
                    required: 'O campo descrição da OS é obrigatório.'
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
