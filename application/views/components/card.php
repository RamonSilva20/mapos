<?php
/**
 * Cartão: superfície com título, conteúdo, ações e rodapé.
 *
 * Os slots (body, actions, footer) aceitam texto, que é escapado, ou um
 * HtmlSeguro (a saída de component() ou de html_purificado()), ou uma lista
 * dos dois.
 *
 * @var string|null                  $title
 * @var string|null                  $subtitle
 * @var string|HtmlSeguro|array|null $body
 * @var string|HtmlSeguro|array|null $actions  Ao lado do título (ex. botões)
 * @var string|HtmlSeguro|array|null $footer
 * @var bool                         $padded   Espaçamento interno no corpo
 * @var string|null                  $id
 * @var string                       $class
 * @var array                        $attrs
 */
$idTitulo = $title !== null && $id !== null ? $id . '-titulo' : null;

$atributos = [
    'id' => $id,
    'aria-labelledby' => $idTitulo,
    'class' => componenteClasses('overflow-hidden rounded-card border border-border bg-surface text-text shadow-card', $class),
];
?>
<section<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
    <?php if ($title !== null || $actions !== null) { ?>
        <header class="flex flex-wrap items-start justify-between gap-3 border-b border-border px-5 py-4">
            <div class="min-w-0">
                <?php if ($title !== null) { ?><h2<?= componenteAtributos(['id' => $idTitulo]) ?> class="text-base font-semibold text-text"><?= e($title) ?></h2><?php } ?>
                <?php if ($subtitle !== null) { ?><p class="mt-0.5 text-sm text-muted"><?= e($subtitle) ?></p><?php } ?>
            </div>
            <?php if ($actions !== null) { ?><div class="flex flex-wrap items-center gap-2"><?= componenteConteudo($actions) ?></div><?php } ?>
        </header>
    <?php } ?>
    <?php if ($body !== null) { ?>
        <div class="<?= e($padded ? 'px-5 py-4' : '') ?>"><?= componenteConteudo($body) ?></div>
    <?php } ?>
    <?php if ($footer !== null) { ?>
        <footer class="flex flex-wrap items-center justify-end gap-2 border-t border-border bg-surface-2 px-5 py-3"><?= componenteConteudo($footer) ?></footer>
    <?php } ?>
</section>
