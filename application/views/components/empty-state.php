<?php
/**
 * Estado vazio: listagem sem registros, busca sem resultado.
 *
 * @var string                       $title
 * @var string|HtmlSeguro|array|null $message
 * @var string|null                  $icon    Ícone Lucide do sprite, ex. folder-open
 * @var string|HtmlSeguro|array|null $action  Normalmente um component('button')
 * @var string|null                  $id
 * @var string                       $class
 * @var array                        $attrs
 */
$atributos = [
    'id' => $id,
    'class' => componenteClasses('flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-border px-6 py-10 text-center', $class),
];
?>
<div<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
    <?php if ($icon !== null) { ?>
        <span class="mb-1 inline-flex size-12 items-center justify-center rounded-full bg-surface-subtle text-muted"><?= icon($icon, ['class' => 'size-6']) ?></span>
    <?php } ?>
    <p class="text-label-md text-text"><?= e($title) ?></p>
    <?php if ($message !== null) { ?><div class="max-w-sm text-caption text-muted"><?= componenteConteudo($message) ?></div><?php } ?>
    <?php if ($action !== null) { ?><div class="mt-2"><?= componenteConteudo($action) ?></div><?php } ?>
</div>
