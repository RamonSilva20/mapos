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
    'neutral' => 'bg-surface-2 text-muted ring-border',
    // No escuro a escala do destaque mistura com a superfície escura e fica
    // apagada; o texto passa a ser o do tema, e a cor fica no fundo e no anel.
    'accent' => 'bg-accent-100 text-accent-800 ring-accent-300 dark:text-text',
    'info' => 'bg-info-soft text-info ring-info/30',
    'success' => 'bg-success-soft text-success ring-success/30',
    'warning' => 'bg-warning-soft text-warning ring-warning/30',
    'danger' => 'bg-danger-soft text-danger ring-danger/30',
];

$tamanhos = ['sm' => 'px-1.5 py-0.5 text-[0.6875rem]', 'md' => 'px-2 py-0.5 text-xs'];

$atributos = [
    'id' => $id,
    'class' => componenteClasses('inline-flex items-center rounded-pill font-medium ring-1 ring-inset', $variantes[$variant], $tamanhos[$size], $class),
];
?>
<span<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>><?= e($label) ?></span>
