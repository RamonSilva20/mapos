// fetch padronizado para o painel.
//
// - Manda X-Requested-With, para o CodeIgniter tratar como AJAX: negação de
//   permissão volta como JSON 403 (MY_Controller, #2866) e não como redirect.
// - Em POST e afins, acrescenta o token do CSRF lido do cookie, cujo nome vem
//   das metas csrf-token-name e csrf-cookie-name do layout.
// - Como o token é regenerado a cada requisição, atualiza os campos ocultos
//   dos formulários da página depois da resposta, como o csrf.js legado faz
//   para o jQuery.

export class ErroHttp extends Error {
    constructor(status, mensagem, corpo = null) {
        super(mensagem);
        this.name = 'ErroHttp';
        this.status = status;
        this.corpo = corpo;
    }
}

function meta(nome, raiz) {
    return raiz.querySelector(`meta[name="${nome}"]`)?.content ?? null;
}

export function lerCookie(nome, cookies) {
    for (const parte of cookies.split(';')) {
        const [chave, ...resto] = parte.trim().split('=');
        if (chave === nome) {
            return decodeURIComponent(resto.join('='));
        }
    }

    return null;
}

export function tokenCsrf(raiz = document) {
    const campo = meta('csrf-token-name', raiz);
    const cookie = meta('csrf-cookie-name', raiz);

    if (!campo || !cookie) {
        return null;
    }

    const valor = lerCookie(cookie, raiz.cookie ?? '');

    return valor === null ? null : { campo, valor };
}

/**
 * Monta o corpo da requisição a partir de FormData, URLSearchParams ou objeto
 * simples, com o token do CSRF quando houver.
 */
export function corpoComCsrf(dados, csrf) {
    const corpo = dados instanceof FormData || dados instanceof URLSearchParams
        ? dados
        : new URLSearchParams(Object.entries(dados ?? {}).map(([k, v]) => [k, String(v)]));

    if (csrf) {
        corpo.set(csrf.campo, csrf.valor);
    }

    return corpo;
}

function atualizarCamposCsrf(raiz = document) {
    const csrf = tokenCsrf(raiz);
    if (!csrf) return;

    raiz.querySelectorAll(`input[name="${csrf.campo}"]`).forEach((campo) => {
        campo.value = csrf.valor;
    });
}

/**
 * Faz a requisição e devolve o JSON (ou o texto, se a resposta não for JSON).
 * Lança ErroHttp para status >= 400, com a mensagem do servidor quando vier.
 */
export async function requisicao(url, { metodo = 'GET', dados = null, headers = {} } = {}) {
    const opcoes = {
        method: metodo,
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json', ...headers },
    };

    if (metodo !== 'GET' && metodo !== 'HEAD') {
        opcoes.body = corpoComCsrf(dados, tokenCsrf());
    }

    const resposta = await fetch(url, opcoes);
    atualizarCamposCsrf();

    const ehJson = (resposta.headers.get('Content-Type') ?? '').includes('application/json');
    const corpo = ehJson ? await resposta.json() : await resposta.text();

    if (!resposta.ok) {
        const mensagem = (ehJson && corpo?.message) || `Erro ${resposta.status}`;
        throw new ErroHttp(resposta.status, mensagem, corpo);
    }

    return corpo;
}

export const get = (url, opcoes = {}) => requisicao(url, { ...opcoes, metodo: 'GET' });
export const post = (url, dados, opcoes = {}) => requisicao(url, { ...opcoes, metodo: 'POST', dados });
