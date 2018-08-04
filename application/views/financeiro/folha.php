<link rel="stylesheet" href="<?php echo base_url();?>js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />

<script type="text/javascript" src="<?php echo base_url()?>js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>



<?php $situacao = $this->input->get('situacao');

    $periodo = $this->input->get('periodo');  

 ?>



<style type="text/css">

  

  label.error{

    color: #b94a48;

  }



  input.error{

    border-color: #b94a48;

  }

  input.valid{

    border-color: #5bb75b;

  }





</style>

<?php if($this->permission->checkPermission($this->session->userdata('permissao'),'aLancamento')){ ?>

  <div class="span12" style="margin-left: 0; margin-bottom: 20px;">

      <a href="#modalDespesa" data-toggle="modal" role="button" class="btn btn-danger tip-bottom" title="Cadastrar novo pagamento"><i class="icon-plus icon-white"></i> Novo Pagamento</a>

  </div>

<?php } ?>

<div class="span12" style="margin-left: 0">

  <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'rCliente')){ ?>

  <form action="<?php echo current_url(); ?>" method="get" >

    <div class="span4" style="margin-left: 0">

      <label>Recurso <i class="icon-info-sign tip-top" title="Nome do Recurso"></i></label>
      <select class="span12" name="recurso" id="recurso">
          <option value="selecione">Selecione</option>
          <option value="Celso Torok">Celso Torok</option>
          <option value="José Marques">José Marques</option>
          <option value="Rafael Marques">Rafael Marques</option>
          <option value="Rosiane Ribeiro">Rosiane Ribeiro</option>
          <option value="Shirley Marques">Shirley Marques</option>
      </select>
    </div>

    <div class="span2">
      <label>Data Inicio <i class="icon-info-sign tip-top" title="Lançamentos com vencimento em uma data específica ou o inicio de um periodo."></i></label>
      <input name="dataInicial" class="span12 datepicker-eua" type="text" value  />
    </div>
    <div class="span2">
      <label>Data Fim <i class="icon-info-sign tip-top" title="Data final de um periodo."></i></label>
      <input name="dataFinal" class="span12 datepicker-eua" type="text" value  />
    </div>

    <div class="span2">

      <label>Período <i class="icon-info-sign tip-top" title="Lançamentos com vencimento no período."></i></label>

      <select name="periodo" class="span12">

        <option value="dia">Dia</option>

        <option value="semana" <?php if($periodo == 'semana'){ echo 'selected';} ?>>Semana</option>

        <option value="mes" <?php if($periodo == 'mes'){ echo 'selected';} ?>>Mês</option>

        <option value="ano" <?php if($periodo == 'ano'){ echo 'selected';} ?>>Ano</option>

        <option value="todos" <?php if($periodo == 'todos'){ echo 'selected';} ?>>Todos</option>

      </select>

    </div>

    <div class="span2" >

      &nbsp

      <button type="submit" class="span12 btn btn-primary">Filtrar</button>

    </div>

    

  </form>

  <?php } ?>

</div>



<div class="span12" style="margin-left: 0;">



<?php



