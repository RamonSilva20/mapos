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
    const conteudo = abas.reduce((soma, a) => soma + a, 0);

    const faixa = {
        ...ouvintes,
        clientWidth: largura,
        scrollWidth: Math.max(conteudo, largura),
        scrollLeft: rolagem,
        chamadas,
        children: [],
        getBoundingClientRect: () => ({ left: 50, width: largura }),
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
    };

    let x = 0;
    faixa.children = abas.map((largura_, indice) => {
        const inicio = x;
        x += largura_;

        return {
            ativa: indice === ativa,
            observada: false,
            getBoundingClientRect: () => ({ left: 50 + inicio - faixa.scrollLeft, width: largura_ }),
        };
    });

    const criarSeta = () => ({ ...criarOuvintes(), hidden: true });
    const setas = comSetas ? { anterior: criarSeta(), proxima: criarSeta() } : {};

    const nav = {
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
        observadores,
        ResizeObserver: ResizeObserverFalso,
        matchMedia: (consulta) => ({ matches: reduzir && consulta === '(prefers-reduced-motion: reduce)' }),
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
