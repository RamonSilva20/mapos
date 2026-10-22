// Tela da OS (assets/js/modules/os/tela.js, #2842): troca dos trechos
// devolvidos pelo servidor, erros por campo e cobrança.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import {
    aplicarTrechos,
    errosDaResposta,
    formatarPreco,
    mensagemDeErro,
    separarCobranca,
    textoSemHtml,
    urlDaCobranca,
} from '../../assets/js/modules/os/tela.js';

function raizFalsa(partes) {
    const elementos = partes.map((parte) => ({ parte, innerHTML: '' }));

    return {
        elementos,
        querySelectorAll(seletor) {
            const parte = seletor.match(/^\[data-os-parte="([a-z]+)"\]$/)?.[1];
            return elementos.filter((el) => el.parte === parte);
        },
    };
}

test('aplicarTrechos troca só as partes que estão na página', () => {
    const raiz = raizFalsa(['produtos', 'abas']);
    const iniciados = [];

    const trocadas = aplicarTrechos(raiz, { produtos: '<table></table>', abas: '<nav></nav>', totais: '<div></div>' }, (el) => iniciados.push(el.parte));

    assert.deepEqual(trocadas, ['produtos', 'abas']);
    assert.equal(raiz.elementos[0].innerHTML, '<table></table>');
    assert.equal(raiz.elementos[1].innerHTML, '<nav></nav>');
    assert.deepEqual(iniciados, ['produtos', 'abas']);
});

test('aplicarTrechos ignora nomes de parte inválidos, conteúdo que não é texto e resposta sem html', () => {
    const raiz = raizFalsa(['produtos']);

    assert.deepEqual(aplicarTrechos(raiz, { 'produtos"]': '<p>x</p>', produtos: 42 }), []);
    assert.deepEqual(aplicarTrechos(raiz, undefined), []);
    assert.equal(raiz.elementos[0].innerHTML, '');
});

test('formatarPreco põe o preço do cadastro no formato do campo', () => {
    assert.equal(formatarPreco('49.9'), '49,90');
    assert.equal(formatarPreco(1234), '1234,00');
    assert.equal(formatarPreco(''), '');
    assert.equal(formatarPreco(null), '');
    assert.equal(formatarPreco('abc'), '');
});

test('errosDaResposta só aceita um objeto de erros', () => {
    assert.deepEqual(errosDaResposta({ erros: { preco: 'Informe o preço.' } }), { preco: 'Informe o preço.' });
    assert.deepEqual(errosDaResposta({ erros: ['x'] }), {});
    assert.deepEqual(errosDaResposta('<p>erro</p>'), {});
    assert.deepEqual(errosDaResposta(null), {});
});

test('textoSemHtml limpa as mensagens de validação do CodeIgniter', () => {
    assert.equal(textoSemHtml('<p>O campo Gateway é obrigatório.</p>\n'), 'O campo Gateway é obrigatório.');
    assert.equal(textoSemHtml(undefined), '');
});

test('mensagemDeErro', () => {
    assert.equal(mensagemDeErro({ status: 403, corpo: { message: 'Você não tem permissão para editar O.S.' } }), 'Você não tem permissão para editar O.S.');
    assert.equal(mensagemDeErro({ status: 422, corpo: { erros: { _geral: 'Adicione produtos.' } }, message: 'x' }, { _geral: 'Adicione produtos.' }), 'Adicione produtos.');
    assert.match(mensagemDeErro({ status: 403, corpo: '<html>The action you have requested is not allowed.</html>' }), /expirou/);
    assert.equal(mensagemDeErro({ status: 500, corpo: '' }), 'Não foi possível concluir. Tente de novo.');
});

test('separarCobranca e urlDaCobranca', () => {
    assert.deepEqual(separarCobranca('Asaas|boleto'), { gateway: 'Asaas', forma: 'boleto' });
    assert.equal(separarCobranca('Asaas'), null);
    assert.equal(separarCobranca('a|b|c'), null);
    assert.equal(separarCobranca(''), null);

    assert.equal(urlDaCobranca('http://mapos.test/index.php/cobrancas/adicionar', 12), 'http://mapos.test/index.php/cobrancas/visualizar/12');
});
