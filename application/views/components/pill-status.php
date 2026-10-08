<?php
/**
 * Estado de uma OS, venda ou cobrança (DESIGN.md pill-status).
 *
 * Sempre com a palavra, nunca só a cor; texto -ink sobre -soft, com contraste
 * conferido nos dois modos. O laranja nunca indica status.
 *
 *     component('pill-status', ['label' => 'Em andamento', 'variant' => 'progress'])
 *
 * @var string      $label
 * @var string      $variant success | warning | danger | info | progress | neutral
 * @var bool        $dot     Ponto colorido antes da palavra
 * @var string|null $id
 * @var string      $class
 * @var array       $attrs
 */
$variantes = [
    'success' => 'bg-success-soft text-success-ink',
    'warning' => 'bg-warning-soft text-warning-ink',
    'danger' => 'bg-danger-soft text-danger-ink',
    'info' => 'bg-info-soft text-info-ink',
    'progress' => 'bg-progress-soft text-progress-ink',
    'neutral' => 'bg-neutral-soft text-neutral-ink',
];

$atributos = [
    'id' => $id,
    'class' => componenteClasses('inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[0.8125rem] leading-[1.6] font-semibold whitespace-nowrap', $variantes[$variant], $class),
];
?>
<span<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>><?php if ($dot) { ?><span class="size-1.5 shrink-0 rounded-full bg-current" aria-hidden="true"></span><?php } ?><?= e($label) ?></span>
