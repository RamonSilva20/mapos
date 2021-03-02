<?php $totalProdutos = 0; ?>
<div class="row-fluid" style="margin-top: 0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-cash-register"></i>
                </span>
                <h5>Venda</h5>
                <div class="buttons">
                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')) {
    echo '<a title="Editar Venda" class="btn btn-mini btn-info" href="' . base_url() . 'index.php/vendas/editar/' . $result->idVendas . '"><i class="fas fa-edit"></i> Editar</a>';
} ?>
                    <a target="_blank" title="Imprimir" class="btn btn-mini btn-inverse" href="<?php echo site_url() ?>/vendas/imprimir/<?php echo $result->idVendas; ?>"><i class="fas fa-print"></i> Imprimir</a>
                    <a target="_blank" title="Imprimir" class="btn btn-mini btn-inverse" href="<?php echo site_url() ?>/vendas/imprimirTermica/<?php echo $result->idVendas; ?>"><i class="fas fa-print"></i> Imprimir Não Fiscal</a>
                </div>
            </div>
            <div class="widget_content" id="printOs">
                <div class="invoice-content">
                    <div class="invoice-head">
                    <!-- Inicio -->
    <div class="invoice-content">
    <table width="100%" class="table table-condensed">
	<?php if ($emitente == null) { ?>
                                <tr>
                                <td colspan="3" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>
                                            <<<</td> </tr>

                                <?php } ?>
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

        
        								<td style="width: 18%; text-align: center">
                                        <span>#Venda Nº: <?php echo $result->idVendas ?></span><br>
                                        <span>Data Venda: <?php echo date('d/m/Y', strtotime($result->dataVenda)); ?></span><br>
                                        <span>Emissão: <?php echo date('d/m/Y'); ?></span>
                                        <!--
                                        <?php if ($result->faturado) : ?><br>
                                                Vencimento:
                                                <?php echo date('d/m/Y', strtotime($result->data_vencimento)); ?>
                                                -->
                                            <?php endif; ?>
                                        </td>
                </tr>
                                    <tr>
                                </table>
                                <table width="100%" class="table table-condensend">
  <tr>
                                    <tr>
                                    <td style="width: 50%; padding-left: 0">
                                        
                                            <br>
                                            <span style="font-size: 13px"><b>Cliente</b></span><br>
            <span style="font-size: 12px"><i class="fas fa-user-check"></i> <?php echo $result->nomeCliente ?></span><br>
            <span style="font-size: 12px"><i class="fas fa-fingerprint" style="margin:5px 1px"></i> <?php echo $result->documento ?></span><br>
            <span style="font-size: 12px"><i class="fas fa-map-marker-alt" style="margin:4px 3px"></i> <?php echo $result->rua ?>,
                                                    <?php echo $result->numero ?>,
                                                    <?php echo $result->bairro ?></span><br>
            <span style="font-size: 12px"><i class="fas fa-map-marker-alt" style="margin:4px 3px"></i> <?php echo $result->cidade ?> - <?php echo $result->estado ?></span><br> 
            <span style="font-size: 12px"><i class="fas fa-map-marker-alt" style="margin:4px 3px"></i> CEP: <?php echo $result->cep ?></span><br>
            <span style="font-size: 12px"><i class="fas fa-phone-alt" style="margin:5px 1px"></i>  <?php echo $result->telefone ?></span>
                            </span>
                                            
                                    </td>
                                    <td style="width: 50%; padding-left: 0">
                                        <br>
                                                <span style="font-size: 15px"><b>Vendedor</b></span><br>
            <span style="font-size: 12px"><i class="fas fa-user-check"></i> <?php echo $result->nome ?></span><br>
            <span style="font-size: 12px"><i class="fas fa-phone-alt" style="margin:5px 1px"></i> <?php echo $result->telefone_usuario ?></span><br>
            <span style="font-size: 12px"><i class="fas fa-envelope" style="margin:5px 1px"></i> <?php echo $result->email_usuario ?></span>
                                            
                                    </td>
                                </tr>
                                </tr>
  
  </table>
  <hr>
							<?php if ($produtos != null) { ?>
                            <table width="100%" style="font-size: 12px" class="table table-bordered table-condensed" id="tblProdutos">
                                <thead>
                                    <tr>
                                        <th width="12%">Cod. Produto</th>
                                        <th width="12%">Cod. Barras</th>
                                        <th>Produto</th>
                                        <th width="8%">Quantidade</th>
                                        <th width="12%">Preço unit.</th>
                                        <th width="12%">Sub-total</th>
                                    </tr>
                                </thead>
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
                                        <td colspan="5" style="text-align: right"><b>Total: </b></td>
                                        <td><div align="center"><b>R$: <?php echo number_format($totalProdutos, 2, ',', '.'); ?></b></div></td>
                                    </tr>
                            </table>
                        <?php } ?>
                        <?php
                        if ($totalProdutos != 0 || $totalServico != 0) {
                            echo "<h4 style='font-size: 15px; text-align: right'>Valor Total: R$" . number_format($totalProdutos, 2, ',', '.') . "</h4>";
                        }
                        ?>
                      </div>
                                 <!-- Fim -->
                    </div>
                </div>
            </div>
        </div>

        <a href="#modal-gerar-pagamento" id="btn-forma-pagamento" role="button" data-toggle="modal" class="btn btn-success"><i class="fas fa-cash-register"></i> Gerar Pagamento</a>

        <?= $modalGerarPagamento ?>
    </div>
</div>
