/*
 * Preferências de aparência da v5, aplicadas antes da primeira pintura.
 *
 * Carregado no <head>, sem defer. Fica em arquivo, e não inline, para não
 * precisar de 'unsafe-inline' na CSP.
 *
 * 1. Modo de cor. O servidor manda o <html> com o modo das Configurações (ver
 *    temaAtributosHtml() em application/helpers/tema_helper.php). Se o usuário
 *    escolheu outro modo na topbar do layout, a escolha fica no localStorage
 *    (chave mapos:tema-modo) e vale neste navegador. No modo "sistema" a
 *    classe .dark segue o prefers-color-scheme, inclusive com a página aberta.
 *    A preferência gravada por usuário é a #2854.
 *
 * 2. Sidebar recolhida do layout (chave mapos:sidebar), aplicada como
 *    <html data-sidebar="recolhida"> para a página já abrir no estado certo.
 */
(function () {
  var html = document.documentElement;
  var MODOS = ['claro', 'escuro', 'sistema'];

  function ler(chave) {
    try {
      return window.localStorage.getItem(chave);
    } catch (erro) {
      // localStorage bloqueado (modo privado, política do navegador).
      return null;
    }
  }

  var modoLocal = ler('mapos:tema-modo');
  if (MODOS.indexOf(modoLocal) !== -1) {
    html.setAttribute('data-tema-modo', modoLocal);
    html.classList.toggle('dark', modoLocal === 'escuro');
  }

  if (ler('mapos:sidebar') === 'recolhida') {
    html.setAttribute('data-sidebar', 'recolhida');
  }

  if (!window.matchMedia) {
    return;
  }

  var consulta = window.matchMedia('(prefers-color-scheme: dark)');

  function aplicar() {
    if (html.getAttribute('data-tema-modo') === 'sistema') {
      html.classList.toggle('dark', consulta.matches);
    }
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
