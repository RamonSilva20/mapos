// Grupo de permissão (assets/js/modules/permissoes/formulario.js, #2846).
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { caixaDeVer } from '../../assets/js/modules/permissoes/formulario.js';

function linha() {
    const ver = { checked: false, dataset: { permissaoAcao: 'v' } };
    const linhaFalsa = { querySelector: (seletor) => (seletor === '[data-permissao-acao="v"]' ? ver : null) };
    const caixa = (acao, marcada) => ({ checked: marcada, dataset: { permissaoAcao: acao }, closest: () => linhaFalsa });

    return { ver, caixa };
}

test('marcar adicionar, editar ou excluir devolve a caixa de ver da mesma linha', () => {
    const { ver, caixa } = linha();
    assert.equal(caixaDeVer(caixa('a', true)), ver);
    assert.equal(caixaDeVer(caixa('d', true)), ver);
});

test('desmarcar ou mexer no próprio ver não força nada', () => {
    const { caixa } = linha();
    assert.equal(caixaDeVer(caixa('e', false)), null);
    assert.equal(caixaDeVer(caixa('v', true)), null);
});

test('caixa fora da matriz (relatórios, sistema) não tem ver', () => {
    assert.equal(caixaDeVer({ checked: true, dataset: {}, closest: () => null }), null);
});
