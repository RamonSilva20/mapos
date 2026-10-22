// Toast (application/views/components/toast.php e assets/js/lib/toast.js).
//
// Some depois de data-duracao milissegundos (0 = só fecha pelo botão). A
// contagem pausa enquanto o mouse ou o foco estão sobre o toast, para dar
// tempo de ler (WCAG 2.2.1).

export function criarTemporizador(duracao, aoTerminar, relogio = globalThis) {
    let restante = duracao;
    let inicio = 0;
    let id = null;

    return {
        iniciar() {
            if (restante <= 0 || id !== null) {
                return;
            }
            inicio = Date.now();
            id = relogio.setTimeout(() => {
                id = null;
                aoTerminar();
            }, restante);
        },
        pausar() {
            if (id === null) {
                return;
            }
            relogio.clearTimeout(id);
            id = null;
            restante -= Date.now() - inicio;
        },
        get ativo() {
            return id !== null;
        },
    };
}

export default function iniciar(toast) {
    const fechar = () => toast.remove();
    const duracao = Number.parseInt(toast.dataset.duracao ?? '0', 10) || 0;
    const temporizador = criarTemporizador(duracao, fechar);

    toast.addEventListener('click', (evento) => {
        if (evento.target.closest?.('[data-dispensar]')) {
            fechar();
        }
    });

    toast.addEventListener('mouseenter', () => temporizador.pausar());
    toast.addEventListener('mouseleave', () => temporizador.iniciar());
    toast.addEventListener('focusin', () => temporizador.pausar());
    toast.addEventListener('focusout', () => temporizador.iniciar());

    temporizador.iniciar();
}
