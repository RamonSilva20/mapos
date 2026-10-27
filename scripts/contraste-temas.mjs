#!/usr/bin/env node
/**
 * Contraste WCAG dos temas de cor do painel (DESIGN.md, "Color themes").
 *
 * Define os tokens de cada tema (padrão, azul, verde, violeta, grafite) em
 * cada modo (claro, escuro), calcula as razões dos pares que importam e sai
 * com código 1 se algum reprovar. Node puro, sem dependências.
 *
 *   node scripts/contraste-temas.mjs           lista de pares
 *   node scripts/contraste-temas.mjs --md      tabela de contrastes em Markdown
 *   node scripts/contraste-temas.mjs --tokens  tabela de tokens em Markdown
 *
 * Os valores abaixo são a fonte dos blocos de tema de assets/src/tokens.css
 * (PR seguinte). Mude aqui e no DESIGN.md juntos.
 */

const hex = (h) => {
  const n = parseInt(h.slice(1), 16);
  return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
};
const toHex = (c) => '#' + c.map((v) => Math.round(v).toString(16).padStart(2, '0')).join('');
/** Mistura `fg` com opacidade `a` sobre `bg` (como o navegador). */
const sobre = (fg, a, bg) => {
  const f = hex(fg);
  const b = hex(bg);
  return toHex(f.map((v, i) => v * a + b[i] * (1 - a)));
};
const luz = (h) => {
  const [r, g, b] = hex(h).map((v) => {
    const s = v / 255;
    return s <= 0.03928 ? s / 12.92 : ((s + 0.055) / 1.055) ** 2.4;
  });
  return 0.2126 * r + 0.7152 * g + 0.0722 * b;
};
const razao = (a, b) => {
  const [x, y] = [luz(a), luz(b)].sort((p, q) => q - p);
  return (x + 0.05) / (y + 0.05);
};

/* ---- Bases dos modos (iguais ao DESIGN.md / tokens.css atuais) ---- */
const CLARO = {
  canvas: '#ffffff',
  surface: '#ffffff',
  subtle: '#f6f5f9',
  text: '#1f1633',
  muted: '#5b5470',
  input: '#8a849c',
  ring: '#3b82f6',
};
const ESCURO = {
  canvas: '#1f1633',
  surface: '#150f23',
  subtle: '#1b1430',
  text: '#ffffff',
  muted: sobre('#ffffff', 0.72, '#150f23'), // rgb(255 255 255 / 72%) sobre o card
  input: '#756d8a',
  ring: '#3b82f6',
};
/** Anel de foco claro, só para a sidebar escura do modo claro (#3b82f6 não chega a 3:1). */
const ANEL_SIDEBAR = '#93c5fd';
const tint = (primary, base, a) => sobre(primary, a, base);

/**
 * Cada tema × modo: cor de ação (primary, hover, pressed, onPrimary), strong
 * (texto/link na cor da ação), tint (fundo do item ativo) e a sidebar.
 * `sidebar: null` = a sidebar acompanha surface/tint/strong (padrão e escuro).
 */
const claro = (extra) => ({ ...CLARO, ...extra });
const escuro = (extra) => ({ ...ESCURO, ...extra });

export const TEMAS = {
  padrao: {
    light: claro({
      primary: '#f37338', hover: '#f47e48', pressed: '#db6230', onPrimary: '#1f1633',
      strong: '#b34d1d', tint: '#feeee7', sidebar: null,
    }),
    dark: escuro({
      primary: '#f37338', hover: '#f47e48', pressed: '#db6230', onPrimary: '#1f1633',
      strong: '#f37338', tint: '#391f26', sidebar: null,
    }),
  },
  azul: {
    light: claro({
      primary: '#2240b8', hover: '#2b4fd6', pressed: '#1a3190', onPrimary: '#ffffff',
      strong: '#2240b8', tint: tint('#2240b8', '#ffffff', 0.1),
      sidebar: { bg: '#121a45', hover: '#1c2762', text: '#eef1ff', muted: '#b7c0ea', activeBg: '#2b4fd6', activeText: '#ffffff', ring: ANEL_SIDEBAR },
    }),
    dark: escuro({
      primary: '#7f98ff', hover: '#93a9ff', pressed: '#6a85f2', onPrimary: '#1f1633',
      strong: '#a3b4ff', tint: tint('#7f98ff', '#150f23', 0.16), sidebar: null,
    }),
  },
  verde: {
    light: claro({
      primary: '#0b6b60', hover: '#0f7a6e', pressed: '#08524a', onPrimary: '#ffffff',
      strong: '#0b6b60', tint: tint('#0b6b60', '#ffffff', 0.1),
      sidebar: { bg: '#0a3a35', hover: '#0f4d46', text: '#e8fbf8', muted: '#a9d9d2', activeBg: '#0f7a6e', activeText: '#ffffff', ring: ANEL_SIDEBAR },
    }),
    dark: escuro({
      primary: '#2dd4bf', hover: '#5eead4', pressed: '#19b8a5', onPrimary: '#1f1633',
      strong: '#5eead4', tint: tint('#2dd4bf', '#150f23', 0.16), sidebar: null,
    }),
  },
  violeta: {
    light: claro({
      primary: '#7424c4', hover: '#8230d6', pressed: '#5f1da3', onPrimary: '#ffffff',
      strong: '#7424c4', tint: tint('#7424c4', '#ffffff', 0.1),
      sidebar: { bg: '#2b1257', hover: '#3d1c78', text: '#f6eeff', muted: '#cdb6ee', activeBg: '#7424c4', activeText: '#ffffff', ring: ANEL_SIDEBAR },
    }),
    dark: escuro({
      primary: '#cf86ff', hover: '#da9dff', pressed: '#bd6ff0', onPrimary: '#1f1633',
      strong: '#dcaaff', tint: tint('#cf86ff', '#150f23', 0.16), sidebar: null,
    }),
  },
  grafite: {
    light: claro({
      primary: '#f37338', hover: '#f47e48', pressed: '#db6230', onPrimary: '#1f1633',
      strong: '#b34d1d', tint: '#feeee7',
      sidebar: { bg: '#1c1c1f', hover: '#2a2a2e', text: '#f4f4f5', muted: '#b4b4bc', activeBg: '#f37338', activeText: '#1f1633', ring: ANEL_SIDEBAR },
    }),
    dark: {
      canvas: '#171717', surface: '#1f1f1f', subtle: '#27272a', text: '#fafafa',
      muted: '#a1a1aa', input: '#74747d', ring: '#3b82f6',
      primary: '#f97316', hover: '#fb8c3a', pressed: '#ea670c', onPrimary: '#171717',
      strong: '#fb923c', tint: '#2a2015', sidebar: null,
    },
  },
};

