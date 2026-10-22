// Testes dos utilitários de assets/js. Rodam com o runner nativo do Node:
//   npm run test:js
import { test } from 'node:test';
import assert from 'node:assert/strict';

import { nomeDeModuloValido } from '../../assets/js/app.js';
import { converter, lerJson } from '../../assets/js/lib/dados.js';
import { ErroHttp, corpoComCsrf, lerCookie, requisicao, tokenCsrf } from '../../assets/js/lib/http.js';

test('nome de módulo: aceita caminho simples e recusa subida de pasta', () => {
    assert.equal(nomeDeModuloValido('servicos/listagem'), true);
    assert.equal(nomeDeModuloValido('os/editar-os'), true);
    for (const nome of ['', '../x', 'a/../b', '/a', 'a/', 'a.js', 'A', 'https://x', null, undefined]) {
        assert.equal(nomeDeModuloValido(nome), false, `deveria recusar ${nome}`);
    }
});

test('converter: tipos de data-*', () => {
    assert.equal(converter('true'), true);
    assert.equal(converter('false'), false);
    assert.equal(converter('10'), 10);
    assert.equal(converter('1.5'), 1.5);
    assert.equal(converter(''), '');
    assert.equal(converter('  '), '  ');
    assert.deepEqual(converter('{"a":1}'), { a: 1 });
    assert.deepEqual(converter('[1,2]'), [1, 2]);
    assert.equal(converter('{quebrado'), '{quebrado');
    assert.equal(converter('texto'), 'texto');
});

test('lerJson: lê o bloco de page_data() e usa o padrão quando falta', () => {
    const blocos = { dados: { textContent: '{"porPagina":10,"nome":"\\u003C/script\\u003E"}' } };
    const raiz = { getElementById: (id) => blocos[id] ?? null };

    assert.deepEqual(lerJson('dados', null, raiz), { porPagina: 10, nome: '</script>' });
    assert.deepEqual(lerJson('ausente', { x: 1 }, raiz), { x: 1 });
});

test('lerCookie: acha o cookie pelo nome exato', () => {
    const cookies = 'MAPOS_CSRF_COOKIE_X=errado; MAPOS_CSRF_COOKIE=abc%3D%3D; outro=1';

    assert.equal(lerCookie('MAPOS_CSRF_COOKIE', cookies), 'abc==');
    assert.equal(lerCookie('nao_existe', cookies), null);
});

function documentoFalso(cookie = 'MAPOS_CSRF_COOKIE=tok123') {
    const metas = { 'csrf-token-name': 'MAPOS_CSRF_TOKEN', 'csrf-cookie-name': 'MAPOS_CSRF_COOKIE' };
    const campos = [{ value: 'velho' }];

    return {
        cookie,
        campos,
        querySelector: (seletor) => {
            const nome = /meta\[name="([^"]+)"\]/.exec(seletor)?.[1];
            return nome in metas ? { content: metas[nome] } : null;
        },
        querySelectorAll: () => campos,
    };
}

test('tokenCsrf: lê nome do campo e valor pelas metas do layout', () => {
    assert.deepEqual(tokenCsrf(documentoFalso()), { campo: 'MAPOS_CSRF_TOKEN', valor: 'tok123' });
    assert.equal(tokenCsrf(documentoFalso('')), null);
});

test('corpoComCsrf: acrescenta o token a objeto, URLSearchParams e FormData', () => {
    const csrf = { campo: 'T', valor: 'v' };

    assert.equal(corpoComCsrf({ id: 5 }, csrf).toString(), 'id=5&T=v');
    assert.equal(corpoComCsrf(new URLSearchParams('a=1'), csrf).toString(), 'a=1&T=v');

    const form = new FormData();
    form.set('a', '1');
    assert.equal(corpoComCsrf(form, csrf).get('T'), 'v');
    assert.equal(corpoComCsrf({ id: 5 }, null).toString(), 'id=5');
});

async function comFetch(resposta, executar) {
    const original = { fetch: globalThis.fetch, document: globalThis.document };
    const chamadas = [];
    globalThis.document = documentoFalso();
    globalThis.fetch = async (url, opcoes) => {
        chamadas.push({ url, opcoes });
        return resposta;
    };

    try {
        return await executar(chamadas);
    } finally {
        globalThis.fetch = original.fetch;
        globalThis.document = original.document;
    }
}

test('requisicao: POST manda X-Requested-With e o token do CSRF', async () => {
    const resposta = new Response('{"ok":true}', { status: 200, headers: { 'Content-Type': 'application/json' } });

    await comFetch(resposta, async (chamadas) => {
        const corpo = await requisicao('/os/salvar', { metodo: 'POST', dados: { id: 1 } });

        assert.deepEqual(corpo, { ok: true });
        assert.equal(chamadas[0].opcoes.headers['X-Requested-With'], 'XMLHttpRequest');
        assert.equal(chamadas[0].opcoes.body.get('MAPOS_CSRF_TOKEN'), 'tok123');
        assert.equal(globalThis.document.campos[0].value, 'tok123', 'os campos ocultos recebem o token novo');
    });
});

test('requisicao: GET não leva corpo', async () => {
    await comFetch(new Response('[]', { headers: { 'Content-Type': 'application/json' } }), async (chamadas) => {
        await requisicao('/os/autoCompleteCliente?term=a');
        assert.equal(chamadas[0].opcoes.body, undefined);
    });
});

test('requisicao: 403 do MY_Controller vira ErroHttp com a mensagem do servidor', async () => {
    const resposta = new Response(
        JSON.stringify({ result: false, message: 'Você não tem permissão para acessar esta página.' }),
        { status: 403, headers: { 'Content-Type': 'application/json; charset=utf-8' } }
    );

    await comFetch(resposta, async () => {
        await assert.rejects(requisicao('/usuarios'), (erro) => {
            assert.ok(erro instanceof ErroHttp);
            assert.equal(erro.status, 403);
            assert.equal(erro.message, 'Você não tem permissão para acessar esta página.');
            return true;
        });
    });
});

test('requisicao: erro sem JSON usa mensagem genérica', async () => {
    await comFetch(new Response('<html>erro</html>', { status: 500 }), async () => {
        await assert.rejects(requisicao('/x'), { name: 'ErroHttp', status: 500, message: 'Erro 500' });
    });
});
