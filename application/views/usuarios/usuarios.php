<?php
/**
 * Listagem de usuários (#2846), no padrão das listagens da v5 (#2852):
 * filtros em GET, data-table que vira cartões abaixo de 640px, empty-state
 * para "nada encontrado", paginação que mantém os filtros, "Novo usuário" na
 * topbar e exclusão com um modal-confirm só (data-valor-* do botão da linha).
 *
 * @var list<object>          $results
 * @var array<string, string> $filtros
 * @var int                   $total
 * @var int                   $logado  Id do usuário logado
 * @var string                $hoje
 * @var array                 $paginacao
 */
$temFiltro = $filtros !== [];

$colunas = [
    ['label' => 'Nome', 'class' => 'min-w-40 [overflow-wrap:anywhere]', 'render' => fn ($u) => component('link', ['label' => (string) $u->nome, 'href' => site_url('usuarios/editar/' . (int) $u->idUsuarios), 'class' => 'font-medium'])],
    ['key' => 'email', 'label' => 'E-mail', 'class' => 'min-w-48 [overflow-wrap:anywhere]', 'hide_until' => 'lg'],
    ['key' => 'telefone', 'label' => 'Telefone', 'nowrap' => true, 'hide_until' => 'xl'],
    ['label' => 'Grupo', 'nowrap' => true, 'render' => fn ($u) => (string) ($u->permissao ?? 'Grupo removido')],
    ['label' => 'Situação', 'nowrap' => true, 'render' => function ($u) use ($hoje) {
        $pill = usuarioSituacaoPill($u, $hoje);
        $expira = dataBr($u->dataExpiracao ?? null);

        return [component('pill-status', $pill), $pill['label'] === 'Ativo' && $expira !== '' ? ' até ' . $expira : ''];
    }],
    ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => function ($u) use ($logado) {
        $acoes = [component('button', ['label' => 'Editar ' . $u->nome, 'icon' => 'pencil', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('usuarios/editar/' . (int) $u->idUsuarios)])];
        if (usuarioPodeSerRemovido((int) $u->idUsuarios, $logado) === null) {
            $acoes[] = component('button', ['label' => 'Excluir ' . $u->nome, 'icon' => 'trash-2', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'class' => 'hover:text-danger-ink', 'attrs' => [
                'data-modal-abrir' => 'excluir-usuario',
                'data-valor-id' => (string) $u->idUsuarios,
                'data-valor-nome' => (string) $u->nome,
            ]]);
        }

        return $acoes;
    }],
];

$resumo = ($total === 1 ? '1 usuário' : number_format($total, 0, ',', '.') . ' usuários') . ($temFiltro ? ($total === 1 ? ' encontrado' : ' encontrados') . ' com os filtros' : '');
?>
<div class="flex flex-col gap-4 pt-2 pb-8">
    <header>
        <h1 class="font-display text-heading-xl text-text">Usuários</h1>
        <p class="text-caption text-muted" aria-live="polite"><?= e($resumo) ?></p>
    </header>

    <form method="get" action="<?= e(site_url('usuarios')) ?>" role="search" aria-label="Filtrar usuários" class="grid items-end gap-3 rounded-xl border border-border bg-surface p-4 sm:grid-cols-[minmax(0,1fr)_12rem_auto]">
        <?= component('input', ['name' => 'pesquisa', 'label' => 'Buscar', 'type' => 'search', 'value' => $filtros['pesquisa'] ?? null, 'placeholder' => 'Nome, e-mail, CPF ou telefone', 'attrs' => ['maxlength' => 100]]) ?>
        <?= component('select', ['name' => 'situacao', 'label' => 'Situação', 'placeholder' => 'Todas', 'options' => ['ativo' => 'Ativos', 'inativo' => 'Inativos'], 'selected' => $filtros['situacao'] ?? null]) ?>
        <div class="flex gap-2">
            <?= component('button', ['label' => 'Filtrar', 'icon' => 'search', 'variant' => 'outline', 'type' => 'submit']) ?>
            <?php if ($temFiltro) { ?>
                <?= component('button', ['label' => 'Limpar', 'icon' => 'x', 'variant' => 'ghost', 'href' => site_url('usuarios')]) ?>
            <?php } ?>
        </div>
    </form>

    <?= component('data-table', [
        'columns' => $colunas,
        'rows' => $results,
        'caption' => 'Usuários',
        'empty' => component('empty-state', [
            'title' => 'Nenhum usuário encontrado',
            'message' => 'Nada corresponde aos filtros. Confira a busca ou limpe os filtros.',
            'icon' => 'search-x',
            'action' => component('button', ['label' => 'Limpar filtros', 'variant' => 'outline', 'href' => site_url('usuarios')]),
            'class' => 'border-0',
        ]),
    ]) ?>

    <?= component('pagination', $paginacao) ?>
</div>

<form id="form-excluir-usuario" method="post" action="<?= e(site_url('usuarios/excluir') . listagemQuery($filtros)) ?>" hidden>
    <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
    <input type="hidden" name="id" value="" data-modal-de="excluir-usuario" data-modal-valor="id">
</form>
<?= component('modal-confirm', [
    'id' => 'excluir-usuario',
    'title' => 'Excluir usuário?',
    'message' => [
        new HtmlSeguro('<strong class="font-semibold text-text" data-modal-valor="nome"></strong>'),
        ' perde o acesso ao painel e sai da lista. Quem tem OS, vendas ou lançamentos não pode ser excluído: desative em vez disso. Essa ação não pode ser desfeita.',
    ],
    'confirm_label' => 'Excluir',
    'confirm_attrs' => ['form' => 'form-excluir-usuario'],
]) ?>
