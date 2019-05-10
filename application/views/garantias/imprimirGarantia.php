<?php $totalProdutos = 0; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Map OS</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-style.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-media.css" />
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
                                        <<<</td> </tr> <?php 
                                                    } else { ?>
                                <tr>
                                    <td style="width: 25%"><img src=" <?php echo $emitente[0]->url_logo; ?> "></td>
                                    <td> <span style="font-size: 20px; ">
                                            <?php echo $emitente[0]->nome; ?></span> <br /><span>
                                            <?php echo $emitente[0]->cnpj; ?> <br />
                                            <?php echo $emitente[0]->rua . ', nº:' . $emitente[0]->numero . ', ' . $emitente[0]->bairro . ' - ' . $emitente[0]->cidade . ' - ' . $emitente[0]->uf; ?> </span> </br> <span> E-mail:
                                            <?php echo $emitente[0]->email . ' - Fone: ' . $emitente[0]->telefone; ?></span></td>
                                    <td style="width: 18%; text-align: center">#Venda: <span>
                                            <?php echo $result->idVendas ?></span><br /> <br /> <span>Emissão:
                                            <?php echo date('d/m/Y'); ?></span>
                                        <?php if ($result->faturado) : ?>
                                        <br>
                                        Vencimento:
                                        <?php echo date('d/m/Y', strtotime($result->data_vencimento)); ?>
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
                                    <td style="width: 50%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <span>
                                                    <h5 class="text-center">Termo de Garantia</h5>
                                                </span>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td style="width: 100%; padding-left: 0">
                                        <ul>
                                            <li>

                                                <span><?php echo htmlspecialchars_decode($result->textoGarantia) ?></span><br />
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table table-bordered table-condensed">                                      
                                <tbody>
                                        <tr> 
                                            <td>Data <hr></td>
                                            <td>Assinatura do Cliente <hr></td>
                                            <td>Assinatura do Técnico Responsável <hr></td>
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