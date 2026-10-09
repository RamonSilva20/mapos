<?php
/**
 * Listagem de vendas (#2843), no padrão das listagens da v5 (#2852, ver
 * views/clientes/clientes.php e views/os/os.php): filtros em GET, data-table
 * que vira cartões abaixo de 640px, empty-state para "nada cadastrado" e
 * "nada encontrado", paginação que mantém os filtros, "Nova venda" na topbar
 * e exclusão com um modal-confirm só, preenchido pelo gatilho da linha
 * (data-valor-*).
 *
 * @var list<object>          $results
 * @var array<string, string> $filtros
 * @var int                   $total
 * @var bool                  $controle_edicao Venda faturada/cancelada continua editável
 * @var array                 $paginacao
 * @var array{adicionar: bool, editar: bool, excluir: bool} $pode
 */
$temFiltro = $filtros !== [];

$colunas = [
    ['key' => 'idVendas', 'label' => 'Nº', 'align' => 'right', 'nowrap' => true],
    ['label' => 'Cliente', 'class' => 'min-w-40', 'render' => fn ($v) => $v->nomeCliente !== null
        ? component('link', ['label' => $v->nomeCliente, 'href' => site_url('clientes/visualizar/' . $v->clientes_id), 'class' => 'font-medium'])
        : 'Cliente removido'],
    ['key' => 'vendedor', 'label' => 'Vendedor', 'nowrap' => true, 'hide_until' => '2xl'],
    ['label' => 'Data', 'align' => 'right', 'nowrap' => true, 'hide_until' => 'lg', 'render' => fn ($v) => dataBr($v->dataVenda)],
    ['label' => 'Garantia', 'nowrap' => true, 'hide_until' => 'xl', 'render' => function ($v) {
        $garantia = vendaGarantiaPill(vendaGarantiaAte($v->dataVenda, $v->garantia));

        return $garantia !== null ? component('pill-status', $garantia) : 'Sem garantia';
    }],
    ['label' => 'Status', 'nowrap' => true, 'render' => fn ($v) => component('pill-status', osStatusPill($v->status))],
    ['label' => 'Total', 'align' => 'right', 'nowrap' => true, 'render' => fn ($v) => dinheiro($v->total)],
    ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => function ($v) use ($pode, $controle_edicao) {
        $numero = 'venda ' . $v->idVendas;
        // Mesma regra da OS: faturada ou cancelada só edita com a configuração ligada.
        $editavel = osEditavel($v, $pode['editar'], $controle_edicao);
        $acoes = [
            component('button', ['label' => 'Ver ' . $numero, 'icon' => 'eye', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('vendas/visualizar/' . $v->idVendas)]),
        ];
        if ($editavel) {
            $acoes[] = component('button', ['label' => 'Editar ' . $numero, 'icon' => 'pencil', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('vendas/editar/' . $v->idVendas)]);
        }
        // Como na v4: excluir exige dVenda e uma venda que ainda pode ser editada.
        if ($pode['excluir'] && $editavel) {
            $acoes[] = component('button', ['label' => 'Excluir ' . $numero, 'icon' => 'trash-2', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'class' => 'hover:text-danger-ink', 'attrs' => [
                'data-modal-abrir' => 'excluir-venda',
                'data-valor-id' => (string) $v->idVendas,
                'data-valor-numero' => (string) $v->idVendas,
                'data-valor-cliente' => (string) ($v->nomeCliente ?? 'cliente removido'),
            ]]);
        }

        return $acoes;
    }],
];

