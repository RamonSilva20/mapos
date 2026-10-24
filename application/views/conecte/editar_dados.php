<?php
/**
 * "Meus dados" do cliente (#2848), no padrão dos formulários (#2851): POST
 * comum, validação no navegador e no servidor, erro em cada campo. Os campos
 * são os de conecte/partes/campos_cliente.
 *
 * @var array<string, string|bool> $valores
 * @var array<string, string>      $erros  Por campo; _geral para falha ao salvar
 */
$erroGeral = $erros['_geral'] ?? null;
$errosDeCampo = array_diff_key($erros, ['_geral' => true]);
?>
<div class="flex max-w-4xl flex-col gap-4" <?= js_module('clientes/formulario') ?>>
    <div>
        <?= component('button', ['label' => 'Minha conta', 'icon' => 'chevron-left', 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('mine/conta'), 'class' => '-ml-3']) ?>
    </div>

    <header>
        <h1 class="font-display text-heading-xl text-text">Meus dados</h1>
        <p class="text-caption text-muted">Mantenha o contato em dia para a assistência falar com você.</p>
    </header>

    <?php if ($erroGeral !== null) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => $erroGeral]) ?>
    <?php } elseif ($errosDeCampo !== []) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => count($errosDeCampo) === 1 ? 'Confira o campo destacado.' : 'Confira os ' . count($errosDeCampo) . ' campos destacados.']) ?>
    <?php } ?>

    <form method="post" action="<?= e(site_url('mine/editarDados')) ?>" novalidate class="flex flex-col gap-4" <?= js_module('formulario/padrao') ?>>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">

        <?php $this->load->view('conecte/partes/campos_cliente', ['erros' => $errosDeCampo, 'novo' => false, 'caixa' => 'rounded-xl border border-border bg-surface p-4 sm:p-6']); ?>

        <section class="rounded-xl border border-border bg-surface p-4 sm:p-6" aria-labelledby="secao-senha-atual">
            <h2 id="secao-senha-atual" class="text-heading-sm text-text">Confirmar com a senha atual</h2>
            <p class="mt-0.5 text-caption text-muted">Só é preciso se você for trocar o e-mail ou a senha.</p>
            <div class="mt-4 sm:max-w-sm">
                <?= component('input', ['name' => 'senha_atual', 'label' => 'Senha atual', 'type' => 'password', 'autocomplete' => 'current-password', 'error' => $errosDeCampo['senha_atual'] ?? null, 'attrs' => ['maxlength' => 72]]) ?>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <?= component('button', ['label' => 'Cancelar', 'variant' => 'ghost', 'href' => site_url('mine/conta')]) ?>
            <?= component('button', ['label' => 'Salvar alterações', 'icon' => 'save', 'type' => 'submit', 'attrs' => ['data-rotulo-carregando' => 'Salvando…']]) ?>
        </div>
    </form>
</div>
