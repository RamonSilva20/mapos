// Modo de cor da v5 (claro, escuro ou sistema) a partir do JavaScript.
//
// O <head> já aplica o modo antes da primeira pintura (assets/js/tema.js);
// estas funções servem para trocar o modo com a página aberta.

export const MODOS = ['claro', 'escuro', 'sistema'];
export const CHAVE_MODO = 'mapos:tema-modo';

export function aplicarModo(html, modo, prefereEscuro) {
    html.dataset.temaModo = modo;
    html.classList.toggle('dark', modo === 'escuro' || (modo === 'sistema' && prefereEscuro));
}

/**
 * Grava o modo escolhido neste navegador. Falha em silêncio quando o
 * localStorage não está disponível: a troca vale até a próxima página.
 */
export function salvarModo(modo, armazenamento = globalThis.localStorage) {
    if (!MODOS.includes(modo)) {
        throw new Error(`Modo de cor inválido: ${modo}`);
    }

    try {
        armazenamento?.setItem(CHAVE_MODO, modo);
    } catch {
        // localStorage bloqueado.
    }
}

// Cor do tema (#3032): padrao (laranja), azul, verde, violeta ou grafite. Vale
// para o atributo data-tema do <html>; o padrão não leva atributo.
export const CORES = ['padrao', 'azul', 'verde', 'violeta', 'grafite'];
export const CHAVE_COR = 'mapos:tema-cor';

export function aplicarCor(html, cor) {
    if (!CORES.includes(cor)) {
        throw new Error(`Cor de tema inválida: ${cor}`);
    }

    if (cor === 'padrao') {
        delete html.dataset.tema;
    } else {
        html.dataset.tema = cor;
    }
}

/** Guarda a cor neste navegador como cache (a preferência fica no servidor). */
export function salvarCor(cor, armazenamento = globalThis.localStorage) {
    if (!CORES.includes(cor)) {
        throw new Error(`Cor de tema inválida: ${cor}`);
    }

    try {
        armazenamento?.setItem(CHAVE_COR, cor);
    } catch {
        // localStorage bloqueado.
    }
}
