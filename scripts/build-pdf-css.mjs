// Gera assets/dist/pdf.css, o CSS dos relatórios em PDF (mPDF).
//
// O mPDF só entende CSS 2.1 e um pouco de CSS 3. A saída do Tailwind v4 usa
// @layer, var(), calc(), rem e cores modernas, então este script compila
// assets/src/pdf.css e achata o resultado: tira as camadas e os @property,
// troca var() pelo valor, rem por px e converte as cores para rgb().
import { execFileSync } from 'node:child_process';
import { mkdtempSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { transform } from 'lightningcss';

const raiz = new URL('..', import.meta.url).pathname;
const pasta = mkdtempSync(join(tmpdir(), 'mapos-pdf-'));
const compilado = join(pasta, 'pdf.css');

try {
  execFileSync('npx', ['tailwindcss', '-i', 'assets/src/pdf.css', '-o', compilado], { cwd: raiz, stdio: 'inherit' });

  const origem = readFileSync(compilado);

  // 1ª passada: junta as variáveis declaradas (:root, :host, *) para resolver os var().
  // Valores iniciais dos @property que as utilities usam (ex.: border-style).
  const variaveis = new Map([['--tw-border-style', [{ type: 'token', value: { type: 'ident', value: 'solid' } }]]]);
  transform({
    filename: 'pdf.css',
    code: origem,
    visitor: {
      Declaration: {
        custom(declaracao) {
          if (declaracao.name.startsWith('--')) variaveis.set(declaracao.name, declaracao.value);
        },
      },
    },
  });

  // Troca var(--x) pelos tokens do valor, seguindo variáveis que apontam para outras.
  const resolver = (tokens, vistos = new Set()) =>
    tokens.flatMap((token) => {
      if (token.type !== 'var') return [token];
      const nome = token.value.name.ident;
      const valor = variaveis.get(nome);
      if (vistos.has(nome)) return [];
      if (valor === undefined) return resolver(token.value.fallback ?? [], vistos);
      return resolver(valor, new Set(vistos).add(nome));
    });

  // lightningcss não desembrulha @layer com segurança: tiramos o invólucro no texto.
  const semCamadas = (css) => {
    let saida = css;
    for (let i = saida.indexOf('@layer '); i !== -1; i = saida.indexOf('@layer ')) {
      const abre = saida.indexOf('{', i);
      const ponto = saida.indexOf(';', i);
      if (ponto !== -1 && (abre === -1 || ponto < abre)) {
        saida = saida.slice(0, i) + saida.slice(ponto + 1);
        continue;
      }
      let fundo = 1;
      let fecha = abre + 1;
      while (fundo > 0 && fecha < saida.length) fundo += { '{': 1, '}': -1 }[saida[fecha++]] ?? 0;
      saida = saida.slice(0, i) + saida.slice(abre + 1, fecha - 1) + saida.slice(fecha);
    }
    return saida;
  };

  const { code } = transform({
    filename: 'pdf.css',
    code: origem,
    minify: true,
    targets: { chrome: 40 << 16, safari: 8 << 16 },
    visitor: {
      Rule: {
        'layer-statement': () => [],
        property: () => [],
        supports: () => [],
      },
      Declaration: {
        custom: (declaracao) => (declaracao.name.startsWith('--') ? [] : undefined),
      },
      Variable: (variavel) => resolver([{ type: 'var', value: variavel }]),
      Length: (comprimento) =>
        comprimento.unit === 'rem' ? { unit: 'px', value: comprimento.value * 16 } : undefined,
    },
  });

  // 2ª passada, sem visitor: com os var() já trocados, o calc() vira número.
  const dobrado = transform({ filename: 'pdf.css', code, minify: true }).code.toString();
  // O lightningcss deixa regras auxiliares (--lightningcss-light) por causa dos targets.
  const limpo = semCamadas(dobrado).replace(/[^{}]*\{[^{}]*--lightningcss[^{}]*\}/g, '');

  // O mPDF não conhece as propriedades lógicas (padding-inline, margin-block...).
  const fisico = limpo.replace(/(padding|margin)-(inline|block):([^;}]+)/g, (_, propriedade, eixo, valor) => {
    const [inicio, fim = inicio] = valor.trim().split(/\s+/);
    const lados = eixo === 'inline' ? ['left', 'right'] : ['top', 'bottom'];
    return `${propriedade}-${lados[0]}:${inicio};${propriedade}-${lados[1]}:${fim}`;
  });

  writeFileSync(join(raiz, 'assets/dist/pdf.css'), fisico);
} finally {
  rmSync(pasta, { recursive: true, force: true });
}
