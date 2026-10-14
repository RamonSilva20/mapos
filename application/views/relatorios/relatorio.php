<?php
/**
 * Tela de um relatório (#2845): atalhos dos relatórios rápidos e o formulário
 * do relatório personalizado, montados pela definição de relatorioDefinicao()
 * (helpers/relatorios_helper.php). Substitui as nove telas rel_*.php.
 *
 * O formulário é um GET que abre o relatório em outra aba (PDF, planilha ou
 * DOCX): por isso não usa o módulo formulario/padrao, cujo estado de
 * carregamento ficaria preso nesta aba. O módulo relatorios/filtros liga as
 * máscaras e os autocompletes.
 *
 * A ação principal da tela é "Gerar relatório" (o único button-primary).
 *
 * @var array{titulo: string, descricao: string, icone: string, rapidos: list<array>, acao: string, dica: string|null, campos: list<array>} $relatorio
 */
$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';
$novaAba = ['target' => '_blank', 'rel' => 'noopener'];
?>
<div class="flex flex-col gap-4 pt-2 pb-8" <?= js_module('relatorios/filtros') ?>>
    <header class="flex items-start gap-3">
        <span class="mt-1 inline-flex size-10 shrink-0 items-center justify-center rounded-md bg-surface-subtle text-text"><?= icon($relatorio['icone'], ['class' => 'size-5']) ?></span>
        <div class="min-w-0">
            <h1 class="font-display text-heading-xl text-text"><?= e($relatorio['titulo']) ?></h1>
            <p class="text-caption text-muted"><?= e($relatorio['descricao']) ?> Os relatórios abrem em outra aba.</p>
        </div>
    </header>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 lg:grid-cols-3">
        <section class="<?= e($caixa) ?> lg:col-span-1" aria-labelledby="secao-rapidos">
            <h2 id="secao-rapidos" class="text-heading-sm text-text">Relatórios rápidos</h2>
            <p class="mt-0.5 text-caption text-muted">Sem filtros, com todos os registros.</p>
            <ul class="mt-4 flex flex-col gap-2">
                <?php foreach ($relatorio['rapidos'] as $rapido) { ?>
                    <li>
                        <?= component('button', ['label' => $rapido['label'], 'icon' => $rapido['icone'], 'variant' => 'outline', 'href' => site_url($rapido['url']), 'class' => 'w-full justify-start', 'attrs' => $novaAba]) ?>
                    </li>
                <?php } ?>
            </ul>
        </section>

        <section class="<?= e($caixa) ?> lg:col-span-2" aria-labelledby="secao-personalizado">
            <h2 id="secao-personalizado" class="text-heading-sm text-text">Relatório personalizado</h2>
            <?php if ($relatorio['dica'] !== null) { ?>
                <p class="mt-0.5 text-caption text-muted"><?= e($relatorio['dica']) ?></p>
            <?php } ?>

            <form method="get" action="<?= e(site_url($relatorio['acao'])) ?>" target="_blank" class="mt-4 flex flex-col gap-4" aria-label="<?= e($relatorio['titulo']) ?>">
                <div class="grid gap-4 sm:grid-cols-2">
                    <?php foreach ($relatorio['campos'] as $campo) { ?>
                        <?php if ($campo['tipo'] === 'data') { ?>
                            <?= component('input', ['name' => $campo['name'], 'label' => $campo['label'], 'type' => 'date']) ?>
                        <?php } elseif ($campo['tipo'] === 'dinheiro') { ?>
                            <?= component('input', ['name' => $campo['name'], 'label' => $campo['label'], 'placeholder' => '0,00', 'class' => 'text-right tabular-nums', 'attrs' => ['data-mascara' => 'dinheiro', 'inputmode' => 'decimal', 'maxlength' => 20, 'autocomplete' => 'off']]) ?>
                        <?php } elseif ($campo['tipo'] === 'numero') { ?>
                            <?= component('input', ['name' => $campo['name'], 'label' => $campo['label'], 'type' => 'number', 'class' => 'tabular-nums', 'attrs' => ['min' => 0, 'step' => 1, 'inputmode' => 'numeric']]) ?>
                        <?php } elseif ($campo['tipo'] === 'select') { ?>
                            <?= component('select', ['name' => $campo['name'], 'label' => $campo['label'], 'options' => $campo['opcoes'], 'placeholder' => $campo['placeholder'] ?? null]) ?>
                        <?php } elseif ($campo['tipo'] === 'busca') { ?>
                            <div class="relative" data-autocomplete="<?= e(site_url($campo['url'])) ?>" data-autocomplete-alvo="<?= e(componenteId($campo['name'])) ?>" data-autocomplete-min="2">
                                <?= component('input', ['name' => $campo['name'] . '_texto', 'label' => $campo['label'], 'placeholder' => $campo['placeholder'] ?? null, 'autocomplete' => 'off', 'attrs' => ['data-msg-invalido' => 'Escolha um nome da lista ou deixe o campo em branco.']]) ?>
                                <input type="hidden" id="<?= e(componenteId($campo['name'])) ?>" name="<?= e($campo['name']) ?>" value="">
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <?= component('button', ['label' => 'Limpar', 'icon' => 'x', 'variant' => 'ghost', 'type' => 'reset']) ?>
                    <?= component('button', ['label' => 'Gerar relatório', 'icon' => 'printer', 'type' => 'submit']) ?>
                </div>
            </form>
        </section>
    </div>
</div>
