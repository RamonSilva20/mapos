<?php
/**
 * Listagem de serviços (#2841), no padrão das listagens (#2852): busca na
 * URL, data-table, estados vazios, ações por linha e exclusão em
 * modal-confirm. "Novo serviço" vem pela topbar.
 *
 * @var list<object>          $results
 * @var array<string, string> $filtros
 * @var int                   $total
 * @var array                 $paginacao
 * @var array{adicionar: bool, editar: bool, excluir: bool} $pode
 */
$temFiltro = $filtros !== [];

$colunas = [
    ['key' => 'idServicos', 'label' => 'Cód.', 'align' => 'right', 'nowrap' => true],
    ['label' => 'Nome', 'class' => 'min-w-40', 'render' => fn ($s) => $pode['editar']
        ? component('link', ['label' => $s->nome, 'href' => site_url('servicos/editar/' . $s->idServicos), 'class' => 'font-medium'])
        : $s->nome],
    ['key' => 'descricao', 'label' => 'Descrição', 'hide_until' => 'md'],
    ['label' => 'Preço', 'align' => 'right', 'nowrap' => true, 'render' => fn ($s) => dinheiro($s->preco)],
    ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => function ($s) use ($pode) {
        $acoes = [];
        if ($pode['editar']) {
            $acoes[] = component('button', ['label' => 'Editar ' . $s->nome, 'icon' => 'pencil', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('servicos/editar/' . $s->idServicos)]);
        }
        if ($pode['excluir']) {
            $acoes[] = component('button', ['label' => 'Excluir ' . $s->nome, 'icon' => 'trash-2', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'class' => 'hover:text-danger-ink', 'attrs' => [
                'data-modal-abrir' => 'excluir-servico',
                'data-valor-id' => (string) $s->idServicos,
                'data-valor-nome' => $s->nome,
            ]]);
        }

        return $acoes;
    }],
];

$vazio = $temFiltro
    ? component('empty-state', [
        'title' => 'Nenhum serviço encontrado',
        'message' => 'Nada corresponde à busca. Confira o texto ou limpe a busca.',
        'icon' => 'search-x',
        'action' => component('button', ['label' => 'Limpar busca', 'variant' => 'outline', 'href' => site_url('servicos')]),
        'class' => 'border-0',
    ])
    : component('empty-state', [
        'title' => 'Nenhum serviço cadastrado',
        'message' => 'Os serviços cadastrados aparecem aqui e podem ser usados nas ordens de serviço.',
        'icon' => 'wrench',
        'action' => $pode['adicionar'] ? component('button', ['label' => 'Cadastrar serviço', 'icon' => 'plus', 'variant' => 'outline', 'href' => site_url('servicos/adicionar')]) : null,
        'class' => 'border-0',
    ]);
?>
<div class="flex flex-col gap-4 pt-2 pb-8">
    <header>
        <h1 class="font-display text-heading-xl text-text">Serviços</h1>
        <p class="text-caption text-muted" aria-live="polite">
            <?= e(($total === 1 ? '1 serviço' : number_format($total, 0, ',', '.') . ' serviços') . ($temFiltro ? ($total === 1 ? ' encontrado' : ' encontrados') . ' na busca' : '')) ?>
        </p>
    </header>

    <form method="get" action="<?= e(site_url('servicos')) ?>" role="search" aria-label="Buscar serviços" class="grid items-end gap-3 rounded-xl border border-border bg-surface p-4 sm:grid-cols-[minmax(0,1fr)_auto]">
        <?= component('input', [
            'name' => 'pesquisa',
            'label' => 'Buscar',
            'type' => 'search',
            'value' => $filtros['pesquisa'] ?? null,
            'placeholder' => 'Nome ou descrição',
            'attrs' => ['maxlength' => 100],
        ]) ?>
        <div class="flex gap-2">
            <?= component('button', ['label' => 'Buscar', 'icon' => 'search', 'variant' => 'outline', 'type' => 'submit']) ?>
            <?php if ($temFiltro) { ?>
                <?= component('button', ['label' => 'Limpar', 'icon' => 'x', 'variant' => 'ghost', 'href' => site_url('servicos')]) ?>
            <?php } ?>
        </div>
    </form>

    <?= component('data-table', [
        'columns' => $colunas,
        'rows' => $results,
        'empty' => $vazio,
        'caption' => 'Serviços',
    ]) ?>

    <?= component('pagination', $paginacao) ?>
</div>

<?php if ($pode['excluir']) { ?>
    <form id="form-excluir-servico" method="post" action="<?= e(site_url('servicos/excluir') . listagemQuery($filtros)) ?>" hidden>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
        <input type="hidden" name="id" value="" data-modal-de="excluir-servico" data-modal-valor="id">
    </form>
    <?= component('modal-confirm', [
        'id' => 'excluir-servico',
        'title' => 'Excluir serviço?',
        // O nome entra pelo modal.js (textContent); aqui só o marcador vazio.
        'message' => [
            new HtmlSeguro('<strong class="font-semibold text-text" data-modal-valor="nome"></strong>'),
            ' será removido do catálogo e também das ordens de serviço em que foi usado. Essa ação não pode ser desfeita.',
        ],
        'confirm_label' => 'Excluir',
        'confirm_attrs' => ['form' => 'form-excluir-servico'],
    ]) ?>
<?php } ?>
