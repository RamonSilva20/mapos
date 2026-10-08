// Copia os arquivos de distribuição das bibliotecas de JS de node_modules
// para assets/vendor/<lib>/, que é commitado. Assim quem instala o Map-OS
// não precisa de Node, como acontece com o CSS do Tailwind.
//
// Uso: npm run build:vendor
//
// assets/vendor é inteiro gerado por este script: ele é apagado e recriado a
// cada execução, para que um arquivo removido da lista também saia de lá.
// Não edite nada dentro dele à mão.

import { cpSync, existsSync, mkdirSync, readFileSync, readdirSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const raiz = join(dirname(fileURLToPath(import.meta.url)), '..');
const modulos = join(raiz, 'node_modules');
const destino = join(raiz, 'assets', 'vendor');

// pasta em assets/vendor → pacote npm e arquivos copiados (origem → nome final).
// Só os arquivos de navegador: build UMD/global minificado e o CSS próprio de
// cada lib. Nada de .map, ESM ou CommonJS.
const bibliotecas = {
  alpinejs: {
    pacote: 'alpinejs',
    arquivos: { 'dist/cdn.min.js': 'alpine.min.js' },
  },
  'tom-select': {
    pacote: 'tom-select',
    arquivos: {
      'dist/js/tom-select.complete.min.js': 'tom-select.complete.min.js',
      'dist/css/tom-select.min.css': 'tom-select.min.css',
    },
  },
  flatpickr: {
    pacote: 'flatpickr',
    arquivos: {
      'dist/flatpickr.min.js': 'flatpickr.min.js',
      'dist/flatpickr.min.css': 'flatpickr.min.css',
      'dist/l10n/pt.js': 'l10n/pt.js',
    },
  },
  imask: {
    pacote: 'imask',
    arquivos: { 'dist/imask.min.js': 'imask.min.js' },
  },
  // O build sem ".all" não injeta <style> pelo JS: o CSS vem em arquivo
  // separado, o que conta para a CSP sem unsafe-inline da RC (#2878).
  sweetalert2: {
    pacote: 'sweetalert2',
    arquivos: {
      'dist/sweetalert2.min.js': 'sweetalert2.min.js',
      'dist/sweetalert2.min.css': 'sweetalert2.min.css',
    },
  },
  'chart.js': {
    pacote: 'chart.js',
    arquivos: { 'dist/chart.umd.min.js': 'chart.umd.min.js' },
  },
  // O pacote "fullcalendar" é o bundle global com core, interaction,
  // daygrid, timegrid, list e multimonth. O locale vem do @fullcalendar/core,
  // na mesma versão.
  fullcalendar: {
    pacote: 'fullcalendar',
    arquivos: { 'index.global.min.js': 'fullcalendar.global.min.js' },
    extras: [
      {
        pacote: '@fullcalendar/core',
        arquivos: { 'locales/pt-br.global.min.js': 'locales/pt-br.global.min.js' },
      },
    ],
  },
};

function lerPacote(pacote) {
  return JSON.parse(readFileSync(join(modulos, pacote, 'package.json'), 'utf8'));
}

function copiar(pacote, arquivos, pasta) {
  for (const [origem, nome] of Object.entries(arquivos)) {
    const de = join(modulos, pacote, origem);
    if (!existsSync(de)) {
      throw new Error(`${pacote}: ${origem} não existe. Rode "npm ci" ou ajuste scripts/build-vendor.mjs.`);
    }
    const para = join(pasta, nome);
    mkdirSync(dirname(para), { recursive: true });
    cpSync(de, para);
  }
}

// Copia o arquivo de licença do pacote. Quando o pacote não publica um (o
// alpinejs é o caso), grava um aviso com a licença declarada no package.json.
function copiarLicenca(pacote, pasta) {
  const dir = join(modulos, pacote);
  const arquivo = readdirSync(dir).find((nome) => /^licen[cs]e(\.(md|txt))?$/i.test(nome));
  const sufixo = pacote.replace('@', '').replace('/', '-');
  const nomeFinal = pacote.startsWith('@') ? `LICENSE.${sufixo}` : 'LICENSE';

  if (arquivo) {
    cpSync(join(dir, arquivo), join(pasta, nomeFinal));
    return;
  }

  const info = lerPacote(pacote);
  const repo = typeof info.repository === 'string' ? info.repository : info.repository?.url ?? '';
  writeFileSync(
    join(pasta, nomeFinal),
    `${info.name} ${info.version}\nLicença: ${info.license}\n${repo ? `Repositório: ${repo}\n` : ''}\n` +
      'O pacote npm não inclui o arquivo de licença; esta nota registra a licença declarada no package.json.\n'
  );
}

rmSync(destino, { recursive: true, force: true });

const versoes = {};

for (const [nome, lib] of Object.entries(bibliotecas)) {
  const pasta = join(destino, nome);

  for (const fonte of [lib, ...(lib.extras ?? [])]) {
    copiar(fonte.pacote, fonte.arquivos, pasta);
    copiarLicenca(fonte.pacote, pasta);
    versoes[fonte.pacote] = lerPacote(fonte.pacote).version;
  }
}

// Registra a versão de cada lib copiada, para quem olha o assets/vendor sem
// abrir o package-lock.json.
writeFileSync(join(destino, 'versions.json'), JSON.stringify(versoes, null, 2) + '\n');

console.log(`assets/vendor atualizado: ${Object.keys(versoes).length} pacotes.`);
