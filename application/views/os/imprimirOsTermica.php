<?php $totalServico = 0;
$totalProdutos = 0; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Mcell OS</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-style.css" />
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="<?= base_url('assets/css/custom.css'); ?>" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href='' rel='stylesheet' type='text/css'>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
    @import url("https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap");
     @font-face {
        font-family: 'RobotoCondensed-Regular';
        src: url('http://localhost/mapos/assets/font-awesome/webfonts/RobotoCondensed-Regular.woff2') format('woff2'),
            url('http://localhost/mapos/assets/font-awesome/webfonts/RobotoCondensed-Regular.woff') format('woff'),
            url('http://localhost/mapos/assets/font-awesome/webfonts/RobotoCondensed-Regular.ttf') format('truetype');
    }
    body {
        font-family: 'RobotoCondensed-Regular', sans-serif;
    }
.table {
    width: 75mm;
    margin: auto;
}

td, th {
    color: black;
    font-size: 14px;
}
.test {
    font-weight: 400;
    text-align: center;
    font-size: 22px;
}
.test2 {
    font-weight: 700;
    letter-spacing: 0, 5px;
    font-size: 16px;
}
.test3 {
    font-weight: 700;
    font-size: 13px;
}
.test4 {
    font-size: 14px;
    letter-spacing: 0, 5px;
}
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">

            <table class="table table-condensed">
                <tbody>
                    <?php if ($emitente == null) { ?>
                        <tr>
                            <td colspan="3" class="alert">Você precisa configurar os dados do emitente. >>>
                                <a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>
                            </td>
                        </tr>

                        <?php } else { ?> <td style="width: 28%"><img src=" <?php echo $emitente[0]->url_logo; ?> ">
    <td> <span style="font-size: 15px;">
    <i class="far fa-hand-point-right" style="margin:0px 2px"></i>
    <?php echo $emitente[0]->nome; ?></span> </br>
    <span style="font-size: 14px; ">
    <span class="icon"><i class="fas fa-fingerprint" style="margin:0px 2px"></i>
    CNPJ: <?php echo $emitente[0]->cnpj; ?> </br>
    <span class="icon"><i class="fas fa-map-marker-alt" style="margin:0px 4px"></i>
    <?php echo $emitente[0]->rua . ', ' . $emitente[0]->cidade . ' - ' . $emitente[0]->uf; ?>
</span> </br>
     <span class="icon">
         <i class="fab fa-whatsapp" style="margin:0px 3px"></i>Fone: <?php echo $emitente[0]->telefone; ?> </br></br>
                    </tbody>
                </table>
            </div>
            <?php } ?>

 <h5><div class="test"><b>ORDEM DE SERVIÇO</b></h5>
</div>
 <table class="table table-condensed">
     <tr>
         <td class="test4"><span><b>N° OS:</b>
         <?php echo $result->idOs ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>EMISSÃO:</b> <?php echo date('d/m/Y') ?>
        </span><br>
    </td>
