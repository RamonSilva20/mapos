// Botão de copiar da área do cliente (views/conecte/partes/pix.php, #2848):
// data-copiar="<id do campo>" copia o valor do campo, como o PIX copia e cola
// da tela da OS no painel (modules/os/tela.js). Sem permissão de acesso à área
// de transferência, o código fica selecionado para o Ctrl+C.

import { mostrarToast } from '../../lib/toast.js';

export async function copiar(campo, area = globalThis.navigator?.clipboard) {
    try {
        await area.writeText(campo.value);
        return true;
    } catch {
        campo.select();
        return false;
    }
}

export default function iniciar(raiz) {
    const doc = raiz.ownerDocument ?? document;

    raiz.addEventListener('click', async (evento) => {
        const botao = evento.target.closest?.('[data-copiar]');
        const campo = botao ? doc.getElementById(botao.dataset.copiar) : null;
        if (!campo) {
            return;
        }

        const copiou = await copiar(campo);
        mostrarToast(copiou
            ? { mensagem: 'Código PIX copiado.', variante: 'success' }
            : { mensagem: 'Não foi possível copiar sozinho: o código ficou selecionado, use Ctrl+C.', variante: 'warning' }, doc);
    });
}