if(!$results){?>

  <div class="widget-box">

     <div class="widget-title">

        <span class="icon">

            <i class="icon-tags"></i>

         </span>

        <h5>Lançamentos Financeiros - RH</h5>



     </div>



<div class="widget-content nopadding">





<table class="table table-bordered ">

    <thead>

        <tr style="backgroud-color: #2D335B">

            <th>#</th>

            <th>Tipo</th>

            <th>Recurso</th>

            <th>Descrição</th>

            <th>Data</th>

            <th>Forma Pgto</th>

            <th>Valor</th>

        </tr>

    </thead>

    <tbody>



        <tr>

            <td colspan="7">Nenhuma lançamento encontrado</td>

        </tr>

    </tbody>

</table>

</div>

</div>

<?php } else{?>





<div class="widget-box">

     <div class="widget-title">

        <span class="icon">

            <i class="icon-tags"></i>

         </span>

        <h5>Lançamentos Financeiros - RH</h5>



     </div>



<div class="widget-content nopadding">





<table class="table table-bordered " id="divLancamentos">

    <thead>

        <tr style="backgroud-color: #2D335B">

            <th>#</th>

            <th>Tipo</th>

            <th>Recurso</th>

            <th>Descrição</th>

            <th>Data</th>

            <th>Forma Pgto</th>

            <th>Valor</th>

        </tr>

    </thead>

    <tbody>

        <?php 

        $totalReceita = 0;

        $totalDespesa = 0;

        $saldo = 0;

        $operador = $this->session->userdata('nome');

        foreach ($results as $r) {

            $vencimento = date(('d/m/Y'),strtotime($r->data_vencimento));

            if($r->baixado == 0){$status = 'Pendente';}else{ $status = 'Pago';};

            if($r->tipo == 'receita'){ $label = 'success'; $totalReceita += $r->valor;} else{$label = 'important'; $totalDespesa += $r->valor;}

            $boss = $r->cliente_fornecedor;

            //escreve a linha se houver permissão

            //if( $boss == 'Pão de Açucar' && $operador != 'Rosiane'){

            //  echo $boss;

            //}

            //if( $boss == 'Rafael Marques' && $operador != 'Rosiane' ){

              echo '<tr>';

              echo '<td>'.$r->idLancamentos.'</td>';

              echo '<td><span class="label label-'.$label.'">'.ucfirst($r->tipo).'</span></td>';

              echo '<td>'.$r->cliente_fornecedor.'</td>';

              echo '<td>'.$r->descricao.'</td>';

              echo '<td>'.$vencimento.'</td>';   

              echo '<td>'.$r->forma_pgto.'</td>';

              echo '<td> R$ '.number_format($r->valor,2,',','.').'</td>';

              echo '</tr>';

            //} else {

              //nada

            //}

            // fim da linha

        }?>

        <tr>

            

        </tr>

    </tbody>

    <tfoot>
      <tr>

        <td colspan="6" style="text-align: right; color: red"> <strong>Total Pago:</strong></td>

        <td colspan="1" style="text-align: left; color: red"><strong>R$ <?php echo number_format($totalDespesa,2,',','.') ?></strong></td>

      </tr>

    </tfoot>

</table>

</div>

</div>



</div>

  

<?php echo $this->pagination->create_links();}?>








<!-- Modal novo pagamento -->

<div id="modalDespesa" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

  <form id="formDespesa" action="<?php echo base_url() ?>index.php/financeiro/adicionarDespesa" method="post">

  <div class="modal-header">

    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

    <h3 id="myModalLabel">Adicionar Pagamento</h3>

  </div>

  <div class="modal-body">

      <div class="span12 alert alert-info" style="margin-left: 0"> Obrigatório o preenchimento dos campos com asterisco.</div>

      <div class="span12" style="margin-left: 0">

        <label for="descricao">Descrição</label>
        <select class="span12" id="descricao">
            <option value="selecione">Selecione</option>
            <option value="Retirada Avulsa">Retirada Avulsa</option>
            <option value="Pagamento de Salário">Pagamento de Salário</option>
            <option value="Auxílio Transporte/Alimentação">Auxílio Transporte/Alimentação</option>
            <option value="Outros">Outros</option>
        </select>
        <input class="span12" id="descricaoOutros" type="text" placeholder="Especifique" style="display: none;"  />
        <input id="urlAtual" type="hidden" name="urlAtual" value="<?php echo current_url() ?>"  />

      </div>  

      <div class="span12" style="margin-left: 0"> 

        <div class="span12" style="margin-left: 0"> 

          <label for="fornecedor">Recurso</label>
          <select class="span12" name="fornecedor" id="fornecedor">
              <option value="selecione">Selecione</option>
              <option value="Celso Torok">Celso Torok</option>
              <option value="José Marques">José Marques</option>
              <option value="Rafael Marques">Rafael Marques</option>
              <option value="Rosiane Ribeiro">Rosiane Ribeiro</option>
              <option value="Shirley Marques">Shirley Marques</option>
          </select>

        </div>

        

        

      </div>

      <div class="span12" style="margin-left: 0"> 

        <div class="span4" style="margin-left: 0">  

          <label for="valor">Valor*</label>

          <input type="hidden"  name="tipo" value="despesa" />  

          <input class="span12 money"  type="text" name="valor"  />

        </div>

        <div class="span4" >

          <label for="vencimento">Data Vencimento*</label>

          <input class="span12 datepicker"  type="text" name="vencimento"  />

        </div>

        

      </div>

      <div class="span12" style="margin-left: 0"> 

        <div class="span4" style="margin-left: 0">

          <label for="pago">Foi Pago?</label>

          &nbsp &nbsp &nbsp &nbsp<input  id="pago" type="checkbox" name="pago" value="1" /> 

        </div>

        <div id="divPagamento" class="span8" style=" display: none">

          <div class="span6">

            <label for="pagamento">Data Pagamento</label>

            <input class="span12 datepicker" id="pagamento" type="text" name="pagamento" /> 

          </div>



          <div class="span6">

            <label for="formaPgto">Forma Pgto</label>

            <select name="formaPgto"  class="span12">
              <option value="Dinheiro">Dinheiro</option>
              <option value="Cartão de Débito">Cartão de Débito</option>    
              <option value="Cartão de Crédito">Cartão de Crédito</option>
              <option value="Pagamento Eletrônico">Cheque</option>
              <option value="Boleto">Boleto</option>
              <option value="Depósito">Depósito</option>

            </select>

          </div>

        </div>

        

      </div>



  </div>

  <div class="modal-footer">

    <button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>

    <button class="btn btn-danger">Adicionar Pagamento</button>

  </div>

  </form>

