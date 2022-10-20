<link href="<?= base_url('assets/css/custom.css'); ?>" rel="stylesheet">
<div class="row-fluid" style="margin-top: 0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-diagnoses"></i>
                </span>
                <h5>Dados da Ordem de Serviço</h5>
                <div class="buttons">
                    <?php if ($editavel) {
    echo '<a title="Editar OS" class="button btn btn-mini btn-success" href="' . base_url() . 'index.php/os/editar/' . $result->idOs . '">
    <span class="button__icon"><i class="bx bx-edit"></i> </span> <span class="button__text">Editar</span></a>';
} ?>

                    <a target="_blank" title="Imprimir OS" class="button btn btn-mini btn-inverse" href="<?php echo site_url() ?>/os/imprimir/<?php echo $result->idOs; ?>">
                        <span class="button__icon"><i class="bx bx-printer"></i></span> <span class="button__text">Papel A4</span></a>
                    <a target="_blank" title="Imprimir OS" class="button btn btn-mini btn-inverse" href="<?php echo site_url() ?>/os/imprimirTermica/<?php echo $result->idOs; ?>">
                        <span class="button__icon"><i class="bx bx-printer"></i></span> <span class="button__text">CP Não Fiscal</span></a>
                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
    $this->load->model('os_model');
    $zapnumber = preg_replace("/[^0-9]/", "", $result->celular_cliente);
    $troca = [$result->nomeCliente, $result->idOs, $result->status, 'R$ ' . ($result->desconto != 0 && $result->valor_desconto != 0 ? number_format($result->valor_desconto, 2, ',', '.') : number_format($totalProdutos + $totalServico, 2, ',', '.')), strip_tags($result->descricaoProduto), ($emitente ? $emitente[0]->nome : ''), ($emitente ? $emitente[0]->telefone : ''), strip_tags($result->observacoes), strip_tags($result->defeito), strip_tags($result->laudoTecnico), date('d/m/Y', strtotime($result->dataFinal)), date('d/m/Y', strtotime($result->dataInicial)), $result->garantia . ' dias'];
    $texto_de_notificacao = $this->os_model->criarTextoWhats($texto_de_notificacao, $troca);
    if (!empty($zapnumber)) {
        echo '<a title="Enviar Por WhatsApp" class="button btn btn-mini btn-success" id="enviarWhatsApp" target="_blank" href="https://wa.me/send?phone=55' . $zapnumber . '&text=' . $texto_de_notificacao . '">
        <span class="button__icon"><i class="bx bxl-whatsapp"></i></span> <span class="button__text">WhatsApp</span></a>';
    }
} ?>

                    <a title="Enviar por E-mail" class="button btn btn-mini btn-warning" href="<?php echo site_url() ?>/os/enviar_email/<?php echo $result->idOs; ?>">
                        <span class="button__icon"><i class="bx bx-envelope"></i></span> <span class="button__text">Via E-mail</span></a>
                    <?php if ($result->garantias_id) { ?> <a target="_blank" title="Imprimir Termo de Garantia" class="button btn btn-mini btn-inverse" href="<?php echo site_url() ?>/garantias/imprimir/<?php echo $result->garantias_id; ?>">
                            <span class="button__icon"><i class="bx bx-printer"></i></span> <span class="button__text">Garantia</span></a> <?php } ?>
                    <a href="#modal-gerar-pagamento" id="btn-forma-pagamento" role="button" data-toggle="modal" class="button btn btn-mini btn-info">
                        <span class="button__icon"><i class='bx bx-qr'></i></span><span class="button__text">Gerar Pagamento</span></a></i>
                </div>
            </div>
            <div class="widget-content" id="printOs">
                <div class="invoice-content">
                    <div class="invoice-head" style="margin-bottom: 0">

                        <table class="table table-condensed">
                            <tbody>
                                <?php if ($emitente == null) { ?>

                                    <tr>
                                        <td colspan="3" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>
                                            <<< </td>
                                    </tr> <?php } else { ?>
                                    <tr>
                                        <td style="width: 25%"><img src=" <?php echo $emitente[0]->url_logo; ?> " style="max-height: 100px"></td>
                                        <td><span style="font-size: 20px; "> <?php echo $emitente[0]->nome; ?></span> </br>
                                            <span><?php echo $emitente[0]->cnpj; ?> </br> <?php echo $emitente[0]->rua . ', ' . $emitente[0]->numero . ' - ' . $emitente[0]->bairro . ' - ' . $emitente[0]->cidade . ' - ' . $emitente[0]->uf; ?> </span> </br>
                                            <span> E-mail: <?php echo $emitente[0]->email . ' - Fone: ' . $emitente[0]->telefone; ?></span>
                                        </td>
                                        <td style="width: 18%; text-align: center"><b>N° OS:</b>
                                            <span><?php echo $result->idOs ?></span></br> </br>
                                            <span>Emissão: <?php echo date('d/m/Y') ?></span>
                                        </td>
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
                                                    <span>Contato: <?php echo $result->celular_cliente ?></span>
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
                                                <span>Contato: <?php echo $result->telefone_usuario ?></span><br />
                                                <span>Email: <?php echo $result->email_usuario ?></span>
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
                                            <?php if ($result->garantia) { ?>
                                                <b>GARANTIA: </b>
                                                <?php echo $result->garantia . ' dias'; ?>
                                        </td>
                                    <?php } ?>
                                    <td>
                                        <b>
                                            <?php if ($result->status == 'Finalizado') { ?>
                                                VENC. DA GARANTIA:
                                        </b>
                                        <?php echo dateInterval($result->dataFinal, $result->garantia); ?><?php } ?>
                                    </td>
                                    <?php if ($result->refGarantia != '') { ?>
                                        <td>
                                            <b>TERMO GARANTIA: </b>
                                            <?php echo $result->refGarantia; ?>
                                        </td>
                                    <?php } ?>
                                    </tr>
                                <?php } ?>

                                <?php if ($result->descricaoProduto != null) { ?>
                                    <tr>
                                        <td colspan="6">
                                            <b>DESCRIÇÃO: </b>
                                            <?php echo htmlspecialchars_decode($result->descricaoProduto) ?>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if ($result->defeito != null) { ?>
                                    <tr>
                                        <td colspan="6">
                                            <b>DEFEITO APRESENTADO: </b>
                                            <?php echo htmlspecialchars_decode($result->defeito) ?>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if ($result->observacoes != null) { ?>
                                    <tr>
                                        <td colspan="6">
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
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Anotação</th>
                                    <th>Data/Hora</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($anotacoes as $a) {
                                    echo '<tr>';
                                    echo '<td>' . $a->anotacao . '</td>';
                                    echo '<td>' . date('d/m/Y H:i:s', strtotime($a->data_hora)) . '</td>';
                                    echo '</tr>';
                                }
                                if (!$anotacoes) {
                                    echo '<tr><td colspan="2">Nenhuma anotação cadastrada</td></tr>';
                                }
                                ?>
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
                                        echo '<tr>';
                                        echo '<td>' . $s->nome . '</td>';
                                        echo '<td>' . ($s->quantidade ?: 1) . '</td>';
                                        echo '<td>' . $preco . '</td>';
                                        echo '<td>R$ ' . number_format($subtotal, 2, ',', '.') . '</td>';
                                        echo '</tr>';
                                    } ?>

                                    <tr>
                                        <td colspan="3" style="text-align: right"><strong>Total:</strong></td>
                                        <td><strong>R$ <?php echo number_format($totalServico, 2, ',', '.'); ?></strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php } ?>

                        <?php if ($anexos != null) { ?>
                            <table class="table table-bordered table-condensed">
                                <thead>
                                    <tr>
                                        <th>Anexo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($anexos as $a) {
                                        if ($a->thumb == null) {
                                            $thumb = base_url() . 'assets/img/icon-file.png';
                                            $link = base_url() . 'assets/img/icon-file.png';
                                        } else {
                                            $thumb = $a->url . '/thumbs/' . $a->thumb;
                                            $link = $a->url . '/' . $a->anexo;
                                        }
                                        echo '<tr>';
                                        echo '<td><a style="min-height: 150px;" href="#modal-anexo" imagem="' . $a->idAnexos . '" link="' . $link . '" role="button" class="btn anexo span12" data-toggle="modal"><img src="' . $thumb . '" alt=""></a></td>';
                                        echo '</tr>';
                                    } ?>
                                </tbody>
                            </table>
                        <?php } ?>

                        <?php
                        if ($totalProdutos != 0 || $totalServico != 0) {
                            echo "<h4 style='text-align: right'>Valor Total: R$ " . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>";
                            echo $result->valor_desconto != 0 ? "<h4 style='text-align: right'> Desconto: R$ " . number_format($result->valor_desconto != 0 ? $result->valor_desconto - ($totalProdutos + $totalServico) : 0.00, 2, ',', '.') . "</h4>" : "";
                            echo $result->valor_desconto != 0 ? "<h4 style='text-align: right'> Total com Desconto: R$ " . number_format($result->valor_desconto, 2, ',', '.') . "</h4>" : "";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $modalGerarPagamento ?>

<!-- Modal visualizar anexo -->
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
        <a href="" link="" class="btn btn-danger" id="excluir-anexo">Excluir Anexo</a>
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
            $("#excluir-anexo").attr('link', url + id);

            $("#download").attr('href', "<?php echo base_url(); ?>index.php/os/downloadanexo/" + id);

        });

        $(document).on('click', '#excluir-anexo', function(event) {
            event.preventDefault();

            var link = $(this).attr('link');
            var idOS = "<?php echo $result->idOs; ?>"

            $('#modal-anexo').modal('hide');
            $("#divAnexos").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");

            $.ajax({
                type: "POST",
                url: link,
                dataType: 'json',
                data: "idOs=" + idOS,
                success: function(data) {
                    if (data.result == true) {
                        $("#divAnexos").load("<?php echo current_url(); ?> #divAnexos");
                    } else {
                        Swal.fire({
                            type: "error",
                            title: "Atenção",
                            text: data.mensagem
                        });
                    }
                }
            });
        });
    });
</script>
