// Rodapé do autocomplete que abre o modal de cadastro rápido (#3003).
import test from 'node:test';
import assert from 'node:assert/strict';

import { iniciarAutocomplete } from '../../assets/js/lib/autocomplete.js';

class Elemento extends EventTarget {
    constructor(tag = 'div') {
        super();
        this.tagName = tag.toUpperCase();
        this.id = '';
        this.value = '';
        this.type = '';
        this.dataset = {};
        this.atributos = {};
        this.filhos = [];
        this.classes = new Set();
        this.classList = {
            add: (...nomes) => nomes.forEach((nome) => this.classes.add(nome)),
            remove: (...nomes) => nomes.forEach((nome) => this.classes.delete(nome)),
            contains: (nome) => this.classes.has(nome),
        };
        this.style = {};
        this.labels = [];
    }

    set className(valor) {
        this.classes = new Set(String(valor).split(' ').filter(Boolean));
    }

    get className() {
        return [...this.classes].join(' ');
    }

    setAttribute(nome, valor) {
        this.atributos[nome] = String(valor);
    }

    getAttribute(nome) {
        return this.atributos[nome] ?? null;
    }

    removeAttribute(nome) {
        delete this.atributos[nome];
    }

    append(...novos) {
        this.filhos.push(...novos);
    }

    appendChild(filho) {
        this.filhos.push(filho);
        return filho;
    }

    replaceChildren(...novos) {
        this.filhos = novos;
    }

    querySelector(seletor) {
        return seletor === 'input:not([type=hidden])' ? this.campo : null;
    }

    setCustomValidity(mensagem) {
        this.mensagem = mensagem;
    }
}

function cenario({ comModal }) {
    const campo = new Elemento('input');
    campo.id = 'cliente';
    const oculto = new Elemento('input');
    oculto.id = 'clientes_id';
    const modal = new Elemento('dialog');
    modal.aberto = false;
    modal.showModal = () => {
        modal.aberto = true;
    };
    const wrapper = new Elemento('div');
    wrapper.campo = campo;
    wrapper.dataset = {
        autocomplete: '/busca',
        autocompleteAlvo: 'clientes_id',
        autocompleteNovo: '/clientes/adicionar',
        autocompleteNovoRotulo: 'Cadastrar novo cliente',
        ...(comModal ? { autocompleteNovoModal: 'cliente-rapido' } : {}),
    };
    const doc = {
        createElement: (tag) => new Elemento(tag),
        createElementNS: (_ns, tag) => new Elemento(tag),
        createTextNode: (texto) => ({ texto }),
        getElementById: (id) => ({ clientes_id: oculto, 'cliente-rapido': comModal ? modal : null })[id] ?? null,
    };
    iniciarAutocomplete(wrapper, { doc, buscar: async () => [] });
    // wrapper.append(popup): o popup é o último filho; o rodapé, o último do popup.
    const popup = wrapper.filhos.at(-1);

    return { campo, oculto, modal, wrapper, rodape: popup.filhos.at(-1) };
}

test('sem o dialog, o rodapé continua sendo o link para a nova aba', () => {
    const { rodape } = cenario({ comModal: false });

    assert.equal(rodape.tagName, 'A');
    assert.equal(rodape.href, '/clientes/adicionar');
    assert.equal(rodape.target, '_blank');
});

test('com o dialog, o rodapé é um botão que avisa o texto digitado e abre o modal', () => {
    const { rodape, campo, wrapper, modal } = cenario({ comModal: true });
    const avisos = [];
    wrapper.addEventListener('autocomplete-novo', (evento) => avisos.push(evento.detail.texto));
    campo.value = '  Maria da Silva ';

    assert.equal(rodape.tagName, 'BUTTON');
    assert.equal(rodape.type, 'button');
    assert.equal(rodape.href, undefined);

    rodape.dispatchEvent(new Event('click'));

    assert.deepEqual(avisos, ['Maria da Silva']);
    assert.equal(modal.aberto, true);
});

test('autocomplete-novo-criado escolhe o cliente criado e preenche o campo oculto', () => {
    const { campo, oculto, wrapper } = cenario({ comModal: true });
    const escolhas = [];
    wrapper.addEventListener('autocomplete-escolha', (evento) => escolhas.push(evento.detail.item));

    wrapper.dispatchEvent(new CustomEvent('autocomplete-novo-criado', { detail: { item: { id: 42, valor: 'Maria da Silva', detalhe: '123' } } }));

    assert.equal(campo.value, 'Maria da Silva');
    assert.equal(oculto.value, '42');
    assert.deepEqual(escolhas, [{ id: '42', valor: 'Maria da Silva', detalhe: '123' }]);
    assert.equal(campo.mensagem, '', 'o campo fica válido');
});

test('autocomplete-novo-criado sem item válido não escolhe nada', () => {
    const { campo, oculto, wrapper } = cenario({ comModal: true });

    wrapper.dispatchEvent(new CustomEvent('autocomplete-novo-criado', { detail: { item: { valor: 'sem id' } } }));
    wrapper.dispatchEvent(new CustomEvent('autocomplete-novo-criado'));

    assert.equal(campo.value, '');
    assert.equal(oculto.value, '');
});
