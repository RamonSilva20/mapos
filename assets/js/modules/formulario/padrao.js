// Padrão dos formulários da v5 (#2851). Na view:
//
//     <form method="post" novalidate <?= js_module('formulario/padrao') ?>>
//
// - Valida no navegador com as regras do próprio HTML (required, type,
//   pattern, maxlength) antes de enviar; o erro aparece no campo, com a mesma
//   marcação do erro que o servidor renderiza (lib/formulario.js), e o foco vai
//   para o primeiro campo com erro. O servidor valida de novo sempre.
// - Depois de um erro, o campo é revalidado enquanto o usuário corrige.
// - Ao enviar, o botão fica em carregamento (data-rotulo-carregando) e não
//   aceita um segundo clique.
// - data-mascara="documento|telefone|cep" formata enquanto digita
//   (lib/mascaras.js).
// - Um botão com data-mostrar-senha="<id do campo>" alterna a senha visível.
//
// O envio continua sendo um POST comum: o servidor responde com a tela e os
// erros, ou redireciona com o toast de sucesso.

import { marcarCarregando, mensagemDoCampo, mostrarErroDoCampo } from '../../lib/formulario.js';
import { MASCARAS } from '../../lib/mascaras.js';

const CAMPOS = 'input:not([type=hidden]):not([type=button]):not([type=submit]), select, textarea';

export function aplicarMascara(campo) {
    const formatar = MASCARAS[campo.dataset.mascara];
    if (!formatar) {
        return;
    }

    const formatado = formatar(campo.value);
    if (formatado !== campo.value) {
        campo.value = formatado;
    }
}

export default function iniciar(formulario) {
    formulario.noValidate = true;

    for (const campo of formulario.querySelectorAll('[data-mascara]')) {
        aplicarMascara(campo);
        campo.addEventListener('input', () => aplicarMascara(campo));
    }

    formulario.addEventListener('input', (evento) => {
        const campo = evento.target;
        if (campo.matches?.(CAMPOS) && campo.getAttribute('aria-invalid') === 'true') {
            mostrarErroDoCampo(campo, mensagemDoCampo(campo));
        }
    });

    formulario.addEventListener('click', (evento) => {
        const botao = evento.target.closest?.('[data-mostrar-senha]');
        if (!botao) {
            return;
        }

        const campo = formulario.querySelector(`#${CSS.escape(botao.dataset.mostrarSenha)}`);
        if (!campo) {
            return;
        }

        const mostrar = campo.type === 'password';
        campo.type = mostrar ? 'text' : 'password';
        botao.setAttribute('aria-pressed', String(mostrar));
    });

    formulario.addEventListener('submit', (evento) => {
        let primeiro = null;

        for (const campo of formulario.querySelectorAll(CAMPOS)) {
            if (campo.disabled) {
                continue;
            }
            const mensagem = mensagemDoCampo(campo);
            mostrarErroDoCampo(campo, mensagem);
            if (mensagem && !primeiro) {
                primeiro = campo;
            }
        }

        if (primeiro) {
            evento.preventDefault();
            primeiro.focus();
            return;
        }

        marcarCarregando(evento.submitter ?? formulario.querySelector('[type=submit]'), true);
    });

    // Voltar pelo histórico (bfcache) não pode deixar o botão travado.
    window.addEventListener('pageshow', () => {
        for (const botao of formulario.querySelectorAll('[type=submit][aria-busy=true]')) {
            marcarCarregando(botao, false);
        }
    });
}
