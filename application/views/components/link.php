<?php
/**
 * Link de texto (DESIGN.md link-on-light): ink com sublinhado permanente, que
 * é a pista do link, sem mudar de cor. No modo escuro segue o texto do tema.
 *
 *     component('link', ['label' => $cliente->nomeCliente, 'href' => site_url('clientes/visualizar/' . $id)])
 *     component('link', ['label' => 'Documentação', 'href' => 'https://...', 'external' => true])
 *
 * external abre em outra aba (rel="noopener noreferrer") e avisa o leitor de
 * tela. Para ação, use button; para destacar com cor, button variant link.
 *
 * @var string      $label
 * @var string      $href
 * @var bool        $external
 * @var string|null $id
 * @var string      $class
 * @var array       $attrs
 */
$atributos = [
    'id' => $id,
    'href' => $href,
    'target' => $external ? '_blank' : null,
    'rel' => $external ? 'noopener noreferrer' : null,
    'class' => componenteClasses(
        'rounded-xs text-text underline decoration-1 underline-offset-4 hover:decoration-2',
        'focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-ring/50',
        $class
    ),
];
?>
<a<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>><?= e($label) ?><?php if ($external) { ?><span class="sr-only"> (abre em outra aba)</span><?php } ?></a>
