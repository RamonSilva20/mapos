// Autocomplete da v5 (#2842): combobox acessível (padrão ARIA 1.2, "combobox
// com listbox") sobre um <input> visível, que mostra o texto escolhido, e um
// <input type=hidden>, que guarda o id.
//
// Na view (ver views/os/formulario.php):
//
//     <div class="relative" data-autocomplete="<url>" data-autocomplete-alvo="<id do oculto>"
//          data-autocomplete-min="2" data-autocomplete-novo="<url de cadastro>"
//          data-autocomplete-novo-rotulo="Cadastrar novo cliente">
//         <?= component('input', [...]) ?>
//         <input type="hidden" id="<id do oculto>" name="..." value="...">
//     </div>
//
// - A URL recebe ?term=<texto> e devolve uma lista JSON de itens com id e
//   label; valor (o texto que fica no campo) e detalhe (linha secundária) são
//   opcionais e, sem eles, saem do label "valor | detalhe | ...".
// - Teclado: ↓/↑ percorrem as opções, Enter escolhe, Esc fecha.
// - Digitar sem escolher da lista apaga o id e deixa o campo inválido
//   (setCustomValidity), com o texto de data-msg-invalido: o envio é barrado
//   pelo módulo formulario/padrao como qualquer outro erro de campo.
// - O rodapé opcional (data-autocomplete-novo) abre o cadastro numa nova aba,
//   para não perder o que já foi preenchido.

import { mensagemDoCampo, mostrarErroDoCampo } from './formulario.js';
import { get as getPadrao } from './http.js';
import { criarIcone } from './icone.js';

export const CLASSES_POPUP = 'absolute inset-x-0 z-30 mt-1 hidden overflow-hidden rounded-sm border border-border bg-surface shadow-overlay';
export const CLASSES_LISTA = 'max-h-64 overflow-y-auto py-1';
export const CLASSES_OPCAO = 'flex cursor-pointer flex-col gap-0.5 px-3 py-2 text-body-md text-text aria-selected:bg-surface-subtle';
export const CLASSES_AVISO = 'px-3 py-2 text-caption text-muted';
export const CLASSES_RODAPE = 'flex items-center gap-2 border-t border-border px-3 py-2 text-label-md text-primary-strong underline hover:bg-surface-subtle';

/**
 * Itens da resposta do servidor no formato do combobox: {id, valor, detalhe}.
 * Itens sem id são descartados; sem valor/detalhe, o label "a | b | c" vira
 * valor "a" e detalhe "b · c".
 */
export function normalizarItens(dados) {
    if (!Array.isArray(dados)) {
        return [];
    }

    return dados
        .filter((item) => item && item.id !== undefined && item.id !== null && String(item.id) !== '')
        .map((item) => {
            const partes = String(item.label ?? '').split(' | ').map((parte) => parte.trim()).filter(Boolean);
            return {
                id: String(item.id),
                valor: String(item.valor ?? partes[0] ?? ''),
                detalhe: String(item.detalhe ?? partes.slice(1).join(' · ')),
            };
        });
}

/** Próxima opção ativa ao navegar com as setas, dando a volta na lista. -1 = nenhuma. */
export function proximoIndice(atual, total, passo) {
    if (total <= 0) {
        return -1;
    }
    if (atual < 0) {
        return passo > 0 ? 0 : total - 1;
    }

    return (atual + passo + total) % total;
}

/** URL da busca com o termo na query string. */
export function urlDeBusca(base, termo) {
    return `${base}${base.includes('?') ? '&' : '?'}term=${encodeURIComponent(termo)}`;
}

/**
 * Situação do campo: 'vazio' (nada digitado), 'escolhido' (o texto é o do
 * item escolhido e há id) ou 'pendente' (digitado sem escolher da lista).
 */
export function situacao(texto, id, valorEscolhido) {
    const digitado = String(texto ?? '').trim();
    if (digitado === '') {
        return 'vazio';
    }

    return id && digitado === String(valorEscolhido ?? '').trim() ? 'escolhido' : 'pendente';
}

