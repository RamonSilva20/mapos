<?php
/**
 * Compras do cliente (#2848), no padrão das listagens (#2852).
 *
 * @var list<object> $results
 * @var int          $total
 * @var array        $paginacao
 */
?>
<div class="flex flex-col gap-4">
    <header>
        <h1 class="font-display text-heading-xl text-text">Compras</h1>
        <p class="text-caption text-muted" aria-live="polite"><?= e($total === 1 ? '1 compra' : number_format($total, 0, ',', '.') . ' compras') ?></p>
    </header>

    <?= component('data-table', [
        'columns' => conecteColunasCompras(true),
        'rows' => $results,
        'caption' => 'Compras',
        'empty' => component('empty-state', [
            'title' => 'Nenhuma compra ainda',
            'message' => 'As compras feitas na loja aparecem aqui, com a garantia de cada uma.',
            'icon' => 'shopping-cart',
            'class' => 'border-0',
        ]),
    ]) ?>

    <?= component('pagination', $paginacao) ?>
</div>
