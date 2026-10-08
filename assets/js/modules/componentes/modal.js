// Modal (application/views/components/modal.php).
//
// O <dialog> nativo faz o trabalho pesado: showModal() deixa o resto da
// página inerte (foco preso no modal), Esc fecha e o foco volta para quem
// abriu. Este módulo só liga os gatilhos:
//
// - data-modal-abrir="<id>" em qualquer lugar da página abre o modal;
// - data-modal-fechar dentro do modal fecha;
// - clique no fundo (fora da caixa) fecha.

export function deveFecharPeloFundo(evento, dialogo) {
    if (evento.target !== dialogo) {
        return false;
    }

    // O clique "no dialog" também acontece na borda interna da caixa; só fecha
    // quando o ponto está fora do retângulo dela.
    const r = dialogo.getBoundingClientRect();

    return evento.clientX < r.left || evento.clientX > r.right || evento.clientY < r.top || evento.clientY > r.bottom;
}

export default function iniciar(dialogo, raiz = document) {
    if (typeof dialogo.showModal !== 'function') {
        return;
    }

    raiz.addEventListener('click', (evento) => {
        const gatilho = evento.target.closest?.('[data-modal-abrir]');

        if (gatilho && gatilho.dataset.modalAbrir === dialogo.id) {
            evento.preventDefault();
            if (!dialogo.open) {
                dialogo.showModal();
            }
        }
    });

    dialogo.addEventListener('click', (evento) => {
        if (evento.target.closest?.('[data-modal-fechar]') || deveFecharPeloFundo(evento, dialogo)) {
            dialogo.close();
        }
    });

    if (dialogo.dataset.modalAberto === 'true' && !dialogo.open) {
        dialogo.showModal();
    }
}
