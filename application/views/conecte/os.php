<?php
/**
 * Ordens de serviço do cliente (#2848), no padrão das listagens (#2852):
 * data-table que vira cartões abaixo de 640px, paginação do componente e
 * "Abrir OS" como ação principal, na Top Nav.
 *
 * @var list<object> $results
 * @var int          $total
 * @var array        $paginacao
 */
?>
<div class="flex flex-col gap-4">
    <header>
        <h1 class="font-display text-heading-xl text-text">Ordens de serviço</h1>
        <p class="text-caption text-muted" aria-live="polite"><?= e($total === 1 ? '1 ordem de serviço' : number_format($total, 0, ',', '.') . ' ordens de serviço') ?></p>
    </header>

    <?= component('data-table', [
        'columns' => conecteColunasOs(true),
        'rows' => $results,
        'caption' => 'Ordens de serviço',
        'empty' => component('empty-state', [
            'title' => 'Nenhuma ordem de serviço ainda',
            'message' => 'Precisa de conserto ou manutenção? Abra uma OS e a assistência entra em contato.',
            'icon' => 'wrench',
            'action' => component('button', ['label' => 'Abrir OS', 'icon' => 'plus', 'variant' => 'outline', 'href' => site_url('mine/adicionarOs')]),
            'class' => 'border-0',
        ]),
    ]) ?>

    <?= component('pagination', $paginacao) ?>
</div>
