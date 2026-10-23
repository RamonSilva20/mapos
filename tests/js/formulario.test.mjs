// Padrões de formulário (#2851): máscaras, mensagens de erro, carregamento e
// os mapeamentos de CEP e CNPJ do formulário de cliente.
import test from 'node:test';
import assert from 'node:assert/strict';

import { formatarCep, formatarDinheiro, formatarDocumento, formatarTelefone, somenteDigitos } from '../../assets/js/lib/mascaras.js';
import { MENSAGENS, marcarCarregando, mensagemDoCampo, semDescritor } from '../../assets/js/lib/formulario.js';
import { consultarCnpj, dadosDoCnpj, dadosDoCnpjWs, enderecoDoCep, ligarBuscas, preencherEndereco, preencherSobrescrevendo, preencherVazios, URL_CEP, URL_CNPJ, URL_CNPJ_MINHA_RECEITA, URL_CNPJ_WS } from '../../assets/js/modules/clientes/formulario.js';
import { aplicarMascara } from '../../assets/js/modules/formulario/padrao.js';

test('máscara de CPF, CNPJ e CNPJ alfanumérico', () => {
    assert.equal(formatarDocumento('12345678909'), '123.456.789-09');
    assert.equal(formatarDocumento('123.456'), '123.456');
    assert.equal(formatarDocumento('11222333000181'), '11.222.333/0001-81');
    assert.equal(formatarDocumento('12abc34501de35'), '12.ABC.345/01DE-35');
    assert.equal(formatarDocumento('112223330001819999'), '11.222.333/0001-81');
    assert.equal(formatarDocumento(''), '');
});

test('máscara de telefone fixo e celular, e de CEP', () => {
    assert.equal(formatarTelefone('1133334444'), '(11) 3333-4444');
    assert.equal(formatarTelefone('11988887777'), '(11) 98888-7777');
    assert.equal(formatarTelefone('(11) 9'), '(11) 9');
    assert.equal(formatarCep('01001000'), '01001-000');
    assert.equal(formatarCep('01001-000123'), '01001-000');
    assert.equal(somenteDigitos('(11) 3333-4444'), '1133334444');
});

const campo = (validity, extra = {}) => ({ validity, type: 'text', dataset: {}, ...extra });

test('mensagem do campo vem da validação nativa e dos data-msg-*', () => {
    assert.equal(mensagemDoCampo(campo({ valid: true })), '');
    assert.equal(mensagemDoCampo(campo({ valid: false, valueMissing: true })), MENSAGENS.vazio);
    assert.equal(mensagemDoCampo(campo({ valid: false, valueMissing: true }, { dataset: { msgVazio: 'Informe o nome.' } })), 'Informe o nome.');
    assert.equal(mensagemDoCampo(campo({ valid: false, typeMismatch: true }, { type: 'email' })), MENSAGENS.email);
    assert.equal(mensagemDoCampo(campo({ valid: false, tooLong: true })), MENSAGENS.longo);
    assert.equal(mensagemDoCampo(campo({ valid: false, patternMismatch: true }, { dataset: { msgInvalido: 'Formato errado.' } })), 'Formato errado.');
});

test('aria-describedby perde só o id do erro', () => {
    assert.deepEqual(semDescritor('campo-ajuda campo-erro', 'campo-erro'), ['campo-ajuda']);
    assert.deepEqual(semDescritor(null, 'x'), []);
});

test('botão em carregamento troca o rótulo e volta ao original', () => {
    const rotulo = { textContent: 'Salvar' };
    const atributos = {};
    const botao = {
        dataset: { rotuloCarregando: 'Salvando…' },
        disabled: false,
        querySelector: (seletor) => (seletor === 'span:not(.sr-only)' ? rotulo : null),
        setAttribute: (nome, valor) => { atributos[nome] = valor; },
    };

    marcarCarregando(botao, true);
    assert.equal(botao.disabled, true);
    assert.equal(atributos['aria-busy'], 'true');
    assert.equal(rotulo.textContent, 'Salvando…');

    marcarCarregando(botao, false);
    assert.equal(botao.disabled, false);
    assert.equal(rotulo.textContent, 'Salvar');
});

