<?php
/**
 * Botão, ou link com aparência de botão quando href é informado.
 *
 * @var string      $label     Texto do botão (com icon_only, vira texto para leitor de tela)
 * @var string      $variant   primary | secondary | ghost | danger | link
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
    'primary' => 'border border-transparent bg-primary text-on-primary hover:bg-primary-hover active:bg-primary-pressed',
    'secondary' => 'border border-border bg-surface text-text hover:bg-surface-subtle',
    'ghost' => 'border border-transparent bg-transparent text-text hover:bg-surface-subtle',
    'danger' => 'border border-transparent bg-danger text-on-dark hover:opacity-90',
    'link' => 'border border-transparent bg-transparent text-primary-strong underline-offset-4 hover:underline',
];

$tamanhos = [
    'sm' => $icon_only ? 'size-8 text-sm' : 'h-8 gap-1.5 px-3 text-sm',
    'md' => $icon_only ? 'size-10 text-base' : 'h-10 gap-2 px-4 text-sm',
    'lg' => $icon_only ? 'size-12 text-lg' : 'h-12 gap-2 px-5 text-base',
];

$atributos = [
    'id' => $id,
    'class' => componenteClasses(
        'inline-flex shrink-0 items-center justify-center rounded-control font-medium whitespace-nowrap transition-colors',
        'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring',
        'disabled:cursor-not-allowed disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:opacity-50',
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
