// Configurações (assets/js/modules/configuracoes/configurar.js, #2846).
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { inserirMarcador } from '../../assets/js/modules/configuracoes/configurar.js';

test('insere o marcador na posição do cursor', () => {
    assert.deepEqual(inserirMarcador('Olá !', '{CLIENTE_NOME}', 4), { texto: 'Olá {CLIENTE_NOME}!', cursor: 18 });
});

test('substitui o texto selecionado', () => {
    assert.deepEqual(inserirMarcador('OS XX pronta', '{NUMERO_OS}', 3, 5), { texto: 'OS {NUMERO_OS} pronta', cursor: 14 });
});

test('sem cursor, vai para o fim', () => {
    assert.equal(inserirMarcador('Total: ', '{VALOR_OS}').texto, 'Total: {VALOR_OS}');
});
