/*
 * Globais que as telas legadas esperam encontrar (modo legado do layout, #2835).
 *
 * O tema/topo.php da v4 escrevia `window.BaseUrl = "..."` num <script> inline.
 * Agora o valor vem do <meta name="base-url"> e este arquivo, carregado no
 * <head> antes de qualquer script das views, o publica no mesmo nome.
 */
(function () {
  var meta = document.querySelector('meta[name="base-url"]');

  if (meta) {
    window.BaseUrl = meta.getAttribute('content');
  }
})();
