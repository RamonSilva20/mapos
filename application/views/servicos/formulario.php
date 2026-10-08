<?php
/**
 * Formulário de serviço, para cadastrar e editar (#2841), no padrão dos
 * formulários (#2851): POST comum, validação no navegador e no servidor com
 * o erro em cada campo, preço com a máscara de dinheiro.
 *
 * @var object|null           $servico  null ao cadastrar
 * @var array<string, string> $valores
 * @var array<string, string> $erros    Por campo; _geral para falha ao salvar
 */
$editando = $servico !== null;
$erroGeral = $erros['_geral'] ?? null;
$errosDeCampo = array_diff_key($erros, ['_geral' => true]);
?>
<div class="flex max-w-2xl flex-col gap-4 pt-2 pb-8">
    <header>
        <h1 class="font-display text-heading-xl text-text"><?= e($editando ? 'Editar serviço' : 'Novo serviço') ?></h1>
        <p class="text-caption text-muted"><?= e($editando ? $servico->nome : 'Mão de obra ou serviço cobrado nas ordens de serviço.') ?></p>
    </header>

    <?php if ($erroGeral !== null) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => $erroGeral]) ?>
    <?php } elseif ($errosDeCampo !== []) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => count($errosDeCampo) === 1 ? 'Confira o campo destacado.' : 'Confira os ' . count($errosDeCampo) . ' campos destacados.']) ?>
    <?php } ?>

    <form method="post" action="<?= e(current_url()) ?>" novalidate class="flex flex-col gap-4" <?= js_module('formulario/padrao') ?>>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">

        <section class="grid gap-4 rounded-xl border border-border bg-surface p-4 sm:grid-cols-[minmax(0,1fr)_12rem] sm:p-6" aria-label="Dados do serviço">
            <?= component('input', [
                'name' => 'nome',
                'label' => 'Nome',
                'required' => true,
                'value' => $valores['nome'] !== '' ? $valores['nome'] : null,
                'error' => $errosDeCampo['nome'] ?? null,
                'attrs' => ['maxlength' => 45, 'autofocus' => ! $editando, 'data-msg-vazio' => 'Informe o nome do serviço.'],
            ]) ?>
            <?= component('input', [
                'name' => 'preco',
                'label' => 'Preço (R$)',
                'required' => true,
                'value' => $valores['preco'] !== '' ? $valores['preco'] : null,
                'error' => $errosDeCampo['preco'] ?? null,
                'class' => 'text-right tabular-nums',
                'attrs' => ['data-mascara' => 'dinheiro', 'inputmode' => 'decimal', 'maxlength' => 20, 'data-msg-vazio' => 'Informe o preço.'],
            ]) ?>
            <div class="sm:col-span-2">
                <?= component('input', [
                    'name' => 'descricao',
                    'label' => 'Descrição',
                    'help' => 'Aparece junto do nome na OS. Até 45 caracteres.',
                    'value' => $valores['descricao'] !== '' ? $valores['descricao'] : null,
                    'error' => $errosDeCampo['descricao'] ?? null,
                    'attrs' => ['maxlength' => 45],
                ]) ?>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <?= component('button', ['label' => 'Cancelar', 'variant' => 'ghost', 'href' => site_url('servicos')]) ?>
            <?= component('button', ['label' => $editando ? 'Salvar alterações' : 'Cadastrar serviço', 'icon' => 'save', 'type' => 'submit', 'attrs' => ['data-rotulo-carregando' => 'Salvando…']]) ?>
        </div>
    </form>
</div>
