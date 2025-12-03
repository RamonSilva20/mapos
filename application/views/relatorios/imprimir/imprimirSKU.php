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
                        <h4 style="text-align: center">Produtos</h4>
                    </div>
                    <div class="widget-content nopadding tab-content">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="font-size: 1.2em; padding: 5px;">ID Cliente</th>
                                    <th style="font-size: 1.2em; padding: 5px;">Nome Cliente</th>
                                    <th style="font-size: 1.2em; padding: 5px;">ID Produto</th>
                                    <th style="font-size: 1.2em; padding: 5px;">Descrição Produto</th>
                                    <th style="font-size: 1.2em; padding: 5px;">Quantidade</th>
                                    <th style="font-size: 1.2em; padding: 5px;">ID Relacionado</th>
                                    <th style="font-size: 1.2em; padding: 5px;">Data</th>
                                    <th style="font-size: 1.2em; padding: 5px;">Preço Unitário</th>
                                    <th style="font-size: 1.2em; padding: 5px;">Preço Total</th>
                                    <th style="font-size: 1.2em; padding: 5px;">Origem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    foreach ($resultados as $r) {
                                        echo '<tr>';
                                        echo '<td>' . $r->idClientes . '</td>';
                                        echo '<td>' . $r->nomeCliente . '</td>';
                                        echo '<td>' . $r->idProdutos . '</td>';
                                        echo '<td>' . $r->descricao . '</td>';
                                        echo '<td>' . $r->quantidade . '</td>';
                                        echo '<td>' . $r->idRelacionado . '</td>';
                                        echo '<td>' . date('d/m/Y', strtotime($r->dataOcorrencia)) . '</td>';
                                        echo '<td>' . 'R$ ' . number_format($r->preco, 2, ',', '.') . '</td>';
                                        echo '<td>' . 'R$ ' . number_format($r->precoTotal, 2, ',', '.') . '</td>';
                                        echo '<td>' . $r->origem . '</td>';
                                        echo '</tr>';
                                    }
    ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <h5 style="text-align: right">Data do Relatório: <?php echo date('d/m/Y'); ?>
                </h5>
            </div>
        </div>
    </div>
</body>

</html>
