// Configurações (views/mapos/configurar.php, #2846): os botões de marcador
// ({CLIENTE_NOME}...) inserem o texto na mensagem de WhatsApp, na posição do
// cursor.

/** Texto com o marcador inserido entre inicio e fim, e a nova posição do cursor. */
export function inserirMarcador(texto, marcador, inicio = texto.length, fim = inicio) {
    const novo = texto.slice(0, inicio) + marcador + texto.slice(fim);

    return { texto: novo, cursor: inicio + marcador.length };
}

export default function iniciar(raiz, { doc = document } = {}) {
    // A barra de abas rola na horizontal: a aba aberta pode ficar fora da
    // área visível (celular, ou as últimas abas a 1280px).
    raiz.querySelector('nav [aria-current="page"]')?.scrollIntoView?.({ block: 'nearest', inline: 'center' });

    raiz.addEventListener('click', (evento) => {
        const botao = evento.target.closest?.('[data-marcador]');
        const campo = botao ? doc.getElementById(botao.dataset.marcadorAlvo) : null;
        if (!campo) {
            return;
        }

        const { texto, cursor } = inserirMarcador(campo.value, botao.dataset.marcador, campo.selectionStart ?? campo.value.length, campo.selectionEnd ?? campo.value.length);
        campo.value = texto;
        campo.focus();
        campo.setSelectionRange?.(cursor, cursor);
    });
}
