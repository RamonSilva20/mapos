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

                    <a target="_blank" title="Imprimir" class="btn btn-mini btn-inverse" href="<?php echo site_url() ?>/mine/imprimirOs/<?php echo $result->idOs; ?>"><i class="fas fa-print"></i> Imprimir</a>
                </div>
            </div>
            <div class="widget-content" id="printOs">
                <div class="invoice-content">
                    <div class="invoice-head" style="margin-bottom: 0">

                        <table class="table table-condensed">
                            <tbody>
                                <?php if ($emitente == null) { ?>

                                    <tr>
                                        <td colspan="3" class="alert">Os dados do emitente não foram configurados.</td>
                                    </tr>
                                <?php
                                } else { ?>
                                    <tr>
                                        <td style="width: 25%"><img src=" <?php echo $emitente[0]->url_logo; ?> "></td>
                                        <td> <span style="font-size: 20px; ">
                                                <?php echo $emitente[0]->nome; ?></span> </br><span>
                                                <?php echo $emitente[0]->cnpj; ?> </br>
                                                <?php echo $emitente[0]->rua . ', nº:' . $emitente[0]->numero . ', ' . $emitente[0]->bairro . ' - ' . $emitente[0]->cidade . ' - ' . $emitente[0]->uf; ?> </span> </br> <span> E-mail:
                                                <?php echo $emitente[0]->email . ' - Fone: ' . $emitente[0]->telefone; ?></span></td>
                                        <td style="width: 18%; text-align: center">#Protocolo: <span>
                                                <?php echo $result->idOs ?></span></br> </br> <span>Emissão:
                                                <?php echo date('d/m/Y') ?></span></td>
                                    </tr>

                                <?php
                                } ?>
                            </tbody>
                        </table>


                        <table class="table table-condensed">
                            <tbody>
                                <tr>
                                    <td style="width: 50%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <span>
                                                    <h5>Cliente</h5>
                                                    <span>
                                                        <?php echo $result->nomeCliente ?></span><br />
                                                    <span>
                                                        <?php echo $result->rua ?>,
                                                        <?php echo $result->numero ?>,
                                                        <?php echo $result->bairro ?></span><br />
                                                    <span>
                                                        <?php echo $result->cidade ?> -
                                                        <?php echo $result->estado ?></span>
                                            </li>
                                        </ul>
                                    </td>
                                    <td style="width: 50%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <span>
                                                    <h5>Responsável</h5>
                                                </span>
                                                <span>
                                                    <?php echo $result->nome ?></span> <br />
                                                <span>Telefone:
                                                    <?php echo $result->telefone_usuario ?></span><br />
                                                <span>Email:
                                                    <?php echo $result->email_usuario ?></span>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>

                    <div style="margin-top: 0; padding-top: 0">

                        <?php if ($result->descricaoProduto != null || $result->defeito != null || $result->laudoTecnico != null || $result->observacoes) { ?>

                            <table class="table table-condensed">
                                <tbody>
                                    <?php if ($result->descricaoProduto != null) { ?>
                                        <tr>
                                            <td>
                                                <strong>Descrição</strong><br>
                                                <?php echo htmlspecialchars_decode($result->descricaoProduto) ?>
                                            </td>
                                        </tr>

                                    <?php
                                    } ?>

                                    <?php if ($result->defeito != null) { ?>
                                        <tr>
                                            <td>
                                                <strong>Defeito</strong><br>
                                                <?php echo htmlspecialchars_decode($result->defeito) ?>
                                            </td>
                                        </tr>
                                    <?php
                                    } ?>

                                    <?php if ($result->laudoTecnico != null) { ?>
                                        <tr>
                                            <td>
                                                <strong>Laudo Técnico</strong> <br>
                                                <?php echo htmlspecialchars_decode($result->laudoTecnico) ?>
                                            </td>
                                        </tr>
                                    <?php
                                    } ?>

                                    <?php if ($result->observacoes != null) { ?>
                                        <tr>
                                            <td>
                                                <strong>Observações</strong> <br>
                                                <?php echo htmlspecialchars_decode($result->observacoes) ?>
                                            </td>
                                        </tr>
                                    <?php
                                    } ?>

                                </tbody>
                            </table>

                        <?php
                        } ?>


                        <?php if ($produtos != null || $servicos != null) { ?>
                            <br />
                            <table class="table table-condensed" id="tblProdutos">
                                <thead>
                                    <tr>
                                        <th style="font-size: large">Item</th>
                                        <th style="font-size: large">Preço unit.</th>
                                        <th style="font-size: large">Quantidade</th>
                                        <th style="font-size: large">Sub-total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                    foreach ($produtos as $p) {
                                        $totalProdutos = $totalProdutos + $p->subTotal;
                                        echo '<tr>';
                                        echo '<td style="text-align: center">' . $p->descricao . '</td>';
                                        echo '<td style="text-align: center">R$' . number_format($p->preco, 2, ',', '.') . '</td>';
                                        echo '<td style="text-align: center">' . $p->quantidade . '</td>';
                                        echo '<td style="text-align: center">R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                                        echo '</tr>';
                                    } ?>


                                    <?php
                                    foreach ($servicos as $s) {
                                        $totalServico = $totalServico + $s->subTotal;
                                        echo '<tr>';
                                        echo '<td style="text-align: center">' . $s->nome . '</td>';
                                        echo '<td style="text-align: center">R$' . number_format($s->preco, 2, ',', '.') . '</td>';
                                        echo '<td style="text-align: center">' . $p->quantidade . '</td>';
                                        echo '<td style="text-align: center">R$ ' . number_format($s->subTotal, 2, ',', '.') . '</td>';
                                        echo '</tr>';
                                    }
                                    ?>

                                    <tr>
                                        <td colspan="3" style="text-align: right"></td>
                                        <td style="text-align: center"><strong>Total: R$
                                                <?php echo number_format($totalProdutos + $totalServico, 2, ',', '.'); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php
                        } ?>
                    </div>





                </div>
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

<?php if ($pagamento->nome != 'MercadoPago') { ?>
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
<script type="text/javascript">
    $(document).ready(function() {
        $("#imprimir").click(function() {
            PrintElem('#printOs');
        })

        function PrintElem(elem) {
            Popup($(elem).html());
        }

        function Popup(data) {
            var mywindow = window.open('', 'mydiv', 'height=600,width=800');
            mywindow.document.open();
            mywindow.document.onreadystatechange = function() {
                if (this.readyState === 'complete') {
                    this.onreadystatechange = function() {};
                    mywindow.focus();
                    mywindow.print();
                    mywindow.close();
                }
            }


            mywindow.document.write('<html><head><title>Map Os</title>');
            mywindow.document.write("<link rel='stylesheet' href='<?php echo base_url(); ?>assets/css/bootstrap.min.css' />");
            mywindow.document.write("<link rel='stylesheet' href='<?php echo base_url(); ?>assets/css/bootstrap-responsive.min.css' />");
            mywindow.document.write("<link rel='stylesheet' href='<?php echo base_url(); ?>assets/css/matrix-style.css' />");
            mywindow.document.write("<link rel='stylesheet' href='<?php echo base_url(); ?>assets/css/matrix-media.css' />");


            mywindow.document.write("</head><body >");
            mywindow.document.write(data);
            mywindow.document.write("</body></html>");

            mywindow.document.close(); // necessary for IE >= 10


            return true;
        }

    });
</script>