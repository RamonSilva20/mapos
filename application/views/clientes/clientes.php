<?php
/**
 * Listagem de clientes e fornecedores (#2841, #2852): primeira tela de módulo
 * migrada para os componentes da v5 (legacy_assets = false).
 *
 * Padrão das listagens:
 * - cabeçalho com o título e o total encontrado;
 * - filtros num formulário GET (a URL guarda os filtros; "Limpar" volta à
 *   listagem sem eles);
 * - data-table, que vira cartões abaixo de 640px, com empty-state diferente
 *   para "nada cadastrado" e "nada encontrado com estes filtros";
 * - paginação que mantém os filtros;
 * - a ação principal (Novo cliente) na topbar, pelo controller;
 * - exclusão com modal-confirm: um modal só, preenchido pelo gatilho da linha
 *   (data-valor-*), e um formulário POST com o token CSRF.
 *
 * @var list<object>          $results
 * @var array<string, string> $filtros
 * @var int                   $total
 * @var array                 $paginacao
 * @var array{adicionar: bool, editar: bool, excluir: bool} $pode
 */
$temFiltro = $filtros !== [];

$colunas = [
    ['key' => 'idClientes', 'label' => 'Cód.', 'align' => 'right', 'nowrap' => true],
    ['label' => 'Nome', 'class' => 'min-w-40', 'render' => fn ($c) => component('link', ['label' => $c->nomeCliente, 'href' => site_url('clientes/visualizar/' . $c->idClientes), 'class' => 'font-medium'])],
    ['key' => 'documento', 'label' => 'CPF/CNPJ', 'nowrap' => true, 'hide_until' => '2xl'],
    ['label' => 'Telefone', 'nowrap' => true, 'render' => fn ($c) => $c->celular ?: $c->telefone],
    // E-mail longo trunca com reticências na tabela (o valor completo fica no
    // title); no cartão do celular, quebra.
    ['label' => 'E-mail', 'hide_until' => 'xl', 'render' => fn ($c) => $c->email ? component('link', [
        'label' => $c->email,
        'href' => 'mailto:' . $c->email,
        'class' => 'block max-w-52 truncate max-sm:max-w-none max-sm:whitespace-normal',
        'attrs' => ['title' => $c->email],
    ]) : ''],
    ['label' => 'Tipo', 'nowrap' => true, 'render' => fn ($c) => component('pill-status', $c->fornecedor ? ['label' => 'Fornecedor', 'variant' => 'info'] : ['label' => 'Cliente', 'variant' => 'neutral'])],
    ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => function ($c) use ($pode) {
        $acoes = [
            component('button', ['label' => 'Ver ' . $c->nomeCliente, 'icon' => 'eye', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('clientes/visualizar/' . $c->idClientes)]),
        ];
        if ($c->email) {
            $acoes[] = component('button', ['label' => 'Área do cliente de ' . $c->nomeCliente, 'icon' => 'key-round', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('mine') . '?' . http_build_query(['e' => $c->email]), 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]);
        }
        if ($pode['editar']) {
            $acoes[] = component('button', ['label' => 'Editar ' . $c->nomeCliente, 'icon' => 'pencil', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('clientes/editar/' . $c->idClientes)]);
        }
        if ($pode['excluir']) {
            $acoes[] = component('button', ['label' => 'Excluir ' . $c->nomeCliente, 'icon' => 'trash-2', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'class' => 'hover:text-danger-ink', 'attrs' => [
                'data-modal-abrir' => 'excluir-cliente',
                'data-valor-id' => (string) $c->idClientes,
                'data-valor-nome' => $c->nomeCliente,
            ]]);
        }

        return $acoes;
    }],
];

$vazio = $temFiltro
    ? component('empty-state', [
        'title' => 'Nenhum cliente encontrado',
        'message' => 'Nada corresponde aos filtros. Confira a busca ou limpe os filtros.',
        'icon' => 'search-x',
        'action' => component('button', ['label' => 'Limpar filtros', 'variant' => 'outline', 'href' => site_url('clientes')]),
        'class' => 'border-0',
    ])
    : component('empty-state', [
        'title' => 'Nenhum cliente cadastrado',
        'message' => 'Os clientes e fornecedores cadastrados aparecem aqui.',
        'icon' => 'users',
        'action' => $pode['adicionar'] ? component('button', ['label' => 'Cadastrar cliente', 'icon' => 'plus', 'variant' => 'outline', 'href' => site_url('clientes/adicionar')]) : null,
        'class' => 'border-0',
    ]);
?>
<div class="flex flex-col gap-4 pt-2 pb-8">
    <header>
        <h1 class="font-display text-heading-xl text-text">Clientes e fornecedores</h1>
        <p class="text-caption text-muted" aria-live="polite">
            <?= e(($total === 1 ? '1 cadastro' : number_format($total, 0, ',', '.') . ' cadastros') . ($temFiltro ? ($total === 1 ? ' encontrado' : ' encontrados') . ' com os filtros' : '')) ?>
        </p>
    </header>

    <form method="get" action="<?= e(site_url('clientes')) ?>" role="search" aria-label="Filtrar clientes" class="grid items-end gap-3 rounded-xl border border-border bg-surface p-4 sm:grid-cols-[minmax(0,1fr)_12rem_auto]">
        <?= component('input', [
            'name' => 'pesquisa',
            'label' => 'Buscar',
            'type' => 'search',
            'value' => $filtros['pesquisa'] ?? null,
            'placeholder' => 'Nome, documento, e-mail ou telefone',
            'attrs' => ['maxlength' => 100],
        ]) ?>
        <?= component('select', [
            'name' => 'tipo',
            'label' => 'Tipo',
            'placeholder' => 'Todos',
            'options' => ['cliente' => 'Clientes', 'fornecedor' => 'Fornecedores'],
            'selected' => $filtros['tipo'] ?? null,
        ]) ?>
        <div class="flex gap-2">
            <?= component('button', ['label' => 'Filtrar', 'icon' => 'search', 'variant' => 'outline', 'type' => 'submit']) ?>
            <?php if ($temFiltro) { ?>
                <?= component('button', ['label' => 'Limpar', 'icon' => 'x', 'variant' => 'ghost', 'href' => site_url('clientes')]) ?>
            <?php } ?>
        </div>
    </form>

    <?= component('data-table', [
        'columns' => $colunas,
        'rows' => $results,
        'empty' => $vazio,
        'caption' => 'Clientes e fornecedores',
    ]) ?>

    <?= component('pagination', $paginacao) ?>
</div>

<?php if ($pode['excluir']) { ?>
    <form id="form-excluir-cliente" method="post" action="<?= e(site_url('clientes/excluir') . listagemQuery($filtros)) ?>" hidden>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
        <input type="hidden" name="id" value="" data-modal-de="excluir-cliente" data-modal-valor="id">
    </form>
    <?= component('modal-confirm', [
        'id' => 'excluir-cliente',
        'title' => 'Excluir cliente?',
        // O nome entra pelo modal.js (textContent) a partir do data-valor-nome
        // do botão da linha; aqui só o marcador vazio, sem dado do usuário.
        'message' => [
            new HtmlSeguro('<strong class="font-semibold text-text" data-modal-valor="nome"></strong>'),
            ' e tudo o que estiver ligado a ele (ordens de serviço, vendas e lançamentos) serão removidos. Essa ação não pode ser desfeita.',
        ],
        'confirm_label' => 'Excluir',
        'confirm_attrs' => ['form' => 'form-excluir-cliente'],
    ]) ?>
<?php } ?>
