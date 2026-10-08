// Listagem de serviços (views/servicos/servicos.php).
//
// O botão de excluir de cada linha abre o modal do Bootstrap e informa o id
// por data-servico. Este módulo copia o id para o campo oculto do formulário
// do modal.

export default function iniciar(elemento) {
    const campo = document.getElementById('idServico');

    if (!campo) {
        return;
    }

    elemento.addEventListener('click', (evento) => {
        const link = evento.target.closest('[data-servico]');

        if (link && elemento.contains(link)) {
            campo.value = link.dataset.servico;
        }
    });
}
