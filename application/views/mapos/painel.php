<?php
/**
 * Painel inicial (#2847), com os componentes da v5.
 *
 * - Cards de resumo (kpi-card): OS em andamento, orçamentos, vendas em aberto
 *   e saldo do mês. Cada um só com a permissão de ver o módulo.
 * - Agenda das entregas de OS (FullCalendar 6), com filtro de status; o
 *   evento abre um modal com os dados da OS.
 * - Balanço do ano (receitas, despesas e saldo por mês) e OS por status,
 *   em Chart.js 4. Os números vão para o módulo em page_data e também numa
 *   tabela para leitores de tela.
 * - Listas do que pede atenção: OS em andamento, vendas em aberto,
 *   lançamentos a vencer e estoque baixo.
 *
 * O módulo painel/painel carrega o Chart.js e o FullCalendar de
 * assets/vendor só quando a tela tem gráfico ou agenda.
 *
 * @var string              $hoje
 * @var array<string, bool> $pode
 */
$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';

$kpis = [];
if ($pode['os']) {
    $aguardando = (int) ($os_por_status['Aguardando Peças'] ?? 0);
    $kpis[] = component('kpi-card', [
        'label' => 'OS em andamento',
        'value' => painelSomar($os_por_status, PAINEL_OS_ANDAMENTO),
        'icon' => 'wrench',
        'caption' => $aguardando === 0 ? 'Nenhuma aguardando peças' : $aguardando . ' aguardando peças',
    ]);
    $kpis[] = component('kpi-card', [
        'label' => 'Orçamentos',
        'value' => painelSomar($os_por_status, PAINEL_OS_ORCAMENTO),
        'icon' => 'file-text',
        'caption' => 'Em orçamento ou negociação',
    ]);
}
if ($pode['vendas']) {
    $kpis[] = component('kpi-card', [
        'label' => 'Vendas em aberto',
        'value' => painelSomar($vendas_por_status, PAINEL_VENDAS_ABERTAS),
        'icon' => 'shopping-cart',
        'caption' => 'Ainda não faturadas',
    ]);
}
if ($pode['lancamentos']) {
    $kpis[] = component('kpi-card', [
        'label' => 'Saldo do mês',
        'value' => dinheiro($mes['saldo']),
        'icon' => 'circle-dollar-sign',
        'caption' => 'A receber: ' . dinheiro($visao_geral['a_receber']),
    ]);
}

