<?php
/**
 * Caixa de seleção com label à direita (DESIGN.md checkbox).
 *
 * Marcada: fundo laranja com o check em ink, nunca branco (2,87:1). A borda
 * desmarcada é a hairline-input (3:1).
 *
 * @var string      $name
 * @var string      $label
 * @var string      $value
 * @var bool        $checked
 * @var bool        $required
 * @var bool        $disabled
 * @var string|null $help
 * @var string|null $error
 * @var string|null $id      Padrão: derivado do name
 * @var string      $class   Classes extras do <label>
 * @var array       $attrs   Atributos extras do <input>
 */
$id = $id ?? componenteId($name);
$idAjuda = $help !== null ? $id . '-ajuda' : null;
$idErro = $error !== null ? $id . '-erro' : null;

$atributos = [
    'type' => 'checkbox',
    'id' => $id,
    'name' => $name,
    'value' => $value,
    'checked' => $checked,
    'required' => $required,
    'disabled' => $disabled,
    'aria-invalid' => $error !== null ? 'true' : null,
    'aria-describedby' => trim(($idAjuda ?? '') . ' ' . ($idErro ?? '')) ?: null,
    'class' => componenteClasses(
        'peer size-5 cursor-pointer appearance-none rounded-xs border-[1.5px] bg-field',
        'checked:border-primary checked:bg-primary',
        'focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-ring/50',
        'disabled:cursor-not-allowed disabled:opacity-60',
        $error !== null ? 'border-danger' : 'border-input'
    ),
];
?>
<div class="flex flex-col gap-1">
    <label class="<?= e(componenteClasses('inline-flex cursor-pointer items-start gap-2.5 text-label-md text-text has-disabled:cursor-not-allowed has-disabled:text-muted', $class)) ?>">
        <span class="relative mt-0.5 inline-flex size-5 shrink-0">
            <input<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
            <?= icon('check', ['class' => 'pointer-events-none invisible absolute inset-0 m-auto size-3.5 stroke-3 text-on-primary peer-checked:visible']) ?>
        </span>
        <span><?= e($label) ?><?php if ($required) { ?> <span class="text-danger-ink" aria-hidden="true">*</span><?php } ?></span>
    </label>
    <?php if ($help !== null) { ?><p id="<?= e($idAjuda) ?>" class="pl-[30px] text-caption text-muted"><?= e($help) ?></p><?php } ?>
    <?php if ($error !== null) { ?><p id="<?= e($idErro) ?>" class="flex items-start gap-1.5 pl-[30px] text-caption text-danger-ink"><?= icon('circle-alert', ['class' => 'mt-0.5 size-4']) ?><span><?= e($error) ?></span></p><?php } ?>
</div>
