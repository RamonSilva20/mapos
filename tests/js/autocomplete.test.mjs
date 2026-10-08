// Autocomplete da v5 (#2842): a lógica pura do combobox.
import test from 'node:test';
import assert from 'node:assert/strict';

import { normalizarItens, proximoIndice, situacao, urlDeBusca } from '../../assets/js/lib/autocomplete.js';

test('itens com valor e detalhe do servidor são usados como vêm', () => {
    assert.deepEqual(normalizarItens([{ id: 7, label: 'Ana | Telefone: 1', valor: 'Ana', detalhe: '123 · (11) 9' }]), [
        { id: '7', valor: 'Ana', detalhe: '123 · (11) 9' },
    ]);
});

test('sem valor/detalhe, o label "a | b | c" vira valor e detalhe', () => {
    assert.deepEqual(normalizarItens([{ id: '3', label: 'Garantia 90 dias' }, { id: 4, label: 'Ana | Telefone: 1 | Celular: 2' }]), [
        { id: '3', valor: 'Garantia 90 dias', detalhe: '' },
        { id: '4', valor: 'Ana', detalhe: 'Telefone: 1 · Celular: 2' },
    ]);
});

test('resposta que não é lista ou item sem id é descartado', () => {
    assert.deepEqual(normalizarItens(''), []);
    assert.deepEqual(normalizarItens(null), []);
    assert.deepEqual(normalizarItens({ id: 1 }), []);
    assert.deepEqual(normalizarItens([{ label: 'sem id' }, { id: '', label: 'vazio' }, null]), []);
});

test('setas dão a volta na lista', () => {
    assert.equal(proximoIndice(-1, 3, 1), 0);
    assert.equal(proximoIndice(-1, 3, -1), 2);
    assert.equal(proximoIndice(0, 3, 1), 1);
    assert.equal(proximoIndice(2, 3, 1), 0);
    assert.equal(proximoIndice(0, 3, -1), 2);
    assert.equal(proximoIndice(0, 0, 1), -1);
});

test('termo vai codificado na query string', () => {
    assert.equal(urlDeBusca('/index.php/os/autoCompleteCliente', 'São & Cia'), '/index.php/os/autoCompleteCliente?term=S%C3%A3o%20%26%20Cia');
    assert.equal(urlDeBusca('/busca?x=1', 'a'), '/busca?x=1&term=a');
});

test('situação do campo: vazio, escolhido e pendente', () => {
    assert.equal(situacao('', '', null), 'vazio');
    assert.equal(situacao('   ', '5', 'Ana'), 'vazio');
    assert.equal(situacao('Ana', '5', 'Ana'), 'escolhido');
    assert.equal(situacao(' Ana ', '5', 'Ana'), 'escolhido');
    assert.equal(situacao('Ana', '', 'Ana'), 'pendente');
    assert.equal(situacao('Anabela', '5', 'Ana'), 'pendente');
});
