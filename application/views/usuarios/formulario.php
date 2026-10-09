<?php
/**
 * Formulário de usuário, para cadastrar e editar (#2846), no padrão dos
 * formulários da v5 (#2851, views/clientes/formulario.php).
 *
 * - Obrigatórios: nome, CPF, e-mail, telefone, situação, grupo e, ao
 *   cadastrar, a senha (mínimo de 8 caracteres). Endereço e RG são opcionais.
 * - Ao editar, a senha em branco mantém a atual.
 * - O módulo usuarios/formulario (o mesmo dos clientes) preenche o endereço
 *   pelo CEP.
 * - O administrador principal e o próprio usuário logado não podem ser
 *   desativados (a situação fica bloqueada).
 *
 * @var object|null           $usuario  null ao cadastrar
 * @var array<string, string> $valores  usuarioValoresDoFormulario()
 * @var array<string, string> $erros
 * @var array<string, string> $grupos   id => nome
 * @var bool                  $protegido
 */
$editando = $usuario !== null;
$erroGeral = $erros['_geral'] ?? null;
$errosDeCampo = array_diff_key($erros, ['_geral' => true]);

$campo = static fn (string $nome, array $props) => $props + [
    'name' => $nome,
    'value' => ($valores[$nome] ?? '') !== '' ? $valores[$nome] : null,
    'error' => $errosDeCampo[$nome] ?? null,
];

