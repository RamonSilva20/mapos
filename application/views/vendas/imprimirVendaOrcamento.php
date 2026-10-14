<?php
$totalProdutos = 0;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?= e($this->config->item('app_name')) ?> - Orçamento da Venda Nº#<?= e($result->idVendas) ?> - <?= e($result->nomeCliente) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/dist/imprimir.css">
</head>
<body>
    <div class="doc-folha">
        <div>
            <header class="doc-cabecalho">
                <?php if ($emitente == null) : ?>
                    <div class="w-full bg-danger-soft p-2 text-danger-ink" role="alert">
                        Você precisa configurar os dados do emitente. >>> <a href="<?=base_url()?>index.php/mapos/emitente">Configurar</a>
                    </div>
                <?php else : ?>
                    <div class="flex items-center">
                        <img src="<?= e($emitente->url_logo) ?>" class="h-auto w-[140px] max-w-full" alt="">
                    </div>
                    <div class="mt-0.5 px-2.5">
                        <span class="text-base"><b><?= e($emitente->nome) ?></b></span><br>
                        <?php if ($emitente->cnpj != "00.000.000/0000-00") : ?>
                            <span>CNPJ: <?= e($emitente->cnpj) ?></span><br>
                        <?php endif; ?>
                        <span>
                            <?= e($emitente->rua.', '.$emitente->numero.', '.$emitente->bairro) ?><br>
                            <?= e($emitente->cidade.' - '.$emitente->uf.' - '.$emitente->cep) ?>
                        </span>
                    </div>
                    <div class="mt-2.5 max-w-[230px] text-right">
                        <span class="font-bold">Tel: <?= e($emitente->telefone) ?></span><br>
                        <span class="font-bold"><?= e($emitente->email) ?></span><br>
                    </div>
                <?php endif; ?>
            </header>
            <section class="w-full py-2.5">
                <div class="doc-titulo">
                    ORÇAMENTO DA VENDA Nº#<?= e(str_pad($result->idVendas, 4, 0, STR_PAD_LEFT)) ?>
                    <span class="absolute right-1 text-[13px]">Emissão: <?= date('d/m/Y H:i:s') ?></span>
                </div>

                <?php if ($result->dataVenda != null): ?>
                    <div>
                        <table class="doc-tabela">
                            <thead>
                                <tr>
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

                <div class="doc-subtitulo">DADOS DO CLIENTE</div>
                <div class="doc-dados">
                    <div>
                        <span><b><?= e($result->nomeCliente) ?></b></span><br />
                        <span>CPF/CNPJ: <?= e($result->documento) ?></span><br />
                        <span><?= e($result->contato.' '.$result->telefone) ?><?= e($result->telefone && $result->celular ? ' / '.$result->celular : $result->celular) ?></span><br />
                        <span><?= e($result->emailCliente) ?></span><br />
                    </div>
                    <div class="text-right">
                        <span><?= e($result->rua.', '.$result->numero.', '.$result->bairro) ?></span><br />
                        <span><?= e($result->complemento.' - '.$result->cidade.' - '.$result->estado) ?></span><br />
                        <span>CEP: <?= e($result->cep) ?></span><br />
                    </div>
                </div>

                <?php if ($result->observacoes_cliente) : ?>
                    <div class="doc-subtitulo">OBSERVAÇÕES</div>
                    <div class="doc-dados">
                        <div>
                            <?= printSafeHtml($result->observacoes_cliente) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($produtos) : ?>
                    <div>
                        <table class="doc-tabela">
                            <thead>
                                <tr>
                                    <th>PRODUTO(S)</th>
                                    <th class="w-[10%] text-center">QTD</th>
                                    <th class="w-[10%] text-center">UNT</th>
                                    <th class="w-[15%] text-right">SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos as $p) :
                                    $totalProdutos = $totalProdutos + $p->subTotal;
                                    echo '<tr>';
                                    echo '  <td>' . e($p->descricao) . osDescontoItemNota($p) . '</td>';
                                    echo '  <td class="text-center">' . e($p->quantidade) . '</td>';
                                    echo '  <td class="text-center">' . number_format($p->preco ?: $p->precoVenda, 2, ',', '.') . '</td>';
                                    echo '  <td class="text-right">R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                                    echo '</tr>';
                                endforeach; ?>
                                <tr>
                                    <td colspan="3" class="text-right"><b>TOTAL PRODUTOS:</b></td>
                                    <td class="text-right"><b>R$ <?= number_format($totalProdutos, 2, ',', '.') ?></b></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if ($totalProdutos != 0) : ?>
                    <div class="flex items-center justify-between">
                        <div class="flex justify-between">
                            <?php if ($this->data['configuration']['pix_key']) : ?>
                                <div><img class="w-[130px]" src="<?= e($qrCode) ?>" alt="QR Code de Pagamento" /></div>
                                <div class="flex flex-wrap content-center">
                                    <div class="w-full text-center"><span class="flex justify-center text-muted"><?= icon('camera', ['class' => 'size-5']) ?></span>Escaneie o QRCode ao lado para pagar por Pix</div>
                                    <div class="mt-2 w-full break-words text-center">Chave Pix: <b><?= e($chaveFormatada) ?></b></div>
                                </div>
                            <?php else: ?>
                                <div></div>
                                <div></div>
                            <?php endif; ?>
                        </div>
                        <div class="w-[400px] text-right">
                            <div>
                                <table class="doc-tabela">
                                    <thead>
                                        <tr>
                                            <th colspan="2">RESUMO DOS VALORES</th>
                                        </tr>
                                    </thead>
                                    <tbody class="font-bold">
                                        <?php if ($result->valor_desconto != 0) : ?>
                                            <tr>
                                                <td class="w-[65%]">SUBTOTAL</td>
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
                                                <td class="w-[290px]">TOTAL</td>
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
            <footer class="doc-rodape">
                <div class="flex justify-between">
                    <span>Data da Venda: <b><?= date('d/m/Y', strtotime($result->dataVenda)) ?></b></span>
                    <span>VENDA <b>#<?= e(str_pad($result->idVendas, 4, 0, STR_PAD_LEFT)) ?></b></span>
                </div>
                <div class="flex justify-around text-center">
                    <span>Vendedor : <b><?php echo e($result->nome); ?> </b></span>
                </div>
            </footer>
        </div>
        </div>
    </div>
<script src="<?= e(base_url('assets/js/imprimir.js')) ?>"></script>
</body>
</html>