$dadosDoModulo = [];
if ($pode['balanco']) {
    $dadosDoModulo['balanco'] = $balanco;
}
if ($pode['os']) {
    $dadosDoModulo['os'] = $os_grafico;
}
?>
<div class="flex flex-col gap-4 pt-2 pb-8" <?= js_module('painel/painel') ?>>
    <header>
        <h1 class="font-display text-heading-xl text-text">Painel</h1>
        <p class="text-caption text-muted"><?= e(ucfirst(painelDataPorExtenso($hoje))) ?></p>
    </header>

    <?php if ($kpis !== []) { ?>
        <div class="grid grid-cols-2 gap-4 xl:grid-cols-4"><?= componenteConteudo($kpis) ?></div>
    <?php } ?>

    <?php if ($pode['os']) { ?>
        <div class="grid gap-4 xl:grid-cols-3">
            <section class="<?= e($caixa) ?> min-w-0 xl:col-span-2" aria-labelledby="titulo-agenda">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 id="titulo-agenda" class="text-heading-sm text-text">Agenda de entregas</h2>
                        <p class="text-caption text-muted">OS pela data final. Clique numa OS para ver os detalhes.</p>
                    </div>
                    <div class="sm:w-56">
                        <?= component('select', [
                            'name' => 'status',
                            'id' => 'agenda-status',
                            'label' => 'Status',
                            'placeholder' => 'Todos os status',
                            'options' => array_combine(array_keys(OS_STATUS_VARIANTES), array_keys(OS_STATUS_VARIANTES)),
                            'attrs' => ['data-agenda-status' => true],
                        ]) ?>
                    </div>
                </div>
                <div class="painel-agenda mt-4 min-h-96" data-agenda="<?= e(site_url('mapos/calendario')) ?>" aria-live="polite">
                    <p class="text-caption text-muted" data-agenda-carregando>Carregando a agenda…</p>
                </div>
            </section>

            <section class="<?= e($caixa) ?> min-w-0" aria-labelledby="titulo-os-status">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 id="titulo-os-status" class="text-heading-sm text-text">OS por status</h2>
                    <?= component('button', ['label' => 'Ver OS', 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('os')]) ?>
                </div>
                <?php if ($os_grafico === []) { ?>
                    <?= component('empty-state', ['title' => 'Nenhuma OS cadastrada', 'message' => 'As OS aparecem aqui por status.', 'icon' => 'chart-pie', 'class' => 'border-0']) ?>
                <?php } else { ?>
                    <div class="relative mx-auto mt-4 aspect-square max-w-72">
                        <canvas data-grafico="os" role="img" aria-label="Gráfico de OS por status"></canvas>
                    </div>
                    <div class="sr-only"><table>
                        <caption>OS por status</caption>
                        <thead><tr><th scope="col">Status</th><th scope="col">OS</th></tr></thead>
                        <tbody>
                            <?php foreach ($os_grafico as $fatia) { ?>
                                <tr><td><?= e($fatia['status']) ?></td><td><?= e($fatia['total']) ?></td></tr>
                            <?php } ?>
                        </tbody>
                    </table></div>
                <?php } ?>
            </section>
        </div>
    <?php } ?>

    <?php if ($pode['balanco']) { ?>
        <section class="<?= e($caixa) ?> min-w-0" aria-labelledby="titulo-balanco">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="titulo-balanco" class="text-heading-sm text-text"><?= e('Balanço de ' . $ano) ?></h2>
                    <p class="text-caption text-muted">Receitas e despesas pagas, pelo mês do pagamento.</p>
                </div>
                <form method="get" action="<?= e(site_url('mapos')) ?>" class="flex items-end gap-2">
                    <div class="w-32">
                        <?= component('select', [
                            'name' => 'ano',
                            'id' => 'balanco-ano',
                            'label' => 'Ano',
                            'options' => array_combine(array_map('strval', $anos), array_map('strval', $anos)),
                            'selected' => (string) $ano,
                        ]) ?>
                    </div>
                    <?= component('button', ['label' => 'Ver', 'variant' => 'outline', 'type' => 'submit']) ?>
                </form>
            </div>
            <?php if (array_sum($balanco['receitas']) + array_sum($balanco['despesas']) == 0) { ?>
                <?= component('empty-state', ['title' => 'Nada pago em ' . $ano, 'message' => 'Os lançamentos pagos do ano aparecem aqui, mês a mês.', 'icon' => 'chart-column', 'class' => 'border-0']) ?>
            <?php } else { ?>
                <div class="relative mt-4 h-72">
                    <canvas data-grafico="balanco" role="img" aria-label="<?= e('Gráfico do balanço de ' . $ano) ?>"></canvas>
                </div>
                <div class="sr-only"><table>
                    <caption><?= e('Balanço de ' . $ano) ?></caption>
                    <thead><tr><th scope="col">Mês</th><th scope="col">Receitas</th><th scope="col">Despesas</th><th scope="col">Saldo</th></tr></thead>
                    <tbody>
                        <?php foreach ($balanco['meses'] as $i => $mesNome) { ?>
                            <tr><td><?= e($mesNome) ?></td><td><?= e(dinheiro($balanco['receitas'][$i])) ?></td><td><?= e(dinheiro($balanco['despesas'][$i])) ?></td><td><?= e(dinheiro($balanco['saldo'][$i])) ?></td></tr>
                        <?php } ?>
                    </tbody>
                </table></div>
            <?php } ?>
        </section>
    <?php } ?>

    <div class="grid gap-4 2xl:grid-cols-2">
        <?php if ($pode['os']) { ?>
            <section class="<?= e($caixa) ?> min-w-0" aria-labelledby="titulo-lista-os">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 id="titulo-lista-os" class="text-heading-sm text-text">OS em andamento</h2>
                    <?= component('button', ['label' => 'Ver todas', 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('os')]) ?>
                </div>
                <div class="mt-3">
                    <?= component('data-table', [
                        'caption' => 'OS em andamento, pela data de entrega',
                        'dense' => true,
                        'rows' => $os_lista,
                        'columns' => [
                            ['label' => 'Nº', 'align' => 'right', 'nowrap' => true, 'render' => fn ($o) => component('link', ['label' => (string) $o->idOs, 'href' => site_url('os/visualizar/' . (int) $o->idOs)])],
                            ['label' => 'Cliente', 'class' => 'min-w-32 [overflow-wrap:anywhere]', 'render' => fn ($o) => (string) ($o->nomeCliente ?? 'Cliente removido')],
                            ['label' => 'Entrega', 'align' => 'right', 'nowrap' => true, 'render' => fn ($o) => $o->dataFinal !== null && substr((string) $o->dataFinal, 0, 10) < $hoje
                                ? component('pill-status', ['label' => 'Atrasada · ' . dataBr($o->dataFinal), 'variant' => 'warning'])
                                : ($o->dataFinal !== null ? dataBr($o->dataFinal) : '—')],
                            ['label' => 'Status', 'nowrap' => true, 'render' => fn ($o) => component('pill-status', osStatusPill($o->status))],
                        ],
                        'empty' => component('empty-state', ['title' => 'Nenhuma OS em andamento', 'message' => 'OS abertas, aprovadas, em andamento ou aguardando peças aparecem aqui.', 'icon' => 'wrench', 'class' => 'border-0']),
                    ]) ?>
                </div>
            </section>
        <?php } ?>

        <?php if ($pode['vendas']) { ?>
            <section class="<?= e($caixa) ?> min-w-0" aria-labelledby="titulo-lista-vendas">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 id="titulo-lista-vendas" class="text-heading-sm text-text">Vendas em aberto</h2>
                    <?= component('button', ['label' => 'Ver todas', 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('vendas')]) ?>
                </div>
                <div class="mt-3">
                    <?= component('data-table', [
                        'caption' => 'Vendas em aberto, da mais recente',
                        'dense' => true,
                        'rows' => $vendas_lista,
                        'columns' => [
                            ['label' => 'Nº', 'align' => 'right', 'nowrap' => true, 'render' => fn ($v) => component('link', ['label' => (string) $v->idVendas, 'href' => site_url('vendas/visualizar/' . (int) $v->idVendas)])],
                            ['label' => 'Cliente', 'class' => 'min-w-32 [overflow-wrap:anywhere]', 'render' => fn ($v) => (string) ($v->nomeCliente ?? 'Cliente removido')],
                            ['label' => 'Data', 'align' => 'right', 'nowrap' => true, 'render' => fn ($v) => dataBr($v->dataVenda)],
                            ['label' => 'Status', 'nowrap' => true, 'render' => fn ($v) => component('pill-status', osStatusPill($v->status))],
                        ],
                        'empty' => component('empty-state', ['title' => 'Nenhuma venda em aberto', 'message' => 'Vendas ainda não faturadas aparecem aqui.', 'icon' => 'shopping-cart', 'class' => 'border-0']),
                    ]) ?>
                </div>
            </section>
        <?php } ?>

        <?php if ($pode['lancamentos']) { ?>
            <section class="<?= e($caixa) ?> min-w-0" aria-labelledby="titulo-lista-lancamentos">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 id="titulo-lista-lancamentos" class="text-heading-sm text-text">Lançamentos a vencer</h2>
                    <?= component('button', ['label' => 'Ver todos', 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('financeiro/lancamentos') . listagemQuery(['periodo' => 'ano', 'status' => 'pendente'])]) ?>
                </div>
                <div class="mt-3">
                    <?= component('data-table', [
                        'caption' => 'Lançamentos em aberto, do vencimento mais antigo',
                        'dense' => true,
                        'rows' => $lancamentos_lista,
                        'columns' => [
                            ['label' => 'Vencimento', 'align' => 'right', 'nowrap' => true, 'render' => fn ($l) => substr((string) $l->data_vencimento, 0, 10) < $hoje
                                ? component('pill-status', ['label' => 'Vencido · ' . dataBr($l->data_vencimento), 'variant' => 'warning'])
                                : dataBr($l->data_vencimento)],
                            ['label' => 'Descrição', 'class' => 'min-w-40 [overflow-wrap:anywhere]', 'render' => fn ($l) => trim((string) $l->descricao . ((string) $l->cliente_fornecedor !== '' ? ' · ' . $l->cliente_fornecedor : ''))],
                            ['label' => 'Tipo', 'nowrap' => true, 'render' => fn ($l) => component('pill-status', $l->tipo === 'receita' ? ['label' => 'Receita', 'variant' => 'info'] : ['label' => 'Despesa', 'variant' => 'neutral'])],
                            ['label' => 'Valor', 'align' => 'right', 'nowrap' => true, 'render' => fn ($l) => dinheiro($l->liquido)],
                        ],
                        'empty' => component('empty-state', ['title' => 'Nenhum lançamento em aberto', 'message' => 'Receitas a receber e despesas a pagar aparecem aqui.', 'icon' => 'circle-dollar-sign', 'class' => 'border-0']),
                    ]) ?>
                </div>
            </section>
        <?php } ?>

        <?php if ($pode['produtos']) { ?>
            <section class="<?= e($caixa) ?> min-w-0" aria-labelledby="titulo-lista-estoque">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 id="titulo-lista-estoque" class="text-heading-sm text-text">Estoque baixo</h2>
                    <?= component('button', ['label' => 'Ver todos', 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('produtos') . listagemQuery(['estoque' => 'baixo'])]) ?>
                </div>
                <div class="mt-3">
                    <?= component('data-table', [
                        'caption' => 'Produtos no estoque mínimo ou abaixo',
                        'dense' => true,
                        'rows' => $estoque_lista,
                        'columns' => [
                            ['label' => 'Produto', 'class' => 'min-w-40 [overflow-wrap:anywhere]', 'render' => fn ($p) => component('link', ['label' => (string) $p->descricao, 'href' => site_url('produtos/visualizar/' . (int) $p->idProdutos)])],
                            ['label' => 'Estoque', 'align' => 'right', 'nowrap' => true, 'render' => fn ($p) => (string) (int) $p->estoque],
                            ['label' => 'Mínimo', 'align' => 'right', 'nowrap' => true, 'render' => fn ($p) => (string) (int) $p->estoqueMinimo],
                            ['label' => 'Situação', 'nowrap' => true, 'render' => fn ($p) => component('pill-status', (int) $p->estoque <= 0 ? ['label' => 'Sem estoque', 'variant' => 'warning'] : ['label' => 'Baixo', 'variant' => 'warning'])],
                        ],
                        'empty' => component('empty-state', ['title' => 'Nenhum produto com estoque baixo', 'message' => 'Produtos no estoque mínimo ou abaixo aparecem aqui.', 'icon' => 'package', 'class' => 'border-0']),
                    ]) ?>
                </div>
            </section>
        <?php } ?>
    </div>

    <?php if ($pode === array_fill_keys(array_keys($pode), false)) { ?>
        <?= component('empty-state', ['title' => 'Bem-vindo ao Map-OS', 'message' => 'Seu usuário ainda não tem permissão para ver os módulos do painel. Fale com o administrador.', 'icon' => 'house']) ?>
    <?php } ?>
