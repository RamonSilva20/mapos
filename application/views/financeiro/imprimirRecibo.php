<?php 
setlocale(LC_TIME, 'portuguese'); 
date_default_timezone_set('America/Sao_Paulo');
 ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Câmeras_CFTV</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-style.css" />
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="<?= base_url('assets/css/custom.css'); ?>" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
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
    <h2>&nbsp;</h2>

<div id="principal">
<div class="book">
<div class="container-fluid page" id="viaCliente">
<div class="subpage">
<div class="row-fluid">
<table class="table table-condensed">
	                       <tbody>
                                <?php if ($emitente == null) { ?>
                                    <tr>
                                        <td colspan="3" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>
                                            </td>
                                    </tr>
                                <?php } else { ?>
                                    <tr>
                                        <td style="width: 25%"><img src=" <?php echo $emitente->url_logo; ?> "></td>
                                        <td> <span style="font-size: 17px;"><?php echo $emitente->nome; ?></span> </br>
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
                                                            E-mail:
                                                            <?php echo $emitente->email . ' - Fone: ' . $emitente->telefone; ?>
                                                            </br>
                                                            <span class="icon">
                                                                <i class="fas fa-user-check"></i>
                                                             
                                                            </span>
                                        </td>
                                        <td style="width: 18%; text-align: center"><span>
                                       <?php if ($qrCode) : ?>
                                        <td style="width: 25%; padding: 0;text-align:center;">
                                            <img style="margin:12px 0px 0px 0px" src="<?php echo base_url(); ?>assets/img/logo_pix.png" width="64px" alt="QR Code de Pagamento" /></br>
                                            <img style="margin:5px 0px 0px 0px" width="94px" src="<?= $qrCode ?>" alt="QR Code de Pagamento" /></br>
                                            <?php echo '<span style="margin:0px;font-size: 80%;text-align:center;">Chave PIX: ' . $chaveFormatada . '</span>' ;?>
                                        </td>
                                    <?php endif ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
</table>

<div style="margin-top: 0; padding-top: 0">
<table class="table table-condensed">
	<tbody>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</tbody>
</table>
</div>

<h1 style="text-align:center"><strong><i class="fas fa-file-invoice fa-xs"></i> Recibo de Pagamento</strong></h1>

<p><strong>DESCRI&Ccedil;&Atilde;O: </strong></p>

<p>Recebi(emos) de <?= $lancamento['cliente_fornecedor'] ?> a import&acirc;ncia de R$<?= $lancamento['valor'] ?>  (<?= $valorporescrito ?>), atr&aacute;ves de <?= $lancamento['forma_pgto'] ?>, referente a <?= $lancamento['descricao'] ?>, firmo(amos) o presente.</p>

<p>&nbsp;</p>

&nbsp;

<table class="table table-bordered table-condensed" style="padding-top:20px">
	<tbody>
		<tr> 
			<td><?= $emitente->cidade ?> , <?php echo strftime(" %d de %B de %Y", strtotime($lancamento['data_pagamento'])); ?>
			
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

<script type="text/javascript">
  window.print(); 
</script>
</body>
</html>
 
 
 
 



