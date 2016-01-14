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
                          <h4 style="text-align: center">Produtos</h4>
                      </div>
                      <div class="widget-content nopadding">

                  <table class="table table-bordered">
                      <thead>
                          <tr>
                              <th style="font-size: 1.2em; padding: 5px;">Nome</th>
                              <th style="font-size: 1.2em; padding: 5px;">UN</th>
                              <th style="font-size: 1.2em; padding: 5px;">Preço Compra</th>
                              <th style="font-size: 1.2em; padding: 5px;">Preço Venda</th>
                              <th style="font-size: 1.2em; padding: 5px;">Estoque</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php
                          foreach ($produtos as $p) {
                              echo '<tr>';
                              echo '<td>' . $p->descricao. '</td>';
                              echo '<td>' . $p->unidade . '</td>';
                              echo '<td>' . $p->precoCompra . '</td>';
                              echo '<td>' . $p->precoVenda . '</td>';
                              echo '<td>' . $p->estoque. '</td>';
                              echo '</tr>';
                          }
                          ?>
                      </tbody>
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

