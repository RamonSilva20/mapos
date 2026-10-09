<?php
/**
 * Listagem de lançamentos financeiros (#2844), no padrão das listagens da v5
 * (#2852, ver views/clientes/clientes.php e views/os/os.php):
 *
 * - cards de resumo (kpi-card) do que os filtros encontram: receitas, despesas,
 *   saldo e o que está vencido;
 * - filtros num formulário GET (período, vencimento, tipo, situação e busca),
 *   guardados na URL; o módulo financeiro/filtros preenche as datas ao trocar
 *   o período;
 * - data-table, que vira cartões abaixo de 640px, com empty-state diferente
 *   para "nada neste período" e "nada com estes filtros";
 * - paginação que mantém os filtros e "Novo lançamento" na topbar;
 * - exclusão com um modal-confirm só, preenchido pelo gatilho da linha
 *   (data-valor-*).
 *
 * @var list<object>          $results
 * @var array<string, string> $filtros     periodo, de e ate sempre presentes
 * @var int                   $total
 * @var array<string, float>  $totais      Financeiro_model::totais()
 * @var array<string, float>  $visao_geral Financeiro_model::visaoGeral()
 * @var array                 $paginacao
 * @var string                $hoje        AAAA-MM-DD
 * @var array{adicionar: bool, editar: bool, excluir: bool, ver_cliente: bool} $pode
 */
$temFiltro = isset($filtros['pesquisa']) || isset($filtros['tipo']) || isset($filtros['status']);
$queryDoPeriodo = listagemQuery(array_intersect_key($filtros, ['periodo' => true, 'de' => true, 'ate' => true]));
$saldo = round($totais['receitas'] - $totais['despesas'], 2);
$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';

$colunas = [
    ['key' => 'idLancamentos', 'label' => 'Nº', 'align' => 'right', 'nowrap' => true],
    ['label' => 'Vencimento', 'align' => 'right', 'nowrap' => true, 'render' => fn ($l) => dataBr($l->data_vencimento)],
    ['label' => 'Tipo', 'nowrap' => true, 'render' => fn ($l) => component('pill-status', $l->tipo === 'receita' ? ['label' => 'Receita', 'variant' => 'info'] : ['label' => 'Despesa', 'variant' => 'neutral'])],
    ['label' => 'Descrição', 'class' => 'min-w-40 [overflow-wrap:anywhere]', 'render' => fn ($l) => (string) $l->descricao],
    ['label' => 'Cliente / fornecedor', 'class' => 'min-w-36 [overflow-wrap:anywhere]', 'hide_until' => 'xl', 'render' => fn ($l) => $l->clientes_id && $pode['ver_cliente']
        ? component('link', ['label' => (string) $l->cliente_fornecedor, 'href' => site_url('clientes/visualizar/' . (int) $l->clientes_id)])
        : (string) $l->cliente_fornecedor],
    ['key' => 'forma_pgto', 'label' => 'Forma', 'nowrap' => true, 'hide_until' => '2xl'],
    ['label' => 'Situação', 'nowrap' => true, 'render' => fn ($l) => component('pill-status', financeiroStatusPill($l, $hoje))],
    ['label' => 'Desconto', 'align' => 'right', 'nowrap' => true, 'hide_until' => '2xl', 'render' => fn ($l) => financeiroDescontoEmReais($l) > 0 ? dinheiro(financeiroDescontoEmReais($l)) : '—'],
    ['label' => 'Valor', 'align' => 'right', 'nowrap' => true, 'render' => fn ($l) => dinheiro(financeiroLiquido($l))],
    ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => function ($l) use ($pode, $filtros) {
        $acoes = [];
        $nome = 'lançamento ' . $l->idLancamentos;
        if ($pode['editar']) {
            $acoes[] = component('button', ['label' => 'Editar ' . $nome, 'icon' => 'pencil', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('financeiro/editar/' . $l->idLancamentos) . listagemQuery($filtros)]);
        }
        if ($pode['excluir']) {
            $acoes[] = component('button', ['label' => 'Excluir ' . $nome, 'icon' => 'trash-2', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'class' => 'hover:text-danger-ink', 'attrs' => [
                'data-modal-abrir' => 'excluir-lancamento',
                'data-valor-id' => (string) $l->idLancamentos,
                'data-valor-descricao' => (string) $l->descricao,
                'data-valor-valor' => dinheiro(financeiroLiquido($l)),
            ]]);
        }

        return $acoes;
    }],
];

