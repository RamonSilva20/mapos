  <head>
    <title>MAPOS</title>
    <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo base_url();?>css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url();?>css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="<?php echo base_url();?>css/fullcalendar.css" />
    <link rel="stylesheet" href="<?php echo base_url();?>css/main.css" />
    <link rel="stylesheet" href="<?php echo base_url();?>css/blue.css" class="skin-color" />
    <script type="text/javascript"  src="<?php echo base_url();?>js/jquery-1.10.2.min.js"></script>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
 
  <body style="background-color: transparent">



      <div class="container-fluid">
    
          <div class="row-fluid">
              <div class="span12">

                  <div class="widget-box">
                      <div class="widget-title">
                          <h4 style="text-align: center">Relatório Financeiro</h4>
                      </div>
                      <div class="widget-content nopadding">

                  <table class="table table-bordered">
                      <thead>
                          <tr>
                              <th style="font-size: 1.2em; padding: 5px;">Cliente/Fornecedor</th>
                              <th style="font-size: 1.2em; padding: 5px;">Tipo</th>
                              <th style="font-size: 1.2em; padding: 5px;">Valor</th>
                              <th style="font-size: 1.2em; padding: 5px;">Vencimento</th>
                              <th style="font-size: 1.2em; padding: 5px;">Pagamento</th>
                              <th style="font-size: 1.2em; padding: 5px;">Forma de Pagamento</th>
                              <th style="font-size: 1.2em; padding: 5px;">Situação</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php
                          $totalReceita = 0;
                          $totalDespesa = 0;
                          $saldo = 0;
                          foreach ($lancamentos as $l) {
                              $vencimento = date('d/m/Y', strtotime($l->data_vencimento));
                              $pagamento = date('d/m/Y', strtotime($l->data_pagamento));
                              if($l->baixado == 1){$situacao = 'Pago';}else{ $situacao = 'Pendente';}
                              if($l->tipo == 'receita'){ $totalReceita += $l->valor;} else{ $totalDespesa += $l->valor;}
                              echo '<tr>';
                              echo '<td>' . $l->cliente_fornecedor . '</td>';
                              echo '<td>' . $l->tipo . '</td>';
                              echo '<td>' . $l->valor . '</td>';
                              echo '<td>' . $vencimento. '</td>';
                              echo '<td>' . $pagamento. '</td>';
                              echo '<td>' . $l->forma_pgto . '</td>';
                              echo '<td>' . $situacao. '</td>';
                              echo '</tr>';
                          }
                          ?>
                      </tbody>
                       <tfoot>
                          <tr>
                            <td colspan="5" style="text-align: right; color: green"> <strong>Total Receitas:</strong></td>
                            <td colspan="2" style="text-align: left; color: green"><strong>R$ <?php echo number_format($totalReceita,2,',','.') ?></strong></td>
                          </tr>
                          <tr>
                            <td colspan="5" style="text-align: right; color: red"> <strong>Total Despesas:</strong></td>
                            <td colspan="2" style="text-align: left; color: red"><strong>R$ <?php echo number_format($totalDespesa,2,',','.') ?></strong></td>
                          </tr>
                          <tr>
                            <td colspan="5" style="text-align: right"> <strong>Saldo:</strong></td>
                            <td colspan="2" style="text-align: left;"><strong>R$ <?php echo number_format($totalReceita - $totalDespesa,2,',','.') ?></strong></td>
                          </tr>
                        </tfoot>
                  </table>
                  
                  </div>
                   
              </div>
                  <h5 style="text-align: right">Data do Relatório: <?php echo date('d/m/Y');?></h5>

          </div>
     


      </div>
</div>




            <!-- Arquivos js-->

            <script src="<?php echo base_url();?>js/excanvas.min.js"></script>
            <script src="<?php echo base_url();?>js/bootstrap.min.js"></script>
            <script src="<?php echo base_url();?>js/sosmc.js"></script>
            <script src="<?php echo base_url();?>js/dashboard.js"></script>
  </body>
</html>








