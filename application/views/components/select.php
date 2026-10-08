<?php
/**
 * Lista de seleção com label, ajuda e erro associados.
 *
 * @var string                 $name
 * @var string                 $label
 * @var array                  $options     ['valor' => 'Texto'] ou [['value' => ..., 'label' => ..., 'disabled' => bool], ...]
 * @var string|int|array|null  $selected    Valor selecionado; array quando multiple
 * @var string|null            $placeholder Primeira opção, vazia (ex. "Selecione...")
 * @var bool                   $multiple
 * @var string|null            $help
 * @var string|null            $error
 * @var bool                   $required
 * @var bool                   $disabled
 * @var bool                   $hide_label
 * @var string|null            $id
 * @var string                 $class
 * @var array                  $attrs
 */
if (! is_array($options)) {
    throw new InvalidArgumentException('A prop options de select deve ser um array.');
}

$id = $id ?? componenteId($name);
$idAjuda = $help !== null ? $id . '-ajuda' : null;
$idErro = $error !== null ? $id . '-erro' : null;

$selecionados = array_map('strval', is_array($selected) ? $selected : ($selected === null ? [] : [$selected]));

$opcoes = [];
foreach ($options as $chave => $opcao) {
    if (is_array($opcao)) {
        if (! array_key_exists('value', $opcao) || ! array_key_exists('label', $opcao)) {
            throw new InvalidArgumentException('Cada opção de select em formato de lista precisa de value e label.');
        }
        $opcoes[] = ['value' => (string) $opcao['value'], 'label' => $opcao['label'], 'disabled' => ! empty($opcao['disabled'])];
    } else {
        $opcoes[] = ['value' => (string) $chave, 'label' => $opcao, 'disabled' => false];
    }
}

$atributos = [
    'id' => $id,
    'name' => $multiple ? $name . '[]' : $name,
    'multiple' => $multiple,
    'required' => $required,
    'disabled' => $disabled,
    'aria-invalid' => $error !== null ? 'true' : null,
    'aria-describedby' => trim(($idAjuda ?? '') . ' ' . ($idErro ?? '')) ?: null,
    'class' => componenteClasses(
        'block w-full rounded-control border bg-surface px-3 text-sm text-text shadow-xs',
        $multiple ? 'min-h-24 py-2' : 'h-10',
        'focus:border-accent-500 focus:outline-2 focus:outline-offset-0 focus:outline-ring/40',
        'disabled:cursor-not-allowed disabled:bg-surface-2 disabled:opacity-70',
        $error !== null ? 'border-danger' : 'border-border',
        $class
    ),
];
?>
<div class="flex flex-col gap-1.5">
    <label for="<?= e($id) ?>" class="<?= e($hide_label ? 'sr-only' : 'text-sm font-medium text-text') ?>">
        <?= e($label) ?><?php if ($required) { ?> <span class="text-danger" aria-hidden="true">*</span><?php } ?>
    </label>
    <select<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
        <?php if ($placeholder !== null) { ?>
            <option value=""<?= componenteAtributos(['selected' => $selecionados === []]) ?>><?= e($placeholder) ?></option>
        <?php } ?>
        <?php foreach ($opcoes as $opcao) { ?>
            <option<?= componenteAtributos([
                'value' => $opcao['value'],
                'selected' => in_array($opcao['value'], $selecionados, true),
                'disabled' => $opcao['disabled'],
            ]) ?>><?= e($opcao['label']) ?></option>
        <?php } ?>
    </select>
    <?php if ($help !== null) { ?><p id="<?= e($idAjuda) ?>" class="text-xs text-muted"><?= e($help) ?></p><?php } ?>
    <?php if ($error !== null) { ?><p id="<?= e($idErro) ?>" class="text-xs font-medium text-danger"><?= e($error) ?></p><?php } ?>
</div>
