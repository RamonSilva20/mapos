<?php $totalProdutos = 0; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Map_Vendas_<?php echo e($result->idVendas);?>_<?php echo e($result->nomeCliente);?></title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/dist/imprimir.css">
</head>

<body>
    <div class="cupom">
        <?php if ($emitente == null) { ?>
            <div class="bg-warning-soft p-2 text-warning-ink">
                Você precisa configurar os dados do emitente. &gt;&gt;&gt;<a class="underline" href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>&lt;&lt;&lt;
            </div>
        <?php } else { ?>
            <div class="text-center">
                <img class="mx-auto max-h-[100px]" src="<?php echo e($emitente->url_logo); ?>" alt="">
            </div>
            <div class="mt-1 text-center">
                <span class="text-xl"><b><?php echo e($emitente->nome); ?></b></span><br>
                <span>
                    <?php echo e('CNPJ: ' . $emitente->cnpj); ?><br>
                    <?php echo e($emitente->rua . ', ' . $emitente->numero . ', ' . $emitente->bairro . ' - ' . $emitente->cidade . ' - ' . $emitente->uf); ?>
                </span><br>
                <span><?php echo e('Fone: ' . $emitente->telefone); ?></span>
            </div>
            <div class="mt-2">
                <b>#Venda: </b><span><?php echo e($result->idVendas);?></span>
                <span class="px-[1em]">Emissão: <?php echo date('d/m/Y H:i:s'); ?></span>
                <?php if ($result->faturado) : ?>
                    <br>
                    <b>Venc. Garantia: </b>
                    <?php echo e(dateInterval($result->dataVenda, $result->garantia)); ?>
                <?php endif; ?>
            </div>
        <?php } ?>

        <div class="mt-2">
            <h5 class="font-bold">CLIENTE</h5>
            <span><?php echo e($result->nomeCliente);?></span><br>
            <span>
                <?php echo e($result->rua);?>,
                <?php echo e($result->numero);?>,
                <?php echo e($result->bairro);?></span><br>
            <span>
                <?php echo e($result->cidade);?> -
                <?php echo e($result->estado);?></span>
        </div>

        <?php if ($produtos != null) { ?>
            <table class="cupom-tabela cupom-tabela-grade mt-2" id="tblProdutos">
                <thead>
                    <tr class="bg-surface-subtle">
                        <th class="text-left text-xs">Produto</th>
                        <th class="text-left text-xs">Quantidade</th>
                        <th class="text-left text-xs">Preço unit.</th>
                        <th class="text-left text-xs">Sub-total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                                foreach ($produtos as $p) {
                                    $totalProdutos = $totalProdutos + $p->subTotal;
                                    echo '<tr>';
                                    echo '<td>' . e($p->descricao) . osDescontoItemNota($p) . '</td>';
                                    echo '<td>' . e($p->quantidade) . '</td>';
                                    echo '<td>R$ ' . e(($p->preco ?: $p->precoVenda)) . '</td>';
                                    echo '<td>R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                                    echo '</tr>';
                                } ?>
                    <?php if ($result->valor_desconto != 0 && $result->desconto != 0) { ?>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total: R$</strong></td>
                            <td>
                                <strong>
                                    <?php echo number_format($totalProdutos, 2, ',', '.'); ?>
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Desconto: R$</strong></td>
                            <td>
                                <strong>
                                    <?php echo number_format($result->valor_desconto - $totalProdutos, 2, ',', '.'); ?>
                                </strong>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="4" class="text-right">
                            <h4 class="text-right text-base font-bold">Total: R$
                                <?php echo number_format($result->desconto != 0 && $result->valor_desconto != 0 ? $result->valor_desconto : $totalProdutos, 2, ',', '.'); ?>
                            </h4>
                        </td>
                    </tr>
                </tbody>
                <?php if (in_array($result->status, ['Finalizado', 'Orçamento', 'Faturado', 'Aberto', 'Em Andamento', 'Aguardando Peças']) && $qrCode): ?>
                    <tr>
                        <td colspan="4" class="text-center">
                            <img class="mx-auto mt-3 w-16" src="<?= base_url(); ?>assets/img/logo_pix.png" alt="QR Code de Pagamento"><br>
                            <img class="mx-auto mt-1 w-[94px]" src="<?= e($qrCode) ?>" alt="QR Code de Pagamento"><br>
                            <span class="text-[80%]">Chave PIX: <?= e($chaveFormatada) ?></span>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        <?php } ?>

        <div class="cupom-assinatura">Assinatura do Recebedor</div>
    </div>
<script src="<?= e(base_url('assets/js/imprimir.js')) ?>"></script>
</body>

</html>
