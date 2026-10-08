<?php
/**
 * Liga/desliga (DESIGN.md switch): um checkbox com role="switch", então o
 * valor vai no formulário como qualquer checkbox.
 *
 * Trilho de 40×24px: hairline-input desligado, laranja ligado; o botão é
 * branco desligado e ink ligado.
 *
 * @var string      $name
 * @var string      $label
 * @var string      $value
 * @var bool        $checked
 * @var bool        $disabled
 * @var string|null $help
 * @var string|null $id      Padrão: derivado do name
 * @var string      $class   Classes extras do <label>
 * @var array       $attrs   Atributos extras do <input>
 */
$id = $id ?? componenteId($name);
$idAjuda = $help !== null ? $id . '-ajuda' : null;

$atributos = [
    'type' => 'checkbox',
    'role' => 'switch',
    'id' => $id,
    'name' => $name,
    'value' => $value,
    'checked' => $checked,
    'disabled' => $disabled,
    'aria-describedby' => $idAjuda,
    'class' => componenteClasses(
        'peer h-6 w-10 cursor-pointer appearance-none rounded-full bg-input transition-colors',
        'checked:bg-primary',
        'focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-ring/50',
        'disabled:cursor-not-allowed disabled:opacity-60'
    ),
];
?>
<div class="flex flex-col gap-1">
    <label class="<?= e(componenteClasses('inline-flex cursor-pointer items-center gap-2.5 text-label-md text-text has-disabled:cursor-not-allowed has-disabled:text-muted', $class)) ?>">
        <span class="relative inline-flex shrink-0">
            <input<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
            <span class="pointer-events-none absolute top-[3px] left-[3px] size-[18px] rounded-full bg-on-dark transition-transform peer-checked:translate-x-4 peer-checked:bg-on-primary" aria-hidden="true"></span>
        </span>
        <span><?= e($label) ?></span>
    </label>
    <?php if ($help !== null) { ?><p id="<?= e($idAjuda) ?>" class="pl-[50px] text-caption text-muted"><?= e($help) ?></p><?php } ?>
</div>
