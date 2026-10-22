// Formulário de produto (views/produtos/formulario.php, #2841): calcula o
// preço de venda pelo preço de compra e pelo lucro, como na v4.
//
// - markup: o lucro é sobre a compra → venda = compra × (1 + lucro%)
// - margem: o lucro é sobre a venda → venda = compra ÷ (1 − lucro%)
//
// O lucro não vai para o banco; só preenche o preço de venda, que o usuário
// pode ajustar à mão depois.

import { formatarDinheiro, somenteDigitos } from '../../lib/mascaras.js';

/** "1.234,56" (máscara) → 1234.56 */
export function reaisParaNumero(texto) {
    const digitos = somenteDigitos(texto);

    return digitos === '' ? null : Number(digitos) / 100;
}

/** Preço de venda, ou null quando não dá para calcular. */
export function precoDeVenda(compra, lucro, tipo) {
    if (compra === null || !Number.isFinite(lucro) || lucro < 0) {
        return null;
    }

    if (tipo === 'margem') {
        return lucro >= 100 ? null : compra / (1 - lucro / 100);
    }

    return compra * (1 + lucro / 100);
}

/** 1234.5 → "1.234,50", no formato da máscara de dinheiro. */
export function numeroParaReais(valor) {
    return formatarDinheiro(String(Math.round(valor * 100)));
}

// O <form> leva o módulo formulario/padrao; este fica num wrapper em volta.
export default function iniciar(raiz) {
    const formulario = raiz.matches?.('form') ? raiz : raiz.querySelector('form');
    const compra = formulario?.elements.namedItem('precoCompra');
    const venda = formulario?.elements.namedItem('precoVenda');
    const lucro = formulario?.elements.namedItem('lucro');
    const tipo = formulario?.elements.namedItem('lucro_tipo');
    if (!compra || !venda || !lucro || !tipo) {
        return;
    }

    const calcular = () => {
        if (lucro.value === '') {
            return;
        }

        const resultado = precoDeVenda(reaisParaNumero(compra.value), Number(lucro.value), tipo.value);
        if (resultado !== null) {
            venda.value = numeroParaReais(resultado);
            venda.dispatchEvent(new Event('input', { bubbles: true }));
        }
    };

    for (const campo of [compra, lucro, tipo]) {
        campo.addEventListener(campo === tipo ? 'change' : 'input', calcular);
    }
}