export function iniciarAutocomplete(wrapper, { buscar = getPadrao, atraso = 250, doc = document } = {}) {
    const campo = wrapper.querySelector('input:not([type=hidden])');
    const oculto = doc.getElementById(wrapper.dataset.autocompleteAlvo ?? '');
    if (!campo || !oculto || !wrapper.dataset.autocomplete) {
        return null;
    }

    const minimo = Number(wrapper.dataset.autocompleteMin ?? 2) || 1;
    const idLista = `${campo.id}-opcoes`;

    const popup = doc.createElement('div');
    popup.className = CLASSES_POPUP;
    const lista = doc.createElement('ul');
    lista.id = idLista;
    lista.className = CLASSES_LISTA;
    lista.setAttribute('role', 'listbox');
    lista.setAttribute('aria-label', campo.labels?.[0]?.textContent?.replace('*', '').trim() || 'Opções');
    popup.append(lista);

    if (wrapper.dataset.autocompleteNovo) {
        const rodape = doc.createElement('a');
        rodape.className = CLASSES_RODAPE;
        rodape.href = wrapper.dataset.autocompleteNovo;
        rodape.target = '_blank';
        rodape.rel = 'noopener';
        rodape.append(criarIcone('plus', 'size-4', doc), doc.createTextNode(wrapper.dataset.autocompleteNovoRotulo || 'Cadastrar novo'));
        // Clicar no link não deve fechar a lista antes de abrir a aba.
        rodape.addEventListener('mousedown', (evento) => evento.preventDefault());
        popup.append(rodape);
    }

    wrapper.append(popup);

    campo.setAttribute('role', 'combobox');
    campo.setAttribute('aria-autocomplete', 'list');
    campo.setAttribute('aria-expanded', 'false');
    campo.setAttribute('aria-controls', idLista);

    let itens = [];
    let ativo = -1;
    let escolhido = oculto.value ? { id: oculto.value, valor: campo.value } : null;
    let espera = null;
    let pedido = 0;

    const aberto = () => !popup.classList.contains('hidden');

    function posicionar() {
        popup.style.top = `${campo.offsetTop + campo.offsetHeight}px`;
    }

    function abrir() {
        posicionar();
        popup.classList.remove('hidden');
        campo.setAttribute('aria-expanded', 'true');
    }

    function fechar() {
        popup.classList.add('hidden');
        campo.setAttribute('aria-expanded', 'false');
        campo.removeAttribute('aria-activedescendant');
        ativo = -1;
    }

    function marcarAtivo(indice) {
        ativo = indice;
        lista.querySelectorAll('[role=option]').forEach((opcao, i) => {
            opcao.setAttribute('aria-selected', String(i === indice));
            if (i === indice) {
                campo.setAttribute('aria-activedescendant', opcao.id);
                opcao.scrollIntoView?.({ block: 'nearest' });
            }
        });
        if (indice < 0) {
            campo.removeAttribute('aria-activedescendant');
        }
    }

    function aviso(texto) {
        const item = doc.createElement('li');
        item.className = CLASSES_AVISO;
        item.setAttribute('role', 'presentation');
        item.textContent = texto;
        lista.replaceChildren(item);
    }

    function renderizar() {
        if (itens.length === 0) {
            aviso('Nenhum resultado. Confira o que foi digitado.');
            return;
        }

        lista.replaceChildren(...itens.map((item, i) => {
            const opcao = doc.createElement('li');
            opcao.id = `${idLista}-${i}`;
            opcao.className = CLASSES_OPCAO;
            opcao.setAttribute('role', 'option');
            opcao.setAttribute('aria-selected', 'false');
            const valor = doc.createElement('span');
            valor.textContent = item.valor;
            opcao.append(valor);
            if (item.detalhe) {
                const detalhe = doc.createElement('span');
                detalhe.className = 'text-caption text-muted';
                detalhe.textContent = item.detalhe;
                opcao.append(detalhe);
            }
            // mousedown, e não click: o blur do campo fecharia a lista antes.
            opcao.addEventListener('mousedown', (evento) => {
                evento.preventDefault();
                escolher(item);
            });
            return opcao;
        }));
    }

    function validar() {
        const estado = situacao(campo.value, oculto.value, escolhido?.valor);
        campo.setCustomValidity(estado === 'pendente' ? (campo.dataset.msgInvalido || 'Escolha uma opção da lista.') : '');
        if (campo.getAttribute('aria-invalid') === 'true') {
            mostrarErroDoCampo(campo, mensagemDoCampo(campo), doc);
        }
    }

    function escolher(item) {
        escolhido = item;
        campo.value = item.valor;
        oculto.value = item.id;
        oculto.dispatchEvent(new Event('change', { bubbles: true }));
        fechar();
        validar();
    }

    async function pesquisar(termo) {
        const numero = ++pedido;
        aviso('Buscando…');
        abrir();
        try {
            const resposta = await buscar(urlDeBusca(wrapper.dataset.autocomplete, termo));
            if (numero !== pedido) {
                return;
            }
            itens = normalizarItens(resposta);
            renderizar();
            marcarAtivo(-1);
        } catch {
            if (numero === pedido) {
                itens = [];
                aviso('Não foi possível buscar agora. Tente de novo.');
            }
        }
    }

    campo.addEventListener('input', () => {
        if (escolhido && campo.value.trim() !== escolhido.valor.trim()) {
            escolhido = null;
            oculto.value = '';
            oculto.dispatchEvent(new Event('change', { bubbles: true }));
        }
        validar();

        clearTimeout(espera);
        const termo = campo.value.trim();
        if (termo.length < minimo) {
            pedido++;
            fechar();
            return;
        }
        espera = setTimeout(() => pesquisar(termo), atraso);
    });

    campo.addEventListener('keydown', (evento) => {
        if (evento.key === 'ArrowDown' || evento.key === 'ArrowUp') {
            evento.preventDefault();
            if (!aberto()) {
                if (campo.value.trim().length >= minimo) {
                    pesquisar(campo.value.trim());
                }
                return;
            }
            marcarAtivo(proximoIndice(ativo, itens.length, evento.key === 'ArrowDown' ? 1 : -1));
        } else if (evento.key === 'Enter' && aberto() && ativo >= 0) {
            evento.preventDefault();
            escolher(itens[ativo]);
        } else if (evento.key === 'Escape' && aberto()) {
            evento.preventDefault();
            evento.stopPropagation();
            fechar();
        }
    });

    campo.addEventListener('blur', () => {
        fechar();
        validar();
    });

    // Volta com erro do servidor: o texto e o id já vêm preenchidos.
    validar();

    return { escolher, fechar, pesquisar };
}

/** Liga todos os autocompletes ([data-autocomplete]) dentro de raiz. */
export function iniciarAutocompletes(raiz, opcoes = {}) {
    return [...raiz.querySelectorAll('[data-autocomplete]')].map((wrapper) => iniciarAutocomplete(wrapper, opcoes)).filter(Boolean);
}
