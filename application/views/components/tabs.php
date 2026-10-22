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
 * rótulo curto entra no lugar do completo, para as abas caberem sem rolagem
 * (o completo continua para o leitor de tela, em sr-only).
 *
 * Quando as abas não cabem, a faixa rola na horizontal. Sem JavaScript ela
 * continua rolável (toque, Shift + roda) e a borda esmaece onde há aba
 * escondida. Com JavaScript (componentes/abas) a aba ativa vem para o centro
 * ao carregar, a roda do mouse rola a faixa e as setas das pontas rolam uma
 * "página"; cada seta só aparece quando há aba escondida daquele lado. As
 * setas são para o mouse: o teclado chega às abas pelo Tab (o navegador
 * rola a aba focada para a vista, e o scroll-padding a mantém fora das
 * setas), então elas ficam fora da ordem de foco e escondidas do leitor de
 * tela (tabindex -1 e aria-hidden, como no Material).
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

// A linha de base fica na nav, fora da faixa que esmaece nas pontas.
$atributos = [
    'id' => $id,
    'aria-label' => $label,
    'data-module' => 'componentes/abas',
    'class' => componenteClasses('relative shadow-[inset_0_-1px_0_var(--color-border)]', $class),
];

// Setas das pontas: hidden até o módulo mostrar. O degradê usa o fundo da
// página (bg-bg) para cobrir as abas que passam por baixo; só o botão recebe
// o mouse, então a aba sob o degradê continua clicável.
$setas = [
    'anterior' => ['icon' => 'chevron-left', 'label' => 'Abas anteriores', 'class' => 'left-0 bg-linear-to-r pr-6'],
    'proxima' => ['icon' => 'chevron-right', 'label' => 'Próximas abas', 'class' => 'right-0 justify-end bg-linear-to-l pl-6'],
];
?>
<nav<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>>
    <div data-abas-lista class="abas-rolagem flex gap-1 overflow-x-auto scroll-px-16 [scrollbar-width:none]">
        <?php foreach ($items as $item) { ?>
            <?php $ativa = ! empty($item['active']); ?>
            <a<?= componenteAtributos([
                'href' => $item['url'],
                'aria-current' => $ativa ? 'page' : null,
                'class' => componenteClasses(
                    'relative inline-flex items-center gap-2 px-3.5 py-3 text-[0.9375rem] leading-tight whitespace-nowrap rounded-t-md',
                    'focus-visible:outline-3 focus-visible:-outline-offset-3 focus-visible:outline-ring',
                    $ativa
                        ? 'font-semibold text-text after:absolute after:inset-x-2.5 after:bottom-0 after:h-0.5 after:rounded-full after:bg-primary'
                        : 'font-medium text-muted hover:text-text'
                ),
            ]) ?>>
                <?php if (! empty($item['icon'])) { ?><?= icon((string) $item['icon'], ['class' => 'size-4 max-sm:hidden']) ?><?php } ?>
                <?php if (! empty($item['short'])) { ?>
                    <span class="sm:hidden" aria-hidden="true"><?= e($item['short']) ?></span><span class="max-sm:sr-only"><?= e($item['label']) ?></span>
                <?php } else { ?>
                    <?= e($item['label']) ?>
                <?php } ?>
                <?php if (isset($item['count']) && $item['count'] !== null) { ?><?= component('badge', ['label' => (string) $item['count'], 'size' => 'sm']) ?><?php } ?>
            </a>
        <?php } ?>
    </div>
    <?php foreach ($setas as $lado => $seta) { ?>
        <div data-abas-seta="<?= e($lado) ?>" hidden class="<?= e(componenteClasses('pointer-events-none absolute top-0 bottom-px flex items-center from-bg from-60% to-transparent', $seta['class'])) ?>">
            <?= component('button', [
                'label' => $seta['label'],
                'icon' => $seta['icon'],
                'icon_only' => true,
                'variant' => 'ghost',
                'size' => 'sm',
                'class' => 'pointer-events-auto',
                'attrs' => ['aria-label' => $seta['label'], 'aria-hidden' => 'true', 'tabindex' => '-1'],
            ]) ?>
        </div>
    <?php } ?>
</nav>
