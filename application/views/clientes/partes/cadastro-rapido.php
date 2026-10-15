<?php
/**
 * Modal de cadastro rápido de cliente (#3003), usado pelas telas da OS e da
 * venda quando o usuário tem a permissão aCliente. Fica depois do </form> da
 * tela, para não aninhar formulários: os campos do modal apontam para o
 * formulário oculto pelo atributo form.
 *
 * O rodapé "Cadastrar novo cliente" do autocomplete (data-autocomplete-novo-modal
 * = "cliente-rapido") abre este modal. O módulo clientes/cadastro-rapido envia
 * o formulário sem recarregar (Clientes::cadastroRapido()) e, com sucesso,
 * escolhe o cliente criado no campo da tela. Sem a senha da área do cliente:
 * o servidor a gera a partir do CPF ou CNPJ. "Cadastro completo" leva ao
 * formulário inteiro numa nova aba.
 */
$idForm = 'form-cliente-rapido';
// Props do input com o id próprio do modal (evita colidir com campos da tela).
$campo = static fn (string $nome, array $props) => $props + [
    'name' => $nome,
    'id' => 'cliente-rapido-' . $nome,
];
$comForm = static fn (array $attrs) => ['form' => $idForm] + $attrs;
?>
<div <?= js_module('clientes/cadastro-rapido') ?>>
    <form id="<?= e($idForm) ?>" method="post" action="<?= e(site_url('clientes/cadastroRapido')) ?>" novalidate hidden>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
    </form>
    <?= component('modal', [
        'id' => 'cliente-rapido',
        'title' => 'Novo cliente',
        'size' => 'lg',
        'body' => [
            new HtmlSeguro('<div class="grid gap-4 sm:grid-cols-2">'),
            new HtmlSeguro('<fieldset class="flex flex-col gap-2 sm:col-span-2"><legend class="text-label-md text-text">Tipo</legend><div class="flex flex-wrap gap-x-6 gap-y-2">'),
            component('radio', ['name' => 'fornecedor', 'id' => 'cliente-rapido-fornecedor-0', 'value' => '0', 'label' => 'Cliente', 'checked' => true, 'attrs' => $comForm([])]),
            component('radio', ['name' => 'fornecedor', 'id' => 'cliente-rapido-fornecedor-1', 'value' => '1', 'label' => 'Fornecedor', 'attrs' => $comForm([])]),
            new HtmlSeguro('</div></fieldset>'),

            new HtmlSeguro('<div class="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-2">'),
            component('input', $campo('documento', [
                'label' => 'CPF ou CNPJ',
                'attrs' => $comForm(['data-mascara' => 'documento', 'maxlength' => 20, 'autocomplete' => 'off']),
            ])),
            component('button', ['label' => 'Buscar CNPJ', 'icon' => 'search', 'variant' => 'outline', 'class' => 'mt-7', 'attrs' => ['data-buscar-cnpj' => true, 'title' => 'Preenche os dados da empresa pela Receita']]),
            new HtmlSeguro('</div>'),
            component('input', $campo('nomeCliente', [
                'label' => 'Nome ou razão social',
                'required' => true,
                'attrs' => $comForm(['maxlength' => 255, 'autocomplete' => 'off', 'data-msg-vazio' => 'Informe o nome do cliente.']),
            ])),
            component('input', $campo('contato', [
                'label' => 'Pessoa de contato',
                'attrs' => $comForm(['maxlength' => 45]),
            ])),
            component('input', $campo('telefone', ['label' => 'Telefone', 'type' => 'tel', 'attrs' => $comForm(['data-mascara' => 'telefone', 'maxlength' => 20])])),
            component('input', $campo('celular', ['label' => 'Celular', 'type' => 'tel', 'attrs' => $comForm(['data-mascara' => 'telefone', 'maxlength' => 20])])),
            new HtmlSeguro('<div class="sm:col-span-2">'),
            component('input', $campo('email', [
                'label' => 'E-mail',
                'type' => 'email',
                'attrs' => $comForm(['maxlength' => 100, 'pattern' => '[^@\\s]+@[^@\\s]+\\.[^@\\s]+', 'data-msg-invalido' => 'Informe um e-mail válido, como nome@empresa.com.br.']),
            ])),
            new HtmlSeguro('</div>'),

            component('input', $campo('cep', ['label' => 'CEP', 'attrs' => $comForm(['data-mascara' => 'cep', 'inputmode' => 'numeric', 'maxlength' => 9, 'autocomplete' => 'off'])])),
            new HtmlSeguro('<div class="hidden sm:block" aria-hidden="true"></div>'),
            component('input', $campo('rua', ['label' => 'Rua', 'attrs' => $comForm(['maxlength' => 70])])),
            new HtmlSeguro('<div class="grid grid-cols-2 gap-4">'),
            component('input', $campo('numero', ['label' => 'Número', 'attrs' => $comForm(['maxlength' => 15])])),
            component('input', $campo('complemento', ['label' => 'Complemento', 'attrs' => $comForm(['maxlength' => 45])])),
            new HtmlSeguro('</div>'),
            component('input', $campo('bairro', ['label' => 'Bairro', 'attrs' => $comForm(['maxlength' => 45])])),
            new HtmlSeguro('<div class="grid grid-cols-[minmax(0,1fr)_7rem] gap-4">'),
            component('input', $campo('cidade', ['label' => 'Cidade', 'attrs' => $comForm(['maxlength' => 45])])),
            component('select', [
                'name' => 'estado',
                'id' => 'cliente-rapido-estado',
                'label' => 'UF',
                'placeholder' => '—',
                'options' => array_combine(array_keys(ufsDoBrasil()), array_keys(ufsDoBrasil())),
                'attrs' => $comForm([]),
            ]),
            new HtmlSeguro('</div>'),
            new HtmlSeguro('<p class="text-caption text-muted sm:col-span-2">A senha da área do cliente começa com o CPF ou CNPJ, só os números. Só o nome é obrigatório.</p>'),
            new HtmlSeguro('</div>'),
        ],
        'footer' => [
            component('button', ['label' => 'Cancelar', 'variant' => 'ghost', 'attrs' => ['data-modal-fechar' => true]]),
            component('button', ['label' => 'Cadastro completo', 'variant' => 'link', 'href' => site_url('clientes/adicionar'), 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]),
            component('button', ['label' => 'Cadastrar cliente', 'icon' => 'save', 'type' => 'submit', 'attrs' => ['form' => $idForm, 'data-rotulo-carregando' => 'Salvando…']]),
        ],
    ]) ?>
</div>
