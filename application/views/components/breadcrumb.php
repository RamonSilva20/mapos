<?php
/**
 * Trilha de navegação. O último item é a página atual (aria-current).
 *
 *     component('breadcrumb', ['items' => [
 *         ['label' => 'Início', 'url' => site_url('mapos')],
 *         ['label' => 'Clientes', 'url' => site_url('clientes')],
 *         ['label' => 'Editar'],
 *     ]])
 *
 * @var array       $items
 * @var string      $label aria-label do <nav>
 * @var string|null $id
 * @var string      $class
 * @var array       $attrs
 */
if (! is_array($items) || ! array_is_list($items)) {
    throw new InvalidArgumentException('A prop items de breadcrumb deve ser uma lista.');
}

foreach ($items as $item) {
    if (! is_array($item) || ! isset($item['label']) || $item['label'] === '') {
        throw new InvalidArgumentException('Cada item de breadcrumb precisa de label.');
    }
}

$ultimo = count($items) - 1;

$atributos = [
    'id' => $id,
    'aria-label' => $label,
    'class' => componenteClasses('text-sm', $class),
];
?>
<nav<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
    <ol class="flex flex-wrap items-center gap-1.5 text-muted">
        <?php foreach ($items as $i => $item) { ?>
            <li class="flex items-center gap-1.5">
                <?php if ($i < $ultimo && ! empty($item['url'])) { ?>
                    <a<?= componenteAtributos(['href' => $item['url']]) ?> class="rounded-sm hover:text-text hover:underline focus-visible:outline-2 focus-visible:outline-ring"><?= e($item['label']) ?></a>
                <?php } elseif ($i === $ultimo) { ?>
                    <span aria-current="page" class="font-medium text-text"><?= e($item['label']) ?></span>
                <?php } else { ?>
                    <span><?= e($item['label']) ?></span>
                <?php } ?>
                <?php if ($i < $ultimo) { ?><span aria-hidden="true" class="text-border">/</span><?php } ?>
            </li>
        <?php } ?>
    </ol>
</nav>
