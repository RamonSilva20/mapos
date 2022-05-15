<?php $totalServico = 0;
$totalProdutos = 0; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Cupom</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-style.css" />
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="<?= base_url('assets/css/custom.css'); ?>" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
    <style>
        body {
            margin: 0px;
            padding: 0px;
            border: 0px;
            line-height: 0.4;
            width: 58mm;
        }

        .tabela {

            width: 100%;
            margin: 0px;
            padding: 0px;
            border: 1px solid;
            margin-bottom:1mm;
            line-height: 1;
            font-family: Arial;
            font-size:12px
            
        }
        .tabela tr{
            border-bottom: 1px solid;
            
            
        }
        .tabela th{
            border-right:1px solid grey;
        }
        .tabela td{
            border:1px solid grey;
            text-align:center
        }
    </style>
</head>

<body>
    <div>

        <div class="container-termica">

            <div class="conteudo-termica">
                <div class="invoice-head" style="margin-bottom: 0; margin-left:0">

                    <table class="tabela" style="font-size:11px; margin-bottom: 0; margin-left:0;margin-top:0;line-height: 1.5;">
                        <tbody>
                            <?php if ($emitente == null) { ?>

                                <tr>
                                    <td colspan="5" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>
                                        <<<< </td>
                                </tr> <?php } else { ?> <tr>

                                    <td colspan="5" style="text-align: center"> <span style="font-size: 16px;line-height: 1; ">
                                            <?php echo $emitente[0]->nome; ?></span> </br>
                                        <span style="font-size: 11px; line-height: 1; ">CNPJ: <?php echo $emitente[0]->cnpj; ?> </br>
                                            <?php echo $emitente[0]->rua . ', ' . $emitente[0]->numero . ' ' . $emitente[0]->bairro . ' -  ' . $emitente[0]->cidade . ' - ' . $emitente[0]->uf; ?> </span> </br> <span>Fone: <?php echo $emitente[0]->telefone; ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%; font-size: 12px;"><b>N° OS:</b> <span><?php echo $result->idOs ?></span><span style="padding-left: 5%;"><br /><b>Emissão: <?php echo date('d/m/Y') ?></span></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <table class="tabela">
                        <tbody>
                            <tr>
                                <td>
                                        <b>Cliente: </b>
                                        <?php echo $result->nomeCliente ?></span><br />
                                        <span><b>Celular:</b> <?php echo $result->celular_cliente ?></span>
                                        <b>
                                        <?php if ($result->status == 'Finalizado' || $result->status == 'Faturado') { ?><br />
                                        <span>V.G:</span>
                                        </b>
                                        <?php echo dateInterval($result->dataFinal, $result->garantia); ?><?php } ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>


                <table class="tabela" style="">
                    <tbody>
                        <tr>
                            <td>
                                <b>Status da O.S.: </b>
                                <?php echo $result->status ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="tabela" style="">
                    <tbody>

                        <?php if ($result->dataInicial != null) { ?>
                            <tr>

                                <td>
                                    <b>Inicial: </b>
                                    <?php echo date('d/m/Y', strtotime($result->dataInicial)); ?>
                                </td>

                                <td>
                                    <b>Final: </b>
                                    <?php echo $result->dataFinal ? date('d/m/Y', strtotime($result->dataFinal)) : ''; ?>
                                </td>

                                <td>
                                    <?php if ($result->garantia != null) { ?>
                                        <b>Garantia: </b>
                                        <?php echo $result->garantia . ' dias'; ?><?php } ?>
                                </td>


                            <?php } ?>
                            <?php if ($result->status == 'Aberto' || true) { ?>
                                <?php if ($result->descricaoProduto != null) { ?>
                            <tr>
                                <td colspan="5">
                                    <b>Descrição: </b>
                                    <?php echo htmlspecialchars_decode($result->descricaoProduto) ?>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php if ($result->defeito != null) { ?>
                            <tr>
                                <td colspan="5">
                                    <b>Defeito Apresentado: </b>
                                    <?php echo htmlspecialchars_decode($result->defeito) ?>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php if ($result->observacoes != null) { ?>
                            <tr>
                                <td colspan="5">
                                    <b>Observações: </b>
                                    <?php echo htmlspecialchars_decode($result->observacoes) ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <?php if ($result->status != 'Aberto') { ?>
                        <?php if ($result->laudoTecnico != null) { ?>
                            <tr>
                                <td colspan="5">
                                    <b>Laudo Técnico: </b>
                                    <?php echo htmlspecialchars_decode($result->laudoTecnico) ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>

                    </tbody>
                </table>
                <?php if ($produtos != null) { ?>
                    <br />
                    <table style='' class="tabela" id="tblProdutos">
                        <thead>
                            <tr>
                                <th>Q</th>
                                <th>Produto</th>
                                <th>P U</th>
                                <th>Sb T</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($produtos as $p) {
                                $totalProdutos = $totalProdutos + $p->subTotal;
                                echo '<tr>';
                                echo '<td>' . $p->quantidade . '</td>';
                                echo '<td>' . $p->descricao . '</td>';
                                echo '<td>R$ ' . $p->preco ?: $p->precoVenda . '</td>';

                                echo '<td>R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                                echo '</tr>';
                            } ?>

                            <tr>
                                <td colspan="3" style="text-align: right"><strong>Total produtos:</strong></td>
                                <td><strong>R$ <?php echo number_format($totalProdutos, 2, ',', '.'); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                <?php } ?>

                <?php if ($servicos != null) { ?>
                    <table style='' class="tabela">
                        <thead>
                            <tr>
                                <th>Q.</th>
                                <th>Serviço</th>
                                <th>PU</th>
                                <th>Sb T</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            setlocale(LC_MONETARY, 'pt_BR');
                            foreach ($servicos as $s) {
                                $preco = $s->preco ?: $s->precoVenda;
                                $subtotal = $preco * ($s->quantidade ?: 1);
                                $totalServico = $totalServico + $subtotal;
                                echo '<tr>';
                                echo '<td>' . ($s->quantidade ?: 1) . '</td>';
                                echo '<td>' . $s->nome . '</td>';
                                echo '<td>R$ ' . number_format($preco, 2, ',', '.') . '</td>';
                                echo '<td>R$ ' . number_format($subtotal, 2, ',', '.') . '</td>';
                                echo '</tr>';
                            } ?>
                            <tr>
                                <td colspan="3" style="text-align: right"><strong>Total serviços:</strong></td>
                                <td><strong>R$ <?php echo number_format($totalServico, 2, ',', '.'); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                <?php } ?>

                <table class="tabela" style="">
                    <tbody>
                        <tr>
                            <td colspan="1"> <?php
                                                if ($totalProdutos != 0 || $totalServico != 0) {
                                                    echo "<h4>À vista com desconto: R$" . number_format($totalProdutos + $totalServico, 2, ',', '.') . " <br /> C. Crédito até 5x total: R$" . number_format((($totalProdutos + $totalServico) * 1.14), 2, ',', '.') . "</h4>";;
                                                }
                                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                if ($result->status == 'Faturado') {
                                    //print_r($lancamentos[0]);die();
                                $lancamentos[0]->baixado == 0 ?$lancamentos[0]->baixado = "NÃO":$lancamentos[0]->baixado="SIM";
                            //echo $valorPago['valor'];
                           echo "Recebido: ".$lancamentos[0]->baixado;
                           if(isset($lancamentos[0]->data_pagamento) && $lancamentos[0]->baixado == "SIM"){
                               echo " em ".date_format(date_create($lancamentos[0]->data_pagamento),'d/m/Y');// echo number_format($valorPago, 2, ',', '.'); 
                           }
                            if($lancamentos[0]->baixado == 1)
                            echo "<b>Valor recebido: R$".$lancamentos[0]->valor." </b><br />";
                            }
                            ?>
                            
                            </td>
                                            </tr>

                        <tr>

                            <td colspan="1">
                                <p style='font-size: 11px; line-height: 1.3; text-align:justify '>
                                    Atenção, fica de responsabilidade do cliente realizar o pagamento e a retirada de equipamentos em até 1 mês a partir da
                                    comunicação de liberação na assistência, após esse período será cobrada taxa de estadia de dois reais ao dia, não ultrapassando o valor do reparo; após 3 MESES poderá ser realizado protesto em cartório e cadastro em restrição de crédito.
                                </p>
                                <p><br/><br />
                                            </p>
                        </tr>
                        <tr>
                        <p class="text-center" style='font-size: 11px; margin-top:3mm; margin-bottom:8mm'>Assinatura do Cliente</p><br />
                        <p>__________________________________</p>
                        </tr>
                        <tr class="text-center">
                        <?php if ($lancamentos[0]->valor > 0) {
                           echo "<p class='text-center'>ASSINATURA DIGITAL: </p>";
                           

                            $assinatura = new Assinador();

                            $assinatura->assinar($result->idOs, $lancamentos[0]->valor, $emitente[0]->cnpj);
                            echo "<p  class='text-center' style='font-size: 10px;'>".$assinatura->getAsssinatura()."</p>";
                            
                        }
                        ?>

                        </tr>
                    </tbody>
                </table>
                
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