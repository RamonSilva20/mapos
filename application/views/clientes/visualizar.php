<?php
/**
 * Ficha do cliente (#2841), migrada para os componentes da v5.
 *
 * - Cabeçalho com o nome, o tipo (pill-status) e o documento; "Editar
 *   cliente" é a ação principal, na topbar; "Excluir" fica no cabeçalho, em
 *   ghost, e confirma num modal-confirm.
 * - Abas por link (?aba=dados|os|vendas), com o total de cada lista: só a
 *   aba aberta consulta as OS ou as vendas.
 * - OS e vendas mais recentes em data-table, com status em pill-status e as
 *   ações conforme as permissões de OS e de vendas.
 *
 * @var object $cliente
 * @var string $aba          dados | os | vendas
 * @var int    $total_os
 * @var int    $total_vendas
 * @var list<object> $os
 * @var list<object> $vendas
 * @var array<string, bool> $pode
 */
$id = (int) $cliente->idClientes;
$url = static fn (string $aba) => site_url('clientes/visualizar/' . $id) . ($aba === 'dados' ? '' : listagemQuery(['aba' => $aba]));
// Descrição da OS (HTML do editor) como texto curto, cortando por caractere.
$texto = static fn ($html, int $limite = 90) => mb_strimwidth(trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8'))), 0, $limite, '…', 'UTF-8');
$campos = static fn (array $itens) => array_filter($itens, static fn ($valor) => $valor !== null && $valor !== '');

$endereco = trim(implode(', ', array_filter([
    trim(($cliente->rua ?? '') . ' ' . ($cliente->numero ?? '')),
    $cliente->complemento ?? '',
    $cliente->bairro ?? '',
])), ', ');
$cidade = trim(implode(' / ', array_filter([$cliente->cidade ?? '', $cliente->estado ?? ''])));

$secoes = [
    'Identificação' => $campos([
        'CPF ou CNPJ' => $cliente->documento,
        'Pessoa de contato' => $cliente->contato,
        'Cadastrado em' => dataBr($cliente->dataCadastro),
    ]),
    'Contato' => $campos([
        'Telefone' => $cliente->telefone,
        'Celular' => $cliente->celular,
        'E-mail' => $cliente->email ? component('link', ['label' => $cliente->email, 'href' => 'mailto:' . $cliente->email]) : null,
    ]),
    'Endereço' => $campos([
        'Logradouro' => $endereco,
        'Cidade' => $cidade,
        'CEP' => $cliente->cep,
    ]),
];
?>
<div class="flex flex-col gap-4 pt-2 pb-8">
    <header class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="font-display text-heading-xl [overflow-wrap:anywhere] text-text"><?= e($cliente->nomeCliente) ?></h1>
                <?= component('pill-status', $cliente->fornecedor ? ['label' => 'Fornecedor', 'variant' => 'info'] : ['label' => 'Cliente', 'variant' => 'neutral']) ?>
            </div>
            <p class="text-caption text-muted"><?= e('Cliente nº ' . $id . ($cliente->documento ? ' · ' . $cliente->documento : '')) ?></p>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php if ($cliente->email) { ?>
                <?= component('button', ['label' => 'Área do cliente', 'icon' => 'key-round', 'variant' => 'ghost', 'href' => site_url('mine') . '?' . http_build_query(['e' => $cliente->email]), 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]) ?>
            <?php } ?>
            <?php if ($pode['excluir']) { ?>
                <?= component('button', ['label' => 'Excluir', 'icon' => 'trash-2', 'variant' => 'ghost', 'class' => 'hover:text-danger-ink', 'attrs' => ['data-modal-abrir' => 'excluir-cliente']]) ?>
            <?php } ?>
        </div>
    </header>

    <?= component('tabs', [
        'label' => 'Seções do cliente',
        'items' => [
            ['label' => 'Dados', 'url' => $url('dados'), 'active' => $aba === 'dados', 'icon' => 'user'],
            ['label' => 'Ordens de serviço', 'url' => $url('os'), 'active' => $aba === 'os', 'icon' => 'file-text', 'count' => $total_os],
            ['label' => 'Vendas', 'url' => $url('vendas'), 'active' => $aba === 'vendas', 'icon' => 'shopping-cart', 'count' => $total_vendas],
        ],
    ]) ?>

    <?php if ($aba === 'dados') { ?>
        <div class="grid gap-4 lg:grid-cols-3">
            <?php foreach ($secoes as $titulo => $itens) { ?>
                <section class="rounded-xl border border-border bg-surface p-4 sm:p-6" aria-label="<?= e($titulo) ?>">
                    <h2 class="text-heading-sm text-text"><?= e($titulo) ?></h2>
                    <?php if ($itens === []) { ?>
                        <p class="mt-3 text-caption text-muted">Nada informado.</p>
                    <?php } else { ?>
                        <dl class="mt-3 flex flex-col gap-3">
                            <?php foreach ($itens as $rotulo => $valor) { ?>
                                <div>
                                    <dt class="text-caption text-muted"><?= e($rotulo) ?></dt>
                                    <dd class="text-body-md [overflow-wrap:anywhere] text-text"><?= componenteConteudo($valor) ?></dd>
                                </div>
                            <?php } ?>
                        </dl>
                    <?php } ?>
                </section>
            <?php } ?>
        </div>
    <?php } elseif ($aba === 'os') { ?>
        <?= component('data-table', [
            'caption' => 'Ordens de serviço do cliente',
            'rows' => $os,
            'empty' => component('empty-state', ['title' => 'Nenhuma ordem de serviço', 'message' => 'As ordens de serviço deste cliente aparecem aqui.', 'icon' => 'file-text', 'class' => 'border-0']),
            'columns' => [
                ['key' => 'idOs', 'label' => 'Nº', 'align' => 'right', 'nowrap' => true],
                ['label' => 'Status', 'nowrap' => true, 'render' => fn ($o) => component('pill-status', osStatusPill($o->status))],
                ['label' => 'Entrada', 'align' => 'right', 'nowrap' => true, 'render' => fn ($o) => dataBr($o->dataInicial)],
                ['label' => 'Previsão', 'align' => 'right', 'nowrap' => true, 'hide_until' => 'md', 'render' => fn ($o) => dataBr($o->dataFinal)],
                ['label' => 'Descrição', 'class' => 'min-w-48', 'render' => fn ($o) => $texto($o->descricaoProduto)],
                ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => function ($o) use ($pode) {
                    $acoes = [];
                    if ($pode['ver_os']) {
                        $acoes[] = component('button', ['label' => 'Ver OS ' . $o->idOs, 'icon' => 'eye', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('os/visualizar/' . $o->idOs)]);
                    }
                    if ($pode['editar_os']) {
                        $acoes[] = component('button', ['label' => 'Editar OS ' . $o->idOs, 'icon' => 'pencil', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('os/editar/' . $o->idOs)]);
                    }

                    return $acoes;
                }],
            ],
        ]) ?>
        <?php if ($total_os > count($os)) { ?>
            <p class="text-caption text-muted"><?= e('Mostrando as ' . count($os) . ' mais recentes de ' . $total_os . '.') ?></p>
        <?php } ?>
    <?php } else { ?>
        <?= component('data-table', [
            'caption' => 'Vendas do cliente',
            'rows' => $vendas,
            'empty' => component('empty-state', ['title' => 'Nenhuma venda', 'message' => 'As vendas para este cliente aparecem aqui.', 'icon' => 'shopping-cart', 'class' => 'border-0']),
            'columns' => [
                ['key' => 'idVendas', 'label' => 'Nº', 'align' => 'right', 'nowrap' => true],
                ['label' => 'Data', 'align' => 'right', 'nowrap' => true, 'render' => fn ($v) => dataBr($v->dataVenda)],
                ['label' => 'Situação', 'nowrap' => true, 'render' => fn ($v) => component('pill-status', vendaFaturadaPill($v->faturado))],
                ['label' => 'Total', 'align' => 'right', 'nowrap' => true, 'render' => fn ($v) => dinheiro($v->valorTotal)],
                ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => function ($v) use ($pode) {
                    $acoes = [];
                    if ($pode['ver_venda']) {
                        $acoes[] = component('button', ['label' => 'Ver venda ' . $v->idVendas, 'icon' => 'eye', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('vendas/visualizar/' . $v->idVendas)]);
                    }
                    if ($pode['editar_venda']) {
                        $acoes[] = component('button', ['label' => 'Editar venda ' . $v->idVendas, 'icon' => 'pencil', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('vendas/editar/' . $v->idVendas)]);
                    }

                    return $acoes;
                }],
            ],
        ]) ?>
        <?php if ($total_vendas > count($vendas)) { ?>
            <p class="text-caption text-muted"><?= e('Mostrando as ' . count($vendas) . ' mais recentes de ' . $total_vendas . '.') ?></p>
        <?php } ?>
    <?php } ?>
</div>

<?php if ($pode['excluir']) { ?>
    <form id="form-excluir-cliente" method="post" action="<?= e(site_url('clientes/excluir')) ?>" hidden>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
        <input type="hidden" name="id" value="<?= e($id) ?>">
    </form>
    <?= component('modal-confirm', [
        'id' => 'excluir-cliente',
        'title' => 'Excluir cliente?',
        'message' => $cliente->nomeCliente . ' e tudo o que estiver ligado a ele (ordens de serviço, vendas e lançamentos) serão removidos. Essa ação não pode ser desfeita.',
        'confirm_label' => 'Excluir',
        'confirm_attrs' => ['form' => 'form-excluir-cliente'],
    ]) ?>
<?php } ?>
