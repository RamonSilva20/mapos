<?php
/**
 * Campos do cadastro do cliente, usados no cadastro (conecte/cadastrar, na
 * superfície de entrada) e em "Meus dados" (conecte/editar_dados). Os nomes
 * são os do formulário de cliente do painel (clienteValoresDoFormulario());
 * o cliente não escolhe se é fornecedor.
 *
 * O CEP preenche o endereço pelo módulo clientes/formulario, no wrapper da
 * tela.
 *
 * @var array<string, string|bool> $valores
 * @var array<string, string>      $erros
 * @var bool                       $novo     Cadastro (senha obrigatória)
 * @var string                     $caixa    Classes de cada seção
 * @var string|null                $caixa_primeira  Classes da primeira seção, se diferentes
 */
// Props do input com o valor e o erro do campo, para component('input', $campo(...)).
$campo = static fn (string $nome, array $props) => $props + [
    'name' => $nome,
    'value' => ($valores[$nome] ?? '') !== '' ? $valores[$nome] : null,
    'error' => $erros[$nome] ?? null,
];
$grade = 'mt-4 grid gap-4 sm:grid-cols-2';
?>
<section class="<?= e($caixa_primeira ?? $caixa) ?>" aria-labelledby="secao-identificacao">
    <h2 id="secao-identificacao" class="text-heading-sm text-text">Identificação</h2>
    <div class="<?= e($grade) ?>">
        <div class="sm:col-span-2">
            <?= component('input', $campo('nomeCliente', [
                'label' => 'Nome completo ou razão social',
                'required' => true,
                'autocomplete' => 'name',
                'attrs' => ['maxlength' => 255, 'autofocus' => $novo, 'data-msg-vazio' => 'Informe o seu nome.'],
            ])) ?>
        </div>
        <?= component('input', $campo('documento', [
            'label' => 'CPF ou CNPJ',
            'help' => 'Com ou sem pontuação.',
            'attrs' => ['data-mascara' => 'documento', 'maxlength' => 20, 'inputmode' => 'numeric', 'autocomplete' => 'off'],
        ])) ?>
        <?= component('input', $campo('contato', [
            'label' => 'Pessoa de contato',
            'help' => 'Em empresas, quem fala com a assistência.',
            'attrs' => ['maxlength' => 45],
        ])) ?>
    </div>
</section>

<section class="<?= e($caixa) ?>" aria-labelledby="secao-contato">
    <h2 id="secao-contato" class="text-heading-sm text-text">Contato</h2>
    <div class="<?= e($grade) ?>">
        <div class="sm:col-span-2">
            <?= component('input', $campo('email', [
                'label' => 'E-mail',
                'type' => 'email',
                'required' => true,
                'autocomplete' => 'email',
                'help' => 'É com ele que você entra na área do cliente.',
                // O type=email do navegador aceita "a@b"; o pattern exige o ponto no domínio, como o servidor.
                'attrs' => ['maxlength' => 100, 'inputmode' => 'email', 'pattern' => '[^@\\s]+@[^@\\s]+\\.[^@\\s]+', 'data-msg-vazio' => 'Informe o e-mail.', 'data-msg-invalido' => 'Informe um e-mail válido, como nome@email.com.br.'],
            ])) ?>
        </div>
        <?= component('input', $campo('celular', ['label' => 'Celular', 'type' => 'tel', 'autocomplete' => 'tel', 'attrs' => ['data-mascara' => 'telefone', 'maxlength' => 20]])) ?>
        <?= component('input', $campo('telefone', ['label' => 'Telefone', 'type' => 'tel', 'autocomplete' => 'tel', 'attrs' => ['data-mascara' => 'telefone', 'maxlength' => 20]])) ?>
    </div>
</section>

<section class="<?= e($caixa) ?>" aria-labelledby="secao-endereco">
    <h2 id="secao-endereco" class="text-heading-sm text-text">Endereço</h2>
    <p class="mt-0.5 text-caption text-muted">Opcional. Digite o CEP para preencher o resto.</p>
    <div class="<?= e($grade) ?>">
        <?= component('input', $campo('cep', ['label' => 'CEP', 'autocomplete' => 'postal-code', 'attrs' => ['data-mascara' => 'cep', 'inputmode' => 'numeric', 'maxlength' => 9]])) ?>
        <div class="hidden sm:block" aria-hidden="true"></div>
        <?= component('input', $campo('rua', ['label' => 'Rua', 'autocomplete' => 'address-line1', 'attrs' => ['maxlength' => 70]])) ?>
        <div class="grid grid-cols-2 gap-4">
            <?= component('input', $campo('numero', ['label' => 'Número', 'attrs' => ['maxlength' => 15]])) ?>
            <?= component('input', $campo('complemento', ['label' => 'Complemento', 'autocomplete' => 'address-line2', 'attrs' => ['maxlength' => 45]])) ?>
        </div>
        <?= component('input', $campo('bairro', ['label' => 'Bairro', 'attrs' => ['maxlength' => 45]])) ?>
        <div class="grid grid-cols-[minmax(0,1fr)_7rem] gap-4">
            <?= component('input', $campo('cidade', ['label' => 'Cidade', 'autocomplete' => 'address-level2', 'attrs' => ['maxlength' => 45]])) ?>
            <?= component('select', [
                'name' => 'estado',
                'label' => 'UF',
                'placeholder' => '—',
                'options' => array_combine(array_keys(ufsDoBrasil()), array_keys(ufsDoBrasil())),
                'selected' => ($valores['estado'] ?? '') !== '' ? strtoupper((string) $valores['estado']) : null,
                'error' => $erros['estado'] ?? null,
                'attrs' => ['autocomplete' => 'address-level1'],
            ]) ?>
        </div>
    </div>
</section>

<section class="<?= e($caixa) ?>" aria-labelledby="secao-senha">
    <h2 id="secao-senha" class="text-heading-sm text-text"><?= e($novo ? 'Senha' : 'Trocar a senha') ?></h2>
    <p class="mt-0.5 text-caption text-muted"><?= e($novo ? 'Pelo menos 8 caracteres.' : 'Deixe em branco para manter a senha atual.') ?></p>
    <div class="<?= e($grade) ?>">
        <div class="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-2">
            <?= component('input', [
                'name' => 'senha',
                'label' => $novo ? 'Senha' : 'Nova senha',
                'type' => 'password',
                'required' => $novo,
                'autocomplete' => 'new-password',
                'error' => $erros['senha'] ?? null,
                'attrs' => ['minlength' => 8, 'maxlength' => 72, 'data-msg-vazio' => 'Crie uma senha.', 'data-msg-invalido' => 'A senha precisa de pelo menos 8 caracteres.'],
            ]) ?>
            <?= component('button', ['label' => 'Mostrar senha', 'icon' => 'eye', 'icon_only' => true, 'variant' => 'ghost', 'class' => 'mt-7', 'attrs' => ['data-mostrar-senha' => 'campo-senha', 'aria-controls' => 'campo-senha', 'aria-pressed' => 'false']]) ?>
        </div>
        <?= component('input', [
            'name' => 'senha_confirmacao',
            'label' => 'Repita a senha',
            'type' => 'password',
            'required' => $novo,
            'autocomplete' => 'new-password',
            'error' => $erros['senha_confirmacao'] ?? null,
            'attrs' => ['maxlength' => 72, 'data-msg-vazio' => 'Repita a senha para confirmar.'],
        ]) ?>
    </div>
</section>
