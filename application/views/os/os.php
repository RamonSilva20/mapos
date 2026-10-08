<?php
/**
 * Listagem de ordens de serviço (#2842), no padrão das listagens da v5 (#2852,
 * ver views/clientes/clientes.php): filtros em GET, data-table que vira
 * cartões abaixo de 640px, empty-state para "nada cadastrado" e "nada
 * encontrado", paginação que mantém os filtros, "Nova OS" na topbar e exclusão
 * com um modal-confirm só, preenchido pelo gatilho da linha (data-valor-*).
 *
 * @var list<object>          $results
 * @var array<string, string> $filtros
 * @var int                   $total
 * @var bool                  $status_ocultos  Sem filtro, só os status marcados em Configurações aparecem
 * @var bool                  $controle_editos OS faturada/cancelada continua editável
 * @var array                 $paginacao
 * @var array{adicionar: bool, editar: bool, excluir: bool} $pode
 */
$temFiltro = $filtros !== [];

$colunas = [
    ['key' => 'idOs', 'label' => 'Nº', 'align' => 'right', 'nowrap' => true],
    ['label' => 'Cliente', 'class' => 'min-w-40', 'render' => fn ($o) => $o->nomeCliente !== null
        ? component('link', ['label' => $o->nomeCliente, 'href' => site_url('clientes/visualizar/' . $o->clientes_id), 'class' => 'font-medium'])
        : 'Cliente removido'],
    // A descrição pode ter HTML (vinha do editor); aqui só um resumo em texto.
    ['label' => 'Equipamento', 'hide_until' => 'lg', 'class' => 'max-w-64', 'render' => fn ($o) => osTextoCurto($o->descricaoProduto, 60)],
    ['key' => 'responsavel', 'label' => 'Responsável', 'nowrap' => true, 'hide_until' => '2xl'],
    ['label' => 'Entrada', 'align' => 'right', 'nowrap' => true, 'hide_until' => 'xl', 'render' => fn ($o) => dataBr($o->dataInicial)],
    ['label' => 'Entrega', 'align' => 'right', 'nowrap' => true, 'hide_until' => 'xl', 'render' => fn ($o) => dataBr($o->dataFinal)],
    ['label' => 'Status', 'nowrap' => true, 'render' => fn ($o) => component('pill-status', osStatusPill($o->status))],
    ['label' => 'Total', 'align' => 'right', 'nowrap' => true, 'render' => fn ($o) => dinheiro($o->total)],
    ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => function ($o) use ($pode, $controle_editos) {
        $numero = 'OS ' . $o->idOs;
        $editavel = osEditavel($o, $pode['editar'], $controle_editos);
        $acoes = [
            component('button', ['label' => 'Ver ' . $numero, 'icon' => 'eye', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('os/visualizar/' . $o->idOs)]),
        ];
        if ($editavel) {
            $acoes[] = component('button', ['label' => 'Editar ' . $numero, 'icon' => 'pencil', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('os/editar/' . $o->idOs)]);
        }
        // Como na v4: excluir exige dOs e uma OS que ainda pode ser editada.
        if ($pode['excluir'] && $editavel) {
            $acoes[] = component('button', ['label' => 'Excluir ' . $numero, 'icon' => 'trash-2', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'class' => 'hover:text-danger-ink', 'attrs' => [
                'data-modal-abrir' => 'excluir-os',
                'data-valor-id' => (string) $o->idOs,
                'data-valor-numero' => (string) $o->idOs,
                'data-valor-cliente' => (string) ($o->nomeCliente ?? 'cliente removido'),
            ]]);
        }

        return $acoes;
    }],
];

