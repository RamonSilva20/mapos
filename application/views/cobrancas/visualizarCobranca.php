<div class="accordion" id="collapse-group">
    <div class="accordion-group widget-box">
        <div class="accordion-heading">
            <div class="widget-title" style="margin: -20px 0 0">
                <a data-parent="#collapse-group" href="#collapseGOne" data-toggle="collapse">
                    <h5>Detalhes da Cobrança</h5>
                </a>
            </div>
        </div>
        <div class="collapse in accordion-body">
            <div class="widget-content">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td style="text-align: right; width: 30%"><strong>Cliente</strong></td>
                            <td>
                                <?php echo $result->nomeCliente; ?>
                            </td>
                        </tr>

                        <tr>
                            <td style="text-align: right; width: 30%"><strong>Cliente (Documento)</strong></td>
                            <td>
                                <?php echo $result->documento; ?>
                            </td>
                        </tr>

                        <tr>
                            <td style="text-align: right; width: 30%"><strong>Cliente (Telefone)</strong></td>
                            <td>
                                <?php echo $result->telefone; ?>
                            </td>
                        </tr>

                        <tr>
                            <td style="text-align: right; width: 30%"><strong>Cliente (Celular)</strong></td>
                            <td>
                                <?php echo $result->celular; ?>
                            </td>
                        </tr>

                        <tr>
                            <td style="text-align: right; width: 30%"><strong>Cliente (Email)</strong></td>
                            <td>
                                <?php echo $result->email; ?>
                            </td>
                        </tr>

                        <tr>
                            <td style="text-align: right; width: 30%"><strong>Id interno (id)</strong></td>
                            <td>
                                <?php echo $result->idCobranca; ?>
                            </td>
                        </tr>

                        <tr>
                            <td style="text-align: right; width: 30%"><strong>Id externo (charge_id)</strong></td>
                            <td>
                                <?php echo $result->charge_id; ?>
                            </td>
                        </tr>

                        <tr>
                            <td style="text-align: right"><strong>Gateway de Pagamento</strong></td>
                            <td>
                                <?php echo $result->payment_gateway; ?>
                            </td>
                        </tr>

                        <tr>
                            <td style="text-align: right"><strong>Valor da cobrança</strong></td>
                            <td>R$
                                <?php echo number_format($result->total / 100, 2, ',', '.'); ?>
                            </td>
                        </tr>

                        <tr>
                            <td style="text-align: right"><strong>Status atual</strong></td>
                            <td>
                                <?php
                                    echo getCobrancaTransactionStatus(
                                        $this->config->item('payment_gateways'),
                                        $result->payment_gateway,
                                        $result->status
                                    );
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <td style="text-align: right"><strong>Expiração</strong></td>
                            <td>
                                <?php echo date('d/m/Y', strtotime($result->expire_at)); ?>
                            </td>
                        </tr>

                        <tr>
                            <td style="text-align: right"><strong>Método de pagamento</strong></td>
                            <td>
                                <?php echo $result->payment_method; ?>
                            </td>
                        </tr>

                        <tr>
                            <td style="text-align: right"><strong>Url de pagamento</strong></td>
                            <td>
                                <?php echo $result->payment_url; ?>
                            </td>
                        </tr>

                        <tr>
                            <td style="text-align: right"><strong>Código de barras</strong></td>
                            <td>
                                <?php echo $result->barcode; ?>
                            </td>
                        </tr>

                        <tr>
                            <td style="text-align: right"><strong>Link</strong></td>
                            <td>
                                <?php if ($result->link) { ?>
                                    <a href="<?php echo $result->link; ?>" target="_blank">Abrir em nova aba</a>
                                <?php } ?>
                            </td>
                        </tr>

                        <tr>
                            <td style="text-align: right"><strong>PDF</strong></td>
                            <td>
                                <?php if ($result->pdf) { ?>
                                    <a href="<?php echo $result->pdf; ?>" target="_blank">Abrir em nova aba</a>
                                <?php } ?>
                            </td>
                        </tr>

                        <tr>
                            <td style="text-align: right"><strong>Mensagem</strong></td>
                            <td>
                                <?php echo $result->message; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
