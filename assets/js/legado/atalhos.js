/*
 * Atalhos de teclado do painel (F1 a F7 e Esc), os mesmos da v4.
 *
 * Antes eram um <script> inline no tema/topo.php. As URLs vêm do bloco JSON
 * #layout-legado (page_data() em tema/topo.php, layoutDadosLegado() no PHP).
 * Depende do assets/js/shortcut.js, carregado antes.
 */
(function () {
  var bloco = document.getElementById('layout-legado');

  if (!bloco || typeof window.shortcut === 'undefined') {
    return;
  }

  var dados = JSON.parse(bloco.textContent);

  function ir(url) {
    return function () {
      window.location.href = url;
    };
  }

  Object.keys(dados.atalhos || {}).forEach(function (tecla) {
    window.shortcut.add(tecla, ir(dados.atalhos[tecla]));
  });

  (dados.atalhosBloqueados || []).forEach(function (tecla) {
    window.shortcut.add(tecla, function () {});
  });
})();