$vazio = $temFiltro
    ? component('empty-state', [
        'title' => 'Nenhuma OS encontrada',
        'message' => 'Nada corresponde aos filtros. Confira a busca ou limpe os filtros.',
        'icon' => 'search-x',
        'action' => component('button', ['label' => 'Limpar filtros', 'variant' => 'outline', 'href' => site_url('os')]),
        'class' => 'border-0',
    ])
    : component('empty-state', [
        'title' => $status_ocultos ? 'Nenhuma OS nos status exibidos' : 'Nenhuma OS cadastrada',
        'message' => $status_ocultos
            ? 'A listagem mostra só os status marcados em Configurações. Filtre por outro status para ver as demais.'
            : 'As ordens de serviço cadastradas aparecem aqui.',
        'icon' => 'file-text',
        'action' => $pode['adicionar'] ? component('button', ['label' => 'Cadastrar OS', 'icon' => 'plus', 'variant' => 'outline', 'href' => site_url('os/adicionar')]) : null,
        'class' => 'border-0',
    ]);

$resumo = ($total === 1 ? '1 ordem de serviço' : number_format($total, 0, ',', '.') . ' ordens de serviço')
    . ($temFiltro ? ($total === 1 ? ' encontrada' : ' encontradas') . ' com os filtros' : '')
    . ($status_ocultos ? ' nos status marcados em Configurações' : '');
?>
<div class="flex flex-col gap-4 pt-2 pb-8">
    <header>
        <h1 class="font-display text-heading-xl text-text">Ordens de serviço</h1>
        <p class="text-caption text-muted" aria-live="polite"><?= e($resumo) ?></p>
    </header>

    <form method="get" action="<?= e(site_url('os')) ?>" role="search" aria-label="Filtrar ordens de serviço" class="grid items-end gap-3 rounded-xl border border-border bg-surface p-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_12rem_10rem_10rem_auto]">
        <?= component('input', [
            'name' => 'pesquisa',
            'label' => 'Buscar',
            'type' => 'search',
            'value' => $filtros['pesquisa'] ?? null,
            'placeholder' => 'Nº, cliente, documento ou equipamento',
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
            'label' => 'Entrada a partir de',
            'type' => 'date',
            'value' => $filtros['de'] ?? null,
        ]) ?>
        <?= component('input', [
            'name' => 'ate',
            'label' => 'Entrega até',
            'type' => 'date',
            'value' => $filtros['ate'] ?? null,
        ]) ?>
        <div class="flex gap-2">
            <?= component('button', ['label' => 'Filtrar', 'icon' => 'search', 'variant' => 'outline', 'type' => 'submit']) ?>
            <?php if ($temFiltro) { ?>
                <?= component('button', ['label' => 'Limpar', 'icon' => 'x', 'variant' => 'ghost', 'href' => site_url('os')]) ?>
            <?php } ?>
        </div>
    </form>

    <?= component('data-table', [
        'columns' => $colunas,
        'rows' => $results,
        'empty' => $vazio,
        'caption' => 'Ordens de serviço',
    ]) ?>

    <?= component('pagination', $paginacao) ?>
</div>

<?php if ($pode['excluir']) { ?>
    <form id="form-excluir-os" method="post" action="<?= e(site_url('os/excluir') . listagemQuery($filtros)) ?>" hidden>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
        <input type="hidden" name="id" value="" data-modal-de="excluir-os" data-modal-valor="id">
    </form>
    <?= component('modal-confirm', [
        'id' => 'excluir-os',
        'title' => 'Excluir OS?',
        // Nº e cliente entram pelo modal.js (textContent) a partir do
        // data-valor-* do botão da linha; aqui só os marcadores vazios.
        'message' => [
            'A OS ',
            new HtmlSeguro('<strong class="font-semibold text-text" data-modal-valor="numero"></strong>'),
            ' de ',
            new HtmlSeguro('<strong class="font-semibold text-text" data-modal-valor="cliente"></strong>'),
            ' será removida com os produtos, serviços, anexos e anotações. O estoque dos produtos volta e a fatura, se houver, é excluída. Essa ação não pode ser desfeita.',
        ],
        'confirm_label' => 'Excluir',
        'confirm_attrs' => ['form' => 'form-excluir-os'],
    ]) ?>
<?php } ?>
