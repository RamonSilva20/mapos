// Formulário de login do painel (views/mapos/login.php) e da área do cliente
// (views/conecte/login.php), que respondem no mesmo contrato.
//
// Valida no navegador, posta em login/verificarLogin pelo post() de
// lib/http.js e mostra o erro no alert da própria página, sem modal. O
// contrato do servidor é o mesmo da tela antiga: {result, message,
// MAPOS_TOKEN}. A resposta sai sem Content-Type JSON, então chega como texto e
// é interpretada aqui.

import { ErroHttp, post } from '../../lib/http.js';
import { criarIcone } from '../../lib/icone.js';

const EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export const MENSAGEM_PADRAO = 'Os dados de acesso estão incorretos, por favor tente novamente!';
export const MENSAGEM_SESSAO = 'Sua sessão expirou. Recarregue a página e tente de novo.';
export const MENSAGEM_REDE = 'Não foi possível falar com o servidor. Verifique a conexão e tente de novo.';

/**
 * Erros de validação por campo. Mensagens vazias significam campo válido.
 *
 * @param {{email: string, senha: string}} valores
 * @param {{emailVazio?: string, emailInvalido?: string, senhaVazia?: string}} textos
 */
export function validar({ email, senha }, textos = {}) {
    const erros = {};
    const emailLimpo = String(email ?? '').trim();

    if (emailLimpo === '') {
        erros.email = textos.emailVazio ?? 'Informe o e-mail.';
    } else if (!EMAIL.test(emailLimpo)) {
        erros.email = textos.emailInvalido ?? 'Informe um e-mail válido.';
    }

    if (String(senha ?? '') === '') {
        erros.senha = textos.senhaVazia ?? 'Informe a senha.';
    }

    return erros;
}

/**
 * Normaliza a resposta do verificarLogin, que pode chegar como texto JSON.
 *
 * @returns {{ok: boolean, mensagem: string, token: string|null}}
 */
export function interpretarResposta(corpo) {
    let dados = corpo;

    if (typeof corpo === 'string') {
        try {
            dados = JSON.parse(corpo);
        } catch {
            return { ok: false, mensagem: MENSAGEM_PADRAO, token: null };
        }
    }

    if (!dados || typeof dados !== 'object') {
        return { ok: false, mensagem: MENSAGEM_PADRAO, token: null };
    }

    const ok = dados.result === true;
    // A validação do servidor manda HTML do validation_errors(); só o texto
    // interessa, e ele é exibido com textContent.
    const mensagem = ok ? '' : textoSimples(dados.message) || MENSAGEM_PADRAO;

    return { ok, mensagem, token: typeof dados.MAPOS_TOKEN === 'string' ? dados.MAPOS_TOKEN : null };
}

export function textoSimples(valor) {
    return String(valor ?? '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
}

/**
 * Mensagem para uma falha de requisição. 403 aqui é o CSRF recusado (token
 * vencido, por exemplo depois de muito tempo com a página aberta).
 */
export function mensagemDeFalha(erro) {
    if (erro instanceof ErroHttp) {
        return erro.status === 403 ? MENSAGEM_SESSAO : MENSAGEM_PADRAO;
    }

    return MENSAGEM_REDE;
}

function mostrarErroDoCampo(campo, mensagem) {
    const idErro = `${campo.id}-erro-js`;
    let aviso = document.getElementById(idErro);
    const descritores = (campo.getAttribute('aria-describedby') ?? '').split(' ').filter((id) => id && id !== idErro);

    if (!mensagem) {
        aviso?.remove();
        campo.removeAttribute('aria-invalid');
        campo.classList.remove('border-danger');
        campo.classList.add('border-input');
        descritores.length ? campo.setAttribute('aria-describedby', descritores.join(' ')) : campo.removeAttribute('aria-describedby');
        return;
    }

    if (!aviso) {
        // Mesma marcação do erro do componente input (ícone + texto).
        aviso = document.createElement('p');
        aviso.id = idErro;
        aviso.className = 'flex items-start gap-1.5 text-caption text-danger-ink';
        aviso.append(criarIcone('circle-alert', 'mt-0.5 size-4'), document.createElement('span'));
        campo.insertAdjacentElement('afterend', aviso);
    }

    aviso.querySelector('span').textContent = mensagem;
    campo.setAttribute('aria-invalid', 'true');
    campo.classList.remove('border-input');
    campo.classList.add('border-danger');
    campo.setAttribute('aria-describedby', [...descritores, idErro].join(' '));
}

export default function iniciar(formulario) {
    const email = formulario.querySelector('[name="email"]');
    const senha = formulario.querySelector('[name="senha"]');
    const botao = formulario.querySelector('[type="submit"]');
    const caixa = formulario.querySelector('[data-login-mensagem]');
    const texto = formulario.querySelector('[data-login-texto]');
    const rotulo = botao?.querySelector('span:not(.sr-only)') ?? botao?.querySelector('span');
    const rotuloOriginal = rotulo?.textContent ?? '';

    const textos = {
        emailVazio: email?.dataset.msgVazio,
        emailInvalido: email?.dataset.msgInvalido,
        senhaVazia: senha?.dataset.msgVazio,
    };

    function mostrarMensagem(mensagem) {
        if (!caixa || !texto) return;
        texto.textContent = mensagem;
        caixa.hidden = !mensagem;
    }

    function carregando(ativo) {
        if (!botao) return;
        botao.disabled = ativo;
        botao.setAttribute('aria-busy', ativo ? 'true' : 'false');
        if (rotulo) rotulo.textContent = ativo ? (botao.dataset.rotuloCarregando || rotuloOriginal) : rotuloOriginal;
    }

    [email, senha].forEach((campo) => {
        campo?.addEventListener('input', () => {
            if (campo.getAttribute('aria-invalid') === 'true') {
                mostrarErroDoCampo(campo, validar({ email: email.value, senha: senha.value }, textos)[campo.name]);
            }
        });
    });

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const erros = validar({ email: email.value, senha: senha.value }, textos);
        mostrarErroDoCampo(email, erros.email);
        mostrarErroDoCampo(senha, erros.senha);

        if (erros.email || erros.senha) {
            (erros.email ? email : senha).focus();
            return;
        }

        mostrarMensagem('');
        carregando(true);

        try {
            const resposta = interpretarResposta(await post(formulario.action, new FormData(formulario)));

            if (resposta.ok) {
                window.location.assign(formulario.dataset.destino);
                return;
            }

            // O post() já atualiza o campo pelo cookie; o MAPOS_TOKEN da resposta
            // cobre o caso de o cookie não ser legível.
            const nomeCsrf = document.querySelector('meta[name="csrf-token-name"]')?.content;
            const campoCsrf = nomeCsrf ? formulario.querySelector(`input[name="${CSS.escape(nomeCsrf)}"]`) : null;
            if (resposta.token && campoCsrf) {
                campoCsrf.value = resposta.token;
            }

            mostrarMensagem(resposta.mensagem);
            senha.value = '';
            senha.focus();
        } catch (erro) {
            mostrarMensagem(mensagemDeFalha(erro));
        }

        carregando(false);
    });
}
