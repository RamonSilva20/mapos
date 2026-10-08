<?php
/**
 * Formulário de produto, para cadastrar e editar (#2841), no padrão dos
 * formulários (#2851).
 *
 * O módulo produtos/formulario (no wrapper) calcula o preço de venda a partir
 * do preço de compra e do lucro, por markup (sobre a compra) ou margem (sobre
 * a venda), como na v4. O lucro não é salvo: só ajuda a chegar no preço.
 *
 * @var object|null                $produto  null ao cadastrar
 * @var array<string, string|bool> $valores
 * @var array<string, string>      $erros    Por campo; _geral para falha ao salvar
 */
$editando = $produto !== null;
$erroGeral = $erros['_geral'] ?? null;
$errosDeCampo = array_diff_key($erros, ['_geral' => true]);

$unidades = unidadesDeMedida();
// Unidade gravada que não está na tabela continua aparecendo.
if ($valores['unidade'] !== '' && ! isset($unidades[$valores['unidade']])) {
    $unidades = [$valores['unidade'] => $valores['unidade']] + $unidades;
}

$campo = static fn (string $nome, array $props) => component('input', $props + [
    'name' => $nome,
    'value' => ($valores[$nome] ?? '') !== '' ? $valores[$nome] : null,
    'error' => $errosDeCampo[$nome] ?? null,
]);
$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';
?>
<div class="flex max-w-4xl flex-col gap-4 pt-2 pb-8" <?= js_module('produtos/formulario') ?>>
    <header>
        <h1 class="font-display text-heading-xl text-text"><?= e($editando ? 'Editar produto' : 'Novo produto') ?></h1>
        <p class="text-caption text-muted"><?= e($editando ? $produto->descricao : 'Peça, acessório ou item vendido e usado nas ordens de serviço.') ?></p>
    </header>

    <?php if ($erroGeral !== null) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => $erroGeral]) ?>
    <?php } elseif ($errosDeCampo !== []) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => count($errosDeCampo) === 1 ? 'Confira o campo destacado.' : 'Confira os ' . count($errosDeCampo) . ' campos destacados.']) ?>
    <?php } ?>

    <form method="post" action="<?= e(current_url()) ?>" novalidate class="flex flex-col gap-4" <?= js_module('formulario/padrao') ?>>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-produto">
            <h2 id="secao-produto" class="text-heading-sm text-text">Produto</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-[minmax(0,1fr)_14rem]">
                <?= $campo('descricao', [
                    'label' => 'Descrição',
                    'required' => true,
                    'attrs' => ['maxlength' => 80, 'autofocus' => ! $editando, 'data-msg-vazio' => 'Informe a descrição do produto.'],
                ]) ?>
                <?= component('select', [
                    'name' => 'unidade',
                    'label' => 'Unidade',
                    'required' => true,
                    'placeholder' => 'Selecione',
                    'options' => $unidades,
                    'selected' => $valores['unidade'] !== '' ? $valores['unidade'] : 'UNID',
                    'error' => $errosDeCampo['unidade'] ?? null,
                ]) ?>
                <?= $campo('codDeBarra', [
                    'label' => 'Código de barras',
                    'help' => 'EAN, código interno ou deixe em branco.',
                    'attrs' => ['maxlength' => 70, 'autocomplete' => 'off'],
                ]) ?>
                <fieldset class="flex flex-col gap-2">
                    <legend class="text-label-md text-text">Movimenta o estoque</legend>
                    <div class="flex flex-wrap gap-x-6 gap-y-2 pt-1">
                        <?= component('checkbox', ['name' => 'entrada', 'label' => 'Entrada', 'checked' => (bool) $valores['entrada']]) ?>
                        <?= component('checkbox', ['name' => 'saida', 'label' => 'Saída', 'checked' => (bool) $valores['saida']]) ?>
                    </div>
                </fieldset>
            </div>
        </section>

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-precos">
            <h2 id="secao-precos" class="text-heading-sm text-text">Preços</h2>
            <p class="mt-0.5 text-caption text-muted">Informe o lucro para calcular o preço de venda, ou digite o preço direto.</p>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <?= $campo('precoCompra', [
                    'label' => 'Preço de compra (R$)',
                    'required' => true,
                    'class' => 'text-right tabular-nums',
                    'attrs' => ['data-mascara' => 'dinheiro', 'inputmode' => 'decimal', 'maxlength' => 20, 'data-msg-vazio' => 'Informe o preço de compra.'],
                ]) ?>
                <?= component('select', [
                    'name' => 'lucro_tipo',
                    'label' => 'Lucro por',
                    'options' => ['markup' => 'Markup', 'margem' => 'Margem'],
                    'selected' => 'markup',
                    'help' => 'Markup: sobre a compra. Margem: sobre a venda.',
                ]) ?>
                <?= component('input', [
                    'name' => 'lucro',
                    'label' => 'Lucro (%)',
                    'type' => 'number',
                    'class' => 'text-right tabular-nums',
                    'attrs' => ['min' => 0, 'max' => 999, 'step' => '0.01', 'inputmode' => 'decimal'],
                ]) ?>
                <?= $campo('precoVenda', [
                    'label' => 'Preço de venda (R$)',
                    'required' => true,
                    'class' => 'text-right tabular-nums',
                    'attrs' => ['data-mascara' => 'dinheiro', 'inputmode' => 'decimal', 'maxlength' => 20, 'data-msg-vazio' => 'Informe o preço de venda.'],
                ]) ?>
            </div>
        </section>

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-estoque">
            <h2 id="secao-estoque" class="text-heading-sm text-text">Estoque</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <?= $campo('estoque', [
                    'label' => $editando ? 'Quantidade em estoque' : 'Estoque inicial',
                    'type' => 'number',
                    'required' => true,
                    'help' => $editando ? 'Para entradas e saídas do dia a dia, use "Entrada de estoque" na ficha do produto.' : null,
                    'attrs' => ['min' => 0, 'max' => 99999999, 'step' => 1, 'inputmode' => 'numeric', 'data-msg-vazio' => 'Informe a quantidade em estoque.'],
                ]) ?>
                <?= $campo('estoqueMinimo', [
                    'label' => 'Estoque mínimo',
                    'type' => 'number',
                    'help' => 'Abaixo disso, o produto aparece como estoque baixo.',
                    'attrs' => ['min' => 0, 'max' => 99999999, 'step' => 1, 'inputmode' => 'numeric'],
                ]) ?>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <?= component('button', ['label' => 'Cancelar', 'variant' => 'ghost', 'href' => $editando ? site_url('produtos/visualizar/' . (int) $produto->idProdutos) : site_url('produtos')]) ?>
            <?= component('button', ['label' => $editando ? 'Salvar alterações' : 'Cadastrar produto', 'icon' => 'save', 'type' => 'submit', 'attrs' => ['data-rotulo-carregando' => 'Salvando…']]) ?>
        </div>
    </form>
</div>
