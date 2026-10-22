// Moldura do painel da v5 (views/tema/*.php): sidebar recolhível no desktop,
// gaveta (drawer) no celular, troca de modo de cor e menu do usuário.
//
// Iniciado pelo data-module="layout/shell" do <body>. O estado inicial da
// sidebar e do modo já vem aplicado pelo assets/js/tema.js no <head>; este
// módulo só reage às ações do usuário.

import { post } from '../../lib/http.js';
import { aplicarModo, salvarModo } from '../../lib/tema.js';

export const CHAVE_SIDEBAR = 'mapos:sidebar';

// Mesmo ponto de quebra do layout.css (lg do Tailwind, 64rem).
export const CONSULTA_DESKTOP = '(min-width: 64rem)';

/**
 * Próximo estado do botão de menu: no desktop recolhe/expande a sidebar, no
 * celular abre/fecha a gaveta.
 */
export function proximoEstado({ desktop, recolhida, gavetaAberta }) {
    if (desktop) {
        return { recolhida: !recolhida, gavetaAberta: false };
    }

    return { recolhida, gavetaAberta: !gavetaAberta };
}

function gravar(chave, valor) {
    try {
        if (valor === null) {
            globalThis.localStorage?.removeItem(chave);
        } else {
            globalThis.localStorage?.setItem(chave, valor);
        }
    } catch {
        // localStorage bloqueado: o estado vale só até a próxima página.
    }
}

// Ctrl+K (ou ⌘K no Mac) abre a busca global (DESIGN.md topbar). Shift e Alt
// ficam de fora para não roubar outros atalhos.
export function ehAtalhoBusca(evento) {
    return Boolean(evento.ctrlKey || evento.metaKey) && !evento.altKey && !evento.shiftKey && String(evento.key).toLowerCase() === 'k';
}

// F1 a F4 levam às telas principais (como na v4), sem Ctrl/Alt/Shift/⌘. Só
// valem as teclas que o servidor mandou: telas que o usuário não pode abrir
// não têm atalho. Dentro de um campo de formulário não agem: navegar
// descartaria o que a pessoa está digitando.
export function urlDoAtalho(evento, atalhos) {
    if (evento.ctrlKey || evento.metaKey || evento.altKey || evento.shiftKey) {
        return null;
    }

    const alvo = evento.target;
    if (alvo?.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(alvo?.tagName)) {
        return null;
    }

    return Object.hasOwn(atalhos, evento.key) ? atalhos[evento.key] : null;
}

function lerAtalhos(corpo) {
    try {
        return JSON.parse(corpo.querySelector('#layout-atalhos')?.textContent ?? '{}') ?? {};
    } catch {
        return {};
    }
}

/**
 * Grava o modo do usuário no servidor (#2854), para valer em qualquer
 * navegador. Se falhar (rede, sessão), o localStorage já guardou a escolha
 * neste navegador, então a falha não é mostrada.
 */
export function gravarNoServidor(url, modo) {
    if (!url) {
        return Promise.resolve(false);
    }

    return post(url, { modo }).then(() => true, () => false);
}

