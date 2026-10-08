<?php
/**
 * Formulário de cliente, para cadastrar e editar (#2851): o padrão dos
 * formulários da v5.
 *
 * - POST comum, com o token CSRF. O módulo formulario/padrao valida no
 *   navegador com as regras do HTML (required, type, maxlength), aplica as
 *   máscaras (data-mascara) e põe o botão em carregamento; o servidor valida
 *   de novo e, com erro, devolve a tela com os valores e o erro em cada campo.
 * - Seções com título curto; campos em duas colunas a partir de 640px.
 * - Um único button-primary (Salvar), no fim do formulário; Cancelar é ghost.
 * - O módulo clientes/formulario (no wrapper) busca o endereço pelo CEP e os
 *   dados da empresa pelo CNPJ, só preenchendo campos vazios.
 *
 * @var object|null                  $cliente  null ao cadastrar
 * @var array<string, string|bool>   $valores  clienteValoresDoFormulario()
 * @var array<string, string>        $erros    Por campo (error_array()); _geral para falha ao salvar
 */
$editando = $cliente !== null;
$erroGeral = $erros['_geral'] ?? null;
$errosDeCampo = array_diff_key($erros, ['_geral' => true]);

$campo = static fn (string $nome, array $props) => component('input', $props + [
    'name' => $nome,
    'value' => ($valores[$nome] ?? '') !== '' ? $valores[$nome] : null,
    'error' => $errosDeCampo[$nome] ?? null,
]);

