// assets/js/tema.js (carregado no <head>): a cor do tema (#3032) vinda do
// localStorage só vale sem preferência gravada no usuário e com allow-list.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const codigo = readFileSync(new URL('../../assets/js/tema.js', import.meta.url), 'utf8');

/** Roda o tema.js com um <html> falso (atributos iniciais) e o localStorage dado. Devolve os atributos finais. */
function executar(atributos, armazenamento) {
    const attrs = { ...atributos };
    const html = {
        classList: { toggle() {}, contains: () => false },
        hasAttribute: (nome) => Object.hasOwn(attrs, nome),
        getAttribute: (nome) => attrs[nome] ?? null,
        setAttribute: (nome, valor) => { attrs[nome] = String(valor); },
        removeAttribute: (nome) => { delete attrs[nome]; },
    };
    const janela = {
        localStorage: { getItem: (chave) => armazenamento[chave] ?? null },
        matchMedia: undefined,
    };
    vm.runInNewContext(codigo, { window: janela, document: { documentElement: html } });

    return attrs;
}

test('sem preferência do usuário o cache do navegador aplica a cor', () => {
    assert.equal(executar({}, { 'mapos:tema-cor': 'violeta' })['data-tema'], 'violeta');
});

test('com preferência gravada no usuário o cache de outro navegador não manda', () => {
    const attrs = executar({ 'data-tema': 'azul', 'data-tema-cor-usuario': '1' }, { 'mapos:tema-cor': 'verde' });

    assert.equal(attrs['data-tema'], 'azul');
});

test('cor fora da allow-list no cache é ignorada', () => {
    assert.equal(executar({}, { 'mapos:tema-cor': '"><script>' })['data-tema'], undefined);
    assert.equal(executar({ 'data-tema': 'azul' }, { 'mapos:tema-cor': 'rosa' })['data-tema'], 'azul', 'Mantém a do servidor.');
});

test('o padrão no cache tira a cor do sistema quando não há preferência', () => {
    assert.equal(executar({ 'data-tema': 'azul' }, { 'mapos:tema-cor': 'padrao' })['data-tema'], undefined);
});

test('a área do cliente (data-tema-fixo) fica na marca', () => {
    assert.equal(executar({ 'data-tema-fixo': '1' }, { 'mapos:tema-cor': 'azul' })['data-tema'], undefined);
});

test('localStorage bloqueado não quebra', () => {
    const attrs = {};
    const html = {
        classList: { toggle() {} },
        hasAttribute: () => false,
        getAttribute: () => null,
        setAttribute: (n, v) => { attrs[n] = v; },
        removeAttribute() {},
    };
    const janela = { localStorage: { getItem: () => { throw new Error('SecurityError'); } }, matchMedia: undefined };

    assert.doesNotThrow(() => vm.runInNewContext(codigo, { window: janela, document: { documentElement: html } }));
    assert.equal(attrs['data-tema'], undefined);
});
