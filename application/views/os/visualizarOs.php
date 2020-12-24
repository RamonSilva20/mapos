<link href="<?= base_url('assets/css/custom.css'); ?>" rel="stylesheet">
<?php $totalServico = 0;
$totalProdutos = 0; ?>
<div class="row-fluid" style="margin-top: 0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-diagnoses"></i>
                </span>
                <h5>Ordem de Serviço</h5>
                <div class="buttons">
                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
                        echo '<a title="Editar OS" class="btn btn-mini btn-info" href="' . base_url() . 'index.php/os/editar/' . $result->idOs . '"><i class="fas fa-edit"></i> Editar</a>';
                    } ?>

                    <a target="_blank" title="Imprimir OS" class="btn btn-mini btn-inverse" href="<?php echo site_url() ?>/os/imprimir/<?php echo $result->idOs; ?>"><i class="fas fa-print"></i> Imprimir A4</a>
                    <a target="_blank" title="Imprimir OS" class="btn btn-mini btn-inverse" href="<?php echo site_url() ?>/os/imprimirTermica/<?php echo $result->idOs; ?>"><i class="fas fa-print"></i> Imprimir Não Fiscal</a>
                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
                        $zapnumber = preg_replace("/[^0-9]/", "", $result->celular_cliente);
                        echo '<a title="Enviar Por WhatsApp" class="btn btn-mini btn-success" id="enviarWhatsApp" target="_blank" href="https://web.whatsapp.com/send?phone=55' . $zapnumber . '&text=Prezado(a)%20*' . $result->nomeCliente . '*.%0d%0a%0d%0aSua%20*O.S%20' . $result->idOs . '*%20referente%20ao%20equipamento%20*' . strip_tags($result->descricaoProduto) . '*%20foi%20atualizada%20para%20*' . $result->status . '*.%0d%0aFavor%20entrar%20em%20contato%20para%20saber%20mais%20detalhes.%0d%0a%0d%0aAtenciosamente,%20_' . ($emitente ? $emitente[0]->nome : '') . '%20' . ($emitente ? $emitente[0]->telefone : '') . '_"><i class="fab fa-whatsapp"></i> WhatsApp</a>';
                    } ?>

                    <a title="Enviar por E-mail" class="btn btn-mini btn-warning" href="<?php echo site_url() ?>/os/enviar_email/<?php echo $result->idOs; ?>"><i class="fas fa-envelope"></i> Enviar por E-mail</a>
                    <?php if ($result->garantias_id) { ?> <a target="_blank" title="Imprimir Termo de Garantia" class="btn btn-mini btn-inverse" href="<?php echo site_url() ?>/garantias/imprimir/<?php echo $result->garantias_id; ?>"><i class="fas fa-text-width"></i> Imprimir Termo de Garantia</a> <?php  } ?>
                </div>
            </div>
            <div class="widget-content" id="printOs">
                <div class="invoice-content">
                    <div class="invoice-head" style="margin-bottom: 0">

                        <table class="table table-condensed">
                            <tbody>
                                <?php if ($emitente == null) { ?>

                                    <tr>
                                        <td colspan="3" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>
                                            <<<</td> </tr> <?php } else { ?> <tr>
                                        <td style="width: 25%"><img src=" <?php echo $emitente[0]->url_logo; ?> " style="max-height: 100px"></td>
                                        <td> <span style="font-size: 20px; "> <?php echo $emitente[0]->nome; ?></span> </br><span><?php echo $emitente[0]->cnpj; ?> </br> <?php echo $emitente[0]->rua . ', ' . $emitente[0]->numero . ' - ' . $emitente[0]->bairro . ' - ' . $emitente[0]->cidade . ' - ' . $emitente[0]->uf; ?> </span> </br> <span> E-mail: <?php echo $emitente[0]->email . ' - Fone: ' . $emitente[0]->telefone; ?></span></td>
                                        <td style="width: 18%; text-align: center"><b>N° OS:</b> <span><?php echo $result->idOs ?></span></br> </br> <span>Emissão: <?php echo date('d/m/Y') ?></span></td>
                                    </tr>

                                <?php } ?>
                            </tbody>
                        </table>

                        <table class="table table-condensend">
                            <tbody>
                                <tr>
                                    <td style="width: 50%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <span>
                                                    <h5><b>CLIENTE</b></h5>
                                                    <span><?php echo $result->nomeCliente ?></span><br />
                                                    <span><?php echo $result->rua ?>, <?php echo $result->numero ?>, <?php echo $result->bairro ?></span>,
                                                    <span><?php echo $result->cidade ?> - <?php echo $result->estado ?></span><br>
                                                    <span>E-mail: <?php echo $result->email ?></span><br>
                                                    <span>Contato: <?php echo $result->celular_cliente ?></span>
                                            </li>
                                        </ul>
                                    </td>
                                    <td style="width: 50%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <span>
                                                    <h5><b>RESPONSÁVEL</b></h5>
                                                </span>
                                                <span><?php echo $result->nome ?></span> <br />
                                                <span>Contato: <?php echo $result->telefone_usuario ?></span><br />
                                                <span>Email: <?php echo $result->email_usuario ?></span>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>

                    <div style="margin-top: 0; padding-top: 0">

                        <table class="table table-condensed">
                            <tbody>
                                <?php if ($result->dataInicial != null) { ?>
                                    <tr>
                                        <td>
                                            <b>STATUS OS: </b>
                                            <?php echo $result->status ?>
                                        </td>

                                        <td>
                                            <b>DATA INICIAL: </b>
                                            <?php echo date('d/m/Y', strtotime($result->dataInicial)); ?>
                                        </td>

                                        <td>
                                            <b>DATA FINAL: </b>
                                            <?php echo $result->dataFinal ? date('d/m/Y', strtotime($result->dataFinal)) : ''; ?>
                                        </td>

                                        <td>
                                            <b>GARANTIA: </b>
                                            <?php echo $result->garantia . ' dias'; ?>
                                        </td>

                                        <td>
                                            <b>
                                                <?php if ($result->status == 'Finalizado') { ?>
                                                    VENC. DA GARANTIA:
                                            </b>
                                            <?php echo dateInterval($result->dataFinal, $result->garantia); ?><?php } ?>
                                        </td>
                                        <?php if ($result->refGarantia != '') { ?>
                                            <td>
                                                <b>TERMO GARANTIA: </b>
                                                <?php echo $result->refGarantia; ?>
                                            </td>
                                        <?php } ?>
                                    </tr>
                                <?php } ?>

                                <?php if ($result->descricaoProduto != null) { ?>
                                    <tr>
                                        <td colspan="6">
                                            <b>DESCRIÇÃO: </b>
                                            <?php echo htmlspecialchars_decode($result->descricaoProduto) ?>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if ($result->defeito != null) { ?>
                                    <tr>
                                        <td colspan="6">
                                            <b>DEFEITO APRESENTADO: </b>
                                            <?php echo htmlspecialchars_decode($result->defeito) ?>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if ($result->observacoes != null) { ?>
                                    <tr>
                                        <td colspan="6">
                                            <b>OBSERVAÇÕES: </b>
                                            <?php echo htmlspecialchars_decode($result->observacoes) ?>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if ($result->laudoTecnico != null) { ?>
                                    <tr>
                                        <td colspan="5">
                                            <b>LAUDO TÉCNICO: </b>
                                            <?php echo htmlspecialchars_decode($result->laudoTecnico) ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                        <?php if ($produtos != null) { ?>
                            <br />
                            <table class="table table-bordered table-condensed" id="tblProdutos">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Quantidade</th>
                                        <th>Preço unit.</th>
                                        <th>Sub-total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                    foreach ($produtos as $p) {
                                        $totalProdutos = $totalProdutos + $p->subTotal;
                                        echo '<tr>';
                                        echo '<td>' . $p->descricao . '</td>';
                                        echo '<td>' . $p->quantidade . '</td>';
                                        echo '<td>' . $p->preco ?: $p->precoVenda . '</td>';
                                        echo '<td>R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                                        echo '</tr>';
                                    } ?>

                                    <tr>
                                        <td></td>
                                        <td colspan="2" style="text-align: right"><strong>Total:</strong></td>
                                        <td><strong>R$ <?php echo number_format($totalProdutos, 2, ',', '.'); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php } ?>

                        <?php if ($servicos != null) { ?>
                            <table class="table table-bordered table-condensed">
                                <thead>
                                    <tr>
                                        <th>Serviço</th>
                                        <th>Quantidade</th>
                                        <th>Preço unit.</th>
                                        <th>Sub-total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    setlocale(LC_MONETARY, 'en_US');
                                    foreach ($servicos as $s) {
                                        $preco = $s->preco ?: $s->precoVenda;
                                        $subtotal = $preco * ($s->quantidade ?: 1);
                                        $totalServico = $totalServico + $subtotal;
                                        echo '<tr>';
                                        echo '<td>' . $s->nome . '</td>';
                                        echo '<td>' . ($s->quantidade ?: 1) . '</td>';
                                        echo '<td>' . $preco . '</td>';
                                        echo '<td>R$ ' . number_format($subtotal, 2, ',', '.') . '</td>';
                                        echo '</tr>';
                                    } ?>

                                    <tr>
                                        <td colspan="3" style="text-align: right"><strong>Total:</strong></td>
                                        <td><strong>R$ <?php echo number_format($totalServico, 2, ',', '.'); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php } ?>
                        <?php
                        if ($totalProdutos != 0 || $totalServico != 0) {
                            echo "<h4 style='text-align: right'>Valor Total: R$" . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div id="msgError" class=" alert alert-danger" hidden> </div>
        <?php

        if ($pagamento) {
            if ($totalProdutos || $totalServico) {

                $preference = @$this->MercadoPago->getPreference($pagamento->access_token, $result->idOs, 'Pagamento da OS', ($totalProdutos + $totalServico), $quantidade = 1);
                if ($pagamento->nome == 'MercadoPago' && isset($preference->id)) {
                    echo '<form action="' . site_url() . '" method="POST">
                            <script src="https://www.mercadopago.com.br/integrations/v1/web-payment-checkout.js" data-preference-id="' . $preference->id . '" data-button-label="Gerar Pagamento">
                            </script>
                        </form>';
                }
            }
        }
        ?>

    </div>
</div>
<?php if ($pagamento->nome != "MercadoPago") { ?>
    <a href="#myModalFormaPagamento" id="btn-forma-pagamento" role="button" data-toggle="modal" class="btn btn-success"><i class="fas fa-cash-register"></i> Gerar Pagamento</a>
<?php } ?>
<div class="modal fade" id="myModalFormaPagamento" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Escolher Forma de Pagamento</h4>
            </div>
            <div class="modal-body">
                <div id="forma-pag" class="">
                    <div class="form-group">
                        <label for="exampleInputPassword1">Forma de Pagamento: </label>
                        <select id="escolha-pagamento" class="form-control" required>
                            <option value="" selected>Forma de Pagamento</option>
                            <option value="boleto">Boleto</option>
                            <option value="link-pagamento">Link de Pagamento</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div id="mostra-butao-pagamento-boleto" hidden>
                    <?php
                    if ($pagamento) {
                        if ($totalProdutos || $totalServico) {

                            if ($pagamento->nome == 'GerenciaNet') {
                                echo '<form id="form-gerar-pagamento-gerencianet-boleto" action="' . base_url() . 'index.php/os/gerarpagamentogerencianetboleto" method="POST">
                                <input type="hidden" id="nomeCliente" name="nomeCliente" value="' . $result->nomeCliente . '">
                                <input type="hidden" id="emailCliente" name="emailCliente" value="' . $result->email . '">
                                <input type="hidden" id="documentoCliente" name="documentoCliente" value="' . $result->documento . '">
                                <input type="hidden" id="celular_cliente" name="celular_cliente" value="' . $result->celular_cliente . '">
                                <input type="hidden" id="ruaCliente" name="ruaCliente" value="' . $result->rua . '">
                                <input type="hidden" id="numeroCliente" name="numeroCliente" value="' . $result->numero . '">
                                <input type="hidden" id="bairroCliente" name="bairroCliente" value="' . $result->bairro . '">
                                <input type="hidden" id="cidadeCliente" name="cidadeCliente" value="' . $result->cidade . '">
                                <input type="hidden" id="estadoCliente" name="estadoCliente" value="' . $result->estado . '">
                                <input type="hidden" id="cepCliente" name="cepCliente" value="' . $result->cep . '">
                                <input type="hidden" id="idOs" name="idOs" value="' . $result->idOs . '">
                                <input type="hidden" id="titleBoleto" name="titleBoleto" value="OS:">
                                <input type="hidden" id="totalValor" name="totalValor" value="' . ($totalProdutos + $totalServico) . '">
                                <input type="hidden" id="quantidade" name="quantidade" value="1">
                    <button id="submitPayment" type="submit" class="btn btn-success">Gerar Boleto de Pagamento</button>
                    </form>';
                            }
                        }
                    } ?>
                </div>

                <div id="mostra-butao-pagamento-link" hidden>
                    <?php
                    if ($pagamento) {
                        if ($totalProdutos || $totalServico) {

                            if ($pagamento->nome == 'GerenciaNet') {

                                echo '<form id="form-gerar-pagamento-gerencianet-link" action="' . base_url() . 'index.php/os/gerarpagamentogerencianetlink" method="POST">
                    <input type="hidden" id="idOs" name="idOs" value="' . $result->idOs . '">
                    <input type="hidden" id="titleLink" name="titleLink" value="OS:">
                    <input type="hidden" id="totalValor" name="totalValor" value="' . ($totalProdutos + $totalServico) . '">
                    <input type="hidden" id="quantidade" name="quantidade" value="1">
                    <button id="submitPayment" type="submit" class="btn btn-success">Gerar Link de Pagamento</button>
                    </form>';
                            }
                        }
                    } ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!--div responsável por exibir o resultado da emissão do boleto-->
<div class="modal fade" id="myModalBoleto" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabelMsg">Boleto Emitido</h4>
            </div>
            <div class="modal-body">
                <div id="boleto" class="">
                    <table class="table" id="result_table">
                        <!--"code":200,"data":{"barcode":"03399.32766 55400.000000 60348.101027 6 69020000009000","link":"https:\/\/visualizacaosandbox.gerencianet.com.br\/emissao\/59808_79_FORAA2\/A4XB-59808-60348-HIMA4","expire_at":"2016-08-30","charge_id":76777,"status":"waiting","total":9000,"payment":"banking_billet"-->

                    </table>
                </div>
            </div>
            <div class="modal-footer">

            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="myModalLink" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title-msg" id="myModalLabelMsg">Link Emitido</h4>
            </div>
            <div class="modal-body">
                <div id="boleto" class="">
                    <table class="table" id="result_table_link">
                        <!--"code":200,"data":{"barcode":"03399.32766 55400.000000 60348.101027 6 69020000009000","link":"https:\/\/visualizacaosandbox.gerencianet.com.br\/emissao\/59808_79_FORAA2\/A4XB-59808-60348-HIMA4","expire_at":"2016-08-30","charge_id":76777,"status":"waiting","total":9000,"payment":"banking_billet"-->

                    </table>
                </div>
            </div>
            <div class="modal-footer">

            </div>
        </div>
    </div>

</div>


<!-- Este componente é utilizando para exibir um alerta(modal) para o usuário aguardar as consultas via API.  -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Um momento.</h4>
            </div>
            <div class="modal-body">
                Estamos processando a requisição <img src="<?= base_url('assets/img/ajax-loader.gif'); ?>">.
            </div>
            <div class="modal-footer">

            </div>
        </div>
    </div>
</div>
<script src="<?= base_url('assets/js/script-payments.js'); ?>"></script>
