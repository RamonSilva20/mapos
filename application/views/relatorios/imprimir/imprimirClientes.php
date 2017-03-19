  <head>
    <title>MAPOS</title>
    <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo base_url();?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url();?>assets/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="<?php echo base_url();?>assets/css/fullcalendar.css" />
    <link rel="stylesheet" href="<?php echo base_url();?>assets/css/main.css" />
    <link rel="stylesheet" href="<?php echo base_url();?>assets/css/blue.css" class="skin-color" />
    <script type="text/javascript"  src="<?php echo base_url();?>assets/js/jquery-1.10.2.min.js"></script>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
 
  <body style="background-color: transparent">



      <div class="container-fluid">
    
          <div class="row-fluid">
              <div class="span12">

                  <div class="widget-box">
                      <div class="widget-title">
                          <h4 style="text-align: center">Clientes</h4>
                      </div>
                      <div class="widget-content nopadding">

                  <table class="table table-bordered">
                      <thead>
                          <tr>
                              <th style="font-size: 1.2em; padding: 5px;">Nome</th>
                              <th style="font-size: 1.2em; padding: 5px;">Documento</th>
                              <th style="font-size: 1.2em; padding: 5px;">Telefone</th>
                              <th style="font-size: 1.2em; padding: 5px;">Email</th>
                              <th style="font-size: 1.2em; padding: 5px;">Cadastro</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php
                          foreach ($clientes as $c) {
                              $dataCadastro = date('d/m/Y', strtotime($c->dataCadastro));
                              echo '<tr>';
                              echo '<td>' . $c->nomeCliente . '</td>';
                              echo '<td>' . $c->documento . '</td>';
                              echo '<td>' . $c->telefone . '</td>';
                              echo '<td>' . $c->email . '</td>';
                              echo '<td>' . $dataCadastro . '</td>';
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


  </body>
</html>







