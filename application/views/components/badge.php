<?php
/**
 * Selo de status.
 *
 * @var string      $label
 * @var string      $variant neutral | accent | info | success | warning | danger
 * @var string      $size    sm | md
 * @var string|null $id
 * @var string      $class
 * @var array       $attrs
 */
$variantes = [
    'neutral' => 'bg-surface-subtle text-muted ring-border',
    // Fundo laranja-claro (primary-tint) com texto do tema: o laranja não
    // serve como texto no fundo claro (DESIGN.md).
    'accent' => 'bg-primary-tint text-text ring-primary/40',
    'info' => 'bg-info-soft text-info-ink ring-info/30',
    'success' => 'bg-success-soft text-success-ink ring-success/30',
    'warning' => 'bg-warning-soft text-warning-ink ring-warning/30',
    'danger' => 'bg-danger-soft text-danger-ink ring-danger/30',
];

$tamanhos = ['sm' => 'px-1.5 py-0.5 text-[0.6875rem]', 'md' => 'px-2 py-0.5 text-xs'];

$atributos = [
    'id' => $id,
    'class' => componenteClasses('inline-flex items-center rounded-pill font-medium ring-1 ring-inset', $variantes[$variant], $tamanhos[$size], $class),
];
?>
<span<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>><?= e($label) ?></span>
