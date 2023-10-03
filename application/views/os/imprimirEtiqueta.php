<?php $totalServico = 0;
$totalProdutos = 0; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Map_OS_
        <?php echo $result->idOs ?>_
        <?php echo $result->nomeCliente ?>
    </title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-style.css" />
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="<?= base_url('assets/css/custom.css'); ?>" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
    <style>
        .table {

            width: 72mm;
            margin: auto;
        }
    </style>
</head>

<body id=body class="body">
    <div id="principal">
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span12">
                    <div class="invoice-content">
                        <div class="invoice-head" style="margin-bottom: 0">
                            <table class="table table-condensend">
                                <tbody>
                                    <tr>
                                        <td><b>N° OS: </b><span><?php echo $result->idOs ?></span></td>
                                    </tr>
                                    <tr>
                                        <td><b>Cliente: </b><?php echo $result->nomeCliente; ?></td>
                                    </tr>
                                    <tr>
                                        <td><b>Contato: </b><?php echo $result->telefone; ?></td>
                                    </tr>
                                    <?php if ($result->dataInicial != null) { ?>
                                        <tr>
                                            <td><b>Data Inicial: </b><?php echo date('d/m/Y', strtotime($result->dataInicial)); ?></td>
                                        </tr>
                                    <?php } ?>
                                    <?php if ($result->descricaoProduto != null) { ?>
                                        <tr>
                                            <td colspan="5"><b>Descrição: </b><?php echo htmlspecialchars_decode($result->descricaoProduto) ?></td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <td colspan="5">
                                            <div style="text-align: center;"><img src="data:image/png;base64,<?= base64_encode($qrCode) ?>" alt="QR Code"></div><hr>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</body>

<script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/matrix.js"></script>

</html>