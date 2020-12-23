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