</div>

<?php if ($dadosDoModulo !== []) { ?>
    <?= page_data('painel-dados', $dadosDoModulo) ?>
<?php } ?>

<?php if ($pode['os']) { ?>
    <?= component('modal', [
        'id' => 'agenda-os',
        'title' => 'OS',
        'size' => 'md',
        // Marcação estática; os dados entram por textContent (módulo
        // painel/painel). Um pill-status por status, escondidos: o módulo
        // mostra o da OS clicada.
        'body' => [
            new HtmlSeguro('<div class="flex flex-col gap-3"><div>'),
            array_map(static fn ($status) => component('pill-status', osStatusPill($status) + ['attrs' => ['data-agenda-pill' => $status, 'hidden' => true]]), array_keys(OS_STATUS_VARIANTES)),
            new HtmlSeguro('</div>'
            . '<dl class="grid gap-3 sm:grid-cols-2">'
            . '<div class="sm:col-span-2"><dt class="text-caption text-muted">Cliente</dt><dd class="text-body-md [overflow-wrap:anywhere] text-text" data-agenda-campo="cliente"></dd></div>'
            . '<div><dt class="text-caption text-muted">Entrada</dt><dd class="text-body-md tabular-nums text-text" data-agenda-campo="dataInicial"></dd></div>'
            . '<div><dt class="text-caption text-muted">Entrega</dt><dd class="text-body-md tabular-nums text-text" data-agenda-campo="dataFinal"></dd></div>'
            . '<div class="sm:col-span-2"><dt class="text-caption text-muted">Equipamento</dt><dd class="text-body-md [overflow-wrap:anywhere] text-text" data-agenda-campo="equipamento"></dd></div>'
            . '<div><dt class="text-caption text-muted">Total</dt><dd class="text-body-md tabular-nums text-text" data-agenda-campo="total"></dd></div>'
            . '<div><dt class="text-caption text-muted">Faturada</dt><dd class="text-body-md text-text" data-agenda-campo="faturado"></dd></div>'
            . '</dl></div>'),
        ],
        'footer' => [
            component('button', ['label' => 'Fechar', 'variant' => 'ghost', 'attrs' => ['data-modal-fechar' => true]]),
            component('button', ['label' => 'Editar OS', 'icon' => 'pencil', 'variant' => 'outline', 'href' => site_url('os'), 'attrs' => ['data-agenda-link' => 'urlEditar', 'hidden' => true]]),
            component('button', ['label' => 'Ver OS', 'icon' => 'eye', 'variant' => 'outline', 'href' => site_url('os'), 'attrs' => ['data-agenda-link' => 'url']]),
        ],
    ]) ?>
<?php } ?>
