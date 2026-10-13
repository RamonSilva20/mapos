// Telas de relatórios (views/relatorios/relatorio.php, #2845): liga as
// máscaras de dinheiro (data-mascara) e os autocompletes de cliente e
// responsável (lib/autocomplete.js).
//
// O formulário é um GET que abre o relatório em outra aba, então não usa o
// módulo formulario/padrao (o botão ficaria em "carregando" nesta aba). O
// cliente ou o responsável escolhido vai no campo oculto; digitar sem escolher
// da lista deixa o campo inválido, e o navegador barra o envio com a mensagem
// de data-msg-invalido.

import { iniciarAutocompletes } from '../../lib/autocomplete.js';
import { MASCARAS } from '../../lib/mascaras.js';

export function aplicarMascara(campo, mascaras = MASCARAS) {
    const formatar = mascaras[campo.dataset.mascara];
    if (!formatar) {
        return;
    }

    const formatado = formatar(campo.value);
    if (formatado !== campo.value) {
        campo.value = formatado;
    }
}

export default function iniciar(raiz) {
    for (const campo of raiz.querySelectorAll('[data-mascara]')) {
        campo.addEventListener('input', () => aplicarMascara(campo));
    }

    iniciarAutocompletes(raiz);
}
