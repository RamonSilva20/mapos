<?php
/**
 * Confirmação de ação destrutiva (DESIGN.md modal-confirm).
 *
 * Mesmo <dialog> e mesmo módulo do componente modal: um gatilho com
 * data-modal-abrir="<id>" abre, Esc e "Cancelar" fecham. A confirmação é um
 * button-danger, nunca laranja; o texto diz o que será perdido.
 *
 *     <?= component('button', ['label' => 'Excluir', 'variant' => 'danger', 'attrs' => ['data-modal-abrir' => 'excluir-os']]) ?>
 *     <?= component('modal-confirm', [
 *         'id' => 'excluir-os',
 *         'title' => 'Excluir OS #1042?',
 *         'message' => 'Produtos, serviços e anexos também serão removidos.',
 *         'confirm_attrs' => ['form' => 'form-excluir-os'],
 *     ]) ?>
 *
 * O botão de confirmação é type="submit": aponte-o para um formulário (com o
 * token CSRF) pelo atributo form em confirm_attrs, ou passe confirm_href
 * para um link.
 *
 * @var string                  $id
 * @var string                  $title
 * @var string|HtmlSeguro|array $message       Consequências da ação
 * @var string                  $confirm_label
 * @var string                  $cancel_label
 * @var string|null             $confirm_href
 * @var array                   $confirm_attrs Atributos extras do botão de confirmação
 * @var string                  $icon          Ícone Lucide do círculo de perigo
 * @var bool                    $open
 * @var string                  $class
 * @var array                   $attrs
 */
if (! is_array($confirm_attrs)) {
    throw new InvalidArgumentException('A prop confirm_attrs de modal-confirm deve ser um array.');
}

$atributos = [
    'id' => $id,
    'data-module' => 'componentes/modal',
    'data-modal-aberto' => $open ? 'true' : null,
    'role' => 'alertdialog',
    'aria-labelledby' => $id . '-titulo',
    'aria-describedby' => $id . '-mensagem',
    'class' => componenteClasses(
        'm-auto w-[calc(100%-2rem)] max-w-[21.25rem] rounded-xl border border-border bg-surface p-6 text-text shadow-overlay',
        'backdrop:bg-backdrop',
        $class
    ),
];
?>
<dialog<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
    <span class="inline-flex size-10 items-center justify-center rounded-full bg-danger-soft text-danger-ink"><?= icon($icon, ['class' => 'size-5']) ?></span>
    <h2 id="<?= e($id . '-titulo') ?>" class="mt-3 text-lg leading-snug font-semibold text-text"><?= e($title) ?></h2>
    <div id="<?= e($id . '-mensagem') ?>" class="mt-1.5 text-caption text-muted"><?= componenteConteudo($message) ?></div>
    <div class="mt-6 flex flex-wrap justify-end gap-2">
        <?= component('button', ['label' => $cancel_label, 'variant' => 'outline', 'attrs' => ['data-modal-fechar' => true, 'autofocus' => true]]) ?>
        <?= component('button', [
            'label' => $confirm_label,
            'variant' => 'danger',
            'type' => 'submit',
            'href' => $confirm_href,
            'attrs' => $confirm_attrs,
        ]) ?>
    </div>
</dialog>
