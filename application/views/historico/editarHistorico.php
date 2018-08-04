<link rel="stylesheet" href="<?php echo base_url();?>js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url()?>js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<div class="row-fluid" style="margin-top:0">
    <div class="span12">
          <div class="widget-box">
                <div class="widget-title">
                      <span class="icon">
                            <i class="icon-align-justify"></i>
                      </span>
                      <h5>Editar Compra</h5>
                </div>
                <div class="widget-content nopadding">
                      <?php echo $custom_error; ?>
                      <form id="formCompra" action="<?php echo current_url(); ?>" method="post">
                        <?php echo form_hidden('idCompras',$result->idCompras) ?>
                        <div class="span12" style="padding: 1%; margin-left: 0">
                            <div class="span4" style="margin-left: 0">
                                <label for="solicitante">Solicitante<span class="required">*</span></label>
                                <select class="span12" name="solicitante" id="solicitante">
                                    <option <?php if($result->solicitante == 'selecione'){echo 'selected';} ?> value="selecione">Selecione</option>
                                    <option <?php if($result->solicitante == 'Alan Silveira'){echo 'selected';} ?> value="Alan Silveira">Alan Silveira</option>
                                    <option <?php if($result->solicitante == 'Celso Torok'){echo 'selected';} ?> value="Celso Torok">Celso Torok</option>
                                    <option <?php if($result->solicitante == 'José Marques'){echo 'selected';} ?> value="José Marques">José Marques</option>
                                    <option <?php if($result->solicitante == 'Rafael Marques'){echo 'selected';} ?> value="Rafael Marques">Rafael Marques</option>
                                    <option <?php if($result->solicitante == 'Rosiane Ribeiro'){echo 'selected';} ?> value="Rosiane Ribeiro">Rosiane Ribeiro</option>
                                    <option <?php if($result->solicitante == 'Shirley Marques'){echo 'selected';} ?> value="Shirley Marques">Shirley Marques</option>
                                </select>
                            </div>
                            <div class="span4">
                                <label for="comprador">Comprador</label>
                                <select class="span12" name="comprador" id="comprador">
                                    <option <?php if($result->comprador == 'selecione'){echo 'selected';} ?> value="selecione">Selecione</option>
                                    <option <?php if($result->comprador == 'Alan Silveira'){echo 'selected';} ?> value="Alan Silveira">Alan Silveira</option>
                                    <option <?php if($result->comprador == 'Celso Torok'){echo 'selected';} ?> value="Celso Torok">Celso Torok</option>
                                    <option <?php if($result->comprador == 'José Marques'){echo 'selected';} ?> value="José Marques">José Marques</option>
                                    <option <?php if($result->comprador == 'Rafael Marques'){echo 'selected';} ?> value="Rafael Marques">Rafael Marques</option>
                                    <option <?php if($result->comprador == 'Rosiane Ribeiro'){echo 'selected';} ?> value="Rosiane Ribeiro">Rosiane Ribeiro</option>
                                    <option <?php if($result->comprador == 'Shirley Marques'){echo 'selected';} ?> value="Shirley Marques">Shirley Marques</option>
                                </select>
                            </div>
                            <div class="span4">
                                <label for="fornecedor">Fornecedor</label>
                                <input type="text" class="span12" name="fornecedor" id="fornecedor" value="<?php echo $result->fornecedor ?>" />
                            </div>
                        </div>

                        <div class="span12" style="padding: 1%; margin-left: 0">
                            <div class="span2">
                                <label for="idOs" class="control-label">Nro OS<span class="required">*</span></label>
                                <input type="text" class="span12" name="idOs" id="idOs" value="<?php echo $result->idOs ?>" />
                            </div>
                            <div class="span6">
                                <label for="descricao" class="control-label">Descrição<span class="required">*</span></label>
                                <input type="text" class="span12" name="descricao" id="descricao" value="<?php echo $result->descricao ?>" />
                            </div>
                            <div class="span4">
                                <label for="preco" class="control-label">Preço</label>
                                <div class="controls">
                                    <input id="valor" class="money" type="text" name="valor" value="<?php echo $result->valor ?>"  />
                                </div>
                            </div>
                        </div>

                        <div class="span12" style="padding: 1%; margin-left: 0">
                            <div class="span3">
                                <label for="status">Status<span class="required">*</span></label>
                                <select class="span12" name="status" id="status" value="">
                                    <option <?php if($result->status == 'Orçamento'){echo 'selected';} ?> value="Orçamento">Orçamento</option>
                                    <option <?php if($result->status == 'Pedido Efetuado'){echo 'selected';} ?> value="Pedido Efetuado">Pedido Efetuado</option>
                                    <option <?php if($result->status == 'Faturado'){echo 'selected';} ?> value="Faturado">Faturado</option>
                                    <option <?php if($result->status == 'Cancelado'){echo 'selected';} ?> value="Cancelado">Cancelado</option>
                                </select>
                            </div>
                            <div class="span3">
                                <label for="dataPedido" class="control-label">Data do Pedido</label>
                                <input id="dataPedido" class="span12 datepicker" type="text" name="dataPedido" value="<?php echo date('d/m/Y', strtotime($result->dataPedido)); ?>"  />
                            </div>
                            <div class="span3">
                                <label for="dataPrevista" class="control-label">Data Prevista</label>
                                <input id="dataPrevista" class="span12 datepicker" type="text" name="dataPrevista" value="<?php echo date('d/m/Y', strtotime($result->dataPrevista)); ?>"  />
                            </div>
                            <div class="span3">
                                <label for="rastreio" class="control-label">Rastreio</label>
                                <input type="text" class="span10" name="rastreio" id="rastreio" value="<?php echo $result->rastreio ?>" />
                                <a target="_blank" href="http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI=<?php echo $result->rastreio ?>" class="tip-bottom" data-original-title="Pesquisar"><i class="icon-search icon-white"></i></a>
                            </div>
                        </div>

                        <div class="form-actions">
                            <div class="span12" style="padding: 1%; margin-left: 0">
                                <div class="span6 offset3" style="text-align: center">
                                    <button class="btn btn-success"><i class="icon-plus icon-white"></i> Editar</button>
                                    <?php if($result->faturado == 0 && $result->status == 'Faturado'){ ?>
                                        <a href="#modal-faturar" id="btn-faturar" role="button" data-toggle="modal" class="btn btn-success"><i class="icon-file"></i> Faturar</a>
                                    <?php } else if($result->faturado == 1 && $result->status == 'Faturado') { ?>
                                        <a class="btn btn-success disabled"><i class="icon-file"></i> Faturado</a>
                                    <?php } else { ?>
                                        <div class="alert alert-danger">
                                            <span>Antes de Faturar Altere o Status para Faturado</span>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
          </div>
    </div>
</div>

<!-- Modal Faturar-->
<div id="modal-faturar" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
<form id="formFaturar" action="<?php echo current_url() ?>" method="post">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
  <h3 id="myModalLabel">Faturar Compra</h3>
</div>
<div class="modal-body">
    
    <div class="span12 alert alert-info" style="margin-left: 0"> Obrigatório o preenchimento dos campos com asterisco.</div>
    <div class="span12" style="margin-left: 0"> 
      <label for="descricaoFaturar">Descrição</label>
      <input class="span12" id="descricaoFaturar" type="text" name="descricaoFaturar" value="Fatura de Compra - #<?php echo $result->idOs; ?> "  />
      
    </div>  
    <div class="span12" style="margin-left: 0"> 
      <div class="span12" style="margin-left: 0"> 
        <label for="fornecedorFaturar">Fornecedor*</label>
        <input class="span12" id="fornecedorFaturar" type="text" name="fornecedorFaturar" value="<?php echo $result->fornecedor ?>" />
        <input type="hidden" name="os_id" id="os_id" value="<?php echo $result->idOs; ?>">
      </div>
      
      
    </div>
    <div class="span12" style="margin-left: 0"> 
      <div class="span4" style="margin-left: 0">  
        <label for="valorFaturar">Valor*</label>
        <input type="hidden" id="tipo" name="tipo" value="despesa" /> 
        <input class="span12 money" id="valorFaturar" type="text" name="valorFaturar" value="<?php echo number_format($result->valor,2); ?>"  />
      </div>
      <div class="span4" >
        <label for="pedido">Data do Pedido*</label>
        <input class="span12 datepicker" id="pedido" type="text" name="pedido" value="<?php echo date('d/m/Y', strtotime($result->dataPedido)); ?>" />
      </div>
      
    </div>
    
    <div class="span12" style="margin-left: 0"> 
      <div class="span4" style="margin-left: 0">
        <label for="recebido">Foi pago?</label>
        &nbsp &nbsp &nbsp &nbsp <input  id="pago" type="checkbox" name="pago" value="1" /> 
      </div>
      <div id="divPagamento" class="span8" style=" display: none">
        <div class="span6">
          <label for="pagamento">Data do Pagamento</label>
          <input class="span12 datepicker" id="pagamento" type="text" name="pagamento" value="<?php echo date('d/m/Y', strtotime($result->dataPedido)); ?>" /> 
        </div>
        <div class="span6">
          <label for="formaPgto">Forma Pgto</label>
          <select name="formaPgto" id="formaPgto" class="span12">
            <option value="Dinheiro">Dinheiro</option>
            <option value="Cartão de Crédito">Cartão de Crédito</option>
            <option value="Cheque">Cheque</option>
            <option value="Boleto">Boleto</option>
            <option value="Depósito">Depósito</option>
            <option value="Débito">Débito</option>        
          </select> 
      </div>
      
    </div>
    </div>
    
</div>
<div class="modal-footer">
  <button class="btn" data-dismiss="modal" aria-hidden="true" id="btn-cancelar-faturar">Cancelar</button>
  <button class="btn btn-primary">Faturar</button>
</div>
</form>
</div>

<script src="<?php echo base_url()?>js/jquery.validate.js"></script>
<script src="<?php echo base_url();?>js/maskmoney.js"></script>
<script type="text/javascript">
      $(document).ready(function(){
        $(".money").maskMoney();

         $('#pago').click(function(event) {
            var flag = $(this).is(':checked');
            if(flag == true){
              $('#divPagamento').show();
            }
            else{
              $('#divPagamento').hide();
            }
         });
         
         $("#formFaturar").validate({
              rules:{
                 descricaoFaturar: {required:true},
                 fornecedorFaturar: {required:true}
              },
              messages:{
                 descricaoFaturar: {required: 'Campo Requerido.'},
                 fornecedorFaturar: {required: 'Campo Requerido.'}
              },
              submitHandler: function( form ){       
                var dados = $( form ).serialize();
                $('#btn-cancelar-faturar').trigger('click');
                $.ajax({
                  type: "POST",
                  url: "<?php echo base_url();?>index.php/compras/faturar",
                  data: dados,
                  dataType: 'json',
                  success: function(data)
                  {
                    if(data.result == true){
                        
                        window.location.reload(true);
                    }
                    else{
                        alert('Ocorreu um erro ao tentar faturar a Compra 3.');
                        $('#progress-fatura').hide();
                    }
                  }
                  });

                  return false;
              }
         });
      });
      $(".datepicker" ).datepicker({ dateFormat: 'dd/mm/yy' });
</script>



