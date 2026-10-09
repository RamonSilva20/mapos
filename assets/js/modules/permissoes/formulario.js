// Grupo de permissão (views/permissoes/formulario.php, #2846).
//
// - Marcar adicionar, editar ou excluir de um módulo marca também "ver" (sem
//   ver, a pessoa não chega à tela onde faria o resto).
// - "Marcar tudo" e "Desmarcar tudo" valem para todas as caixas do grupo.

/** Caixas da mesma linha da matriz que precisam ficar marcadas junto com `caixa`. */
export function caixaDeVer(caixa) {
    if (!caixa.checked || caixa.dataset.permissaoAcao === 'v') {
        return null;
    }

    return caixa.closest('[data-permissoes-modulo]')?.querySelector('[data-permissao-acao="v"]') ?? null;
}

export default function iniciar(raiz) {
    raiz.addEventListener('change', (evento) => {
        const ver = caixaDeVer(evento.target);
        if (ver) {
            ver.checked = true;
        }
    });

    raiz.addEventListener('click', (evento) => {
        const botao = evento.target.closest?.('[data-permissoes-todas]');
        if (!botao) {
            return;
        }

        const marcar = botao.dataset.permissoesTodas === '1';
        raiz.querySelectorAll('input[type=checkbox][name="permissoes[]"]').forEach((caixa) => {
            caixa.checked = marcar;
        });
    });
}
