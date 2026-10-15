// Abas que transbordam (component('tabs')).
//
// Quando as abas não cabem na largura, a faixa rola na horizontal. Sem este
// módulo ela continua rolável (toque e Shift + roda) com a borda esmaecida
// (classe abas-rolagem, assets/src/app.css). Com ele:
//
// - ao carregar, a aba ativa (aria-current="page") vem para o centro da faixa;
// - a roda do mouse na vertical rola a faixa, só enquanto ela tem para onde
//   rolar (nas pontas a roda volta a rolar a página);
// - as setas das pontas ([data-abas-seta]) rolam cerca de uma "página" e só
//   aparecem quando há aba escondida daquele lado.
//
// As setas são para o mouse: o teclado chega às abas pelo Tab e o navegador
// rola a aba focada para a vista, por isso elas têm tabindex -1 e aria-hidden.
//
// A view marca a <nav> com data-module="componentes/abas" (o component faz
// isso), a faixa com data-abas-lista e cada seta com data-abas-seta="anterior"
// ou "proxima".

/** Folga, em px, para a posição fracionária da rolagem não esconder a seta errada. */
const TOLERANCIA = 1;

/** Fração da largura visível que uma seta rola. */
const PASSO = 0.8;

/** Altura de uma "linha" da roda (deltaMode 1), em px. */
const LINHA = 16;

/** O usuário pediu menos movimento? Sem matchMedia (testes, navegador antigo), não. */
export function reduzMovimento(janela = globalThis) {
    return janela.matchMedia?.('(prefers-reduced-motion: reduce)').matches === true;
}

/** Onde a faixa está: se transborda e se já chegou a cada ponta. */
export function estadoDaRolagem(lista) {
    const maximo = lista.scrollWidth - lista.clientWidth;

    return {
        transborda: maximo > TOLERANCIA,
        noInicio: lista.scrollLeft <= TOLERANCIA,
        noFim: lista.scrollLeft >= maximo - TOLERANCIA,
    };
}

/** scrollLeft que deixa a aba no centro da faixa, limitado às pontas. */
export function posicaoParaCentralizar(lista, aba) {
    const faixa = lista.getBoundingClientRect();
    const alvo = aba.getBoundingClientRect();
    const centro = lista.scrollLeft + (alvo.left - faixa.left) - (faixa.width - alvo.width) / 2;
    const maximo = lista.scrollWidth - lista.clientWidth;

    return Math.round(Math.min(Math.max(centro, 0), Math.max(maximo, 0)));
}

/** Quanto uma seta rola, com sinal: -1 para a esquerda, 1 para a direita. */
export function passoDaSeta(lista, direcao) {
    return direcao * Math.round(lista.clientWidth * PASSO);
}

/**
 * Quantos px a roda do mouse deve rolar a faixa, ou null para deixar o
 * navegador quieto: gesto horizontal (trackpad, Shift + roda), zoom com Ctrl,
 * faixa que cabe inteira ou já na ponta para onde a roda aponta (aí a roda
 * rola a página, sem prender o usuário na faixa).
 */
export function deltaDaRoda(evento, lista) {
    if (evento.ctrlKey || evento.metaKey || evento.altKey || evento.shiftKey) {
        return null;
    }

    if (Math.abs(evento.deltaY) <= Math.abs(evento.deltaX)) {
        return null;
    }

    const { transborda, noInicio, noFim } = estadoDaRolagem(lista);
    if (!transborda || (evento.deltaY < 0 && noInicio) || (evento.deltaY > 0 && noFim)) {
        return null;
    }

    if (evento.deltaMode === 1) {
        return evento.deltaY * LINHA;
    }

    return evento.deltaMode === 2 ? evento.deltaY * lista.clientWidth : evento.deltaY;
}

export default function iniciar(nav, janela = globalThis) {
    const lista = nav.querySelector('[data-abas-lista]');
    if (!lista) {
        return;
    }

    const setas = {
        anterior: nav.querySelector('[data-abas-seta="anterior"]'),
        proxima: nav.querySelector('[data-abas-seta="proxima"]'),
    };

    function atualizar() {
        const { transborda, noInicio, noFim } = estadoDaRolagem(lista);

        if (setas.anterior) {
            setas.anterior.hidden = !transborda || noInicio;
        }
        if (setas.proxima) {
            setas.proxima.hidden = !transborda || noFim;
        }
    }

    function rolar(direcao) {
        lista.scrollBy({
            left: passoDaSeta(lista, direcao),
            behavior: reduzMovimento(janela) ? 'auto' : 'smooth',
        });
    }

    for (const [lado, direcao] of [['anterior', -1], ['proxima', 1]]) {
        const seta = setas[lado];
        if (!seta) {
            continue;
        }

        seta.addEventListener('click', () => rolar(direcao));
        // O clique não tira o foco de onde estava (e não seleciona texto).
        seta.addEventListener('mousedown', (evento) => evento.preventDefault());
    }

    lista.addEventListener('wheel', (evento) => {
        const delta = deltaDaRoda(evento, lista);
        if (delta === null) {
            return;
        }

        evento.preventDefault();
        lista.scrollLeft += delta;
    }, { passive: false });

    lista.addEventListener('scroll', atualizar, { passive: true });

    // A faixa muda de largura com a janela, e cada aba muda com a fonte que
    // termina de carregar ou com o contador que cresce.
    if (typeof janela.ResizeObserver === 'function') {
        const observador = new janela.ResizeObserver(atualizar);
        observador.observe(lista);
        Array.from(lista.children).forEach((aba) => observador.observe(aba));
    }

    // Posição inicial sem animação: é o estado de partida da tela, não uma ação.
    const ativa = lista.querySelector('[aria-current="page"]');
    if (ativa && estadoDaRolagem(lista).transborda) {
        lista.scrollTo({ left: posicaoParaCentralizar(lista, ativa), behavior: 'auto' });
    }

    atualizar();
}
