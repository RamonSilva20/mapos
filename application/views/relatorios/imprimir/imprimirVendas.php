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

                        <table width="1000" class="table_v">
                            <thead>
                                <tr>
                                    <th width="600" style="font-size: 1.1em; padding: 5px;">Cliente</th>
                                    <th width="100" style="font-size: 1.1em; padding: 5px;">Total</th>
                                    <th width="100" style="font-size: 1.1em; padding: 5px;">Data</th>
                                    <th width="200" style="font-size: 1.1em; padding: 5px;">Vendedor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                        foreach ($vendas as $c) {

                                            echo '<tr>';
                                            echo '<td>' . $c->nomeCliente . '</td>';
                                            echo '<td>' . $c->valorTotal . '</td>';
                                            echo '<td>' . date('d/m/Y', strtotime($c->dataVenda)) . '</td>';
                                            echo '<td>' . $c->nome . '</td>';
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
