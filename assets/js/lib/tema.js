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
