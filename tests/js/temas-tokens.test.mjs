// Consistência dos temas de cor (#3032): assets/src/tokens.css tem de bater,
// para cada tema × modo, com os valores de scripts/contraste-temas.mjs (a fonte
// dos contrastes do DESIGN.md). Se um muda sem o outro, este teste falha.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

import { TEMAS, STATUS, statusDe } from '../../scripts/contraste-temas.mjs';

const css = readFileSync(new URL('../../assets/src/tokens.css', import.meta.url), 'utf8').replace(/\/\*[\s\S]*?\*\//g, '');

/** Declarações (--nome: valor) de todas as regras cujo seletor é exatamente `seletor`, na ordem do arquivo. */
function declaracoes(seletor) {
    const vars = {};
    for (const [, lista, corpo] of css.matchAll(/([^{}]+)\{([^{}]*)\}/g)) {
        if (!lista.split(',').some((s) => s.trim() === seletor)) {
            continue;
        }
        for (const [, nome, valor] of corpo.matchAll(/(--[\w-]+)\s*:\s*([^;]+);/g)) {
            vars[nome] = valor.trim().toLowerCase();
        }
    }

    return vars;
}

/** Seletor da regra de cada tema × modo. O Padrão vive em :root (claro) e .dark (escuro). */
function seletor(tema, modo) {
    if (tema === 'padrao') {
        return modo === 'light' ? ':root' : '.dark';
    }

    return modo === 'light' ? `:root[data-tema="${tema}"]:not(.dark)` : `:root.dark[data-tema="${tema}"]`;
}

/** Valor efetivo de uma variável: a do tema e, se não houver, a do modo (:root ou .dark). */
function efetivas(tema, modo) {
    const base = declaracoes(modo === 'light' ? ':root' : '.dark');

    return { ...base, ...declaracoes(seletor(tema, modo)) };
}

for (const [tema, modos] of Object.entries(TEMAS)) {
    for (const [modo, t] of Object.entries(modos)) {
        test(`tokens.css bate com contraste-temas.mjs: ${tema} ${modo}`, () => {
            const v = efetivas(tema, modo);

            assert.equal(v['--mapos-primary'], t.primary, '--mapos-primary');
            assert.equal(v['--mapos-primary-hover'], t.hover, '--mapos-primary-hover');
            assert.equal(v['--mapos-primary-pressed'], t.pressed, '--mapos-primary-pressed');
            assert.equal(v['--mapos-on-primary'], t.onPrimary, '--mapos-on-primary');
            assert.equal(v['--mapos-primary-strong'], t.strong, '--mapos-primary-strong');
            assert.equal(v['--mapos-primary-tint'], t.tint, '--mapos-primary-tint');

            if (t.sidebar) {
                assert.equal(v['--mapos-sidebar-bg'], t.sidebar.bg, 'fundo da sidebar');
                assert.equal(v['--mapos-sidebar-hover'], t.sidebar.hover, 'hover da sidebar');
                assert.equal(v['--mapos-sidebar-text'], t.sidebar.text, 'texto da sidebar');
                assert.equal(v['--mapos-sidebar-muted'], t.sidebar.muted, 'texto secundário da sidebar');
                assert.equal(v['--mapos-sidebar-active-bg'], t.sidebar.activeBg, 'fundo do item ativo');
                assert.equal(v['--mapos-sidebar-active-text'], t.sidebar.activeText, 'texto do item ativo');
                assert.equal(v['--mapos-sidebar-accent'], t.sidebar.accent, 'acento da sidebar');
                assert.equal(v['--mapos-sidebar-accent-ink'], t.sidebar.accentInk, 'rótulo sobre o acento');
                assert.equal(v['--mapos-sidebar-ring'], t.sidebar.ring, 'anel de foco da sidebar');
            } else {
                // A sidebar acompanha o conteúdo: o fundo dela é a superfície do modo.
                assert.equal(v['--mapos-sidebar-bg'], 'var(--mapos-surface)', 'a sidebar segue a superfície');
                assert.equal(v['--mapos-surface'], t.surface, 'superfície do modo');
            }
        });
    }
}

test('o Grafite escuro troca a paleta inteira para a neutra do script', () => {
    const t = TEMAS.grafite.dark;
    const v = efetivas('grafite', 'dark');

    assert.equal(v['--mapos-bg'], t.canvas);
    assert.equal(v['--mapos-surface'], t.surface);
    assert.equal(v['--mapos-surface-subtle'], t.subtle);
    assert.equal(v['--mapos-text'], t.text);
    assert.equal(v['--mapos-muted'], t.muted);
    assert.equal(v['--mapos-input'], t.input);
});

test('os fundos -soft de status batem com o script em todo tema × modo', () => {
    for (const [tema, modos] of Object.entries(TEMAS)) {
        for (const modo of Object.keys(modos)) {
            const v = efetivas(tema, modo);
            const status = statusDe(tema, modo === 'light' ? 'light' : 'dark');

            for (const [nome, { ink, soft }] of Object.entries(status)) {
                assert.equal(v[`--mapos-${nome}-soft`], soft, `${tema} ${modo}: ${nome}-soft`);
                assert.equal(v[`--mapos-${nome}-ink`], ink, `${tema} ${modo}: ${nome}-ink`);
            }
        }
    }
});

test('só o Grafite escuro tem status neutros; os demais seguem o escuro padrão', () => {
    assert.notDeepEqual(statusDe('grafite', 'dark'), STATUS.dark);
    assert.deepEqual(statusDe('azul', 'dark'), STATUS.dark);
    assert.deepEqual(statusDe('grafite', 'light'), STATUS.light);
});

test('a lista de temas do script é a allow-list do PHP', () => {
    const php = readFileSync(new URL('../../application/helpers/tema_helper.php', import.meta.url), 'utf8');
    const lista = php.match(/define\('TEMA_CORES', \[([^\]]+)\]\)/)[1].replaceAll("'", '').split(',').map((s) => s.trim());

    assert.deepEqual(lista, Object.keys(TEMAS));
});
