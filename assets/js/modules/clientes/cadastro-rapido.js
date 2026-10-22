// Cadastro rápido de cliente em modal (#3003), nas telas da OS e da venda.
//
// A view (views/clientes/partes/cadastro-rapido.php) põe este módulo num
// wrapper em volta do <dialog id="cliente-rapido"> e do <form> oculto a que
// os campos do modal se ligam por form="...".
//
// Fluxo:
// - O rodapé do autocomplete de cliente (data-autocomplete-novo-modal) dispara
//   "autocomplete-novo" no seu wrapper e abre o modal. O formulário é limpo e
//   o texto digitado entra no nome, ou no CPF/CNPJ se forem só números.
// - Ao enviar, valida com as regras do HTML, faz o POST (lib/http.js) e:
//   422 mostra o erro de cada campo; sucesso fecha o modal, mostra o toast e
//   dispara "autocomplete-novo-criado" ({item}) no wrapper de origem, que
//   escolhe o cliente no campo da tela sem perder o que já foi preenchido.
// - Busca de CEP e de CNPJ são as do formulário de cliente (ligarBuscas).

import { post as postPadrao } from '../../lib/http.js';
import { marcarCarregando, mensagemDoCampo, mostrarErroDoCampo } from '../../lib/formulario.js';
import { somenteDigitos } from '../../lib/mascaras.js';
import { mostrarToast } from '../../lib/toast.js';
import { aplicarMascara } from '../formulario/padrao.js';
import { ligarBuscas } from './formulario.js';

/** Campo em que o texto digitado no autocomplete entra: só números é documento, o resto é nome. */
export function campoDoTexto(texto) {
    const limpo = String(texto ?? '').trim();
    if (limpo === '') {
        return null;
    }

    return /^[\d.\-/\s]+$/.test(limpo) && somenteDigitos(limpo).length >= 3 ? 'documento' : 'nomeCliente';
}

/** Erros por campo de uma resposta 422, ou {} para qualquer outro corpo. */
export function errosDaResposta(corpo) {
    const erros = corpo && typeof corpo === 'object' ? corpo.erros : null;

    return erros && typeof erros === 'object' && !Array.isArray(erros) ? erros : {};
}

/** Mensagem do toast quando o erro não é de campo (resposta que não é JSON, como a 403 do CSRF vencido). */
export function mensagemDeErro(erro, erros = {}) {
    if (erro?.corpo === null || typeof erro?.corpo !== 'object') {
        return erro?.status === 403
            ? 'A página ficou muito tempo aberta e expirou. Recarregue e tente de novo.'
            : 'Não foi possível cadastrar agora. Tente de novo.';
    }

    return String(erros._geral ?? erro.corpo.message ?? '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim()
        || 'Não foi possível cadastrar agora. Tente de novo.';
}

const camposDoFormulario = (formulario) => [...formulario.elements].filter((campo) => campo.willValidate && campo.type !== 'hidden' && campo.id);

export default function iniciar(raiz, { post = postPadrao, doc = document } = {}) {
    const formulario = raiz.querySelector('form');
    const dialogo = raiz.querySelector('dialog');
    if (!formulario || !dialogo) {
        return;
    }

    const botao = raiz.querySelector(`[type=submit][form="${formulario.id}"]`);
    const buscas = ligarBuscas(formulario, raiz);
    let origem = null;

    for (const campo of raiz.querySelectorAll('[data-mascara]')) {
        aplicarMascara(campo);
        campo.addEventListener('input', (evento) => aplicarMascara(campo, evento));
    }

    // Só o rodapé do autocomplete que aponta para este modal, não qualquer autocomplete.
    doc.addEventListener('autocomplete-novo', (evento) => {
        if (evento.target?.dataset?.autocompleteNovoModal !== dialogo.id) {
            return;
        }

        origem = evento.target;
        for (const campo of camposDoFormulario(formulario)) {
            mostrarErroDoCampo(campo, '', doc);
        }
        formulario.reset();
        buscas.reiniciar();

        const alvo = campoDoTexto(evento.detail?.texto);
        if (alvo) {
            const campo = formulario.elements.namedItem(alvo);
            campo.value = evento.detail.texto.trim();
            aplicarMascara(campo);
        }
        // O foco vai para o campo que o texto digitado não preencheu.
        formulario.elements.namedItem(alvo === 'nomeCliente' ? 'documento' : 'nomeCliente')?.focus();
    });

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        let primeiro = null;
        for (const campo of camposDoFormulario(formulario)) {
            const mensagem = mensagemDoCampo(campo);
            mostrarErroDoCampo(campo, mensagem, doc);
            primeiro ??= mensagem ? campo : null;
        }
        if (primeiro) {
            primeiro.focus();
            return;
        }

        marcarCarregando(botao, true);
        try {
            const resposta = await post(formulario.action, new FormData(formulario));

            dialogo.close();
            mostrarToast({ variante: 'success', mensagem: resposta.message || 'Cliente cadastrado.' });
            origem?.dispatchEvent(new CustomEvent('autocomplete-novo-criado', { bubbles: true, detail: { item: resposta.cliente } }));
        } catch (erro) {
            const erros = errosDaResposta(erro?.corpo);
            let focado = null;
            for (const [nome, mensagem] of Object.entries(erros)) {
                const campo = nome === '_geral' ? null : formulario.elements.namedItem(nome);
                const alvo = campo instanceof RadioNodeList ? campo[0] : campo;
                if (alvo?.id) {
                    mostrarErroDoCampo(alvo, String(mensagem), doc);
                    focado ??= alvo;
                }
            }

            if (focado) {
                focado.focus();
            } else {
                mostrarToast({ variante: 'danger', mensagem: mensagemDeErro(erro, erros) });
            }
        } finally {
            marcarCarregando(botao, false);
        }
    });
}
