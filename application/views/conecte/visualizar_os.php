<?php $totalServico = 0;
$totalProdutos = 0; ?>
<div class="row-fluid" style="margin-top: 0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-diagnoses"></i>
                </span>
                <h5>Ordem de Serviço</h5>
                <div class="buttons" style=" padding-left:5px;">
                    <a target="_blank" title="Imprimir Relatório" class="button btn btn-mini btn-inverse" href="<?php echo site_url() ?>/mine/imprimirOs/<?php echo $result->idOs; ?>">
                        <span class="button__icon"><i class="bx bx-printer"></i></span> <span class="button__text">Imprimir Relatório</span></a>
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
                                <?php } else { ?>
                                    <tr>
                                        <td style="width: 25%"><img src=" <?php echo $emitente->url_logo; ?> " style="max-height: 100px"></td>
                                        <td>
                                            <span style="font-size: 20px;"><?php echo $emitente->nome; ?></span></br>
                                            <?php if($emitente->cnpj != "00.000.000/0000-00") { ?><span class="icon"><i class="fas fa-fingerprint" style="margin:5px 1px"></i> <?php echo $emitente->cnpj; ?></span></br><?php } ?>
                                            <span class="icon"><i class="fas fa-map-marker-alt" style="margin:4px 3px"></i> <?php echo $emitente->rua . ', ' . $emitente->numero . ', ' . $emitente->bairro . ' - ' . $emitente->cidade . ' - ' . $emitente->uf; ?></span></br>
                                            <span class="icon"><i class="fas fa-comments" style="margin:5px 1px"></i> E-mail: <?php echo $emitente->email . ' - Fone: ' . $emitente->telefone; ?></span></br>
                                            <span class="icon"><i class="fas fa-user-check"></i> Responsável: <?php echo $result->nome ?>
                                        </td>
                                        <td style="width: 18%; text-align: center">
                                            <span><b>N° OS: </b><?php echo $result->idOs ?></span></br></br>
                                            <span>Emissão: <?php echo date('d/m/Y') ?></span>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="margin-top: 0; padding-top: 0">
                        <table class="table table-condensed">
                            <tbody>
                                    <?php if ($result->dataInicial != null) { ?>
                                        <tr>
                                            <td>
                                                <b>STATUS OS: </b><?php echo $result->status ?>
                                            </td>

                                            <td>
                                                <b>DATA INICIAL: </b><?php echo date('d/m/Y', strtotime($result->dataInicial)); ?>
                                            </td>

                                            <td>
                                                <b>DATA FINAL: </b><?php echo $result->dataFinal ? date('d/m/Y', strtotime($result->dataFinal)) : ''; ?>
                                            </td>

                                            <td>
                                                <?php if (!empty ($result->garantia)) { ?>
                                                    <b>GARANTIA: </b><?php echo $result->garantia . ' dia(s)'; ?>
                                                <?php } ?>
                                            </td>

                                            <td>
                                                <b><?php if ($result->status == 'Finalizado') { ?> VENC. DA GARANTIA: </b><?php echo dateInterval($result->dataFinal, $result->garantia); ?><?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                            </tbody>
                        </table>

                        <table class="table table-condensed">
                            <?php if ($result->descricaoProduto != null || $result->defeito != null || $result->laudoTecnico != null || $result->observacoes) { ?>
                                    <?php if ($result->descricaoProduto != null) { ?>
                                        <tr>
                                            <td>
                                                <strong>DESCRIÇÃO: </strong><br>
                                                <?php echo htmlspecialchars_decode($result->descricaoProduto) ?>
                                            </td>
                                        </tr>

                                    <?php } ?>

                                    <?php if ($result->defeito != null) { ?>
                                        <tr>
                                            <td>
                                                <strong>DEFEITO APRESENTADO: </strong><br>
                                                <?php echo htmlspecialchars_decode($result->defeito) ?>
                                            </td>
                                        </tr>
                                    <?php } ?>

                                    <?php if ($result->observacoes != null) { ?>
                                        <tr>
                                            <td>
                                                <strong>OBSERVAÇÕES: </strong><br>
                                                <?php echo htmlspecialchars_decode($result->observacoes) ?>
                                            </td>
                                        </tr>
                                    <?php } ?>

                                    <?php if ($result->laudoTecnico != null) { ?>
                                        <tr>
                                            <td>
                                                <strong>LAUDO TÉCNICO: </strong><br>
                                                <?php echo htmlspecialchars_decode($result->laudoTecnico) ?>
                                            </td>
                                        </tr>
                                    <?php } ?>

                                    <?php if ($result->garantias_id != null) { ?>
                                        <tr>
                                            <td>
                                                <strong>TERMO DE GARANTIA </strong><br>
                                                <?php echo htmlspecialchars_decode($result->textoGarantia) ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                            <?php } ?>
                        </table>
                        
                        <?php if ($anexos != null) { ?>
                            <table class="table table-bordered table-condensed">
                                <thead>
                                    <tr>
                                        <th>Anexo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <th>
                                    <?php foreach ($anexos as $a) {
                                        if ($a->thumb == null) {
                                            $thumb = base_url() . 'assets/img/icon-file.png';
                                            $link = base_url() . 'assets/img/icon-file.png';
                                        } else {
                                            $thumb = $a->url . '/thumbs/' . $a->thumb;
                                            $link = $a->url . '/' . $a->anexo;
                                        }
                                        echo '<div class="span3" style="min-height: 150px; margin-left: 0"><a style="min-height: 150px;" href="#modal-anexo" imagem="' . $a->idAnexos . '" link="' . $link . '" role="button" class="btn anexo span12" data-toggle="modal"><img src="' . $thumb . '" alt=""></a></div>';
                                    } ?>
                                    </th>
                                </tbody>
                            </table>
                        <?php } ?>

                        <?php $totalServico = 0; $totalProdutos = 0; ?>
                        <?php if ($produtos != null) { ?>
                            <br />
                            <table class="table table-bordered table-condensed" id="tblProdutos">
                                <thead>
                                    <tr>
                                        <th>PRODUTO</th>
                                        <th>QTD</th>
                                        <th>UNT</th>
                                        <th>SUBTOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produtos as $p) {
                                        $totalProdutos = $totalProdutos + $p->subTotal;
                                        echo '<tr>';
                                        echo '<td>' . $p->descricao . '</td>';
                                        echo '<td>' . $p->quantidade . '</td>';
                                        echo '<td>R$ ' . $p->preco ?: $p->precoVenda . '</td>';
                                        echo '<td>R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                                        echo '</tr>';
                                    } ?>
                                    <tr>
                                        <td></td>
                                        <td colspan="2" style="text-align: right"><strong>TOTAL:</strong></td>
                                        <td><strong>R$ <?php echo number_format($totalProdutos, 2, ',', '.'); ?></strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php } ?>

                        <?php if ($servicos != null) { ?>
                            <table class="table table-bordered table-condensed">
                                <thead>
                                    <tr>
                                        <th>SERVIÇO</th>
                                        <th>QTD</th>
                                        <th>UNT</th>
                                        <th>SUBTOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php setlocale(LC_MONETARY, 'en_US'); foreach ($servicos as $s) {
                                        $totalServico = $totalServico + $s->subTotal;
                                        $preco = $s->preco ?: $s->precoVenda;
                                        $subtotal = $preco * ($s->quantidade ?: 1);
                                        echo '<tr>';
                                        echo '<td>' . $s->nome . '</td>';
                                        echo '<td>' . ($s->quantidade ?: 1) . '</td>';
                                        echo '<td>R$ ' . $preco . '</td>';
                                        echo '<td>R$ ' . number_format($subtotal, 2, ',', '.') . '</td>';
                                        echo '</tr>';
                                    } ?>
                                    <tr>
                                        <td colspan="3" style="text-align: right"><strong>TOTAL:</strong></td>
                                        <td><strong>R$ <?php echo number_format($totalServico, 2, ',', '.'); ?></strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php } ?>

                        <table class="table table-condensed">
                            <thead>
                                <td>
                                    <?php if ($totalProdutos != 0 || $totalServico != 0) {
                                        if ($result->valor_desconto != 0) {
                                            echo "<h4 style='text-align: right'>SUBTOTAL: R$ " . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>";
                                            echo $result->valor_desconto != 0 ? "<h4 style='text-align: right'>DESCONTO: R$ " . number_format($result->valor_desconto != 0 ? $result->valor_desconto - ($totalProdutos + $totalServico) : 0.00, 2, ',', '.') . "</h4>" : "";
                                            echo "<h4 style='text-align: right'>TOTAL: R$ " . number_format($result->valor_desconto, 2, ',', '.') . "</h4>";
                                        } else { echo "<h4 style='text-align: right'>TOTAL: R$ " . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>"; }
                                    }?>
                                </td>

                                <?php if ($result->status == 'Finalizado' || $result->status == 'Aprovado') { ?>
                                    <?php if ($qrCode) : ?>
                                        <td style="width: 15%; padding-left: 0; text-align:center;">
                                            <img style="margin:0px" src="<?php echo base_url(); ?>assets/img/logo_pix.png" width="48px" alt="QR Code de Pagamento" /></br>
                                            <img style="margin:6px 0px 0px 0px" width="94px" src="<?= $qrCode ?>" alt="QR Code de Pagamento" /></br>
                                            <?php echo '<span style="margin:0px;font-size: 80%;text-align:center;">Chave PIX: ' . $chaveFormatada . '</span>';?>
                                        </td>
                                    <?php endif ?>
                                <?php } ?>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inicio Modal visualizar anexo -->
<div id="modal-anexo" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Visualizar Anexo</h3>
    </div>
    <div class="modal-body">
        <div class="span12" id="div-visualizar-anexo" style="text-align: center">
            <div class='progress progress-info progress-striped active'>
                <div class='bar' style='width: 100%'></div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
        <a href="" id-imagem="" class="btn btn-inverse" id="download">Download</a>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', '.anexo', function(event) {
            event.preventDefault();
            var link = $(this).attr('link');
            var id = $(this).attr('imagem');
            var url = '<?php echo base_url(); ?>index.php/os/excluirAnexo/';
            $("#div-visualizar-anexo").html('<img src="' + link + '" alt="">');
            $("#download").attr('href', "<?php echo base_url(); ?>index.php/os/downloadanexo/" + id);
        });
    });
</script>
<!-- Fim Modal visualizar anexo -->

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
