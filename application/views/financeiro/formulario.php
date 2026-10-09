<?php
/**
 * Formulário de lançamento (#2844), para cadastrar receitas e despesas e para
 * editar, no padrão dos formulários da v5 (#2851, views/clientes/formulario.php).
 *
 * - POST comum, com o token CSRF; o módulo formulario/padrao valida no
 *   navegador e o servidor valida de novo (Financeiro::formulario()).
 * - Valor, desconto e entrada têm a máscara de dinheiro; o valor líquido e as
 *   parcelas são só uma prévia (módulo financeiro/formulario): o servidor
 *   calcula tudo de novo.
 * - Cliente / fornecedor é texto livre, com sugestões dos clientes do cadastro
 *   (que vinculam o lançamento ao cliente) e dos nomes já usados.
 * - O parcelamento só existe ao cadastrar: cada parcela vira um lançamento.
 *
 * @var object|null           $lancamento null ao cadastrar
 * @var array<string, string> $valores    financeiroValoresDoFormulario()
 * @var array<string, string> $erros      Por campo; _geral para falha ao salvar
 * @var array<string, string> $retorno    Filtros da listagem de onde a pessoa veio
 * @var bool                  $controle_baixa
 */
$editando = $lancamento !== null;
$erroGeral = $erros['_geral'] ?? null;
$errosDeCampo = array_diff_key($erros, ['_geral' => true]);
$baixado = $valores['baixado'] === '1';
$parcelado = ! $editando && (int) ($valores['parcelas'] ?: 1) > 1;

$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';
$grade = 'mt-4 grid gap-4 sm:grid-cols-2';
$listagem = site_url('financeiro/lancamentos') . listagemQuery($retorno);
$valor = static fn (string $nome): ?string => $valores[$nome] !== '' ? $valores[$nome] : null;

