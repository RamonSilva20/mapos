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
                                    </tr>
                                <?php } else { ?>
                                    <tr>
                                        <td style="width: 25%"><img src=" <?php echo $emitente->url_logo; ?> "></td>
                                        <td> <span style="font-size: 17px;"><b><?php echo $emitente->nome; ?></b></span> </br>
                                            <span style="font-size: 12px; ">
                                                <span class="icon">
                                                    <i class="fas fa-fingerprint" style="margin:5px 1px"></i>
                                                    <?php echo $emitente->cnpj; ?> </br>
                                                    <span class="icon">
                                                        <i class="fas fa-map-marker-alt" style="margin:4px 3px"></i><?php echo $emitente->rua . ', nº:' . $emitente->numero . ', ' . $emitente->bairro?></br>
                                                        <i class="fas fa-map-marker-alt" style="margin:4px 3px"></i><?php echo $emitente->cidade . ' - ' . $emitente->uf; ?>
                                                    </span> </br> <span>
                                                        <span class="icon">
                                                            <i class="fas fa-comments" style="margin:5px 1px"></i>
                                                            E-mail:
                                                            <?php echo $emitente->email . ' - Fone: ' . $emitente->telefone; ?>
                                                            </br>
                                                            <span class="icon">
                                                                <i class="fas fa-user-check"></i>
                                                                Vendedor: <?php echo $result->nome ?>
                                                            </span>
                                        </td>
                                        <td style="width: 18%; text-align: center"><b>#Venda: </b><span>
                                                <?php echo $result->idVendas ?></span></br> </br> <span>Emissão:
                                                <?php echo date('d/m/Y'); ?></span>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td style="width: 85%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <span>
                                                    <h5><b>CLIENTE</b></h5>
                                                    <span><?php echo $result->nomeCliente ?></span><br />
                                                    <span><?php echo $result->rua ?>, <?php echo $result->numero ?></br>
                                                        <?php echo $result->bairro ?></span></br>
                                                    <span><?php echo $result->cidade ?> - <?php echo $result->estado ?> -
                                                        CEP: <?php echo $result->cep ?></span><br />
                                                    <span>Email: <?php echo $result->emailCliente ?></span></br>
                                                    <?php if ($result->contato) { ?>
                                                        <span>Contato: <?php echo $result->contato ?></span>
                                                    <?php } ?>
                                                    <span>Celular: <?php echo $result->celular ?></span>
                                                </span>
                                            </li>
                                        </ul>
                                    </td>
                                    <?php if (in_array($result->status, ['Finalizado', 'Orçamento', 'Faturado', 'Aberto', 'Em Andamento', 'Aguardando Peças']) && $qrCode): ?>
                                            <td style="width: 25%; padding: 0; text-align: center;">
                                                <img style="margin: 12px 0 0 0;" src="<?= base_url(); ?>assets/img/logo_pix.png" width="64px" alt="QR Code de Pagamento" /><br>
                                                <img style="margin: 5px 0 0 0;" width="94px" src="<?= $qrCode ?>" alt="QR Code de Pagamento" /><br>
                                                 <span style="margin: 0; font-size: 80%; text-align: center;">Chave PIX: <?= $chaveFormatada ?></span>
                                            </td>
                                    <?php endif; ?>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div style="margin-top: 0; padding-top: 0">
                        <table class="table table-condensed">
                            <tbody>
                                <?php if ($result->dataVenda != null) { ?>
                                    <tr>
                                        <td>
                                            <b>Status Venda: </b>
                                            <?php echo $result->status ?>
                                        </td>

                                        <td>
                                            <b>Data da Venda: </b>
                                            <?php echo date('d/m/Y', strtotime($result->dataVenda)); ?>
                                        </td>

                                        <td>
                                            <?php if ($result->garantia) { ?>
                                                <b>Garantia: </b>
                                                <?php echo $result->garantia . ' dia(s)'; ?>
                                            <?php } ?>
                                        </td>

                                        <td>
                                            <?php if (in_array($result->status, ['Finalizado', 'Faturado', 'Orçamento', 'Aberto', 'Em Andamento', 'Aguardando Peças'])): ?>
                                                <b>Venc. da Garantia: </b><?php echo dateInterval($result->dataVenda, $result->garantia); ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td colspan="4">
                                        <b>Observações: </b>
                                        <?php echo htmlspecialchars_decode($result->observacoes_cliente) ?>
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
                                        <th style="font-size: 12px">Cód</th>
                                        <th style="font-size: 12px">Produto</th>
                                        <th style="font-size: 12px">Qt</th>
                                        <th style="font-size: 12px">V. UN R$</th>
                                        <th style="font-size: 12px">S.Total R$</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($produtos as $p) {
                                        echo '<tr>';
                                        echo '<td>' . $p->codDeBarra . '</td>';
                                        echo '<td>' . $p->descricao . '</td>';
                                        echo '<td>' . $p->quantidade . '</td>';
                                        echo '<td>' . ($p->preco ?: $p->precoVenda) . '</td>';
                                        echo '<td> ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                                        echo '</tr>';
                                        $totalProdutos += $p->subTotal;
                                    } ?>
                                    <tr>
                                        <td colspan="4" style="text-align: right"><strong>Total:</strong></td>
                                        <td><strong>R$
                                                <?php echo number_format($totalProdutos, 2, ',', '.'); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                            <hr />
                        <?php } ?>
                        <h4 style="text-align: right">Total: R$
                            <?php echo number_format($totalProdutos, 2, ',', '.'); ?>
                        </h4>
                        <?php if ($result->valor_desconto != 0 && $result->desconto != 0) { ?>
                            <h4 style="text-align: right">Desconto: R$
                                <?php echo number_format($result->valor_desconto - $totalProdutos, 2, ',', '.'); ?>
                            </h4>
                            <h4 style="text-align: right">Total Com Desconto: R$
                                <?php echo number_format($result->valor_desconto, 2, ',', '.'); ?>
                            </h4>
                        <?php } ?>
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
