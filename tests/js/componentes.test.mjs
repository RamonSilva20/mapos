// Testes dos módulos dos componentes (assets/js/modules/componentes e
// assets/js/lib/toast.js). Sem DOM real: um documento falso mínimo basta.
import { test } from 'node:test';
import assert from 'node:assert/strict';

import { deveFecharPeloFundo } from '../../assets/js/modules/componentes/modal.js';
import { criarTemporizador } from '../../assets/js/modules/componentes/toast.js';
import { aplicarModo } from '../../assets/js/modules/componentes/catalogo.js';
import { criarToast, regiaoDeToasts, CLASSES_REGIAO } from '../../assets/js/lib/toast.js';

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
