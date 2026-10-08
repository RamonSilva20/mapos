// Modal (application/views/components/modal.php).
//
// O <dialog> nativo faz o trabalho pesado: showModal() deixa o resto da
// página inerte (foco preso no modal), Esc fecha e o foco volta para quem
// abriu. Este módulo só liga os gatilhos:
//
// - data-modal-abrir="<id>" em qualquer lugar da página abre o modal;
// - data-modal-fechar dentro do modal fecha;
// - clique no fundo (fora da caixa) fecha.
//
// Um mesmo modal pode servir a várias linhas de uma listagem (ex. excluir):
// o gatilho leva os valores em data-valor-<campo> e, ao abrir, cada elemento
// com data-modal-valor="<campo>" recebe o valor: campos de formulário em
// value, o resto em textContent (nunca HTML). Os alvos ficam dentro do modal
// ou fora dele com data-modal-de="<id do modal>" (ex. o input hidden do
// formulário apontado pelo botão de confirmação).

export function deveFecharPeloFundo(evento, dialogo) {
    if (evento.target !== dialogo) {
        return false;
    }

    // O clique "no dialog" também acontece na borda interna da caixa; só fecha
    // quando o ponto está fora do retângulo dela.
    const r = dialogo.getBoundingClientRect();

    return evento.clientX < r.left || evento.clientX > r.right || evento.clientY < r.top || evento.clientY > r.bottom;
}

export function preencher(alvos, gatilho) {
    for (const alvo of alvos) {
        const valor = gatilho.getAttribute(`data-valor-${alvo.dataset.modalValor}`);
        if (valor === null) {
            continue;
        }

        if (['INPUT', 'SELECT', 'TEXTAREA'].includes(alvo.tagName)) {
            alvo.value = valor;
        } else {
            alvo.textContent = valor;
        }
    }
}

export default function iniciar(dialogo, raiz = document) {
    if (typeof dialogo.showModal !== 'function') {
        return;
    }

    raiz.addEventListener('click', (evento) => {
        const gatilho = evento.target.closest?.('[data-modal-abrir]');

        if (gatilho && gatilho.dataset.modalAbrir === dialogo.id) {
            evento.preventDefault();
            preencher([
                ...dialogo.querySelectorAll('[data-modal-valor]'),
                ...[...raiz.querySelectorAll('[data-modal-de][data-modal-valor]')].filter((alvo) => alvo.dataset.modalDe === dialogo.id),
            ], gatilho);
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