</div>







<!-- Modal editar lançamento -->

<div id="modalEditar" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

  <form id="formEditar" action="<?php echo base_url() ?>index.php/financeiro/editar" method="post">

  <div class="modal-header">

    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

    <h3 id="myModalLabel">Editar Lançamento</h3>

  </div>

  <div class="modal-body">

      <div class="span12 alert alert-info" style="margin-left: 0"> Obrigatório o preenchimento dos campos com asterisco.</div>

      <div class="span12" style="margin-left: 0"> 

        <label for="descricao">Descrição</label>

        <input class="span12" id="descricaoEditar" type="text" name="descricao"  />

        <input id="urlAtualEditar" type="hidden" name="urlAtual" value=""  />

      </div>  

      <div class="span12" style="margin-left: 0"> 

        <div class="span12" style="margin-left: 0"> 

          <label for="fornecedor">Fornecedor / Empresa*</label>

          <input class="span12" id="fornecedorEditar" type="text" name="fornecedor"  />

        </div>

        

        

      </div>

      <div class="span12" style="margin-left: 0"> 

        <div class="span4" style="margin-left: 0">  

          <label for="valor">Valor*</label>

          <input type="hidden"  name="tipo" value="despesa" />  

          <input type="hidden"  id="idEditar" name="id" value="" /> 

          <input class="span12 money"  type="text" name="valor" id="valorEditar" />

        </div>

        <div class="span4" >

          <label for="vencimento">Data Vencimento*</label>

          <input class="span12 datepicker"  type="text" name="vencimento" id="vencimentoEditar"  />

        </div>

        <div class="span4">

          <label for="vencimento">Tipo*</label>

          <select class="span12" name="tipo" id="tipoEditar">

            <option value="receita">Receita</option>

            <option value="despesa">Despesa</option>

          </select>

        </div>

        

      </div>

      <div class="span12" style="margin-left: 0"> 

        <div class="span4" style="margin-left: 0">

          <label for="pago">Foi Pago?</label>

          &nbsp &nbsp &nbsp &nbsp<input  id="pagoEditar" type="checkbox" name="pago" value="1" /> 

        </div>

        <div id="divPagamentoEditar" class="span8" style=" display: none">

          <div class="span6">

            <label for="pagamento">Data Pagamento</label>

            <input class="span12 datepicker" id="pagamentoEditar" type="text" name="pagamento" />  

          </div>



          <div class="span6">

            <label for="formaPgto">Forma Pgto</label>

            <select name="formaPgto" id="formaPgtoEditar"  class="span12">

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

    <button class="btn" data-dismiss="modal" aria-hidden="true" id="btnCancelarEditar">Cancelar</button>

    <button class="btn btn-primary">Salvar Alterações</button>

  </div>

  </form>

</div>













<!-- Modal Excluir lançamento-->

