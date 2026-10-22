// Testes do módulo das abas que transbordam (assets/js/modules/componentes/abas.js).
// Sem DOM real: a faixa, as abas e as setas são objetos falsos com a parte da
// API que o módulo usa (rolagem, medidas e ouvintes de evento).
import { test } from 'node:test';
import assert from 'node:assert/strict';

import iniciar, {
    deltaDaRoda,
    estadoDaRolagem,
    passoDaSeta,
    posicaoParaCentralizar,
    reduzMovimento,
} from '../../assets/js/modules/componentes/abas.js';

function criarOuvintes() {
    const registrados = {};

    return {
        registrados,
        addEventListener(tipo, funcao, opcoes) {
            (registrados[tipo] ??= []).push({ funcao, opcoes });
        },
        disparar(tipo, evento = {}) {
            const completo = { defaultPrevented: false, preventDefault() { this.defaultPrevented = true; }, ...evento };
            (registrados[tipo] ?? []).forEach(({ funcao }) => funcao(completo));

            return completo;
        },
    };
}

/**
 * Faixa de abas falsa. `abas` são as larguras de cada aba; a faixa mostra
 * `largura` px do conteúdo. A aba `ativa` (índice) leva aria-current.
 */
function criarFaixa({ largura = 400, abas = [100, 100, 100, 100, 100, 100], ativa = null, rolagem = 0, comSetas = true } = {}) {
    const ouvintes = criarOuvintes();
    const chamadas = [];
    let larguras = [...abas];

    const faixa = {
        ...ouvintes,
        clientWidth: largura,
        scrollWidth: Math.max(abas.reduce((soma, a) => soma + a, 0), largura),
        scrollLeft: rolagem,
        chamadas,
        children: [],
        getBoundingClientRect: () => ({ left: 50, width: faixa.clientWidth }),
        scrollTo({ left, behavior }) {
            chamadas.push({ tipo: 'scrollTo', left, behavior });
            faixa.scrollLeft = left;
            faixa.disparar('scroll');
        },
        scrollBy({ left, behavior }) {
            chamadas.push({ tipo: 'scrollBy', left, behavior });
            faixa.scrollLeft = Math.min(Math.max(faixa.scrollLeft + left, 0), faixa.scrollWidth - faixa.clientWidth);
            faixa.disparar('scroll');
        },
        querySelector: (seletor) => (seletor === '[aria-current="page"]' ? faixa.children.find((a) => a.ativa) ?? null : null),
        /** As abas mudaram de largura (a fonte da interface terminou de carregar). */
        redimensionarAbas(novas) {
            larguras = [...novas];
            faixa.scrollWidth = Math.max(larguras.reduce((soma, a) => soma + a, 0), faixa.clientWidth);
        },
    };

    faixa.children = abas.map((_, indice) => ({
        ativa: indice === ativa,
        getBoundingClientRect: () => ({
            left: 50 + larguras.slice(0, indice).reduce((soma, a) => soma + a, 0) - faixa.scrollLeft,
            width: larguras[indice],
        }),
    }));

    const criarSeta = () => ({ ...criarOuvintes(), hidden: true });
    const setas = comSetas ? { anterior: criarSeta(), proxima: criarSeta() } : {};

    const nav = {
        isConnected: true,
        querySelector(seletor) {
            if (seletor === '[data-abas-lista]') {
                return faixa;
            }
            if (seletor === '[data-abas-seta="anterior"]') {
                return setas.anterior ?? null;
            }
            if (seletor === '[data-abas-seta="proxima"]') {
                return setas.proxima ?? null;
            }

            return null;
        },
    };

    return { nav, faixa, setas };
}

function janelaFalsa({ reduzir = false } = {}) {
    const observadores = [];
    const ouvintes = criarOuvintes();
    const removidos = [];

    class ResizeObserverFalso {
        constructor(funcao) {
            this.funcao = funcao;
            this.alvos = [];
            observadores.push(this);
        }

        observe(alvo) {
            this.alvos.push(alvo);
        }
    }

    return {
        ...ouvintes,
        observadores,
        removidos,
        ResizeObserver: ResizeObserverFalso,
        matchMedia: (consulta) => ({ matches: reduzir && consulta === '(prefers-reduced-motion: reduce)' }),
        removeEventListener: (tipo, funcao) => removidos.push({ tipo, funcao }),
    };
}

