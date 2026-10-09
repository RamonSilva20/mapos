<?php
/**
 * Listagem de cobranças (#2844), no padrão das listagens da v5 (#2852, ver
 * views/clientes/clientes.php): filtros em GET (busca, status do gateway e
 * tipo), data-table que vira cartões abaixo de 640px, empty-state para "nada
 * ainda" e "nada encontrado" e paginação que mantém os filtros.
 *
 * As ações da linha mudam dados no gateway, então são POST com o token CSRF
 * (na v4, atualizar e e-mail eram links GET):
 *
 * - atualizar e enviar por e-mail: botões de envio do formulário oculto
 *   form-acao-cobranca, com formaction apontando para a cobrança;
 * - confirmar pagamento, cancelar e excluir: um modal por ação, preenchido
 *   pelo botão da linha (data-valor-*), com o id num formulário oculto.
 *
 * @var list<object>          $results
 * @var array<string, string> $filtros
 * @var int                   $total
 * @var array                 $paginacao
 * @var array<string, string> $status_opcoes
 * @var array                 $gateways
 * @var array{editar: bool, excluir: bool} $pode
 */
$temFiltro = $filtros !== [];
$query = listagemQuery($filtros);

$colunas = [
    ['key' => 'idCobranca', 'label' => 'Nº', 'align' => 'right', 'nowrap' => true],
    ['label' => 'Cliente', 'class' => 'min-w-36 [overflow-wrap:anywhere]', 'render' => fn ($c) => (string) ($c->nomeCliente ?? '')],
    ['label' => 'Referência', 'nowrap' => true, 'render' => function ($c) {
        if (! empty($c->os_id)) {
            return component('link', ['label' => 'OS #' . (int) $c->os_id, 'href' => site_url('os/visualizar/' . (int) $c->os_id)]);
        }
        if (! empty($c->vendas_id)) {
            return component('link', ['label' => 'Venda #' . (int) $c->vendas_id, 'href' => site_url('vendas/visualizar/' . (int) $c->vendas_id)]);
        }

        return '—';
    }],
    ['label' => 'Gateway', 'nowrap' => true, 'hide_until' => '2xl', 'render' => fn ($c) => (string) $c->payment_gateway],
    ['label' => 'Método', 'nowrap' => true, 'hide_until' => '2xl', 'render' => fn ($c) => (string) $c->payment_method],
    ['label' => 'Vencimento', 'align' => 'right', 'nowrap' => true, 'render' => fn ($c) => dataBr($c->expire_at)],
    ['label' => 'Situação', 'nowrap' => true, 'render' => fn ($c) => component('pill-status', cobrancaStatusPill($c->status) + [
        'attrs' => ['title' => cobrancaDescricaoDoStatus($gateways, $c->payment_gateway, $c->status)],
    ])],
    ['label' => 'Valor', 'align' => 'right', 'nowrap' => true, 'render' => fn ($c) => dinheiro((float) $c->total / 100)],
    ['label' => 'Ações', 'align' => 'right', 'class' => 'max-w-44 sm:min-w-44', 'render' => function ($c) use ($pode, $query) {
        $nome = 'cobrança ' . $c->idCobranca;
        $acoes = [
            component('button', ['label' => 'Ver ' . $nome, 'icon' => 'eye', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('cobrancas/visualizar/' . $c->idCobranca)]),
        ];
        $link = cobrancaUrlSegura($c->link);
        if ($link !== null && $c->barcode !== null && $c->barcode !== '') {
            $acoes[] = component('button', ['label' => 'Abrir boleto da ' . $nome, 'icon' => 'barcode', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => $link, 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]);
        }
        if ($pode['editar']) {
            $acoes[] = component('button', ['label' => 'Enviar ' . $nome . ' por e-mail', 'icon' => 'mail', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'type' => 'submit', 'attrs' => [
                'form' => 'form-acao-cobranca',
                'formaction' => site_url('cobrancas/enviarEmail/' . $c->idCobranca) . $query,
            ]]);
            $acoes[] = component('button', ['label' => 'Atualizar status da ' . $nome, 'icon' => 'history', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'type' => 'submit', 'attrs' => [
                'form' => 'form-acao-cobranca',
                'formaction' => site_url('cobrancas/atualizar/' . $c->idCobranca) . $query,
            ]]);
            $acoes[] = component('button', ['label' => 'Confirmar pagamento da ' . $nome, 'icon' => 'circle-check', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'attrs' => [
                'data-modal-abrir' => 'confirmar-pagamento',
                'data-valor-id' => (string) $c->idCobranca,
                'data-valor-nome' => '#' . $c->idCobranca,
            ]]);
            $acoes[] = component('button', ['label' => 'Cancelar ' . $nome, 'icon' => 'x', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'class' => 'hover:text-danger-ink', 'attrs' => [
                'data-modal-abrir' => 'cancelar-cobranca',
                'data-valor-id' => (string) $c->idCobranca,
                'data-valor-nome' => '#' . $c->idCobranca,
            ]]);
        }
        if ($pode['excluir']) {
            $acoes[] = component('button', ['label' => 'Excluir ' . $nome, 'icon' => 'trash-2', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'class' => 'hover:text-danger-ink', 'attrs' => [
                'data-modal-abrir' => 'excluir-cobranca',
                'data-valor-id' => (string) $c->idCobranca,
                'data-valor-nome' => '#' . $c->idCobranca,
            ]]);
        }

        return $acoes;
    }],
];