/**
 * Status (DESIGN.md, Semantic e Panel Dark Mode): a cor-base é fixa; -ink e
 * -soft mudam com o modo. Fonte: assets/src/tokens.css.
 */
const STATUS_BASE = { success: '#15803d', warning: '#a16207', info: '#0369a1', progress: '#6a5fc1', neutral: '#79628c' };
const STATUS = {
  light: {
    success: { ink: '#166534', soft: '#e3f0e8' }, warning: { ink: '#854d0e', soft: '#f4ece1' },
    info: { ink: '#075985', soft: '#e1edf4' }, progress: { ink: '#4a3f9e', soft: '#edecf8' },
    neutral: { ink: '#4b4560', soft: '#efecf1' },
  },
  dark: {
    success: { ink: '#4ade80', soft: '#1d3032' }, warning: { ink: '#facc15', soft: '#3a2d21' },
    info: { ink: '#38bdf8', soft: '#1b2b45' }, progress: { ink: '#b8b0f0', soft: '#2b244c' },
    neutral: { ink: '#d6cfe0', soft: '#2f253e' },
  },
};

/**
 * kpi-card: o degradê é a cor-base do status a KPI_MIX sobre a superfície
 * (mesmo valor do color-mix em assets/src/app.css). O pior ponto é o canto,
 * onde o degradê é mais forte; confere texto, valor, caption, pill de
 * tendência (ink sobre soft) e sparkline (-ink) contra esse fundo.
 */
const KPI_MIX = 0.20;
const KPI_STATUS = ['success', 'warning', 'info', 'progress'];
const kpiFundo = (t, modo, s) => sobre(STATUS_BASE[s], KPI_MIX, t.surface);
const PARES_KPI = [];
for (const s of KPI_STATUS) {
  PARES_KPI.push(
    [`kpi ${s}: label/caption (muted) no canto`, 4.5, (t, m) => [t.muted, kpiFundo(t, m, s)]],
    [`kpi ${s}: valor (text) no canto`, 4.5, (t, m) => [t.text, kpiFundo(t, m, s)]],
    [`kpi ${s}: sparkline (ink) no canto`, 3, (t, m) => [STATUS[m][s].ink, kpiFundo(t, m, s)]],
    [`kpi ${s}: ícone (ink on soft)`, 4.5, (t, m) => [STATUS[m][s].ink, STATUS[m][s].soft]],
  );
}
// Pill de tendência: bom = success, ruim = warning, estável = neutral (ink sobre soft).
for (const s of ['success', 'warning', 'neutral']) {
  PARES_KPI.push([`kpi tendência ${s}: ink on soft`, 4.5, (t, m) => [STATUS[m][s].ink, STATUS[m][s].soft]]);
}