test('estadoDaRolagem: cabe, no início, no meio e no fim', () => {
    const cabe = { scrollWidth: 400, clientWidth: 400, scrollLeft: 0 };
    assert.deepEqual(estadoDaRolagem(cabe), { transborda: false, noInicio: true, noFim: true });

    const inicio = { scrollWidth: 900, clientWidth: 400, scrollLeft: 0 };
    assert.deepEqual(estadoDaRolagem(inicio), { transborda: true, noInicio: true, noFim: false });

    const meio = { scrollWidth: 900, clientWidth: 400, scrollLeft: 200 };
    assert.deepEqual(estadoDaRolagem(meio), { transborda: true, noInicio: false, noFim: false });

    const fim = { scrollWidth: 900, clientWidth: 400, scrollLeft: 500 };
    assert.deepEqual(estadoDaRolagem(fim), { transborda: true, noInicio: false, noFim: true });
});

test('estadoDaRolagem: posição fracionária e meio pixel de diferença não contam', () => {
    assert.equal(estadoDaRolagem({ scrollWidth: 400.5, clientWidth: 400, scrollLeft: 0 }).transborda, false);
    assert.equal(estadoDaRolagem({ scrollWidth: 900, clientWidth: 400, scrollLeft: 499.4 }).noFim, true);
    assert.equal(estadoDaRolagem({ scrollWidth: 900, clientWidth: 400, scrollLeft: 0.6 }).noInicio, true);
});

test('posicaoParaCentralizar: centraliza a aba e respeita as pontas', () => {
    // Faixa de 400px sobre 600px de conteúdo (6 abas de 100px).
    const { faixa } = criarFaixa({ largura: 400, abas: [100, 100, 100, 100, 100, 100] });

    // A 4ª aba (300 a 400) tem o centro em 350: 350 - 200 = 150.
    assert.equal(posicaoParaCentralizar(faixa, faixa.children[3]), 150);
    // A 3ª aba (200 a 300): 250 - 200 = 50.
    assert.equal(posicaoParaCentralizar(faixa, faixa.children[2]), 50);
    // A 1ª aba pediria um valor negativo e a última passaria do máximo (200).
    assert.equal(posicaoParaCentralizar(faixa, faixa.children[0]), 0);
    assert.equal(posicaoParaCentralizar(faixa, faixa.children[5]), 200);
});

test('posicaoParaCentralizar: leva em conta a rolagem atual', () => {
    const { faixa } = criarFaixa({ largura: 400, abas: [100, 100, 100, 100, 100, 100], rolagem: 200 });

    assert.equal(posicaoParaCentralizar(faixa, faixa.children[2]), 50);
});

test('passoDaSeta: 80% da largura visível, com o sinal da direção', () => {
    assert.equal(passoDaSeta({ clientWidth: 500 }, 1), 400);
    assert.equal(passoDaSeta({ clientWidth: 500 }, -1), -400);
});

test('reduzMovimento: lê a preferência do sistema e aceita ambiente sem matchMedia', () => {
    assert.equal(reduzMovimento(janelaFalsa({ reduzir: true })), true);
    assert.equal(reduzMovimento(janelaFalsa({ reduzir: false })), false);
    assert.equal(reduzMovimento({}), false);
});

test('deltaDaRoda: a roda na vertical vira rolagem horizontal quando a faixa transborda', () => {
    const lista = { scrollWidth: 900, clientWidth: 400, scrollLeft: 100 };

    assert.equal(deltaDaRoda({ deltaY: 100, deltaX: 0, deltaMode: 0 }, lista), 100);
    assert.equal(deltaDaRoda({ deltaY: -100, deltaX: 0, deltaMode: 0 }, lista), -100);
    // Linhas (Firefox) e páginas.
    assert.equal(deltaDaRoda({ deltaY: 3, deltaX: 0, deltaMode: 1 }, lista), 48);
    assert.equal(deltaDaRoda({ deltaY: 1, deltaX: 0, deltaMode: 2 }, lista), 400);
});

