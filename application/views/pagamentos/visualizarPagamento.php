<?php $totalProdutos = 0; ?>
<div class="row-fluid" style="margin-top: 0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-book"></i>
                </span>
                <h5>Credencial de Pagamento</h5>
                <div class="buttons">
                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'ePagamento')) {
                        echo '<a title="Editar Credencial de Pagamento" class="btn btn-mini btn-info" href="' . base_url() . 'index.php/pagamentos/editar/' . $pagamento->idPag . '"><i class="fas fa-edit"></i> Editar</a>';
                    } ?>
                </div>
            </div>
            <div class="widget-content" id="printOs">
                <div class="invoice-content">
                    <div class="invoice-head">
                        <table class="table">
                            <tbody>

                            </tbody>
                        </table>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td style="width: 25%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <span>
                                                    <h5><b>Nome</b></h5>
                                                </span>
                                                <span>
                                                    <?php echo $pagamento->nome ?></span> <br />
                                            </li>
                                        </ul>
                                    </td>
                                    <td style="width: 60%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <span>
                                                    <h5><b>Credenciais</b></h5>
                                                </span>
                                                <span><b>Client Id:</b>
                                                    <?php echo $pagamento->client_id ?></span> <br />
                                                <span><b>Client Secret:</b>
                                                    <?php echo $pagamento->client_secret ?></span><br />
                                                <span><b>Public key:</b>
                                                    <?php echo $pagamento->public_key ?></span><br />
                                                <span><b>Access token:</b> <?php echo $pagamento->access_token ?></span><br />
                                                <span><b>Pagamento Padrão:</b> <?php echo $pagamento->default_pag == 1 ? "SIM" : "NÃO"; ?>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row-fluid" style="margin-top: 0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-book"></i>
                </span>
                <h5>Pagamentos Gerados</h5>
            </div>
            <div class="widget-content" id="printOs">
                <div class="invoice-content">
                    <div class="invoice-head">
                        <table class="table">
                            <tbody>
                                <td id="msg"></td>
                            </tbody>
                        </table>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td style="width: 25%; padding-left: 0">
                                        <ul>
                                            <li>
                                                <span>
                                                    <h5><b>Boletos Gerados</b></h5>
                                                </span>
                                                <span>
                                                    <table class="table table-bordered table-condensed" id="tblProdutos">
                                                        <thead>
                                                            <tr>
                                                                <th>Data</th>
                                                                <th>Nome</th>
                                                                <th>Ordens</th>
                                                                <th>Status</th>
                                                                <th>Valor</th>
                                                                <th>Tipo</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php

                                                            if ($pagamento) {

                                                                if ($pagamento->nome == 'Wirecard') {
                                                                    $payment = @$this->Wirecard->allPayment(
                                                                        $pagamento->access_token,
                                                                        $pagamento->public_key
                                                                    );

                                                                    //$payment = json_decode(utf8_encode($payment), true);
                                                                    $decode = json_decode($payment, TRUE);

                                                                    foreach ($decode["orders"] as $valor) {
                                                                        $date = new DateTime($valor["createdAt"]);

                                                                        echo '<td>' . $date->format('d/m/Y H:i:s') . '</td>';
                                                                        echo '<td>' . $valor["customer"]["fullname"] . '</td>';
                                                                        echo '<td>' . $valor["id"] . '</td>';
                                                                        echo '<td>' . $valor["status"] . '</td>';
                                                                        echo '<td> R$ ' . $newValor = number_format(floatval($valor["amount"]["total"]) / 100, 2, ',', '.') . '</td>';
                                                                        echo '<td>' . $valor["payments"]["0"]["fundingInstrument"]["method"] . '</td>';
                                                                        echo '<td><form id="form-gerar-boleto" action="' . base_url() . 'index.php/pagamentos/gerarboleto" method="POST">
                                                                <input type="hidden" id="accessToken" name="accessToken" value="' . $pagamento->access_token . '">
                                                                <input type="hidden" id="publicKey" name="publicKey" value="' . $pagamento->public_key . '">
                                                                <input type="hidden" id="codePayment" name="codePayment" value="' . $valor['payments']['0']['id'] . '">
                                                                <div class="text-center">
                                                                <button class="btn btn-success type="submit">Gerar Boleto</button></form></td>
                                                                </div>';
                                                                        echo '</tr>';
                                                                    }
                                                                    //print_r($payment);
                                                                }
                                                            }
                                                            ?>

                                                        </tbody>
                                                    </table>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('form#form-gerar-boleto').submit(function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function(response) {
                window.open(response, '_blank', false);
                //window.focus(); //manter focus na janela anterior e não na nova janela.

                //$('#msg').html(response).fadeIn('slow');
                $('#msg').addClass("alert alert-success").html("Boleto gerado com sucesso!").fadeIn('slow'); //also show a success message 
                $('#msg').delay(5000).fadeOut('slow');

            }
        });
        return false;

    });
</script>
