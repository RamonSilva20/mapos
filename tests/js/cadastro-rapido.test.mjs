// Cadastro rápido de cliente em modal (#3003): a lógica pura do módulo.
import test from 'node:test';
import assert from 'node:assert/strict';

import { campoDoTexto, errosDaResposta, mensagemDeErro } from '../../assets/js/modules/clientes/cadastro-rapido.js';
import { ErroHttp } from '../../assets/js/lib/http.js';

test('texto só com números vai para o documento; o resto, para o nome', () => {
    assert.equal(campoDoTexto('Maria da Silva'), 'nomeCliente');
    assert.equal(campoDoTexto('Loja 2'), 'nomeCliente');
    assert.equal(campoDoTexto('123.456.789-09'), 'documento');
    assert.equal(campoDoTexto('11222333000181'), 'documento');
    assert.equal(campoDoTexto('12'), 'nomeCliente');
    assert.equal(campoDoTexto('   '), null);
    assert.equal(campoDoTexto(undefined), null);
});

test('erros por campo só vêm de um objeto', () => {
    assert.deepEqual(errosDaResposta({ erros: { email: 'Em uso.' } }), { email: 'Em uso.' });
    assert.deepEqual(errosDaResposta({ erros: ['x'] }), {});
    assert.deepEqual(errosDaResposta('<html>'), {});
    assert.deepEqual(errosDaResposta(null), {});
});

test('mensagem do toast: JSON usa a do servidor, HTML de 403 avisa que expirou', () => {
    assert.equal(mensagemDeErro(new ErroHttp(403, 'Erro 403', '<html>')), 'A página ficou muito tempo aberta e expirou. Recarregue e tente de novo.');
    assert.equal(mensagemDeErro(new ErroHttp(403, 'x', { result: false, message: 'Você não tem permissão para adicionar clientes.' })), 'Você não tem permissão para adicionar clientes.');
    assert.equal(mensagemDeErro(new ErroHttp(422, 'x', { message: '<p>Falhou</p>' }), {}), 'Falhou');
    assert.equal(mensagemDeErro(new ErroHttp(422, 'x', {}), { _geral: 'Não foi possível salvar.' }), 'Não foi possível salvar.');
    assert.equal(mensagemDeErro(new ErroHttp(500, 'x', null)), 'Não foi possível cadastrar agora. Tente de novo.');
});
