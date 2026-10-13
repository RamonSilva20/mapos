// Telas de relatórios (assets/js/modules/relatorios/filtros.js, #2845): a
// máscara de dinheiro dos filtros de preço.
import { test } from 'node:test';
import assert from 'node:assert/strict';

import { aplicarMascara } from '../../assets/js/modules/relatorios/filtros.js';
import { MASCARAS } from '../../assets/js/lib/mascaras.js';

function campo(mascara, valor) {
    return { dataset: { mascara }, value: valor };
}

test('o dinheiro digitado vira reais com centavos', () => {
    const preco = campo('dinheiro', '150000');

    aplicarMascara(preco, MASCARAS);

    assert.equal(preco.value, '1.500,00');
});

test('campo sem máscara conhecida não muda', () => {
    const outro = campo('inexistente', 'abc');
    const semMascara = { dataset: {}, value: 'xyz' };

    aplicarMascara(outro, MASCARAS);
    aplicarMascara(semMascara, MASCARAS);

    assert.equal(outro.value, 'abc');
    assert.equal(semMascara.value, 'xyz');
});

test('o valor já formatado fica como está', () => {
    const preco = campo('dinheiro', '1.500,00');

    aplicarMascara(preco, MASCARAS);

    assert.equal(preco.value, '1.500,00');
});