$vazio = $temFiltro
    ? component('empty-state', [
        'title' => 'Nenhuma venda encontrada',
        'message' => 'Nada corresponde aos filtros. Confira a busca ou limpe os filtros.',
        'icon' => 'search-x',
        'action' => component('button', ['label' => 'Limpar filtros', 'variant' => 'outline', 'href' => site_url('vendas')]),
        'class' => 'border-0',
    ])
    : component('empty-state', [
        'title' => 'Nenhuma venda cadastrada',
        'message' => 'As vendas de produtos cadastradas aparecem aqui.',
        'icon' => 'shopping-cart',
        'action' => $pode['adicionar'] ? component('button', ['label' => 'Cadastrar venda', 'icon' => 'plus', 'variant' => 'outline', 'href' => site_url('vendas/adicionar')]) : null,
        'class' => 'border-0',
    ]);

$resumo = ($total === 1 ? '1 venda' : number_format($total, 0, ',', '.') . ' vendas')
    . ($temFiltro ? ($total === 1 ? ' encontrada' : ' encontradas') . ' com os filtros' : '');
?>
<div class="flex flex-col gap-4 pt-2 pb-8">
    <header>
        <h1 class="font-display text-heading-xl text-text">Vendas</h1>
        <p class="text-caption text-muted" aria-live="polite"><?= e($resumo) ?></p>
    </header>

    <form method="get" action="<?= e(site_url('vendas')) ?>" role="search" aria-label="Filtrar vendas" class="grid items-end gap-3 rounded-xl border border-border bg-surface p-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_12rem_10rem_10rem_auto]">
        <?= component('input', [
            'name' => 'pesquisa',
            'label' => 'Buscar',
            'type' => 'search',
            'value' => $filtros['pesquisa'] ?? null,
            'placeholder' => 'Nº, cliente ou documento',
            'attrs' => ['maxlength' => 100],
        ]) ?>
        <?= component('select', [
            'name' => 'status',
            'label' => 'Status',
            'placeholder' => 'Todos',
            'options' => array_combine(array_keys(OS_STATUS_VARIANTES), array_keys(OS_STATUS_VARIANTES)),
            'selected' => $filtros['status'] ?? null,
        ]) ?>
        <?= component('input', [
            'name' => 'de',
            'label' => 'Venda a partir de',
            'type' => 'date',
            'value' => $filtros['de'] ?? null,
        ]) ?>
        <?= component('input', [
            'name' => 'ate',
            'label' => 'Venda até',
            'type' => 'date',
            'value' => $filtros['ate'] ?? null,
        ]) ?>
        <div class="flex gap-2">
            <?= component('button', ['label' => 'Filtrar', 'icon' => 'search', 'variant' => 'outline', 'type' => 'submit']) ?>
            <?php if ($temFiltro) { ?>
                <?= component('button', ['label' => 'Limpar', 'icon' => 'x', 'variant' => 'ghost', 'href' => site_url('vendas')]) ?>
            <?php } ?>
        </div>
    </form>

    <?= component('data-table', [
        'columns' => $colunas,
        'rows' => $results,
        'empty' => $vazio,
        'caption' => 'Vendas',
    ]) ?>

    <?= component('pagination', $paginacao) ?>
</div>

<?php if ($pode['excluir']) { ?>
    <form id="form-excluir-venda" method="post" action="<?= e(site_url('vendas/excluir') . listagemQuery($filtros)) ?>" hidden>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
        <input type="hidden" name="id" value="" data-modal-de="excluir-venda" data-modal-valor="id">
    </form>
    <?= component('modal-confirm', [
        'id' => 'excluir-venda',
        'title' => 'Excluir venda?',
        // Nº e cliente entram pelo modal.js (textContent) a partir do
        // data-valor-* do botão da linha; aqui só os marcadores vazios.
        'message' => [
            'A venda ',
            new HtmlSeguro('<strong class="font-semibold text-text" data-modal-valor="numero"></strong>'),
            ' de ',
            new HtmlSeguro('<strong class="font-semibold text-text" data-modal-valor="cliente"></strong>'),
            ' será removida com os produtos, e a fatura, se houver, é excluída. Essa ação não pode ser desfeita.',
        ],
        'confirm_label' => 'Excluir',
        'confirm_attrs' => ['form' => 'form-excluir-venda'],
    ]) ?>
<?php } ?>
