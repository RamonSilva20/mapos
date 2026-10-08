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
        'block w-full rounded-sm border bg-field px-3 py-2 text-body-md text-text',
        // O múltiplo mantém a aparência nativa (lista com rolagem); a opção
        // marcada usa o tint do item ativo em vez do azul do sistema, e a barra
        // de rolagem usa os tokens (sem a calha clara do sistema no escuro).
        $multiple ? 'min-h-24 [scrollbar-color:var(--color-input)_transparent] [&_option:checked]:bg-primary-tint [&_option:checked]:text-text' : 'appearance-none pr-10 max-sm:min-h-11',
        // DESIGN.md text-input: borda hairline-input (3:1), texto body-md 400,
        // foco com sombra interna e anel azul de 3px, erro em danger.
        'focus:outline-3 focus:outline-offset-0 focus:outline-ring/50 focus:shadow-field-focus',
        'disabled:cursor-not-allowed disabled:bg-surface-subtle disabled:text-muted',
        $error !== null ? 'border-danger' : 'border-input',
        $class
    ),
];
?>
<div class="flex flex-col gap-1.5">
    <label for="<?= e($id) ?>" class="<?= e($hide_label ? 'sr-only' : 'text-label-md text-text') ?>">
        <?= e($label) ?><?php if ($required) { ?> <span class="text-danger-ink" aria-hidden="true">*</span><?php } ?>
    </label>
    <div class="relative">
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
        <?php if (! $multiple) { ?><?= icon('chevron-down', ['class' => 'pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-muted']) ?><?php } ?>
    </div>
    <?php if ($help !== null) { ?><p id="<?= e($idAjuda) ?>" class="text-caption text-muted"><?= e($help) ?></p><?php } ?>
    <?php if ($error !== null) { ?><p id="<?= e($idErro) ?>" class="flex items-start gap-1.5 text-caption text-danger-ink"><?= icon('circle-alert', ['class' => 'mt-0.5 size-4']) ?><span><?= e($error) ?></span></p><?php } ?>
</div>
