// Tela da OS (views/os/visualizar.php, #2842).
//
// Os formulários marcados com data-os-acao (adicionar produto, serviço,
// anexo e anotação, desconto, as exclusões confirmadas em modal-confirm,
// faturar e gerar cobrança) são enviados sem recarregar a página, pelo post()
// de lib/http.js (token CSRF, X-Requested-With). O servidor responde JSON:
//
//     {result, message, erros?: {campo: mensagem}, html?: {parte: html}, redirecionar?}
//
// - html: trechos da tela renderizados de novo (views/os/partes/*), que
//   entram no lugar do conteúdo de [data-os-parte="<parte>"];
// - erros: mensagens por campo (o name do campo no formulário), mostradas com
//   a mesma marcação do erro do servidor (lib/formulario.js); _geral vira
//   toast;
// - redirecionar: depois de faturar a tela é recarregada (o status muda).
//
// Também liga o autocomplete de produto/serviço (o preço sugerido entra no
// campo de preço, que continua editável), o botão de copiar o PIX copia e
// cola e o "Já foi recebido" do faturamento.

import { ErroHttp, post as postPadrao } from '../../lib/http.js';
import { mostrarToast } from '../../lib/toast.js';
import { marcarCarregando, mensagemDoCampo, mostrarErroDoCampo } from '../../lib/formulario.js';
import { iniciarAutocompletes } from '../../lib/autocomplete.js';
import { aplicarMascara } from '../formulario/padrao.js';

/** Preço vindo do servidor ("49.9", 49.9) no formato do campo: "49,90". */
export function formatarPreco(valor) {
    const numero = Number(String(valor ?? '').replace(',', '.'));

    return Number.isFinite(numero) && String(valor ?? '').trim() !== '' ? numero.toFixed(2).replace('.', ',') : '';
}