</tr>
<table class="table table-condensed">
    <tr>
        <td colspan="5">
            <h5><div class="test2"><i class="fas fa-user"></i>&nbsp;CLIENTE</h5>
            <span><b>NOME:</b> </span><?php echo $result->nomeCliente ?></span><br>
            <span><b>END.:</b> <?php echo $result->rua ?>, <?php echo $result->numero ?>,
            <span><?php echo $result->cidade ?> - <?php echo $result->estado ?></span><br>
            <span><b>FONE:</b> <?php echo $result->celular_cliente ?></span>
        </td>
    </tr>
    <table class="table table-condensed">
        <tr>
            <td colspan="5">
                <h5>
                    <div class="test2"><i class="fas fa-mobile-alt"></i> APARELHO/SERVIÇO</h5>
                    <span><b>ENTRADA:</b>
                     <?php echo date('d/m/y', strtotime($result->dataInicial)); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                     <span><b>SAÍDA:</b>
                     <?php echo $result->dataFinal ? date('d/m/y', strtotime($result->dataFinal)) : ''; ?></span><br>
                     <span><b>MARCA/MODELO:</b>
                     <?php echo htmlspecialchars_decode($result->descricaoProduto) ?></span><br>
                     <span><b>DEFEITO/RECL.:</b>
                     <?php echo htmlspecialchars_decode($result->defeito) ?></span></br>
                     <span><b>ACESSÓRIOS/OBS.:</b>
                     <?php echo htmlspecialchars_decode($result->observacoes) ?></span></br>
                     <span><b>TÉCNICO RESP.:</b> <?php echo ($result->nome) ?></span><br>
                     <span><b>LAUDO TÉCNICO:</b>
                     <?php echo htmlspecialchars_decode($result->laudoTecnico) ?></span></br>
                     <span><b>STATUS:</b> <?php echo $result->status ?>&nbsp;&nbsp;&nbsp;&nbsp;
                     <span><b>GARANTIA:</b> <?php echo $result->garantia . ' dias'; ?></span><br>
                    </td>
                </tr>
                <tr>
                    <td class="test3">
                        Aprovado: S: (&nbsp;&nbsp;) N: (&nbsp;&nbsp;)&nbsp;&nbsp;&nbsp;
                        Pago: S: (&nbsp;&nbsp;) N: (&nbsp;&nbsp;)<br>
                        Testes START: OK: (&nbsp;&nbsp;)&nbsp;&nbsp;&nbsp;
                        Testes END: OK: (&nbsp;&nbsp;)
                    </td>
                </tr>
                <table class="table table-condensed">
                            <tr>
                                <td colspan="5">
                                    <h5>
                                        <div class="test2">
                                            <i class="fas fa-qrcode"></i> VALORES PRODUTOS/SERVIÇOS
                                    </h5>
                                </td>
                            </tr>
                            <?php if ($produtos != null) { ?>
                            <table class="table table-bordered table-condensed" id="tblProdutos">
                                <thead>
                                    <tr>
                                        <th>Qtd</th>
                                        <th>Produtos</th>
                                        <th>V. Unt</th>
                                        <th>S. Total</th>
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
                                        <td colspan="5" style="text-align: right"><strong>Total:</strong>
                                            <strong>R$
                                                <?php echo number_format($totalProdutos, 2, ',', '.'); ?></strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <?php } ?>

                            <?php if ($servicos != null) { ?>
                            <table class="table table-bordered table-condensed">
                                <thead>
                                    <tr>
                                        <th>Qtd</th>
                                        <th>Serviços</th>
                                        <th>V. Unt</th>
                                        <th>S. Total</th>
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
                                        echo '<td>' . ($s->quantidade ?: 1) . '</td>';
                                        echo '<td>' . $s->nome . '</td>';
                                        echo '<td>R$ ' . $preco . '</td>';
                                        echo '<td>R$ ' . number_format($subtotal, 2, ',', '.') . '</td>';
                                        echo '</tr>';
                                    } ?>
                                    <tr>
                                        <td colspan="5" style="text-align: right"><strong>Total:</strong>
                                            <strong>R$ <?php echo number_format($totalServico, 2, ',', '.'); ?></strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <?php } ?>
                            <table class="table table-bordered table-condensed">
                                <tbody>
                                    <tr>
                                        <td class="test2"> <?php
                      if ($totalProdutos != 0 || $totalServico != 0) {
                          echo "<h4 style='text-align: right;'>Valor Total: R$" . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>";
                          } ?>
                          </td>
                        </tr>
                    </tbody>
                </table>

                <table class="table table-condensed">
                    <tbody>
                        <tr>
                            <td colspan="5"><img src="<?php echo base_url() ?>assets/img/Padrão.webp" alt="imagem não encontrada" style="margin:4px 10px; width: 49px; height: 52px">
                            <td> <span style="font-size: 10px">
                            <p class="text-left">PIN: ____________________ <br>SENHA: _______________________________ <br>IMEI: __________________________________</p></span><br><br>
                        </td>
                    </tr>
                </tbody>
            </table>
            <tr>
                <td> <span style="font-size: 11px;"> <p class="text-center"> ________________________________________________________</p>
            </td>
            <tbody>
                <tr>
                    <td> <span style="font-size: 11px;"><p class="text-center">Ass. Carimbo da Assistência Técnica</p><br>
                    <tr>
                        <td> <span style="font-size: 11px;"><p class="text-center"> ________________________________________________________</p>
                        <tbody>
                            <tr>
                                <td> <span style="font-size: 11px;"><p class="text-center">Assinatura do Cliente</p></span><br>
                                <td> <span style="font-size: 11px;"><p class="text-center">VIA M-CELL</p></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

<script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/matrix.js"></script>
<script> window.print();</script>

</body>
</html>
