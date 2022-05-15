<?php $totalProdutos = 0; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Ulsis</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=0.6" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-style.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-media.css" />
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-1.10.2.min.js"></script>
</head>

<body  style="background-color: white;">
    <div class="container-fluid bg-white">
        <div class="row-fluid bg-white">
            <div class="span12 bg-white">
                <div class="invoice-content bg-white" style="margin-bottom: 0; padding: 0">
                    <div class="invoice-head" style="margin-top: 0; margin-bottom: 0;padding-top: 0; padding-bottom:0;">
                        <table class="table bg-white">
                            <tbody>
                                <?php if ($emitente == null) { ?>
                                    <tr >
                                        <td colspan="3" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>
                                            <<<</td> </tr> <?php
                                                        } else { ?> <tr>
                                        <td style="width: 15%"><img style="height: 85px;"src=" <?php echo $emitente[0]->url_logo ; ?>  " ></td>
                                        <td> <span style="font-size: 20px; ">
                                                <?php echo $emitente[0]->nome; ?></span> </br><span>
                                                <?php echo $emitente[0]->cnpj; ?> </br>
                                                <?php echo $emitente[0]->rua . ', nº:' . $emitente[0]->numero . ', ' . $emitente[0]->bairro . ' - ' . $emitente[0]->cidade . ' - ' . $emitente[0]->uf; ?> </span> </br> <span> E-mail:
                                                <?php echo $emitente[0]->email . ' - Fone: ' . $emitente[0]->telefone; ?></span></td>
                                        <td style="width: 41%; text-align: center">
                                        <h3># Recibo ref. venda: <?php echo $result->idVendas ?> </h3>
                                        <p> </p>

                                                <h4>Emitido: <?php echo date('d/m/Y'); ?> Assinatura: <?php  
                                                $assinatura = new Assinador();
                                                $assinatura->assinar($result->idVendas,$lancamentos[0]->valor,$emitente[0]->cnpj);
                                                echo $assinatura->getAsssinatura();?></h4>
                                                
                                        </td>
                                    </tr>
                                <?php
                                //echo "<h4>RECIBO REFERENTE VENDA: $result->idVendas</h4>";
                                                        } ?>
                            </tbody>
                        </table>
                        <table class="table bg-white">
                            <tbody>
                                <tr>
                                    <td style="width: 50%; padding-left: 0;padding-top: 0">
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
                    <div style="margin-top: 0; margin-bottom: 0;padding-top: 0; padding-bottom:0;">
                        <?php if ($produtos != null) { ?>
                            <table class="table table-bordered table-condensed" id="tblProdutos">
                                <thead>
                                    <tr>
                                        <th style="font-size: 15px">Produto</th>
                                        <th style="font-size: 15px">Quantidade</th>
                                        <th style="font-size: 15px">Preço unit.</th>
                                        <th style="font-size: 15px">Sub-total</th>
                                    </tr>
                                </thead>
                                <tbody >
                                    <?php
                                    foreach ($produtos as $p) {
                                        $totalProdutos = $totalProdutos + $p->subTotal;
                                        echo '<tr>';
                                       // echo '<td>' . $p->codDeBarra . '</td>';
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

                        <h4 style="text-align: left">Observações:
                        </h4>
                        <table class="table"style="margin-top: 0; margin-bottom: 0;padding-top: 0; padding-bottom:0;">
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



                        <?php 
                        echo "<h5 style='text-align: right'>";
                        if($lancamentos[0]->baixado == 1){
                            echo "Valor recebido: R$".$lancamentos[0]->valor." |";
                        }
                        else{

                        }
                        ?>
                       
                            <?php
                            $lancamentos[0]->baixado == 0 ?$lancamentos[0]->baixado = "NÃO":$lancamentos[0]->baixado="SIM";
                            //echo $valorPago['valor'];
                           echo " Pagamento concluído/recebido: ".$lancamentos[0]->baixado;
                           if(isset($lancamentos[0]->data_pagamento) && $lancamentos[0]->baixado == "SIM"){
                               echo " em ".$lancamentos[0]->data_pagamento;// echo number_format($valorPago, 2, ',', '.'); 
                           }
                               echo "</H5>"?>
                        <?php
                        if ($totalProdutos != 0 || $totalServico != 0) {

                            echo "<h4 style='text-align: right'>Valor Total: R$" . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>";
                            
                        }
                        echo "<h6>Tempo dos serviços acima: ". $tempoTotal. " Minutos </h6>";
                        ?>

                        <table class="table table-bordered table-condensed">
                            <tbody>
                                <tr>
                                    <td>Data
                                        <hr>
                                    </td>
                                    <td>Assinatura do Cliente
                                        <hr>
                                    </td>
                                    <td>Assinatura do Responsável
                                        <hr>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/matrix.js"></script>

    <script>
        window.print();
    </script>

</body>

</html>


















                        <?php /*
                        <hr />
                        <h5 style="text-align: right">Valor pago pelo cliente: R$
                            <?php
                            $lancamentos[0]->baixado == 0 ?$lancamentos[0]->baixado = "NÃO":$lancamentos[0]->baixado="SIM";
                            //echo $valorPago['valor'];
                           echo ($lancamentos[0]->valor)." | Pagamento concluído: ".$lancamentos[0]->baixado;
                           if(isset($lancamentos[0]->data_pagamento))
                               echo " em ".$lancamentos[0]->data_pagamento;// echo number_format($valorPago, 2, ',', '.'); ?>
                               | Valor Total: R$ <?php echo number_format($totalProdutos, 2, ',', '.'); ?>
                        </h5>
                        
                            
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/matrix.js"></script>
    <script>
        window.print();
    </script>
</body>

</html>