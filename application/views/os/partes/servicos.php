<?php
/**
 * Serviços da OS (#2842). Serviço sem preço usa o do cadastro, e sem
 * quantidade conta 1, como em Os_model::valorTotalOS().
 *
 * @var list<object> $servicos
 * @var array        $totais
 * @var array<string, bool> $pode
 */
$preco = static fn ($s) => (float) ($s->preco ?: $s->precoVenda);
$quantidade = static fn ($s) => (float) ($s->quantidade ?: 1);

$colunas = [
    ['label' => 'Serviço', 'class' => 'min-w-48 [overflow-wrap:anywhere]', 'render' => fn ($s) => (string) $s->nome],
    ['label' => 'Qtd.', 'align' => 'right', 'nowrap' => true, 'render' => fn ($s) => rtrim(rtrim(number_format($quantidade($s), 2, ',', '.'), '0'), ',')],
    ['label' => 'Preço', 'align' => 'right', 'nowrap' => true, 'render' => fn ($s) => dinheiro($preco($s))],
    ['label' => 'Subtotal', 'align' => 'right', 'nowrap' => true, 'render' => fn ($s) => dinheiro($preco($s) * $quantidade($s))],
];

if ($pode['editar']) {
    $colunas[] = ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => fn ($s) => component('button', [
        'label' => 'Excluir ' . $s->nome,
        'icon' => 'trash-2',
        'icon_only' => true,
        'variant' => 'ghost',
        'size' => 'sm',
        'attrs' => ['data-modal-abrir' => 'excluir-servico', 'data-valor-id' => (string) $s->idServicos_os, 'data-valor-nome' => (string) $s->nome],
    ])];
}
?>
<div class="flex flex-col gap-2">
    <?= component('data-table', [
        'caption' => 'Serviços da OS',
        'rows' => $servicos,
        'columns' => $colunas,
        'empty' => component('empty-state', [
            'title' => 'Nenhum serviço na OS',
            'message' => $pode['editar'] ? 'Adicione a mão de obra e os serviços pelo formulário acima.' : 'Esta OS não tem serviços.',
            'icon' => 'wrench',
            'class' => 'border-0',
        ]),
    ]) ?>
    <?php if ($servicos !== []) { ?>
        <p class="text-right text-body-md text-text">Total de serviços: <span class="font-medium tabular-nums"><?= e(dinheiro($totais['servicos'])) ?></span></p>
    <?php } ?>
</div>
