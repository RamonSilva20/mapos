// Amostras (swatches) do card "Personalizar tema" do painel (#painel).
//
// Trocam o atributo data-tema no <html> e guardam no localStorage
// (mapos:tema-cor), igual ao seletor do menu do usuário (layout/tema-cor); o
// assets/js/tema.js reaplica antes da pintura nas próximas páginas. Por
// navegador — ainda sem persistência no servidor/por usuário.

const CORES = ['claro', 'azul', 'grafite', 'verde', 'violeta'];
const CHAVE = 'mapos:tema-cor';

function salvar(valor) {
    try {
        if (valor === 'padrao') {
            window.localStorage.removeItem(CHAVE);
        } else {
            window.localStorage.setItem(CHAVE, valor);
        }
    } catch {
        // localStorage bloqueado: vale só nesta página.
    }
}

export default function iniciar(raiz) {
    const html = document.documentElement;
    const atual = () => {
        const t = html.getAttribute('data-tema');
        return CORES.includes(t) ? t : 'padrao';
    };

    const marcar = () => {
        for (const botao of raiz.querySelectorAll('[data-tema-cor-escolher]')) {
            botao.setAttribute('aria-pressed', String(botao.dataset.temaCorEscolher === atual()));
        }
    };

    marcar();

    raiz.addEventListener('click', (evento) => {
        const botao = evento.target.closest?.('[data-tema-cor-escolher]');
        if (!botao) {
            return;
        }

        const cor = CORES.includes(botao.dataset.temaCorEscolher) ? botao.dataset.temaCorEscolher : 'padrao';
        if (cor === 'padrao') {
            html.removeAttribute('data-tema');
        } else {
            html.setAttribute('data-tema', cor);
        }
        salvar(cor);
        marcar();
    });
}
