<?php $totalProdutos = 0; ?>
<div class="row-fluid" style="margin-top: 0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: 10px 0 0">
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
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <table class="table">
                            <tbody>
                                <div class="invoice-head" style="margin-bottom: 0; margin-top:-30px">
                                    <table class="table table-condensed">
                                        <tbody>
                                            <?php if ($emitente == null) { ?>
                                                <tr>
                                                    <td colspan="3" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar <<< </a></td>
                                                </tr>
                                            <?php } ?>
                                            <h3><i class='bx bx-cart'></i> Venda #<?php echo sprintf('%04d', $result->idVendas) ?></h3>
                                        </tbody>
                                    </table>
                                    <table class="table table-condensend">
                                        <tbody>
                                            <tr>
                                                <td style="width: 60%; padding-left: 0">
                                                    <span>
                                                        <h5><b>CLIENTE</b></h5>
                                                        <span><i class='bx bxs-business'></i> <b><?php echo $result->nomeCliente ?></b></span><br />
                                                        <?php if (!empty($result->celular) || !empty($result->telefone) || !empty($result->contato_cliente)): ?>
                                                            <span><i class='bx bxs-phone'></i>
                                                                <?= !empty($result->contato_cliente) ? $result->contato_cliente . ' ' : "" ?>
                                                                <?php if ($result->celular == $result->telefone) { ?>
                                                                    <?= $result->celular ?>
                                                                <?php } else { ?>
                                                                    <?= !empty($result->telefone) ? $result->telefone : "" ?>
                                                                    <?= !empty($result->celular) && !empty($result->telefone) ? ' / ' : "" ?>
                                                                    <?= !empty($result->celular) ? $result->celular : "" ?>
                                                                <?php } ?>
                                                            </span></br>
                                                        <?php endif; ?>
                                                        <?php $retorno_end = array_filter([$result->rua, $result->numero, $result->complemento, $result->bairro . ' - ']);
                                                            $endereco = implode(', ', $retorno_end);
                                                            echo '<i class="fas fa-map-marker-alt"></i> ';
                                                            if (!empty($endereco)) {
                                                                echo $endereco;
                                                            }
                                                            if (!empty($result->cidade) || !empty($result->estado) || !empty($result->cep)) {
                                                                echo "<span> {$result->cep}, {$result->cidade}/{$result->estado}</span><br>";
                                                            }
                                                        ?>
                                                        <?php if (!empty($result->email)): ?>
                                                            <span>
                                                                <i class="fas fa-envelope"></i> <?php echo $result->email ?>
                                                            </span><br>
                                                        <?php endif; ?>
                                                    </span>
                                                </td>
                                                <td style="width: 40%; padding-left: 0">
                                                    <ul>
                                                        <li>
                                                            <span>
                                                                <h5><b>RESPONSÁVEL</b></h5>
                                                            </span>
                                                            <span><b><i class="fas fa-user"></i>
                                                                    <?php echo $result->nome ?></b></span><br />
                                                            <span><i class="fas fa-phone"></i>
                                                                <?php echo $result->telefone_usuario ?></span><br />
                                                            <span><i class="fas fa-envelope"></i>
                                                                <?php echo $result->email_usuario ?></span>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
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
                                    <?php if ($result->observacoes != null): ?>
                                    <tr>
                                        <td colspan="4"><b>Observações Internas: </b><?php echo htmlspecialchars_decode($result->observacoes); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($result->observacoes_cliente != null): ?>
                                    <tr>
                                        <td colspan="4"><b>Observações ao Cliente: </b><?php echo htmlspecialchars_decode($result->observacoes_cliente); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="margin-top: 0; padding-top: 0">
                            <?php if ($produtos != null): ?>
                                <table class="table table-bordered table-condensed" id="tblProdutos">
                                    <thead>
                                        <tr>
                                            <th>COD. BARRAS</th>
                                            <th>PRODUTO</th>
                                            <th>QTD</th>
                                            <th>UNT</th>
                                            <th>SUBTOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($produtos as $p): ?>
                                            <?php $totalProdutos += $p->subTotal; ?>
                                            <tr>
                                                <td style="width: 10%;"><?php echo $p->codDeBarra; ?></td>
                                                <td style="width: 70%;"><?php echo $p->descricao; ?></td>
                                                <td style="width: 5%;"><?php echo $p->quantidade; ?></td>
                                                <td style="width: 10%;">R$ <?php echo ($p->preco ?: $p->precoVenda); ?></td>
                                                <td style="width: 10%;">R$ <?php echo number_format($p->subTotal, 2, ',', '.'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <hr>
                                <div style="text-align: right;">
                                <?php if ($result->valor_desconto != 0 && $result->desconto != 0): ?>
                                    <h4>SUBTOTAL: R$ <?php echo number_format($totalProdutos, 2, ',', '.'); ?></h4>
                                    <h4>DESCONTO: R$ <?php echo number_format($result->valor_desconto - $totalProdutos, 2, ',', '.'); ?></h4>
                                    <h4>TOTAL: R$ <?php echo number_format($result->valor_desconto, 2, ',', '.'); ?></h4>
                                    
                                <?php else: ?>
                                    <h4 style="text-align: right">TOTAL: R$ <?php echo number_format($totalProdutos, 2, ',', '.'); ?></h4>
                                <?php endif; ?>
                                </div>
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