$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';
$grade = 'mt-4 grid gap-4 sm:grid-cols-2';
?>
<div class="flex max-w-4xl flex-col gap-4 pt-2 pb-8" <?= js_module('clientes/formulario') ?>>
    <header>
        <h1 class="font-display text-heading-xl text-text"><?= e($editando ? 'Editar cliente' : 'Novo cliente') ?></h1>
        <p class="text-caption text-muted"><?= e($editando ? $cliente->nomeCliente : 'Cliente ou fornecedor. Só o nome é obrigatório.') ?></p>
    </header>

    <?php if ($erroGeral !== null) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => $erroGeral]) ?>
    <?php } elseif ($errosDeCampo !== []) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => count($errosDeCampo) === 1 ? 'Confira o campo destacado.' : 'Confira os ' . count($errosDeCampo) . ' campos destacados.']) ?>
    <?php } ?>

    <form method="post" action="<?= e(current_url()) ?>" novalidate class="flex flex-col gap-4" <?= js_module('formulario/padrao') ?>>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-identificacao">
            <h2 id="secao-identificacao" class="text-heading-sm text-text">Identificação</h2>
            <div class="<?= e($grade) ?>">
                <fieldset class="flex flex-col gap-2 sm:col-span-2">
                    <legend class="text-label-md text-text">Tipo</legend>
                    <div class="flex flex-wrap gap-x-6 gap-y-2">
                        <?= component('radio', ['name' => 'fornecedor', 'value' => '0', 'label' => 'Cliente', 'checked' => ! $valores['fornecedor']]) ?>
                        <?= component('radio', ['name' => 'fornecedor', 'value' => '1', 'label' => 'Fornecedor', 'checked' => (bool) $valores['fornecedor']]) ?>
                    </div>
                </fieldset>

                <div class="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-2">
                    <?= $campo('documento', [
                        'label' => 'CPF ou CNPJ',
                        'help' => 'Com ou sem pontuação.',
                        'attrs' => ['data-mascara' => 'documento', 'maxlength' => 20, 'autocomplete' => 'off'],
                    ]) ?>
                    <?= component('button', ['label' => 'Buscar CNPJ', 'icon' => 'search', 'variant' => 'outline', 'class' => 'mt-7', 'attrs' => ['data-buscar-cnpj' => true, 'title' => 'Preenche os dados da empresa pela Receita']]) ?>
                </div>
                <?= $campo('nomeCliente', [
                    'label' => 'Nome ou razão social',
                    'required' => true,
                    'autocomplete' => 'name',
                    'attrs' => ['maxlength' => 255, 'autofocus' => ! $editando, 'data-msg-vazio' => 'Informe o nome do cliente.'],
                ]) ?>
                <?= $campo('contato', [
                    'label' => 'Pessoa de contato',
                    'help' => 'Em empresas, quem atende pelo cliente.',
                    'attrs' => ['maxlength' => 45],
                ]) ?>
            </div>
        </section>

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-contato">
            <h2 id="secao-contato" class="text-heading-sm text-text">Contato</h2>
            <div class="<?= e($grade) ?>">
                <?= $campo('telefone', ['label' => 'Telefone', 'type' => 'tel', 'autocomplete' => 'tel', 'attrs' => ['data-mascara' => 'telefone', 'maxlength' => 20]]) ?>
                <?= $campo('celular', ['label' => 'Celular', 'type' => 'tel', 'autocomplete' => 'tel', 'attrs' => ['data-mascara' => 'telefone', 'maxlength' => 20]]) ?>
                <?= $campo('email', [
                    'label' => 'E-mail',
                    'type' => 'email',
                    'autocomplete' => 'email',
                    'help' => 'Também é o login da área do cliente.',
                    // O type=email do navegador aceita "a@b"; o pattern exige o ponto no domínio, como o servidor.
                    'attrs' => ['maxlength' => 100, 'pattern' => '[^@\\s]+@[^@\\s]+\\.[^@\\s]+', 'data-msg-invalido' => 'Informe um e-mail válido, como nome@empresa.com.br.'],
                ]) ?>
            </div>
        </section>

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-acesso">
            <h2 id="secao-acesso" class="text-heading-sm text-text">Área do cliente</h2>
            <p class="mt-0.5 text-caption text-muted">Acesso do cliente para acompanhar ordens de serviço, compras e cobranças.</p>
            <div class="<?= e($grade) ?>">
                <div class="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-2">
                    <?= component('input', [
                        'name' => 'senha',
                        'label' => 'Senha',
                        'type' => 'password',
                        'autocomplete' => 'new-password',
                        'error' => $errosDeCampo['senha'] ?? null,
                        'help' => $editando ? 'Deixe em branco para manter a senha atual.' : 'Sem senha, o acesso usa o CPF ou CNPJ, só os números.',
                    ]) ?>
                    <?= component('button', ['label' => 'Mostrar senha', 'icon' => 'eye', 'icon_only' => true, 'variant' => 'ghost', 'class' => 'mt-7', 'attrs' => ['data-mostrar-senha' => 'campo-senha', 'aria-controls' => 'campo-senha', 'aria-pressed' => 'false']]) ?>
                </div>
            </div>
        </section>

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-endereco">
            <h2 id="secao-endereco" class="text-heading-sm text-text">Endereço</h2>
            <p class="mt-0.5 text-caption text-muted">Digite o CEP para preencher o resto.</p>
            <div class="<?= e($grade) ?>">
                <?= $campo('cep', ['label' => 'CEP', 'autocomplete' => 'postal-code', 'attrs' => ['data-mascara' => 'cep', 'inputmode' => 'numeric', 'maxlength' => 9]]) ?>
                <div class="hidden sm:block" aria-hidden="true"></div>
                <?= $campo('rua', ['label' => 'Rua', 'autocomplete' => 'address-line1', 'attrs' => ['maxlength' => 70]]) ?>
                <div class="grid grid-cols-2 gap-4">
                    <?= $campo('numero', ['label' => 'Número', 'attrs' => ['maxlength' => 15]]) ?>
                    <?= $campo('complemento', ['label' => 'Complemento', 'autocomplete' => 'address-line2', 'attrs' => ['maxlength' => 45]]) ?>
                </div>
                <?= $campo('bairro', ['label' => 'Bairro', 'attrs' => ['maxlength' => 45]]) ?>
                <div class="grid grid-cols-[minmax(0,1fr)_7rem] gap-4">
                    <?= $campo('cidade', ['label' => 'Cidade', 'autocomplete' => 'address-level2', 'attrs' => ['maxlength' => 45]]) ?>
                    <?= component('select', [
                        'name' => 'estado',
                        'label' => 'UF',
                        'placeholder' => '—',
                        'options' => array_combine(array_keys(ufsDoBrasil()), array_keys(ufsDoBrasil())),
                        'selected' => $valores['estado'] !== '' ? strtoupper((string) $valores['estado']) : null,
                        'error' => $errosDeCampo['estado'] ?? null,
                        'attrs' => ['autocomplete' => 'address-level1'],
                    ]) ?>
                </div>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <?= component('button', ['label' => 'Cancelar', 'variant' => 'ghost', 'href' => site_url('clientes')]) ?>
            <?= component('button', ['label' => $editando ? 'Salvar alterações' : 'Cadastrar cliente', 'icon' => 'save', 'type' => 'submit', 'attrs' => ['data-rotulo-carregando' => 'Salvando…']]) ?>
        </div>
    </form>
</div>
