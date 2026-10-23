// Formulário de cliente (views/clientes/formulario.php, #2851): busca o
// endereço pelo CEP (ViaCEP) e os dados da empresa pelo CNPJ.
//
// O CNPJ passa por uma cadeia de APIs públicas, na ordem: BrasilAPI, Minha
// Receita e CNPJ.ws (#2996). Cada uma é consultada por fetch, com tempo limite;
// falha de rede, tempo esgotado, 429, erro do servidor (5xx) ou qualquer outro
// erro HTTP passa para a próxima. Um 400 ou 404 quer dizer que o CNPJ não
// existe e encerra a cadeia. A ReceitaWS não entra: ela não manda CORS (a v4
// usava JSONP, que a CSP da v5 bloqueia). Todas as APIs mandam CORS e estão no
// connect-src da CSP (application/helpers/seguranca_cabecalhos_helper.php).
//
// O CNPJ e o CEP sobrescrevem os campos com o que a consulta traz; número e
// complemento digitados são preservados. Falha de consulta vira erro no próprio
// campo (CEP ou CPF/CNPJ), como os erros de validação; o sucesso do CNPJ mostra um toast.

import { mostrarErroDoCampo } from '../../lib/formulario.js';
import { formatarCep, formatarTelefone, somenteDigitos } from '../../lib/mascaras.js';
import { mostrarToast } from '../../lib/toast.js';

export const URL_CEP = (cep) => `https://viacep.com.br/ws/${cep}/json/`;
export const URL_CNPJ = (cnpj) => `https://brasilapi.com.br/api/cnpj/v1/${cnpj}`;
export const URL_CNPJ_MINHA_RECEITA = (cnpj) => `https://minhareceita.org/${cnpj}`;
export const URL_CNPJ_WS = (cnpj) => `https://publica.cnpj.ws/cnpj/${cnpj}`;

/** Tempo máximo de cada consulta de CNPJ antes de tentar a próxima API. */
export const TEMPO_LIMITE_CNPJ = 8000;

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

/** Campos do formulário a partir da resposta da BrasilAPI (ou da Minha Receita, que usa os mesmos nomes). */
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

