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
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400&display=swap" rel="stylesheet">
    <style>

  @font-face {
  font-family: 'RobotoCondensed-Regular';
  src: url('http://localhost/mapos/assets/font-awesome/webfonts/RobotoCondensed-Regular.woff2') format('woff2'),
       url('http://localhost/mapos/assets/font-awesome/webfonts/RobotoCondensed-Regular.woff') format('woff'),
       url('http://localhost/mapos/assets/font-awesome/webfonts/RobotoCondensed-Regular.ttf')  format('truetype');
}
body 
{
  font-family: 'RobotoCondensed-Regular', sans-serif;
}
.table
     {
        width: 85mm;
      margin: auto;
        }

td, th { 
        background-color:white;
        color text:black;
        font-size:14px;
    }
    
.test {
    text-align:center;
    font-size:19px;
        }
.test2 {
    letter-spacing:0,5px;
    font-size:17px;
        }

        .test3 {
    font-size:13px;
    text-align:center;
   
        }
.test4{
    font-size:13px;
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
 <?php } else { ?> <td style="width: 26%"><img src=" <?php echo $emitente[0]->url_logo; ?> ">

         <td> <span style="font-size: 19px;">
           <?php echo $emitente[0]->nome; ?></span> </br>
            <span style="font-size: 16px; ">
             <span class="icon">
              <i class="fas fa-fingerprint" style="margin:5px 1px"></i>
           CNPJ: <?php echo $emitente[0]->cnpj; ?> </br>
           <span class="icon">
          <i class="fas fa-map-marker-alt" style="margin:4px 3px"></i>
             <?php echo $emitente[0]->rua . ', ' . $emitente[0]->cidade . ' - ' . $emitente[0]->uf; ?>
              </span> </br> <span>
            <span class="icon">
              <i class="fas fa-comments" style="margin:5px 1px"></i>
            Telefone: <?php echo $emitente[0]->telefone; ?>
               </tbody>
            </table>
            </div>
        <?php } ?>
 
        <table class="table table-bordered table-condensed">
        <tr>
      <td class="test4">                           
      <span><b>VENDA:</b> <?php echo $result->idVendas ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>EMISSÃO:</b> <?php echo date('d/m/Y', strtotime($result->data_vencimento)); ?></span><br>
             <h5>
             <div class="test">
             <b>NOTA FISCAL VENDA A CONSUMIDOR</b></h5></table>  
      
            <table class="table table-bordered table-condensed">
               <td colspan="5">
                                    <b>CLIENTE:</b>
                                    <span><?php echo $result->nomeCliente ?></span><br/>
                                            
                                    <b>ENDEREÇO:</b>        
                                    <span><?php echo $result->rua ?>,
                                                        <?php echo $result->numero ?>,
                                                        <?php echo $result->bairro ?>
                                                    
                                    <?php echo $result->cidade ?> -
                                                        <?php echo $result->estado ?></span><br/>
                                                        <span>
                                    <b>OBSERVAÇÕES:</b>
                                    <span><?php echo htmlspecialchars_decode($result->observacoes) ?>
                                                    </span>
                                            </li>
                                        </ul>
                                     </td>
                                </tr>
                                <table class="table table-bordered table-condensed">
      <tr>
      <td colspan="5">                                
      <h5><div class="test2">
      VALORES PRODUTOS/SERVIÇOS</h5></td> </tr>
              <?php if ($produtos != null) { ?>
                 <table class="table table-bordered table-condensed" id="tblProdutos">
                      <thead>
                        <tr>            
                                        <th>Qtd.</th>
                                        <th>Produtos</th>
                                        <th>V. Unt.</th>
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
                                        <strong>R$ <?php echo number_format($totalProdutos, 2, ',', '.'); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php } ?>                   

         <table class="table table-bordered table-condensed">
             <tbody>
                 <tr>
                   <td colspan="5"> <?php
                      if ($totalProdutos != 0 || $totalServico != 0) {
                         echo "<h4 style='text-align: right; font-size: 18px; '>Valor Total: R$" . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>";                                                       }
                        ?>
                               </td>
                                </tr>
                            </tbody>
                        </table>
                         <table class="table table-bordered table-condensed">
                    <tbody>
                <tr>
   <?php if ($qrCode): ?>
             <td style="width: 15%; padding-left: 0">
                   <img style= "margin:0px 0px; width: 290px; height: 150px" src="<?= $qrCode ?>" alt="QR Code de Pagamento" />
                       </td>
                </tr>
          <?php endif ?>
                <table class="table table-condensed">
        <tbody>
     <tr>                  
             <tbody>
             <tr>

<td> <span style="font-size: 13px;">
    <p class="text-center">OBRIGADO PELA PREFERÊNCIA!
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