<div id="modalExcluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

  <div class="modal-header">

    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

    <h3 id="myModalLabel">Excluir Lançamento</h3>

  </div>

  <div class="modal-body">

    <h5 style="text-align: center">Deseja realmente excluir esse lançamento?</h5>

    <input name="id" id="idExcluir" type="hidden" value="" />

  </div>

  <div class="modal-footer">

    <button class="btn" data-dismiss="modal" aria-hidden="true" id="btnCancelExcluir">Cancelar</button>

    <button class="btn btn-danger" id="btnExcluir">Excluir Lançamento</button>

  </div>

</div>











<script src="<?php echo base_url()?>js/jquery.validate.js"></script>

<script src="<?php echo base_url();?>js/maskmoney.js"></script>

<script type="text/javascript">

  jQuery(document).ready(function($) {



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





    $('#recebido').click(function(event) {

      var flag = $(this).is(':checked');

      if(flag == true){

        $('#divRecebimento').show();

      }

      else{

        $('#divRecebimento').hide();

      }

    });



    $('#pagoEditar').click(function(event) {

      var flag = $(this).is(':checked');

      if(flag == true){

        $('#divPagamentoEditar').show();

      }

      else{

        $('#divPagamentoEditar').hide();

      }

    });





    $("#formReceita").validate({

          rules:{

             descricao: {required:true},

             cliente: {required:true},

             valor: {required:true},

             vencimento: {required:true}

      

          },

          messages:{

             descricao: {required: 'Campo Requerido.'},

             cliente: {required: 'Campo Requerido.'},

             valor: {required: 'Campo Requerido.'},

             vencimento: {required: 'Campo Requerido.'}

          }

    });







    $("#formDespesa").validate({

          rules:{

             // descricao: {required:true},

             fornecedor: {required:true},

             valor: {required:true},

             vencimento: {required:true}

      

          },

          messages:{

             // descricao: {required: 'Campo Requerido.'},

             fornecedor: {required: 'Campo Requerido.'},

             valor: {required: 'Campo Requerido.'},

             vencimento: {required: 'Campo Requerido.'}

          }

        });

    



    $(document).on('click', '.excluir', function(event) {

      $("#idExcluir").val($(this).attr('idLancamento'));

    });





    $(document).on('click', '.editar', function(event) {

      $("#idEditar").val($(this).attr('idLancamento'));

      $("#descricaoEditar").val($(this).attr('descricao'));

      $("#fornecedorEditar").val($(this).attr('cliente'));

      $("#valorEditar").val($(this).attr('valor'));

      $("#vencimentoEditar").val($(this).attr('vencimento'));

      $("#pagamentoEditar").val($(this).attr('pagamento'));

      $("#formaPgtoEditar").val($(this).attr('formaPgto'));

      $("#tipoEditar").val($(this).attr('tipo'));

      $("#urlAtualEditar").val($(location).attr('href'));

      var baixado = $(this).attr('baixado');

      if(baixado == 1){

        $("#pagoEditar").attr('checked', true);

        $("#divPagamentoEditar").show();

      }

      else{

        $("#pagoEditar").attr('checked', false); 

        $("#divPagamentoEditar").hide();

      }

      



    });



    $(document).on('click', '#btnExcluir', function(event) {

        var id = $("#idExcluir").val();

    

        $.ajax({

          type: "POST",

          url: "<?php echo base_url();?>index.php/financeiro/excluirLancamento",

          data: "id="+id,

          dataType: 'json',

          success: function(data)

          {

            if(data.result == true){

                $("#btnCancelExcluir").trigger('click');

                $("#divLancamentos").html('<div class="progress progress-striped active"><div class="bar" style="width: 100%;"></div></div>');

                $("#divLancamentos").load( $(location).attr('href')+" #divLancamentos" );

                

            }

            else{

                $("#btnCancelExcluir").trigger('click');

                alert('Ocorreu um erro ao tentar excluir produto.');

            }

          }

        });

        return false;

    });

 

    $(".datepicker-eua" ).datepicker({ dateFormat: 'y-m-d' });

    $(".datepicker" ).datepicker({ dateFormat: 'dd/mm/yy' });


    $( "#descricao" ).change(function() {
      if ( $(this).val() == "Outros") {
          $(this).attr('name','');
          $( "#descricaoOutros" ).attr('name','descricao').show();
      } else {
          $(this).attr('name','descricao');
          $( "#descricaoOutros" ).attr('name','').hide();
      }
    });


  });



</script>





