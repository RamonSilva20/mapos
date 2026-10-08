<?php
/**
 * Notificação temporária (toast).
 *
 * O componente renderiza um toast; quem o posiciona é a região de toasts do
 * layout (um contêiner com data-toast-region). O módulo
 * assets/js/modules/componentes/toast.js some com ele depois de duration
 * milissegundos (0 = fica até ser fechado) e pausa a contagem enquanto o
 * mouse ou o foco estão sobre ele.
 *
 * Do JavaScript, use mostrarToast() de assets/js/lib/toast.js, que monta o
 * mesmo HTML.
 *
 * @var string|HtmlSeguro|array $message
 * @var string                  $variant       info | success | warning | danger
 * @var string|null             $title
 * @var int                     $duration      Milissegundos; 0 não some sozinho
 * @var bool                    $dismissible
 * @var string                  $dismiss_label
 * @var string|null             $id
 * @var string                  $class
 * @var array                   $attrs
 */
$icones = [
    'info' => 'bx-info-circle text-info-ink',
    'success' => 'bx-check-circle text-success-ink',
    'warning' => 'bx-error text-warning-ink',
    'danger' => 'bx-error-circle text-danger-ink',
];

$atributos = [
    'id' => $id,
    'role' => in_array($variant, ['warning', 'danger'], true) ? 'alert' : 'status',
    'aria-atomic' => 'true',
    'data-module' => 'componentes/toast',
    'data-duracao' => (string) max(0, (int) $duration),
    'class' => componenteClasses(
        'pointer-events-auto flex w-80 max-w-full items-start gap-3 rounded-card border border-border bg-surface px-4 py-3 text-sm text-text shadow-overlay',
        $class
    ),
];
?>
<div<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
    <i class="<?= e('bx ' . $icones[$variant] . ' mt-0.5 text-lg leading-none') ?>" aria-hidden="true"></i>
    <div class="min-w-0 flex-1">
        <?php if ($title !== null) { ?><p class="font-semibold"><?= e($title) ?></p><?php } ?>
        <div class="<?= e($title !== null ? 'mt-0.5 text-muted' : '') ?>"><?= componenteConteudo($message) ?></div>
    </div>
    <?php if ($dismissible) { ?>
        <button type="button" data-dispensar class="-m-1 inline-flex size-7 shrink-0 items-center justify-center rounded-control text-muted hover:bg-surface-subtle hover:text-text focus-visible:outline-2 focus-visible:outline-ring">
            <span aria-hidden="true" class="text-lg leading-none">&times;</span>
            <span class="sr-only"><?= e($dismiss_label) ?></span>
        </button>
    <?php } ?>
</div>
