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
        'block w-full rounded-sm border bg-field px-3 py-2 text-body-md text-text placeholder:text-muted',
        // DESIGN.md text-input: borda hairline-input (3:1), texto body-md 400,
        // foco com sombra interna e anel azul de 3px, erro em danger.
        'focus:outline-3 focus:outline-offset-0 focus:outline-ring/50 focus:shadow-field-focus',
        'disabled:cursor-not-allowed disabled:bg-surface-subtle disabled:text-muted read-only:bg-surface-subtle',
        $error !== null ? 'border-danger' : 'border-input',
        $class
    ),
];
?>
<div class="flex flex-col gap-1.5">
    <label for="<?= e($id) ?>" class="<?= e($hide_label ? 'sr-only' : 'text-label-md text-text') ?>">
        <?= e($label) ?><?php if ($required) { ?> <span class="text-danger-ink" aria-hidden="true">*</span><?php } ?>
    </label>
    <textarea<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>><?= e($value === null ? '' : (string) $value) ?></textarea>
    <?php if ($help !== null) { ?><p id="<?= e($idAjuda) ?>" class="text-caption text-muted"><?= e($help) ?></p><?php } ?>
    <?php if ($error !== null) { ?><p id="<?= e($idErro) ?>" class="flex items-start gap-1.5 text-caption text-danger-ink"><?= icon('circle-alert', ['class' => 'mt-0.5 size-4']) ?><span><?= e($error) ?></span></p><?php } ?>
</div>
