<link rel="stylesheet" href="<?php echo base_url();?>js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url()?>js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="<?php echo base_url()?>js/jquery.validate.js"></script>
<?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vOs')){ ?>
<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="icon-tags"></i>
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
                                <?php if($custom_error == true){ ?>
                                <div class="span12 alert alert-danger" id="divInfo" style="padding: 1%;">Dados incompletos, verifique os campos com asterisco ou se selecionou corretamente cliente e responsável.</div>
                                <?php } ?>
                                <form action="<?php echo current_url(); ?>" method="post" id="formOs">

                                    <div class="span12" style="padding: 1%">
                                        <label for="Anotações">Anotações Gerais (<i>Não aparece para o cliente</i>)</label>
                                        <textarea class="span12" name="anotacoesOs" id="anotacoesOs" rows="3" maxlength="1024"></textarea>
                                        <span></span>
                                    </div>

                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="control-group span4">
                                            <label for="cliente">Cliente<span class="required">*</span></label>
                                            <input id="cliente" class="span12" type="text" name="cliente" value=""  />
                                            <input id="clientes_id" class="span12" type="hidden" name="clientes_id" value=""  />
                                        </div>
                                        <div class="control-group span4">
                                            <label for="operador_id">Atendente<span class="required">*</span></label>
                                            <select class="span12" name="operador_id" id="operador_id">
                                                <option value="selecione">Selecione</option>
                                                <option value="5">Celso Torok</option>
                                                <option value="6">José Marques</option>
                                                <option value="3">Rafael Marques</option>
                                                <option value="7">Rosiane Ribeiro</option>
                                                <option value="4">Shirley Marques</option>
                                            </select>
                                        </div>
                                        <div class="control-group span4">
                                            <label for="tecnico">Técnico / Responsável<span class="required">*</span></label>
                                            <input id="tecnico" class="span12" type="text" name="tecnico" value="Computador Ponto Com" disabled="disabled"/>
                                            <input id="usuarios_id" class="span12" type="hidden" name="usuarios_id" value="1" />
                                        </div>
                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span3">
                                            <label for="status">Status<span class="required">*</span></label>
                                            <select class="span12" name="status" id="status" value="">
                                                <option value="Orçamento">Orçamento</option>
                                                <option value="Aprovado">Aprovado</option>
                                                <option value="Em Andamento">Em Andamento</option>
                                                <option value="Finalizado">Finalizado</option>
                                                <option value="Faturado">Faturado</option>
                                            </select>
                                        </div>
                                        <div class="control-group span3">
                                            <label for="dataInicial">Data Inicial<span class="required">*</span></label>
                                            <input id="dataInicial" class="span12 datepicker" type="text" name="dataInicial" value=""  />
                                        </div>
                                        <div class="control-group span3">
                                            <label for="dataFinal">Data Final<span class="required">*</span></label>
                                            <input id="dataFinal" class="span12 datepicker" type="text" name="dataFinal" value=""  />
                                        </div>

                                        <div class="span3">
                                            <label for="garantia">Garantia</label>
                                            <select class="span12" name="garantia" id="garantia">
                                                <option value="30">30 Dias</option>
                                                <option value="60">60 Dias</option>
                                                <option value="90">90 Dias</option>
                                                <option value="180">6 meses</option>
                                                <option value="360">1 ano</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span3">
                                            <label for="equipamento">Equipamento</label>
                                            <select class="span12" name="equipamento" id="equipamento" value="">
                                                <option value="selecione">Selecione</option>
                                                <option value="Notebook">Notebook</option>
                                                <option value="Netbook">Netbook</option>
                                                <option value="Desktop">Desktop</option>
                                                <option value="All-In-One">All-In-One</option>
                                                <option value="Tablet">Tablet</option>
                                                <option value="Impressora">Impressora</option>
                                                <option value="Monitor">Monitor</option>
                                                <option value="Fonte">Fonte</option>
                                                <option value="Carregador">Carregador</option>
                                                <option value="Bateria">Bateria</option>
                                                <option value="Estabilizador">Estabilizador</option>
                                                <option value="Nobreak">Nobreak</option>
                                                <option value="HD-Externo">HD-Externo</option>
                                                <option value="Cartucho">Cartucho</option>
                                                <option value="Outros">Outros</option>
                                            </select>
                                        </div>
                                        <div class="span3">
                                            <label for="marca">Marca</label>
                                            <select class="span12" name="marca" id="marca" value="">
                                                <option value="selecione">Selecione</option>
                                                <option value="Acer">Acer</option>
                                                <option value="Amazon">Amazon</option>
                                                <option value="Aoc">Aoc</option>
                                                <option value="APC">APC</option>
                                                <option value="Apple">Apple</option>
                                                <option value="Asus">Asus</option>
                                                <option value="Canon">Canon</option>
                                                <option value="Cce">Cce</option>
                                                <option value="Cecomil">Cecomil</option>
                                                <option value="Compaq">Compaq</option>
                                                <option value="Dell">Dell</option>
                                                <option value="Goldentech">Goldentech</option>
                                                <option value="HP">HP</option>
                                                <option value="iByte">iByte</option>
                                                <option value="Lenovo">Lenovo</option>
                                                <option value="Lexmark">Lexmark</option>
                                                <option value="LG">LG</option>
                                                <option value="Megaware">Megaware</option>
                                                <option value="Microboard">Microboard</option>
                                                <option value="Microsol">Microsol</option>
                                                <option value="Philco">Philco</option>
                                                <option value="Philips">Philips</option>
                                                <option value="Positvo">Positvo</option>
                                                <option value="Samsung">Samsung</option>
                                                <option value="Sim">Sim</option>
                                                <option value="SMS">SMS</option>
                                                <option value="Sony">Sony</option>
                                                <option value="Sti">Sti</option>
                                                <option value="Toshiba">Toshiba</option>
                                                <option value="Win">Win</option>
                                                <option value="Outros">Outros</option>
                                            </select>
                                        </div>
                                        <div class="span3">
                                            <label for="modelo">Modelo</label>
                                            <input class="span12" type="text" class="form-control" name="modelo" id="modelo" value="">
                                        </div>
                                        <div class="span3">
                                            <label for="nronumber">Nro. Série</label>
                                            <input class="span12" type="text" class="form-control" name="nronumber" id="nronumber" value="">
                                        </div>
                                    </div>

                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="control-group span4">
                                              <label for="senha">Senha<span class="required">*</span></label>
                                              <input class="span12" type="text" name="senha" id="senha" value="">
                                        </div>
                                        <div class="span4">
                                            <label for="backup">Backup</label>
                                            <select class="span12" name="backup" id="backup" value="">
                                                <option value="selecione">Selecione</option>
                                                <option value="Sim">Sim</option>
                                                <option value="Não">Nao</option>
                                            </select>
                                        </div>
                                        <div class="span4">
                                            <label for="cabos">Acessórios</label>
                                            <select class="span12" name="cabos" id="cabos" value="">
                                                <option value="selecione">Selecione</option>
                                                <option value="Sim">Sim</option>
                                                <option value="Não">Nao</option>
                                            </select>
                                        </div>
                                        <div id="descricaoCabosSim" class="span12" style="display: none; margin-left: 0;">
                                            <label for="descricaoCabos">Descrição dos Acessórios</label>
                                            <input class="span12" type="text" class="form-control" name="descricaoCabos" placeholder="Especifique">
                                        </div>
                                        <div id="descricaoCabosNao" class="span12" style="display: none; margin-left: 0;">
                                            <label for="descricaoCabos">Descrição dos Acessórios</label>
                                            <input class="span12" type="text" class="form-control" name="descricaoCabos" value="Nenhum acessório (fonte, cabo, case e etc) foi entregue com o equipamento.">
                                        </div>
                                    </div>

                                    <div class="span12" style="padding: 1%; margin-left: 0">

                                        <div class="span6">
                                            <label for="descricaoProduto">Descrição Produto/Serviço</label>
                                            <textarea class="span12" name="descricaoProduto" id="descricaoProduto" cols="30" rows="5" maxlength="255"></textarea><span></span>
                                        </div>
                                        <div class="control-group span6">
                                            <label for="defeito">Defeito<span class="required">*</span></label>
                                            <textarea class="span12" name="defeito" id="defeito" cols="30" rows="5" maxlength="255"></textarea><span></span>
                                        </div>

                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span6">
                                            <label for="observacoes">Observações</label>
                                            <textarea class="span12" name="observacoes" id="observacoes" cols="30" rows="5" maxlength="255"></textarea><span></span>
                                        </div>
                                        <div class="span6">
                                            <label for="laudoTecnico">Laudo Técnico</label>
                                            <textarea class="span12" name="laudoTecnico" id="laudoTecnico" cols="30" rows="5" maxlength="255"></textarea><span></span>
                                        </div>
                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span6 offset3" style="text-align: center">
                                            <button class="btn btn-success" id="btnContinuar"><i class="icon-share-alt icon-white"></i> Continuar</button>
                                            <a href="<?php echo base_url() ?>index.php/os" class="btn"><i class="icon-arrow-left"></i> Voltar</a>
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
<?php } else { ?>
<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="icon-tags"></i>
                </span>
                <h5>Cadastro de Clientes</h5>
            </div>
            <div class="widget-content nopadding">
                <h2>Cadastro realizado com sucesso!</h2>
            </div>
        </div>
    </div>
</div>
<?php } ?>



