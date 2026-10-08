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
    'info' => ['caixa' => 'border-info/40 bg-info-soft', 'icone' => 'bx-info-circle text-info-ink'],
    'success' => ['caixa' => 'border-success/40 bg-success-soft', 'icone' => 'bx-check-circle text-success-ink'],
    'warning' => ['caixa' => 'border-warning/40 bg-warning-soft', 'icone' => 'bx-error text-warning-ink'],
    'danger' => ['caixa' => 'border-danger/40 bg-danger-soft', 'icone' => 'bx-error-circle text-danger-ink'],
];

$atributos = [
    'id' => $id,
    'role' => in_array($variant, ['warning', 'danger'], true) ? 'alert' : 'status',
    'data-module' => $dismissible ? 'componentes/dispensavel' : null,
    'class' => componenteClasses('flex items-start gap-3 rounded-card border px-4 py-3 text-sm text-text', $variantes[$variant]['caixa'], $class),
];
?>
<div<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
    <i class="<?= e('bx ' . $variantes[$variant]['icone'] . ' mt-0.5 text-lg leading-none') ?>" aria-hidden="true"></i>
    <div class="min-w-0 flex-1">
        <?php if ($title !== null) { ?><p class="font-semibold"><?= e($title) ?></p><?php } ?>
        <div class="<?= e($title !== null ? 'mt-0.5' : '') ?>"><?= componenteConteudo($message) ?></div>
    </div>
    <?php if ($dismissible) { ?>
        <button type="button" data-dispensar class="-m-1 inline-flex size-7 shrink-0 items-center justify-center rounded-control text-muted hover:bg-surface hover:text-text focus-visible:outline-2 focus-visible:outline-ring">
            <span aria-hidden="true" class="text-lg leading-none">&times;</span>
            <span class="sr-only"><?= e($dismiss_label) ?></span>
        </button>
    <?php } ?>
</div>
