<?php
    $totalServico  = 0;
    $totalProdutos = 0;
    $totalOutros = 0;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Proposta Comercial - <?= $result->clientes_id ? ($result->nomeCliente ?? '') : ($result->cliente_nome ?? 'N/A'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap5.3.2.min.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/imprimir.css">
    <style>
        .no-print {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .no-print button, .no-print a {
            margin: 0 10px;
            padding: 10px 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bx bx-printer"></i> Imprimir
        </button>
        <a href="<?php echo base_url(); ?>index.php/propostas/gerarPdf/<?php echo $result->idProposta; ?>" class="btn btn-danger" target="_blank">
            <i class="bx bx-file"></i> Salvar PDF
        </a>
        <?php 
        $clienteNome = $result->clientes_id ? ($result->nomeCliente ?? '') : ($result->cliente_nome ?? '');
        $numeroProposta = $result->numero_proposta ?: 'PROP-' . str_pad($result->idProposta, 6, 0, STR_PAD_LEFT);
        $valorTotal = number_format($result->valor_total, 2, ',', '.');
        $textoWhatsApp = urlencode("Olá! Segue a proposta comercial:\n\n*Proposta:* $numeroProposta\n*Cliente:* $clienteNome\n*Valor Total:* R$ $valorTotal\n\n" . base_url() . "index.php/propostas/visualizar/" . $result->idProposta);
        $telefoneCliente = '';
        if ($result->celular_cliente) {
            $telefoneCliente = preg_replace('/[^0-9]/', '', $result->celular_cliente);
        } elseif ($result->telefone) {
            $telefoneCliente = preg_replace('/[^0-9]/', '', $result->telefone);
        }
        ?>
        <?php if ($telefoneCliente) { ?>
            <a href="https://api.whatsapp.com/send?phone=55<?php echo $telefoneCliente; ?>&text=<?php echo $textoWhatsApp; ?>" class="btn btn-success" target="_blank">
                <i class="bx bxl-whatsapp"></i> Compartilhar WhatsApp
            </a>
        <?php } else { ?>
            <a href="https://api.whatsapp.com/send?text=<?php echo $textoWhatsApp; ?>" class="btn btn-success" target="_blank">
                <i class="bx bxl-whatsapp"></i> Compartilhar WhatsApp
            </a>
        <?php } ?>
        <a href="<?php echo base_url(); ?>index.php/propostas" class="btn btn-secondary">
            <i class="bx bx-arrow-back"></i> Voltar
        </a>
    </div>

    <div class="main-page">
        <div class="sub-page">
            <header>
                <?php if ($emitente == null) : ?>
                    <div class="alert alert-danger" role="alert">
                        Você precisa configurar os dados do emitente. >>> <a href="<?=base_url()?>index.php/mapos/emitente">Configurar</a>
                    </div>
                <?php else : ?>
                    <div class="imgLogo" class="align-middle">
                        <img src="<?= $emitente->url_logo ?>" class="img-fluid" style="width:140px;">
                    </div>
                    <div class="emitente">
                        <span style="font-size: 16px;"><b><?= $emitente->nome ?></b></span></br>
                        <?php if($emitente->cnpj != "00.000.000/0000-00") : ?>
                            <span class="align-middle">CNPJ: <?= $emitente->cnpj ?></span></br>
                        <?php endif; ?>
                        <span class="align-middle">
                            <?= $emitente->rua.', '.$emitente->numero.', '.$emitente->bairro ?><br>
                            <?= $emitente->cidade.' - '.$emitente->uf.' - '.$emitente->cep ?>
                        </span>
                    </div>
                    <div class="contatoEmitente">
                        <span style="font-weight: bold;">Tel: <?= $emitente->telefone ?></span></br>
                        <span style="font-weight: bold;"><?= $emitente->email ?></span></br>
                        <span style="word-break: break-word;">Vendedor: <b><?= $result->nome ?></b></span>
                    </div>
                <?php endif; ?>
            </header>
            <section>
                <div class="title">
                    PROPOSTA COMERCIAL <?= $result->numero_proposta ? 'N° ' . $result->numero_proposta : '# ' . str_pad($result->idProposta, 4, 0, STR_PAD_LEFT) ?>
                    <span class="emissao">Emissão: <?= date('d/m/Y', strtotime($result->data_proposta)) ?></span>
                </div>

                <?php if ($result->data_validade) : ?>
                    <div style="text-align: center; margin: 10px 0; font-weight: bold; color: #d9534f;">
                        Validade: <?= date('d/m/Y', strtotime($result->data_validade)) ?>
                    </div>
                <?php endif; ?>

                <div class="subtitle">DADOS DO CLIENTE</div>
                <div class="dados">
                    <div>
                        <span><b><?= $result->clientes_id ? ($result->nomeCliente ?? '') : ($result->cliente_nome ?? 'N/A'); ?></b></span><br />
                        <?php if ($result->documento) : ?>
                            <span>CPF/CNPJ: <?= $result->documento ?></span><br />
                        <?php endif; ?>
                        <?php if ($result->telefone) : ?>
                            <span>Tel: <?= $result->telefone ?></span>
                            <?php if ($result->celular_cliente) : ?>
                                <span> / Cel: <?= $result->celular_cliente ?></span>
                            <?php endif; ?>
                            <br />
                        <?php elseif ($result->celular_cliente) : ?>
                            <span>Cel: <?= $result->celular_cliente ?></span><br />
                        <?php endif; ?>
                        <?php if ($result->email) : ?>
                            <span><?= $result->email ?></span><br />
                        <?php endif; ?>
                    </div>
                    <?php if ($result->rua || $result->cidade) : ?>
                        <div style="text-align: right;">
                            <?php if ($result->rua) : ?>
                                <span><?= $result->rua.', '.($result->numero ?? '') ?></span><br />
                            <?php endif; ?>
                            <?php if ($result->bairro) : ?>
                                <span><?= $result->bairro ?></span><br />
                            <?php endif; ?>
                            <?php if ($result->cidade) : ?>
                                <span><?= $result->cidade.' - '.(isset($result->estado) && $result->estado ? $result->estado : '') ?></span><br />
                            <?php endif; ?>
                            <?php if ($result->cep) : ?>
                                <span>CEP: <?= $result->cep ?></span><br />
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($produtos && count($produtos) > 0) : ?>
                    <div class="tabela">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="table-secondary">
                                    <th>PRODUTO(S)</th>
                                    <th class="text-center" width="10%">QTD</th>
                                    <th class="text-center" width="10%">UNT</th>
                                    <th class="text-end" width="15%">SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos as $p) :
                                    $totalProdutos = $totalProdutos + $p->subtotal;
                                    echo '<tr>';
                                    echo '  <td>' . $p->descricao . '</td>';
                                    echo '  <td class="text-center">' . number_format($p->quantidade, 2, ',', '.') . '</td>';
                                    echo '  <td class="text-center">R$ ' . number_format($p->preco, 2, ',', '.') . '</td>';
                                    echo '  <td class="text-end">R$ ' . number_format($p->subtotal, 2, ',', '.') . '</td>';
                                    echo '</tr>';
                                endforeach; ?>
                                <tr>
                                    <td colspan="3" class="text-end"><b>TOTAL PRODUTOS:</b></td>
                                    <td class="text-end"><b>R$ <?= number_format($totalProdutos, 2, ',', '.') ?></b></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <?php if ($servicos && count($servicos) > 0) : ?>
                    <div class="tabela">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="table-secondary">
                                    <th>SERVIÇO(S)</th>
                                    <th class="text-center" width="10%">QTD</th>
                                    <th class="text-center" width="10%">UNT</th>
                                    <th class="text-end" width="15%">SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servicos as $s) :
                                    $preco = $s->preco;
                                    $subtotal = $preco * ($s->quantidade ?: 1);
                                    $totalServico = $totalServico + $subtotal;
                                    echo '<tr>';
                                    echo '  <td>' . $s->descricao . '</td>';
                                    echo '  <td class="text-center">' . number_format($s->quantidade, 2, ',', '.') . '</td>';
                                    echo '  <td class="text-center">R$ ' . number_format($preco, 2, ',', '.') . '</td>';
                                    echo '  <td class="text-end">R$ ' . number_format($subtotal, 2, ',', '.') . '</td>';
                                    echo '</tr>';
                                endforeach; ?>
                                <tr>
                                    <td colspan="3" class="text-end"><b>TOTAL SERVIÇOS:</b></td>
                                    <td class="text-end"><b>R$ <?= number_format($totalServico, 2, ',', '.') ?></b></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if ($outros && count($outros) > 0) : ?>
                    <div class="tabela">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="table-secondary">
                                    <th>OUTROS PRODUTOS/SERVIÇOS</th>
                                    <th class="text-end" width="15%">VALOR</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($outros as $o) :
                                    $totalOutros = $totalOutros + $o->preco;
                                    echo '<tr>';
                                    echo '  <td>' . nl2br($o->descricao) . '</td>';
                                    echo '  <td class="text-end">R$ ' . number_format($o->preco, 2, ',', '.') . '</td>';
                                    echo '</tr>';
                                endforeach; ?>
                                <tr>
                                    <td class="text-end"><b>TOTAL:</b></td>
                                    <td class="text-end"><b>R$ <?= number_format($totalOutros, 2, ',', '.') ?></b></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if ($parcelas && count($parcelas) > 0) : ?>
                    <div class="tabela">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="table-secondary">
                                    <th width="5%">Nº</th>
                                    <th width="15%">DIAS</th>
                                    <th width="20%">VENCIMENTO</th>
                                    <th width="20%" class="text-end">VALOR</th>
                                    <th>OBSERVAÇÃO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($parcelas as $p) :
                                    $dataVencimento = $p->data_vencimento ? date('d/m/Y', strtotime($p->data_vencimento)) : '-';
                                    echo '<tr>';
                                    echo '  <td>' . $p->numero . '</td>';
                                    echo '  <td>' . $p->dias . ' dias</td>';
                                    echo '  <td>' . $dataVencimento . '</td>';
                                    echo '  <td class="text-end">R$ ' . number_format($p->valor, 2, ',', '.') . '</td>';
                                    echo '  <td>' . ($p->observacao ?? '') . '</td>';
                                    echo '</tr>';
                                endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php 
                $subtotal = $totalProdutos + $totalServico + $totalOutros;
                $desconto = floatval($result->valor_desconto ?? 0);
                $total = $subtotal - $desconto;
                ?>
                <div class="tabela">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td colspan="3" class="text-end"><b>SUBTOTAL:</b></td>
                                <td class="text-end"><b>R$ <?= number_format($subtotal, 2, ',', '.') ?></b></td>
                            </tr>
                            <?php if ($desconto > 0) : ?>
                                <tr>
                                    <td colspan="3" class="text-end"><b>DESCONTO:</b></td>
                                    <td class="text-end"><b>- R$ <?= number_format($desconto, 2, ',', '.') ?></b></td>
                                </tr>
                            <?php endif; ?>
                            <tr style="background-color: #f0f0f0; font-size: 16px;">
                                <td colspan="3" class="text-end"><b>TOTAL:</b></td>
                                <td class="text-end"><b>R$ <?= number_format($total, 2, ',', '.') ?></b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php if ($result->observacoes) : ?>
                    <div class="subtitle">OBSERVAÇÕES</div>
                    <div class="dados">
                        <div style="text-align: justify;">
                            <?= nl2br(htmlspecialchars($result->observacoes)) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="margin-top: 40px; text-align: center; font-size: 10px; color: #666;">
                    <p>Esta proposta é válida até <?= $result->data_validade ? date('d/m/Y', strtotime($result->data_validade)) : 'a definir'; ?></p>
                    <p>Status: <strong><?= $result->status ?></strong></p>
                </div>
            </section>
        </div>
    </div>
</body>
</html>