test('deltaDaRoda: sem rolagem para fazer, deixa a página rolar', () => {
    const cabe = { scrollWidth: 400, clientWidth: 400, scrollLeft: 0 };
    assert.equal(deltaDaRoda({ deltaY: 100, deltaX: 0, deltaMode: 0 }, cabe), null);

    const inicio = { scrollWidth: 900, clientWidth: 400, scrollLeft: 0 };
    assert.equal(deltaDaRoda({ deltaY: -100, deltaX: 0, deltaMode: 0 }, inicio), null);
    assert.equal(deltaDaRoda({ deltaY: 100, deltaX: 0, deltaMode: 0 }, inicio), 100);

    const fim = { scrollWidth: 900, clientWidth: 400, scrollLeft: 500 };
    assert.equal(deltaDaRoda({ deltaY: 100, deltaX: 0, deltaMode: 0 }, fim), null);
    assert.equal(deltaDaRoda({ deltaY: -100, deltaX: 0, deltaMode: 0 }, fim), -100);
});

test('deltaDaRoda: gesto horizontal, zoom e atalhos ficam com o navegador', () => {
    const lista = { scrollWidth: 900, clientWidth: 400, scrollLeft: 100 };

    assert.equal(deltaDaRoda({ deltaY: 10, deltaX: 80, deltaMode: 0 }, lista), null);
    assert.equal(deltaDaRoda({ deltaY: 0, deltaX: 0, deltaMode: 0 }, lista), null);
    assert.equal(deltaDaRoda({ deltaY: 100, deltaX: 0, deltaMode: 0, ctrlKey: true }, lista), null);
    assert.equal(deltaDaRoda({ deltaY: 100, deltaX: 0, deltaMode: 0, shiftKey: true }, lista), null);
    assert.equal(deltaDaRoda({ deltaY: 100, deltaX: 0, deltaMode: 0, altKey: true }, lista), null);
    assert.equal(deltaDaRoda({ deltaY: 100, deltaX: 0, deltaMode: 0, metaKey: true }, lista), null);
});

test('iniciar: leva a aba ativa para o centro sem animação e mostra as setas dos dois lados', () => {
    const { nav, faixa, setas } = criarFaixa({ largura: 400, abas: [100, 100, 100, 100, 100, 100, 100, 100], ativa: 4 });

    iniciar(nav, janelaFalsa());

    // A 5ª aba (400 a 500) no centro de uma faixa de 400px: 450 - 200 = 250.
    assert.deepEqual(faixa.chamadas, [{ tipo: 'scrollTo', left: 250, behavior: 'auto' }]);
    assert.equal(setas.anterior.hidden, false);
    assert.equal(setas.proxima.hidden, false);
});

test('iniciar: aba ativa no começo não rola e só a seta da direita aparece', () => {
    const { nav, faixa, setas } = criarFaixa({ largura: 400, abas: [100, 100, 100, 100, 100, 100], ativa: 0 });

    iniciar(nav, janelaFalsa());

    assert.equal(faixa.scrollLeft, 0);
    assert.equal(setas.anterior.hidden, true);
    assert.equal(setas.proxima.hidden, false);
});

test('iniciar: aba ativa no fim fica na ponta e só a seta da esquerda aparece', () => {
    const { nav, faixa, setas } = criarFaixa({ largura: 400, abas: [100, 100, 100, 100, 100, 100], ativa: 5 });

    iniciar(nav, janelaFalsa());

    assert.equal(faixa.scrollLeft, 200);
    assert.equal(setas.anterior.hidden, false);
    assert.equal(setas.proxima.hidden, true);
});

test('iniciar: quando as abas cabem, nada rola e nenhuma seta aparece', () => {
    const { nav, faixa, setas } = criarFaixa({ largura: 800, abas: [100, 100, 100], ativa: 2 });

    iniciar(nav, janelaFalsa());

    assert.deepEqual(faixa.chamadas, []);
    assert.equal(setas.anterior.hidden, true);
    assert.equal(setas.proxima.hidden, true);
});

