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

                        <table width="1200" class="table_v">
                            <thead>
                                <tr>
                                	<th width="170" style="font-size: 15px">Cliente/Fornecedor</th>
                                	<th width="355" style="font-size: 15px">Descrição</th>
                                    <th width="80" style="font-size: 15px">Tipo</th>
                                    <th width="110" style="font-size: 15px">Valor</th>
                                    <th width="110" style="font-size: 15px">Entrada</th>
                                    <th width="110" style="font-size: 15px">Pagamento</th>
                                    <th width="180" style="font-size: 15px">Forma de Pagamento</th>
                                    <th width="85" style="font-size: 15px">Situação</th>
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
                                        if ($l->baixado == 1) {
                                            $situacao = 'Pago';
                                        } else {
                                            $situacao = 'Pendente';
                                        }
                                        if ($l->tipo == 'receita') {
                                            $totalReceita += $l->valor;
                                        } else {
                                            $totalDespesa += $l->valor;
                                        }
                                        echo '<tr>';
										echo '<td>' . $l->cliente_fornecedor . '</td>';
                                        echo '<td>' . $l->descricao . '</td>';
                                        echo '<td>' . $l->tipo . '</td>';
                                        echo '<td>R$: ' . $l->valor . '</td>';
                                        echo '<td>' . $vencimento . '</td>';
                                        echo '<td>' . $pagamento . '</td>';
                                        echo '<td>' . $l->forma_pgto . '</td>';
                                        echo '<td>' . $situacao . '</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" style="text-align: right; color: green">
                                        <strong>Total Receitas:</strong>
                                    </td>
                                    <td colspan="3" style="text-align: left; color: green">
                                        <strong>R$
                                            <?php echo number_format($totalReceita, 2, ',', '.') ?>
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6" style="text-align: right; color: red">
                                        <strong>Total Despesas:</strong>
                                    </td>
                                    <td colspan="3" style="text-align: left; color: red">
                                        <strong>R$
                                            <?php echo number_format($totalDespesa, 2, ',', '.') ?>
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6" style="text-align: right">
                                        <strong>Saldo:</strong>
                                    </td>
                                    <td colspan="3" style="text-align: left;">
                                        <strong>R$
                                            <?php echo number_format($totalReceita - $totalDespesa, 2, ',', '.') ?>
                                        </strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <h5 style="text-align: right; font-size: 0.8em; padding: 5px;">Data do Relatório:
                    <?php echo date('d/m/Y'); ?>
                </h5>

            </div>
        </div>
    </div>
</body>

</html>
