// Área do cliente (assets/js/modules/conecte/copiar.js, #2848): copiar o PIX
// copia e cola, com o código selecionado quando a área de transferência falha.
import { test } from 'node:test';
import assert from 'node:assert/strict';

import { copiar } from '../../assets/js/modules/conecte/copiar.js';

function campoFalso(valor) {
    return { value: valor, selecionado: false, select() { this.selecionado = true; } };
}

test('copia o valor do campo para a área de transferência', async () => {
    const copiados = [];
    const campo = campoFalso('000201PIX');

    assert.equal(await copiar(campo, { writeText: async (texto) => copiados.push(texto) }), true);
    assert.deepEqual(copiados, ['000201PIX']);
    assert.equal(campo.selecionado, false);
});

test('sem permissão, seleciona o código para o Ctrl+C', async () => {
    const campo = campoFalso('000201PIX');

    assert.equal(await copiar(campo, { writeText: async () => { throw new Error('negado'); } }), false);
    assert.equal(campo.selecionado, true);
});

test('sem a API de área de transferência, também seleciona', async () => {
    const campo = campoFalso('x');

    assert.equal(await copiar(campo, undefined), false);
    assert.equal(campo.selecionado, true);
});
