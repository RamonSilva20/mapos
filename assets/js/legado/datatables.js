/*
 * DataTables na tabela #tabela das telas legadas, como o tema/rodape.php da
 * v4 fazia num <script> inline. A configuração (ligado, itens por página e
 * arquivo de idioma) vem do bloco JSON #layout-legado.
 */
(function ($) {
  var bloco = document.getElementById('layout-legado');

  if (!bloco || !$) {
    return;
  }

  var dados = JSON.parse(bloco.textContent).dataTable || {};

  $(function () {
    if (!dados.ativo || !$.fn.dataTable) {
      return;
    }

    $('#tabela').dataTable({
      pageLength: dados.porPagina,
      ordering: false,
      info: false,
      language: {
        url: dados.idioma,
      },
      oLanguage: {
        sSearch: 'Pesquisa rápida na tabela abaixo:',
      },
    });
  });
})(window.jQuery);