test('endereço do ViaCEP', () => {
    assert.deepEqual(enderecoDoCep({ logradouro: 'Praça da Sé', complemento: 'lado ímpar', bairro: 'Sé', localidade: 'São Paulo', uf: 'SP' }), {
        rua: 'Praça da Sé', complemento: 'lado ímpar', bairro: 'Sé', cidade: 'São Paulo', estado: 'SP',
    });
    assert.equal(enderecoDoCep({ erro: true }), null);
    assert.equal(enderecoDoCep(null), null);
});

test('dados da empresa pela BrasilAPI', () => {
    const dados = dadosDoCnpj({
        razao_social: 'BANCO DO BRASIL SA',
        nome_fantasia: 'DIRECAO GERAL',
        ddd_telefone_1: '6134939002',
        email: 'SECEX@BB.COM.BR',
        cep: '70040912',
        descricao_tipo_de_logradouro: 'QUADRA',
        logradouro: 'SAUN QUADRA 5 BLOCO B',
        numero: 'S/N',
        complemento: 'ANDAR 1 A 16',
        bairro: 'ASA NORTE',
        municipio: 'BRASILIA',
        uf: 'DF',
    });

    assert.equal(dados.nomeCliente, 'Banco Do Brasil Sa');
    assert.equal(dados.telefone, '(61) 3493-9002');
    assert.equal(dados.email, 'secex@bb.com.br');
    assert.equal(dados.cep, '70040-912');
    assert.equal(dados.rua, 'Quadra Saun Quadra 5 Bloco B');
    assert.equal(dados.cidade, 'Brasilia');
    assert.equal(dados.estado, 'DF');
    assert.equal(dadosDoCnpj({}), null);
});

// --- Busca de CNPJ com fallback (#2996) ------------------------------------

const CNPJ = '00000000000191';

const empresaBrasilApi = {
    razao_social: 'BANCO DO BRASIL SA',
    nome_fantasia: 'DIRECAO GERAL',
    ddd_telefone_1: '6134939002',
    email: 'SECEX@BB.COM.BR',
    cep: '70040912',
    descricao_tipo_de_logradouro: 'QUADRA',
    logradouro: 'SAUN QUADRA 5 BLOCO B',
    numero: 'S/N',
    complemento: 'ANDAR 1 A 16',
    bairro: 'ASA NORTE',
    municipio: 'BRASILIA',
    uf: 'DF',
};

const empresaCnpjWs = {
    razao_social: 'BANCO DO BRASIL SA',
    estabelecimento: {
        nome_fantasia: 'DIRECAO GERAL',
        ddd1: '61',
        telefone1: '34939002',
        email: 'SECEX@BB.COM.BR',
        cep: '70040912',
        tipo_logradouro: 'QUADRA',
        logradouro: 'SAUN QUADRA 5 BLOCO B',
        numero: 'S/N',
        complemento: 'ANDAR 1 A 16',
        bairro: 'ASA NORTE',
        cidade: { nome: 'Brasília' },
        estado: { sigla: 'DF' },
    },
};

const resposta = (status, corpo) => ({ ok: status >= 200 && status < 300, status, json: async () => corpo });

/**
 * fetch falso: cada API tem o seu comportamento, indexado pela URL. O valor é
 * uma resposta pronta, ou uma função (url, opcoes) para falhar de outro jeito.
 * `chamadas` guarda as URLs na ordem em que foram pedidas.
 */
function fetchFalso(comportamentos) {
    const chamadas = [];
    const buscar = async (url, opcoes) => {
        chamadas.push(url);
        const comportamento = comportamentos[url];

        return typeof comportamento === 'function' ? comportamento(url, opcoes) : comportamento;
    };
    buscar.chamadas = chamadas;

    return buscar;
}

const erroDeRede = () => {
    throw new TypeError('Failed to fetch');
};

test('CNPJ.ws: dados do estabelecimento viram os mesmos campos da BrasilAPI', () => {
    const dados = dadosDoCnpjWs(empresaCnpjWs);

    assert.deepEqual(dados, {
        nomeCliente: 'Banco Do Brasil Sa',
        contato: 'Direcao Geral',
        telefone: '(61) 3493-9002',
        email: 'secex@bb.com.br',
        cep: '70040-912',
        rua: 'Quadra Saun Quadra 5 Bloco B',
        numero: 'S/N',
        complemento: 'Andar 1 A 16',
        bairro: 'Asa Norte',
        cidade: 'Brasília',
        estado: 'DF',
    });
    assert.deepEqual(dados, { ...dadosDoCnpj(empresaBrasilApi), cidade: 'Brasília' });
});