test('iniciar: as setas rolam uma página com animação, e sem animação para quem pediu menos movimento', () => {
    const suave = criarFaixa({ largura: 500, abas: new Array(10).fill(100), ativa: 0 });
    iniciar(suave.nav, janelaFalsa());

    suave.setas.proxima.disparar('click');
    assert.deepEqual(suave.faixa.chamadas.at(-1), { tipo: 'scrollBy', left: 400, behavior: 'smooth' });
    assert.equal(suave.faixa.scrollLeft, 400);
    assert.equal(suave.setas.anterior.hidden, false);

    suave.setas.anterior.disparar('click');
    assert.deepEqual(suave.faixa.chamadas.at(-1), { tipo: 'scrollBy', left: -400, behavior: 'smooth' });
    assert.equal(suave.faixa.scrollLeft, 0);
    assert.equal(suave.setas.anterior.hidden, true);

    const reduzido = criarFaixa({ largura: 500, abas: new Array(10).fill(100), ativa: 0 });
    iniciar(reduzido.nav, janelaFalsa({ reduzir: true }));
    reduzido.setas.proxima.disparar('click');
    assert.equal(reduzido.faixa.chamadas.at(-1).behavior, 'auto');
});

test('iniciar: a seta some ao chegar na ponta', () => {
    const { nav, faixa, setas } = criarFaixa({ largura: 500, abas: new Array(7).fill(100), ativa: 0 });
    iniciar(nav, janelaFalsa());

    setas.proxima.disparar('click');

    // Máximo de 200px: a rolagem de 400 para na ponta.
    assert.equal(faixa.scrollLeft, 200);
    assert.equal(setas.proxima.hidden, true);
    assert.equal(setas.anterior.hidden, false);
});

test('iniciar: o clique na seta não tira o foco de onde estava', () => {
    const { nav, setas } = criarFaixa({ abas: new Array(10).fill(100) });
    iniciar(nav, janelaFalsa());

    assert.equal(setas.proxima.disparar('mousedown').defaultPrevented, true);
    assert.equal(setas.anterior.disparar('mousedown').defaultPrevented, true);
});

test('iniciar: a roda na vertical rola a faixa e cancela a rolagem da página', () => {
    const { nav, faixa } = criarFaixa({ largura: 400, abas: new Array(8).fill(100), ativa: 0 });
    iniciar(nav, janelaFalsa());

    const [ouvinte] = faixa.registrados.wheel;
    assert.equal(ouvinte.opcoes.passive, false, 'sem passive: false o preventDefault seria ignorado');

    const evento = faixa.disparar('wheel', { deltaY: 120, deltaX: 0, deltaMode: 0 });
    assert.equal(evento.defaultPrevented, true);
    assert.equal(faixa.scrollLeft, 120);
});

test('iniciar: na ponta a roda segue para a página', () => {
    const { nav, faixa } = criarFaixa({ largura: 400, abas: new Array(8).fill(100), ativa: 7 });
    iniciar(nav, janelaFalsa());

    assert.equal(faixa.scrollLeft, 400);
    const evento = faixa.disparar('wheel', { deltaY: 120, deltaX: 0, deltaMode: 0 });
    assert.equal(evento.defaultPrevented, false);
    assert.equal(faixa.scrollLeft, 400);
});

test('iniciar: abas que cabem deixam a roda para a página', () => {
    const { nav, faixa } = criarFaixa({ largura: 800, abas: [100, 100, 100] });
    iniciar(nav, janelaFalsa());

    const evento = faixa.disparar('wheel', { deltaY: 120, deltaX: 0, deltaMode: 0 });
    assert.equal(evento.defaultPrevented, false);
    assert.equal(faixa.scrollLeft, 0);
});

test('iniciar: acompanha o tamanho da faixa e das abas (ResizeObserver)', () => {
    const janela = janelaFalsa();
    const { nav, faixa, setas } = criarFaixa({ largura: 400, abas: new Array(6).fill(100), ativa: 0 });
    iniciar(nav, janela);

    assert.equal(janela.observadores.length, 1);
    assert.deepEqual(janela.observadores[0].alvos, [faixa, ...faixa.children]);
    assert.equal(setas.proxima.hidden, false);

    // A janela cresceu e as abas passaram a caber.
    faixa.clientWidth = 700;
    faixa.scrollWidth = 700;
    janela.observadores[0].funcao();
    assert.equal(setas.proxima.hidden, true);
    assert.equal(setas.anterior.hidden, true);

    // E encolheu de novo.
    faixa.clientWidth = 300;
    faixa.scrollWidth = 600;
    janela.observadores[0].funcao();
    assert.equal(setas.proxima.hidden, false);
});

