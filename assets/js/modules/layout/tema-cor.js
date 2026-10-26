// Seletor de tema de cor (prova de conceito, #tema), no menu do usuário.
//
// Troca o atributo data-tema no <html> na hora e guarda a escolha no
// localStorage (mapos:tema-cor); o assets/js/tema.js reaplica antes da pintura
// nas próximas páginas, sem flash. Por enquanto é por navegador — ainda sem
// persistência no servidor/por usuário.

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
        // localStorage bloqueado (modo privado, política do navegador): a troca
        // vale só nesta página.
    }
}

export default function iniciar(select) {
    const html = document.documentElement;
    const atual = html.getAttribute('data-tema');
    select.value = CORES.includes(atual) ? atual : 'padrao';

    select.addEventListener('change', () => {
        const cor = CORES.includes(select.value) ? select.value : 'padrao';
        if (cor === 'padrao') {
            html.removeAttribute('data-tema');
        } else {
            html.setAttribute('data-tema', cor);
        }
        salvar(cor);
    });
}