test('CNPJ.ws: campos ausentes ficam vazios e sem razão social não há dados', () => {
    const dados = dadosDoCnpjWs({ razao_social: 'FULANO LTDA', estabelecimento: { ddd1: '61', cidade: null } });

    assert.equal(dados.nomeCliente, 'Fulano Ltda');
    assert.equal(dados.telefone, '', 'Sem o telefone, o DDD sozinho não vira número.');
    assert.equal(dados.cidade, '');
    assert.equal(dados.estado, '');
    assert.equal(dadosDoCnpjWs({ razao_social: 'FULANO LTDA' }).nomeCliente, 'Fulano Ltda');
    assert.equal(dadosDoCnpjWs({ estabelecimento: {} }), null);
    assert.equal(dadosDoCnpjWs(null), null);
});

test('CNPJ: a BrasilAPI responde e as outras nem são chamadas', async () => {
    const buscar = fetchFalso({ [URL_CNPJ(CNPJ)]: resposta(200, empresaBrasilApi) });

    const dados = await consultarCnpj(CNPJ, buscar);

    assert.equal(dados.nomeCliente, 'Banco Do Brasil Sa');
    assert.equal(dados.cidade, 'Brasilia');
    assert.deepEqual(buscar.chamadas, ['https://brasilapi.com.br/api/cnpj/v1/00000000000191']);
});

test('CNPJ: erro 5xx na BrasilAPI cai na Minha Receita', async () => {
    const buscar = fetchFalso({
        [URL_CNPJ(CNPJ)]: resposta(503, {}),
        [URL_CNPJ_MINHA_RECEITA(CNPJ)]: resposta(200, empresaBrasilApi),
    });

    const dados = await consultarCnpj(CNPJ, buscar);

    assert.equal(dados.nomeCliente, 'Banco Do Brasil Sa');
    assert.equal(dados.telefone, '(61) 3493-9002');
    assert.deepEqual(buscar.chamadas, ['https://brasilapi.com.br/api/cnpj/v1/00000000000191', 'https://minhareceita.org/00000000000191']);
});

test('CNPJ: limite de consultas (429) passa para a próxima API', async () => {
    const buscar = fetchFalso({
        [URL_CNPJ(CNPJ)]: resposta(429, {}),
        [URL_CNPJ_MINHA_RECEITA(CNPJ)]: resposta(429, {}),
        [URL_CNPJ_WS(CNPJ)]: resposta(200, empresaCnpjWs),
    });

    const dados = await consultarCnpj(CNPJ, buscar);

    assert.equal(dados.cidade, 'Brasília');
    assert.equal(buscar.chamadas.length, 3);
});

test('CNPJ: outro erro HTTP (bloqueio, 403) também passa para a próxima API', async () => {
    const buscar = fetchFalso({
        [URL_CNPJ(CNPJ)]: resposta(403, {}),
        [URL_CNPJ_MINHA_RECEITA(CNPJ)]: resposta(200, empresaBrasilApi),
    });

    assert.equal((await consultarCnpj(CNPJ, buscar)).nomeCliente, 'Banco Do Brasil Sa');
    assert.equal(buscar.chamadas.length, 2);
});

test('CNPJ: erro de rede passa para a próxima API', async () => {
    const buscar = fetchFalso({
        [URL_CNPJ(CNPJ)]: erroDeRede,
        [URL_CNPJ_MINHA_RECEITA(CNPJ)]: resposta(200, empresaBrasilApi),
    });

    const dados = await consultarCnpj(CNPJ, buscar);

    assert.equal(dados.nomeCliente, 'Banco Do Brasil Sa');
    assert.equal(buscar.chamadas.length, 2);
});

test('CNPJ: resposta que não é JSON passa para a próxima API', async () => {
    const buscar = fetchFalso({
        [URL_CNPJ(CNPJ)]: { ok: true, status: 200, json: async () => { throw new SyntaxError('Unexpected token <'); } },
        [URL_CNPJ_MINHA_RECEITA(CNPJ)]: resposta(200, empresaBrasilApi),
    });

    assert.equal((await consultarCnpj(CNPJ, buscar)).nomeCliente, 'Banco Do Brasil Sa');
});