$parcelas = [];
for ($i = 1; $i <= FINANCEIRO_PARCELAS_MAX; $i++) {
    $parcelas[(string) $i] = $i === 1 ? 'À vista' : $i . 'x';
}
?>
<div class="flex max-w-4xl flex-col gap-4 pt-2 pb-8" <?= js_module('financeiro/formulario') ?> data-financeiro-sugestoes="<?= e(site_url('financeiro/autoCompleteClienteFornecedor')) ?>">
    <header class="flex flex-col gap-1">
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="font-display text-heading-xl text-text"><?= e($editando ? 'Editar lançamento #' . $lancamento->idLancamentos : 'Novo lançamento') ?></h1>
            <?php if ($editando) { ?>
                <?= component('pill-status', financeiroStatusPill($lancamento, date('Y-m-d'))) ?>
            <?php } ?>
        </div>
        <p class="text-caption text-muted"><?= e($editando
            ? ($lancamento->modificado_por ? 'Última alteração por ' . $lancamento->modificado_por . '.' : 'Receita ou despesa lançada no financeiro.')
            : 'Receita ou despesa, à vista ou parcelada.') ?></p>
    </header>

    <?php if ($erroGeral !== null) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => $erroGeral]) ?>
    <?php } elseif ($errosDeCampo !== []) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => count($errosDeCampo) === 1 ? 'Confira o campo destacado.' : 'Confira os ' . count($errosDeCampo) . ' campos destacados.']) ?>
    <?php } ?>

    <form method="post" action="<?= e(current_url() . listagemQuery($retorno)) ?>" novalidate class="flex flex-col gap-4" <?= js_module('formulario/padrao') ?>>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-lancamento">
            <h2 id="secao-lancamento" class="text-heading-sm text-text">Lançamento</h2>
            <div class="<?= e($grade) ?>">
                <?= component('select', [
                    'name' => 'tipo',
                    'label' => 'Tipo',
                    'required' => true,
                    'options' => FINANCEIRO_TIPOS,
                    'selected' => $valor('tipo'),
                    'error' => $errosDeCampo['tipo'] ?? null,
                ]) ?>
                <?= component('input', [
                    'name' => 'descricao',
                    'label' => 'Descrição ou referência',
                    'required' => true,
                    'value' => $valor('descricao'),
                    'error' => $errosDeCampo['descricao'] ?? null,
                    'attrs' => ['maxlength' => 255, 'autofocus' => ! $editando, 'data-msg-vazio' => 'Informe a descrição.'],
                ]) ?>
                <div class="sm:col-span-2">
                    <?= component('input', [
                        'name' => 'cliente_fornecedor',
                        'label' => 'Cliente / fornecedor',
                        'required' => true,
                        'value' => $valor('cliente_fornecedor'),
                        'autocomplete' => 'off',
                        'help' => 'Escolha um cliente da lista para vincular o lançamento ao cadastro, ou digite o nome de um fornecedor.',
                        'error' => $errosDeCampo['cliente_fornecedor'] ?? null,
                        'attrs' => ['maxlength' => 255, 'list' => 'cliente-sugestoes', 'data-msg-vazio' => 'Informe o cliente ou fornecedor.'],
                    ]) ?>
                    <datalist id="cliente-sugestoes"></datalist>
                    <input type="hidden" id="clientes_id" name="clientes_id" value="<?= e($valores['clientes_id']) ?>">
                </div>
                <div class="sm:col-span-2">
                    <?= component('textarea', [
                        'name' => 'observacoes',
                        'label' => 'Observações',
                        'rows' => 3,
                        'value' => $valor('observacoes'),
                        'error' => $errosDeCampo['observacoes'] ?? null,
                    ]) ?>
                </div>
            </div>
        </section>

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-valores">
            <h2 id="secao-valores" class="text-heading-sm text-text">Valores e vencimento</h2>
            <div class="<?= e($grade) ?>">
                <?= component('input', [
                    'name' => 'valor',
                    'label' => 'Valor (R$)',
                    'required' => true,
                    'value' => $valor('valor'),
                    'placeholder' => '0,00',
                    'error' => $errosDeCampo['valor'] ?? null,
                    'attrs' => ['inputmode' => 'numeric', 'data-mascara' => 'dinheiro', 'data-msg-vazio' => 'Informe o valor.'],
                ]) ?>
                <?= component('input', [
                    'name' => 'desconto',
                    'label' => 'Desconto (R$)',
                    'value' => $valor('desconto'),
                    'placeholder' => '0,00',
                    'help' => 'Opcional. Em reais, menor que o valor.',
                    'error' => $errosDeCampo['desconto'] ?? null,
                    'attrs' => ['inputmode' => 'numeric', 'data-mascara' => 'dinheiro'],
                ]) ?>
                <?= component('input', [
                    'name' => 'data_vencimento',
                    'label' => 'Vencimento',
                    'type' => 'date',
                    'required' => true,
                    'value' => $valor('data_vencimento'),
                    'help' => $editando ? null : 'Com parcelas, é o vencimento da 1ª.',
                    'error' => $errosDeCampo['data_vencimento'] ?? null,
                    'attrs' => ['data-msg-vazio' => 'Informe a data de vencimento.'],
                ]) ?>
                <p class="self-end text-body-md text-text" aria-live="polite">
                    Valor líquido: <strong class="font-medium tabular-nums" data-financeiro-liquido>—</strong>
                </p>
            </div>
        </section>

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-pagamento">
            <h2 id="secao-pagamento" class="text-heading-sm text-text">Pagamento</h2>
            <div class="<?= e($grade) ?>">
                <div class="sm:col-span-2" data-financeiro-baixa-opcao<?= $parcelado ? ' hidden' : '' ?>>
                    <?= component('checkbox', [
                        'name' => 'baixado',
                        'label' => 'Já foi recebido ou pago',
                        'value' => '1',
                        'checked' => $baixado,
                        'help' => 'Dá baixa no lançamento, com a data e a forma de pagamento abaixo.',
                        'error' => $errosDeCampo['baixado'] ?? null,
                    ]) ?>
                </div>
                <div data-financeiro-baixa<?= $baixado ? '' : ' hidden' ?>>
                    <?= component('input', [
                        'name' => 'data_pagamento',
                        'label' => 'Data do pagamento',
                        'type' => 'date',
                        'required' => $baixado,
                        'value' => $valor('data_pagamento'),
                        'help' => $controle_baixa ? 'O controle de baixa está ligado: só a data de hoje.' : null,
                        'error' => $errosDeCampo['data_pagamento'] ?? null,
                        'attrs' => ['data-msg-vazio' => 'Informe a data do pagamento.'],
                    ]) ?>
                </div>
                <div data-financeiro-baixa<?= $baixado ? '' : ' hidden' ?>>
                    <?= component('select', [
                        'name' => 'forma_pgto',
                        'label' => 'Forma de pagamento',
                        'placeholder' => 'Escolha',
                        'required' => $baixado,
                        'options' => array_combine(FINANCEIRO_FORMAS_PAGAMENTO, FINANCEIRO_FORMAS_PAGAMENTO),
                        'selected' => $valor('forma_pgto'),
                        'error' => $errosDeCampo['forma_pgto'] ?? null,
                        'attrs' => ['data-msg-vazio' => 'Escolha a forma de pagamento.'],
                    ]) ?>
                </div>
            </div>
        </section>

        <?php if (! $editando) { ?>
            <section class="<?= e($caixa) ?>" aria-labelledby="secao-parcelamento">
                <h2 id="secao-parcelamento" class="text-heading-sm text-text">Parcelamento</h2>
                <div class="<?= e($grade) ?>">
                    <?= component('select', [
                        'name' => 'parcelas',
                        'label' => 'Parcelas',
                        'options' => $parcelas,
                        'selected' => $valor('parcelas') ?? '1',
                        'help' => 'Cada parcela vira um lançamento, com vencimento a cada mês.',
                        'error' => $errosDeCampo['parcelas'] ?? null,
                    ]) ?>
                    <p class="self-end text-body-md text-text" aria-live="polite" data-financeiro-resumo></p>
                    <div data-financeiro-entrada<?= $parcelado ? '' : ' hidden' ?>>
                        <?= component('input', [
                            'name' => 'entrada',
                            'label' => 'Entrada (R$)',
                            'value' => $valor('entrada'),
                            'placeholder' => '0,00',
                            'help' => 'Opcional. Entra como um lançamento já pago, na data da entrada.',
                            'error' => $errosDeCampo['entrada'] ?? null,
                            'attrs' => ['inputmode' => 'numeric', 'data-mascara' => 'dinheiro'],
                        ]) ?>
                    </div>
                    <div data-financeiro-entrada<?= $parcelado ? '' : ' hidden' ?>>
                        <?= component('input', [
                            'name' => 'data_entrada',
                            'label' => 'Data da entrada',
                            'type' => 'date',
                            'value' => $valor('data_entrada'),
                            'error' => $errosDeCampo['data_entrada'] ?? null,
                        ]) ?>
                    </div>
                </div>
            </section>
        <?php } ?>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <?= component('button', ['label' => 'Cancelar', 'variant' => 'ghost', 'href' => $listagem]) ?>
            <?= component('button', ['label' => $editando ? 'Salvar alterações' : 'Lançar', 'icon' => 'save', 'type' => 'submit', 'attrs' => ['data-rotulo-carregando' => 'Salvando…']]) ?>
        </div>
    </form>
</div>
