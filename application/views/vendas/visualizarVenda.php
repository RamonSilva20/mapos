<?php $totalProdutos = 0; ?>
<div class="row-fluid" style="margin-top: 0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-cash-register"></i>
                </span>
                <h5>Dados da Venda Nº <span><b><?php echo $result->idVendas; ?></b></h5>
                <div class="buttons">
                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eVenda')): ?>
                        <a title="Editar Venda" class="button btn btn-mini btn-success" href="<?php echo base_url() . 'index.php/vendas/editar/' . $result->idVendas; ?>">
                            <span class="button__icon"><i class="bx bx-edit"></i></span>
                            <span class="button__text">Editar</span>
                        </a>
                    <?php endif; ?>
                    <a target="_blank" title="Imprimir Orcamento A4" class="button btn btn-mini btn-inverse" href="<?php echo site_url() . '/vendas/imprimirVendaOrcamento/' . $result->idVendas; ?>">
                        <span class="button__icon"><i class="bx bx-printer"></i></span>
                        <span class="button__text">Orçamento</span>
                    </a>
                    <a target="_blank" title="Imprimir Papel A4" class="button btn btn-mini btn-inverse" href="<?php echo site_url() . '/vendas/imprimir/' . $result->idVendas; ?>">
                        <span class="button__icon"><i class="bx bx-printer"></i></span>
                        <span class="button__text">Papel A4</span>
                    </a>
                    <a target="_blank" title="Imprimir Cupom Não Fiscal" class="button btn btn-mini btn-inverse" href="<?php echo site_url() . '/vendas/imprimirTermica/' . $result->idVendas; ?>">
                        <span class="button__icon"><i class="bx bx-printer"></i></span>
                        <span class="button__text">CP Não Fiscal</span>
                    </a>
                    <a href="#modal-gerar-pagamento" id="btn-forma-pagamento" role="button" data-toggle="modal" class="button btn btn-mini btn-info">
                        <span class="button__icon"><i class="bx bx-qr"></i></span>
                        <span class="button__text">Gerar Pagamento</span>
                    </a>
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
            <?= $modalGerarPagamento ?>
        </div>
    </div>
</div>