test('CNPJ: tempo esgotado aborta a consulta e passa para a próxima API', async () => {
    const sinais = [];
    const travada = (url, { signal }) => new Promise((_, rejeitar) => {
        sinais.push(signal);
        signal.addEventListener('abort', () => rejeitar(new DOMException('The operation was aborted.', 'AbortError')));
    });
    const buscar = fetchFalso({
        [URL_CNPJ(CNPJ)]: travada,
        [URL_CNPJ_MINHA_RECEITA(CNPJ)]: resposta(200, empresaBrasilApi),
    });

    const dados = await consultarCnpj(CNPJ, buscar, 10);

    assert.equal(dados.nomeCliente, 'Banco Do Brasil Sa');
    assert.equal(sinais.length, 1);
    assert.equal(sinais[0].aborted, true);
});

test('CNPJ: as duas primeiras falham e a CNPJ.ws responde com os campos mapeados', async () => {
    const buscar = fetchFalso({
        [URL_CNPJ(CNPJ)]: resposta(500, {}),
        [URL_CNPJ_MINHA_RECEITA(CNPJ)]: erroDeRede,
        [URL_CNPJ_WS(CNPJ)]: resposta(200, empresaCnpjWs),
    });

    const dados = await consultarCnpj(CNPJ, buscar);

    assert.equal(dados.nomeCliente, 'Banco Do Brasil Sa');
    assert.equal(dados.telefone, '(61) 3493-9002');
    assert.equal(dados.rua, 'Quadra Saun Quadra 5 Bloco B');
    assert.equal(dados.cidade, 'Brasília');
    assert.equal(dados.estado, 'DF');
    assert.deepEqual(buscar.chamadas, [
        'https://brasilapi.com.br/api/cnpj/v1/00000000000191',
        'https://minhareceita.org/00000000000191',
        'https://publica.cnpj.ws/cnpj/00000000000191',
    ]);
});

test('CNPJ: 404 e 400 dizem que o CNPJ não existe e encerram a cadeia', async () => {
    for (const status of [404, 400]) {
        const buscar = fetchFalso({
            [URL_CNPJ(CNPJ)]: resposta(status, { message: 'CNPJ não encontrado' }),
            [URL_CNPJ_MINHA_RECEITA(CNPJ)]: resposta(200, empresaBrasilApi),
        });

        assert.equal(await consultarCnpj(CNPJ, buscar), null, `HTTP ${status}`);
        assert.equal(buscar.chamadas.length, 1, `HTTP ${status}: não pergunta a outra API.`);
    }
});

test('CNPJ: 404 numa API de fallback também encerra a cadeia', async () => {
    const buscar = fetchFalso({
        [URL_CNPJ(CNPJ)]: erroDeRede,
        [URL_CNPJ_MINHA_RECEITA(CNPJ)]: resposta(404, {}),
        [URL_CNPJ_WS(CNPJ)]: resposta(200, empresaCnpjWs),
    });

    assert.equal(await consultarCnpj(CNPJ, buscar), null);
    assert.equal(buscar.chamadas.length, 2);
});

test('CNPJ: resposta sem razão social conta como não encontrado', async () => {
    const buscar = fetchFalso({ [URL_CNPJ(CNPJ)]: resposta(200, {}) });

    assert.equal(await consultarCnpj(CNPJ, buscar), null);
    assert.equal(buscar.chamadas.length, 1);
});

test('CNPJ: todas as APIs falhando lança erro com o motivo de cada uma', async () => {
    const buscar = fetchFalso({
        [URL_CNPJ(CNPJ)]: resposta(502, {}),
        [URL_CNPJ_MINHA_RECEITA(CNPJ)]: erroDeRede,
        [URL_CNPJ_WS(CNPJ)]: resposta(429, {}),
    });

    await assert.rejects(consultarCnpj(CNPJ, buscar), (erro) => {
        assert.deepEqual(erro.falhas, [
            { provedor: 'BrasilAPI', motivo: 'HTTP 502' },
            { provedor: 'Minha Receita', motivo: 'Failed to fetch' },
            { provedor: 'CNPJ.ws', motivo: 'HTTP 429' },
        ]);

        return true;
    });
    assert.equal(buscar.chamadas.length, 3);
});

test('CNPJ: cada consulta leva um sinal de cancelamento e o cabeçalho Accept', async () => {
    const opcoesRecebidas = [];
    const buscar = async (url, opcoes) => {
        opcoesRecebidas.push(opcoes);

        return resposta(200, empresaBrasilApi);
    };

    await consultarCnpj(CNPJ, buscar);

    assert.equal(opcoesRecebidas[0].headers.Accept, 'application/json');
    assert.equal(typeof opcoesRecebidas[0].signal.aborted, 'boolean');
    assert.equal(opcoesRecebidas[0].signal.aborted, false);
});

