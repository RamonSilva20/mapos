<?php
$totalServico  = 0;
$totalProdutos = 0;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?= e($this->config->item('app_name')) ?> - <?= e($result->idOs) ?> - <?= e($result->nomeCliente) ?></title>
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
                        <span class="break-words">Responsável: <b><?= e($result->nome) ?></b></span>
                    </div>
                <?php endif; ?>
            </header>
            <section class="w-full py-2.5">
                <div class="doc-titulo">

                    ORDEM DE SERVIÇO #<?= e(str_pad($result->idOs, 4, 0, STR_PAD_LEFT)) ?>
                    <span class="absolute right-1 text-[13px]">Emissão: <?= date('d/m/Y') ?></span>
                </div>

                <?php if ($result->dataInicial != null): ?>
                    <div>
                        <table class="doc-tabela">
                            <thead>
                                <tr>
                                    <th class="text-center">STATUS</th>
                                    <th class="text-center">DATA INICIAL</th>
                                    <th class="text-center">DATA FINAL</th>
                                    <?php if ($result->garantia) : ?>
                                        <th class="text-center">GARANTIA</th>
                                    <?php endif; ?>
                                    <?php if (in_array($result->status, ['Finalizado', 'Faturado'])) : ?>
                                        <th class="text-center">VENC. GARANTIA</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center"><?= e($result->status) ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($result->dataInicial)) ?></td>
                                    <td class="text-center"><?= $result->dataFinal ? date('d/m/Y', strtotime($result->dataFinal)) : '' ?></td>
                                    <?php if ($result->garantia) : ?>
                                        <td class="text-center"><?= e($result->garantia . ' dia(s)') ?></td>
                                    <?php endif; ?>
                                    <?php if (in_array($result->status, ['Finalizado', 'Faturado'])) : ?>
                                        <td class="text-center"><?= e(dateInterval($result->dataFinal, $result->garantia)) ?></td>
                                    <?php endif; ?>
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
                        <span><?= e($result->contato_cliente.' '.$result->telefone) ?><?= e($result->telefone && $result->celular ? ' / '.$result->celular : $result->celular) ?></span><br />
                        <span><?= e($result->email) ?></span><br />
                    </div>
                    <div class="text-right">
                        <span><?= e($result->rua.', '.$result->numero.', '.$result->bairro) ?></span><br />
                        <span><?= e($result->complemento.' - '.$result->cidade.' - '.$result->estado) ?></span><br />
                        <span>CEP: <?= e($result->cep) ?></span><br />
                    </div>
                </div>

                <?php if ($result->descricaoProduto) : ?>
                    <div class="doc-subtitulo">DESCRIÇÃO</div>
                    <div class="doc-dados">
                        <div>
                            <?= printSafeHtml($result->descricaoProduto) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($result->defeito) : ?>
                    <div class="doc-subtitulo">DEFEITO APRESENTADO</div>
                    <div class="doc-dados">
                        <div>
                            <?= printSafeHtml($result->defeito) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($result->observacoes) : ?>
                    <div class="doc-subtitulo">OBSERVAÇÕES</div>
                    <div class="doc-dados">
                        <div>
                            <?= printSafeHtml($result->observacoes) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($result->laudoTecnico) : ?>
                    <div class="doc-subtitulo">PARECER TÉCNICO</div>
                    <div class="doc-dados">
                        <div>
                            <?= printSafeHtml($result->laudoTecnico) ?>
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

                <?php if ($servicos) : ?>
                    <div>
                        <table class="doc-tabela">
                            <thead>
                                <tr>
                                    <th>SERVIÇO(S)</th>
                                    <th class="w-[10%] text-center">QTD</th>
                                    <th class="w-[10%] text-center">UNT</th>
                                    <th class="w-[15%] text-right">SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    setlocale(LC_MONETARY, 'en_US');
                    foreach ($servicos as $s) :
                        $preco = $s->preco ?: $s->precoVenda;
                        $subtotal = osValorDoItem($s);
                        $totalServico = $totalServico + $subtotal;
                        echo '<tr>';
                        echo '  <td>' . e($s->nome) . osDescontoItemNota($s) . '</td>';
                        echo '  <td class="text-center">' . e(($s->quantidade ?: 1)) . '</td>';
                        echo '  <td class="text-center">' . number_format($preco, 2, ',', '.') . '</td>';
                        echo '  <td class="text-right">R$ ' . number_format($subtotal, 2, ',', '.') . '</td>';
                        echo '</tr>';
                    endforeach; ?>
                                <tr>
                                    <td colspan="3" class="text-right"><b>TOTAL SERVIÇOS:</b></td>
                                    <td class="text-right"><b>R$ <?= number_format($totalServico, 2, ',', '.') ?></b></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if ($totalProdutos != 0 || $totalServico != 0) : ?>
                    <div class="flex items-center justify-between">
                        <div class="flex justify-between">
                            <?php if ($chaveFormatada) : ?>
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
                                                <td>R$ <b><?= number_format($totalProdutos + $totalServico, 2, ',', '.') ?></b></td>
                                            </tr>
                                            <tr>
                                                <td>DESCONTO</td>
                                                <td>R$ <b><?= number_format($result->valor_desconto != 0 ? $result->valor_desconto - ($totalProdutos + $totalServico) : 0.00, 2, ',', '.') ?></b></td>
                                            </tr>
                                            <tr>
                                                <td>TOTAL</td>
                                                <td>R$ <?= number_format($result->valor_desconto, 2, ',', '.') ?></td>
                                            </tr>
                                        <?php else : ?>
                                            <tr>
                                                <td class="w-[290px]">TOTAL</td>
                                                <td>R$ <?= number_format($totalProdutos + $totalServico, 2, ',', '.') ?></td>
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
                    <span>Data inicial: <b><?= date('d/m/Y', strtotime($result->dataInicial)) ?></b></span>
                    <span>ORDEM DE SERVIÇO <b>#<?= e(str_pad($result->idOs, 4, 0, STR_PAD_LEFT)) ?></b></span>
                    <span>Data final: <b><?= $result->dataFinal ? date('d/m/Y', strtotime($result->dataFinal)) : '' ?></b></span>
                </div>
                <div class="flex justify-around">
                    <span class="doc-assinatura">Assinatura do cliente</span>
                    <span class="doc-assinatura">Assinatura do técnico</span>
                </div>
            </footer>
        </div>