$vazio = $temFiltro
    ? component('empty-state', [
        'title' => 'Nenhum lançamento encontrado',
        'message' => 'Nada corresponde aos filtros neste período. Confira a busca, limpe os filtros ou mude o período.',
        'icon' => 'search-x',
        'action' => component('button', ['label' => 'Limpar filtros', 'variant' => 'outline', 'href' => site_url('financeiro/lancamentos') . $queryDoPeriodo]),
        'class' => 'border-0',
    ])
    : component('empty-state', [
        'title' => 'Nenhum lançamento neste período',
        'message' => 'As receitas e despesas com vencimento de ' . dataBr($filtros['de']) . ' a ' . dataBr($filtros['ate']) . ' aparecem aqui.',
        'icon' => 'banknote',
        'action' => $pode['adicionar'] ? component('button', ['label' => 'Cadastrar lançamento', 'icon' => 'plus', 'variant' => 'outline', 'href' => site_url('financeiro/adicionar') . listagemQuery($filtros)]) : null,
        'class' => 'border-0',
    ]);

$resumo = ($total === 1 ? '1 lançamento' : number_format($total, 0, ',', '.') . ' lançamentos')
    . ' com vencimento de ' . dataBr($filtros['de']) . ' a ' . dataBr($filtros['ate'])
    . ($temFiltro ? ', filtrados' : '');