$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';
$grade = 'mt-4 grid gap-4 sm:grid-cols-2';
?>
<div class="flex max-w-4xl flex-col gap-4 pt-2 pb-8" <?= js_module('usuarios/formulario') ?>>
    <header>
        <h1 class="font-display text-heading-xl text-text"><?= e($editando ? 'Editar usuário' : 'Novo usuário') ?></h1>
        <p class="text-caption text-muted"><?= e($editando ? $usuario->nome : 'Quem entra no painel. O grupo define o que a pessoa pode ver e fazer.') ?></p>
    </header>

    <?php if ($erroGeral !== null) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => $erroGeral]) ?>
    <?php } elseif ($errosDeCampo !== []) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => count($errosDeCampo) === 1 ? 'Confira o campo destacado.' : 'Confira os ' . count($errosDeCampo) . ' campos destacados.']) ?>
    <?php } ?>

    <form method="post" action="<?= e(current_url()) ?>" novalidate class="flex flex-col gap-4" <?= js_module('formulario/padrao') ?>>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-dados">
            <h2 id="secao-dados" class="text-heading-sm text-text">Dados pessoais</h2>
            <div class="<?= e($grade) ?>">
                <div class="sm:col-span-2">
                    <?= component('input', $campo('nome', ['label' => 'Nome', 'required' => true, 'autocomplete' => 'name', 'attrs' => ['maxlength' => 80, 'autofocus' => ! $editando, 'data-msg-vazio' => 'Informe o nome.']])) ?>
                </div>
                <?= component('input', $campo('cpf', ['label' => 'CPF', 'required' => true, 'attrs' => ['data-mascara' => 'documento', 'maxlength' => 20, 'inputmode' => 'numeric', 'autocomplete' => 'off', 'data-msg-vazio' => 'Informe o CPF.']])) ?>
                <?= component('input', $campo('rg', ['label' => 'RG', 'attrs' => ['maxlength' => 20]])) ?>
                <?= component('input', $campo('telefone', ['label' => 'Telefone', 'type' => 'tel', 'required' => true, 'autocomplete' => 'tel', 'attrs' => ['data-mascara' => 'telefone', 'maxlength' => 20, 'data-msg-vazio' => 'Informe o telefone.']])) ?>
                <?= component('input', $campo('celular', ['label' => 'Celular', 'type' => 'tel', 'autocomplete' => 'tel', 'attrs' => ['data-mascara' => 'telefone', 'maxlength' => 20]])) ?>
            </div>
        </section>

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-acesso">
            <h2 id="secao-acesso" class="text-heading-sm text-text">Acesso</h2>
            <div class="<?= e($grade) ?>">
                <?= component('input', $campo('email', ['label' => 'E-mail', 'type' => 'email', 'required' => true, 'autocomplete' => 'username', 'help' => 'Usado para entrar no painel.', 'attrs' => ['maxlength' => 80, 'data-msg-vazio' => 'Informe o e-mail.']])) ?>
                <div class="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-2">
                    <?= component('input', [
                        'name' => 'senha',
                        'label' => 'Senha',
                        'type' => 'password',
                        'required' => ! $editando,
                        'autocomplete' => 'new-password',
                        'error' => $errosDeCampo['senha'] ?? null,
                        'help' => $editando ? 'Deixe em branco para manter a senha atual. Mínimo de 8 caracteres.' : 'Mínimo de 8 caracteres.',
                        'attrs' => ['minlength' => USUARIO_SENHA_MINIMA, 'data-msg-vazio' => 'Informe a senha.', 'data-msg-invalido' => 'A senha tem pelo menos 8 caracteres.'],
                    ]) ?>
                    <?= component('button', ['label' => 'Mostrar senha', 'icon' => 'eye', 'icon_only' => true, 'variant' => 'ghost', 'class' => 'mt-7', 'attrs' => ['data-mostrar-senha' => 'campo-senha', 'aria-controls' => 'campo-senha', 'aria-pressed' => 'false']]) ?>
                </div>
                <?= component('select', [
                    'name' => 'permissoes_id',
                    'label' => 'Grupo de permissão',
                    'required' => true,
                    'placeholder' => 'Escolha',
                    'options' => $grupos,
                    'selected' => $valores['permissoes_id'] !== '' ? $valores['permissoes_id'] : null,
                    'error' => $errosDeCampo['permissoes_id'] ?? null,
                    'help' => 'Os grupos ficam em Configurações > Permissões.',
                    'attrs' => ['data-msg-vazio' => 'Escolha o grupo de permissão.'],
                ]) ?>
                <?= component('select', [
                    'name' => 'situacao',
                    'label' => 'Situação',
                    'required' => true,
                    'options' => ['1' => 'Ativo', '0' => 'Inativo'],
                    'selected' => $valores['situacao'] !== '' ? $valores['situacao'] : '1',
                    'error' => $errosDeCampo['situacao'] ?? null,
                    'disabled' => $protegido,
                    'help' => $protegido ? 'Este usuário não pode ser desativado.' : 'Inativo não entra no painel.',
                ]) ?>
                <?php if ($protegido) { ?>
                    <input type="hidden" name="situacao" value="1">
                <?php } ?>
                <?= component('input', $campo('dataExpiracao', ['label' => 'Acesso até', 'type' => 'date', 'help' => 'Opcional. Depois desta data o login é negado.'])) ?>
            </div>
        </section>

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-endereco">
            <h2 id="secao-endereco" class="text-heading-sm text-text">Endereço</h2>
            <p class="mt-0.5 text-caption text-muted">Opcional. Digite o CEP para preencher o resto.</p>
            <div class="<?= e($grade) ?>">
                <?= component('input', $campo('cep', ['label' => 'CEP', 'autocomplete' => 'postal-code', 'attrs' => ['data-mascara' => 'cep', 'inputmode' => 'numeric', 'maxlength' => 9]])) ?>
                <div class="hidden sm:block" aria-hidden="true"></div>
                <?= component('input', $campo('rua', ['label' => 'Rua', 'autocomplete' => 'address-line1', 'attrs' => ['maxlength' => 70]])) ?>
                <?= component('input', $campo('numero', ['label' => 'Número', 'attrs' => ['maxlength' => 15]])) ?>
                <?= component('input', $campo('bairro', ['label' => 'Bairro', 'attrs' => ['maxlength' => 45]])) ?>
                <div class="grid grid-cols-[minmax(0,1fr)_7rem] gap-4">
                    <?= component('input', $campo('cidade', ['label' => 'Cidade', 'autocomplete' => 'address-level2', 'attrs' => ['maxlength' => 45]])) ?>
                    <?= component('select', [
                        'name' => 'estado',
                        'label' => 'UF',
                        'placeholder' => '—',
                        'options' => array_combine(array_keys(ufsDoBrasil()), array_keys(ufsDoBrasil())),
                        'selected' => $valores['estado'] !== '' ? strtoupper($valores['estado']) : null,
                        'error' => $errosDeCampo['estado'] ?? null,
                        'attrs' => ['autocomplete' => 'address-level1'],
                    ]) ?>
                </div>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <?= component('button', ['label' => 'Cancelar', 'variant' => 'ghost', 'href' => site_url('usuarios')]) ?>
            <?= component('button', ['label' => $editando ? 'Salvar alterações' : 'Cadastrar usuário', 'icon' => 'save', 'type' => 'submit', 'attrs' => ['data-rotulo-carregando' => 'Salvando…']]) ?>
        </div>
    </form>
</div>
