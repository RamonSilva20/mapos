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
    'info' => ['info', 'text-info-ink'],
    'success' => ['circle-check', 'text-success-ink'],
    'warning' => ['triangle-alert', 'text-warning-ink'],
    'danger' => ['circle-alert', 'text-danger-ink'],
];

// DESIGN.md toast: cartão nível 2, ícone na cor do estado, título 600 e
// linha secundária em muted. assets/js/lib/toast.js repete estas classes.
$atributos = [
    'id' => $id,
    'role' => in_array($variant, ['warning', 'danger'], true) ? 'alert' : 'status',
    'aria-atomic' => 'true',
    'data-module' => 'componentes/toast',
    'data-duracao' => (string) max(0, (int) $duration),
    'class' => componenteClasses(
        'pointer-events-auto flex w-[340px] max-w-full items-start gap-3 rounded-xl border border-border bg-surface py-3.5 pr-3.5 pl-4 text-caption text-text shadow-overlay',
        $class
    ),
];
?>
<div<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
    <?= icon($icones[$variant][0], ['class' => 'mt-0.5 size-5 ' . $icones[$variant][1]]) ?>
    <div class="min-w-0 flex-1">
        <?php if ($title !== null) { ?><p class="text-[0.9375rem] leading-snug font-semibold"><?= e($title) ?></p><?php } ?>
        <div class="<?= e($title !== null ? 'mt-0.5 text-muted' : '') ?>"><?= componenteConteudo($message) ?></div>
    </div>
    <?php if ($dismissible) { ?>
        <button type="button" data-dispensar class="-m-1 inline-flex size-8 shrink-0 items-center justify-center rounded-md text-muted hover:bg-surface-subtle hover:text-text focus-visible:outline-3 focus-visible:outline-ring/50">
            <?= icon('x', ['class' => 'size-4']) ?>
            <span class="sr-only"><?= e($dismiss_label) ?></span>
        </button>
    <?php } ?>
</div>