/** Campos do formulário a partir da resposta da CNPJ.ws pública, que aninha os dados do estabelecimento. */
export function dadosDoCnpjWs(dados) {
    if (!dados || !dados.razao_social) {
        return null;
    }

    const estabelecimento = dados.estabelecimento ?? {};

    return dadosDoCnpj({
        razao_social: dados.razao_social,
        nome_fantasia: estabelecimento.nome_fantasia,
        ddd_telefone_1: estabelecimento.ddd1 && estabelecimento.telefone1 ? `${estabelecimento.ddd1}${estabelecimento.telefone1}` : '',
        email: estabelecimento.email,
        cep: estabelecimento.cep,
        descricao_tipo_de_logradouro: estabelecimento.tipo_logradouro,
        logradouro: estabelecimento.logradouro,
        numero: estabelecimento.numero,
        complemento: estabelecimento.complemento,
        bairro: estabelecimento.bairro,
        municipio: estabelecimento.cidade?.nome,
        uf: estabelecimento.estado?.sigla,
    });
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

/** Preenche os campos sobrescrevendo o que houver; devolve quantos mudaram. */
export function preencherSobrescrevendo(formulario, valores) {
    let preenchidos = 0;

    for (const [nome, valor] of Object.entries(valores)) {
        const campo = formulario.elements.namedItem(nome);
        if (campo && valor && campo.value !== valor) {
            campo.value = valor;
            preenchidos++;
        }
    }

    return preenchidos;
}

const CAMPOS_DO_CEP = ['rua', 'bairro', 'cidade', 'estado'];

/** Endereço do ViaCEP: sobrescreve rua/bairro/cidade/estado e só preenche o complemento vazio. */
export function preencherEndereco(formulario, endereco) {
    const principais = Object.fromEntries(CAMPOS_DO_CEP.map((nome) => [nome, endereco[nome]]));

    return preencherSobrescrevendo(formulario, principais)
        + preencherVazios(formulario, { complemento: endereco.complemento });
}

async function consultar(url, buscar = fetch) {
    const resposta = await buscar(url, { headers: { Accept: 'application/json' } });
    if (!resposta.ok) {
        return null;
    }

    return resposta.json();
}

/** APIs de CNPJ na ordem em que são tentadas; `mapear` devolve os campos do formulário ou null. */
export const PROVEDORES_CNPJ = [
    { nome: 'BrasilAPI', url: URL_CNPJ, mapear: dadosDoCnpj },
    { nome: 'Minha Receita', url: URL_CNPJ_MINHA_RECEITA, mapear: dadosDoCnpj },
    { nome: 'CNPJ.ws', url: URL_CNPJ_WS, mapear: dadosDoCnpjWs },
];

/** Status com que as APIs dizem que o CNPJ não existe (ou é inválido): não adianta perguntar a outra. */
const STATUS_CNPJ_INEXISTENTE = [400, 404];

/** Consulta uma API; devolve { encontrado: false } para 400/404 e lança em qualquer outra falha. */
async function consultarProvedor(provedor, cnpj, buscar, tempoLimite) {
    const controle = new AbortController();
    const temporizador = setTimeout(() => controle.abort(), tempoLimite);

    try {
        const resposta = await buscar(provedor.url(cnpj), { headers: { Accept: 'application/json' }, signal: controle.signal });
        if (STATUS_CNPJ_INEXISTENTE.includes(resposta.status)) {
            return { encontrado: false };
        }
        if (!resposta.ok) {
            throw new Error(`HTTP ${resposta.status}`);
        }

        return { encontrado: true, dados: provedor.mapear(await resposta.json()) };
    } finally {
        clearTimeout(temporizador);
    }
}

/**
 * Dados da empresa pelo CNPJ (só dígitos), tentando as APIs em PROVEDORES_CNPJ.
 *
 * Passa para a próxima API quando a atual falha na rede, estoura o tempo limite
 * ou responde 429, 5xx ou qualquer erro que não seja 400/404. Devolve os campos do
 * formulário, ou null se a API diz que o CNPJ não existe (a cadeia para aí).
 * Se todas falham, lança um Error com `falhas` ([{ provedor, motivo }]).
 */
export async function consultarCnpj(cnpj, buscar = fetch, tempoLimite = TEMPO_LIMITE_CNPJ) {
    const falhas = [];

    for (const provedor of PROVEDORES_CNPJ) {
        try {
            const { encontrado, dados } = await consultarProvedor(provedor, cnpj, buscar, tempoLimite);

            return encontrado ? dados : null;
        } catch (erro) {
            falhas.push({ provedor: provedor.nome, motivo: erro?.message || String(erro) });
        }
    }

    const erro = new Error('Nenhuma API de CNPJ respondeu.');
    erro.falhas = falhas;

    throw erro;
}

/**
 * Liga a busca de CEP (campo cep) e o botão [data-buscar-cnpj] ao formulário.
 * Os campos vêm de formulario.elements (inclui os ligados por form="id"); o
 * botão é procurado em `raiz`, que por padrão é o próprio formulário. O modal
 * de cadastro rápido (clientes/cadastro-rapido) usa a mesma função e chama
 * reiniciar() do retorno ao limpar o formulário.
 */
export function ligarBuscas(formulario, raiz = formulario) {
    const cep = formulario.elements.namedItem('cep');
    const documento = formulario.elements.namedItem('documento');
    const botaoCnpj = raiz.querySelector('[data-buscar-cnpj]');
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
            // Entre o pedido e a resposta o usuário pode ter trocado o CEP: uma
            // resposta antiga não pode sobrescrever o endereço do CEP atual.
            if (digitos !== ultimoCep) {
                return;
            }
            if (!endereco) {
                mostrarErroDoCampo(cep, 'CEP não encontrado. Confira ou preencha o endereço à mão.');
                return;
            }

            mostrarErroDoCampo(cep, '');
            preencherEndereco(formulario, endereco);
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
            const dados = await consultarCnpj(cnpj);
            if (!dados) {
                mostrarErroDoCampo(documento, 'CNPJ não encontrado na Receita.');
                return;
            }

            mostrarErroDoCampo(documento, '');
            const preenchidos = preencherSobrescrevendo(formulario, dados);
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

    // Depois de limpar o formulário, o mesmo CEP precisa poder ser buscado de novo.
    return { reiniciar: () => { ultimoCep = ''; } };
}

// O <form> já leva o módulo formulario/padrao; este fica num wrapper em volta.
export default function iniciar(raiz) {
    const formulario = raiz.matches?.('form') ? raiz : raiz.querySelector('form');
    if (!formulario) {
        return;
    }

    ligarBuscas(formulario, formulario);
}
