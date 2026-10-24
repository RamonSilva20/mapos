<?php
/**
 * Valor em dinheiro que o botão de olho do painel pode ocultar (#3022).
 *
 *     component('valor-sensivel', ['value' => dinheiro($saldo)])
 *
 * Só marca o texto com data-valor-sensivel; quem esconde é o CSS, com
 * <html data-valores="ocultos"> (assets/js/tema.js e modules/painel/painel.js).
 * Os valores continuam no HTML: é preferência de exibição, não controle de
 * acesso (para isso há as permissões).
 *
 * @var string|HtmlSeguro|array $value
 * @var string|null             $id
 * @var string                  $class
 * @var array                   $attrs
 */
$atributos = [
    'id' => $id,
    'class' => $class !== '' ? $class : null,
    'data-valor-sensivel' => true,
];
?>
<span<?= componenteAtributos(componenteMesclarAtributos($atributos, $attrs)) ?>><?= componenteConteudo($value) ?></span>
