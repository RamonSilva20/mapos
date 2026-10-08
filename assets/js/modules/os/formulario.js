// Formulário de OS (views/os/formulario.php, #2842): liga os autocompletes de
// cliente, técnico e termo de garantia (lib/autocomplete.js). A validação, o
// carregamento do botão e o envio ficam com o módulo formulario/padrao, no
// <form>.

import { iniciarAutocompletes } from '../../lib/autocomplete.js';

export default function iniciar(elemento) {
    iniciarAutocompletes(elemento);
}
