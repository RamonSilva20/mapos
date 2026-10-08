// Controles do catálogo de componentes (views/componentes/catalogo.php):
// modo claro/escuro/sistema, cor de destaque e demonstração de toast.
//
// Só mexe no <html> da página do catálogo, sem gravar nada: a preferência
// real do sistema continua em Configurações.

import { mostrarToast } from '../../lib/toast.js';
import { aplicarModo } from '../../lib/tema.js';

export { aplicarModo };

export default function iniciar(barra) {
    const html = document.documentElement;
    const consulta = window.matchMedia?.('(prefers-color-scheme: dark)');
    const prefereEscuro = () => Boolean(consulta?.matches);

    const marcar = () => {
        for (const botao of barra.querySelectorAll('[data-tema-modo]')) {
            botao.setAttribute('aria-pressed', String(botao.dataset.temaModo === html.dataset.temaModo));
        }
    };

    barra.addEventListener('click', (evento) => {
        const modo = evento.target.closest('[data-tema-modo]');
        if (modo) {
            aplicarModo(html, modo.dataset.temaModo, prefereEscuro());
            marcar();
        }

        const toast = evento.target.closest('[data-toast-variante]');
        if (toast) {
            const variante = toast.dataset.toastVariante;
            mostrarToast({
                variante,
                titulo: 'Toast de exemplo',
                mensagem: `Variante ${variante}, criado por mostrarToast(). Some em 5 segundos.`,
            });
        }
    });

    const destaque = barra.querySelector('[data-trocar-destaque]');
    destaque?.addEventListener('change', () => {
        html.dataset.accent = destaque.value;
    });

    consulta?.addEventListener?.('change', () => {
        if (html.dataset.temaModo === 'sistema') {
            aplicarModo(html, 'sistema', prefereEscuro());
        }
    });

    marcar();
}
