<?php
$totalProdutos = 0;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?= e($this->config->item('app_name')) ?> - Orçamento da Venda Nº#<?= e($result->idVendas) ?> - <?= e($result->nomeCliente) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap5.3.2.min.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/imprimir.css">
</head>
<body>
    <div class="main-page">
        <div class="sub-page">
            <header>
                <?php if ($emitente == null) : ?>
                    <div class="alert alert-danger" role="alert">
                        Você precisa configurar os dados do emitente. >>> <a href="<?=base_url()?>index.php/mapos/emitente">Configurar</a>
                    </div>
                <?php else : ?>
                    <div class="imgLogo" class="align-middle">
                        <img src="<?= e($emitente->url_logo) ?>" class="img-fluid" style="width:140px;">
                    </div>
                    <div class="emitente">
                        <span style="font-size: 16px;"><b><?= e($emitente->nome) ?></b></span><br>
                        <?php if ($emitente->cnpj != "00.000.000/0000-00") : ?>
                            <span class="align-middle">CNPJ: <?= e($emitente->cnpj) ?></span><br>
                        <?php endif; ?>
                        <span class="align-middle">
                            <?= e($emitente->rua.', '.$emitente->numero.', '.$emitente->bairro) ?><br>
                            <?= e($emitente->cidade.' - '.$emitente->uf.' - '.$emitente->cep) ?>
                        </span>
                    </div>
                    <div class="contatoEmitente">
                        <span style="font-weight: bold;">Tel: <?= e($emitente->telefone) ?></span><br>
                        <span style="font-weight: bold;"><?= e($emitente->email) ?></span><br>
                    </div>
                <?php endif; ?>
            </header>
            <section>
                <div class="title">
                    ORÇAMENTO DA VENDA Nº#<?= e(str_pad($result->idVendas, 4, 0, STR_PAD_LEFT)) ?>
                    <span class="emissao">Emissão: <?= date('d/m/Y H:i:s') ?></span>
                </div>

                <?php if ($result->dataVenda != null): ?>
                    <div class="tabela">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="table-secondary">
                                    <th class="text-center">STATUS</th>
                                    <th class="text-center">DATA DA VENDA</th>
                                    <th class="text-center">VALIDADE</th>
                                    <th class="text-center">VENC. DO ORÇAMENTO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center"><?= e($result->status) ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($result->dataVenda)) ?></td>
                                    <td class="text-center">
                                        <?php if ($result->garantia > 0): ?>
                                            <?= e($result->garantia . ' dia(s)') ?>
                                        <?php else: ?>
                                            Sem garantia
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($result->garantia > 0): ?>
                                            <?php
                                                $dataVenda = strtotime($result->dataVenda);
                                            $vencimentoGarantia = date('d/m/Y', strtotime("+{$result->garantia} days", $dataVenda));
                                            echo e($vencimentoGarantia);
                                            ?>
                                        <?php else: ?>
                                            Sem garantia
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="subtitle">DADOS DO CLIENTE</div>
                <div class="dados">
                    <div>
                        <span><b><?= e($result->nomeCliente) ?></b></span><br />
                        <span>CPF/CNPJ: <?= e($result->documento) ?></span><br />
                        <span><?= e($result->contato.' '.$result->telefone) ?><?= e($result->telefone && $result->celular ? ' / '.$result->celular : $result->celular) ?></span><br />
                        <span><?= e($result->emailCliente) ?></span><br />
                    </div>
                    <div style="text-align: right;">
                        <span><?= e($result->rua.', '.$result->numero.', '.$result->bairro) ?></span><br />
                        <span><?= e($result->complemento.' - '.$result->cidade.' - '.$result->estado) ?></span><br />
                        <span>CEP: <?= e($result->cep) ?></span><br />
                    </div>
                </div>

                <?php if ($result->observacoes_cliente) : ?>
                    <div class="subtitle">OBSERVAÇÕES</div>
                    <div class="dados">
                        <div>
                            <?= printSafeHtml($result->observacoes_cliente) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($produtos) : ?>
                    <div class="tabela">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="table-secondary">
                                    <th>PRODUTO(S)</th>
                                    <th class="text-center" width="10%">QTD</th>
                                    <th class="text-center" width="10%">UNT</th>
                                    <th class="text-end" width="15%" >SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos as $p) :
                                    $totalProdutos = $totalProdutos + $p->subTotal;
                                    echo '<tr>';
                                    echo '  <td>' . e($p->descricao) . '</td>';
                                    echo '  <td class="text-center">' . e($p->quantidade) . '</td>';
                                    echo '  <td class="text-center">' . number_format($p->preco ?: $p->precoVenda, 2, ',', '.') . '</td>';
                                    echo '  <td class="text-end">R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
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

                <?php if ($totalProdutos != 0) : ?>
                    <div class="pagamento">
                        <div class="qrcode">
                            <?php if ($this->data['configuration']['pix_key']) : ?>
                                <div><img width="130px" src="<?= e($qrCode) ?>" alt="QR Code de Pagamento" /></div>
                                <div style="display: flex; flex-wrap: wrap; align-content: center;">
                                    <div style="width: 100%; text-align:center;"><i class="fas fa-camera"></i><br />Escaneie o QRCode ao lado para pagar por Pix</div>
                                    <div class="chavePix">Chave Pix: <b><?= e($chaveFormatada) ?></b></div>
                                </div>
                            <?php else: ?>
                                <div></div>
                                <div></div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="tabela">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr class="table-secondary">
                                            <th colspan="2">RESUMO DOS VALORES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result->valor_desconto != 0) : ?>
                                            <tr>
                                                <td width="65%">SUBTOTAL</td>
                                                <td>R$ <b><?= number_format($totalProdutos, 2, ',', '.') ?></b></td>
                                            </tr>
                                            <tr>
                                                <td>DESCONTO</td>
                                                <td>R$ <b><?= number_format($result->valor_desconto - $totalProdutos, 2, ',', '.') ?></b></td>
                                            </tr>
                                            <tr>
                                                <td>TOTAL</td>
                                                <td>R$ <?= number_format($result->valor_desconto, 2, ',', '.') ?></td>
                                            </tr>
                                        <?php else : ?>
                                            <tr>
                                                <td style="width:290px">TOTAL</td>
                                                <td>R$ <?= number_format($totalProdutos, 2, ',', '.') ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>


            </section>
            <footer>
                <div class="detalhes">
                    <span>Data da Venda: <b><?= date('d/m/Y', strtotime($result->dataVenda)) ?></b></span>
                    <span>VENDA <b>#<?= e(str_pad($result->idVendas, 4, 0, STR_PAD_LEFT)) ?></b></span>
                </div>
                <div class="vendedor">
                    <span>Vendedor : <b><?php echo e($result->nome); ?> </b></span>
                </div>
            </footer>
        </div>
        </div>
    </div>
    <script type="text/javascript">
        window.print();
    </script>
</body>
</html>
