<?php
/**
 * Desconto da OS (#2842), em R$ ou %. O servidor calcula o total com
 * desconto (Os::adicionarDesconto(), osCalcularDesconto()); o módulo os/tela
 * envia sem recarregar e troca este trecho e os totais.
 *
 * Só aparece com a OS editável: depois de faturada, o desconto fica no
 * kpi-card dos totais.
 *
 * @var object $os
 * @var array  $totais
 * @var array<string, bool> $pode
 */
if (! $pode['editar']) {
    return;
}

$valorAtual = $totais['desconto'] > 0 ? rtrim(rtrim(number_format($totais['informado'], 2, ',', ''), '0'), ',') : null;
?>
<section class="rounded-xl border border-border bg-surface p-4 sm:p-6" aria-labelledby="secao-desconto">
    <h2 id="secao-desconto" class="text-heading-sm text-text">Desconto</h2>
    <p class="mt-1 text-caption text-muted">
        <?= e($totais['desconto'] > 0
            ? 'Desconto de ' . dinheiro($totais['desconto']) . '. Total da OS: ' . dinheiro($totais['total']) . '.'
            : 'Sem desconto. Adicionar ou remover produtos e serviços também tira o desconto.') ?>
    </p>
    <form method="post" action="<?= e(site_url('os/adicionarDesconto')) ?>" novalidate class="mt-4 grid gap-4 sm:grid-cols-[9rem_minmax(0,16rem)_auto] sm:items-start" data-os-acao="desconto">
        <input type="hidden" name="idOs" value="<?= e((int) $os->idOs) ?>">
        <?= component('select', [
            'name' => 'tipoDesconto',
            'id' => 'desconto-tipo',
            'label' => 'Tipo',
            'options' => ['real' => 'Em reais (R$)', 'porcento' => 'Em porcentagem (%)'],
            'selected' => $totais['tipo'] ?? 'real',
            'required' => true,
        ]) ?>
        <?= component('input', [
            'name' => 'desconto',
            'id' => 'desconto-valor',
            'label' => 'Desconto',
            'value' => $valorAtual,
            'placeholder' => '0,00',
            'required' => true,
            'help' => 'Informe 0 para tirar o desconto.',
            'attrs' => [
                'inputmode' => 'decimal',
                'pattern' => '^\d+([.,]\d{1,2})?$',
                'data-msg-vazio' => 'Informe o desconto, ou 0 para tirar.',
                'data-msg-invalido' => 'Use só números, como 10 ou 10,50.',
            ],
        ]) ?>
        <div class="sm:pt-7">
            <?= component('button', ['label' => 'Aplicar desconto', 'icon' => 'percent', 'variant' => 'outline', 'type' => 'submit', 'attrs' => ['data-rotulo-carregando' => 'Aplicando…']]) ?>
        </div>
    </form>
</section>
