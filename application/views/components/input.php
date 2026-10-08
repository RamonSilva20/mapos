<?php
/**
 * Campo de texto com label, ajuda e erro associados.
 *
 * @var string      $name
 * @var string      $label
 * @var string      $type         text | email | password | number | date | datetime-local | time | tel | url | search
 * @var mixed       $value
 * @var string|null $placeholder
 * @var string|null $help         Texto de ajuda abaixo do campo
 * @var string|null $error        Mensagem de erro; marca o campo como inválido
 * @var bool        $required
 * @var bool        $disabled
 * @var bool        $readonly
 * @var string|null $autocomplete
 * @var bool        $hide_label   Esconde o label visualmente (continua acessível)
 * @var string|null $id           Padrão: derivado do name
 * @var string      $class        Classes extras do campo
 * @var array       $attrs        Atributos extras do campo
 */
$id = $id ?? componenteId($name);
$idAjuda = $help !== null ? $id . '-ajuda' : null;
$idErro = $error !== null ? $id . '-erro' : null;

$atributos = [
    'type' => $type,
    'id' => $id,
    'name' => $name,
    'value' => $value === null ? null : (string) $value,
    'placeholder' => $placeholder,
    'autocomplete' => $autocomplete,
    'required' => $required,
    'disabled' => $disabled,
    'readonly' => $readonly,
    'aria-invalid' => $error !== null ? 'true' : null,
    'aria-describedby' => trim(($idAjuda ?? '') . ' ' . ($idErro ?? '')) ?: null,
    'class' => componenteClasses(
        'block h-10 w-full rounded-control border bg-surface px-3 text-sm text-text placeholder:text-muted shadow-xs',
        'focus:border-input focus:outline-3 focus:outline-offset-0 focus:outline-ring/50',
        'disabled:cursor-not-allowed disabled:bg-surface-subtle disabled:opacity-70 read-only:bg-surface-subtle',
        $error !== null ? 'border-danger' : 'border-input',
        $class
    ),
];
?>
<div class="flex flex-col gap-1.5">
    <label for="<?= e($id) ?>" class="<?= e($hide_label ? 'sr-only' : 'text-sm font-medium text-text') ?>">
        <?= e($label) ?><?php if ($required) { ?> <span class="text-danger-ink" aria-hidden="true">*</span><?php } ?>
    </label>
    <input<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
    <?php if ($help !== null) { ?><p id="<?= e($idAjuda) ?>" class="text-xs text-muted"><?= e($help) ?></p><?php } ?>
    <?php if ($error !== null) { ?><p id="<?= e($idErro) ?>" class="text-xs font-medium text-danger-ink"><?= e($error) ?></p><?php } ?>
</div>
