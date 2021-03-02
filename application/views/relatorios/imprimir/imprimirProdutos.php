<!DOCTYPE html>
<html>

<head>
    <title>MAPOS</title>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/fullcalendar.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/main.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/blue.css" class="skin-color" />
</head>

<body style="background-color: transparent">
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div class="widget-box">
                    <?= $topo ?>
                    <div class="widget-title">
                        <h4 style="text-align: center; font-size: 1.1em; padding: 5px;">
                            <?= ucfirst($title) ?>
                        </h4>
                    </div>
                    <div class="widget_content nopadding">

                        <table width="1300" class="table_v">
                      <thead>
                          <tr>
                          <th width="690" align="center" style="font-size: 15px">Nome</th>
                          <th width="130" align="center" style="font-size: 15px">Cod. Produto</th>
                          <th width="150" align="center" style="font-size: 15px">Cod. Barras</th>
                          <th width="130" align="center" style="font-size: 15px">Preço Compra</th>
                          <th width="130" align="center" style="font-size: 15px">Preço Venda</th>
                          <th width="90" align="center" style="font-size: 15px">Estoque</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php
                          foreach ($produtos as $p) {
                              echo '<tr>';
                              echo '<td>' . $p->descricao. '</td>';
                              echo '<td align="center">' . $p->idProdutos . '</td>';
							  echo '<td align="center">' . $p->codDeBarra . '</td>';
                              echo '<td align="center">R$: ' . $p->precoCompra . '</td>' ;
                              echo '<td align="center">R$: ' . $p->precoVenda . '</td>' ;
                              echo '<td align="center">' . $p->estoque. '</td>';
                              echo '</tr>';
                          }
                          ?>
                      </tbody>
                  </table>

                    </div>

                </div>
                <h5 style="text-align: right; font-size: 0.8em; padding: 5px;">Data do Relatório: <?php echo date('d/m/Y'); ?>
                </h5>
            </div>
        </div>
    </div>
</body>

</html>
