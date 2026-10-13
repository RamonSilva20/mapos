<?php
/**
 * Início da área do cliente (#2848): resumo em kpi-card e as últimas OS e
 * compras. "Abrir OS" é a ação principal, na Top Nav. No celular, as
 * cobranças (fora da tabbar) têm o atalho no cartão de resumo.
 *
 * @var list<object> $os
 * @var list<object> $compras
 * @var array{os_em_andamento: int, os: int, compras: int, cobrancas: int} $resumo
 * @var string $cliente_nome
 */
$primeiroNome = strtok(trim($cliente_nome), ' ') ?: $cliente_nome;
$quantos = static fn (int $n, string $um, string $varios) => $n === 0 ? 'Nenhuma ainda' : $n . ' ' . ($n === 1 ? $um : $varios);
$verTodas = static fn (string $rotulo, string $url) => component('link', ['label' => $rotulo, 'href' => site_url($url)]);
?>
<div class="flex flex-col gap-6">
    <header>
        <h1 class="font-display text-heading-xl text-text"><?= e('Olá, ' . $primeiroNome) ?></h1>
        <p class="text-caption text-muted">Acompanhe suas ordens de serviço, compras e cobranças.</p>
    </header>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
        <?= component('kpi-card', ['label' => 'OS em andamento', 'value' => $resumo['os_em_andamento'], 'icon' => 'wrench', 'caption' => $verTodas($quantos($resumo['os'], 'OS no total', 'OS no total'), 'mine/os')]) ?>
        <?= component('kpi-card', ['label' => 'Compras', 'value' => $resumo['compras'], 'icon' => 'shopping-cart', 'caption' => $verTodas('Ver compras', 'mine/compras')]) ?>
        <?= component('kpi-card', ['label' => 'Cobranças', 'value' => $resumo['cobrancas'], 'icon' => 'receipt', 'caption' => $verTodas('Ver cobranças', 'mine/cobrancas'), 'class' => 'col-span-2 lg:col-span-1']) ?>
    </div>

    <section class="flex flex-col gap-3" aria-labelledby="titulo-ultimas-os">
        <div class="flex items-baseline justify-between gap-3">
            <h2 id="titulo-ultimas-os" class="text-heading-sm text-text">Últimas ordens de serviço</h2>
            <?php if ($os !== []) { ?>
                <?= component('link', ['label' => 'Ver todas', 'href' => site_url('mine/os')]) ?>
            <?php } ?>
        </div>
        <?= component('data-table', [
            'columns' => conecteColunasOs(false),
            'rows' => $os,
            'caption' => 'Últimas ordens de serviço',
            'empty' => component('empty-state', [
                'title' => 'Nenhuma ordem de serviço ainda',
                'message' => 'Quando você deixar um equipamento na assistência ou abrir uma OS por aqui, ela aparece nesta lista.',
                'icon' => 'wrench',
                'class' => 'border-0',
            ]),
        ]) ?>
    </section>

    <section class="flex flex-col gap-3" aria-labelledby="titulo-ultimas-compras">
        <div class="flex items-baseline justify-between gap-3">
            <h2 id="titulo-ultimas-compras" class="text-heading-sm text-text">Últimas compras</h2>
            <?php if ($compras !== []) { ?>
                <?= component('link', ['label' => 'Ver todas', 'href' => site_url('mine/compras')]) ?>
            <?php } ?>
        </div>
        <?= component('data-table', [
            'columns' => conecteColunasCompras(false),
            'rows' => $compras,
            'caption' => 'Últimas compras',
            'empty' => component('empty-state', [
                'title' => 'Nenhuma compra ainda',
                'message' => 'As compras feitas na loja aparecem aqui, com a garantia de cada uma.',
                'icon' => 'shopping-cart',
                'class' => 'border-0',
            ]),
        ]) ?>
    </section>
</div>
