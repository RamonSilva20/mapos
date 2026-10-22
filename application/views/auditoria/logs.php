<?php
/**
 * Auditoria (#2846): o registro das ações dos usuários, no padrão das
 * listagens (#2852), com busca e período na URL. "Limpar" apaga os registros
 * com mais de 30 dias, por POST confirmado em modal-confirm.
 *
 * @var list<object>          $results
 * @var array<string, string> $filtros
 * @var int                   $total
 * @var array                 $paginacao
 */
$temFiltro = $filtros !== [];
$colunas = [
    ['label' => 'Data', 'align' => 'right', 'nowrap' => true, 'render' => fn ($l) => trim(dataBr($l->data) . ' ' . substr((string) $l->hora, 0, 5))],
    ['key' => 'usuario', 'label' => 'Usuário', 'nowrap' => true],
    ['key' => 'tarefa', 'label' => 'Ação', 'class' => 'min-w-48 [overflow-wrap:anywhere]'],
    ['key' => 'ip', 'label' => 'IP', 'nowrap' => true, 'hide_until' => 'md'],
];
?>
<div class="flex flex-col gap-4 pt-2 pb-8">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="font-display text-heading-xl text-text">Auditoria</h1>
            <p class="text-caption text-muted" aria-live="polite"><?= e(($total === 1 ? '1 registro' : number_format($total, 0, ',', '.') . ' registros') . ($temFiltro ? ' com os filtros' : '')) ?></p>
        </div>
        <?= component('button', ['label' => 'Limpar mais de 30 dias', 'icon' => 'trash-2', 'variant' => 'ghost', 'attrs' => ['data-modal-abrir' => 'limpar-logs']]) ?>
    </header>

    <form method="get" action="<?= e(site_url('auditoria')) ?>" role="search" aria-label="Filtrar registros" class="grid items-end gap-3 rounded-xl border border-border bg-surface p-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_10rem_10rem_auto]">
        <?= component('input', ['name' => 'pesquisa', 'label' => 'Buscar', 'type' => 'search', 'value' => $filtros['pesquisa'] ?? null, 'placeholder' => 'Usuário, ação ou IP', 'attrs' => ['maxlength' => 100]]) ?>
        <?= component('input', ['name' => 'de', 'label' => 'De', 'type' => 'date', 'value' => $filtros['de'] ?? null]) ?>
        <?= component('input', ['name' => 'ate', 'label' => 'Até', 'type' => 'date', 'value' => $filtros['ate'] ?? null]) ?>
        <div class="flex gap-2">
            <?= component('button', ['label' => 'Filtrar', 'icon' => 'search', 'variant' => 'outline', 'type' => 'submit']) ?>
            <?php if ($temFiltro) { ?>
                <?= component('button', ['label' => 'Limpar', 'icon' => 'x', 'variant' => 'ghost', 'href' => site_url('auditoria')]) ?>
            <?php } ?>
        </div>
    </form>

    <?= component('data-table', [
        'columns' => $colunas,
        'rows' => $results,
        'caption' => 'Registro de ações',
        'dense' => true,
        'empty' => component('empty-state', [
            'title' => $temFiltro ? 'Nenhum registro encontrado' : 'Nenhum registro',
            'message' => $temFiltro ? 'Confira a busca ou o período.' : 'As ações dos usuários aparecem aqui.',
            'icon' => 'history',
            'class' => 'border-0',
        ]),
    ]) ?>

    <?= component('pagination', $paginacao) ?>
</div>

<form id="form-limpar-logs" method="post" action="<?= e(site_url('auditoria/clean')) ?>" hidden>
    <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
</form>
<?= component('modal-confirm', [
    'id' => 'limpar-logs',
    'title' => 'Apagar registros antigos?',
    'message' => 'Os registros com mais de 30 dias saem da auditoria. Essa ação não pode ser desfeita.',
    'confirm_label' => 'Apagar',
    'confirm_attrs' => ['form' => 'form-limpar-logs'],
]) ?>