test('iniciar: funciona sem ResizeObserver, sem setas e sem aba ativa', () => {
    const { nav, faixa } = criarFaixa({ largura: 400, abas: new Array(6).fill(100), comSetas: false });

    assert.doesNotThrow(() => iniciar(nav, {}));
    assert.deepEqual(faixa.chamadas, []);
    assert.equal(faixa.registrados.wheel.length, 1);
});

test('iniciar: nav sem a faixa é ignorada', () => {
    assert.doesNotThrow(() => iniciar({ querySelector: () => null }, janelaFalsa()));
});

// A causa do #2995: o módulo roda antes de a fonte da interface carregar. Com a
// fonte de reserva as abas são mais estreitas e o limite da rolagem é menor
// (305px em vez de 347px na tela das Configurações); sem refazer a posição, a
// aba ativa ficava 40px aquém do centro possível depois que a fonte chegava.
test('iniciar: quando a fonte chega e as abas crescem, a aba ativa volta ao centro possível', () => {
    const janela = janelaFalsa();
    const { nav, faixa } = criarFaixa({ largura: 400, abas: new Array(8).fill(90), ativa: 6 });
    iniciar(nav, janela);

    // Medida com a fonte de reserva: máximo de 320px.
    assert.deepEqual(faixa.chamadas, [{ tipo: 'scrollTo', left: 320, behavior: 'auto' }]);

    // A fonte carregou: cada aba passou de 90px para 100px (máximo de 400px).
    faixa.redimensionarAbas(new Array(8).fill(100));
    janela.observadores[0].funcao();

    assert.deepEqual(faixa.chamadas.at(-1), { tipo: 'scrollTo', left: 400, behavior: 'auto' });
    assert.equal(faixa.scrollLeft, 400);
});

test('iniciar: sem mudança de medida o ResizeObserver não rola de novo', () => {
    const janela = janelaFalsa();
    const { nav, faixa } = criarFaixa({ largura: 400, abas: new Array(8).fill(100), ativa: 4 });
    iniciar(nav, janela);
    assert.equal(faixa.chamadas.length, 1);

    janela.observadores[0].funcao();
    janela.observadores[0].funcao();

    assert.equal(faixa.chamadas.length, 1);
});

test('iniciar: abas que passam a transbordar depois (fonte maior) também centralizam', () => {
    const janela = janelaFalsa();
    const { nav, faixa, setas } = criarFaixa({ largura: 400, abas: [90, 90, 90, 90], ativa: 3 });
    iniciar(nav, janela);
    assert.deepEqual(faixa.chamadas, []);
    assert.equal(setas.proxima.hidden, true);

    faixa.redimensionarAbas([120, 120, 120, 120, 120]);
    janela.observadores[0].funcao();

    assert.equal(faixa.chamadas.length, 1);
    assert.equal(setas.anterior.hidden, false);
});

// Depois que o usuário mexe na faixa, a posição dele vale: o layout que muda
// (janela, fonte) não pode puxar a faixa de volta.
const ASSUMINDO = {
    'roda do mouse': (faixa) => faixa.disparar('wheel', { deltaY: 50, deltaX: 0, deltaMode: 0 }),
    'gesto horizontal do trackpad': (faixa) => faixa.disparar('wheel', { deltaY: 0, deltaX: 40, deltaMode: 0 }),
    'toque ou clique na faixa': (faixa) => faixa.disparar('pointerdown'),
    'foco numa aba (Tab)': (faixa) => faixa.disparar('focusin'),
    'seta': (faixa, setas) => setas.anterior.disparar('click'),
};

