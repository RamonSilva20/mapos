// Padrões de formulário da v5 (#2851): erro de campo, mensagem a partir da
// validação nativa do navegador e estado de carregamento do botão.
//
// O erro tem a mesma marcação do componente input (application/views/
// components/input.php): <p id="<id>-erro"> com ícone e texto, ligado ao campo
// por aria-describedby, aria-invalid no campo e borda border-danger. Se o
// servidor já renderizou o erro, o mesmo elemento é reaproveitado.

import { criarIcone } from './icone.js';

export const MENSAGENS = {
    vazio: 'Preencha este campo.',
    email: 'Informe um e-mail válido.',
    invalido: 'Confira o valor deste campo.',
    longo: 'Texto maior que o permitido.',
};

/**
 * Mensagem de erro do campo pela validação nativa (required, type, pattern,
 * maxlength), com os textos de data-msg-vazio e data-msg-invalido quando a
 * view os define. Campo válido devolve ''.
 */
export function mensagemDoCampo(campo) {
    const v = campo.validity;
    if (!v || v.valid) {
        return '';
    }

    if (v.valueMissing) {
        return campo.dataset.msgVazio || MENSAGENS.vazio;
    }
    if (v.typeMismatch && campo.type === 'email') {
        return campo.dataset.msgInvalido || MENSAGENS.email;
    }
    if (v.tooLong) {
        return campo.dataset.msgInvalido || MENSAGENS.longo;
    }

    return campo.dataset.msgInvalido || MENSAGENS.invalido;
}

/** Ids de aria-describedby, sem o informado. */
export function semDescritor(atual, id) {
    return String(atual ?? '').split(' ').filter((item) => item && item !== id);
}

export function mostrarErroDoCampo(campo, mensagem, doc = document) {
    const idErro = `${campo.id}-erro`;
    let aviso = doc.getElementById(idErro);
    const descritores = semDescritor(campo.getAttribute('aria-describedby'), idErro);

    if (!mensagem) {
        aviso?.remove();
        campo.removeAttribute('aria-invalid');
        campo.classList.remove('border-danger');
        campo.classList.add('border-input');
        descritores.length ? campo.setAttribute('aria-describedby', descritores.join(' ')) : campo.removeAttribute('aria-describedby');
        return;
    }

    if (!aviso) {
        aviso = doc.createElement('p');
        aviso.id = idErro;
        aviso.className = 'flex items-start gap-1.5 text-caption text-danger-ink';
        aviso.append(criarIcone('circle-alert', 'mt-0.5 size-4', doc), doc.createElement('span'));
        // Mesma ordem do componente: campo, ajuda, erro.
        const ajuda = doc.getElementById(`${campo.id}-ajuda`);
        (ajuda ?? campo).insertAdjacentElement('afterend', aviso);
    }

    aviso.querySelector('span').textContent = mensagem;
    campo.setAttribute('aria-invalid', 'true');
    campo.classList.remove('border-input');
    campo.classList.add('border-danger');
    campo.setAttribute('aria-describedby', [...descritores, idErro].join(' '));
}

/**
 * Botão de envio em carregamento: desabilitado, aria-busy e o rótulo trocado
 * por data-rotulo-carregando (ex. "Salvando…").
 */
export function marcarCarregando(botao, ativo) {
    if (!botao) {
        return;
    }

    const rotulo = botao.querySelector('span:not(.sr-only)') ?? botao.querySelector('span');
    if (rotulo && botao.dataset.rotuloOriginal === undefined) {
        botao.dataset.rotuloOriginal = rotulo.textContent;
    }

    botao.disabled = ativo;
    botao.setAttribute('aria-busy', ativo ? 'true' : 'false');
    if (rotulo) {
        rotulo.textContent = ativo ? (botao.dataset.rotuloCarregando || botao.dataset.rotuloOriginal) : botao.dataset.rotuloOriginal;
    }
}
