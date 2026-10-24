<?php
/**
 * Contador ou categoria pequena (ex. "18" ao lado de um item do menu).
 *
 * Para o estado de uma OS, venda ou cobrança use pill-status: badge não tem
 * cor de status (DESIGN.md).
 *
 * @var string      $label
 * @var string      $variant neutral | accent
 * @var string      $size    sm | md
 * @var string|null $id
 * @var string      $class
 * @var array       $attrs
 */
$variantes = [
    'neutral' => 'bg-neutral-soft text-neutral-ink',
    // Fundo laranja-claro (primary-tint) com texto do tema: o laranja não
    // serve como texto no fundo claro (DESIGN.md).
    'accent' => 'bg-primary-tint text-text',
];

$tamanhos = ['sm' => 'min-w-5 px-1.5 text-[0.6875rem] leading-5', 'md' => 'min-w-[22px] px-2 text-xs leading-5'];

$atributos = [
    'id' => $id,
    'class' => componenteClasses('inline-flex items-center justify-center rounded-full font-semibold tabular-nums', $variantes[$variant], $tamanhos[$size], $class),
];
?>
<span<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>><?= e($label) ?></span>
