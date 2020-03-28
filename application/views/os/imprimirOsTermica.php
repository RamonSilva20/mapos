<?php $totalServico = 0;
$totalProdutos = 0; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Map OS</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-style.css" />
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="<?= base_url('assets/css/custom.css'); ?>" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
    <style>
        .table {
            
            width:72mm;
            margin: auto;
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">

                <div class="invoice-content">
                    <div class="invoice-head" style="margin-bottom: 0">

                        <table class="table table-condensed">
                            <tbody>
                                <?php if ($emitente == null) { ?>

                                    <tr>
                                        <td colspan="4" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>
                                            <<<</td> </tr> <?php } else { ?> <tr>
                                        
                                        <td colspan="4" style="text-align: center"> <span style="font-size: 20px; "> 
                                        <?php echo $emitente[0]->nome; ?></span> </br>
                                        <span style="font-size: 10px; ">CNPJ: <?php echo $emitente[0]->cnpj; ?> </br> 
                                        <?php echo $emitente[0]->rua . ', ' . $emitente[0]->numero . ' ' . $emitente[0]->bairro . ' -  ' . $emitente[0]->cidade . ' - ' . $emitente[0]->uf; ?> </span> </br> <span>Fone: <?php echo $emitente[0]->telefone; ?></span></td>
                                        </tr>
                                        <tr>
                                        <td style="width: 100%; font-size: 10px;"><b>N° OS:</b> <span><?php echo $result->idOs ?></span><span style="padding-left: 2%;"><b>Emissão:</b> <?php echo date('d/m/Y') ?></span></td>
                                    </tr>

                                <?php } ?>
                            </tbody>
                        </table>


                        <table class="table table-condensend">
                            <tbody>
                                <tr>
                                    <td style="width: 50%; padding-left: 0; font-size: 11px;">
                                        <ul>
                                            <li>
                                                <span>
                                                    <b>CLIENTE: </b>
                                                    <span><?php echo $result->nomeCliente ?></span><br />
                                            </li>
                                        </ul>
                                    </td>
                                    
                                </tr>
                            </tbody>
                        </table>

                    </div>

                    <div style="margin-top: 0; padding-top: 0">


                        <?php if ($produtos != null) { ?>
                            <br />
                            <table style='font-size: 11px;' class="table table-bordered table-condensed" id="tblProdutos">
                                <thead>
                                    <tr>
                                        <th>Qtd.</th>
                                        <th>Produto</th>
                                        <th>Preço unit.</th>
                                        <th>Sub-total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                        foreach ($produtos as $p) {
                                            $totalProdutos = $totalProdutos + $p->subTotal;
                                            echo '<tr>';
                                            echo '<td>' . $p->quantidade . '</td>';
                                            echo '<td>' . $p->descricao . '</td>';
                                            echo '<td>R$ ' . $p->preco ?: $p->precoVenda . '</td>';

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
                            <table style='font-size: 11px;' class="table table-bordered table-condensed">
                                <thead>
                                    <tr>
                                        <th>Qtd.</th>
                                        <th>Serviço</th>
                                        <th>Preço unit.</th>
                                        <th>Sub-total</th>
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
                                            echo '<td>' . ($s->quantidade ?: 1) . '</td>';
                                            echo '<td>' . $s->nome . '</td>';
                                            echo '<td>R$ ' . $preco . '</td>';
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
                       

                        <table class="table table-bordered table-condensed">
                            <tbody>
                                <tr>
                                    <td colspan="4"> <?php
                        if ($totalProdutos != 0 || $totalServico != 0) {
                            echo "<h4 style='text-align: right; font-size: 13px;'>Valor Total: R$" . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>";
                        }

                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
