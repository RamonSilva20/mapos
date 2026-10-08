// Testes do módulo da moldura do painel (assets/js/modules/layout/shell.js)
// e do modo de cor (assets/js/lib/tema.js).
import { test } from 'node:test';
import assert from 'node:assert/strict';

import { proximoEstado, CONSULTA_DESKTOP, ehAtalhoBusca } from '../../assets/js/modules/layout/shell.js';
import { aplicarModo, salvarModo, CHAVE_MODO } from '../../assets/js/lib/tema.js';

test('no desktop o botão de menu recolhe e expande a sidebar', () => {
    assert.deepEqual(proximoEstado({ desktop: true, recolhida: false, gavetaAberta: false }), { recolhida: true, gavetaAberta: false });
    assert.deepEqual(proximoEstado({ desktop: true, recolhida: true, gavetaAberta: false }), { recolhida: false, gavetaAberta: false });
});

test('no celular o botão de menu abre e fecha a gaveta sem mexer na sidebar recolhida', () => {
    assert.deepEqual(proximoEstado({ desktop: false, recolhida: true, gavetaAberta: false }), { recolhida: true, gavetaAberta: true });
    assert.deepEqual(proximoEstado({ desktop: false, recolhida: false, gavetaAberta: true }), { recolhida: false, gavetaAberta: false });
});

test('a consulta de desktop é o lg do layout.css', () => {
    assert.equal(CONSULTA_DESKTOP, '(min-width: 64rem)');
});

function htmlFalso() {
    const classes = new Set();
    return {
        dataset: {},
        classList: {
            toggle(nome, ligar) {
                if (ligar) classes.add(nome);
                else classes.delete(nome);
            },
            contains: (nome) => classes.has(nome),
        },
    };
}

test('aplicarModo liga .dark no escuro e no sistema com preferência escura', () => {
    const html = htmlFalso();

    aplicarModo(html, 'escuro', false);
    assert.equal(html.dataset.temaModo, 'escuro');
    assert.ok(html.classList.contains('dark'));

    aplicarModo(html, 'claro', true);
    assert.ok(!html.classList.contains('dark'));

    aplicarModo(html, 'sistema', true);
    assert.ok(html.classList.contains('dark'));

    aplicarModo(html, 'sistema', false);
    assert.ok(!html.classList.contains('dark'));
});

test('salvarModo grava a escolha e recusa modo inválido', () => {
    const gravado = {};
    const armazenamento = { setItem: (k, v) => { gravado[k] = v; } };

    salvarModo('escuro', armazenamento);
    assert.equal(gravado[CHAVE_MODO], 'escuro');

    assert.throws(() => salvarModo('roxo', armazenamento), /inválido/);
});

test('salvarModo não quebra com localStorage bloqueado', () => {
    const bloqueado = { setItem: () => { throw new Error('SecurityError'); } };

    assert.doesNotThrow(() => salvarModo('claro', bloqueado));
});

test('Ctrl+K e ⌘K levam à busca; com Shift, Alt ou outra tecla, não', () => {
    assert.equal(ehAtalhoBusca({ ctrlKey: true, key: 'k' }), true);
    assert.equal(ehAtalhoBusca({ metaKey: true, key: 'K' }), true);
    assert.equal(ehAtalhoBusca({ ctrlKey: true, shiftKey: true, key: 'k' }), false);
    assert.equal(ehAtalhoBusca({ ctrlKey: true, altKey: true, key: 'k' }), false);
    assert.equal(ehAtalhoBusca({ ctrlKey: true, key: 'j' }), false);
    assert.equal(ehAtalhoBusca({ key: 'k' }), false);
});
