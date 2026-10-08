// Padrões de formulário (#2851): máscaras, mensagens de erro, carregamento e
// os mapeamentos de CEP e CNPJ do formulário de cliente.
import test from 'node:test';
import assert from 'node:assert/strict';

import { formatarCep, formatarDinheiro, formatarDocumento, formatarTelefone, somenteDigitos } from '../../assets/js/lib/mascaras.js';
import { MENSAGENS, marcarCarregando, mensagemDoCampo, semDescritor } from '../../assets/js/lib/formulario.js';
import { dadosDoCnpj, enderecoDoCep, preencherVazios } from '../../assets/js/modules/clientes/formulario.js';
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

test('preenche só os campos vazios', () => {
    const campos = { nomeCliente: { value: 'Digitado' }, cidade: { value: '' }, estado: { value: '  ' } };
    const formulario = { elements: { namedItem: (nome) => campos[nome] ?? null } };

    const total = preencherVazios(formulario, { nomeCliente: 'Da API', cidade: 'Brasília', estado: 'DF', bairro: 'Asa Norte', numero: '' });

    assert.equal(total, 2);
    assert.equal(campos.nomeCliente.value, 'Digitado');
    assert.equal(campos.cidade.value, 'Brasília');
    assert.equal(campos.estado.value, 'DF');
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
