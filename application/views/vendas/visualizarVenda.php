<?php $totalProdutos = 0; ?>
<div class="row-fluid" style="margin-top: 0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                    <span class="icon">
                        <i class="fas fa-cash-register"></i>
                    </span>
                    <h5>Dados da Venda Nº <span><b><?php echo $result->idVendas; ?></b></span></h5>
                    <div class="buttons">
                        <?php 
                        $editavel = $this->vendas_model->isEditable($result->idVendas);
                        if (($result->faturado != 1 || $editavel) && $this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')): ?>
                            <a title="Editar Venda" class="button btn btn-mini btn-success" href="<?php echo base_url() . 'index.php/vendas/editar/' . $result->idVendas; ?>">
                                <span class="button__icon"><i class="bx bx-edit"></i></span>
                                <span class="button__text">Editar</span>
                            </a>
                        <?php endif; ?>

                        <div class="button-container">
                            <a target="_blank" title="Imprimir Venda" class="button btn btn-mini btn-inverse">
                                <span class="button__icon"><i class="bx bx-printer"></i></span><span class="button__text">Imprimir</span>
                            </a>
                            <div class="cascading-buttons">
                                <a target="_blank" title="Imprimir Orcamento A4" class="button btn btn-mini btn-inverse" href="<?php echo site_url() . '/vendas/imprimirVendaOrcamento/' . $result->idVendas; ?>">
                                    <span class="button__icon"><i class="bx bx-printer"></i></span>
                                    <span class="button__text">Orçamento</span>
                                </a>
                                <a target="_blank" title="Impressão em Papel A4" class="button btn btn-mini btn-inverse" href="<?php echo site_url() ?>/vendas/imprimir/<?php echo $result->idVendas; ?>">
                                    <span class="button__icon"><i class='bx bx-file'></i></span> <span class="button__text">Papel A4</span>
                                </a>
                                <a target="_blank" title="Impressão Cupom Não Fiscal" class="button btn btn-mini btn-inverse" href="<?php echo site_url() ?>/vendas/imprimirTermica/<?php echo $result->idVendas; ?>">
                                    <span class="button__icon"><i class='bx bx-receipt'></i></span> <span class="button__text">Cupom 80mm</span>
                                </a>
                            </div>
                        </div>

                        <a href="#modal-gerar-pagamento" id="btn-forma-pagamento" role="button" data-toggle="modal" class="button btn btn-mini btn-primary">
                            <span class="button__icon"><i class='bx bx-dollar'></i></span><span class="button__text">Gerar Pagamento</span>
                        </a>

                        <?php if ($qrCode): ?>
                            <a href="#modal-pix" id="btn-pix" role="button" data-toggle="modal" class="button btn btn-mini btn-info">
                                <span class="button__icon"><i class='bx bx-qr'></i></span><span class="button__text">Chave PIX</span>
                            </a>
                        <?php endif; ?>
                    </div>
            </div>
            <div class="widget-content" id="printOs">
                <div class="invoice-content">
                    <div class="invoice-head">
                        <table class="table">
                            <tbody>
                                <?php if ($emitente == null): ?>
                                    <tr>
                                        <td colspan="3" class="alert">
                                            Você precisa configurar os dados do emitente.
                                            <a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td style="width: 25%">
                                            <img src="<?php echo $emitente->url_logo; ?>" style="max-height: 100px">
                                        </td>
                                        <td>
                                            <span style="font-size: 17px;"><b><?php echo $emitente->nome; ?></b></span><br>
                                            <span style="font-size: 12px;">
                                                <span class="icon">
                                                    <i class="fas fa-fingerprint" style="margin:5px 1px"></i>
                                                    <?php echo $emitente->cnpj; ?>
                                                </span><br>
                                                <span class="icon">
                                                    <i class="fas fa-map-marker-alt" style="margin:4px 3px"></i>
                                                    <?php echo $emitente->rua . ', nº:' . $emitente->numero . ', ' . $emitente->bairro . ' - ' . $emitente->cidade . ' - ' . $emitente->uf; ?>
                                                </span><br>
                                                <span class="icon">
                                                    <i class="fas fa-comments" style="margin:5px 1px"></i>
                                                    E-mail: <?php echo $emitente->email . ' - Fone: ' . $emitente->telefone; ?>
                                                </span><br>
                                                <span class="icon">
                                                    <i class="fas fa-user-check"></i>
                                                    Vendedor: <?php echo $result->nome; ?>
                                                </span>
                                            </span>
                                        </td>
                                        <td style="width: 18%; text-align: center">
                                            <b>#Venda: </b><span><b><?php echo $result->idVendas; ?></b></span><br>
                                            <br><span>Emissão: <?php echo date('d/m/Y'); ?></span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td style="width: 50%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <h5><b>CLIENTE</b></h5>
                                                <span><?php echo $result->nomeCliente; ?></span><br>
                                                <span><?php echo $result->rua . ', ' . $result->numero . ', ' . $result->bairro; ?></span><br>
                                                <span><?php echo $result->cidade . ' - ' . $result->estado . ' - CEP: ' . $result->cep; ?></span><br>
                                                <span>Email: <?php echo $result->emailCliente; ?></span><br>
                                                <?php if ($result->contato): ?>
                                                    <span>Contato: <?php echo $result->contato; ?></span><br>
                                                <?php endif; ?>
                                                <span>Celular: <?php echo $result->celular; ?></span>
                                            </li>
                                        </ul>
                                    </td>
                                    <td style="width: 40%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <h5><b>VENDEDOR</b></h5>
                                                <span><?php echo $result->nome; ?></span><br>
                                                <span>Telefone: <?php echo $result->telefone_usuario; ?></span><br>
                                                <span>Email: <?php echo $result->email_usuario; ?></span>
                                            </li>
                                        </ul>
                                    </td>
                                    <?php if ($qrCode): ?>
                                        <td style="width: 15%; padding: 0; text-align: center;">
                                            <img style="margin: 12px 0 0 0;" src="<?php echo base_url(); ?>assets/img/logo_pix.png" width="64px" alt="QR Code de Pagamento"><br>
                                            <img style="margin: 5px 0 0 0;" width="94px" src="<?php echo $qrCode; ?>" alt="QR Code de Pagamento"><br>
                                            <span style="margin: 0; font-size: 80%; text-align: center;">Chave PIX: <?php echo $chaveFormatada; ?></span>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            </tbody>
                        </table>
                        <div style="margin-top: 0; padding-top: 0">
                            <table class="table table-condensed">
                                <tbody>
                                    <?php if ($result->dataVenda != null): ?>
                                        <tr>
                                            <td><b>Status Venda: </b><?php echo $result->status; ?></td>
                                            <td><b>Data da Venda: </b><?php echo date('d/m/Y', strtotime($result->dataVenda)); ?></td>
                                            <td><?php if ($result->garantia): ?><b>Garantia: </b><?php echo $result->garantia . ' dia(s)'; ?><?php endif; ?></td>
                                            <td><?php if (in_array($result->status, ['Finalizado', 'Faturado', 'Orçamento', 'Aberto', 'Em Andamento', 'Aguardando Peças'])): ?><b>Venc. da Garantia: </b><?php echo dateInterval($result->dataVenda, $result->garantia); ?><?php endif; ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td colspan="4"><b>Observações: </b><?php echo htmlspecialchars_decode($result->observacoes_cliente); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div style="margin-top: 0; padding-top: 0">
                            <?php if ($produtos != null): ?>
                                <table class="table table-bordered table-condensed" id="tblProdutos">
                                    <thead>
                                        <tr>
                                            <th style="font-size: 15px">Cód. de barra</th>
                                            <th style="font-size: 15px">Produto</th>
                                            <th style="font-size: 15px">Quantidade</th>
                                            <th style="font-size: 15px">Preço unit.</th>
                                            <th style="font-size: 15px">Sub-total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($produtos as $p): ?>
                                            <?php $totalProdutos += $p->subTotal; ?>
                                            <tr>
                                                <td><?php echo $p->codDeBarra; ?></td>
                                                <td><?php echo $p->descricao; ?></td>
                                                <td><?php echo $p->quantidade; ?></td>
                                                <td><?php echo ($p->preco ?: $p->precoVenda); ?></td>
                                                <td>R$ <?php echo number_format($p->subTotal, 2, ',', '.'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr>
                                            <td colspan="4" style="text-align: right"><strong>Total:</strong></td>
                                            <td><strong>R$ <?php echo number_format($totalProdutos, 2, ',', '.'); ?></strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <hr>
                                <h4 style="text-align: right">Total: R$ <?php echo number_format($totalProdutos, 2, ',', '.'); ?></h4>
                                <?php if ($result->valor_desconto != 0 && $result->desconto != 0): ?>
                                    <h4 style="text-align: right">Desconto: R$ <?php echo number_format($result->valor_desconto - $totalProdutos, 2, ',', '.'); ?></h4>
                                    <h4 style="text-align: right">Total Com Desconto: R$ <?php echo number_format($result->valor_desconto, 2, ',', '.'); ?></h4>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $modalGerarPagamento ?>

<!-- Modal PIX -->
<div id="modal-pix" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
        <h3 id="myModalLabel">Pagamento via PIX</h3>
    </div>
    <div class="modal-body">
        <div class="span12" id="div-pix" style="text-align: center">
            <td style="width: 15%; padding: 0;text-align:center;">
                <img src="<?php echo base_url(); ?>assets/img/logo_pix.png" alt="QR Code de Pagamento" /></br>
                <img id="qrCodeImage" width="50%" src="<?= $qrCode ?>" alt="QR Code de Pagamento" /></br>
                <?php echo '<span>Chave PIX: ' . $chaveFormatada . '</span>'; ?></br>
                <?php if ($totalProdutos != 0) {
                    if ($result->valor_desconto != 0) {
                        echo "Valor Total: R$ " . number_format($result->valor_desconto, 2, ',', '.');
                    } else {
                        echo "Valor Total: R$ " . number_format($totalProdutos, 2, ',', '.');
                    }
                } ?>
            </td>
        </div>
    </div>
    <div class="modal-footer">
        <?php if (!empty($zapnumber)) {
            echo "<button id='pixWhatsApp' class='btn btn-success' data-dismiss='modal' aria-hidden='true' style='color: #FFF'><i class='bx bxl-whatsapp'></i> WhatsApp</button>";
        } ?>
        <button class="btn btn-primary" id="copyButton" style="margin:5px; color: #FFF"><i class="fas fa-copy"></i> Copia e Cola</button>
        <button class="btn btn-danger" data-dismiss="modal" aria-hidden="true" style="color: #FFF">Fechar</button>
    </div>
</div>

<script src="https://cdn.rawgit.com/cozmo/jsQR/master/dist/jsQR.js"></script>
<script type="text/javascript">

    $('#copyButton').on('click', function() {
        var $qrCodeImage = $('#qrCodeImage');
        var canvas = document.createElement('canvas');
        canvas.width = $qrCodeImage.width();
        canvas.height = $qrCodeImage.height();
        var context = canvas.getContext('2d');
        context.drawImage($qrCodeImage[0], 0, 0, $qrCodeImage.width(), $qrCodeImage.height());
        var imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        var code = jsQR(imageData.data, imageData.width, imageData.height);
        if (code) {
            navigator.clipboard.writeText(code.data).then(function() {
                $('#modal-pix').modal('hide');
                swal({
                    type: "success",
                    title: "Sucesso!",
                    text: "QR Code copiado com sucesso: " + code.data,
                    icon: "success",
                    timer: 3000,
                    showConfirmButton: false,
                });

            }).catch(function(err) {
                swal({
                    type: "error",
                    title: "Atenção",
                    text: "Erro ao copiar QR Code: ",
                    err
                });
            });
        } else {
            swal({
                type: "error",
                title: "Atenção",
                text: "Não foi possível decodificar o QR Code.",
            });
        }
    });

    $('#pixWhatsApp').on('click', function() {
        var $qrCodeImage = $('#qrCodeImage');
        var canvas = document.createElement('canvas');
        canvas.width = $qrCodeImage.width();
        canvas.height = $qrCodeImage.height();
        var context = canvas.getContext('2d');
        context.drawImage($qrCodeImage[0], 0, 0, $qrCodeImage.width(), $qrCodeImage.height());
        var imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        var code = jsQR(imageData.data, imageData.width, imageData.height);
        if (code) {
            var whatsappLink = 'https://api.whatsapp.com/send?phone=55' + <?= isset($zapnumber) ? $zapnumber : "" ?> + '&text=' + code.data;
            window.open(whatsappLink, '_blank');
        } else {
            swal({
                type: "error",
                title: "Atenção",
                text: "Não foi possível decodificar o QR Code.",
            });
        }
    });
</script>
