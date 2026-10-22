// Testes da paleta de busca global (assets/js/modules/layout/busca.js) e dos
// atalhos F1–F4 do shell (#2850).
import { test } from 'node:test';
import assert from 'node:assert/strict';

import { proximoIndice, termoValido, MINIMO_LETRAS } from '../../assets/js/modules/layout/busca.js';
import { urlDoAtalho, ehAtalhoBusca } from '../../assets/js/modules/layout/shell.js';

test('as setas percorrem os resultados e dão a volta', () => {
    assert.equal(proximoIndice(-1, 3, 'ArrowDown'), 0);
    assert.equal(proximoIndice(0, 3, 'ArrowDown'), 1);
    assert.equal(proximoIndice(2, 3, 'ArrowDown'), 0);
    assert.equal(proximoIndice(-1, 3, 'ArrowUp'), 2);
    assert.equal(proximoIndice(0, 3, 'ArrowUp'), 2);
    assert.equal(proximoIndice(2, 3, 'ArrowUp'), 1);
});

test('sem resultados nenhum item fica ativo', () => {
    assert.equal(proximoIndice(0, 0, 'ArrowDown'), -1);
    assert.equal(proximoIndice(-1, 0, 'ArrowUp'), -1);
});

test('outras teclas mantêm o item ativo', () => {
    assert.equal(proximoIndice(1, 3, 'a'), 1);
});

test('a busca só começa com o mínimo de letras, sem contar espaços', () => {
    assert.equal(MINIMO_LETRAS, 2);
    assert.equal(termoValido(''), false);
    assert.equal(termoValido(' a '), false);
    assert.equal(termoValido('an'), true);
    assert.equal(termoValido('  ana  '), true);
});

test('F1 a F4 só valem para as telas que o servidor liberou', () => {
    const atalhos = { F1: '/clientes', F4: '/os' };

    assert.equal(urlDoAtalho({ key: 'F1' }, atalhos), '/clientes');
    assert.equal(urlDoAtalho({ key: 'F4' }, atalhos), '/os');
    assert.equal(urlDoAtalho({ key: 'F2' }, atalhos), null, 'Sem permissão para produtos');
    assert.equal(urlDoAtalho({ key: 'a' }, atalhos), null);
    assert.equal(urlDoAtalho({ key: 'toString' }, atalhos), null, 'Não herda propriedades de Object');
});

test('os atalhos não roubam combinações com Ctrl, Alt, Shift ou ⌘', () => {
    const atalhos = { F1: '/clientes' };

    for (const modificador of ['ctrlKey', 'altKey', 'shiftKey', 'metaKey']) {
        assert.equal(urlDoAtalho({ key: 'F1', [modificador]: true }, atalhos), null, modificador);
    }
});

test('Ctrl K continua sendo o atalho da busca', () => {
    assert.equal(ehAtalhoBusca({ key: 'k', ctrlKey: true }), true);
    assert.equal(ehAtalhoBusca({ key: 'K', metaKey: true }), true);
    assert.equal(ehAtalhoBusca({ key: 'k' }), false);
});

test('dentro de um campo de formulário os atalhos não navegam, para não perder o que foi digitado', () => {
    const atalhos = { F1: '/clientes' };

    for (const tag of ['INPUT', 'TEXTAREA', 'SELECT']) {
        assert.equal(urlDoAtalho({ key: 'F1', target: { tagName: tag } }, atalhos), null, tag);
    }
    assert.equal(urlDoAtalho({ key: 'F1', target: { tagName: 'DIV', isContentEditable: true } }, atalhos), null);
    assert.equal(urlDoAtalho({ key: 'F1', target: { tagName: 'BODY' } }, atalhos), '/clientes');
});