$vazio = $temFiltro
    ? component('empty-state', [
        'title' => 'Nenhuma cobrança encontrada',
        'message' => 'Nada corresponde aos filtros. Confira a busca ou limpe os filtros.',
        'icon' => 'search-x',
        'action' => component('button', ['label' => 'Limpar filtros', 'variant' => 'outline', 'href' => site_url('cobrancas/cobrancas')]),
        'class' => 'border-0',
    ])
    : component('empty-state', [
        'title' => 'Nenhuma cobrança gerada',
        'message' => 'As cobranças são geradas na tela de uma OS ou de uma venda e aparecem aqui.',
        'icon' => 'banknote',
        'class' => 'border-0',
    ]);

$resumo = ($total === 1 ? '1 cobrança' : number_format($total, 0, ',', '.') . ' cobranças')
    . ($temFiltro ? ($total === 1 ? ' encontrada' : ' encontradas') . ' com os filtros' : '');
?>
<div class="flex flex-col gap-4 pt-2 pb-8">
    <header>
        <h1 class="font-display text-heading-xl text-text">Cobranças</h1>
        <p class="text-caption text-muted" aria-live="polite"><?= e($resumo) ?></p>
    </header>

    <form method="get" action="<?= e(site_url('cobrancas/cobrancas')) ?>" role="search" aria-label="Filtrar cobranças" class="grid items-end gap-3 rounded-xl border border-border bg-surface p-4 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_14rem_10rem_auto]">
        <?= component('input', [
            'name' => 'pesquisa',
            'label' => 'Buscar',
            'type' => 'search',
            'value' => $filtros['pesquisa'] ?? null,
            'placeholder' => 'Nº da cobrança, id no gateway ou cliente',
            'attrs' => ['maxlength' => 100],
        ]) ?>
        <?= component('select', [
            'name' => 'status',
            'label' => 'Situação',
            'placeholder' => 'Todas',
            'options' => $status_opcoes,
            'selected' => $filtros['status'] ?? null,
        ]) ?>
        <?= component('select', [
            'name' => 'tipo',
            'label' => 'Origem',
            'placeholder' => 'Todas',
            'options' => ['os' => 'Ordem de serviço', 'venda' => 'Venda'],
            'selected' => $filtros['tipo'] ?? null,
        ]) ?>
        <div class="flex gap-2">
            <?= component('button', ['label' => 'Filtrar', 'icon' => 'search', 'variant' => 'outline', 'type' => 'submit']) ?>
            <?php if ($temFiltro) { ?>
                <?= component('button', ['label' => 'Limpar', 'icon' => 'x', 'variant' => 'ghost', 'href' => site_url('cobrancas/cobrancas')]) ?>
            <?php } ?>
        </div>
    </form>

    <?= component('data-table', [
        'columns' => $colunas,
        'rows' => $results,
        'empty' => $vazio,
        'caption' => 'Cobranças',
    ]) ?>

    <?= component('pagination', $paginacao) ?>
</div>

<form id="form-acao-cobranca" method="post" action="<?= e(site_url('cobrancas/cobrancas')) ?>" hidden>
    <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
</form>

<?php if ($pode['editar']) { ?>
    <form id="form-confirmar-pagamento" method="post" action="<?= e(site_url('cobrancas/confirmarPagamento') . $query) ?>" hidden>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
        <input type="hidden" name="id" value="" data-modal-de="confirmar-pagamento" data-modal-valor="id">
    </form>
    <?= component('modal', [
        'id' => 'confirmar-pagamento',
        'title' => 'Confirmar pagamento?',
        'size' => 'sm',
        'body' => [
            'A cobrança ',
            new HtmlSeguro('<strong class="font-semibold text-text" data-modal-valor="nome"></strong>'),
            ' será marcada como paga no gateway.',
        ],
        'footer' => [
            component('button', ['label' => 'Voltar', 'variant' => 'ghost', 'attrs' => ['data-modal-fechar' => true]]),
            component('button', ['label' => 'Confirmar', 'icon' => 'circle-check', 'type' => 'submit', 'attrs' => ['form' => 'form-confirmar-pagamento', 'data-rotulo-carregando' => 'Confirmando…']]),
        ],
    ]) ?>

    <form id="form-cancelar-cobranca" method="post" action="<?= e(site_url('cobrancas/cancelar') . $query) ?>" hidden>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
        <input type="hidden" name="id" value="" data-modal-de="cancelar-cobranca" data-modal-valor="id">
    </form>
    <?= component('modal-confirm', [
        'id' => 'cancelar-cobranca',
        'title' => 'Cancelar cobrança?',
        'message' => [
            'A cobrança ',
            new HtmlSeguro('<strong class="font-semibold text-text" data-modal-valor="nome"></strong>'),
            ' será cancelada no gateway e o cliente não poderá mais pagá-la.',
        ],
        'confirm_label' => 'Cancelar cobrança',
        'cancel_label' => 'Voltar',
        'confirm_attrs' => ['form' => 'form-cancelar-cobranca'],
    ]) ?>
<?php } ?>

<?php if ($pode['excluir']) { ?>
    <form id="form-excluir-cobranca" method="post" action="<?= e(site_url('cobrancas/excluir') . $query) ?>" hidden>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
        <input type="hidden" name="id" value="" data-modal-de="excluir-cobranca" data-modal-valor="id">
    </form>
    <?= component('modal-confirm', [
        'id' => 'excluir-cobranca',
        'title' => 'Excluir cobrança?',
        'message' => [
            'A cobrança ',
            new HtmlSeguro('<strong class="font-semibold text-text" data-modal-valor="nome"></strong>'),
            ' será cancelada no gateway e removida do Map-OS. Essa ação não pode ser desfeita.',
        ],
        'confirm_label' => 'Excluir',
        'confirm_attrs' => ['form' => 'form-excluir-cobranca'],
    ]) ?>
<?php } ?>
