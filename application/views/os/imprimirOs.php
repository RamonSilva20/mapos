<?php $totalServico = 0;
$totalProdutos = 0; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Map_OS_<?php echo $result->idOs ?>_<?php echo $result->nomeCliente ?></title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-style.css" />
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="<?= base_url('assets/css/custom.css'); ?>" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
    <style>
        .table {
            margin-bottom: 5px;
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">

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
                                                    <span>Celular: <?php echo $result->celular_cliente ?></span>
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
                                                <span>Telefone: <?php echo $result->telefone_usuario ?></span><br />
                                                <span>Email: <?php echo $result->email_responsavel ?></span>
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
                                    </tr>
                                <?php } ?>

                                <?php if ($result->descricaoProduto != null) { ?>
                                    <tr>
                                        <td colspan="5">
                                            <b>DESCRIÇÃO: </b>
                                            <?php echo htmlspecialchars_decode($result->descricaoProduto) ?>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if ($result->defeito != null) { ?>
                                    <tr>
                                        <td colspan="5">
                                            <b>DEFEITO APRESENTADO: </b>
                                            <?php echo htmlspecialchars_decode($result->defeito) ?>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if ($result->observacoes != null) { ?>
                                    <tr>
                                        <td colspan="5">
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

                        <table class="table table-bordered table-condensed">
                            <tbody>
                                <tr>
                                    <td>Data
                                        <hr>
                                    </td>
                                    <td>Assinatura do Cliente
                                        <hr>
                                    </td>
                                    <td>Assinatura do Técnico Responsável
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
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1252">
<meta name=Generator content="Microsoft Word 14 (filtered)">
<style>
<!--
 /* Font Definitions */
 @font-face
    {font-family:Wingdings;
    panose-1:5 0 0 0 0 0 0 0 0 0;}
@font-face
    {font-family:Wingdings;
    panose-1:5 0 0 0 0 0 0 0 0 0;}
@font-face
    {font-family:Calibri;
    panose-1:2 15 5 2 2 2 4 3 2 4;}
@font-face
    {font-family:Tahoma;
    panose-1:2 11 6 4 3 5 4 4 2 4;}
 /* Style Definitions */
 p.MsoNormal, li.MsoNormal, div.MsoNormal
    {margin-top:0cm;
    margin-right:0cm;
    margin-bottom:10.0pt;
    margin-left:0cm;
    line-height:115%;
    font-size:11.0pt;
    font-family:"Calibri","sans-serif";}
a:link, span.MsoHyperlink
    {color:blue;
    text-decoration:underline;}
a:visited, span.MsoHyperlinkFollowed
    {color:purple;
    text-decoration:underline;}
p.MsoAcetate, li.MsoAcetate, div.MsoAcetate
    {mso-style-link:"Texto de balão Char";
    margin:0cm;
    margin-bottom:.0001pt;
    font-size:8.0pt;
    font-family:"Tahoma","sans-serif";}
p.MsoNoSpacing, li.MsoNoSpacing, div.MsoNoSpacing
    {margin:0cm;
    margin-bottom:.0001pt;
    font-size:11.0pt;
    font-family:"Calibri","sans-serif";}
span.TextodebaloChar
    {mso-style-name:"Texto de balão Char";
    mso-style-link:"Texto de balão";
    font-family:"Tahoma","sans-serif";}
@page WordSection1
    {size:595.3pt 841.9pt;
    margin:28.4pt 28.3pt 14.2pt 1.0cm;}
div.WordSection1
    {page:WordSection1;}
 /* List Definitions */
 ol
    {margin-bottom:0cm;}
ul
    {margin-bottom:0cm;}
.cores {
    color: #00F;
}
.fonte {
    font-size: 12pt;
}
.valor {
    font-size: 24px;
}
-->
body {
    -webkit-print-color-adjust: exact;
}
</style>

</head>

<body lang=PT-BR link=blue vlink=purple>

<div class=WordSection1>

  <table class=MsoNormalTable border=1 cellspacing=0 cellpadding=0 width=831
 style='width:551.1pt;border-collapse:collapse'>
    <tr style='height:48.95pt'> 
      <td width=139 rowspan=2 valign=top style='width:104.4pt;border:none;
  border-bottom:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;height:48.95pt'> 
        <p class=MsoNoSpacing align=center style='text-align:center'><b><span
  style='font-size:12.0pt'> <img src="http://chart.apis.google.com/chart?chs=200x200&cht=qr&chld=|0&chl=https://loja.tectonny.com.br/FERYESCLWH" width="100" height="100"><br>
        Consulte online!</span></b></p></td>
      <td colspan=4 valign=top style='width:312.1pt;border:none;
  border-bottom:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;height:48.95pt'> 
        <p class=MsoNormal align=center style='margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  normal'><b><span style='font-size:16.0pt'>TECTONNY ELETRONICA E INFORMÁTICA</span></b></p>
        <p class=MsoNormal align=center style='margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  normal'>Est Governador Leonel Brizola, 651  Palhada – Nova Iguaçu- RJ</p>
        <p class=MsoNormal align=center style='margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  normal'><b>Posto autorizado , Master Áudio, Navcity e Century.</b></p>
        <p class=MsoNormal align=center style='margin-bottom:0cm;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><strong><font color="#000000" size="4">Tel: 
          ou Whatsapp: 21-98344-3304</font></strong></p>
        <p class=MsoNormal align=center style='margin-bottom:0cm;margin-bottom:.0001pt;
  text-align:center;line-height:normal'>CNPJ: 11.523.004/0001-70</p></td>
      <td colspan=2 style='width: 134.6pt; border: none; border-bottom: solid windowtext 1.0pt; padding: 0cm 5.4pt 0cm 5.4pt; height: 48.95pt; color: #000;'> 
        <p class=MsoNormal align=center style='margin-bottom: 0cm; margin-bottom: .0001pt; text-align: center; line-height: normal;'>CHAVE WEB:</p>
        <p class=MsoNormal align=center style='margin-bottom: 0cm; margin-bottom: .0001pt; text-align: center; line-height: normal;'><strong><em>
          FERYESCLWH
        </em></strong></p>
        <p class=MsoNormal align=center style='margin-bottom: 0cm; margin-bottom: .0001pt; text-align: center; line-height: normal;'>SENHA:<strong> <em>
          CSTHTK
        </em></strong></p>
        <p class=MsoNormal align=center style='margin-bottom: 0cm; margin-bottom: .0001pt; text-align: center; line-height: normal;'>Acompanhe 
          no site:<strong><br>
        loja.tectonny.com.br</strong></p></td>
    </tr>
    <tr> 
      <td colspan=3 valign=top style='width:306.0pt;border:none;
  border-bottom:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'> <p class=MsoNormal align=center style='margin-bottom:0cm;margin-bottom:.0001pt;
  text-align:center;line-height:normal'><b><span style='font-size:18.0pt'><strong>GARANTIA</strong> - <span>OS: 200714</span></span></b></p></td>
      <td colspan=3 valign=top style='width:140.7pt;border:none;
  border-bottom:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt'> <p class=MsoNormal style='margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  normal'><b>Data:</b> 14/07/2020 15:38:11</p></td>
    </tr>
    <tr> 
      <td colspan=3 valign=top style='width:346.45pt;border:none;
  padding:0cm 5.4pt 0cm 5.4pt'> <p class=MsoNormal style='margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  normal'><b>Cliente: </b>ELIAS GOMES DE PAULA</p>
        <p class=MsoNormal style='margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  normal'><b>Endereço</b>: RUA ENEIMA, nº 43</p></td>
      <td colspan=4 valign=top style='width:204.65pt;border:none;
  padding:0cm 5.4pt 0cm 5.4pt'> <p class=MsoNormal style='margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  normal'><strong>Celular:</strong> (21)99549-9317</p>
        <p class=MsoNormal style='margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  normal'><strong>Telefone:</strong> </p></td>
    </tr>
    <tr style='height:15.1pt'> 
      <td colspan=2 valign=top style='width:176.6pt;padding:
  0cm 5.4pt 0cm 5.4pt;height:15.1pt'> <p class=MsoNormal style='margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  normal'><b>Aparelho:</b> NOTEBOOK</p></td>
      <td width=226 valign=top style='width:169.85pt;padding:0cm 5.4pt 0cm 5.4pt;
  height:15.1pt'> <p class=MsoNormal style='margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  normal'><b>Marca:</b> DELL</p></td>
      <td colspan=4 valign=top style='width:204.65pt;padding:0cm 5.4pt 0cm 5.4pt;
  height:15.1pt'> <p class=MsoNormal style='margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  normal'><b>Modelo:</b> GYHC2G</p></td>
    </tr>
    <tr style='height:15.05pt'>
      <td colspan=2 valign=top style='width:176.6pt;padding:
  0cm 5.4pt 0cm 5.4pt;height:15.1pt'><p class=MsoNormal style='margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  normal'><b>Série:</b> GYHC2G</p></td> 
      <td colspan=4 valign=top style='width:261.15pt;padding:0cm 5.4pt 0cm 5.4pt;
  height:15.05pt'><b>Acessórios:</b> COM CARREGADOR </p></td>
      <td width=151 valign=top style='width:113.35pt;padding:0cm 5.4pt 0cm 5.4pt;
  height:15.05pt'> <p class=MsoNormal style='margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  normal'><b>Cor:</b> <ORDEMS.COR></p></td>
    </tr>
    <tr style='height:35.95pt'> 
      <td colspan=7 valign=top style='width:551.1pt;padding:0cm 5.4pt 0cm 5.4pt;
  height:35.95pt'> <p class=MsoNormal style='margin-bottom:0cm;margin-bottom:.0001pt;line-height:
  normal'><b>Serviço Reclamado:</b><br>NÃO INICIA OS
        <br> <br>
        <strong>Serviço Executado</strong>: </p>
      <p>DEFEITO NO HD QUE TEVE SUA PARTIÇÃO 0 DELETADA, RESTAURAR E FORMATAR (TODOS OS DADOS FORAM PERDIDOS)  </p></td>
    </tr>
    <tr style='height: 35.95pt; font-size: 18px;'> 
      <td colspan=7 style="text-align: right"><strong>Garantia até: 26/10/2020 11:58:12 
        </strong> <span class="valor"> | Valor Total: R$ 60,00</span></td>
    </tr>
    <tr style='height:110.95pt'> 
      <td colspan=7 valign=top style='width: 551.1pt; padding: 0cm 5.4pt 0cm 5.4pt; height: 110.95pt; font-size: 12px;'> 
        <p><strong>SOBRE A GARANTIA DOS SERVIÇOS:</strong><br>
          1- A Empresa garante seus serviços por um período de 90 dias contados 
          a partir da data de retirada do aparelho.<br>
          2- A garantia cobre <strong>somente a mão de obra e peças usadas no 
          conserto do aparelho</strong>. <br>
          3- A garantia não cobre frete do aparelho, sendo necessário o frete 
          será cobrado a parte <br>
          4- Essa garantia ficará automaticamente cancelada se os aparelhos vierem 
          sofrer reparos por pessoas não autorizadas, receber maus tratos ou sofrer 
          danos decorrentes de acidentes, quedas, variações de tensão elétrica 
          e sobrecarga acima do especificado. </p>
        <strong>Observações Importantes:<br></strong>  </td>
    </tr>
    <tr height=0> 
      <td width=152 style='border:none'></td>
      <td width=118 style='border:none'></td>
      <td width=226 style='border:none'></td>
      <td width=78 style='border:none'></td>
      <td width=78 style='border:none'></td>
      <td width=28 style='border:none'></td>
      <td width=151 style='border:none'></td>
    </tr>
    <tr style='height:110.95pt'> 
      <td colspan=7 valign=top style='padding: 0cm 5.4pt 0cm 5.4pt; height: 110.95pt; font-size: 12px;'> 
        <p><strong>Veja como funciona  a garantia:</strong></p>
<ul>
  <li>A garantia é de 90 dias (3 meses);</li>
  <li>A garantia cobre a mão de obra e somente a peça  trocada;</li>
  <li>A garantia não cobre peças que não foram  trocadas;</li>
  <li>A garantia não cobre frete para retirar o  aparelho, caso queira taxa de R$20,00;</li>
  <li>Em caso de queima de peça que não foi troca, a  mesma será cobrada;</li>
</ul>
<p><strong>Veja como funciona  o prazo de entrega:</strong></p>
<ul>
  <li>Tempo de entrega com peça no estoque é de 48hs</li>
  <li>Tempo de entrega comprando peça no RJ é de 72hs</li>
  <li>Tempo de entrega comprando peça fora do RJ 7  dias</li>
  <li>Tempo de entrega com peça em falta é  indeterminado, tendo que o cliente retirar o aparelho e aguardar a chegada da  peça em casa.</li>
</ul>
<p><strong>Como funciona a  Visita técnica?</strong></p>
<ul>
  <li>Não é cobrado para primeira visita de retirada  de aparelho;</li>
  <li>Não tem como marca hora exata, apenas o dia;</li>
  <li>Visita para instalação e configuração de  aparelhos é cobrada uma taxa de R$30,00;</li>
  <li>Visitas para retirada do aparelho na garantia é  cobrada R$20,00 se estiver com defeito e R$30,00 se não estiver com defeito e a  solução for feita no local.</li>
  <li>Não é cobrada para entrega de aparelhos  aprovados e prontos.</li>
  <li>Caso de entrega de aparelhos reprovados ou  devolvido por falta de peça é cobrado uma taxa de R$20,00</li>
</ul>
<p class=MsoNoSpacing align=center style='text-align:center'><strong>Consulte seu orçamento online entre em loja.tectonny.com.br</strong></p>

      </td>
    </tr>
  </table>

</body>
</html>



    <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/matrix.js"></script>

    <script>
        window.print();
    </script>

</body>

</html>
