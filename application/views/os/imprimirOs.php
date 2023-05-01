<?php $totalServico = 0;
$totalProdutos = 0; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Map_OS_<?php echo $result->idOs ?>_<?php echo $result->nomeCliente ?></title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-style.css" />
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="<?= base_url('assets/css/custom.css'); ?>" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
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
                <div class="subpage"><?php echo (!$configuration['control_2vias']) ? "" : "Via Cliente" ?>
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="invoice-content">
                                <div class="invoice-head" style="margin-bottom: 0">
                                    <table class="table table-condensed">
                                        <tbody>
                                            <?php if ($emitente == null) { ?>

                                                <tr>
                                                    <td colspan="3" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>
                                                        <<<< /td>
                                                </tr> <?php } else { ?> <td style="width: 20%"><img src=" <?php echo $emitente->url_logo; ?> ">
                                                </td>
                                                <td> <span style="font-size: 17px;">
                                                        <?php echo $emitente->nome; ?></span> </br>
                                                    <span style="font-size: 12px; ">
                                                        <span class="icon">
                                                            <i class="fas fa-fingerprint" style="margin:5px 1px"></i>
                                                            <?php echo $emitente->cnpj; ?> </br>
                                                            <span class="icon">
                                                                <i class="fas fa-map-marker-alt" style="margin:4px 3px"></i>
                                                                <?php echo $emitente->rua . ', nº:' . $emitente->numero . ', ' . $emitente->bairro . ' - ' . $emitente->cidade . ' - ' . $emitente->uf; ?>
                                                            </span> </br> <span>
                                                                <span class="icon">
                                                                    <i class="fas fa-comments" style="margin:5px 1px"></i>
                                                                    E-mail:<?php echo $emitente->email . ' - Fone: ' . $emitente->telefone; ?> </br>
                                                                    <span class="icon">
                                                                        <i class="fas fa-user-check"></i>
                                                                        Responsável: <?php echo $result->nome ?>
                                                <td style="width: 18%; text-align: center"><b>N° OS:</b> <span><?php echo $result->idOs ?></span></br> </br> <span>Emissão: <?php echo date('d/m/Y') ?></span></td>
                                                </span>
                                                </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                    <table class="table table-condensend">
                                        <tbody>
                                            <tr>
                                                <td style="width: 85%; padding-left: 0">
                                                    <ul>
                                                        <li>
                                                            <span>
                                                                <h5><b>CLIENTE</b></h5>
                                                                <span><?php echo $result->nomeCliente ?></span><br />
                                                                <span><?php echo $result->rua ?>, <?php echo $result->numero ?>, <?php echo $result->bairro ?></span>,
                                                                <span><?php echo $result->cidade ?> - <?php echo $result->estado ?></span><br>
                                                                <span>E-mail: <?php echo $result->email ?></span><br>
                                                                <span>Celular: <?php echo $result->celular_cliente ?></span>
                                                        </li>
                                                    </ul>
                                                </td>
                                                <?php if ($qrCode) : ?>
                                                    <td style="width: 15%; padding-left: 0">
                                                        <img style="margin:12px 0px 2px 7px" src="<?php echo base_url(); ?>assets/img/logo_pix.png" width="64px" alt="QR Code de Pagamento" />
                                                        <img style="margin:6px 12px 2px 0px" width="94" src="<?= $qrCode ?>" alt="QR Code de Pagamento" />
                                                    </td>
                                                <?php endif ?>
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
                                                            <?php echo $result->garantia . ' dias'; ?>
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
                                        </tbody>
                                    </table>
                                    <?php if ($produtos != null) { ?>
                                        <table class="table table-bordered table-condensed" id="tblProdutos">
                                            <thead>
                                                <tr>
                                                    <th>Produtos</th>
                                                    <th>Qt</th>
                                                    <th>V. UN R$</th>
                                                    <th>S.Total R$</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($produtos as $p) {
                                                    $totalProdutos = $totalProdutos + $p->subTotal;
                                                    echo '<tr>';
                                                    echo '<td>' . $p->descricao . '</td>';
                                                    echo '<td>' . $p->quantidade . '</td>';
                                                    echo '<td>' . $p->preco ?: $p->precoVenda . '</td>';
                                                    echo '<td>R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                                                    echo '</tr>';
                                                } ?>
                                                <tr>
                                                    <td colspan="3" style="text-align: right"><strong>Total:</strong></td>
                                                    <td><strong>R$ <?php echo number_format($totalProdutos, 2, ',', '.'); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    <?php } ?>
                                    <?php if ($servicos != null) { ?>
                                        <table class="table table-bordered table-condensed">
                                            <thead>
                                                <tr>
                                                    <th>Serviços</th>
                                                    <th>Qt</th>
                                                    <th>V. UN R$</th>
                                                    <th>S.Total R$</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                setlocale(LC_MONETARY, 'en_US');
                                        foreach ($servicos as $s) {
                                            $preco = $s->preco ?: $s->precoVenda;
                                            $subtotal = $preco * ($s->quantidade ?: 1);
                                            $totalServico = $totalServico + $subtotal;
                                            echo '<tr>';
                                            echo '<td>' . $s->nome . '</td>';
                                            echo '<td>' . ($s->quantidade ?: 1) . '</td>';
                                            echo '<td>' . $preco . '</td>';
                                            echo '<td>R$ ' . number_format($subtotal, 2, ',', '.') . '</td>';
                                            echo '</tr>';
                                        } ?>
                                                <tr>
                                                    <td colspan="3" style="text-align: right"><strong>Total:</strong></td>
                                                    <td><strong>R$ <?php echo number_format($totalServico, 2, ',', '.'); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    <?php } ?>
                                    <?php
                                    if ($totalProdutos != 0 || $totalServico != 0) {
                                        echo "<h4 style='text-align: right'> Valor Total da OS: R$ " . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>";
                                        echo $result->valor_desconto != 0 ? "<h4 style='text-align: right'> Desconto: R$ " . number_format($result->valor_desconto != 0 ? $result->valor_desconto - ($totalProdutos + $totalServico) : 0.00, 2, ',', '.') . "</h4>" : "";
                                        echo $result->valor_desconto != 0 ? "<h4 style='text-align: right'> Total com Desconto: R$ " . number_format($result->valor_desconto, 2, ',', '.') . "</h4>" : "";
                                    }
?>
                                    <table class="table table-bordered table-condensed">
                                        <tbody>
                                            <tr>
                                                <td>Data
                                                    <hr>
                                                </td>
                                                <td>Assinatura do Cliente
                                                    <hr>
                                                </td>
                                                <td>Assinatura do Técnico Responsável
                                                    <hr>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- VIA EMPRESA  -->
            <?php $totalServico = 0;
$totalProdutos = 0; ?>
            <div class="container-fluid page" id="ViaEmpresa" <?php echo (!$configuration['control_2vias']) ? "style='display: none;'" : "style='display: block;'" ?>>
                <div class="subpage">Via Empresa
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="invoice-content">
                                <div class="invoice-head" style="margin-bottom: 0">
                                    <table class="table table-condensed">
                                        <tbody>
                                            <?php if ($emitente == null) { ?>

                                                <tr>
                                                    <td colspan="3" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>
                                                        <<<< /td>
                                                </tr> <?php } else { ?> <td style="width: 20%"><img src=" <?php echo $emitente->url_logo; ?> ">
                                                </td>
                                                <td> <span style="font-size: 17px;">
                                                        <?php echo $emitente->nome; ?></span> </br>
                                                    <span style="font-size: 12px; ">
                                                        <span class="icon">
                                                            <i class="fas fa-fingerprint" style="margin:5px 1px"></i>
                                                            <?php echo $emitente->cnpj; ?> </br>
                                                            <span class="icon">
                                                                <i class="fas fa-map-marker-alt" style="margin:4px 3px"></i>
                                                                <?php echo $emitente->rua . ', nº:' . $emitente->numero . ', ' . $emitente->bairro . ' - ' . $emitente->cidade . ' - ' . $emitente->uf; ?>
                                                            </span> </br> <span>
                                                                <span class="icon">
                                                                    <i class="fas fa-comments" style="margin:5px 1px"></i>
                                                                    E-mail:<?php echo $emitente->email . ' - Fone: ' . $emitente->telefone; ?> </br>
                                                                    <span class="icon">
                                                                        <i class="fas fa-user-check"></i>
                                                                        Responsável: <?php echo $result->nome ?>
                                                <td style="width: 18%; text-align: center"><b>N° OS:</b> <span><?php echo $result->idOs ?></span></br> </br> <span>Emissão: <?php echo date('d/m/Y') ?></span></td>
                                                </span>
                                                </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                    <table class="table table-condensend">
                                        <tbody>
                                            <tr>
                                                <td style="width: 85%; padding-left: 0">
                                                    <ul>
                                                        <li>
                                                            <span>
                                                                <h5><b>CLIENTE</b></h5>
                                                                <span><?php echo $result->nomeCliente ?></span><br />
                                                                <span><?php echo $result->rua ?>, <?php echo $result->numero ?>, <?php echo $result->bairro ?></span>,
                                                                <span><?php echo $result->cidade ?> - <?php echo $result->estado ?></span><br>
                                                                <span>E-mail: <?php echo $result->email ?></span><br>
                                                                <span>Celular: <?php echo $result->celular_cliente ?></span>
                                                        </li>
                                                    </ul>
                                                </td>
                                                <?php if ($qrCode) : ?>
                                                    <td style="width: 15%; padding-left: 0">
                                                        <img style="margin:12px 0px 2px 7px" src="<?php echo base_url(); ?>assets/img/logo_pix.png" width="64px" alt="QR Code de Pagamento" />
                                                        <img style="margin:6px 12px 2px 0px" width="94px" src="<?= $qrCode ?>" alt="QR Code de Pagamento" />
                                                    </td>
                                                <?php endif ?>
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
                                                            <?php echo $result->garantia . ' dias'; ?>
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
                                        </tbody>
                                    </table>
                                    <?php if ($produtos != null) { ?>
                                        <table class="table table-bordered table-condensed" id="tblProdutos">
                                            <thead>
                                                <tr>
                                                    <th>Produtos</th>
                                                    <th>Qt</th>
                                                    <th>V. UN R$</th>
                                                    <th>S.Total R$</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($produtos as $p) {
                                                    $totalProdutos = $totalProdutos + $p->subTotal;
                                                    echo '<tr>';
                                                    echo '<td>' . $p->descricao . '</td>';
                                                    echo '<td>' . $p->quantidade . '</td>';
                                                    echo '<td>' . $p->preco ?: $p->precoVenda . '</td>';
                                                    echo '<td>R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                                                    echo '</tr>';
                                                } ?>
                                                <tr>
                                                    <td colspan="3" style="text-align: right"><strong>Total:</strong></td>
                                                    <td><strong>R$ <?php echo number_format($totalProdutos, 2, ',', '.'); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    <?php } ?>
                                    <?php if ($servicos != null) { ?>
                                        <table class="table table-bordered table-condensed">
                                            <thead>
                                                <tr>
                                                    <th>Serviços</th>
                                                    <th>Qt</th>
                                                    <th>V. UN R$</th>
                                                    <th>S.Total R$</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                setlocale(LC_MONETARY, 'en_US');
                                        foreach ($servicos as $s) {
                                            $preco = $s->preco ?: $s->precoVenda;
                                            $subtotal = $preco * ($s->quantidade ?: 1);
                                            $totalServico = $totalServico + $subtotal;
                                            echo '<tr>';
                                            echo '<td>' . $s->nome . '</td>';
                                            echo '<td>' . ($s->quantidade ?: 1) . '</td>';
                                            echo '<td>' . $preco . '</td>';
                                            echo '<td>R$ ' . number_format($subtotal, 2, ',', '.') . '</td>';
                                            echo '</tr>';
                                        } ?>
                                                <tr>
                                                    <td colspan="3" style="text-align: right"><strong>Total:</strong></td>
                                                    <td><strong>R$ <?php echo number_format($totalServico, 2, ',', '.'); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    <?php } ?>
                                    <?php
                                    if ($totalProdutos != 0 || $totalServico != 0) {
                                        echo "<h4 style='text-align: right'> Valor Total da OS: R$ " . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>";
                                        echo $result->valor_desconto != 0 ? "<h4 style='text-align: right'> Desconto: R$ " . number_format($result->valor_desconto != 0 ? $result->valor_desconto - ($totalProdutos + $totalServico) : 0.00, 2, ',', '.') . "</h4>" : "";
                                        echo $result->valor_desconto != 0 ? "<h4 style='text-align: right'> Total com Desconto: R$ " . number_format($result->valor_desconto, 2, ',', '.') . "</h4>" : "";
                                    }
?>
                                    <table class="table table-bordered table-condensed">
                                        <tbody>
                                            <tr>
                                                <td>Data
                                                    <hr>
                                                </td>
                                                <td>Assinatura do Cliente
                                                    <hr>
                                                </td>
                                                <td>Assinatura do Técnico Responsável
                                                    <hr>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script type="text/javascript">
  window.print(); 
</script>
</body>

</html>
