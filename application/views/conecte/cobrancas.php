<?php
/**
 * Cobranças do cliente (#2848): a que se referem (OS ou venda), vencimento,
 * status em pill-status (cobrancaStatusPill()) e o valor. "Pagar" abre o
 * boleto ou o link do gateway, só quando a URL é http(s)
 * (cobrancaUrlSegura()); cobrança paga, cancelada ou estornada não mostra.
 *
 * @var list<object> $results
 * @var int          $total
 * @var array        $paginacao
 */
$colunas = [
    ['label' => 'Referência', 'nowrap' => true, 'render' => function ($c) {
        $referencia = conecteReferenciaDaCobranca($c);

        return $referencia['href'] === null ? $referencia['label'] : component('link', ['label' => $referencia['label'], 'href' => $referencia['href'], 'class' => 'font-medium']);
    }],
    ['label' => 'Vencimento', 'align' => 'right', 'nowrap' => true, 'render' => fn ($c) => dataBr($c->expire_at)],
    ['label' => 'Status', 'nowrap' => true, 'render' => fn ($c) => component('pill-status', cobrancaStatusPill($c->status))],
    ['label' => 'Valor', 'align' => 'right', 'nowrap' => true, 'render' => fn ($c) => dinheiro(((float) $c->total) / 100)],
    ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => function ($c) {
        $pill = cobrancaStatusPill($c->status);
        $link = cobrancaUrlSegura($c->link) ?? cobrancaUrlSegura($c->payment_url ?? null);
        $pdf = cobrancaUrlSegura($c->pdf ?? null);
        $acoes = [];

        if (! in_array($pill['label'], ['Paga', 'Cancelada', 'Estornada'], true)) {
            if ($link !== null) {
                $acoes[] = component('button', ['label' => 'Pagar', 'icon' => 'external-link', 'variant' => 'outline', 'size' => 'sm', 'href' => $link, 'attrs' => ['target' => '_blank', 'rel' => 'noopener', 'aria-label' => 'Pagar a cobrança de ' . dinheiro(((float) $c->total) / 100) . ' (abre o site do pagamento)']]);
            }
        }
        if ($pdf !== null) {
            $acoes[] = component('button', ['label' => 'Baixar o boleto em PDF', 'icon' => 'download', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => $pdf, 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]);
        }

        return $acoes === [] ? '—' : $acoes;
    }],
];
?>
<div class="flex flex-col gap-4">
    <header>
        <h1 class="font-display text-heading-xl text-text">Cobranças</h1>
        <p class="text-caption text-muted" aria-live="polite"><?= e($total === 1 ? '1 cobrança' : number_format($total, 0, ',', '.') . ' cobranças') ?></p>
    </header>

    <?= component('data-table', [
        'columns' => $colunas,
        'rows' => $results,
        'caption' => 'Cobranças',
        'empty' => component('empty-state', [
            'title' => 'Nenhuma cobrança',
            'message' => 'Os boletos e links de pagamento das suas OS e compras aparecem aqui.',
            'icon' => 'receipt',
            'class' => 'border-0',
        ]),
    ]) ?>

    <?= component('pagination', $paginacao) ?>
</div>
