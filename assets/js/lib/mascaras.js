// Máscaras de campos brasileiros (#2851), como funções puras: recebem o que o
// usuário digitou e devolvem o valor formatado. Usadas pelo módulo
// formulario/padrao.js nos campos com data-mascara.
//
//     <input data-mascara="documento">   CPF ou CNPJ (inclusive o alfanumérico)
//     <input data-mascara="telefone">    fixo ou celular, com DDD
//     <input data-mascara="cep">
//
// O servidor continua validando: a máscara só ajuda a digitar.

export function somenteDigitos(valor) {
    return String(valor ?? '').replace(/\D/g, '');
}

function aplicarModelo(caracteres, modelo) {
    let saida = '';
    let i = 0;

    for (const marca of modelo) {
        if (i >= caracteres.length) {
            break;
        }
        saida += marca === '#' ? caracteres[i++] : marca;
    }

    return saida;
}

/**
 * CPF (000.000.000-00) até 11 dígitos; acima disso, CNPJ (00.000.000/0000-00).
 * O CNPJ alfanumérico (a partir de 2026) aceita letras nas 12 primeiras
 * posições, sempre em maiúsculas; os 2 dígitos verificadores são números.
 */
export function formatarDocumento(valor) {
    const limpo = String(valor ?? '').toUpperCase().replace(/[^0-9A-Z]/g, '').slice(0, 14);
    const temLetra = /[A-Z]/.test(limpo);

    if (!temLetra && limpo.length <= 11) {
        return aplicarModelo(limpo, '###.###.###-##');
    }

    return aplicarModelo(limpo, '##.###.###/####-##');
}

/** (00) 0000-0000 para fixo; (00) 00000-0000 com 11 dígitos. */
export function formatarTelefone(valor) {
    const digitos = somenteDigitos(valor).slice(0, 11);

    return aplicarModelo(digitos, digitos.length > 10 ? '(##) #####-####' : '(##) ####-####');
}

export function formatarCep(valor) {
    return aplicarModelo(somenteDigitos(valor).slice(0, 8), '#####-###');
}

export const MASCARAS = {
    documento: formatarDocumento,
    telefone: formatarTelefone,
    cep: formatarCep,
};
