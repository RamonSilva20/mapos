// Ícone Lucide a partir do JavaScript: o mesmo SVG do helper icon()
// (application/helpers/icone_helper.php).
//
//     import { criarIcone } from '../lib/icone.js';
//     botao.prepend(criarIcone('plus', 'size-4'));
//
// O nome precisa estar em assets/src/icones.json: o sprite só tem os ícones
// listados ali. A URL do sprite sai da posição deste arquivo
// (assets/js/lib → assets/vendor/lucide), sem depender do base_url.

const SVG = 'http://www.w3.org/2000/svg';

export const URL_SPRITE = new URL('../../vendor/lucide/sprite.svg', import.meta.url).href;

export function criarIcone(nome, classe = '', doc = document) {
    if (!/^[a-z0-9]+(-[a-z0-9]+)*$/.test(nome)) {
        throw new Error(`Nome de ícone inválido: ${nome}`);
    }

    const svg = doc.createElementNS(SVG, 'svg');
    const atributos = {
        // Em SVG, className é somente leitura: a classe vai por setAttribute.
        class: ['shrink-0', classe].filter(Boolean).join(' '),
        width: '20',
        height: '20',
        fill: 'none',
        stroke: 'currentColor',
        'stroke-width': '2',
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        focusable: 'false',
        'aria-hidden': 'true',
    };
    for (const [nomeAtributo, valor] of Object.entries(atributos)) {
        svg.setAttribute(nomeAtributo, valor);
    }

    const uso = doc.createElementNS(SVG, 'use');
    uso.setAttribute('href', `${URL_SPRITE}#${nome}`);
    svg.appendChild(uso);

    return svg;
}
