<?php $totalProdutos = 0; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Venda Nº: <?php echo $result->idVendas ?> - <?php echo $this->config->item('app_name') ?></title>
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
    <div class="invoice-content">
    <table width="100%" class="table_r">
  <?php if ($emitente == null) { ?>
  <tr>
    <td colspan="3" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a><<<
    </td>
    </tr>
    <?php } else { ?>
  <tr>
    <td style="width: 25%"><br><img src=" <?php echo $emitente[0]->url_logo; ?> " style="max-height: 100px"></td>
    <td>
<span style="font-size: 15px"><b><?php echo $emitente[0]->nome; ?></b></span></br>
<span style="font-size: 13px"><i class="fas fa-fingerprint" style="margin:5px 1px"></i> <?php echo $emitente[0]->cnpj; ?></span></br>
<span style="font-size: 13px"><i class="fas fa-map-marker-alt" style="margin:4px 3px"></i> <?php echo $emitente[0]->rua . ', ' . $emitente[0]->numero . ' - ' . $emitente[0]->bairro; ?></span></br>
<span style="font-size: 13px"><i class="fas fa-map-marker-alt" style="margin:4px 3px"></i> <?php echo $emitente[0]->cidade . ' - ' . $emitente[0]->uf; ?></br>
<span style="font-size: 13px"><i class="fas fa-map-marker-alt" style="margin:4px 3px"></i> <?= 'CEP: ' . $emitente[0]->cep; ?></span><br>
<span style="font-size: 13px"><i class="fas fa-envelope" style="margin:5px 1px"></i>  <?php echo $emitente[0]->email; ?></span></br>
<span style="font-size: 13px"><i class="fas fa-phone-alt" style="margin:5px 1px"></i>  <?php echo $emitente[0]->telefone; ?></span>
</td>

    
    <td style="text-align: center">
        <span style="font-size: 12px"><b>Venda Nº: </b><?php echo $result->idVendas ?></span></br>
        <span style="font-size: 12px"><b>Data Venda: </b><?php echo date('d/m/Y', strtotime($result->dataVenda)); ?></span></br>
        <span style="font-size: 12px"><b>Emissão: </b><?php echo date('d/m/Y'); ?></span></br>
        <!--
        <?php if ($result->faturado) : ?><br>
        <span style="font-size: 12px"><b>Vencimento: </b><?php echo date('d/m/Y', strtotime($result->data_vencimento)); ?><?php endif; ?></span></br>
        -->
        </td>
  </tr>
  <?php } ?>
  <tr>
    <td colspan="2">
            <span style="font-size: 13px"><b>Cliente</b></span><br>
            <span style="font-size: 12px"><i class="fas fa-user-check"></i> <?php echo $result->nomeCliente ?></span><br>
            <span style="font-size: 12px"><i class="fas fa-fingerprint" style="margin:5px 1px"></i> <?php echo $result->documento ?></span><br>
            <span style="font-size: 12px"><i class="fas fa-map-marker-alt" style="margin:4px 3px"></i> <?php echo $result->rua ?>,
                                                    <?php echo $result->numero ?>,
                                                    <?php echo $result->bairro ?></span><br>
            <span style="font-size: 12px"><i class="fas fa-map-marker-alt" style="margin:4px 3px"></i> <?php echo $result->cidade ?> - <?php echo $result->estado ?></span><br> 
            <span style="font-size: 12px"><i class="fas fa-map-marker-alt" style="margin:4px 3px"></i> CEP: <?php echo $result->cep ?></span><br>
            <span style="font-size: 12px"><i class="fas fa-phone-alt" style="margin:5px 1px"></i>  <?php echo $result->telefone ?></span>
                          </td>
                          <td>
			<span style="font-size: 13px"><b>Vendedor</b></span><br>
            <span style="font-size: 12px"><i class="fas fa-user-check"></i> <?php echo $result->nome ?></span><br>
            <span style="font-size: 12px"><i class="fas fa-phone-alt" style="margin:5px 1px"></i> <?php echo $result->telefone_usuario ?></span>
            </td>
  </tr>
  <?php if ($produtos != null) { ?>
                                    <tr>
                                        <td colspan="3"><br>
                            <table width="100%" class="table_p" id="tblProdutos">
                                <thead>
                                    <tr>
                                        <th width="10%">Cod. Produto</th>
                                        <th width="10%">Cod. Barras</th>
                                        <th>Produto</th>
                                        <th width="8%">Quantidade</th>
                                        <th width="10%">Preço unit.</th>
                                        <th width="10%">Sub-total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                    foreach ($produtos as $p) {

                                        $totalProdutos = $totalProdutos + $p->subTotal;
                                        echo '<tr>';
										echo '<td><div align="center">' . $p->idProdutos . '</div></td>';
                                        echo '<td><div align="center">' . $p->codDeBarra . '</td>';
                                        echo '<td>' . $p->descricao . '</td>';
                                        echo '<td><div align="center">' . $p->quantidade . '</div></td>';
                                        echo '<td><div align="center">R$: ' . $p->preco ?: $p->precoVenda . '</div></td>';
										echo '<td><div align="center">R$: ' . number_format($p->subTotal, 2, ',', '.') . '</div></td>';
                                        echo '</tr>';
                                    } ?>
                                    <tr>
                                        <td colspan="5" style="text-align: right"><strong>Total: </strong></td>
                                        <td><div align="center"><strong>R$: <?php echo number_format($totalProdutos, 2, ',', '.'); ?></strong></div></td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            </td>
                                    </tr>
                                <?php } ?>
                                <tr>
    	<td colspan="3">
        					<!-- QR Code PIX -->
                            <table width="100%" class="table_x">
                                		<tr>
                                        <th><div align="center">
                                        <table width="200">
                                            <tr>
                                              <td>
                                                <div align="center">
                                                  <?php if ($qrCode): ?>
                                                  <img src="../../../assets/img/logo_pix.png" width="150px">
                                                  <img src="<?= $qrCode ?>" alt="QR Code de Pagamento" />
                                              </div></td>
                                              <?php endif ?>
                                              
                                          </table>
                                          </div></th>
                                          </tr>
                                          </table>
									<!-- Fim QR Code PIX -->
	</td>
    </tr>
</table>


                             </div>
    <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/matrix.js"></script>
    <script>
        window.print();
    </script>
</body>

</html>
