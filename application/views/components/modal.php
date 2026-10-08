<?php
/**
 * Janela modal sobre o <dialog> nativo.
 *
 * O <dialog> aberto com showModal() deixa o resto da página inerte (o foco
 * fica preso no modal), fecha com Esc e devolve o foco a quem o abriu. O
 * módulo assets/js/modules/componentes/modal.js só liga os gatilhos:
 *
 *     <?= component('button', ['label' => 'Excluir', 'attrs' => ['data-modal-abrir' => 'modal-excluir']]) ?>
 *     <?= component('modal', ['id' => 'modal-excluir', 'title' => 'Excluir cliente?', 'body' => '...', 'footer' => [...]]) ?>
 *
 * Dentro do modal, qualquer elemento com data-modal-fechar fecha. Clicar no
 * fundo também fecha.
 *
 * @var string                       $id
 * @var string                       $title
 * @var string|HtmlSeguro|array|null $body
 * @var string|HtmlSeguro|array|null $footer      Normalmente os botões de ação
 * @var string                       $size        sm | md | lg | xl
 * @var string                       $close_label Texto do botão de fechar (leitor de tela)
 * @var bool                         $open        Abre ao carregar a página
 * @var string                       $class
 * @var array                        $attrs
 */
$larguras = ['sm' => 'max-w-sm', 'md' => 'max-w-lg', 'lg' => 'max-w-2xl', 'xl' => 'max-w-4xl'];

$atributos = [
    'id' => $id,
    'data-module' => 'componentes/modal',
    'data-modal-aberto' => $open ? 'true' : null,
    'aria-labelledby' => $id . '-titulo',
    'class' => componenteClasses(
        'm-auto w-[calc(100%-2rem)] rounded-xl border border-border bg-surface p-0 text-text shadow-overlay',
        'backdrop:bg-backdrop backdrop:backdrop-blur-[1px]',
        $larguras[$size],
        $class
    ),
];
?>
<dialog<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
    <div class="flex items-start justify-between gap-4 border-b border-border px-6 py-4">
        <h2 id="<?= e($id . '-titulo') ?>" class="text-lg leading-snug font-semibold text-text"><?= e($title) ?></h2>
        <button type="button" data-modal-fechar class="-m-1 inline-flex size-8 items-center justify-center rounded-md text-muted hover:bg-surface-subtle hover:text-text focus-visible:outline-3 focus-visible:outline-ring/50">
            <?= icon('x', ['class' => 'size-5']) ?>
            <span class="sr-only"><?= e($close_label) ?></span>
        </button>
    </div>
    <?php if ($body !== null) { ?><div class="px-6 py-5 text-body-md"><?= componenteConteudo($body) ?></div><?php } ?>
    <?php if ($footer !== null) { ?>
        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-border bg-surface-subtle px-6 py-3"><?= componenteConteudo($footer) ?></div>
    <?php } ?>
</dialog>
