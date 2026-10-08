<?php
/**
 * Abas de navegação dentro de uma página (DESIGN.md tabs), como links.
 *
 *     component('tabs', ['items' => [
 *         ['label' => 'Detalhes', 'url' => site_url('os/editar/1'), 'active' => true],
 *         ['label' => 'Produtos', 'url' => site_url('os/editar/1/produtos'), 'count' => 2],
 *     ]])
 *
 * A aba ativa leva aria-current="page", texto em ink 600 e sublinhado laranja
 * de 2px: a segunda pista da regra do DESIGN.md (o laranja fino nunca marca o
 * estado sozinho).
 *
 * @var array       $items Lista de ['label', 'url', 'active' => bool, 'count' => int|null, 'icon' => string|null, 'short' => string|null]
 *
 * No celular (abaixo de sm) os ícones somem e, quando a aba tem short, o
 * rótulo curto entra no lugar do completo, para as abas caberem sem rolagem.
 * @var string      $label Nome da navegação para leitores de tela
 * @var string|null $id
 * @var string      $class
 * @var array       $attrs
 */
if (! is_array($items)) {
    throw new InvalidArgumentException('A prop items de tabs deve ser um array.');
}

foreach ($items as $item) {
    if (! is_array($item) || ! isset($item['label'], $item['url'])) {
        throw new InvalidArgumentException('Cada aba precisa de label e url.');
    }
    if (isset($item['icon']) && ! preg_match(COMPONENTE_ICONE, (string) $item['icon'])) {
        throw new InvalidArgumentException('Formato inválido para tabs.icon');
    }
}

$atributos = [
    'id' => $id,
    'aria-label' => $label,
    'class' => componenteClasses('abas-rolagem flex gap-1 overflow-x-auto shadow-[inset_0_-1px_0_var(--color-border)] [scrollbar-width:none]', $class),
];
?>
<nav<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
    <?php foreach ($items as $item) { ?>
        <?php $ativa = ! empty($item['active']); ?>
        <a<?= componenteAtributos([
            'href' => $item['url'],
            'aria-current' => $ativa ? 'page' : null,
            'class' => componenteClasses(
                'relative inline-flex items-center gap-2 px-3.5 py-3 text-[0.9375rem] leading-tight whitespace-nowrap rounded-t-md',
                'focus-visible:outline-3 focus-visible:-outline-offset-3 focus-visible:outline-ring/50',
                $ativa
                    ? 'font-semibold text-text after:absolute after:inset-x-2.5 after:bottom-0 after:h-0.5 after:rounded-full after:bg-primary'
                    : 'font-medium text-muted hover:text-text'
            ),
        ]) ?>>
            <?php if (! empty($item['icon'])) { ?><?= icon((string) $item['icon'], ['class' => 'size-4 max-sm:hidden']) ?><?php } ?>
            <?php if (! empty($item['short'])) { ?>
                <span class="sm:hidden"><?= e($item['short']) ?></span><span class="max-sm:hidden"><?= e($item['label']) ?></span>
            <?php } else { ?>
                <?= e($item['label']) ?>
            <?php } ?>
            <?php if (isset($item['count']) && $item['count'] !== null) { ?><?= component('badge', ['label' => (string) $item['count'], 'size' => 'sm']) ?><?php } ?>
        </a>
    <?php } ?>
</nav>
