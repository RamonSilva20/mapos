<?php
/**
 * Listagem dos termos de garantia (#2845), no padrão das listagens (#2852):
 * busca na URL, data-table, estados vazios, ações por linha e exclusão em
 * modal-confirm. "Novo termo" vem pela topbar.
 *
 * @var list<object>          $results
 * @var array<string, string> $filtros
 * @var int                   $total
 * @var array                 $paginacao
 * @var array{adicionar: bool, editar: bool, excluir: bool} $pode
 */
$temFiltro = $filtros !== [];

$colunas = [
    ['key' => 'idGarantias', 'label' => 'Cód.', 'align' => 'right', 'nowrap' => true],
    ['label' => 'Referência', 'class' => 'min-w-32', 'render' => fn ($g) => component('link', ['label' => trim((string) $g->refGarantia) !== '' ? $g->refGarantia : 'Termo #' . (int) $g->idGarantias, 'href' => site_url('garantias/visualizar/' . (int) $g->idGarantias), 'class' => 'font-medium'])],
    ['label' => 'Termo', 'class' => 'min-w-48 [overflow-wrap:anywhere]', 'hide_until' => 'md', 'render' => fn ($g) => osTextoCurto($g->textoGarantia, 80)],
    ['label' => 'Responsável', 'nowrap' => true, 'hide_until' => 'lg', 'render' => fn ($g) => (string) ($g->nome ?? '')],
    ['label' => 'Data', 'align' => 'right', 'nowrap' => true, 'hide_until' => 'lg', 'render' => fn ($g) => dataBr($g->dataGarantia)],
    ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => function ($g) use ($pode) {
        $nome = trim((string) $g->refGarantia) !== '' ? $g->refGarantia : 'termo #' . (int) $g->idGarantias;
        $acoes = [
            component('button', ['label' => 'Ver ' . $nome, 'icon' => 'eye', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('garantias/visualizar/' . (int) $g->idGarantias)]),
            component('button', ['label' => 'Imprimir ' . $nome, 'icon' => 'printer', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('garantias/imprimir/' . (int) $g->idGarantias), 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]),
        ];
        if ($pode['editar']) {
            $acoes[] = component('button', ['label' => 'Editar ' . $nome, 'icon' => 'pencil', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('garantias/editar/' . (int) $g->idGarantias)]);
        }
        if ($pode['excluir']) {
            $acoes[] = component('button', ['label' => 'Excluir ' . $nome, 'icon' => 'trash-2', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'class' => 'hover:text-danger-ink', 'attrs' => [
                'data-modal-abrir' => 'excluir-garantia',
                'data-valor-id' => (string) $g->idGarantias,
                'data-valor-nome' => $nome,
            ]]);
        }

        return $acoes;
    }],
];

$vazio = $temFiltro
    ? component('empty-state', [
        'title' => 'Nenhum termo encontrado',
        'message' => 'Nada corresponde à busca. Confira o texto ou limpe a busca.',
        'icon' => 'search-x',
        'action' => component('button', ['label' => 'Limpar busca', 'variant' => 'outline', 'href' => site_url('garantias')]),
        'class' => 'border-0',
    ])
    : component('empty-state', [
        'title' => 'Nenhum termo de garantia cadastrado',
        'message' => 'Os termos cadastrados podem ser vinculados às ordens de serviço e vendas, e saem na impressão.',
        'icon' => 'file-text',
        'action' => $pode['adicionar'] ? component('button', ['label' => 'Cadastrar termo', 'icon' => 'plus', 'variant' => 'outline', 'href' => site_url('garantias/adicionar')]) : null,
        'class' => 'border-0',
    ]);
?>
<div class="flex flex-col gap-4 pt-2 pb-8">
    <header>
        <h1 class="font-display text-heading-xl text-text">Termos de garantia</h1>
        <p class="text-caption text-muted" aria-live="polite">
            <?= e(($total === 1 ? '1 termo' : number_format($total, 0, ',', '.') . ' termos') . ($temFiltro ? ($total === 1 ? ' encontrado' : ' encontrados') . ' na busca' : '')) ?>
        </p>
    </header>

    <form method="get" action="<?= e(site_url('garantias')) ?>" role="search" aria-label="Buscar termos de garantia" class="grid items-end gap-3 rounded-xl border border-border bg-surface p-4 sm:grid-cols-[minmax(0,1fr)_auto]">
        <?= component('input', [
            'name' => 'pesquisa',
            'label' => 'Buscar',
            'type' => 'search',
            'value' => $filtros['pesquisa'] ?? null,
            'placeholder' => 'Referência ou texto do termo',
            'attrs' => ['maxlength' => 100],
        ]) ?>
        <div class="flex gap-2">
            <?= component('button', ['label' => 'Buscar', 'icon' => 'search', 'variant' => 'outline', 'type' => 'submit']) ?>
            <?php if ($temFiltro) { ?>
                <?= component('button', ['label' => 'Limpar', 'icon' => 'x', 'variant' => 'ghost', 'href' => site_url('garantias')]) ?>
            <?php } ?>
        </div>
    </form>

    <?= component('data-table', [
        'columns' => $colunas,
        'rows' => $results,
        'empty' => $vazio,
        'caption' => 'Termos de garantia',
    ]) ?>

    <?= component('pagination', $paginacao) ?>
</div>

<?php if ($pode['excluir']) { ?>
    <form id="form-excluir-garantia" method="post" action="<?= e(site_url('garantias/excluir') . listagemQuery($filtros)) ?>" hidden>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
        <input type="hidden" name="id" value="" data-modal-de="excluir-garantia" data-modal-valor="id">
    </form>
    <?= component('modal-confirm', [
        'id' => 'excluir-garantia',
        'title' => 'Excluir termo de garantia?',
        // O nome entra pelo modal.js (textContent); aqui só o marcador vazio.
        'message' => [
            new HtmlSeguro('<strong class="font-semibold text-text" data-modal-valor="nome"></strong>'),
            ' será removido. Se alguma OS usa este termo, a exclusão é recusada. Essa ação não pode ser desfeita.',
        ],
        'confirm_label' => 'Excluir',
        'confirm_attrs' => ['form' => 'form-excluir-garantia'],
    ]) ?>
<?php } ?>
