<link rel="stylesheet" href="<?=base_url('assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css')?>" />
<script type="text/javascript" src="<?=base_url('assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js')?>"></script>
<script type="text/javascript" src="<?=base_url('assets/js/jquery.validate.js')?>"></script>
<div class="row-fluid" style="margin-top:0">
    <a class="btn btn-success" href="#modalCliente" data-toggle="modal" role="button">Adicionar Cliente</a>
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
                                <form action="<?=current_url()?>" method="post" id="formOs">

                                    <div class="span12" style="padding: 1%">
                                        <div class="span6">
                                            <label for="cliente">Cliente<span class="required">*</span></label>
                                            <input id="cliente" class="span12" type="text" name="cliente" value=""  />
                                            <input id="clientes_id" class="span12" type="hidden" name="clientes_id" value=""  />
                                        </div>
                                        <div class="span6">
                                            <label for="tecnico">Técnico / Responsável<span class="required">*</span></label>
                                            <input id="tecnico" class="span12" type="text" name="tecnico" value=""  />
                                            <input id="usuarios_id" class="span12" type="hidden" name="usuarios_id" value=""  />
                                        </div>
                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span3">
                                            <label for="status">Status<span class="required">*</span></label>
                                            <select class="span12" name="status" id="status" value="">
                                                <option value="Orçamento">Orçamento</option>
                                                <option value="Aberto">Aberto</option>
                                                <option value="Em Andamento">Em Andamento</option>
                                                <option value="Finalizado">Finalizado</option>
                                                <option value="Cancelado">Cancelado</option>
                                            </select>
                                        </div>
                                        <div class="span3">
                                            <label for="dataInicial">Data Inicial<span class="required">*</span></label>
                                            <input id="dataInicial" class="span12 datepicker" type="text" name="dataInicial" value=""  />
                                        </div>
                                        <div class="span3">
                                            <label for="dataFinal">Data Final</label>
                                            <input id="dataFinal" class="span12 datepicker" type="text" name="dataFinal" value=""  />
                                        </div>

                                        <div class="span3">
                                            <label for="garantia">Garantia</label>
                                            <input id="garantia" type="text" class="span12" name="garantia" value=""  />
                                        </div>
                                    </div>

                                    <div class="span12" style="padding: 1%; margin-left: 0">

                                        <div class="span6">
                                            <label for="descricaoProduto">Descrição Produto/Serviço</label>
                                            <textarea class="span12" name="descricaoProduto" id="descricaoProduto" cols="30" rows="5"></textarea>
                                        </div>
                                        <div class="span6">
                                            <label for="defeito">Defeito</label>
                                            <textarea class="span12" name="defeito" id="defeito" cols="30" rows="5"></textarea>
                                        </div>

                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span6">
                                            <label for="observacoes">Observações</label>
                                            <textarea class="span12" name="observacoes" id="observacoes" cols="30" rows="5"></textarea>
                                        </div>
                                        <div class="span6">
                                            <label for="laudoTecnico">Laudo Técnico</label>
                                            <textarea class="span12" name="laudoTecnico" id="laudoTecnico" cols="30" rows="5"></textarea>
                                        </div>
                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span6 offset3" style="text-align: center">
                                            <button class="btn btn-success" id="btnContinuar"><i class="icon-share-alt icon-white"></i> Continuar</button>
                                            <a href="<?=site_url('os')?>" class="btn"><i class="icon-arrow-left"></i> Voltar</a>
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
<!-- Modal novo cliente -->
<div id="modalCliente" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
<?=form_open(site_url('clientes/adicionarAjax'),'class="form-horizontal" id="formCliente"') ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">MapOS - Adicionar Despesa</h3>
  </div>

