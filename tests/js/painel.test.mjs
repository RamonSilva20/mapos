// Painel inicial (assets/js/modules/painel/painel.js, #2847): URL da agenda e
// configuração dos gráficos.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { CHAVE_VALORES, alternarValores, configuracaoBalanco, configuracaoOs, dataIso, moeda, urlDaAgenda } from '../../assets/js/modules/painel/painel.js';

test('dataIso usa o dia local', () => {
    assert.equal(dataIso(new Date(2026, 0, 5)), '2026-01-05');
    assert.equal(dataIso(new Date(2026, 11, 31, 23, 59)), '2026-12-31');
});

test('urlDaAgenda leva o período e o status só quando escolhido', () => {
    const inicio = new Date(2026, 8, 28);
    const fim = new Date(2026, 10, 9);
    assert.equal(urlDaAgenda('/index.php/mapos/calendario', inicio, fim), '/index.php/mapos/calendario?start=2026-09-28&end=2026-11-09');
    assert.equal(
        urlDaAgenda('http://localhost/index.php/mapos/calendario', inicio, fim, 'Aguardando Peças'),
        'http://localhost/index.php/mapos/calendario?start=2026-09-28&end=2026-11-09&status=Aguardando+Pe%C3%A7as',
    );
});

test('moeda em reais', () => {
    assert.equal(moeda(1234.5).replace(/\s/g, ' '), 'R$ 1.234,50');
    assert.equal(moeda('abc').replace(/\s/g, ' '), 'R$ 0,00');
});

test('configuracaoBalanco: barras de receitas e despesas e o saldo em linha', () => {
    const balanco = { meses: ['Jan'], receitas: [10], despesas: [4], saldo: [6] };
    const cores = { texto: 't', grade: 'g', receitas: 'r', despesas: 'd', saldo: 's', receitasSoft: 'rs', despesasSoft: 'ds' };
    const config = configuracaoBalanco(balanco, cores);

    assert.deepEqual(config.data.datasets.map((d) => [d.type, d.label, d.data[0]]), [['bar', 'Receitas', 10], ['bar', 'Despesas', 4], ['line', 'Saldo', 6]]);
    assert.equal(config.data.datasets[0].backgroundColor, 'rs', 'barra com fundo soft (glass)');
    assert.equal(config.data.datasets[0].borderColor, 'r', 'borda na cor-base');
    assert.equal(config.options.scales.y.ticks.callback(10).replace(/\s/g, ' '), 'R$ 10,00');
});

test('configuracaoOs: uma cor por variante do status', () => {
    const fatias = [{ status: 'Aberto', total: 2, variante: 'info' }, { status: 'Faturado', total: 1, variante: 'success' }];
    const config = configuracaoOs(fatias, (variante) => `cor-${variante}`, { texto: 't', fundo: 'f' });

    assert.equal(config.type, 'doughnut');
    assert.deepEqual(config.data.labels, ['Aberto', 'Faturado']);
    assert.deepEqual(config.data.datasets[0].backgroundColor, ['cor-info-soft', 'cor-success-soft'], 'fatia com fundo soft (glass)');
    assert.deepEqual(config.data.datasets[0].borderColor, ['cor-info', 'cor-success'], 'borda na cor-base');
    assert.deepEqual(config.data.datasets[0].data, [2, 1], 'Sem modo, usa a quantidade.');
});

test('configuracaoOs: o modo valor usa o R$ de cada fatia', () => {
    const fatias = [{ status: 'Aberto', total: 2, valor: 350, variante: 'info' }, { status: 'Faturado', total: 1, valor: 500, variante: 'success' }];
    const config = configuracaoOs(fatias, (variante) => `cor-${variante}`, { texto: 't', fundo: 'f' }, 'valor');

    assert.deepEqual(config.data.datasets[0].data, [350, 500]);
});

test('alternarValores: oculta e mostra de novo', () => {
    assert.equal(alternarValores(false), true);
    assert.equal(alternarValores(true), false);
    assert.equal(CHAVE_VALORES, 'mapos:valores-ocultos');
});
