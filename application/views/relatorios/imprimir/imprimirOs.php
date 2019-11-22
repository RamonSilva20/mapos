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
                    <?=isset($topo) ? $topo : ''?>
                        <div class="widget-title">
                            <h4 style="text-align: center">
                                <?=ucfirst($title)?>
                            </h4>
                        </div>
                        <div class="widget-content nopadding">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th style="padding: 5px;">CLIENTE</th>
                                        <th style="padding: 5px;">STATUS</th>
                                        <th style="padding: 5px;">DATA</th>
                                        <th style="padding: 5px;">DESCRIÇÃO</th>
                                        <th style="padding: 5px;">TOTAL PRODUTOS</th>
                                        <th style="padding: 5px;">TOTAL SERVIÇOS</th>
                                        <th style="padding: 5px;">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        foreach ($os as $c) {
                                            echo '<tr>';
                                            echo '<td><small>' . $c->nomeCliente . '</small></td>';
                                            echo '<td><small>' . $c->status . '</small></td>';
                                            echo '<td><small>' . date('d/m/Y', strtotime($c->dataInicial)) . '</small></td>';
                                            echo '<td><small>' . $c->descricaoProduto . '</small></td>';
                                            echo '<td><small>R$ ' . number_format($c->total_produto, 2, ',', '.') . '</small></td>';
                                            echo '<td><small>R$ ' . number_format($c->total_servico, 2, ',', '.') . '</small></td>';
                                            echo '<td><small>R$ ' . number_format($c->total_produto + $c->total_servico, 2, ',', '.') . '</small></td>';
                                            echo '</tr>';
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                </div>
                <p style="text-align: right">Data do Relatório: <?php echo date('d/m/Y'); ?>
                </p>
            </div>
        </div>
    </div>
</body>

</html>
