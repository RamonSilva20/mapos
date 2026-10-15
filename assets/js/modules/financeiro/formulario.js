// Formulário de lançamento (views/financeiro/formulario.php, #2844).
//
// - Baixa: "Já foi recebido ou pago" mostra a data e a forma de pagamento
//   (e as torna obrigatórias).
// - Parcelas: a partir de 2, aparecem a entrada e a data dela, a baixa some
//   (o lançamento parcelado nasce em aberto) e um resumo mostra o valor de
//   cada parcela.
// - Valor líquido: valor menos desconto, ao digitar.
// - Cliente / fornecedor: autocomplete compartilhado com a OS e a venda
//   (lib/autocomplete.js, modo livre), com os clientes do cadastro e os nomes
//   já usados; escolher um cliente guarda o id (clientes_id), e mudar o texto
//   depois solta o vínculo.
//
// Tudo isto é prévia: o servidor calcula o líquido, as parcelas e o vínculo de
// novo (financeiroLancamentoDoFormulario() e financeiroLinhasDoParcelamento()).

import { iniciarAutocompletes } from '../../lib/autocomplete.js';

/** Texto em reais ("1.234,56" ou "1234.56") em centavos; vazio ou inválido, null. */
export function paraCentavos(texto) {
    let limpo = String(texto ?? '').replace(/[R$\s ]/g, '');
    if (limpo === '') {
        return null;
    }
    if (limpo.includes(',')) {
        limpo = limpo.replace(/\./g, '').replace(',', '.');
    }
    if (!/^\d+(\.\d{1,2})?$/.test(limpo)) {
        return null;
    }

    return Math.round(Number(limpo) * 100);
}

/** Centavos em "R$ 1.234,56". */
export function formatarReais(centavos) {
    const inteiro = Math.trunc(Math.abs(centavos) / 100).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    const resto = String(Math.abs(centavos) % 100).padStart(2, '0');

    return `${centavos < 0 ? '-' : ''}R$ ${inteiro},${resto}`;
}

/**
 * Divide o total em centavos em partes iguais; os centavos que sobram vão um a
 * um para as primeiras partes. Mesma regra de financeiroDividirCentavos().
 */
export function dividirCentavos(total, partes) {
    const quantidade = Math.max(1, partes);
    const base = Math.floor(total / quantidade);
    const resto = total - base * quantidade;

    return Array.from({ length: quantidade }, (_, i) => base + (i < resto ? 1 : 0));
}

/** Valor líquido em centavos, ou null se o valor for inválido ou o desconto não couber. */
export function liquidoEmCentavos(valorTexto, descontoTexto) {
    const valor = paraCentavos(valorTexto);
    const desconto = String(descontoTexto ?? '').trim() === '' ? 0 : paraCentavos(descontoTexto);
    if (valor === null || valor <= 0 || desconto === null || desconto >= valor) {
        return null;
    }

    return valor - desconto;
}

/**
 * Resumo do parcelamento: "3x de R$ 100,00", "3x de R$ 66,67 (as últimas de
 * R$ 66,66)" quando as parcelas não são iguais, e "Entrada de R$ 200,00 + ..."
 * com entrada. Vazio quando não há o que mostrar.
 */
export function resumoDoParcelamento({ liquido, parcelas, entrada = 0 }) {
    if (liquido === null || parcelas < 2 || entrada < 0 || entrada >= liquido) {
        return '';
    }

    const partes = dividirCentavos(liquido - entrada, parcelas);
    const primeira = partes[0];
    const ultima = partes[partes.length - 1];
    const parcela = ultima === primeira
        ? `${parcelas}x de ${formatarReais(primeira)}`
        : `${parcelas}x de ${formatarReais(primeira)} (as últimas de ${formatarReais(ultima)})`;

    return entrada > 0 ? `Entrada de ${formatarReais(entrada)} + ${parcela}` : parcela;
}

function iniciarBaixa(raiz, formulario) {
    const caixa = formulario.elements.namedItem('baixado');
    if (!caixa) {
        return;
    }

    const aplicar = () => {
        raiz.querySelectorAll('[data-financeiro-baixa]').forEach((grupo) => {
            grupo.hidden = !caixa.checked;
        });
        for (const nome of ['data_pagamento', 'forma_pgto']) {
            const campo = formulario.elements.namedItem(nome);
            if (campo) {
                campo.required = caixa.checked;
            }
        }
    };
    caixa.addEventListener('change', aplicar);
    aplicar();
}

function iniciarValores(raiz, formulario) {
    const valor = formulario.elements.namedItem('valor');
    const desconto = formulario.elements.namedItem('desconto');
    const parcelas = formulario.elements.namedItem('parcelas');
    const entrada = formulario.elements.namedItem('entrada');
    const caixa = formulario.elements.namedItem('baixado');
    const liquidoEl = raiz.querySelector('[data-financeiro-liquido]');
    const resumoEl = raiz.querySelector('[data-financeiro-resumo]');

    const atualizar = () => {
        const liquido = liquidoEmCentavos(valor?.value, desconto?.value);
        if (liquidoEl) {
            liquidoEl.textContent = liquido === null ? '—' : formatarReais(liquido);
        }

        if (!parcelas) {
            return;
        }

        const quantidade = Number(parcelas.value) || 1;
        const parcelado = quantidade > 1;
        raiz.querySelectorAll('[data-financeiro-entrada]').forEach((grupo) => {
            grupo.hidden = !parcelado;
        });
        raiz.querySelectorAll('[data-financeiro-baixa-opcao]').forEach((grupo) => {
            grupo.hidden = parcelado;
        });
        if (parcelado && caixa?.checked) {
            caixa.checked = false;
            caixa.dispatchEvent(new Event('change'));
        }
        if (resumoEl) {
            resumoEl.textContent = resumoDoParcelamento({ liquido, parcelas: quantidade, entrada: paraCentavos(entrada?.value) ?? 0 });
        }
    };

    for (const campo of [valor, desconto, parcelas, entrada]) {
        campo?.addEventListener('input', atualizar);
        campo?.addEventListener('change', atualizar);
    }
    atualizar();
}

export default function iniciar(raiz) {
    const formulario = raiz.querySelector('form');
    if (!formulario) {
        return;
    }

    iniciarBaixa(raiz, formulario);
    iniciarValores(raiz, formulario);
    iniciarAutocompletes(raiz);
}
