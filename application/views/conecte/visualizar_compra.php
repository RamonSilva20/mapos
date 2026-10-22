<?php
/**
 * Compra do cliente (#2848). Só leitura: status, valores
 * (vendas/partes/totais), dados da venda, produtos (vendas/partes/produtos,
 * sem as ações do painel), as observações ao cliente e o PIX. As observações
 * internas da venda não aparecem aqui.
 *
 * @var object      $venda
 * @var array       $totais
 * @var list<object> $produtos
 * @var string|null $garantia_ate
 * @var array|null  $pix
 */
$idVenda = (int) $venda->idVendas;
$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';
$garantia = vendaGarantiaPill($garantia_ate);

$dados = array_filter([
    'Data da compra' => dataBr($venda->dataVenda),
    'Vendedor' => (string) $venda->nome,
    'Garantia' => $garantia === null ? null : (int) $venda->garantia . ' dias, ' . lcfirst($garantia['label']),
], static fn ($valor) => $valor !== null && $valor !== '');

$observacoes = trim(strip_tags((string) $venda->observacoes_cliente)) !== '' ? osTextoExibicao($venda->observacoes_cliente) : null;
?>
<div class="flex flex-col gap-4">
    <div>
        <?= component('button', ['label' => 'Compras', 'icon' => 'chevron-left', 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('mine/compras'), 'class' => '-ml-3']) ?>
    </div>

    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="font-display text-heading-xl text-text"><?= e('Venda #' . $idVenda) ?></h1>
                <?= component('pill-status', osStatusPill($venda->status)) ?>
            </div>
            <p class="text-caption text-muted"><?= e('Compra de ' . dataBr($venda->dataVenda)) ?></p>
        </div>
        <?= component('button', ['label' => 'Imprimir', 'icon' => 'printer', 'variant' => 'outline', 'href' => site_url('mine/imprimirCompra/' . $idVenda), 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]) ?>
    </header>

    <?php $this->load->view('vendas/partes/totais'); ?>

    <div class="grid gap-4 lg:grid-cols-3">
        <section class="<?= e($caixa) ?>" aria-labelledby="secao-dados">
            <h2 id="secao-dados" class="text-heading-sm text-text">Dados da compra</h2>
            <dl class="mt-3 flex flex-col gap-3">
                <?php foreach ($dados as $rotulo => $valor) { ?>
                    <div>
                        <dt class="text-caption text-muted"><?= e($rotulo) ?></dt>
                        <dd class="text-body-md [overflow-wrap:anywhere] text-text"><?= e($valor) ?></dd>
                    </div>
                <?php } ?>
            </dl>
        </section>
        <section class="<?= e($caixa) ?> lg:col-span-2" aria-labelledby="secao-observacoes">
            <h2 id="secao-observacoes" class="text-heading-sm text-text">Observações</h2>
            <?php if ($observacoes === null) { ?>
                <p class="mt-3 text-caption text-muted">Nenhuma observação nesta compra.</p>
            <?php } else { ?>
                <div class="mt-3 text-body-md [overflow-wrap:anywhere] text-text"><?= componenteConteudo($observacoes) ?></div>
            <?php } ?>
        </section>
    </div>

    <section class="flex flex-col gap-3" aria-labelledby="secao-produtos">
        <h2 id="secao-produtos" class="text-heading-sm text-text">Produtos</h2>
        <?php $this->load->view('vendas/partes/produtos'); ?>
    </section>

    <?php $this->load->view('conecte/partes/pix'); ?>
</div>
