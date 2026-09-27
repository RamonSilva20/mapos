<!DOCTYPE html>
<html>

<head>
	<title>Etiquetas</title>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="<?= base_url() ?>assets/css/mpdf-barcode.css" />

</head>

<body style="background-color: transparent">

	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<div class="widget-box">
					<div class="widget-content nopadding tab-content">
					<?php
                        // O tipo de codigo de barras vem da query string e e
                        // usado tanto no `if` abaixo quanto no atributo `type` do
                        // <barcode>. Sem uma lista branca o valor refletido
                        // permitiria breakout do atributo (XSS refletido).
                        // A lista deve acompanhar as opcoes de
                        // application/views/produtos/produtos.php.
                        $tiposPermitidos = ['EAN13', 'UPCA', 'C93', 'C128A', 'CODABAR', 'QR'];
	$tipoEtiqueta = (string) $this->input->get('etiquetaCode');
	if (! in_array($tipoEtiqueta, $tiposPermitidos, true)) {
	    $tipoEtiqueta = 'EAN13';
	}

	$qtdEtiqueta = $this->input->get('qtdEtiqueta');
	?>
					<?php
	if ($tipoEtiqueta !== 'EAN13' && $tipoEtiqueta !== 'QR' && $tipoEtiqueta !== 'UPCA') {
	    if (isset($qtdEtiqueta)) {
	        foreach ($produtos as $p) {
	            for ($i = 0; $p->estoque >  $i++;) {
	                ?>
							<div class="detalheProdutoEtiqueta">
								<div class="descricaoProdutoEtiqueta">
									<?php $string = strtoupper($p->descricao); ?>
									<div>
										<strong>
											<?= (esc(limitarTexto($string, $limite = 23))) ?>
										</strong>
									</div>
								</div>
								<div class="textoProdutoEtiqueta">Cod:
									<b>
										<?= esc($p->idProdutos) ?>
									</b>
									<br /> Preço: R$
									<b>
										<?php $precoVenda = str_replace(".", ",", $p->precoVenda);
	                echo esc($precoVenda); ?>
									</b>
								</div>
								<div class="barcodecell">
									<barcode code="<?= esc($p->codDeBarra) ?>" text="0" type="<?= esc($tipoEtiqueta) ?>" size="0.7" disableborder="0"
									 class="barcode" />
								</div>
							</div>
							<?php
	            }
	        }
	    } else {
	        foreach ($produtos as $p) {
	            ?>
							<div class="detalheProdutoEtiqueta">
								<div class="descricaoProdutoEtiqueta">
									<?php $string = strtoupper($p->descricao); ?>
									<div>
										<strong>
											<?= (esc(limitarTexto($string, $limite = 23))) ?>
										</strong>
									</div>
								</div>
								<div class="textoProdutoEtiqueta">Cod:
									<b>
										<?= esc($p->idProdutos) ?>
									</b>
									<br /> Preço: R$
									<b>
										<?php $precoVenda = str_replace(".", ",", $p->precoVenda);
	            echo esc($precoVenda); ?>
									</b>
								</div>

								<div class="barcodecell">
									<barcode code="<?= esc($p->codDeBarra) ?>" text="0" type="<?= esc($tipoEtiqueta) ?>" size="0.7" disableborder="0"
									 class="barcode" />
								</div>

							</div>
							<?php
	        }
	    }
	} else {
	    if (isset($qtdEtiqueta)) {
	        foreach ($produtos as $p) {
	            for ($i = 0; $p->estoque >  $i++;) {
	                ?>
							<div class="detalheProdutoEtiquetaEan13">
								<div class="descricaoProdutoEtiqueta">
									<?php $string = strtoupper($p->descricao); ?>
									<div>
										<strong>
											<?= (esc(limitarTexto($string, $limite = 23))) ?>
										</strong>
									</div>
								</div>
								<div class="textoProdutoEtiqueta">Cod:
									<b>
										<?= esc($p->idProdutos) ?>
									</b>
									<br /> Preço: R$
									<b>
										<?php $precoVenda = str_replace(".", ",", $p->precoVenda);
	                echo esc($precoVenda); ?>
									</b>
								</div>
								<div class="barcodecell">
									<barcode code="<?= esc($p->codDeBarra) ?>" text="0" type="<?= esc($tipoEtiqueta) ?>" size="0.62" disableborder="0"
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
									<?php $string = strtoupper($p->descricao); ?>
									<div>
										<strong>
											<?= (esc(limitarTexto($string, $limite = 23))) ?>
										</strong>
									</div>
								</div>
								<div class="textoProdutoEtiqueta">Cod:
									<b>
										<?= esc($p->idProdutos) ?>
									</b>
									<br /> Preço: R$
									<b>
										<?php $precoVenda = str_replace(".", ",", $p->precoVenda);
	            echo esc($precoVenda); ?>
									</b>
								</div>

								<div class="barcodecell">
									<barcode code="<?= esc($p->codDeBarra) ?>" text="0" type="<?= esc($tipoEtiqueta) ?>" size="0.62" disableborder="0"
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
