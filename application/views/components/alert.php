<?php
/**
 * Aviso dentro da página.
 *
 * warning e danger usam role="alert" (o leitor de tela anuncia na hora);
 * info e success usam role="status". Com dismissible, um botão fecha o aviso
 * pelo módulo assets/js/modules/componentes/dispensavel.js.
 *
 * @var string|HtmlSeguro|array $message
 * @var string                  $variant       info | success | warning | danger
 * @var string|null             $title
 * @var bool                    $dismissible
 * @var string                  $dismiss_label
 * @var string|null             $id
 * @var string                  $class
 * @var array                   $attrs
 */
$variantes = [
    'info' => ['caixa' => 'bg-info-soft text-info-ink', 'icone' => 'info'],
    'success' => ['caixa' => 'bg-success-soft text-success-ink', 'icone' => 'circle-check'],
    'warning' => ['caixa' => 'bg-warning-soft text-warning-ink', 'icone' => 'triangle-alert'],
    'danger' => ['caixa' => 'bg-danger-soft text-danger-ink', 'icone' => 'circle-alert'],
];

// DESIGN.md alert-*: texto -ink sobre -soft, borda na mesma cor a 28%.
$atributos = [
    'id' => $id,
    'role' => in_array($variant, ['warning', 'danger'], true) ? 'alert' : 'status',
    'data-module' => $dismissible ? 'componentes/dispensavel' : null,
    'class' => componenteClasses('flex items-start gap-3 rounded-lg border border-current/28 px-4 py-3.5 text-[0.9375rem] leading-normal', $variantes[$variant]['caixa'], $class),
];
?>
<div<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
    <?= icon($variantes[$variant]['icone'], ['class' => 'mt-0.5 size-5']) ?>
    <div class="min-w-0 flex-1">
        <?php if ($title !== null) { ?><p class="font-semibold"><?= e($title) ?></p><?php } ?>
        <div><?= componenteConteudo($message) ?></div>
    </div>
    <?php if ($dismissible) { ?>
        <button type="button" data-dispensar class="-m-1 inline-flex size-8 shrink-0 items-center justify-center rounded-md opacity-80 hover:bg-current/10 hover:opacity-100 focus-visible:outline-3 focus-visible:outline-ring/50">
            <?= icon('x', ['class' => 'size-4']) ?>
            <span class="sr-only"><?= e($dismiss_label) ?></span>
        </button>
    <?php } ?>
</div>
