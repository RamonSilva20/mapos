// Toast a partir do JavaScript: mesmo HTML de application/views/components/toast.php.
//
//     import { mostrarToast } from '../lib/toast.js';
//     mostrarToast({ mensagem: 'Cliente salvo.', variante: 'success' });
//
// O texto entra por textContent, nunca como HTML. O toast vai para a região
// [data-toast-region] da página; sem ela, uma região é criada no <body>.
// As classes repetem as do partial PHP: se mudar uma, mude a outra.

import iniciarToast from '../modules/componentes/toast.js';

export const VARIANTES = {
    info: 'bx-info-circle text-info-ink',
    success: 'bx-check-circle text-success-ink',
    warning: 'bx-error text-warning-ink',
    danger: 'bx-error-circle text-danger-ink',
};

export const CLASSES_REGIAO = 'pointer-events-none fixed right-4 bottom-4 z-50 flex flex-col items-end gap-2';
export const CLASSES_TOAST = 'pointer-events-auto flex w-80 max-w-full items-start gap-3 rounded-card border border-border bg-surface px-4 py-3 text-sm text-text shadow-overlay';

export function regiaoDeToasts(doc = document) {
    let regiao = doc.querySelector('[data-toast-region]');

    if (!regiao) {
        regiao = doc.createElement('div');
        regiao.setAttribute('data-toast-region', '');
        regiao.className = CLASSES_REGIAO;
        doc.body.appendChild(regiao);
    }

    return regiao;
}

export function criarToast({ mensagem, titulo = null, variante = 'info', duracao = 5000, rotuloFechar = 'Fechar notificação' }, doc = document) {
    if (!(variante in VARIANTES)) {
        throw new Error(`Variante de toast inválida: ${variante}`);
    }

    const el = (tag, classe = '') => {
        const n = doc.createElement(tag);
        if (classe) {
            n.className = classe;
        }
        return n;
    };

    const toast = el('div', CLASSES_TOAST);
    toast.setAttribute('role', variante === 'warning' || variante === 'danger' ? 'alert' : 'status');
    toast.setAttribute('aria-atomic', 'true');
    toast.dataset.duracao = String(Math.max(0, Number(duracao) || 0));

    const icone = el('i', `bx ${VARIANTES[variante]} mt-0.5 text-lg leading-none`);
    icone.setAttribute('aria-hidden', 'true');

    const corpo = el('div', 'min-w-0 flex-1');
    if (titulo) {
        const t = el('p', 'font-semibold');
        t.textContent = titulo;
        corpo.appendChild(t);
    }
    const texto = el('div', titulo ? 'mt-0.5 text-muted' : '');
    texto.textContent = mensagem;
    corpo.appendChild(texto);

    const fechar = el('button', '-m-1 inline-flex size-7 shrink-0 items-center justify-center rounded-control text-muted hover:bg-surface-subtle hover:text-text focus-visible:outline-2 focus-visible:outline-ring');
    fechar.type = 'button';
    fechar.setAttribute('data-dispensar', '');
    const x = el('span', 'text-lg leading-none');
    x.setAttribute('aria-hidden', 'true');
    x.textContent = '×';
    const rotulo = el('span', 'sr-only');
    rotulo.textContent = rotuloFechar;
    fechar.append(x, rotulo);

    toast.append(icone, corpo, fechar);

    return toast;
}

export function mostrarToast(opcoes, doc = document) {
    const toast = criarToast(opcoes, doc);
    regiaoDeToasts(doc).appendChild(toast);
    iniciarToast(toast);

    return toast;
}
