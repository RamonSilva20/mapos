<?php
/**
 * Produtos da OS (#2842). Excluir abre o modal-confirm "excluir-produto" da
 * tela (os/visualizar), que leva o id do item por data-valor-id.
 *
 * @var list<object> $produtos
 * @var array        $totais
 * @var array<string, bool> $pode
 */
$colunas = [
    ['label' => 'Produto', 'class' => 'min-w-48 [overflow-wrap:anywhere]', 'render' => fn ($p) => (string) $p->descricao],
    ['label' => 'Qtd.', 'align' => 'right', 'nowrap' => true, 'render' => fn ($p) => (string) (0 + $p->quantidade)],
    ['label' => 'Preço', 'align' => 'right', 'nowrap' => true, 'render' => fn ($p) => dinheiro($p->preco)],
    ['label' => 'Subtotal', 'align' => 'right', 'nowrap' => true, 'render' => fn ($p) => dinheiro($p->subTotal)],
];

if ($pode['editar']) {
    $colunas[] = ['label' => 'Ações', 'align' => 'right', 'nowrap' => true, 'render' => fn ($p) => component('button', [
        'label' => 'Excluir ' . $p->descricao,
        'icon' => 'trash-2',
        'icon_only' => true,
        'variant' => 'ghost',
        'size' => 'sm',
        'attrs' => ['data-modal-abrir' => 'excluir-produto', 'data-valor-id' => (string) $p->idProdutos_os, 'data-valor-nome' => (string) $p->descricao],
    ])];
}
?>
<div class="flex flex-col gap-2">
    <?= component('data-table', [
        'caption' => 'Produtos da OS',
        'rows' => $produtos,
        'columns' => $colunas,
        'empty' => component('empty-state', [
            'title' => 'Nenhum produto na OS',
            'message' => $pode['editar'] ? 'Adicione as peças e os produtos usados pelo formulário acima.' : 'Esta OS não tem produtos.',
            'icon' => 'package',
            'class' => 'border-0',
        ]),
    ]) ?>
    <?php if ($produtos !== []) { ?>
        <p class="text-right text-body-md text-text">Total de produtos: <span class="font-medium tabular-nums"><?= e(dinheiro($totais['produtos'])) ?></span></p>
    <?php } ?>
</div>
