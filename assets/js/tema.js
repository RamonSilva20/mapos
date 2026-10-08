/*
 * Modo "sistema" do tema da v5.
 *
 * Carregado no <head> do layout novo, sem defer, para aplicar a classe .dark
 * antes da primeira pintura. Nos modos claro e escuro o servidor já manda o
 * <html> certo (ver temaAtributosHtml() em application/helpers/tema_helper.php),
 * e este script não faz nada.
 *
 * Fica em arquivo, e não inline, para não precisar de 'unsafe-inline' na CSP.
 */
(function () {
  var html = document.documentElement;

  if (html.getAttribute('data-tema-modo') !== 'sistema' || !window.matchMedia) {
    return;
  }

  var consulta = window.matchMedia('(prefers-color-scheme: dark)');

  function aplicar() {
    html.classList.toggle('dark', consulta.matches);
  }

  aplicar();

  // O sistema pode trocar de modo com a página aberta (ex.: modo noturno
  // automático).
  if (consulta.addEventListener) {
    consulta.addEventListener('change', aplicar);
  } else if (consulta.addListener) {
    consulta.addListener(aplicar);
  }
})();
