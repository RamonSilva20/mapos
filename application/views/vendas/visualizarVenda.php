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
            <div class="widget-content" id="printOs">
                <div class="invoice-content">
                    <div class="invoice-head">
                        <table class="table">
                            <tbody>
                                <?php if ($emitente == null) { ?>
                                    <tr>
                                        <td colspan="3" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>
                                            <<<</td> </tr> <?php
                                                        } else { ?> <tr>
                                        <td style="width: 25%"><img src=" <?php echo $emitente[0]->url_logo; ?> "></td>
                                        <td> <span style="font-size: 20px; ">
                                                <?php echo $emitente[0]->nome; ?></span> </br><span>
                                                <?php echo $emitente[0]->cnpj; ?> </br>
                                                <?php echo $emitente[0]->rua . ', nº:' . $emitente[0]->numero . ', ' . $emitente[0]->bairro . ' - ' . $emitente[0]->cidade . ' - ' . $emitente[0]->uf; ?> </span> </br> <span> E-mail:
                                                <?php echo $emitente[0]->email . ' - Fone: ' . $emitente[0]->telefone; ?></span></td>
                                        <td style="width: 18%; text-align: center">#Venda: <span>
                                                <?php echo $result->idVendas ?></span></br> </br> <span>Emissão:
                                                <?php echo date('d/m/Y'); ?></span>
                                            <?php if ($result->faturado) : ?>
                                                <br>
                                                Vencimento:
                                                <?php echo date('d/m/Y', strtotime($result->data_vencimento)); ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php
                                                        } ?>
                            </tbody>
                        </table>
                        <table class="table">
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
                                                        <?php echo $result->estado ?><br />
                                                        <span>Email:
                                                            <?php echo $result->emailCliente ?></span>
                                            </li>
                                        </ul>
                                    </td>
                                    <td style="width: 50%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <span>
                                                    <h5>Vendedor</h5>
                                                </span>
                                                <span>
                                                    <?php echo $result->nome ?></span> <br />
                                                <span>Telefone:
                                                    <?php echo $result->telefone ?></span><br />
                                                <span>Email:
                                                    <?php echo $result->emailUser ?></span>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 0; padding-top: 0">
                        <?php if ($produtos != null) { ?>
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
                                    <?php
                                    foreach ($produtos as $p) {
                                        $totalProdutos = $totalProdutos + $p->subTotal;
                                        echo '<tr>';
                                        echo '<td>' . $p->codDeBarra . '</td>';
                                        echo '<td>' . $p->descricao . '</td>';
                                        echo '<td>' . $p->quantidade . '</td>';
                                        echo '<td>' . ($p->preco ?: $p->precoVenda) . '</td>';
                                        echo '<td>R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                                        echo '</tr>';
                                    } ?>
                                    <tr>
                                        <td colspan="3" style="text-align: right"><strong>Total:</strong></td>
                                        <td><strong>R$
                                                <?php echo number_format($totalProdutos, 2, ',', '.'); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php
                        } ?>
                        <hr />
                        <h4 style="text-align: right">Valor Total: R$
                            <?php echo number_format($totalProdutos, 2, ',', '.'); ?>
                        </h4>
                    </div>
                    <hr />
                    <h4 style="text-align: left">Observações:
                    </h4>
                    <table class="table">
                        <tbody>
                            <tr>
                                <td style="width: 100%; padding-left: 0">
                                    <ul>
                                        <li>
                                            <span><?php echo htmlspecialchars_decode($result->observacoes_cliente) ?></span><br />
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="table">
                        <tbody>
                            <tr>
                                <td style="width: 100%; padding-left: 0">
                                    <ul>
                                        <li>

                                            <?php

                                            if ($pagamento) {
                                                if ($totalProdutos) {

                                                    try {
                                                        //code...
                                                        $preference = @$this->MercadoPago->getPreference($pagamento->access_token, $result->idVendas, 'Pagamento da Venda ', ($totalProdutos));
                                                        if ($pagamento->nome == 'MercadoPago' && isset($preference->id)) {
                                                            echo '<form action="' . site_url() . '" method="POST">
                    <script src="https://www.mercadopago.com.br/integrations/v1/web-payment-checkout.js" data-preference-id="' . $preference->id . '" data-button-label="Gerar Pagamento">
                    </script>
                </form>';
                                                        }
                                                    } catch (\Throwable $th) {
                                                        //throw $th;
                                                        echo '<div id="msgConexao" class=" alert alert-danger"> Precisa de conexão com a internet para gerar pagamento!</div>';
                                                    }
                                                }
                                            }
                                            ?>

                                            <table id="tabelaPagamento" name="tabelaPagamento" class="table table-condensed" hidden="true">
                                                <tbody>
                                                    <div id="msg"></div>
                                                    <tr>
                                                        <td colspan="3" id="dadosPagamento" name="dadosPagamento" class="alert">

                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                            <?php
                                            if ($pagamento) {
                                                if ($totalProdutos) {

                                                    if ($pagamento->nome == 'Wirecard') {

                                                        echo '<form id="form-gerar-pagamento" action="' . base_url() . 'index.php/vendas/gerarpagamento" method="POST">
            <input type="hidden" id="access_token" name="access_token" value="' . $pagamento->access_token . '">
            <input type="hidden" id="public_key" name="public_key" value="' . $pagamento->public_key . '">
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
            <input type="hidden" id="idVenda" name="idVenda" value="' . $result->idVendas . '">
            <input type="hidden" id="titleVenda" name="titleVenda" value="Venda:">
            <input type="hidden" id="totalValor" name="totalValor" value="' . (number_format($totalProdutos, 2, ',', '.')) . '">
            <input type="hidden" id="quantidade" name="quantidade" value="1">
            <button type="submit" class="btn btn-success">Gerar Pagamento</button>
            </form>';
                                                    }
                                                }
                                            } ?>

                                            <?php
                                            if ($pagamento) {
                                                if ($totalProdutos) {

                                                    if ($pagamento->nome == 'GerenciaNet') {

                                                        echo '<form id="form-gerar-pagamento" action="' . base_url() . 'index.php/vendas/gerarpagamentogerencianet" method="POST">
            <input type="hidden" id="client_id" name="client_id" value="' . $pagamento->client_id . '">
            <input type="hidden" id="client_secret" name="client_secret" value="' . $pagamento->client_secret . '">
            <input type="hidden" id="nomeCliente" name="nomeCliente" value="' . $result->nomeCliente . '">
            <input type="hidden" id="emailCliente" name="emailCliente" value="' . $result->email . '">
            <input type="hidden" id="documentoCliente" name="documentoCliente" value="' . $result->documento . '">
            <input type="hidden" id="celular_cliente" name="celular_cliente" value="' . $result->celular . '">
            <input type="hidden" id="ruaCliente" name="ruaCliente" value="' . $result->rua . '">
            <input type="hidden" id="numeroCliente" name="numeroCliente" value="' . $result->numero . '">
            <input type="hidden" id="bairroCliente" name="bairroCliente" value="' . $result->bairro . '">
            <input type="hidden" id="cidadeCliente" name="cidadeCliente" value="' . $result->cidade . '">
            <input type="hidden" id="estadoCliente" name="estadoCliente" value="' . $result->estado . '">
            <input type="hidden" id="cepCliente" name="cepCliente" value="' . $result->cep . '">
            <input type="hidden" id="idVenda" name="idVenda" value="' . $result->idVendas . '">
            <input type="hidden" id="titleVenda" name="titleVenda" value="Venda:">
            <input type="hidden" id="totalValor" name="totalValor" value="' . ($totalProdutos) . '">
            <input type="hidden" id="quantidade" name="quantidade" value="1">
            <button type="submit" class="btn btn-success">Gerar Pagamento</button>
            </form>';
                                                    }
                                                }
                                            } ?>

                </div>
            </div>
            <script type="text/javascript">
                $('form#form-gerar-pagamento').submit(function(e) {
                    e.preventDefault();
                    $("#tabelaPagamento").show();
                    document.getElementById("dadosPagamento").innerHTML = "Por favor aguarde gerando o boleto....";
                    var form = $(this);
                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'),
                        data: form.serialize(),
                        success: function(response) {
                            $("#tabelaPagamento").show();
                            document.getElementById("dadosPagamento").innerHTML = response;
                            //window.focus(); //manter focus na janela anterior e não na nova janela.
                            //$('#msg').html(response).fadeIn('slow');
                            if (online = navigator.onLine) {
                                $('#msg').addClass("alert alert-success").html("Pagamento gerado com sucesso!").fadeIn('slow'); //also show a success message 
                                $('#msg').delay(5000).fadeOut('slow');
                            }

                        }
                    });
                    return false;

                });
            </script>

            </li>
            </ul>
            </td>
            </tr>
            </tbody>
            </table>
            <hr />
        </div>
    </div>
</div>
</div>
</div>
