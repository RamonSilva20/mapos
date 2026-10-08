<?php
/**
 * Botão, ou link com aparência de botão quando href é informado.
 *
 * @var string      $label     Texto do botão (com icon_only, vira texto para leitor de tela)
 * @var string      $variant   primary | outline | ghost | danger | inverted | ghost-on-dark | link
 *                             (DESIGN.md: um primary por tela; inverted e ghost-on-dark só sobre fundo escuro)
 * @var string      $size      sm | md | lg
 * @var string      $type      button | submit | reset (ignorado com href)
 * @var string|null $href      Renderiza <a> em vez de <button>
 * @var string|null $icon      Ícone Lucide do sprite, ex. plus (ver icone_helper.php)
 * @var bool        $icon_only Mostra só o ícone; o label continua acessível
 * @var bool        $disabled
 * @var string|null $name
 * @var string|null $value
 * @var string|null $id
 * @var string      $class     Classes extras
 * @var array       $attrs     Atributos extras (ex. data-modal-abrir)
 */
$variantes = [
    // DESIGN.md button-primary: rótulo sempre ink (6,00:1), nunca branco.
    'primary' => 'border border-transparent bg-primary text-on-primary text-button-cap uppercase hover:bg-primary-hover active:bg-primary-pressed',
    'outline' => 'border border-hairline-cool bg-transparent text-text text-button-cap-light uppercase hover:bg-surface-subtle dark:border-input',
    'ghost' => 'border border-transparent bg-transparent text-muted text-button-cap-light uppercase hover:bg-surface-subtle hover:text-text',
    // Só ações destrutivas, de preferência dentro de modal-confirm.
    'danger' => 'border border-transparent bg-danger text-on-dark text-button-cap uppercase hover:opacity-90 active:bg-danger-ink',
    // Sobre fundo escuro (telas de entrada): branco com ink.
    'inverted' => 'border border-transparent bg-on-dark text-ink text-button-cap uppercase shadow-elev-1 active:bg-press-light active:text-ink-press active:shadow-elev-4',
    'ghost-on-dark' => 'border border-transparent bg-on-dark-faint text-on-dark text-button-cap uppercase hover:bg-on-dark/25',
    'link' => 'border border-transparent bg-transparent text-primary-strong text-label-md underline underline-offset-4 hover:decoration-2',
];

$tamanhos = [
    // DESIGN.md: botões com 44px no celular, inclusive os pequenos.
    // Só ícone em sm: 36px, o tamanho das ações de linha do data-table.
    'sm' => $icon_only ? 'size-9 max-sm:size-11' : 'h-8 gap-1.5 px-3 max-sm:h-11',
    'md' => $icon_only ? 'size-10 max-sm:size-11' : 'h-10 gap-2 px-4 max-sm:h-11',
    'lg' => $icon_only ? 'size-12' : 'h-12 gap-2 px-5',
];

$atributos = [
    'id' => $id,
    'class' => componenteClasses(
        'inline-flex shrink-0 items-center justify-center rounded-md whitespace-nowrap transition-colors',
        'focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-ring/50',
        // DESIGN.md button-disabled: legível, sem opacidade.
        'disabled:cursor-not-allowed disabled:border-transparent disabled:bg-disabled-bg disabled:text-disabled disabled:shadow-none',
        'aria-disabled:pointer-events-none aria-disabled:border-transparent aria-disabled:bg-disabled-bg aria-disabled:text-disabled',
        $variantes[$variant],
        $tamanhos[$size],
        $class
    ),
];

if ($icon_only) {
    $atributos['title'] = $label;
}

if ($href !== null) {
    $tag = 'a';
    $atributos['href'] = $disabled ? null : $href;
    $atributos['aria-disabled'] = $disabled ? 'true' : null;
    $atributos['role'] = $disabled ? 'link' : null;
} else {
    $tag = 'button';
    $atributos['type'] = $type;
    $atributos['name'] = $name;
    $atributos['value'] = $value;
    $atributos['disabled'] = $disabled;
}
?>
<<?= e($tag) ?><?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
    <?php if ($icon !== null) { ?><?= icon($icon, ['class' => 'size-[1.25em]']) ?><?php } ?>
    <span class="<?= e($icon_only ? 'sr-only' : '') ?>"><?= e($label) ?></span>
</<?= e($tag) ?>>
