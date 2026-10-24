// Aviso que o usuário pode fechar (component('alert', ['dismissible' => true])).
//
// O botão com data-dispensar remove o elemento e dispara o evento
// "dispensado" (com bubbles), para quem precisar reagir.

export default function iniciar(elemento) {
    elemento.addEventListener('click', (evento) => {
        if (!evento.target.closest?.('[data-dispensar]')) {
            return;
        }

        elemento.dispatchEvent(new CustomEvent('dispensado', { bubbles: true }));
        elemento.remove();
    });
}
