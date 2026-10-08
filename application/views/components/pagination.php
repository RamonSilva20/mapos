<?php
/**
 * Paginação.
 *
 * url é um modelo com {page} (número da página) ou {offset} (deslocamento,
 * (página - 1) × per_page, que é o que o CI_Pagination põe na URL e o que os
 * models do Map-OS recebem). Com {offset}, per_page é obrigatório.
 *
 *     component('pagination', [
 *         'total_pages' => 12,
 *         'current' => 3,
 *         'url' => site_url('clientes/gerenciar/{offset}'),
 *         'per_page' => 10,
 *     ])
 *
 * Com uma página só (ou nenhuma), não renderiza nada.
 *
 * @var int         $total_pages
 * @var int         $current
 * @var string      $url
 * @var int|null    $per_page
 * @var int         $window     Páginas mostradas de cada lado da atual
 * @var string      $label      aria-label do <nav>
 * @var string      $prev_label
 * @var string      $next_label
 * @var string|null $id
 * @var string      $class
 * @var array       $attrs
 */
$total = (int) $total_pages;
if ($total < 0) {
    throw new InvalidArgumentException('total_pages de pagination não pode ser negativo.');
}

$atual = min(max(1, (int) $current), max(1, $total));
$porPagina = $per_page === null ? null : (int) $per_page;

if ($total <= 1) {
    return;
}

$link = static fn (int $pagina): string => componentePaginaUrl($url, $pagina, $porPagina);

$item = 'inline-flex h-9 min-w-9 items-center justify-center gap-1 rounded-control px-3 text-sm font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring';
$normal = $item . ' border border-border bg-surface text-text hover:bg-surface-2';
$desligado = $item . ' border border-border bg-surface text-muted opacity-50';
$ativo = $item . ' border border-transparent bg-accent-600 text-accent-contrast';

$atributos = [
    'id' => $id,
    'aria-label' => $label,
    'class' => componenteClasses($class),
];
?>
<nav<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
    <ul class="flex flex-wrap items-center gap-1">
        <li>
            <?php if ($atual > 1) { ?>
                <a<?= componenteAtributos(['href' => $link($atual - 1), 'rel' => 'prev', 'class' => $normal]) ?>><i class="bx bx-chevron-left" aria-hidden="true"></i><?= e($prev_label) ?></a>
            <?php } else { ?>
                <span aria-disabled="true" class="<?= e($desligado) ?>"><i class="bx bx-chevron-left" aria-hidden="true"></i><?= e($prev_label) ?></span>
            <?php } ?>
        </li>
        <?php foreach (componentePaginas($atual, $total, (int) $window) as $pagina) { ?>
            <li>
                <?php if ($pagina === null) { ?>
                    <span class="inline-flex h-9 min-w-6 items-center justify-center text-muted" aria-hidden="true">&hellip;</span>
                <?php } elseif ($pagina === $atual) { ?>
                    <a<?= componenteAtributos(['href' => $link($pagina), 'aria-current' => 'page', 'class' => $ativo]) ?>><?= e($pagina) ?></a>
                <?php } else { ?>
                    <a<?= componenteAtributos(['href' => $link($pagina), 'class' => $normal]) ?>><span class="sr-only">Página </span><?= e($pagina) ?></a>
                <?php } ?>
            </li>
        <?php } ?>
        <li>
            <?php if ($atual < $total) { ?>
                <a<?= componenteAtributos(['href' => $link($atual + 1), 'rel' => 'next', 'class' => $normal]) ?>><?= e($next_label) ?><i class="bx bx-chevron-right" aria-hidden="true"></i></a>
            <?php } else { ?>
                <span aria-disabled="true" class="<?= e($desligado) ?>"><?= e($next_label) ?><i class="bx bx-chevron-right" aria-hidden="true"></i></span>
            <?php } ?>
        </li>
    </ul>
</nav>