/** Pares conferidos: [rótulo, mínimo, função (t) => [fg, bg]]. */
const PARES_TEMA = [
  ['on-primary on primary', 4.5, (t) => [t.onPrimary, t.primary]],
  ['on-primary on hover', 4.5, (t) => [t.onPrimary, t.hover]],
  ['on-primary on pressed', 4.5, (t) => [t.onPrimary, t.pressed]],
  ['primary-strong on canvas', 4.5, (t) => [t.strong, t.canvas]],
  ['primary-strong on surface', 4.5, (t) => [t.strong, t.surface]],
  ['primary-strong on primary-tint', 4.5, (t) => [t.strong, t.tint]],
  ['muted on surface', 4.5, (t) => [t.muted, t.surface]],
  ['muted on surface-subtle', 4.5, (t) => [t.muted, t.subtle]],
  ['input border on surface', 3, (t) => [t.input, t.surface]],
  ['input border on canvas', 3, (t) => [t.input, t.canvas]],
  ['focus ring on canvas', 3, (t) => [t.ring, t.canvas]],
  ['focus ring on sidebar', 3, (t) => [t.sidebar?.ring ?? t.ring, t.sidebar?.bg ?? t.surface]],
  ['sidebar: text', 4.5, (t) => [t.sidebar?.text ?? t.text, t.sidebar?.bg ?? t.surface]],
  ['sidebar: text on hover', 4.5, (t) => [t.sidebar?.text ?? t.text, t.sidebar?.hover ?? t.subtle]],
  ['sidebar: muted', 4.5, (t) => [t.sidebar?.muted ?? t.muted, t.sidebar?.bg ?? t.surface]],
  ['sidebar: active item', 4.5, (t) => [t.sidebar?.activeText ?? t.strong, t.sidebar?.activeBg ?? t.tint]],
];

const PARES = [...PARES_TEMA, ...PARES_KPI];

/** Cores que a ação nunca pode repetir: status e foco (DESIGN.md, Semantic). */
const RESERVADAS = {
  success: '#15803d', info: '#0369a1', progress: '#6a5fc1', warning: '#a16207',
  danger: '#dc2626', neutral: '#79628c', foco: '#3b82f6',
  'success-dark': '#4ade80', 'info-dark': '#38bdf8', 'progress-dark': '#b8b0f0',
};

const linhas = [];
let falhas = 0;
for (const [tema, modos] of Object.entries(TEMAS)) {
  for (const [modo, t] of Object.entries(modos)) {
    for (const [rotulo, min, fn] of PARES) {
      const [fg, bg] = fn(t, modo === 'light' ? 'light' : 'dark');
      const r = razao(fg, bg);
      const ok = r >= min;
      if (!ok) falhas++;
      linhas.push({ tema, modo, rotulo, fg, bg, min, r, ok });
    }
    for (const [nome, h] of Object.entries(RESERVADAS)) {
      if (t.primary.toLowerCase() === h) {
        falhas++;
        linhas.push({ tema, modo, rotulo: `primary equals ${nome}`, fg: t.primary, bg: h, min: 0, r: 0, ok: false });
      }
    }
  }
}

const arg = process.argv[2];
const f = (n) => n.toFixed(2).replace('.', ',');

if (arg === '--tokens') {
  console.log('| Tema | Modo | primary | hover | pressed | on-primary | strong | tint | sidebar | item ativo (fundo / texto) |');
  console.log('| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |');
  for (const [tema, modos] of Object.entries(TEMAS)) {
    for (const [modo, t] of Object.entries(modos)) {
      const s = t.sidebar;
      console.log(`| ${tema} | ${modo} | \`${t.primary}\` | \`${t.hover}\` | \`${t.pressed}\` | \`${t.onPrimary}\` | \`${t.strong}\` | \`${t.tint}\` | ${s ? `\`${s.bg}\`` : 'surface'} | ${s ? `\`${s.activeBg}\` / \`${s.activeText}\`` : 'tint / strong'} |`);
    }
  }
} else if (arg === '--md') {
  // Um par por linha, um tema × modo por coluna; ponto decimal, como o DESIGN.md.
  const cols = Object.entries(TEMAS).flatMap(([tema, modos]) => Object.keys(modos).map((modo) => [tema, modo]));
  const ponto = (n) => n.toFixed(2);
  console.log(`| Pair | Min | ${cols.map(([t, m]) => `${t} ${m}`).join(' | ')} |`);
  console.log(`| --- | ---: | ${cols.map(() => '---:').join(' | ')} |`);
  for (const [rotulo, min] of PARES) {
    const cel = cols.map(([t, m]) => ponto(linhas.find((l) => l.tema === t && l.modo === m && l.rotulo === rotulo).r));
    console.log(`| ${rotulo} | ${ponto(min)} | ${cel.join(' | ')} |`);
  }
} else {
  const w = Math.max(...linhas.map((l) => l.rotulo.length));
  for (const l of linhas) {
    console.log(`${l.ok ? 'ok   ' : 'FALHA'} ${l.tema.padEnd(8)} ${l.modo.padEnd(6)} ${l.rotulo.padEnd(w)} ${l.fg} on ${l.bg}  ${f(l.r)}:1 (mín. ${f(l.min)})`);
  }
}

if (falhas > 0) {
  console.error(`\n${falhas} par(es) reprovado(s).`);
  process.exit(1);
}
if (!arg) console.log(`\nTodos os ${linhas.length} pares passam.`);
