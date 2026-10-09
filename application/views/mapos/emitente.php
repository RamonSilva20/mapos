<?php
/**
 * Emitente (#2846): os dados da empresa que saem nas impressões, nos e-mails
 * e no PIX, num formulário só, no padrão dos formulários da v5 (#2851).
 *
 * - Sem emitente cadastrado, o mesmo formulário cadastra; com, edita (o id
 *   nunca vem do POST).
 * - O logo é opcional (PNG, JPG ou BMP até 2 MB); enviar outro troca só o
 *   arquivo dele.
 * - O CEP preenche o endereço (módulo usuarios/formulario, o mesmo dos
 *   clientes).
 *
 * @var object|null           $emitente
 * @var array<string, string> $valores
 * @var array<string, string> $erros
 */
$erroGeral = $erros['_geral'] ?? null;
$errosDeCampo = array_diff_key($erros, ['_geral' => true]);
$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';
$grade = 'mt-4 grid gap-4 sm:grid-cols-2';
$campo = static fn (string $nome, array $props) => $props + [
    'name' => $nome,
    'value' => ($valores[$nome] ?? '') !== '' ? $valores[$nome] : null,
    'error' => $errosDeCampo[$nome] ?? null,
];
$logo = (string) ($emitente->url_logo ?? '');
?>
<div class="flex max-w-4xl flex-col gap-4 pt-2 pb-8" <?= js_module('usuarios/formulario') ?>>
    <header>
        <h1 class="font-display text-heading-xl text-text">Emitente</h1>
        <p class="text-caption text-muted">Os dados da sua empresa nas impressões de OS e vendas, nos e-mails e no PIX.</p>
    </header>

    <?php if ($emitente === null) { ?>
        <?= component('alert', ['variant' => 'info', 'message' => 'Nenhum emitente cadastrado ainda. Preencha os dados para as impressões e o PIX saírem completos.']) ?>
    <?php } ?>
    <?php if ($erroGeral !== null) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => $erroGeral]) ?>
    <?php } elseif ($errosDeCampo !== []) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => count($errosDeCampo) === 1 ? 'Confira o campo destacado.' : 'Confira os ' . count($errosDeCampo) . ' campos destacados.']) ?>
    <?php } ?>

    <form method="post" action="<?= e(site_url('mapos/emitente')) ?>" enctype="multipart/form-data" novalidate class="flex flex-col gap-4" <?= js_module('formulario/padrao') ?>>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-empresa">
            <h2 id="secao-empresa" class="text-heading-sm text-text">Empresa</h2>
            <div class="<?= e($grade) ?>">
                <div class="sm:col-span-2">
                    <?= component('input', $campo('nome', ['label' => 'Razão social', 'required' => true, 'autocomplete' => 'organization', 'attrs' => ['maxlength' => 255, 'data-msg-vazio' => 'Informe a razão social.']])) ?>
                </div>
                <?= component('input', $campo('cnpj', ['label' => 'CNPJ ou CPF', 'required' => true, 'attrs' => ['data-mascara' => 'documento', 'maxlength' => 45, 'data-msg-vazio' => 'Informe o CNPJ ou CPF.']])) ?>
                <?= component('input', $campo('ie', ['label' => 'Inscrição estadual', 'attrs' => ['maxlength' => 50]])) ?>
                <?= component('input', $campo('telefone', ['label' => 'Telefone', 'type' => 'tel', 'required' => true, 'autocomplete' => 'tel', 'attrs' => ['data-mascara' => 'telefone', 'maxlength' => 20, 'data-msg-vazio' => 'Informe o telefone.']])) ?>
                <?= component('input', $campo('email', ['label' => 'E-mail', 'type' => 'email', 'required' => true, 'autocomplete' => 'email', 'help' => 'Remetente dos e-mails do sistema.', 'attrs' => ['maxlength' => 255, 'data-msg-vazio' => 'Informe o e-mail.']])) ?>
            </div>
        </section>

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-endereco">
            <h2 id="secao-endereco" class="text-heading-sm text-text">Endereço</h2>
            <p class="mt-0.5 text-caption text-muted">Digite o CEP para preencher o resto.</p>
            <div class="<?= e($grade) ?>">
                <?= component('input', $campo('cep', ['label' => 'CEP', 'required' => true, 'autocomplete' => 'postal-code', 'attrs' => ['data-mascara' => 'cep', 'inputmode' => 'numeric', 'maxlength' => 9, 'data-msg-vazio' => 'Informe o CEP.']])) ?>
                <div class="hidden sm:block" aria-hidden="true"></div>
                <?= component('input', $campo('rua', ['label' => 'Rua', 'required' => true, 'autocomplete' => 'address-line1', 'attrs' => ['maxlength' => 70, 'data-msg-vazio' => 'Informe a rua.']])) ?>
                <?= component('input', $campo('numero', ['label' => 'Número', 'required' => true, 'attrs' => ['maxlength' => 15, 'data-msg-vazio' => 'Informe o número.']])) ?>
                <?= component('input', $campo('bairro', ['label' => 'Bairro', 'required' => true, 'attrs' => ['maxlength' => 45, 'data-msg-vazio' => 'Informe o bairro.']])) ?>
                <div class="grid grid-cols-[minmax(0,1fr)_7rem] gap-4">
                    <?= component('input', $campo('cidade', ['label' => 'Cidade', 'required' => true, 'autocomplete' => 'address-level2', 'attrs' => ['maxlength' => 45, 'data-msg-vazio' => 'Informe a cidade.']])) ?>
                    <?= component('select', [
                        'name' => 'estado',
                        'label' => 'UF',
                        'required' => true,
                        'placeholder' => '—',
                        'options' => array_combine(array_keys(ufsDoBrasil()), array_keys(ufsDoBrasil())),
                        'selected' => $valores['estado'] !== '' ? strtoupper($valores['estado']) : null,
                        'error' => $errosDeCampo['estado'] ?? null,
                        'attrs' => ['data-msg-vazio' => 'Escolha a UF.'],
                    ]) ?>
                </div>
            </div>
        </section>

        <section class="<?= e($caixa) ?>" aria-labelledby="secao-logo">
            <h2 id="secao-logo" class="text-heading-sm text-text">Logo</h2>
            <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start">
                <?php if ($logo !== '') { ?>
                    <img src="<?= e($logo) ?>" alt="Logo atual do emitente" class="h-24 w-auto max-w-56 shrink-0 rounded-md border border-border bg-field object-contain p-2">
                <?php } ?>
                <div class="flex min-w-0 flex-1 flex-col gap-1.5">
                    <label for="emitente-logo" class="text-label-md text-text"><?= e($logo !== '' ? 'Trocar o logo' : 'Logo') ?></label>
                    <input type="file" id="emitente-logo" name="userfile" accept=".png,.jpg,.jpeg,.bmp"
                           aria-describedby="emitente-logo-ajuda<?= isset($errosDeCampo['userfile']) ? ' emitente-logo-erro' : '' ?>"<?= isset($errosDeCampo['userfile']) ? ' aria-invalid="true"' : '' ?>
                           class="block w-full rounded-sm border border-input bg-field px-3 py-2 text-body-md text-text file:mr-3 file:rounded-sm file:border-0 file:bg-surface-subtle file:px-3 file:py-1 file:text-label-md file:text-text focus:outline-3 focus:outline-offset-0 focus:outline-ring/50">
                    <p id="emitente-logo-ajuda" class="text-caption text-muted">Opcional. PNG, JPG ou BMP, até 2 MB. Sai no topo das impressões.</p>
                    <?php if (isset($errosDeCampo['userfile'])) { ?>
                        <p id="emitente-logo-erro" class="text-caption text-danger-ink"><?= e($errosDeCampo['userfile']) ?></p>
                    <?php } ?>
                </div>
            </div>
        </section>

        <div class="flex justify-end">
            <?= component('button', ['label' => $emitente === null ? 'Cadastrar emitente' : 'Salvar alterações', 'icon' => 'save', 'type' => 'submit', 'attrs' => ['data-rotulo-carregando' => 'Salvando…']]) ?>
        </div>
    </form>
</div>