export default function iniciar(corpo) {
    const html = document.documentElement;
    const desktop = window.matchMedia?.(CONSULTA_DESKTOP);
    const escuro = window.matchMedia?.('(prefers-color-scheme: dark)');
    const sidebar = corpo.querySelector('[data-sidebar]');
    const botoesMenu = corpo.querySelectorAll('[data-sidebar-alternar]');

    const estado = () => ({
        desktop: Boolean(desktop?.matches),
        recolhida: html.dataset.sidebar === 'recolhida',
        gavetaAberta: html.dataset.gaveta === 'aberta',
    });

    const marcarBotoes = () => {
        const { desktop: ehDesktop, recolhida, gavetaAberta } = estado();
        for (const botao of botoesMenu) {
            botao.setAttribute('aria-expanded', String(ehDesktop ? !recolhida : gavetaAberta));
        }
    };

    const aplicar = (novo, origem = null) => {
        if (novo.recolhida) {
            html.dataset.sidebar = 'recolhida';
        } else {
            delete html.dataset.sidebar;
        }
        gravar(CHAVE_SIDEBAR, novo.recolhida ? 'recolhida' : null);

        const estavaAberta = html.dataset.gaveta === 'aberta';
        if (novo.gavetaAberta) {
            html.dataset.gaveta = 'aberta';
            sidebar?.querySelector('a, button, summary')?.focus();
        } else {
            delete html.dataset.gaveta;
            if (estavaAberta) {
                (origem ?? botoesMenu[0])?.focus();
            }
        }

        marcarBotoes();
    };

    // Telas legadas que medem a largura em JS (o calendário do Início) só se
    // ajustam no resize da janela: avisa quando a margem do conteúdo muda.
    const main = corpo.querySelector('.v5-main');
    main?.addEventListener('transitionend', (evento) => {
        if (evento.target === main && evento.propertyName === 'margin-left') {
            window.dispatchEvent(new Event('resize'));
        }
    });

    corpo.addEventListener('click', (evento) => {
        const alvo = evento.target;

        const alternar = alvo.closest?.('[data-sidebar-alternar]');
        if (alternar) {
            aplicar(proximoEstado(estado()), alternar);
            return;
        }

        if (alvo.closest?.('[data-gaveta-fechar]')) {
            aplicar({ ...estado(), gavetaAberta: false });
            return;
        }

        // Com a sidebar recolhida, abrir um grupo (Relatórios, Configurações)
        // expande a sidebar para os itens aparecerem com o nome.
        const grupo = alvo.closest?.('[data-sidebar] summary');
        if (grupo && estado().desktop && estado().recolhida) {
            aplicar({ ...estado(), recolhida: false });
        }

        const modo = alvo.closest?.('[data-tema-escolher]');
        if (modo) {
            const escolhido = modo.dataset.temaEscolher;
            aplicarModo(html, escolhido, Boolean(escuro?.matches));
            salvarModo(escolhido);
            gravarNoServidor(corpo.dataset.temaUrl, escolhido);
            marcarModo();
        }

        // Clique fora do menu do usuário fecha o menu.
        for (const menu of corpo.querySelectorAll('details[data-menu-suspenso][open]')) {
            if (!menu.contains(alvo)) {
                menu.open = false;
            }
        }
    });

    const busca = corpo.querySelector('#v5-busca');
    const atalhos = lerAtalhos(corpo);

    const abrirBusca = () => {
        if (busca?.showModal) {
            if (!busca.open) {
                busca.showModal();
            }
            busca.querySelector('[data-busca-campo]')?.focus();
            return;
        }

        // Sem a paleta: foca o campo da topbar (ou o da gaveta, no celular).
        let campo = corpo.querySelector('#v5-pesquisa');
        if (!campo || campo.offsetParent === null) {
            aplicar({ ...estado(), gavetaAberta: true });
            campo = corpo.querySelector('#v5-pesquisa-gaveta');
        }
        campo?.focus();
        campo?.select?.();
    };

    // Campo de busca da topbar e botão de busca do celular abrem a paleta.
    corpo.addEventListener('click', (evento) => {
        if (evento.target.closest?.('[data-busca-abrir], #v5-pesquisa')) {
            evento.preventDefault();
            abrirBusca();
        }
    });

    corpo.addEventListener('keydown', (evento) => {
        if (ehAtalhoBusca(evento)) {
            evento.preventDefault();
            abrirBusca();
            return;
        }

        const destino = urlDoAtalho(evento, atalhos);
        if (destino !== null && !document.querySelector('dialog[open]')) {
            evento.preventDefault();
            window.location.href = destino;
            return;
        }

        if (evento.key !== 'Escape') {
            return;
        }

        const menuAberto = corpo.querySelector('details[data-menu-suspenso][open]');
        if (menuAberto) {
            menuAberto.open = false;
            menuAberto.querySelector('summary')?.focus();
            // Sem isso o atalho Esc da v4 (assets/js/legado/atalhos.js) levaria
            // para a página inicial.
            evento.stopPropagation();
            return;
        }

        if (estado().gavetaAberta) {
            aplicar({ ...estado(), gavetaAberta: false });
            evento.stopPropagation();
        }
    });

    // Passou de celular para desktop com a gaveta aberta: fecha a gaveta.
    desktop?.addEventListener?.('change', () => {
        if (estado().desktop && estado().gavetaAberta) {
            aplicar({ ...estado(), gavetaAberta: false });
        }
        marcarBotoes();
    });

    const marcarModo = () => {
        for (const botao of corpo.querySelectorAll('[data-tema-escolher]')) {
            botao.setAttribute('aria-pressed', String(botao.dataset.temaEscolher === html.dataset.temaModo));
        }
    };

    marcarModo();
    marcarBotoes();
}