?>
<div class="flex flex-col gap-4 pt-2 pb-8">
    <header>
        <h1 class="font-display text-heading-xl text-text">Lançamentos</h1>
        <p class="text-caption text-muted" aria-live="polite"><?= e($resumo) ?></p>
    </header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <?= component('kpi-card', [
            'label' => 'Receitas',
            'value' => dinheiro($totais['receitas']),
            'icon' => 'circle-dollar-sign',
            'caption' => dinheiro($totais['receitas_pagas']) . ' recebidos · ' . dinheiro($totais['receitas_pendentes']) . ' a receber',
        ]) ?>
        <?= component('kpi-card', [
            'label' => 'Despesas',
            'value' => dinheiro($totais['despesas']),
            'icon' => 'banknote',
            'caption' => dinheiro($totais['despesas_pagas']) . ' pagos · ' . dinheiro($totais['despesas_pendentes']) . ' a pagar',
        ]) ?>
        <?= component('kpi-card', [
            'label' => 'Saldo previsto',
            'value' => dinheiro($saldo),
            'icon' => 'chart-column',
            'caption' => 'Realizado: ' . dinheiro($totais['receitas_pagas'] - $totais['despesas_pagas']),
        ]) ?>
        <?= component('kpi-card', [
            'label' => 'Vencidos',
            'value' => number_format((int) $totais['vencidos'], 0, ',', '.'),
            'icon' => 'triangle-alert',
            'caption' => dinheiro($totais['receitas_vencidas']) . ' a receber · ' . dinheiro($totais['despesas_vencidas']) . ' a pagar',
        ]) ?>
    </div>

    <form method="get" action="<?= e(site_url('financeiro/lancamentos')) ?>" role="search" aria-label="Filtrar lançamentos" <?= js_module('financeiro/filtros') ?>
          class="grid items-end gap-3 rounded-xl border border-border bg-surface p-4 sm:grid-cols-2 lg:grid-cols-[repeat(5,minmax(0,1fr))]">
        <?= component('select', [
            'name' => 'periodo',
            'id' => 'filtro-periodo',
            'label' => 'Período',
            'options' => FINANCEIRO_PERIODOS,
            'selected' => $filtros['periodo'],
        ]) ?>
        <?= component('input', [
            'name' => 'de',
            'id' => 'filtro-de',
            'label' => 'Vencimento de',
            'type' => 'date',
            'value' => $filtros['de'],
        ]) ?>
        <?= component('input', [
            'name' => 'ate',
            'id' => 'filtro-ate',
            'label' => 'Vencimento até',
            'type' => 'date',
            'value' => $filtros['ate'],
        ]) ?>
        <?= component('select', [
            'name' => 'tipo',
            'label' => 'Tipo',
            'placeholder' => 'Todos',
            'options' => FINANCEIRO_TIPOS,
            'selected' => $filtros['tipo'] ?? null,
        ]) ?>
        <?= component('select', [
            'name' => 'status',
            'label' => 'Situação',
            'placeholder' => 'Todas',
            'options' => FINANCEIRO_STATUS,
            'selected' => $filtros['status'] ?? null,
        ]) ?>
        <div class="sm:col-span-2 lg:col-span-4">
            <?= component('input', [
                'name' => 'pesquisa',
                'label' => 'Buscar',
                'type' => 'search',
                'value' => $filtros['pesquisa'] ?? null,
                'placeholder' => 'Nº, cliente ou fornecedor, descrição ou observações',
                'attrs' => ['maxlength' => 100],
            ]) ?>
        </div>
        <div class="flex gap-2">
            <?= component('button', ['label' => 'Filtrar', 'icon' => 'search', 'variant' => 'outline', 'type' => 'submit']) ?>
            <?php if ($temFiltro) { ?>
                <?= component('button', ['label' => 'Limpar', 'icon' => 'x', 'variant' => 'ghost', 'href' => site_url('financeiro/lancamentos') . $queryDoPeriodo]) ?>
            <?php } ?>
        </div>
    </form>

    <?= component('data-table', [
        'columns' => $colunas,
        'rows' => $results,
        'empty' => $vazio,
        'caption' => 'Lançamentos financeiros',
    ]) ?>

    <?= component('pagination', $paginacao) ?>

    <section class="<?= e($caixa) ?>" aria-labelledby="secao-visao-geral">
        <h2 id="secao-visao-geral" class="text-heading-sm text-text">Visão geral</h2>
        <p class="mt-1 text-caption text-muted">Todos os lançamentos, de qualquer período.</p>
        <dl class="mt-3 grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div>
                <dt class="text-caption text-muted">Saldo realizado</dt>
                <dd class="text-body-md font-medium tabular-nums text-text"><?= e(dinheiro($visao_geral['saldo_realizado'])) ?></dd>
                <dd class="text-caption text-muted">Receitas pagas menos despesas pagas</dd>
            </div>
            <div>
                <dt class="text-caption text-muted">A receber</dt>
                <dd class="text-body-md font-medium tabular-nums text-text"><?= e(dinheiro($visao_geral['a_receber'])) ?></dd>
                <dd class="text-caption text-muted">Receitas pendentes</dd>
            </div>
            <div>
                <dt class="text-caption text-muted">A pagar</dt>
                <dd class="text-body-md font-medium tabular-nums text-text"><?= e(dinheiro($visao_geral['a_pagar'])) ?></dd>
                <dd class="text-caption text-muted">Despesas pendentes</dd>
            </div>
            <div>
                <dt class="text-caption text-muted">Descontos dados</dt>
                <dd class="text-body-md font-medium tabular-nums text-text"><?= e(dinheiro($visao_geral['descontos'])) ?></dd>
                <dd class="text-caption text-muted">Pagos e pendentes</dd>
            </div>
        </dl>
    </section>
</div>

<?php if ($pode['excluir']) { ?>
    <form id="form-excluir-lancamento" method="post" action="<?= e(site_url('financeiro/excluirLancamento') . listagemQuery($filtros)) ?>" hidden>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
        <input type="hidden" name="id" value="" data-modal-de="excluir-lancamento" data-modal-valor="id">
    </form>
    <?= component('modal-confirm', [
        'id' => 'excluir-lancamento',
        'title' => 'Excluir lançamento?',
        // Descrição e valor entram pelo modal.js (textContent) a partir do
        // data-valor-* do botão da linha; aqui só os marcadores vazios.
        'message' => [
            new HtmlSeguro('<strong class="font-semibold text-text" data-modal-valor="descricao"></strong>'),
            ' (',
            new HtmlSeguro('<span data-modal-valor="valor"></span>'),
            ') será removido do financeiro. Se for a fatura de uma venda ou de uma OS, ela volta a não faturada. Essa ação não pode ser desfeita.',
        ],
        'confirm_label' => 'Excluir',
        'confirm_attrs' => ['form' => 'form-excluir-lancamento'],
    ]) ?>
<?php } ?>
