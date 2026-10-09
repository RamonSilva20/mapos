// Módulos do financeiro (assets/js/modules/financeiro, #2844): períodos da
// listagem e cálculos de prévia do formulário.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { formatarIso, intervaloDoPeriodo } from '../../assets/js/modules/financeiro/filtros.js';
import {
    dividirCentavos,
    formatarReais,
    liquidoEmCentavos,
    paraCentavos,
    resumoDoParcelamento,
} from '../../assets/js/modules/financeiro/formulario.js';

// Quarta-feira, 14/10/2026.
const hoje = new Date(2026, 9, 14);

test('intervaloDoPeriodo calcula os períodos predefinidos', () => {
    assert.deepEqual(intervaloDoPeriodo('dia', hoje), ['2026-10-14', '2026-10-14']);
    assert.deepEqual(intervaloDoPeriodo('semana', hoje), ['2026-10-11', '2026-10-17'], 'Domingo a sábado.');
    assert.deepEqual(intervaloDoPeriodo('mes_anterior', hoje), ['2026-09-01', '2026-09-30']);
    assert.deepEqual(intervaloDoPeriodo('mes', hoje), ['2026-10-01', '2026-10-31']);
    assert.deepEqual(intervaloDoPeriodo('mes_posterior', hoje), ['2026-11-01', '2026-11-30']);
    assert.deepEqual(intervaloDoPeriodo('ano', hoje), ['2026-01-01', '2026-12-31']);
});

test('intervaloDoPeriodo atravessa o fim do ano e fevereiro bissexto', () => {
    assert.deepEqual(intervaloDoPeriodo('mes_posterior', new Date(2026, 11, 20)), ['2027-01-01', '2027-01-31']);
    assert.deepEqual(intervaloDoPeriodo('mes_anterior', new Date(2027, 0, 5)), ['2026-12-01', '2026-12-31']);
    assert.deepEqual(intervaloDoPeriodo('mes', new Date(2028, 1, 10)), ['2028-02-01', '2028-02-29']);
    assert.deepEqual(intervaloDoPeriodo('semana', new Date(2026, 0, 1)), ['2025-12-28', '2026-01-03']);
});

test('intervaloDoPeriodo não tem intervalo para personalizado nem para nome desconhecido', () => {
    assert.equal(intervaloDoPeriodo('personalizado', hoje), null);
    assert.equal(intervaloDoPeriodo('qualquer', hoje), null);
});

test('formatarIso zera à esquerda', () => {
    assert.equal(formatarIso(new Date(2026, 0, 5)), '2026-01-05');
});

test('paraCentavos entende o formato da máscara e o do banco', () => {
    assert.equal(paraCentavos('1.234,56'), 123456);
    assert.equal(paraCentavos('R$ 10,5'), 1050);
    assert.equal(paraCentavos('1234.56'), 123456);
    assert.equal(paraCentavos('0,00'), 0);
    assert.equal(paraCentavos(''), null);
    assert.equal(paraCentavos('abc'), null);
    assert.equal(paraCentavos(null), null);
});

test('formatarReais usa o ponto de milhar e a vírgula dos centavos', () => {
    assert.equal(formatarReais(123456), 'R$ 1.234,56');
    assert.equal(formatarReais(5), 'R$ 0,05');
    assert.equal(formatarReais(100000000), 'R$ 1.000.000,00');
    assert.equal(formatarReais(-250), '-R$ 2,50');
});

test('dividirCentavos reparte o resto nas primeiras parcelas e soma o total', () => {
    assert.deepEqual(dividirCentavos(10000, 3), [3334, 3333, 3333]);
    assert.deepEqual(dividirCentavos(10000, 4), [2500, 2500, 2500, 2500]);
    assert.deepEqual(dividirCentavos(1, 3), [1, 0, 0]);
    assert.equal(dividirCentavos(99999, 7).reduce((a, b) => a + b, 0), 99999);
    assert.deepEqual(dividirCentavos(500, 0), [500], 'Zero parcelas vira uma.');
});

test('liquidoEmCentavos desconta, e recusa valor inválido ou desconto que não cabe', () => {
    assert.equal(liquidoEmCentavos('1.000,00', '250,00'), 75000);
    assert.equal(liquidoEmCentavos('1.000,00', ''), 100000);
    assert.equal(liquidoEmCentavos('1.000,00', '1.000,00'), null);
    assert.equal(liquidoEmCentavos('0,00', ''), null);
    assert.equal(liquidoEmCentavos('', ''), null);
    assert.equal(liquidoEmCentavos('100,00', 'x'), null);
});

test('resumoDoParcelamento descreve as parcelas e a entrada', () => {
    assert.equal(resumoDoParcelamento({ liquido: 30000, parcelas: 3 }), '3x de R$ 100,00');
    assert.equal(resumoDoParcelamento({ liquido: 10000, parcelas: 3 }), '3x de R$ 33,34 (as últimas de R$ 33,33)');
    assert.equal(resumoDoParcelamento({ liquido: 50000, parcelas: 4, entrada: 20000 }), 'Entrada de R$ 200,00 + 4x de R$ 75,00');
    assert.equal(resumoDoParcelamento({ liquido: 10000, parcelas: 1 }), '', 'À vista não tem resumo.');
    assert.equal(resumoDoParcelamento({ liquido: null, parcelas: 3 }), '');
    assert.equal(resumoDoParcelamento({ liquido: 10000, parcelas: 3, entrada: 10000 }), '', 'Entrada não pode cobrir tudo.');
});