<script type="text/javascript">
$(document).ready(function(){

      $("#cliente").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteCliente",
            minLength: 1,
            select: function( event, ui ) {

                 $("#clientes_id").val(ui.item.id);
                

            }
      });

      $("#tecnico").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteUsuario",
            minLength: 1,
            select: function( event, ui ) {

                 $("#usuarios_id").val(ui.item.id);


            }
      });

      
      

      $("#formOs").validate({
          rules:{
             cliente: {required:true},
             tecnico: {required:true},
             dataInicial: {required:true},
             dataFinal: {required:true},
             defeito: {required:true},
             senha: {required:true}
          },
          messages:{
             cliente: {required: 'Campo Requerido.'},
             tecnico: {required: 'Campo Requerido.'},
             dataInicial: {required: 'Campo Requerido.'},
             dataFinal: {required: 'Campo Requerido.'},
             defeito: {required: 'Campo Requerido.'},
             senha: {required: 'Campo Requerido.'}
          },

            errorClass: "help-inline",
            errorElement: "span",
            highlight:function(element, errorClass, validClass) {
                $(element).parents('.control-group').addClass('error');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error');
                $(element).parents('.control-group').addClass('success');
            }
       });

    $(".datepicker" ).datepicker({ dateFormat: 'dd/mm/yy' });

    $( "#cabos" ).change(function() {
      if ( $(this).val() == "Sim") {
          $( "#descricaoCabosSim > input" ).attr('name','descricaoCabos');
          $( "#descricaoCabosSim" ).show();
          $( "#descricaoCabosNao > input" ).attr('name','');
          $( "#descricaoCabosNao" ).hide();
      } else {
          $( "#descricaoCabosNao > input" ).attr('name','descricaoCabos');
          $( "#descricaoCabosNao" ).show();
          $( "#descricaoCabosSim > input" ).attr('name','');
          $( "#descricaoCabosSim" ).hide();
      }
    });

    //contador de caracteres
    $('textarea').on({
        focus: function() {
            var text_max = $(this).attr('maxlength');
            var text_length = $(this).val().length;
            var text_remaining = text_max - text_length;
            $(this).next().html(text_remaining + ' caracteres restantes.');
        },
        keyup: function() {
            var text_max = $(this).attr('maxlength');
            var text_length = $(this).val().length;
            var text_remaining = text_max - text_length;
            $(this).next().html(text_remaining + ' caracteres restantes.');
        }
    });
   
});

</script>