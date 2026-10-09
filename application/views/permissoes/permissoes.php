<?php
/**
 * Grupos de permissão (#2846): data-table com o nome, quantos usuários usam
 * cada grupo, a criação e a situação em pill-status. "Novo grupo" na topbar;
 * desativar confirma em modal-confirm (os usuários do grupo perdem o acesso).
 * O grupo do usuário logado não pode ser desativado.
 *
 * @var list<object> $results
 * @var int          $grupo_logado
 */
$colunas = [
    ['label' => 'Grupo', 'class' => 'min-w-40 [overflow-wrap:anywhere]', 'render' => fn ($g) => component('link', ['label' => (string) $g->nome, 'href' => site_url('permissoes/editar/' . (int) $g->idPermissao), 'class' => 'font-medium'])],
    ['label' => 'Usuários', 'align' => 'right', 'nowrap' => true, 'render' => fn ($g) => (string) (int) $g->usuarios],
    ['label' => 'Criado em', 'align' => 'right', 'nowrap' => true, 'hide_until' => 'md', 'render' => fn ($g) => dataBr($g->data)],
    ['label' => 'Situação', 'nowrap' => true, 'render' => fn ($g) => component('pill-status', (int) $g->situacao === 1 ? ['label' => 'Ativo', 'variant' => 'success'] : ['label' => 'Inativo', 'variant' => 'neutral'])],
    ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => function ($g) use ($grupo_logado) {
        $acoes = [component('button', ['label' => 'Editar ' . $g->nome, 'icon' => 'pencil', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('permissoes/editar/' . (int) $g->idPermissao)])];
        if ((int) $g->situacao === 1 && (int) $g->idPermissao !== $grupo_logado && (int) $g->idPermissao !== PERMISSAO_ADMIN) {
            $acoes[] = component('button', ['label' => 'Desativar ' . $g->nome, 'icon' => 'x', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'class' => 'hover:text-danger-ink', 'attrs' => [
                'data-modal-abrir' => 'desativar-grupo',
                'data-valor-id' => (string) $g->idPermissao,
                'data-valor-nome' => (string) $g->nome,
                'data-valor-usuarios' => (string) (int) $g->usuarios,
            ]]);
        }

        return $acoes;
    }],
];
?>
<div class="flex max-w-4xl flex-col gap-4 pt-2 pb-8">
    <header>
        <h1 class="font-display text-heading-xl text-text">Permissões</h1>
        <p class="text-caption text-muted">Cada usuário pertence a um grupo, que define o que ele pode ver e fazer.</p>
    </header>

    <?= component('data-table', [
        'columns' => $colunas,
        'rows' => $results,
        'caption' => 'Grupos de permissão',
        'empty' => component('empty-state', ['title' => 'Nenhum grupo de permissão', 'message' => 'Crie um grupo para cadastrar usuários.', 'icon' => 'key-round', 'class' => 'border-0']),
    ]) ?>
</div>

<form id="form-desativar-grupo" method="post" action="<?= e(site_url('permissoes/desativar')) ?>" hidden>
    <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
    <input type="hidden" name="id" value="" data-modal-de="desativar-grupo" data-modal-valor="id">
</form>
<?= component('modal-confirm', [
    'id' => 'desativar-grupo',
    'title' => 'Desativar grupo?',
    'message' => [
        'O grupo ',
        new HtmlSeguro('<strong class="font-semibold text-text" data-modal-valor="nome"></strong>'),
        ' sai da lista de grupos dos usuários, e quem está nele (',
        new HtmlSeguro('<span data-modal-valor="usuarios"></span>'),
        ' usuário(s)) perde o acesso ao painel. Dá para reativar em Editar.',
    ],
    'confirm_label' => 'Desativar',
    'confirm_attrs' => ['form' => 'form-desativar-grupo'],
]) ?>
