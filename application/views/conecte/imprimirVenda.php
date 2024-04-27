<?php $totalProdutos = 0; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Map_OS_<?php echo $result->idVendas ?>_<?php echo $result->nomeCliente ?></title>
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
                                        <td colspan="3" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>
                                            <<<< /td>
                                    </tr> <?php } else { ?> <tr>
                                        <td style="width: 25%"><img src=" <?php echo $emitente->url_logo; ?> "></td>
                                        <td> <span style="font-size: 20px; "> <?php echo $emitente->nome; ?></span> </br><span><?php echo $emitente->cnpj; ?> </br> <?php echo $emitente->rua . ', nº:' . $emitente->numero . ', ' . $emitente->bairro . ' - ' . $emitente->cidade . ' - ' . $emitente->uf; ?> </span> </br> <span> E-mail: <?php echo $emitente->email . ' - Fone: ' . $emitente->telefone; ?></span></td>
                                        <td style="width: 18%; text-align: center">#Venda: <span><?php echo $result->idVendas ?></span></br> </br> <span>Emissão: <?php echo date('d/m/Y'); ?></span></td>
                                    </tr>

                                <?php } ?>
                            </tbody>
                        </table>

                        <table class="table">
                            <tbody>
                                <tr>
                                <td style="width: 50%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <span>
                                                    <h5>Cliente</h5>
                                                    <span>
                                                        <?php echo $result->nomeCliente ?>
                                                    </span><br />
                                                    <span>
                                                        <?php echo $result->rua ?>, <?php echo $result->numero ?>, <?php echo $result->bairro ?>
                                                    </span><br/>
                                                    <span>
                                                        <?php echo $result->cidade ?> - <?php echo $result->estado ?> - CEP: <?php echo $result->cep ?>
                                                    </span><br/>
                                                    <span>
                                                        Email: <?php echo $result->emailCliente ?>
                                                    </span></br>
                                                    <?php if ($result->contato) { ?>
                                                        <span>Contato: <?php echo $result->contato ?> </span>
                                                    <?php } ?>
                                                    <span>Celular: <?php echo $result->celular ?></span>
							                    </span>
                                            </li>
                                        </ul>
                                    </td>
                                    <td style="width: 50%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <span>
                                                    <h5>Vendedor</h5>
                                                </span>
                                                <span><?php echo $result->nome ?></span> <br />
                                                <span>Telefone: <?php echo $result->telefone ?></span><br />
                                                <span>Email: <?php echo $result->email_usuario ?></span>
                                            </li>
                                        </ul>
                                    </td>
                                    <?php if ($qrCode) : ?>
                                        <td style="width: 25%; padding: 0;text-align:center;">
                                            <img style="margin:12px 0px 0px 0px" src="<?php echo base_url(); ?>assets/img/logo_pix.png" width="64px" alt="QR Code de Pagamento" /></br>
                                            <img style="margin:5px 0px 0px 0px" width="94px" src="<?= $qrCode ?>" alt="QR Code de Pagamento" /></br>
                                            <?php echo '<span style="margin:0px;font-size: 80%;text-align:center;">Chave PIX: ' . $chaveFormatada . '</span>' ;?>
                                        </td>
                                    <?php endif ?>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                    
                    <div style="margin-top: 0; padding-top: 0">
                        <table class="table table-condensed">
                            <tbody>
                                <?php if ($result->dataVenda != null) { ?>
                                    <tr>
                                        <td>
                                            <b>STATUS VENDA: </b><?php echo $result->status ?>
                                        </td>

                                        <td>
                                            <b>DATA INICIAL: </b><?php echo date('d/m/Y', strtotime($result->dataVenda)); ?>
                                        </td>

                                        <td>
                                            <?php if ($result->garantia) { ?>
                                                <b>GARANTIA: </b><?php echo $result->garantia . ' dia(s)'; ?>
                                            <?php } ?>
                                        </td>

                                        <td>
                                            <?php if ($result->status == 'Finalizado') { ?>
                                                <b>VENC. DA GARANTIA:</b><?php echo dateInterval($result->dataFinal, $result->garantia); ?>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td colspan="4"> 
                                        <b>OBSERVAÇÕES: </b>
                                        <?php echo htmlspecialchars_decode($result->observacoes_cliente) ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <?php if ($produtos != null) { ?>

                            <table class="table table-bordered table-condensed" id="tblProdutos">
                                <thead>
                                    <tr>
                                        <th style="font-size: 15px">Produto</th>
                                        <th style="font-size: 15px">Quantidade</th>
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

                                        echo '<td>R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                                        echo '</tr>';
                                    } ?>

                                    <tr>
                                        <td colspan="2" style="text-align: right"><strong>Total:</strong></td>
                                        <td><strong>R$ <?php echo number_format($totalProdutos, 2, ',', '.'); ?></strong></td>
                                    </tr>
                                    <?php if ($result->valor_desconto != 0 || $result->valor_desconto != 0) { ?>
                                    <tr>
                                        <td colspan="2" style="text-align: right"><strong>Desconto: </strong></td>
                                        <td>
                                            <strong>
                                                R$ <?php echo number_format($result->valor_desconto - $totalProdutos, 2, ',', '.'); ?>
                                            </strong>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } ?>

                        <hr />

                        <h4 style="text-align: right">Valor Total: R$ <?php echo number_format($result->desconto != 0 && $result->valor_desconto != 0 ? $result->valor_desconto : $totalProdutos, 2, ',', '.'); ?></h4>

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
