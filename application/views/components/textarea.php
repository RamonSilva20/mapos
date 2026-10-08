<?php
/**
 * Área de texto com label, ajuda e erro associados.
 *
 * @var string      $name
 * @var string      $label
 * @var mixed       $value
 * @var int         $rows
 * @var string|null $placeholder
 * @var string|null $help
 * @var string|null $error
 * @var bool        $required
 * @var bool        $disabled
 * @var bool        $readonly
 * @var bool        $hide_label
 * @var string|null $id
 * @var string      $class
 * @var array       $attrs
 */
$id = $id ?? componenteId($name);
$idAjuda = $help !== null ? $id . '-ajuda' : null;
$idErro = $error !== null ? $id . '-erro' : null;

$atributos = [
    'id' => $id,
    'name' => $name,
    'rows' => (string) max(1, (int) $rows),
    'placeholder' => $placeholder,
    'required' => $required,
    'disabled' => $disabled,
    'readonly' => $readonly,
    'aria-invalid' => $error !== null ? 'true' : null,
    'aria-describedby' => trim(($idAjuda ?? '') . ' ' . ($idErro ?? '')) ?: null,
    'class' => componenteClasses(
        'block w-full rounded-control border bg-surface px-3 py-2 text-sm text-text placeholder:text-muted shadow-xs',
        'focus:border-accent-500 focus:outline-2 focus:outline-offset-0 focus:outline-ring/40',
        'disabled:cursor-not-allowed disabled:bg-surface-2 disabled:opacity-70 read-only:bg-surface-2',
        $error !== null ? 'border-danger' : 'border-border',
        $class
    ),
];
?>
<div class="flex flex-col gap-1.5">
    <label for="<?= e($id) ?>" class="<?= e($hide_label ? 'sr-only' : 'text-sm font-medium text-text') ?>">
        <?= e($label) ?><?php if ($required) { ?> <span class="text-danger" aria-hidden="true">*</span><?php } ?>
    </label>
    <textarea<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>><?= e($value === null ? '' : (string) $value) ?></textarea>
    <?php if ($help !== null) { ?><p id="<?= e($idAjuda) ?>" class="text-xs text-muted"><?= e($help) ?></p><?php } ?>
    <?php if ($error !== null) { ?><p id="<?= e($idErro) ?>" class="text-xs font-medium text-danger"><?= e($error) ?></p><?php } ?>
</div>
