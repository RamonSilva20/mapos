// Formulário de venda (views/vendas/formulario.php, #2843): liga os
// autocompletes de cliente e vendedor (lib/autocomplete.js). A validação, o
// carregamento do botão e o envio ficam com o módulo formulario/padrao, no
// <form>.

import { iniciarAutocompletes } from '../../lib/autocomplete.js';

export default function iniciar(elemento) {
    iniciarAutocompletes(elemento);
}
