<?php
/**
 * Cartão de resumo acima das listagens (DESIGN.md kpi-card).
 *
 *     component('kpi-card', ['label' => 'Abertas', 'value' => 18, 'icon' => 'wrench', 'caption' => '+3 hoje'])
 *
 * caption aceita HtmlSeguro, para uma frase com cor de status, por exemplo
 * html_purificado('<span class="font-semibold text-warning-ink">2 há mais de 5 dias</span>').
 *
 * sensitive marca o valor e a legenda com data-valor-sensivel, para o painel
 * poder ocultá-los (ver valor-sensivel).
 *
 * @var string                       $label
 * @var string|int|float             $value
 * @var string|null                  $icon    Ícone Lucide do sprite
 * @var string|HtmlSeguro|array|null $caption
 * @var bool                         $sensitive Valor e legenda são dinheiro: o botão de olho do painel os oculta (valor-sensivel)
 * @var string|null                  $id
 * @var string                       $class
 * @var array                        $attrs
 */
$atributos = [
    'id' => $id,
    'class' => componenteClasses('min-w-0 rounded-xl border border-border bg-surface px-5 py-4.5 text-text shadow-card', $class),
];
?>
<div<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm font-medium text-muted"><?= e($label) ?></p>
        <?php if ($icon !== null) { ?>
            <span class="inline-flex size-[34px] shrink-0 items-center justify-center rounded-md bg-surface-subtle text-text"><?= icon($icon, ['class' => 'size-4']) ?></span>
        <?php } ?>
    </div>
    <p class="mt-2.5 font-display text-[2.125rem] leading-[1.1] font-medium tabular-nums"><?= $sensitive ? component('valor-sensivel', ['value' => (string) $value]) : e((string) $value) ?></p>
    <?php if ($caption !== null) { ?><div class="mt-2 text-caption text-muted"><?= $sensitive ? component('valor-sensivel', ['value' => $caption]) : componenteConteudo($caption) ?></div><?php } ?>
</div>
