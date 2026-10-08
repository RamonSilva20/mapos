// Lógica do formulário de login (assets/js/modules/login/formulario.js).
import { test } from 'node:test';
import assert from 'node:assert/strict';

import { ErroHttp } from '../../assets/js/lib/http.js';
import {
    MENSAGEM_PADRAO,
    MENSAGEM_REDE,
    MENSAGEM_SESSAO,
    interpretarResposta,
    mensagemDeFalha,
    textoSimples,
    validar,
} from '../../assets/js/modules/login/formulario.js';

test('validar: campos vazios e e-mail inválido', () => {
    assert.deepEqual(validar({ email: '', senha: '' }), { email: 'Informe o e-mail.', senha: 'Informe a senha.' });
    assert.deepEqual(validar({ email: '   ', senha: 'x' }), { email: 'Informe o e-mail.' });
    assert.deepEqual(validar({ email: 'admin', senha: 'x' }), { email: 'Informe um e-mail válido.' });
    assert.deepEqual(validar({ email: 'a@b', senha: 'x' }), { email: 'Informe um e-mail válido.' });
});

test('validar: dados válidos não geram erro, e e-mail com espaços nas pontas passa', () => {
    assert.deepEqual(validar({ email: 'admin@admin.com', senha: 'password' }), {});
    assert.deepEqual(validar({ email: '  admin@admin.com ', senha: ' ' }), {});
});

test('validar: usa os textos da view quando informados', () => {
    const textos = { emailVazio: 'E?', emailInvalido: 'E!', senhaVazia: 'S?' };
    assert.deepEqual(validar({ email: '', senha: '' }, textos), { email: 'E?', senha: 'S?' });
    assert.deepEqual(validar({ email: 'x', senha: 'y' }, textos), { email: 'E!' });
});

test('interpretarResposta: sucesso vindo como texto JSON', () => {
    assert.deepEqual(interpretarResposta('{"result":true}'), { ok: true, mensagem: '', token: null });
});

test('interpretarResposta: credenciais erradas com token novo', () => {
    const r = interpretarResposta({ result: false, message: 'Os dados de acesso estão incorretos.', MAPOS_TOKEN: 'abc' });
    assert.deepEqual(r, { ok: false, mensagem: 'Os dados de acesso estão incorretos.', token: 'abc' });
});

test('interpretarResposta: conta expirada mantém a mensagem do servidor', () => {
    const r = interpretarResposta('{"result":false,"message":"A conta do usuário está expirada, por favor entre em contato com o administrador do sistema."}');
    assert.equal(r.ok, false);
    assert.match(r.mensagem, /expirada/);
});

test('interpretarResposta: validation_errors() em HTML vira texto', () => {
    const r = interpretarResposta({ result: false, message: '<p>O campo E-mail deve conter um endereço de e-mail válido.</p>\n' });
    assert.equal(r.mensagem, 'O campo E-mail deve conter um endereço de e-mail válido.');
});

test('interpretarResposta: corpo inesperado cai na mensagem padrão', () => {
    for (const corpo of ['<html>erro</html>', '', null, 42, { result: false }]) {
        assert.equal(interpretarResposta(corpo).mensagem, MENSAGEM_PADRAO, `corpo: ${String(corpo)}`);
        assert.equal(interpretarResposta(corpo).ok, false);
    }
});

test('textoSimples: remove tags e normaliza espaços', () => {
    assert.equal(textoSimples('<p>a</p>\n<p>b</p>'), 'a b');
    assert.equal(textoSimples(undefined), '');
});

test('mensagemDeFalha: 403 é sessão/CSRF vencido, outros status e rede têm mensagens próprias', () => {
    assert.equal(mensagemDeFalha(new ErroHttp(403, 'x')), MENSAGEM_SESSAO);
    assert.equal(mensagemDeFalha(new ErroHttp(500, 'x')), MENSAGEM_PADRAO);
    assert.equal(mensagemDeFalha(new TypeError('Failed to fetch')), MENSAGEM_REDE);
});