/** Texto sem tags (mensagens de validação do CodeIgniter vêm com <p>). */
export function textoSemHtml(texto) {
    return String(texto ?? '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
}

/** Erros por campo de uma resposta 422, ou {} (inclusive para corpo que não é objeto). */
export function errosDaResposta(corpo) {
    const erros = corpo && typeof corpo === 'object' ? corpo.erros : null;

    return erros && typeof erros === 'object' && !Array.isArray(erros) ? erros : {};
}

/**
 * Mensagem do toast para um erro do servidor. Resposta que não é JSON (ex.: a
 * página 403 do CodeIgniter quando o token CSRF expira) não tem mensagem útil.
 */
export function mensagemDeErro(erro, erros = {}) {
    const corpoJson = erro?.corpo !== null && typeof erro?.corpo === 'object';
    if (!corpoJson) {
        return erro?.status === 403
            ? 'A página ficou muito tempo aberta e expirou. Recarregue e tente de novo.'
            : 'Não foi possível concluir. Tente de novo.';
    }

    return textoSemHtml(erros._geral ?? erro.corpo.message ?? erro.message) || 'Não foi possível concluir. Tente de novo.';
}

/** "Biblioteca|forma" do select de cobrança → {gateway, forma}, ou null. */
export function separarCobranca(valor) {
    const [gateway, forma, ...resto] = String(valor ?? '').split('|');

    return gateway && forma && resto.length === 0 ? { gateway, forma } : null;
}

/** URL da cobrança criada, a partir da URL de cobrancas/adicionar. */
export function urlDaCobranca(acao, id) {
    return String(acao).replace(/\/adicionar\/?$/, `/visualizar/${encodeURIComponent(id)}`);
}

/**
 * Troca o conteúdo de cada [data-os-parte="<parte>"] dentro de raiz pelo HTML
 * que o servidor renderizou (views/os/partes/*, escapado no PHP). Partes que
 * não estão na página (outra aba) são ignoradas. Devolve as partes trocadas.
 */
export function aplicarTrechos(raiz, html, aoTrocar = () => {}) {
    const trocadas = [];

    for (const [parte, conteudo] of Object.entries(html ?? {})) {
        if (!/^[a-z]+$/.test(parte) || typeof conteudo !== 'string') {
            continue;
        }

        raiz.querySelectorAll(`[data-os-parte="${parte}"]`).forEach((alvo) => {
            alvo.innerHTML = conteudo;
            aoTrocar(alvo);
            trocadas.push(parte);
        });
    }

    return trocadas;
}

/** Campos do formulário que o navegador valida (inclui os ligados por form="id"). */
function camposValidaveis(form) {
    return [...form.elements].filter((campo) => campo.willValidate && campo.type !== 'hidden' && campo.id);
}

function limparErros(form, doc) {
    camposValidaveis(form).forEach((campo) => {
        if (campo.getAttribute('aria-invalid') === 'true') {
            mostrarErroDoCampo(campo, '', doc);
        }
    });
}

/** Valida no navegador; mostra os erros e devolve false se houver algum. */
function validar(form, doc) {
    let primeiro = null;

    for (const campo of camposValidaveis(form)) {
        const mensagem = campo.checkValidity() ? '' : mensagemDoCampo(campo);
        mostrarErroDoCampo(campo, mensagem, doc);
        if (mensagem && !primeiro) {
            primeiro = campo;
        }
    }

    primeiro?.focus();

    return primeiro === null;
}

function mostrarErrosDoServidor(form, erros, doc) {
    let primeiro = null;

    for (const [nome, mensagem] of Object.entries(erros)) {
        if (nome === '_geral') {
            continue;
        }

        const campo = form.elements.namedItem(nome);
        const alvo = campo instanceof RadioNodeList ? campo[0] : campo;
        if (alvo && alvo.id) {
            mostrarErroDoCampo(alvo, String(mensagem), doc);
            primeiro ??= alvo;
        }
    }

    primeiro?.focus();

    return primeiro !== null;
}

function botaoDeEnvio(form, evento, doc) {
    return evento.submitter
        ?? form.querySelector('[type=submit]')
        ?? (form.id ? doc.querySelector(`[type=submit][form="${form.id}"]`) : null);
}

export default function iniciar(raiz, { post = postPadrao, doc = document, navegar = (url) => window.location.assign(url) } = {}) {
    iniciarAutocompletes(raiz);

    // Preço com a máscara de dinheiro dos formulários (1.234,56).
    for (const campo of raiz.querySelectorAll('[data-mascara]')) {
        aplicarMascara(campo);
        campo.addEventListener('input', (evento) => aplicarMascara(campo, evento));
    }

    const trocar = (alvo) => {
        // Os trechos novos podem trazer autocompletes (não trazem hoje).
        iniciarAutocompletes(alvo);
    };

    async function enviar(form, evento) {
        const botao = botaoDeEnvio(form, evento, doc);
        const dialogo = botao?.closest('dialog') ?? null;

        if (!validar(form, doc)) {
            return;
        }

        const dados = new FormData(form);
        dados.set('aba', raiz.dataset.osAba ?? 'resumo');

        if (form.dataset.osAcao === 'cobranca') {
            const escolha = separarCobranca(dados.get('cobranca'));
            if (!escolha) {
                return;
            }
            dados.set('gateway_de_pagamento', escolha.gateway);
            dados.set('forma_pagamento', escolha.forma);
            dados.delete('cobranca');
        }

        marcarCarregando(botao, true);

        try {
            const resposta = await post(form.action, dados);

            if (form.dataset.osAcao === 'cobranca' && resposta?.idCobranca) {
                navegar(urlDaCobranca(form.action, resposta.idCobranca));
                return;
            }

            if (resposta?.redirecionar) {
                navegar(resposta.redirecionar);
                return;
            }

            aplicarTrechos(raiz, resposta?.html, trocar);
            dialogo?.close();

            if (form.hasAttribute('data-os-limpar')) {
                form.reset();
                form.querySelectorAll('input[type=hidden][id]').forEach((oculto) => {
                    oculto.value = '';
                });
                raiz.querySelectorAll('[data-os-estoque-texto]').forEach((aviso) => {
                    aviso.textContent = '';
                });
                limparErros(form, doc);
                camposValidaveis(form)[0]?.focus();
            }

            if (resposta?.message) {
                mostrarToast({ mensagem: resposta.message, variante: 'success' }, doc);
            }
        } catch (erro) {
            if (erro instanceof ErroHttp) {
                aplicarTrechos(raiz, erro.corpo?.html, trocar);
                const erros = errosDaResposta(erro.corpo);
                const marcouCampo = erro.status === 422 && mostrarErrosDoServidor(form, erros, doc);
                if (!marcouCampo) {
                    dialogo?.close();
                    mostrarToast({ mensagem: mensagemDeErro(erro, erros), variante: 'danger', duracao: 8000 }, doc);
                }
            } else {
                mostrarToast({ mensagem: 'Sem conexão com o servidor. Confira a internet e tente de novo.', variante: 'danger', duracao: 8000 }, doc);
            }
        } finally {
            marcarCarregando(botao, false);
        }
    }

    // Os formulários de exclusão e de faturamento ficam fora de raiz (junto
    // dos modais), e os trechos trocados recriam os botões: tudo por
    // delegação no documento.
    doc.addEventListener('submit', (evento) => {
        const form = evento.target;
        if (!(form instanceof HTMLFormElement) || !form.dataset.osAcao) {
            return;
        }

        evento.preventDefault();
        enviar(form, evento);
    });

    // Revalida o campo enquanto a pessoa corrige o erro.
    doc.addEventListener('input', (evento) => {
        const campo = evento.target;
        if (campo?.form?.dataset?.osAcao && campo.getAttribute?.('aria-invalid') === 'true') {
            mostrarErroDoCampo(campo, campo.checkValidity() ? '' : mensagemDoCampo(campo), doc);
        }
    });

    // Produto/serviço escolhido no autocomplete: preço sugerido (editável) e
    // estoque disponível.
    raiz.addEventListener('autocomplete-escolha', (evento) => {
        const wrapper = evento.target;
        const dados = evento.detail?.dados ?? {};
        const preco = doc.getElementById(wrapper.dataset.osPreco ?? '');
        if (preco && dados.preco !== undefined) {
            preco.value = formatarPreco(dados.preco);
            aplicarMascara(preco);
            mostrarErroDoCampo(preco, '', doc);
        }

        const aviso = doc.getElementById(wrapper.dataset.osEstoque ?? '');
        if (aviso) {
            aviso.setAttribute('data-os-estoque-texto', '');
            aviso.textContent = dados.estoque !== undefined ? `Em estoque: ${dados.estoque}` : '';
        }
    });

    // Copiar o PIX copia e cola.
    doc.addEventListener('click', async (evento) => {
        const botao = evento.target.closest?.('[data-copiar]');
        if (!botao) {
            return;
        }

        const campo = doc.getElementById(botao.dataset.copiar);
        if (!campo) {
            return;
        }

        try {
            await navigator.clipboard.writeText(campo.value);
            mostrarToast({ mensagem: 'Código PIX copiado.', variante: 'success' }, doc);
        } catch {
            campo.select();
            mostrarToast({ mensagem: 'Não foi possível copiar sozinho: o código ficou selecionado, use Ctrl+C.', variante: 'warning' }, doc);
        }
    });

    // "Já foi recebido" habilita a data do recebimento.
    doc.addEventListener('change', (evento) => {
        const caixa = evento.target;
        const alvo = caixa?.dataset?.osRecebido ? doc.getElementById(caixa.dataset.osRecebido) : null;
        if (alvo) {
            alvo.disabled = !caixa.checked;
            alvo.required = caixa.checked;
            if (!caixa.checked) {
                mostrarErroDoCampo(alvo, '', doc);
            }
        }
    });
}