test('preenche só os campos vazios', () => {
    const campos = { nomeCliente: { value: 'Digitado' }, cidade: { value: '' }, estado: { value: '  ' } };
    const formulario = { elements: { namedItem: (nome) => campos[nome] ?? null } };

    const total = preencherVazios(formulario, { nomeCliente: 'Da API', cidade: 'Brasília', estado: 'DF', bairro: 'Asa Norte', numero: '' });

    assert.equal(total, 2);
    assert.equal(campos.nomeCliente.value, 'Digitado');
    assert.equal(campos.cidade.value, 'Brasília');
    assert.equal(campos.estado.value, 'DF');
});

test('dados do CNPJ sobrescrevem o que o navegador ou uma busca anterior deixou', () => {
    const campos = {
        nomeCliente: { value: 'Nome Do Navegador' },
        contato: { value: '' },
        telefone: { value: '' },
        email: { value: '' },
        cep: { value: '' },
        rua: { value: '' },
        numero: { value: '' },
        complemento: { value: '' },
        bairro: { value: '' },
        cidade: { value: 'Cidade Do Autofill' },
        estado: { value: '' },
    };
    const formulario = { elements: { namedItem: (nome) => campos[nome] ?? null } };

    const total = preencherSobrescrevendo(formulario, dadosDoCnpj({
        razao_social: 'BANCO DO BRASIL SA',
        nome_fantasia: 'DIRECAO GERAL',
        ddd_telefone_1: '6134939002',
        email: null,
        cep: '70040912',
        descricao_tipo_de_logradouro: 'QUADRA',
        logradouro: 'SAUN QUADRA 5 BLOCO B',
        numero: 'SN',
        complemento: 'ANDAR 1',
        bairro: 'ASA NORTE',
        municipio: 'BRASILIA',
        uf: 'DF',
    }));

    assert.equal(campos.nomeCliente.value, 'Banco Do Brasil Sa', 'O nome do autofill é corrigido pela Receita.');
    assert.equal(campos.cidade.value, 'Brasilia', 'A cidade do autofill é corrigida.');
    assert.equal(campos.telefone.value, '(61) 3493-9002');
    assert.equal(campos.cep.value, '70040-912');
    assert.equal(campos.bairro.value, 'Asa Norte');
    assert.equal(campos.email.value, '', 'E-mail vazio na Receita não escreve nada.');
    assert.equal(total, 10);
});

test('endereço do CEP sobrescreve rua/bairro/cidade/estado e preserva número/complemento', () => {
    const campos = {
        rua: { value: 'Rua Antiga' },
        numero: { value: '100' },
        complemento: { value: '' },
        bairro: { value: '' },
        cidade: { value: 'Cidade Velha' },
        estado: { value: 'RJ' },
    };
    const formulario = { elements: { namedItem: (nome) => campos[nome] ?? null } };

    const total = preencherEndereco(formulario, {
        rua: 'Praça da Sé', complemento: 'lado ímpar', bairro: 'Sé', cidade: 'São Paulo', estado: 'SP',
    });

    assert.equal(total, 5);
    assert.equal(campos.rua.value, 'Praça da Sé', 'A rua já preenchida é corrigida pela consulta.');
    assert.equal(campos.numero.value, '100', 'O número digitado é mantido.');
    assert.equal(campos.complemento.value, 'lado ímpar');
    assert.equal(campos.bairro.value, 'Sé');
    assert.equal(campos.cidade.value, 'São Paulo');
    assert.equal(campos.estado.value, 'SP');
});

test('endereço do CEP não apaga campos quando o ViaCEP não traz o logradouro', () => {
    const campos = {
        rua: { value: 'Rua Que Eu Digitei' },
        numero: { value: '' },
        complemento: { value: 'Apto 42' },
        bairro: { value: '' },
        cidade: { value: '' },
        estado: { value: '' },
    };
    const formulario = { elements: { namedItem: (nome) => campos[nome] ?? null } };

    const total = preencherEndereco(formulario, {
        rua: '', complemento: '', bairro: '', cidade: 'Jundiaí', estado: 'SP',
    });

    assert.equal(total, 2);
    assert.equal(campos.rua.value, 'Rua Que Eu Digitei', 'Valor vazio do ViaCEP não apaga a rua digitada.');
    assert.equal(campos.complemento.value, 'Apto 42', 'O complemento digitado é preservado.');
    assert.equal(campos.cidade.value, 'Jundiaí');
    assert.equal(campos.estado.value, 'SP');
});

