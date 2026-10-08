<?php
/**
 * Ficha do produto (#2841): dados, preços com o lucro e o estoque, com a
 * entrada de estoque no próprio cartão. "Editar produto" fica na topbar;
 * "Excluir" no cabeçalho, com modal-confirm.
 *
 * @var object              $produto
 * @var array<string, bool> $pode
 */
$id = (int) $produto->idProdutos;
$compra = (float) $produto->precoCompra;
$venda = (float) $produto->precoVenda;
// Markup sobre a compra e margem sobre a venda, como o cálculo do formulário.
$markup = $compra > 0 ? ($venda - $compra) / $compra * 100 : null;
$margem = $venda > 0 ? ($venda - $compra) / $venda * 100 : null;
$porcento = static fn (?float $valor) => $valor === null ? '—' : number_format($valor, 1, ',', '.') . '%';
$baixo = produtoEstoqueBaixo($produto);
$unidade = (string) $produto->unidade;

$movimento = array_filter([$produto->entrada ? 'Entrada' : null, $produto->saida ? 'Saída' : null]);
$secoes = [
    'Produto' => array_filter([
        'Código' => (string) $id,
        'Código de barras' => (string) $produto->codDeBarra,
        'Unidade' => unidadesDeMedida()[$unidade] ?? $unidade,
        'Movimenta o estoque' => $movimento === [] ? 'Não' : implode(' e ', $movimento),
    ], static fn ($valor) => $valor !== ''),
    'Preços' => [
        'Compra' => dinheiro($compra),
        'Venda' => dinheiro($venda),
        'Markup' => $porcento($markup),
        'Margem' => $porcento($margem),
    ],
];
?>
<div class="flex flex-col gap-4 pt-2 pb-8">
    <header class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="font-display text-heading-xl [overflow-wrap:anywhere] text-text"><?= e($produto->descricao) ?></h1>
                <?php if ($baixo) { ?><?= component('pill-status', ['label' => 'Estoque baixo', 'variant' => 'warning']) ?><?php } ?>
            </div>
            <p class="text-caption [overflow-wrap:anywhere] text-muted"><?= e('Produto nº ' . $id . ($produto->codDeBarra ? ' · ' . $produto->codDeBarra : '')) ?></p>
        </div>
        <?php if ($pode['excluir']) { ?>
            <?= component('button', ['label' => 'Excluir', 'icon' => 'trash-2', 'variant' => 'ghost', 'class' => 'hover:text-danger-ink', 'attrs' => ['data-modal-abrir' => 'excluir-produto']]) ?>
        <?php } ?>
    </header>

    <div class="grid gap-4 lg:grid-cols-3">
        <?php foreach ($secoes as $titulo => $itens) { ?>
            <section class="rounded-xl border border-border bg-surface p-4 sm:p-6" aria-label="<?= e($titulo) ?>">
                <h2 class="text-heading-sm text-text"><?= e($titulo) ?></h2>
                <dl class="mt-3 grid grid-cols-2 gap-3">
                    <?php foreach ($itens as $rotulo => $valor) { ?>
                        <div>
                            <dt class="text-caption text-muted"><?= e($rotulo) ?></dt>
                            <dd class="text-body-md tabular-nums [overflow-wrap:anywhere] text-text"><?= e($valor) ?></dd>
                        </div>
                    <?php } ?>
                </dl>
            </section>
        <?php } ?>

        <section class="rounded-xl border border-border bg-surface p-4 sm:p-6" aria-labelledby="titulo-estoque">
            <h2 id="titulo-estoque" class="text-heading-sm text-text">Estoque</h2>
            <dl class="mt-3 grid grid-cols-2 gap-3">
                <div>
                    <dt class="text-caption text-muted">Em estoque</dt>
                    <dd class="font-display text-heading-lg tabular-nums text-text"><?= e((int) $produto->estoque . ' ' . $unidade) ?></dd>
                </div>
                <div>
                    <dt class="text-caption text-muted">Mínimo</dt>
                    <dd class="text-body-md tabular-nums text-text"><?= e($produto->estoqueMinimo !== null && $produto->estoqueMinimo !== '' ? (int) $produto->estoqueMinimo . ' ' . $unidade : '—') ?></dd>
                </div>
            </dl>
            <?php if ($pode['editar']) { ?>
                <form method="post" action="<?= e(site_url('produtos/atualizar_estoque')) ?>" novalidate class="mt-4 grid grid-cols-[minmax(0,1fr)_auto] items-start gap-2 border-t border-border pt-4" <?= js_module('formulario/padrao') ?>>
                    <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
                    <input type="hidden" name="id" value="<?= e($id) ?>">
                    <?= component('input', [
                        'name' => 'quantidade',
                        'label' => 'Entrada de estoque',
                        'type' => 'number',
                        'required' => true,
                        'help' => 'Negativo para retirar.',
                        'attrs' => ['step' => 1, 'min' => -1000000, 'max' => 1000000, 'inputmode' => 'numeric', 'data-msg-vazio' => 'Informe a quantidade.'],
                    ]) ?>
                    <?= component('button', ['label' => 'Atualizar', 'icon' => 'package-plus', 'variant' => 'outline', 'type' => 'submit', 'class' => 'mt-7', 'attrs' => ['data-rotulo-carregando' => 'Salvando…']]) ?>
                </form>
            <?php } ?>
        </section>
    </div>
</div>

<?php if ($pode['excluir']) { ?>
    <form id="form-excluir-produto" method="post" action="<?= e(site_url('produtos/excluir')) ?>" hidden>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
        <input type="hidden" name="id" value="<?= e($id) ?>">
    </form>
    <?= component('modal-confirm', [
        'id' => 'excluir-produto',
        'title' => 'Excluir produto?',
        'message' => [
            new HtmlSeguro('<strong class="font-semibold text-text">'),
            $produto->descricao,
            new HtmlSeguro('</strong>'),
            ' será removido do catálogo e também das ordens de serviço e das vendas em que foi usado. Essa ação não pode ser desfeita.',
        ],
        'confirm_label' => 'Excluir',
        'confirm_attrs' => ['form' => 'form-excluir-produto'],
    ]) ?>
<?php } ?>
