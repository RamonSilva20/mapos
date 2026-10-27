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
 *    Quem já escolheu um modo tem a preferência gravada no usuário (#2854):
 *    o servidor a manda no <html> (data-tema-usuario) e ela vale em qualquer
 *    navegador, no lugar do localStorage.
 *
 * 2. Cor do tema (data-tema), com o mesmo critério do modo: a preferência
 *    gravada no usuário (data-tema-cor-usuario) manda; sem ela, vale o cache
 *    do navegador (chave mapos:tema-cor), que é gravada ao trocar a cor.
 *
 * 3. Sidebar recolhida do layout (chave mapos:sidebar), aplicada como
 *    <html data-sidebar="recolhida"> para a página já abrir no estado certo.
 *
 * 4. Valores ocultos no painel (chave mapos:valores-ocultos), aplicados como
 *    <html data-valores="ocultos"> pelo mesmo motivo.
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

  // Com preferência gravada para o usuário (data-tema-usuario, #2854) o
  // servidor manda: o localStorage de um navegador não a sobrescreve.
  var modoLocal = html.hasAttribute('data-tema-usuario') ? null : ler('mapos:tema-modo');
  if (MODOS.indexOf(modoLocal) !== -1) {
    html.setAttribute('data-tema-modo', modoLocal);
    html.classList.toggle('dark', modoLocal === 'escuro');
  }

  // Cor do tema (data-tema, #3032). O servidor já manda o atributo; o
  // localStorage só entra quando a pessoa não tem preferência gravada
  // (data-tema-cor-usuario) e a tela não fixa a marca (data-tema-fixo, área
  // do cliente). Allow-list: o padrão (laranja) não leva atributo.
  var CORES = ['azul', 'verde', 'violeta', 'grafite'];
  if (!html.hasAttribute('data-tema-cor-usuario') && !html.hasAttribute('data-tema-fixo')) {
    var corLocal = ler('mapos:tema-cor');
    if (CORES.indexOf(corLocal) !== -1) {
      html.setAttribute('data-tema', corLocal);
    } else if (corLocal === 'padrao') {
      html.removeAttribute('data-tema');
    }
  }

  if (ler('mapos:sidebar') === 'recolhida') {
    html.setAttribute('data-sidebar', 'recolhida');
  }

  // Valores em dinheiro do painel ocultos pelo botão de olho (#3022).
  if (ler('mapos:valores-ocultos') === '1') {
    html.setAttribute('data-valores', 'ocultos');
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
