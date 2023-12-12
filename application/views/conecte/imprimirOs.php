<?php $totalServico = 0;
$totalProdutos = 0; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?php echo $this->config->item('app_name') ?></title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="<?php echo $this->config->item('app_name') . ' - ' . $this->config->item('app_subname') ?>">
    <link rel="shortcut icon" type="image/png" href="<?php echo base_url(); ?>assets/img/favicon.png" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-style.css" />
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-1.10.2.min.js"></script>
    <style>
        body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: #FAFAFA;
        }

        * {
            box-sizing: border-box;
            -moz-box-sizing: border-box;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 4mm;
            margin: 1mm auto;
            border: 1px #D3D3D3 solid;
            border-radius: 5px;
            background: white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .subpage {
            padding: 0.5cm;
            border: 0px red solid;
            height: 257mm;
            outline: 2cm #FFEAEA solid;
        }

        @page {
            size: A4;
            margin: 0;
        }

        @media print {

            html,
            body {
                width: 210mm;
                height: 297mm;
            }

            .page {
                margin: 0;
                border: initial;
                border-radius: initial;
                width: initial;
                min-height: initial;
                box-shadow: initial;
                background: initial;
                page-break-after: always;
            }
        }
    </style>
</head>

<body style="background-color: rgba(0,0,0,.4)" id=body>
    <div id="principal">
        <div class="book">
            <div class="container-fluid page" id="viaCliente">
                <div class="row-fluid">
                    <div class="span12">
                        <div class="invoice-content">
                            <div class="invoice-head" style="margin-bottom: 0">
                                <table class="table table-condensed">
                                    <div class="table-bordered" style="text-align: right; padding-right: 15px">Emitido pelo Cliente</div>
                                    <tbody>
                                        <?php if ($emitente == null) { ?>
                                            <tr>
                                                <td colspan="3" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>
                                                    <<<< /td>
                                            </tr> <?php } else { ?><td style="width: 20%"><img src=" <?php echo $emitente->url_logo; ?> "></td>
                                            <td>
                                                <span style="font-size: 17px;"><?php echo $emitente->nome; ?></span></br>
                                                <?php if($emitente->cnpj != "00.000.000/0000-00") { ?><span class="icon"><i class="fas fa-fingerprint" style="margin:5px 1px"></i> <?php echo $emitente->cnpj; ?></span></br><?php } ?>
                                                <span class="icon"><i class="fas fa-map-marker-alt" style="margin:4px 3px"></i><?php echo $emitente->rua . ', ' . $emitente->numero . ', ' . $emitente->bairro . ' - ' . $emitente->cidade . ' - ' . $emitente->uf; ?></span></br>
                                                <span><span class="icon"><i class="fas fa-comments" style="margin:5px 1px"></i> E-mail: <?php echo $emitente->email . ' - Fone: ' . $emitente->telefone; ?></br>
                                                <span class="icon"><i class="fas fa-user-check"></i> Responsável: <?php echo $result->nome ?>
                                                <td style="width: 18%; text-align: center"><b>N° OS:</b> <span><?php echo $result->idOs ?></span></br></br><span>Emissão: <?php echo date('d/m/Y') ?></span></td></span>
                                            </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                                <table class="table table-condensend">
                                    <tbody>
                                        <tr>
                                            <td style="width: 85%; padding-left: 0; text-align: center">
                                                <ul>
                                                    <li>
                                                        <span>
                                                            <h5><b>RELATÓRIO DA ORDEM DE SERVIÇO</b></h5>
                                                        </span>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div style="margin-top: 0; padding-top: 0">
                                <table class="table table-condensed">
                                    <tbody>
                                        <?php if ($result->dataInicial != null) { ?>
                                            <tr>
                                                <td>
                                                    <b>STATUS OS: </b>
                                                    <?php echo $result->status ?>
                                                </td>
                                                <td>
                                                    <b>DATA INICIAL: </b>
                                                    <?php echo date('d/m/Y', strtotime($result->dataInicial)); ?>
                                                </td>
                                                <td>
                                                    <b>DATA FINAL: </b>
                                                    <?php echo $result->dataFinal ? date('d/m/Y', strtotime($result->dataFinal)) : ''; ?>
                                                </td>
                                                <?php if ($result->garantia) {
                                                    ?>
                                                    <td>
                                                        <b>GARANTIA: </b>
                                                        <?php echo $result->garantia . ' dia(s)'; ?>
                                                    </td>
                                                <?php
                                                } ?>
                                                <td>
                                                    <b>
                                                        <?php if ($result->status == 'Finalizado') { ?>
                                                            VENC. DA GARANTIA:
                                                    </b>
                                                    <?php echo dateInterval($result->dataFinal, $result->garantia); ?><?php } ?>
                                            </tr>
                                        <?php } ?>
                                        <?php if ($result->descricaoProduto != null) { ?>
                                            <tr>
                                                <td colspan="5">
                                                    <b>DESCRIÇÃO: </b>
                                                    <?php echo htmlspecialchars_decode($result->descricaoProduto) ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        <?php if ($result->defeito != null) { ?>
                                            <tr>
                                                <td colspan="5">
                                                    <b>DEFEITO APRESENTADO: </b>
                                                    <?php echo htmlspecialchars_decode($result->defeito) ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        <?php if ($result->observacoes != null) { ?>
                                            <tr>
                                                <td colspan="5">
                                                    <b>OBSERVAÇÕES: </b>
                                                    <?php echo htmlspecialchars_decode($result->observacoes) ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        <?php if ($result->laudoTecnico != null) { ?>
                                            <tr>
                                                <td colspan="5">
                                                    <b>LAUDO TÉCNICO: </b>
                                                    <?php echo htmlspecialchars_decode($result->laudoTecnico) ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        <?php if ($result->garantias_id != null) { ?>
                                            <tr>
                                                <td colspan="5">
                                                    <strong>TERMO DE GARANTIA </strong><br>
                                                    <?php echo htmlspecialchars_decode($result->textoGarantia) ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                                <?php if ($produtos != null) { ?>
                                    <table class="table table-bordered table-condensed" id="tblProdutos">
                                        <thead>
                                            <tr>
                                                <th>PRODUTO</th>
                                                <th>QTD</th>
                                                <th>UNT</th>
                                                <th>SUBTOTAL</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($produtos as $p) {
                                                $totalProdutos = $totalProdutos + $p->subTotal;
                                                echo '<tr>';
                                                echo '<td>' . $p->descricao . '</td>';
                                                echo '<td>' . $p->quantidade . '</td>';
                                                echo '<td>' . $p->preco ?: $p->precoVenda . '</td>';
                                                echo '<td>R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                                                echo '</tr>';
                                            } ?>
                                            <tr>
                                                <td colspan="3" style="text-align: right"><strong>TOTAL:</strong></td>
                                                <td><strong>R$ <?php echo number_format($totalProdutos, 2, ',', '.'); ?></strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                <?php } ?>
                                <?php if ($servicos != null) { ?>
                                    <table class="table table-bordered table-condensed">
                                        <thead>
                                            <tr>
                                                <th>SERVIÇO</th>
                                                <th>QTD</th>
                                                <th>UNT</th>
                                                <th>SUBTOTAL</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php setlocale(LC_MONETARY, 'en_US'); foreach ($servicos as $s) {
                                                $preco = $s->preco ?: $s->precoVenda;
                                                $subtotal = $preco * ($s->quantidade ?: 1);
                                                $totalServico = $totalServico + $subtotal;
                                                echo '<tr>';
                                                echo '<td>' . $s->nome . '</td>';
                                                echo '<td>' . ($s->quantidade ?: 1) . '</td>';
                                                echo '<td>R$ ' . $preco . '</td>';
                                                echo '<td>R$ ' . number_format($subtotal, 2, ',', '.') . '</td>';
                                                echo '</tr>';
                                            } ?>
                                            <tr>
                                                <td colspan="3" style="text-align: right"><strong>TOTAL:</strong></td>
                                                <td><strong>R$ <?php echo number_format($totalServico, 2, ',', '.'); ?></strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                <?php } ?>

                                <?php if ($totalProdutos != 0 || $totalServico != 0) {
                                    if ($result->valor_desconto != 0) {
                                        echo "<h4 style='text-align: right'>SUBTOTAL: R$ " . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>";
                                        echo $result->valor_desconto != 0 ? "<h4 style='text-align: right'>DESCONTO: R$ " . number_format($result->valor_desconto != 0 ? $result->valor_desconto - ($totalProdutos + $totalServico) : 0.00, 2, ',', '.') . "</h4>" : "";
                                        echo "<h4 style='text-align: right'>TOTAL: R$ " . number_format($result->valor_desconto, 2, ',', '.') . "</h4>";
                                    } else { echo "<h4 style='text-align: right'>TOTAL: R$ " . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>"; }
                                }?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/matrix.js"></script>

    <script>
        window.print();
    </script>

</body>

</html>
