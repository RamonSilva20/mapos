<?php $totalServico = 0;
$totalProdutos = 0; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Map_OS_<?php echo e($result->idOs);?>_<?php echo e($result->nomeCliente);?></title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/dist/imprimir.css">
</head>

<body>
<div id="principal" class="cupom">

        <?php if ($emitente == null) { ?>
            <div class="bg-warning-soft p-2 text-warning-ink">
                Você precisa configurar os dados do emitente. &gt;&gt;&gt;<a class="underline" href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>&lt;&lt;&lt;
            </div>
        <?php } else { ?>
            <div class="text-center">
                <img class="mx-auto max-h-[100px]" src="<?php echo e($emitente->url_logo); ?>" alt="">
            </div>
            <div class="mt-1 text-center">
                <span class="text-xs uppercase"><b><?php echo e($emitente->nome); ?></b></span><br>
                <?php if ($emitente->cnpj != "00.000.000/0000-00") { ?><span class="inline-flex items-center gap-1"><?= icon('fingerprint', ['class' => 'size-4']) ?> <?php echo e($emitente->cnpj); ?></span><br><?php } ?>
                <span><?php echo e($emitente->rua . ', ' . $emitente->numero) . '<br>' . e($emitente->bairro . ', ' . $emitente->cidade . ' - ' . $emitente->uf); ?></span><br>
                <span><?php echo e($emitente->email); ?> - <?php echo e($emitente->telefone); ?></span>
            </div>
        <?php } ?>

        <div class="mt-2">
            <span><b>CLIENTE</b><br></span>
            <span><?php echo e($result->nomeCliente);?><br></span>
            <?= !empty($result->contato_cliente) ? '<span>' . e($result->contato_cliente) . ' </span>' : '' ?>
            <?php if ($result->celular_cliente == $result->telefone_cliente) { ?>
                <span><?= e($result->celular_cliente) ?></span><br>
            <?php } else { ?>
                <?= e(!empty($result->telefone_cliente) ? $result->telefone_cliente : "") ?>
                <?= !empty($result->celular_cliente) && !empty($result->telefone_cliente) ? ' / ' : "" ?>
                <?= e(!empty($result->celular_cliente) ? $result->celular_cliente : "") ?><br>
            <?php } ?>
            <?php if (!empty($result->email)) : ?>
                <span><?php echo e($result->email);?></span><br>
            <?php endif; ?>
            <span><?php
                $retorno_end = array_filter([$result->rua, $result->numero, $result->complemento, $result->bairro]);
