<!DOCTYPE html>
<html>

<head>
	<title>Etiquetas</title>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/mpdf-barcode.css" />

</head>

<body style="background-color: transparent">

	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<div class="widget-box">
					<div class="widget_box_Painel2">
						<?php
                        if (
                            $this->input->get("etiquetaCode") !== "EAN13" && $this->input->get("etiquetaCode") !== "QR"
                            && $this->input->get("etiquetaCode") !== "UPCA"
                        ) {
                            if (isset($_GET['qtdEtiqueta'])) {
                                foreach ($produtos as $p) {
                                    for ($i = 0; $p->estoque >  $i++;) {
                                        ?>
							<div class="detalheProdutoEtiquetaEan13">
                                <div class="descricaoProdutoEtiqueta">
                                <table width="100%" class="table table-condensend">
                                <tr>
<td class="textoProdutoEtiqueta" align="left"><b>SKU: <?php echo $p->idProdutos; ?></b></td>
<td class="textoProdutoEtiqueta" align="right"><b>R$ <?php $precoVenda = str_replace(".", ",", $p->precoVenda); echo $precoVenda; ?></b></td>
								</tr>
								</table>
                                	<?php $string = strtoupper($p->descricao); ?>
									<div>
                                    <strong>
                                    <?php print(limitarTexto($string, $limite = 40)); ?>
                                    </strong>
									</div>
								</div>
                                <div class="barcodecell">
		<barcode code="<?= $p->idProdutos ?>" text="0" type="<?= $this->input->get("etiquetaCode") ?>" size="0.7" disableborder="0"
									 class="barcode" />
								</div>
							</div>
							<?php
                                    }
                                }
                            } else {
                                foreach ($produtos as $p) {
                                    ?>
							<div class="detalheProdutoEtiquetaEan13">
                                <div class="descricaoProdutoEtiqueta">
                                <table width="100%" class="table table-condensend">
                                <tr>
<td class="textoProdutoEtiqueta" align="left"><b>SKU: <?php echo $p->idProdutos; ?></b></td>
<td class="textoProdutoEtiqueta" align="right"><b>R$ <?php $precoVenda = str_replace(".", ",", $p->precoVenda); echo $precoVenda; ?></b></td>
								</tr>
								</table>
                                	<?php $string = strtoupper($p->descricao); ?>
									<div>
                                    <strong>
                                    <?php print(limitarTexto($string, $limite = 40)); ?>
                                    </strong>
									</div>
								</div>
                                <div class="barcodecell">
		<barcode code="<?= $p->idProdutos ?>" text="0" type="<?= $this->input->get("etiquetaCode") ?>" size="0.7" disableborder="0"
									 class="barcode" />
								</div>

							</div>
							<?php
                                }
                            }
                        } else {
                            if (isset($_GET['qtdEtiqueta'])) {
                                foreach ($produtos as $p) {
                                    for ($i = 0; $p->estoque >  $i++;) {
                                        ?>
							<div class="detalheProdutoEtiquetaEan13">
                                <div class="descricaoProdutoEtiqueta">
                                <table width="100%" class="table table-condensend">
                                <tr>
<td class="textoProdutoEtiqueta" align="left"><b>SKU: <?php echo $p->idProdutos; ?></b></td>
<td class="textoProdutoEtiqueta" align="right"><b>R$ <?php $precoVenda = str_replace(".", ",", $p->precoVenda); echo $precoVenda; ?></b></td>
								</tr>
								</table>
                                	<?php $string = strtoupper($p->descricao); ?>
									<div>
                                    <strong>
                                    <?php print(limitarTexto($string, $limite = 40)); ?>
                                    </strong>
									</div>
								</div>
                                <div class="barcodecell">
		<barcode code="<?= $p->idProdutos ?>" text="0" type="<?= $this->input->get("etiquetaCode") ?>" size="0.62" disableborder="0"
									 class="barcode" />
								</div>
							</div>


							<?php
                                    }
                                }
                            } else {
                                foreach ($produtos as $p) {
                                    ?>
							<div class="detalheProdutoEtiquetaEan13">
                                <div class="descricaoProdutoEtiqueta">
                                <table width="100%" class="table table-condensend">
                                <tr>
<td class="textoProdutoEtiqueta" align="left"><b>SKU: <?php echo $p->idProdutos; ?></b></td>
<td class="textoProdutoEtiqueta" align="right"><b>R$ <?php $precoVenda = str_replace(".", ",", $p->precoVenda); echo $precoVenda; ?></b></td>
								</tr>
								</table>
                                	<?php $string = strtoupper($p->descricao); ?>
									<div>
                                    <strong>
                                    <?php print(limitarTexto($string, $limite = 40)); ?>
                                    </strong>
									</div>
								</div>
                                <div class="barcodecell">
		<barcode code="<?= $p->idProdutos ?>" text="0" type="<?= $this->input->get("etiquetaCode") ?>" size="0.62" disableborder="0"
									 class="barcode" />
								</div>

							</div>
							<?php
                                }
                            }
                        }
                        ?>

					</div>
				</div>
			</div>
		</div>
	</div>

</body>

</html>