test('CEP: resposta antiga não sobrescreve o endereço do CEP mais novo', async () => {
    const resolvers = {};
    global.document = { getElementById: () => null };
    global.fetch = (url) => new Promise((resolve) => { resolvers[url] = resolve; });

    const cep = {
        value: '', id: 'cep',
        addEventListener(tipo, fn) { this[tipo] = fn; },
        setAttribute() {}, removeAttribute() {}, getAttribute: () => null,
        classList: { add() {}, remove() {} },
    };
    const campos = {
        cep,
        rua: { value: '' }, bairro: { value: '' }, cidade: { value: '' }, estado: { value: '' },
        complemento: { value: '' }, numero: { value: '', focus() {} },
    };
    const formulario = { elements: { namedItem: (nome) => campos[nome] ?? null }, querySelector: () => null };

    try {
        ligarBuscas(formulario);

        cep.value = '01001-000';
        const buscaA = cep.input();
        cep.value = '20040-002';
        const buscaB = cep.input();

        // A resposta do CEP novo (B) chega primeiro e é aplicada.
        resolvers[URL_CEP('20040002')](resposta(200, { logradouro: 'Rua B', bairro: 'Centro', localidade: 'Rio de Janeiro', uf: 'RJ' }));
        await buscaB;
        // A do CEP antigo (A) chega depois e deve ser ignorada.
        resolvers[URL_CEP('01001000')](resposta(200, { logradouro: 'Praça da Sé', bairro: 'Sé', localidade: 'São Paulo', uf: 'SP' }));
        await buscaA;

        assert.equal(campos.cidade.value, 'Rio de Janeiro', 'A resposta antiga do CEP A não substitui o endereço do CEP B.');
        assert.equal(campos.rua.value, 'Rua B');
        assert.equal(campos.bairro.value, 'Centro');
        assert.equal(campos.estado.value, 'RJ');
    } finally {
        delete global.fetch;
        delete global.document;
    }
});

test('máscara de dinheiro: centavos da direita, milhar com ponto', () => {
    assert.equal(formatarDinheiro('1'), '0,01');
    assert.equal(formatarDinheiro('123456'), '1.234,56');
    assert.equal(formatarDinheiro('1234.50'), '1.234,50');
    assert.equal(formatarDinheiro('0,00'), '0,00');
    assert.equal(formatarDinheiro('R$ 1.000.000,00'), '1.000.000,00');
    assert.equal(formatarDinheiro(''), '');
    // Até 10 dígitos (DECIMAL(10,2)): o 11º é ignorado.
    assert.equal(formatarDinheiro('99999999999'), '99.999.999,99');
});

test('dinheiro: apagar até só sobrar zero esvazia o campo', () => {
    const campo = { dataset: { mascara: 'dinheiro' }, value: '0,0' };
    aplicarMascara(campo, { inputType: 'deleteContentBackward' });
    assert.equal(campo.value, '');

    const digitando = { dataset: { mascara: 'dinheiro' }, value: '0' };
    aplicarMascara(digitando, { inputType: 'insertText' });
    assert.equal(digitando.value, '0,00');

    const parcial = { dataset: { mascara: 'dinheiro' }, value: '1.234,5' };
    aplicarMascara(parcial, { inputType: 'deleteContentBackward' });
    assert.equal(parcial.value, '123,45');
});

import { numeroParaReais, precoDeVenda, reaisParaNumero } from '../../assets/js/modules/produtos/formulario.js';

test('preço de venda por markup e por margem', () => {
    assert.equal(reaisParaNumero('1.234,56'), 1234.56);
    assert.equal(reaisParaNumero(''), null);
    assert.equal(numeroParaReais(450), '450,00');
    assert.equal(numeroParaReais(precoDeVenda(300, 50, 'markup')), '450,00');
    assert.equal(numeroParaReais(precoDeVenda(300, 40, 'margem')), '500,00');
    assert.equal(precoDeVenda(300, 100, 'margem'), null);
    assert.equal(precoDeVenda(null, 10, 'markup'), null);
    assert.equal(precoDeVenda(300, -1, 'markup'), null);
});
