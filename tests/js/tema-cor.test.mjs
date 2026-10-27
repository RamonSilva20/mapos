// Cor do tema (#3032): assets/js/lib/tema.js e a troca de cor no módulo
// layout/shell.js (menu do usuário da topbar).
import { test } from 'node:test';
import assert from 'node:assert/strict';

import { aplicarCor, salvarCor, CORES, CHAVE_COR } from '../../assets/js/lib/tema.js';
import iniciar, { gravarCorNoServidor } from '../../assets/js/modules/layout/shell.js';

function htmlFalso() {
    return { dataset: {}, classList: { toggle() {}, contains: () => false } };
}

const respostaJson = { ok: true, headers: { get: () => 'application/json' }, json: async () => ({ result: true }) };

test('aplicarCor troca o data-tema do <html> e o padrão (laranja) remove o atributo', () => {
    const html = htmlFalso();

    assert.deepEqual(CORES, ['padrao', 'azul', 'verde', 'violeta', 'grafite']);

    aplicarCor(html, 'azul');
    assert.equal(html.dataset.tema, 'azul');

    aplicarCor(html, 'grafite');
    assert.equal(html.dataset.tema, 'grafite');

    aplicarCor(html, 'padrao');
    assert.equal(html.dataset.tema, undefined);
});

test('aplicarCor e salvarCor recusam cor fora da allow-list', () => {
    const html = htmlFalso();

    assert.throws(() => aplicarCor(html, 'rosa'), /inválida/);
    assert.throws(() => aplicarCor(html, '"><script>'), /inválida/);
    assert.equal(html.dataset.tema, undefined);
    assert.throws(() => salvarCor('rosa', { setItem() {} }), /inválida/);
});

test('salvarCor guarda o cache no navegador e não quebra com localStorage bloqueado', () => {
    const gravado = {};

    salvarCor('verde', { setItem: (k, v) => { gravado[k] = v; } });
    assert.equal(gravado[CHAVE_COR], 'verde');
    assert.equal(CHAVE_COR, 'mapos:tema-cor');

    assert.doesNotThrow(() => salvarCor('azul', { setItem: () => { throw new Error('SecurityError'); } }));
});

test('a troca de cor é gravada no servidor; sem URL ou com falha não quebra', async () => {
    assert.equal(await gravarCorNoServidor('', 'azul'), false);
    assert.equal(await gravarCorNoServidor(undefined, 'azul'), false);

    const original = { fetch: globalThis.fetch, document: globalThis.document };
    globalThis.document = { querySelector: () => null, querySelectorAll: () => [], cookie: '' };
    const chamadas = [];
    globalThis.fetch = async (url, opcoes) => {
        chamadas.push({ url, corpo: String(opcoes.body) });

        return respostaJson;
    };
    try {
        assert.equal(await gravarCorNoServidor('/index.php/mapos/salvarTemaCor', 'violeta'), true);
        assert.equal(chamadas[0].url, '/index.php/mapos/salvarTemaCor');
        assert.match(chamadas[0].corpo, /cor=violeta/);

        globalThis.fetch = async () => {
            throw new Error('sem rede');
        };
        assert.equal(await gravarCorNoServidor('/x', 'azul'), false, 'Falha de rede não estoura: o cache já guardou.');
    } finally {
        globalThis.fetch = original.fetch;
        globalThis.document = original.document;
    }
});

/** Monta o <body> falso do painel com as amostras e devolve o que o teste confere. */
function painelFalso(urlDaCor) {
    const html = htmlFalso();
    const ouvintes = {};
    const botoes = ['padrao', 'azul', 'violeta'].map((cor) => {
        const atributos = {};

        return { dataset: { temaCorEscolher: cor }, setAttribute: (nome, valor) => { atributos[nome] = valor; }, atributos };
    });
    const corpo = {
        dataset: urlDaCor ? { temaCorUrl: urlDaCor } : {},
        querySelector: () => null,
        querySelectorAll: (seletor) => (seletor === '[data-tema-cor-escolher]' ? botoes : []),
        addEventListener: (tipo, fn) => { (ouvintes[tipo] ??= []).push(fn); },
    };
    const clicar = (alvo) => {
        const evento = { target: { closest: (seletor) => (seletor === '[data-tema-cor-escolher]' ? alvo : null) } };
        ouvintes.click.forEach((fn) => fn(evento));
    };

    return { html, botoes, corpo, clicar };
}

function ambienteDoPainel(html, chamadas, gravado) {
    const original = {
        document: globalThis.document, window: globalThis.window, fetch: globalThis.fetch, localStorage: globalThis.localStorage,
    };
    globalThis.document = { documentElement: html, querySelector: () => null, querySelectorAll: () => [], cookie: '' };
    globalThis.window = { matchMedia: undefined };
    Object.defineProperty(globalThis, 'localStorage', { value: { setItem: (k, v) => { gravado[k] = v; }, removeItem() {} }, configurable: true });
    globalThis.fetch = async (url, opcoes) => {
        chamadas.push({ url, corpo: String(opcoes.body) });

        return respostaJson;
    };

    return () => {
        globalThis.document = original.document;
        globalThis.window = original.window;
        globalThis.fetch = original.fetch;
        Object.defineProperty(globalThis, 'localStorage', { value: original.localStorage, configurable: true });
    };
}

test('clicar numa amostra troca a cor na hora, marca aria-pressed e grava no servidor', () => {
    const { html, botoes, corpo, clicar } = painelFalso('/index.php/mapos/salvarTemaCor');
    const chamadas = [];
    const gravado = {};
    const restaurar = ambienteDoPainel(html, chamadas, gravado);

    try {
        iniciar(corpo);
        assert.equal(botoes[0].atributos['aria-pressed'], 'true', 'Sem data-tema o padrão está marcado.');

        clicar(botoes[1]);
        assert.equal(html.dataset.tema, 'azul');
        assert.equal(html.dataset.temaCorUsuario, '1', 'Daqui em diante a escolha é da pessoa.');
        assert.equal(gravado['mapos:tema-cor'], 'azul');
        assert.equal(botoes[1].atributos['aria-pressed'], 'true');
        assert.equal(botoes[0].atributos['aria-pressed'], 'false');
        assert.match(chamadas[0].corpo, /cor=azul/);

        // Valor fora da allow-list (atributo adulterado no HTML) é ignorado.
        clicar({ dataset: { temaCorEscolher: 'rosa' } });
        assert.equal(html.dataset.tema, 'azul');
        assert.equal(chamadas.length, 1);

        clicar(botoes[0]);
        assert.equal(html.dataset.tema, undefined, 'O padrão tira o atributo.');
    } finally {
        restaurar();
    }
});

test('sem a URL no <body> a troca de cor vale na tela e não quebra', () => {
    const { html, botoes, corpo, clicar } = painelFalso(null);
    const chamadas = [];
    const restaurar = ambienteDoPainel(html, chamadas, {});

    try {
        iniciar(corpo);
        clicar(botoes[2]);
        assert.equal(html.dataset.tema, 'violeta');
        assert.equal(chamadas.length, 0, 'Sem URL não há requisição.');
    } finally {
        restaurar();
    }
});
