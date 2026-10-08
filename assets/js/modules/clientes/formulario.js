// Formulário de cliente (views/clientes/formulario.php, #2851): busca o
// endereço pelo CEP (ViaCEP) e os dados da empresa pelo CNPJ (BrasilAPI).
// As duas APIs mandam CORS e estão no connect-src da CSP.
//
// Os dados só preenchem campos vazios: nada que o usuário digitou é apagado.
// Falha de consulta vira erro no próprio campo (CEP ou CPF/CNPJ), como os
// erros de validação; o sucesso da busca de CNPJ mostra um toast.

import { mostrarErroDoCampo } from '../../lib/formulario.js';
import { formatarCep, formatarTelefone, somenteDigitos } from '../../lib/mascaras.js';
import { mostrarToast } from '../../lib/toast.js';

export const URL_CEP = (cep) => `https://viacep.com.br/ws/${cep}/json/`;
export const URL_CNPJ = (cnpj) => `https://brasilapi.com.br/api/cnpj/v1/${cnpj}`;

const capitalizar = (texto) => String(texto ?? '').toLowerCase().replace(/(^|\s)(\p{L})/gu, (_, espaco, letra) => espaco + letra.toUpperCase());

/** Campos do formulário a partir da resposta do ViaCEP; null se o CEP não existe. */
export function enderecoDoCep(dados) {
    if (!dados || dados.erro) {
        return null;
    }

    return {
        rua: dados.logradouro ?? '',
        complemento: dados.complemento ?? '',
        bairro: dados.bairro ?? '',
        cidade: dados.localidade ?? '',
        estado: dados.uf ?? '',
    };
}

/** Campos do formulário a partir da resposta da BrasilAPI de CNPJ. */
export function dadosDoCnpj(dados) {
    if (!dados || !dados.razao_social) {
        return null;
    }

    const rua = [dados.descricao_tipo_de_logradouro, dados.logradouro].filter(Boolean).join(' ');

    return {
        nomeCliente: capitalizar(dados.razao_social),
        contato: capitalizar(dados.nome_fantasia ?? ''),
        telefone: dados.ddd_telefone_1 ? formatarTelefone(dados.ddd_telefone_1) : '',
        email: String(dados.email ?? '').toLowerCase(),
        cep: dados.cep ? formatarCep(String(dados.cep)) : '',
        rua: capitalizar(rua),
        numero: dados.numero ?? '',
        complemento: capitalizar(dados.complemento ?? ''),
        bairro: capitalizar(dados.bairro ?? ''),
        cidade: capitalizar(dados.municipio ?? ''),
        estado: dados.uf ?? '',
    };
}

/** Preenche só os campos vazios; devolve quantos foram preenchidos. */
export function preencherVazios(formulario, valores) {
    let preenchidos = 0;

    for (const [nome, valor] of Object.entries(valores)) {
        const campo = formulario.elements.namedItem(nome);
        if (campo && valor && !String(campo.value ?? '').trim()) {
            campo.value = valor;
            preenchidos++;
        }
    }

    return preenchidos;
}

async function consultar(url, buscar = fetch) {
    const resposta = await buscar(url, { headers: { Accept: 'application/json' } });
    if (!resposta.ok) {
        return null;
    }

    return resposta.json();
}

// O <form> já leva o módulo formulario/padrao; este fica num wrapper em volta.
export default function iniciar(raiz) {
    const formulario = raiz.matches?.('form') ? raiz : raiz.querySelector('form');
    if (!formulario) {
        return;
    }

    const cep = formulario.elements.namedItem('cep');
    const documento = formulario.elements.namedItem('documento');
    const botaoCnpj = formulario.querySelector('[data-buscar-cnpj]');
    let ultimoCep = somenteDigitos(cep?.value);

    cep?.addEventListener('input', async () => {
        const digitos = somenteDigitos(cep.value);
        if (digitos.length !== 8 || digitos === ultimoCep) {
            return;
        }
        ultimoCep = digitos;

        cep.setAttribute('aria-busy', 'true');
        try {
            const endereco = enderecoDoCep(await consultar(URL_CEP(digitos)));
            if (!endereco) {
                mostrarErroDoCampo(cep, 'CEP não encontrado. Confira ou preencha o endereço à mão.');
                return;
            }

            mostrarErroDoCampo(cep, '');
            preencherVazios(formulario, endereco);
            formulario.elements.namedItem('numero')?.focus();
        } catch {
            mostrarErroDoCampo(cep, 'Não foi possível consultar o CEP agora. Preencha o endereço à mão.');
        } finally {
            cep.removeAttribute('aria-busy');
        }
    });

    botaoCnpj?.addEventListener('click', async () => {
        const cnpj = somenteDigitos(documento?.value);
        if (cnpj.length !== 14) {
            mostrarErroDoCampo(documento, 'Digite um CNPJ com 14 números para buscar os dados da empresa.');
            documento?.focus();
            return;
        }

        botaoCnpj.disabled = true;
        botaoCnpj.setAttribute('aria-busy', 'true');
        try {
            const dados = dadosDoCnpj(await consultar(URL_CNPJ(cnpj)));
            if (!dados) {
                mostrarErroDoCampo(documento, 'CNPJ não encontrado na Receita.');
                return;
            }

            mostrarErroDoCampo(documento, '');
            const preenchidos = preencherVazios(formulario, dados);
            mostrarToast({
                variante: 'success',
                mensagem: preenchidos ? `${preenchidos} campos preenchidos com os dados da Receita. Confira antes de salvar.` : 'Os campos já estavam preenchidos; nada foi alterado.',
            });
        } catch {
            mostrarErroDoCampo(documento, 'Não foi possível consultar o CNPJ agora. Tente de novo em instantes.');
        } finally {
            botaoCnpj.disabled = false;
            botaoCnpj.removeAttribute('aria-busy');
        }
    });
}
