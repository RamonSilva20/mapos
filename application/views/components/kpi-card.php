<?php
/**
 * Cartão de resumo (DESIGN.md kpi-card): rótulo, valor e legenda à esquerda e
 * um ícone colorido grande à direita. Compacto.
 *
 *     component('kpi-card', ['label' => 'Abertas', 'value' => 18, 'icon' => 'wrench', 'accent' => 'primary', 'caption' => '+3 hoje'])
 *
 * accent (primary, info, success, warning, danger, progress ou neutral) dá a
 * cor do ícone e um degradê translúcido com barra lateral (classes
 * kpi-acento-* em assets/src/app.css). neutral (padrão) é o cinza sóbrio.
 *
 * href torna o cartão clicável (vira <a>, com hover e foco).
 *
 * caption aceita HtmlSeguro, para uma frase com cor de status.
 *
 * sensitive marca o valor e a legenda com data-valor-sensivel, para o painel
 * poder ocultá-los (ver valor-sensivel).
 *
 * @var string                       $label
 * @var string|int|float             $value
 * @var string|null                  $icon    Ícone Lucide do sprite
 * @var string                       $accent  Cor do acento (ver acima)
 * @var string|null                  $href    URL; torna o cartão clicável
 * @var string|HtmlSeguro|array|null $caption
 * @var bool                         $sensitive Valor e legenda são dinheiro: o botão de olho do painel os oculta (valor-sensivel)
 * @var string|null                  $id
 * @var string                       $class
 * @var array                        $attrs
 */
// Cor do quadrado do ícone por acento; o acento também vira a classe
// kpi-acento-<accent> (degradê + barra), menos o neutral.
$acentos = [
    'primary' => 'bg-primary-tint text-primary-strong',
    'info' => 'bg-info-soft text-info-ink',
    'success' => 'bg-success-soft text-success-ink',
    'warning' => 'bg-warning-soft text-warning-ink',
    'danger' => 'bg-danger-soft text-danger-ink',
    'progress' => 'bg-progress-soft text-progress-ink',
    'neutral' => 'bg-surface-subtle text-text',
];
$acento = array_key_exists((string) $accent, $acentos) ? (string) $accent : 'neutral';
$ehLink = $href !== null && $href !== '';

$atributos = [
    'id' => $id,
    'class' => componenteClasses(
        'group flex items-center justify-between gap-4 rounded-xl border border-border bg-surface px-5 py-4 text-text shadow-card',
        $acento !== 'neutral' ? 'kpi-acento-' . $acento : null,
        $ehLink ? 'ui-lift hover:border-input focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-ring' : null,
        $class
    ),
];
if ($ehLink) {
    $atributos['href'] = $href;
}
$tag = $ehLink ? 'a' : 'div';
?>
<<?= e($tag) ?><?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
    <div class="min-w-0 flex-1">
        <p class="text-sm font-medium text-muted"><?= e($label) ?></p>
        <div class="mt-0.5 flex flex-wrap items-baseline gap-x-2 gap-y-1">
            <p class="font-display text-[1.9rem] leading-[1.15] font-medium tabular-nums"><?= $sensitive ? component('valor-sensivel', ['value' => (string) $value]) : e((string) $value) ?></p>
            <?php if (is_array($trend) && isset($trend['curto'])) {
                $trendCor = ($trend['bom'] ?? null) === true ? 'bg-success-soft text-success-ink' : (($trend['bom'] ?? null) === false ? 'bg-danger-soft text-danger-ink' : 'bg-neutral-soft text-neutral-ink');
                $trendSeta = ($trend['dir'] ?? 'flat') === 'up' ? '↑' : (($trend['dir'] ?? 'flat') === 'down' ? '↓' : '→'); ?>
                <span class="kpi-oculto-valores inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-caption font-semibold <?= e($trendCor) ?>" title="<?= e((string) ($trend['texto'] ?? '')) ?>"><span aria-hidden="true"><?= e($trendSeta) ?></span><?= e((string) $trend['curto']) ?></span>
            <?php } ?>
        </div>
        <?php if ($caption !== null) { ?><div class="mt-0.5 text-caption text-muted"><?= $sensitive ? component('valor-sensivel', ['value' => $caption]) : componenteConteudo($caption) ?></div><?php } ?>
        <?php if (is_array($spark) && count($spark) >= 2) { ?>
            <div class="kpi-spark kpi-oculto-valores mt-2.5" aria-hidden="true">
                <svg viewBox="0 0 120 28" width="100%" height="28" preserveAspectRatio="none" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="<?= e(sparklinePontos($spark, 120, 28)) ?>"></polyline>
                </svg>
            </div>
        <?php } ?>
    </div>
    <?php if ($icon !== null) { ?>
        <span class="inline-flex size-14 shrink-0 items-center justify-center self-start rounded-xl <?= e($acentos[$acento]) ?>"><?= icon($icon, ['class' => 'size-7']) ?></span>
    <?php } ?>
</<?= e($tag) ?>>
