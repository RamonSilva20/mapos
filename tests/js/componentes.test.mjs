// Testes dos módulos dos componentes (assets/js/modules/componentes e
// assets/js/lib/toast.js). Sem DOM real: um documento falso mínimo basta.
import { test } from 'node:test';
import assert from 'node:assert/strict';

import { deveFecharPeloFundo, preencher } from '../../assets/js/modules/componentes/modal.js';
import { criarTemporizador } from '../../assets/js/modules/componentes/toast.js';
import { aplicarModo } from '../../assets/js/modules/componentes/catalogo.js';
import { criarToast, regiaoDeToasts, CLASSES_REGIAO } from '../../assets/js/lib/toast.js';
import { criarIcone, URL_SPRITE } from '../../assets/js/lib/icone.js';

function documentoFalso() {
    const criar = (tag) => {
        const filhos = [];
        const atributos = {};
        return {
            tagName: tag.toUpperCase(),
            className: '',
            textContent: '',
            type: '',
            dataset: {},
            filhos,
            atributos,
            setAttribute(nome, valor) {
                atributos[nome] = String(valor);
            },
            getAttribute(nome) {
                return atributos[nome] ?? null;
            },
            appendChild(filho) {
                filhos.push(filho);
                return filho;
            },
            append(...novos) {
                filhos.push(...novos);
            },
        };
    };
    const body = criar('body');

    return {
        body,
        createElement: criar,
        createElementNS: (namespace, tag) => Object.assign(criar(tag), { namespaceURI: namespace }),
        querySelector: (seletor) => (seletor === '[data-toast-region]' ? body.filhos.find((f) => 'data-toast-region' in f.atributos) ?? null : null),
    };
}

test('modal: clique no fundo fecha, clique dentro da caixa não', () => {
    const dialogo = { getBoundingClientRect: () => ({ left: 100, right: 300, top: 100, bottom: 200 }) };

    assert.equal(deveFecharPeloFundo({ target: dialogo, clientX: 50, clientY: 150 }, dialogo), true);
    assert.equal(deveFecharPeloFundo({ target: dialogo, clientX: 150, clientY: 150 }, dialogo), false);
    assert.equal(deveFecharPeloFundo({ target: {}, clientX: 50, clientY: 50 }, dialogo), false);
});

test('toast: temporizador termina, pausa e retoma com o tempo restante', () => {
    const agendados = [];
    const relogio = {
        setTimeout: (fn, ms) => agendados.push({ fn, ms, ativo: true }) - 1,
        clearTimeout: (id) => {
            agendados[id].ativo = false;
        },
    };
    let terminou = 0;
    const t = criarTemporizador(5000, () => terminou++, relogio);

    t.iniciar();
    assert.equal(agendados[0].ms, 5000);
    assert.equal(t.ativo, true);

    t.pausar();
    assert.equal(agendados[0].ativo, false);
    assert.equal(t.ativo, false);

    t.iniciar();
    assert.ok(agendados[1].ms <= 5000 && agendados[1].ms > 4900);
    agendados[1].fn();
    assert.equal(terminou, 1);
    assert.equal(t.ativo, false);
});

test('toast: duração 0 nunca agenda', () => {
    let chamadas = 0;
    const t = criarTemporizador(0, () => {}, { setTimeout: () => chamadas++, clearTimeout: () => {} });

    t.iniciar();
    assert.equal(chamadas, 0);
});

test('criarToast: texto entra como texto, papel segue a variante', () => {
    const doc = documentoFalso();
    const toast = criarToast({ mensagem: '<img src=x onerror=alert(1)>', titulo: 'Erro', variante: 'danger', duracao: -5 }, doc);

    assert.equal(toast.getAttribute('role'), 'alert');
    assert.equal(toast.dataset.duracao, '0');
    const [, corpo, fechar] = toast.filhos;
    assert.equal(corpo.filhos[0].textContent, 'Erro');
    assert.equal(corpo.filhos[1].textContent, '<img src=x onerror=alert(1)>');
    assert.equal(fechar.getAttribute('data-dispensar'), '');
    assert.equal(criarToast({ mensagem: 'ok' }, doc).getAttribute('role'), 'status');
    assert.throws(() => criarToast({ mensagem: 'x', variante: 'roxo' }, doc), /Variante de toast inválida/);
});

test('criarIcone: SVG do sprite Lucide, decorativo, com a classe pedida', () => {
    const doc = documentoFalso();
    const svg = criarIcone('plus', 'size-4 text-muted', doc);

    assert.equal(svg.namespaceURI, 'http://www.w3.org/2000/svg');
    assert.equal(svg.getAttribute('class'), 'shrink-0 size-4 text-muted');
    assert.equal(svg.getAttribute('aria-hidden'), 'true');
    assert.equal(svg.getAttribute('stroke'), 'currentColor');
    assert.equal(svg.filhos[0].getAttribute('href'), `${URL_SPRITE}#plus`);
    assert.match(URL_SPRITE, /\/assets\/vendor\/lucide\/sprite\.svg$/);
    assert.throws(() => criarIcone('plus" onload="x', '', doc), /Nome de ícone inválido/);
});

test('criarToast: ícone Lucide da variante', () => {
    const doc = documentoFalso();
    const [icone] = criarToast({ mensagem: 'ok', variante: 'success' }, doc).filhos;

    assert.equal(icone.namespaceURI, 'http://www.w3.org/2000/svg');
    assert.match(icone.getAttribute('class'), /text-success-ink/);
    assert.match(icone.filhos[0].getAttribute('href'), /#circle-check$/);
});

test('regiaoDeToasts: cria uma vez e reaproveita', () => {
    const doc = documentoFalso();
    const regiao = regiaoDeToasts(doc);

    assert.equal(regiao.className, CLASSES_REGIAO);
    assert.equal(regiaoDeToasts(doc), regiao);
    assert.equal(doc.body.filhos.length, 1);
});

test('catálogo: aplicarModo liga .dark conforme o modo', () => {
    const classes = new Set();
    const html = { dataset: {}, classList: { toggle: (c, ligar) => (ligar ? classes.add(c) : classes.delete(c)) } };

    aplicarModo(html, 'escuro', false);
    assert.ok(classes.has('dark'));
    aplicarModo(html, 'claro', true);
    assert.ok(!classes.has('dark'));
    aplicarModo(html, 'sistema', true);
    assert.ok(classes.has('dark'));
    assert.equal(html.dataset.temaModo, 'sistema');
});

test('modal: preenche os alvos com os data-valor-* do gatilho, sem HTML', () => {
    const gatilho = { getAttribute: (nome) => ({ 'data-valor-id': '15', 'data-valor-nome': '<b>Ana</b>' }[nome] ?? null) };
    const input = { tagName: 'INPUT', dataset: { modalValor: 'id' }, value: '' };
    const nome = { tagName: 'STRONG', dataset: { modalValor: 'nome' }, textContent: '' };
    const semValor = { tagName: 'SPAN', dataset: { modalValor: 'telefone' }, textContent: 'antes' };

    preencher([input, nome, semValor], gatilho);

    assert.equal(input.value, '15');
    assert.equal(nome.textContent, '<b>Ana</b>');
    assert.equal(nome.innerHTML, undefined);
    assert.equal(semValor.textContent, 'antes');
});