for (const [nome, acao] of Object.entries(ASSUMINDO)) {
    test(`iniciar: depois de ${nome} o layout que muda não recentra`, () => {
        const janela = janelaFalsa();
        const { nav, faixa, setas } = criarFaixa({ largura: 400, abas: new Array(8).fill(90), ativa: 6 });
        iniciar(nav, janela);

        // A roda para baixo na ponta direita seria da página; por isso o
        // usuário puxa a faixa um pouco para trás antes de agir.
        faixa.scrollLeft = 200;
        faixa.disparar('scroll');
        acao(faixa, setas);
        faixa.scrollLeft = 120;
        const antes = faixa.chamadas.length;

        faixa.redimensionarAbas(new Array(8).fill(100));
        janela.observadores[0].funcao();

        assert.equal(faixa.chamadas.length, antes, 'não pode haver scrollTo depois do usuário assumir');
        assert.equal(faixa.scrollLeft, 120);
    });
}

test('iniciar: a roda que não rola nada (faixa na ponta) não conta como o usuário assumir', () => {
    const janela = janelaFalsa();
    const { nav, faixa } = criarFaixa({ largura: 400, abas: new Array(8).fill(90), ativa: 6 });
    iniciar(nav, janela);
    assert.equal(faixa.scrollLeft, 320);

    // Na ponta direita a roda para baixo é da página.
    const evento = faixa.disparar('wheel', { deltaY: 100, deltaX: 0, deltaMode: 0 });
    assert.equal(evento.defaultPrevented, false);

    faixa.redimensionarAbas(new Array(8).fill(100));
    janela.observadores[0].funcao();
    assert.equal(faixa.scrollLeft, 400);
});

test('iniciar: a rolagem que o próprio módulo faz não conta como o usuário assumir', () => {
    const janela = janelaFalsa();
    const { nav, faixa } = criarFaixa({ largura: 400, abas: new Array(8).fill(90), ativa: 6 });
    iniciar(nav, janela);

    // O scrollTo inicial dispara "scroll" (o fake faz como o navegador).
    assert.equal(faixa.registrados.scroll.length, 1);
    faixa.redimensionarAbas(new Array(8).fill(100));
    janela.observadores[0].funcao();

    assert.equal(faixa.scrollLeft, 400);
});

test('iniciar: voltar do bfcache (pageshow persisted) refaz o centro; carregamento comum não mexe', () => {
    const janela = janelaFalsa();
    const { nav, faixa } = criarFaixa({ largura: 400, abas: new Array(8).fill(100), ativa: 6 });
    iniciar(nav, janela);
    assert.equal(faixa.scrollLeft, 400);
    assert.equal(janela.registrados.pageshow.length, 1);

    // O usuário rolou a faixa para o começo e saiu da página.
    faixa.disparar('pointerdown');
    faixa.scrollLeft = 0;
    const antes = faixa.chamadas.length;

    janela.disparar('pageshow', { persisted: false });
    assert.equal(faixa.chamadas.length, antes);
    assert.equal(faixa.scrollLeft, 0);

    // Voltou do bfcache: a página reaparece com a faixa onde ficou.
    janela.disparar('pageshow', { persisted: true });
    assert.equal(faixa.scrollLeft, 400);

    // E volta a acompanhar o layout, como num carregamento novo.
    faixa.redimensionarAbas(new Array(8).fill(110));
    janela.observadores[0].funcao();
    assert.equal(faixa.scrollLeft, 480);
});

test('iniciar: nav que saiu da página solta o ouvinte de pageshow e não rola', () => {
    const janela = janelaFalsa();
    const { nav, faixa } = criarFaixa({ largura: 400, abas: new Array(8).fill(100), ativa: 6 });
    iniciar(nav, janela);
    const antes = faixa.chamadas.length;
    faixa.scrollLeft = 0;

    nav.isConnected = false;
    janela.disparar('pageshow', { persisted: true });

    assert.equal(faixa.chamadas.length, antes);
    assert.equal(faixa.scrollLeft, 0);
    assert.deepEqual(janela.removidos.map((r) => r.tipo), ['pageshow']);
    assert.equal(janela.removidos[0].funcao, janela.registrados.pageshow[0].funcao);
});

test('iniciar: janela sem addEventListener (testes, ambiente antigo) não quebra', () => {
    const { nav } = criarFaixa({ largura: 400, abas: new Array(8).fill(100), ativa: 6 });

    assert.doesNotThrow(() => iniciar(nav, {}));
});
