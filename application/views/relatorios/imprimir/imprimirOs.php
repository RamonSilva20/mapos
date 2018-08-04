<!DOCTYPE html>
<html>
  <head>
    <title>MAPOS - Relatórios</title>
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
                          <h4 style="text-align: center">Ordens de Serviço</h4>
                      </div>
                      <div class="widget-content nopadding">

                  <table class="table table-bordered">
                      <thead>
                          <tr>
                              <th style="font-size: 1.2em; padding: 5px;">Cliente</th>
                              <th style="font-size: 1.2em; padding: 5px;">Status</th>
                              <th style="font-size: 1.2em; padding: 5px;">Data</th>
                              <th style="font-size: 1.2em; padding: 5px;">Descrição</th>
                              <th style="font-size: 1.2em; padding: 5px;">Valor</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php
                          $totalOs = 0;
                          foreach ($os as $c) {
                              $totalOs += $c->valorTotal;

                              echo '<tr>';
                              echo '<td>' . $c->nomeCliente . '</td>';
                              echo '<td>' . $c->status . '</td>';
                              echo '<td>' . date('d/m/Y',  strtotime($c->dataInicial)) . '</td>';
                              echo '<td>' . $c->descricaoProduto. '</td>';
                              echo '<td>' . number_format($c->valorTotal,2,',','.') . '</td>';
                              echo '</tr>';
                          }
                          ?>
                      </tbody>
                      <tfoot>
                          <tr>
                            <?php 
                              if ($c->usuarios_id == "5") {
                                $tecnico = "Celso Torok";
                              } elseif ($c->usuarios_id == "6") {
                                $tecnico = "José Marques";
                              } elseif ($c->usuarios_id == "3") {
                                $tecnico = "Rafael Marques";
                              }
                            ?>
                            <td colspan="4" style="text-align: right; color: green"><?php echo $tecnico ?> <strong>Total:</strong></td>
                            <td colspan="1" style="text-align: left; color: green"><strong><?php echo number_format($totalOs,2,',','.') ?></strong></td>
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
            <script src="<?php echo base_url();?>js/jquery.flot.min.js"></script>
            <script src="<?php echo base_url();?>js/jquery.flot.resize.min.js"></script>
            <script src="<?php echo base_url();?>js/jquery.peity.min.js"></script>
            <script src="<?php echo base_url();?>js/fullcalendar.min.js"></script>
            <script src="<?php echo base_url();?>js/sosmc.js"></script>
            <script src="<?php echo base_url();?>js/dashboard.js"></script>
  </body>
</html>