<!-- <form action="<?=site_url('clientes/adicionarAjax')?>" id="formCliente" method="post" class="form-horizontal" > -->
  <div class="modal-body">
                    <div class="control-group">
                        <label for="nomeCliente" class="control-label">Nome<span class="required">*</span></label>
                        <div class="controls">
                            <input id="nomeCliente" type="text" name="nomeCliente" value="<?=set_value('nomeCliente')?>"  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="documento" class="control-label">CPF/CNPJ<span class="required">*</span></label>
                        <div class="controls">
                            <input id="documento" type="text" name="documento" value="<?=set_value('documento')?>"  />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="telefone" class="control-label">Telefone<span class="required">*</span></label>
                        <div class="controls">
                            <input id="telefone" type="text" name="telefone" value="<?=set_value('telefone')?>"  />
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="celular" class="control-label">Celular</label>
                        <div class="controls">
                            <input id="celular" type="text" name="celular" value="<?=set_value('celular')?>"  />
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="email" class="control-label">Email<span class="required">*</span></label>
                        <div class="controls">
                            <input id="email" type="text" name="email" value="<?=set_value('email')?>"  />
                        </div>
                    </div>

                    <div class="control-group" class="control-label">
                        <label for="rua" class="control-label">Rua<span class="required">*</span></label>
                        <div class="controls">
                            <input id="rua" type="text" name="rua" value="<?=set_value('rua')?>"  />
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="numero" class="control-label">Número<span class="required">*</span></label>
                        <div class="controls">
                            <input id="numero" type="text" name="numero" value="<?=set_value('numero')?>"  />
                        </div>
                    </div>

                    <div class="control-group" class="control-label">
                        <label for="bairro" class="control-label">Bairro<span class="required">*</span></label>
                        <div class="controls">
                            <input id="bairro" type="text" name="bairro" value="<?=set_value('bairro')?>"  />
                        </div>
                    </div>

                    <div class="control-group" class="control-label">
                        <label for="cidade" class="control-label">Cidade<span class="required">*</span></label>
                        <div class="controls">
                            <input id="cidade" type="text" name="cidade" value="<?=set_value('cidade')?>"  />
                        </div>
                    </div>

                    <div class="control-group" class="control-label">
                        <label for="estado" class="control-label">Estado<span class="required">*</span></label>
                        <div class="controls">
                            <input id="estado" type="text" name="estado" value="<?=set_value('estado')?>"  />
                        </div>
                    </div>

                    <div class="control-group" class="control-label">
                        <label for="cep" class="control-label">CEP<span class="required">*</span></label>
                        <div class="controls">
                            <input id="cep" type="text" name="cep" value="<?=set_value('cep')?>"  />
                        </div>
                    </div>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
    <button type="submit" class="btn btn-success"><i class="icon-plus icon-white"></i> Adicionar</button>
  </div>
</form>
  </form>
</div>
<script type="text/javascript">
$(document).ready(function(){
      $("#cliente").autocomplete({
            source: "<?=site_url('os/autoCompleteCliente')?>",
            minLength: 1,
            select: function( event, ui ) {
                 $("#clientes_id").val(ui.item.id);
            }
      });
      $("#tecnico").autocomplete({
            source: "<?=site_url('os/autoCompleteUsuario')?>",
            minLength: 1,
            select: function( event, ui ) {
                 $("#usuarios_id").val(ui.item.id);
            }
      });
      $("#formOs").validate({
          rules:{
             cliente: {required:true},
             tecnico: {required:true},
             dataInicial: {required:true}
          },
          messages:{
             cliente: {required: 'Campo Requerido.'},
             tecnico: {required: 'Campo Requerido.'},
             dataInicial: {required: 'Campo Requerido.'}
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
    $('#formCliente').validate({
            rules :{
                  nomeCliente:{ required: true},
                  documento:{ required: true},
                  telefone:{ required: true},
                  email:{ required: true},
                  rua:{ required: true},
                  numero:{ required: true},
                  bairro:{ required: true},
                  cidade:{ required: true},
                  estado:{ required: true},
                  cep:{ required: true}
            },
            messages:{
                  nomeCliente :{ required: 'Campo Requerido.'},
                  documento :{ required: 'Campo Requerido.'},
                  telefone:{ required: 'Campo Requerido.'},
                  email:{ required: 'Campo Requerido.'},
                  rua:{ required: 'Campo Requerido.'},
                  numero:{ required: 'Campo Requerido.'},
                  bairro:{ required: 'Campo Requerido.'},
                  cidade:{ required: 'Campo Requerido.'},
                  estado:{ required: 'Campo Requerido.'},
                  cep:{ required: 'Campo Requerido.'}
            },
            submitHandler: function(form){
                        var dados = $(form).serialize();
                        $.ajax({
                          type: "POST",
                          url: "<?=site_url('clientes/adicionarAjax?ajax=true') ?>",
                          data: dados,
                          dataType: 'json',
                          success: function(data)
                          {
                            if(data.result == true){
                                // window.location.href = "<?=site_url()?>";
                                // $('#modalCliente').toggle();
                                $('.close').trigger('click');
                                $('.row-fluid').prepend('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button>Sucesso ao adicionar cliente!</div>');
                            }
                            else{
                                $('#call-modal').trigger('click');
                                $('.row-fluid').prepend('<div class="alert alert-error"><button class="close" data-dismiss="alert">×</button>Erro ao adicionar cliente!</div>');
                            }
                          }
                          });
                          return false;
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
    // $('#formCliente').submit()
});
</script>
