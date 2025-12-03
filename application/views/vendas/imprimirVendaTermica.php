<?php $totalProdutos = 0; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Map_Vendas_<?php echo $result->idVendas ?>_<?php echo $result->nomeCliente ?></title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-style.css" />
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-1.10.2.min.js"></script>
    <style>
        .table {
            width: 72mm;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div class="invoice-content">
                    <div class="invoice-head">
                        <table class="table">
                            <tbody>
                                <?php if ($emitente == null) { ?>
                                    <tr>
                                        <td colspan="5" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>
                                            <<<</td> </tr> <?php } else { ?> 
                                    <td style="width: 25%; text-align: center;"><img src="<?php echo $emitente->url_logo; ?>" style="max-height: 100px"></td>
                                    <tr>
                                        <td colspan="4" style="text-align: center;"> <span style="font-size: 20px;">
                                                <b><?php echo $emitente->nome; ?></b></span> </br><span>
                                                <?php echo 'CNPJ: ' . $emitente->cnpj; ?> </br>
                                                <?php echo $emitente->rua . ', ' . $emitente->numero . ', ' . $emitente->bairro . ' - ' . $emitente->cidade . ' - ' . $emitente->uf; ?> </span> </br>
                                            <span><?php echo 'Fone: ' . $emitente->telefone; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="width: 100%;"><b>#Venda: </b><span>
                                                <?php echo $result->idVendas ?></span>
                                            <span style="padding-inline: 1em">Emissão: <?php echo date('d/m/Y H:i:s'); ?></span>
                                            <?php if ($result->faturado) : ?>
                                                <br>
                                                <b>Venc. Garantia: </b>
                                                <?php echo dateInterval($result->dataVenda, $result->garantia); ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php
                                } ?>
                            </tbody>
                        </table>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td colspan="4" style="width: 50%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <span>
                                                    <h5><b>CLIENTE</b></h5>
                                                    <span>
                                                        <?php echo $result->nomeCliente ?></span><br />
                                                    <span>
                                                        <?php echo $result->rua ?>,
                                                        <?php echo $result->numero ?>,
                                                        <?php echo $result->bairro ?></span><br />
                                                    <span>
                                                        <?php echo $result->cidade ?> -
                                                        <?php echo $result->estado ?></span>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 0; padding-top: 0">
                        <?php if ($produtos != null) { ?>
                            <table class="table table-bordered table-condensed" id="tblProdutos">
                                <thead>
                                    <tr>
                                        <th style="font-size: 15px">Produto</th>
                                        <th style="font-size: 15px">Quantidade</th>
                                        <th style="font-size: 15px">Preço unit.</th>
                                        <th style="font-size: 15px">Sub-total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($produtos as $p) {
                                        $totalProdutos = $totalProdutos + $p->subTotal;
                                        echo '<tr>';
                                        echo '<td>' . $p->descricao . '</td>';
                                        echo '<td>' . $p->quantidade . '</td>';
                                        echo '<td>R$ ' . ($p->preco ?: $p->precoVenda) . '</td>';
                                        echo '<td>R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                                        echo '</tr>';
                                    } ?>
                                    <?php if ($result->valor_desconto != 0 && $result->desconto != 0) { ?>
                                        <tr>
                                        <td colspan="3" style="text-align: right"><strong>Total: R$</strong></td>
                                        <td>
                                            <strong>
                                                <?php echo number_format($totalProdutos, 2, ',', '.'); ?>
                                            </strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="text-align: right"><strong>Desconto: R$</strong></td>
                                        <td>
                                            <strong>
                                                <?php echo number_format($result->valor_desconto - $totalProdutos, 2, ',', '.'); ?>
                                            </strong>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                    <tr>
                                        <td colspan="4" style="text-align: right">
                                            <h4 style="text-align: right">Total: R$
                                                <?php echo number_format($result->desconto != 0 && $result->valor_desconto != 0 ? $result->valor_desconto : $totalProdutos, 2, ',', '.'); ?>
                                            </h4>
                                        </td>
                                    </tr>
                                </tbody>
                                <?php if (in_array($result->status, ['Finalizado', 'Orçamento', 'Faturado', 'Aberto', 'Em Andamento', 'Aguardando Peças']) && $qrCode): ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center;">
                                            <img style="margin: 12px 0 0 0;" src="<?= base_url(); ?>assets/img/logo_pix.png" width="64px" alt="QR Code de Pagamento" /><br>
                                            <img style="margin: 5px 0 0 0;" width="94px" src="<?= $qrCode ?>" alt="QR Code de Pagamento" /><br>
                                            <span style="margin: 0; font-size: 80%; text-align: center;">Chave PIX: <?= $chaveFormatada ?></span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php } ?>
                            </table>
                            <table class="table table-bordered table-condensed" style="font-size: 15px">
                                <tbody>
                                    <tr>
                                        <td colspan="5">
                                            <b><p class="text-center">Assinatura do Recebedor</p></b><br />
                                            <hr>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php ?>
                        <hr />
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
