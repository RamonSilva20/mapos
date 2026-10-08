<?php
/**
 * Opção de um grupo de rádio (DESIGN.md radio). Para o grupo, envolva as
 * opções num <fieldset> com <legend>.
 *
 *     <fieldset><legend class="text-label-md">Prioridade</legend>
 *         <?= component('radio', ['name' => 'prioridade', 'value' => 'normal', 'label' => 'Normal', 'checked' => true]) ?>
 *         <?= component('radio', ['name' => 'prioridade', 'value' => 'urgente', 'label' => 'Urgente']) ?>
 *     </fieldset>
 *
 * Marcada: fundo laranja com o ponto em ink.
 *
 * @var string      $name
 * @var string      $value
 * @var string      $label
 * @var bool        $checked
 * @var bool        $required
 * @var bool        $disabled
 * @var string|null $id      Padrão: derivado de name e value
 * @var string      $class   Classes extras do <label>
 * @var array       $attrs   Atributos extras do <input>
 */
$id = $id ?? componenteId($name . '-' . $value);

$atributos = [
    'type' => 'radio',
    'id' => $id,
    'name' => $name,
    'value' => $value,
    'checked' => $checked,
    'required' => $required,
    'disabled' => $disabled,
    'class' => componenteClasses(
        'peer size-5 cursor-pointer appearance-none rounded-full border-[1.5px] border-input bg-field',
        'checked:border-primary checked:bg-primary',
        'focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-ring/50',
        'disabled:cursor-not-allowed disabled:opacity-60'
    ),
];
?>
<label class="<?= e(componenteClasses('inline-flex cursor-pointer items-start gap-2.5 text-label-md text-text has-disabled:cursor-not-allowed has-disabled:text-muted', $class)) ?>">
    <span class="relative mt-0.5 inline-flex size-5 shrink-0">
        <input<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
        <span class="pointer-events-none invisible absolute inset-0 m-auto size-2 rounded-full bg-on-primary peer-checked:visible" aria-hidden="true"></span>
    </span>
    <span><?= e($label) ?></span>
</label>
