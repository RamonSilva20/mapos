<?php
/**
 * Busca geral (#2855): clientes, OS, produtos e serviços que combinam com o
 * termo. É a tela da busca da topbar. Cada grupo só aparece para quem pode
 * visualizá-lo; as ações de editar, só com a permissão de editar.
 *
 * @var string       $termo
 * @var list<object> $clientes
 * @var list<object> $os
 * @var list<object> $produtos
 * @var list<object> $servicos
 * @var array<string, bool> $pode
 */
$acao = static fn (string $rotulo, string $icone, string $url) => component('button', ['label' => $rotulo, 'icon' => $icone, 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url($url)]);

$grupos = [
    'clientes' => [
        'titulo' => 'Clientes e fornecedores',
        'icone' => 'users',
        'vazio' => 'Nenhum cliente encontrado.',
        'linhas' => $clientes,
        'colunas' => [
            ['key' => 'idClientes', 'label' => 'Cód.', 'align' => 'right', 'nowrap' => true],
            ['label' => 'Nome', 'class' => 'min-w-32 [overflow-wrap:anywhere]', 'render' => fn ($c) => component('link', ['label' => (string) $c->nomeCliente, 'href' => site_url('clientes/visualizar/' . (int) $c->idClientes), 'class' => 'font-medium'])],
            ['label' => 'Documento', 'nowrap' => true, 'hide_until' => 'md', 'render' => fn ($c) => (string) $c->documento],
            ['label' => 'Tipo', 'nowrap' => true, 'render' => fn ($c) => component('pill-status', $c->fornecedor ? ['label' => 'Fornecedor', 'variant' => 'info'] : ['label' => 'Cliente', 'variant' => 'neutral'])],
            ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => fn ($c) => array_filter([
                $acao('Ver ' . $c->nomeCliente, 'eye', 'clientes/visualizar/' . (int) $c->idClientes),
                $pode['editar_cliente'] ? $acao('Editar ' . $c->nomeCliente, 'pencil', 'clientes/editar/' . (int) $c->idClientes) : null,
            ])],
        ],
    ],
    'os' => [
        'titulo' => 'Ordens de serviço',
        'icone' => 'file-text',
        'vazio' => 'Nenhuma OS encontrada.',
        'linhas' => $os,
        'colunas' => [
            ['label' => 'OS', 'nowrap' => true, 'render' => fn ($o) => component('link', ['label' => 'OS #' . (int) $o->idOs, 'href' => site_url('os/visualizar/' . (int) $o->idOs), 'class' => 'font-medium'])],
            ['label' => 'Equipamento', 'class' => 'min-w-32 [overflow-wrap:anywhere]', 'render' => fn ($o) => osTextoCurto($o->descricaoProduto, 60) ?: '—'],
            ['label' => 'Entrada', 'align' => 'right', 'nowrap' => true, 'hide_until' => 'md', 'render' => fn ($o) => dataBr($o->dataInicial)],
            ['label' => 'Status', 'nowrap' => true, 'render' => fn ($o) => component('pill-status', osStatusPill($o->status))],
            ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => fn ($o) => array_filter([
                $acao('Ver OS #' . (int) $o->idOs, 'eye', 'os/visualizar/' . (int) $o->idOs),
                $pode['editar_os'] ? $acao('Editar OS #' . (int) $o->idOs, 'pencil', 'os/editar/' . (int) $o->idOs) : null,
            ])],
        ],
    ],
    'produtos' => [
        'titulo' => 'Produtos',
        'icone' => 'package',
        'vazio' => 'Nenhum produto encontrado.',
        'linhas' => $produtos,
        'colunas' => [
            ['key' => 'idProdutos', 'label' => 'Cód.', 'align' => 'right', 'nowrap' => true],
            ['label' => 'Descrição', 'class' => 'min-w-32 [overflow-wrap:anywhere]', 'render' => fn ($p) => component('link', ['label' => (string) $p->descricao, 'href' => site_url('produtos/visualizar/' . (int) $p->idProdutos), 'class' => 'font-medium'])],
            ['label' => 'Preço', 'align' => 'right', 'nowrap' => true, 'render' => fn ($p) => dinheiro($p->precoVenda)],
            ['label' => 'Estoque', 'align' => 'right', 'nowrap' => true, 'hide_until' => 'md', 'render' => fn ($p) => (string) $p->estoque],
            ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => fn ($p) => array_filter([
                $acao('Ver ' . $p->descricao, 'eye', 'produtos/visualizar/' . (int) $p->idProdutos),
                $pode['editar_produto'] ? $acao('Editar ' . $p->descricao, 'pencil', 'produtos/editar/' . (int) $p->idProdutos) : null,
            ])],
        ],
    ],
    'servicos' => [
        'titulo' => 'Serviços',
        'icone' => 'wrench',
        'vazio' => 'Nenhum serviço encontrado.',
        'linhas' => $servicos,
        'colunas' => [
            ['key' => 'idServicos', 'label' => 'Cód.', 'align' => 'right', 'nowrap' => true],
            ['label' => 'Nome', 'class' => 'min-w-32 [overflow-wrap:anywhere]', 'render' => fn ($s) => (string) $s->nome],
            ['label' => 'Preço', 'align' => 'right', 'nowrap' => true, 'render' => fn ($s) => dinheiro($s->preco)],
            ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => fn ($s) => $pode['editar_servico'] ? $acao('Editar ' . $s->nome, 'pencil', 'servicos/editar/' . (int) $s->idServicos) : ''],
        ],
    ],
];
$visiveis = array_filter($grupos, static fn (string $chave) => $pode[$chave], ARRAY_FILTER_USE_KEY);
$total = array_sum(array_map(static fn (array $g) => count($g['linhas']), $visiveis));
?>
<div class="flex flex-col gap-4 pt-2 pb-8">
    <header>
        <h1 class="font-display text-heading-xl text-text">Busca</h1>
        <p class="text-caption text-muted" aria-live="polite">
            <?= e($termo === '' ? 'Procure clientes, OS, produtos e serviços.' : ($total === 0 ? 'Nada encontrado para “' . $termo . '”.' : ($total === 1 ? '1 resultado' : $total . ' resultados') . ' para “' . $termo . '”.')) ?>
        </p>
    </header>

    <form method="get" action="<?= e(site_url('mapos/pesquisar')) ?>" role="search" aria-label="Buscar no sistema" class="grid items-end gap-3 rounded-xl border border-border bg-surface p-4 sm:grid-cols-[minmax(0,1fr)_auto]">
        <?= component('input', [
            'name' => 'termo',
            'label' => 'Buscar',
            'type' => 'search',
            'value' => $termo !== '' ? $termo : null,
            'placeholder' => 'Nome, documento, telefone, OS, produto ou serviço',
            'attrs' => ['maxlength' => 100, 'autofocus' => $termo === ''],
        ]) ?>
        <?= component('button', ['label' => 'Buscar', 'icon' => 'search', 'variant' => 'outline', 'type' => 'submit']) ?>
    </form>

    <?php if ($termo === '') { ?>
        <?= component('empty-state', ['title' => 'O que você procura?', 'message' => 'Digite um nome, documento, telefone, número de OS, produto ou serviço.', 'icon' => 'search']) ?>
    <?php } elseif ($visiveis === []) { ?>
        <?= component('empty-state', ['title' => 'Sem acesso à busca', 'message' => 'Seu nível de acesso não permite visualizar clientes, OS, produtos ou serviços.', 'icon' => 'search-x']) ?>
    <?php } else { ?>
        <?php foreach ($visiveis as $chave => $grupo) { ?>
            <section class="flex flex-col gap-2" aria-labelledby="<?= e('busca-' . $chave) ?>">
                <h2 id="<?= e('busca-' . $chave) ?>" class="flex items-center gap-2 text-heading-sm text-text">
                    <?= icon($grupo['icone'], ['class' => 'size-5 text-muted']) ?>
                    <?= e($grupo['titulo']) ?>
                    <span class="text-caption font-normal text-muted"><?= e((string) count($grupo['linhas'])) ?></span>
                </h2>
                <?= component('data-table', [
                    'columns' => $grupo['colunas'],
                    'rows' => $grupo['linhas'],
                    'caption' => $grupo['titulo'],
                    'empty' => component('empty-state', ['title' => $grupo['vazio'], 'icon' => 'search-x', 'class' => 'border-0']),
                ]) ?>
            </section>
        <?php } ?>
    <?php } ?>
</div>
