// Paleta de busca global (application/views/tema/busca.php, #2850).
//
// Consulta mapos/buscaRapida com atraso de 200 ms enquanto o usuário digita,
// cancela a consulta anterior e mostra os resultados por grupo. Tudo que vem
// do servidor entra por textContent: nunca como HTML.
//
// Teclado: ↑ ↓ percorrem os resultados (aria-activedescendant, o foco fica no
// campo), Enter abre o escolhido ou, sem escolha, vai para a página de
// resultados completa, e Esc fecha (nativo do <dialog>).
import { get } from '../../lib/http.js';

export const MINIMO_LETRAS = 2;
export const ATRASO_MS = 200;

/** Próximo índice ativo; -1 = nenhum. ↓ no último volta ao primeiro, ↑ no primeiro vai ao último. */
export function proximoIndice(atual, total, tecla) {
    if (total <= 0) {
        return -1;
    }

    if (tecla === 'ArrowDown') {
        return atual < 0 || atual >= total - 1 ? 0 : atual + 1;
    }

    if (tecla === 'ArrowUp') {
        return atual <= 0 ? total - 1 : atual - 1;
    }

    return atual;
}

export function termoValido(termo) {
    return String(termo).trim().length >= MINIMO_LETRAS;
}

function elemento(tag, classes, texto) {
    const el = document.createElement(tag);
    if (classes) el.className = classes;
    if (texto !== undefined) el.textContent = texto;

    return el;
}

export default function iniciar(dialogo) {
    const campo = dialogo.querySelector('[data-busca-campo]');
    const lista = dialogo.querySelector('[data-busca-resultados]');
    const estado = dialogo.querySelector('[data-busca-estado]');
    const todos = dialogo.querySelector('[data-busca-todos]');
    const formulario = dialogo.querySelector('[data-busca-form]');
    const urlBase = todos.getAttribute('href');
    let opcoes = [];
    let ativo = -1;
    let espera = null;
    let controle = null;

    const mostrarEstado = (texto) => {
        estado.textContent = texto;
        estado.hidden = texto === '';
    };

    const marcar = (indice) => {
        ativo = indice;
        opcoes.forEach((opcao, i) => {
            const eleito = i === indice;
            opcao.setAttribute('aria-selected', String(eleito));
            opcao.classList.toggle('bg-primary-tint', eleito);
            if (eleito) {
                campo.setAttribute('aria-activedescendant', opcao.id);
                opcao.scrollIntoView?.({ block: 'nearest' });
            }
        });
        if (indice < 0) {
            campo.removeAttribute('aria-activedescendant');
        }
    };

    const limpar = () => {
        lista.replaceChildren();
        opcoes = [];
        marcar(-1);
        campo.setAttribute('aria-expanded', 'false');
    };

    const atualizarTodos = (termo) => {
        todos.href = termoValido(termo) ? `${urlBase}?termo=${encodeURIComponent(termo.trim())}` : urlBase;
    };

    const desenhar = (dados) => {
        limpar();

        let n = 0;
        for (const grupo of dados.grupos) {
            const secao = elemento('div', 'py-1');
            secao.setAttribute('role', 'group');
            const titulo = elemento('p', 'px-4 pt-2 pb-1 text-xs font-medium text-muted', grupo.titulo);
            titulo.id = `v5-busca-g-${grupo.chave}`;
            secao.setAttribute('aria-labelledby', titulo.id);
            secao.append(titulo);

            for (const item of grupo.itens) {
                const link = elemento('a', 'flex flex-col gap-0.5 px-4 py-2 text-text hover:bg-surface-subtle');
                link.href = item.url;
                link.id = `v5-busca-o-${n++}`;
                link.setAttribute('role', 'option');
                link.setAttribute('aria-selected', 'false');
                link.tabIndex = -1;
                link.append(elemento('span', 'text-sm font-medium', item.titulo));
                if (item.detalhe) {
                    link.append(elemento('span', 'text-xs text-muted', item.detalhe));
                }
                secao.append(link);
                opcoes.push(link);
            }
            lista.append(secao);
        }

        campo.setAttribute('aria-expanded', String(opcoes.length > 0));
        mostrarEstado(opcoes.length === 0 ? `Nada encontrado para “${dados.termo}”.` : '');
    };

    const buscar = async (termo) => {
        controle?.abort();
        controle = new AbortController();
        mostrarEstado('Buscando…');

        try {
            const dados = await get(`${dialogo.dataset.url}?termo=${encodeURIComponent(termo.trim())}`, { signal: controle.signal });
            if (campo.value.trim() === dados.termo) {
                desenhar(dados);
            }
        } catch (erro) {
            if (erro?.name !== 'AbortError') {
                limpar();
                mostrarEstado('Não foi possível buscar agora. Tente de novo.');
            }
        }
    };

    campo.addEventListener('input', () => {
        clearTimeout(espera);
        atualizarTodos(campo.value);

        if (!termoValido(campo.value)) {
            controle?.abort();
            limpar();
            mostrarEstado(campo.value.trim() === '' ? `Digite pelo menos ${MINIMO_LETRAS} letras para buscar.` : 'Continue digitando…');
            return;
        }

        espera = setTimeout(() => buscar(campo.value), ATRASO_MS);
    });

    campo.addEventListener('keydown', (evento) => {
        if (evento.key === 'ArrowDown' || evento.key === 'ArrowUp') {
            evento.preventDefault();
            marcar(proximoIndice(ativo, opcoes.length, evento.key));
        }
    });

    formulario.addEventListener('submit', (evento) => {
        if (ativo >= 0 && opcoes[ativo]) {
            evento.preventDefault();
            window.location.href = opcoes[ativo].href;
            return;
        }

        if (!termoValido(campo.value)) {
            evento.preventDefault();
        }
    });

    dialogo.addEventListener('close', () => {
        clearTimeout(espera);
        controle?.abort();
        campo.value = '';
        limpar();
        atualizarTodos('');
        mostrarEstado(`Digite pelo menos ${MINIMO_LETRAS} letras para buscar.`);
    });

    // Clique no fundo (fora da caixa) fecha.
    dialogo.addEventListener('click', (evento) => {
        if (evento.target !== dialogo) {
            return;
        }
        const r = dialogo.getBoundingClientRect();
        if (evento.clientX < r.left || evento.clientX > r.right || evento.clientY < r.top || evento.clientY > r.bottom) {
            dialogo.close();
        }
    });
}