$endereco = implode(', ', $retorno_end);
if (!empty($endereco)) {
    echo e($endereco) . '<br>';
}
if (!empty($result->cidade) || !empty($result->estado) || !empty($result->cep)) {
    echo '<span>' . e("{$result->cidade} - {$result->estado}, {$result->cep}") . '</span><br>';
}
?></span>
        </div>

        <div class="mt-2 border-t border-hairline-cool pt-1 text-center text-xs">
            <b>N° OS: </b><span><?php echo e($result->idOs);?></span>
            <span class="pl-[5%]"><b>Status: </b><?php echo e($result->status);?></span><br>
            <b>Emissão:</b> <?php echo date('d/m/Y H:i:s') ?>
        </div>

        <div class="mt-1 text-xs">
            <table class="cupom-tabela">
                <tbody>
                    <?php if ($result->dataInicial != null) { ?>
                        <tr>
                            <td><b>Inicial: </b><?php echo date('d/m/Y', strtotime($result->dataInicial)); ?></td>
                            <td><b>Final: </b><?php echo $result->dataFinal ? date('d/m/Y', strtotime($result->dataFinal)) : ''; ?></td>
                            <?php if ($result->garantia != null) { ?><td><b>Garantia:</b><br><?php echo e($result->garantia . ' dia(s)'); ?></td><?php } ?>
                        </tr>
                    <?php } ?>
                    <?php if ($result->descricaoProduto != null) { ?>
                        <tr>
                            <td colspan="5"><b>Descrição: </b><?php echo printSafeHtml($result->descricaoProduto) ?></td>
                        </tr>
                    <?php } ?>
                    <?php if ($result->defeito != null) { ?>
                        <tr>
                            <td colspan="5"><b>Defeito Apresentado: </b><?php echo printSafeHtml($result->defeito) ?></td>
                        </tr>
                    <?php } ?>
                    <?php if ($result->observacoes != null) { ?>
                        <tr>
                            <td colspan="5"><b>Observações: </b><?php echo printSafeHtml($result->observacoes) ?></td>
                        </tr>
                    <?php } ?>
                    <?php if ($result->status != 'Aberto') { ?>
                        <?php if ($result->laudoTecnico != null) { ?>
                            <tr>
                                <td colspan="5"><b>Laudo Técnico: </b><?php echo printSafeHtml($result->laudoTecnico) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <?php if ($result->garantias_id != null) { ?>
                        <tr>
                            <td colspan="5">
                                <strong>Termo de Garantia: </strong><br>
                                <?php echo printSafeHtml($result->textoGarantia) ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <?php if ($produtos != null) { ?>
                <table class="cupom-tabela cupom-tabela-grade" id="tblProdutos">
                    <thead>
                        <tr class="bg-surface-subtle">
                            <th class="text-left">Qtd</th>
                            <th class="text-left">Produto</th>
                            <th class="text-left">Unitário</th>
                            <th class="text-left">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
            foreach ($produtos as $p) {
                $totalProdutos = $totalProdutos + $p->subTotal;
                echo '<tr>';
                echo '<td>' . e($p->quantidade) . '</td>';
                echo '<td>' . e($p->descricao) . osDescontoItemNota($p) . '</td>';
                echo '<td>R$ ' . e($p->preco ?: $p->precoVenda) . '</td>';
                echo '<td>R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                echo '</tr>';
            } ?>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                            <td><strong>R$ <?php echo number_format($totalProdutos, 2, ',', '.'); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            <?php } ?>

            <?php if ($servicos != null) { ?>
                <table class="cupom-tabela cupom-tabela-grade">
                    <thead>
                        <tr class="bg-surface-subtle">
                            <th class="text-left">Qtd</th>
                            <th class="text-left">Serviço</th>
                            <th class="text-left">Unitário</th>
                            <th class="text-left">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php setlocale(LC_MONETARY, 'en_US');
                foreach ($servicos as $s) {
                    $preco = $s->preco ?: $s->precoVenda;
                    $subtotal = osValorDoItem($s);
                    $totalServico = $totalServico + $subtotal;
                    echo '<tr>';
                    echo '<td>' . e(($s->quantidade ?: 1)) . '</td>';
                    echo '<td>' . e($s->nome) . osDescontoItemNota($s) . '</td>';
                    echo '<td>R$ ' . e($preco) . '</td>';
                    echo '<td>R$ ' . number_format($subtotal, 2, ',', '.') . '</td>';
                    echo '</tr>';
                } ?>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                            <td><strong>R$ <?php echo number_format($totalServico, 2, ',', '.'); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            <?php } ?>

            <table class="cupom-tabela cupom-tabela-grade">
                <tbody>
                    <tr>
                        <td colspan="5"> <?php
                if ($totalProdutos != 0 || $totalServico != 0) {
                    if ($result->valor_desconto != 0) {
                        echo "<h4 class='cupom-total'>Subtotal: R$ " . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>";
                        echo $result->valor_desconto != 0 ? "<h4 class='cupom-total'> Desconto: R$ " . number_format($result->valor_desconto != 0 ? $result->valor_desconto - ($totalProdutos + $totalServico) : 0.00, 2, ',', '.') . "</h4>" : "";
                        echo $result->valor_desconto != 0 ? "<h4 class='cupom-total'> Total: R$ " . number_format($result->valor_desconto, 2, ',', '.') . "</h4>" : "";
                    } else {
                        echo "<h4 class='cupom-total'>Total: R$ " . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>";
                    }
                } ?>
                        </td>
                    </tr>
                    <?php if ($result->status == 'Finalizado' || $result->status == 'Orçamento') { ?>
                        <?php if ($qrCode) : ?>
                            <tr>
                                <td colspan="5" class="text-center">
                                    <img class="mx-auto mt-3 w-16" src="<?php echo base_url(); ?>assets/img/logo_pix.png" alt="QR Code de Pagamento"><br>
                                    <img class="mx-auto mt-1 w-[94px]" src="<?= e($qrCode) ?>" alt="QR Code de Pagamento"><br>
                                    <?php echo '<span class="text-[80%]">Chave PIX: ' . e($chaveFormatada) . '</span>' ;?>
                                </td>
                            </tr>
                        <?php endif ?>
                    <?php } ?>
                </tbody>
            </table>

            <div class="cupom-assinatura">Assinatura do Cliente</div>
        </div>

    <!-- Via Da Empresa  -->
    <?php $totalServico = 0;
$totalProdutos = 0; ?>
    <div id="ViaEmpresa" class="<?php echo (!$configuration['control_2vias']) ? 'hidden' : 'block mt-4 border-t border-dashed border-ink pt-2' ?>">

        <?php if ($emitente == null) { ?>
            <div class="bg-warning-soft p-2 text-warning-ink">
                Você precisa configurar os dados do emitente. &gt;&gt;&gt;<a class="underline" href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>&lt;&lt;&lt;
            </div>
        <?php } else { ?>
            <div class="text-center">
                <img class="mx-auto max-h-[100px]" src="<?php echo e($emitente->url_logo); ?>" alt="">
            </div>
            <div class="mt-1 text-center">
                <span class="text-xs uppercase"><b><?php echo e($emitente->nome); ?></b></span><br>
                <?php if ($emitente->cnpj != "00.000.000/0000-00") { ?><span class="inline-flex items-center gap-1"><?= icon('fingerprint', ['class' => 'size-4']) ?> <?php echo e($emitente->cnpj); ?></span><br><?php } ?>
                <span><?php echo e($emitente->rua . ', ' . $emitente->numero) . '<br>' . e($emitente->bairro . ', ' . $emitente->cidade . ' - ' . $emitente->uf); ?></span><br>
                <span><?php echo e($emitente->email); ?> - <?php echo e($emitente->telefone); ?></span>
            </div>
        <?php } ?>

        <div class="mt-2">
            <span><b>CLIENTE</b><br></span>
            <span><?php echo e($result->nomeCliente);?><br></span>
            <?= !empty($result->contato_cliente) ? '<span>' . e($result->contato_cliente) . ' </span>' : '' ?>
            <?php if ($result->celular_cliente == $result->telefone_cliente) { ?>
                <span><?= e($result->celular_cliente) ?></span><br>
            <?php } else { ?>
                <?= e(!empty($result->telefone_cliente) ? $result->telefone_cliente : "") ?>
                <?= !empty($result->celular_cliente) && !empty($result->telefone_cliente) ? ' / ' : "" ?>
                <?= e(!empty($result->celular_cliente) ? $result->celular_cliente : "") ?><br>
            <?php } ?>
            <?php if (!empty($result->email)) : ?>
                <span><?php echo e($result->email);?></span><br>
            <?php endif; ?>
            <span><?php
            $retorno_end = array_filter([$result->rua, $result->numero, $result->complemento, $result->bairro]);
$endereco = implode(', ', $retorno_end);
if (!empty($endereco)) {
    echo e($endereco) . '<br>';
}
if (!empty($result->cidade) || !empty($result->estado) || !empty($result->cep)) {
    echo '<span>' . e("{$result->cidade} - {$result->estado}, {$result->cep}") . '</span><br>';
}
?></span>
        </div>

        <div class="mt-2 border-t border-hairline-cool pt-1 text-center text-xs">
            <b>N° OS: </b><span><?php echo e($result->idOs);?></span>
            <span class="pl-[5%]"><b>Status: </b><?php echo e($result->status);?></span><br>
            <b>Emissão:</b> <?php echo date('d/m/Y') ?>
        </div>

        <div class="mt-1 text-xs">
            <table class="cupom-tabela">
                <tbody>
                    <?php if ($result->dataInicial != null) { ?>
                        <tr>
                            <td><b>Inicial: </b><?php echo date('d/m/Y', strtotime($result->dataInicial)); ?></td>
                            <td><b>Final: </b><?php echo $result->dataFinal ? date('d/m/Y', strtotime($result->dataFinal)) : ''; ?></td>
                            <td>
                            <?php if ($result->garantia != null) { ?>
                                <b>Garantia: </b><?php echo e($result->garantia . ' dia(s)'); ?>
                            <?php } ?>
                        </td>
                        </tr>
                    <?php } ?>
                    <?php if ($result->descricaoProduto != null) { ?>
                        <tr>
                            <td colspan="5"><b>Descrição: </b><?php echo printSafeHtml($result->descricaoProduto) ?></td>
                        </tr>
                    <?php } ?>
                    <?php if ($result->defeito != null) { ?>
                        <tr>
                            <td colspan="5"><b>Defeito Apresentado: </b><?php echo printSafeHtml($result->defeito) ?></td>
                        </tr>
                    <?php } ?>
                    <?php if ($result->observacoes != null) { ?>
                        <tr>
                            <td colspan="5"><b>Observações: </b><?php echo printSafeHtml($result->observacoes) ?></td>
                        </tr>
                    <?php } ?>
                    <?php if ($result->status != 'Aberto') { ?>
                        <?php if ($result->laudoTecnico != null) { ?>
                            <tr>
                                <td colspan="5"><b>Laudo Técnico: </b><?php echo printSafeHtml($result->laudoTecnico) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <?php if ($result->garantias_id != null) { ?>
                        <tr>
                            <td colspan="5">
                                <strong>Termo de Garantia: </strong><br>
                                <?php echo printSafeHtml($result->textoGarantia) ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <?php if ($produtos != null) { ?>
                <table class="cupom-tabela cupom-tabela-grade" id="tblProdutos">
                    <thead>
                        <tr class="bg-surface-subtle">
                            <th class="text-left">Qtd</th>
                            <th class="text-left">Produto</th>
                            <th class="text-left">Unitário</th>
                            <th class="text-left">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
            foreach ($produtos as $p) {
                $totalProdutos = $totalProdutos + $p->subTotal;
                echo '<tr>';
                echo '<td>' . e($p->quantidade) . '</td>';
                echo '<td>' . e($p->descricao) . osDescontoItemNota($p) . '</td>';
                echo '<td>R$ ' . e($p->preco ?: $p->precoVenda) . '</td>';
                echo '<td>R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                echo '</tr>';
            } ?>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                            <td><strong>R$ <?php echo number_format($totalProdutos, 2, ',', '.'); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            <?php } ?>

            <?php if ($servicos != null) { ?>
                <table class="cupom-tabela cupom-tabela-grade">
                    <thead>
                        <tr class="bg-surface-subtle">
                            <th class="text-left">Qtd</th>
                            <th class="text-left">Serviço</th>
                            <th class="text-left">Unitário</th>
                            <th class="text-left">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php setlocale(LC_MONETARY, 'en_US');
                foreach ($servicos as $s) {
                    $preco = $s->preco ?: $s->precoVenda;
                    $subtotal = osValorDoItem($s);
                    $totalServico = $totalServico + $subtotal;
                    echo '<tr>';
                    echo '<td>' . e(($s->quantidade ?: 1)) . '</td>';
                    echo '<td>' . e($s->nome) . osDescontoItemNota($s) . '</td>';
                    echo '<td>R$ ' . e($preco) . '</td>';
                    echo '<td>R$ ' . number_format($subtotal, 2, ',', '.') . '</td>';
                    echo '</tr>';
                } ?>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                            <td><strong>R$ <?php echo number_format($totalServico, 2, ',', '.'); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            <?php } ?>

            <table class="cupom-tabela cupom-tabela-grade">
                <tbody>
                    <tr>
                        <td colspan="5"> <?php
                if ($totalProdutos != 0 || $totalServico != 0) {
                    if ($result->valor_desconto != 0) {
                        echo "<h4 class='cupom-total'>Subtotal: R$ " . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>";
                        echo $result->valor_desconto != 0 ? "<h4 class='cupom-total'> Desconto: R$ " . number_format($result->valor_desconto != 0 ? $result->valor_desconto - ($totalProdutos + $totalServico) : 0.00, 2, ',', '.') . "</h4>" : "";
                        echo $result->valor_desconto != 0 ? "<h4 class='cupom-total'> Total: R$ " . number_format($result->valor_desconto, 2, ',', '.') . "</h4>" : "";
                    } else {
                        echo "<h4 class='cupom-total'>Total: R$ " . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>";
                    }
                } ?>
                        </td>
                    </tr>
                    <?php if ($result->status == 'Finalizado' || $result->status == 'Orçamento') { ?>
                        <?php if ($qrCode) : ?>
                            <tr>
                                <td colspan="5" class="text-center">
                                    <img class="mx-auto mt-3 w-16" src="<?php echo base_url(); ?>assets/img/logo_pix.png" alt="QR Code de Pagamento"><br>
                                    <img class="mx-auto mt-1 w-[94px]" src="<?= e($qrCode) ?>" alt="QR Code de Pagamento"><br>
                                    <?php echo '<span class="text-[80%]">Chave PIX: ' . e($chaveFormatada) . '</span>' ;?>
                                </td>
                            </tr>
                        <?php endif ?>
                    <?php } ?>
                </tbody>
            </table>

            <div class="cupom-assinatura">Assinatura do Recebedor</div>
        </div>

    </div>
</div>
<script src="<?= e(base_url('assets/js/imprimir.js')) ?>"></script>
</body>

</html>
