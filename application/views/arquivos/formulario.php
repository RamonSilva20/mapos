<?php
/**
 * Formulário de arquivo, para enviar e editar (#2845), no padrão dos
 * formulários (#2851): POST comum com o token CSRF, validação no navegador e
 * no servidor com o erro em cada campo. Ao enviar, o formulário leva o
 * arquivo (multipart); na edição só mudam nome, descrição e data, e o arquivo
 * aparece para baixar.
 *
 * @var object|null           $arquivo  null ao enviar
 * @var array<string, string> $valores  nome, descricao e data (AAAA-MM-DD)
 * @var array<string, string> $erros    Por campo; _geral para falha ao salvar; userfile para o envio
 */
$editando = $arquivo !== null;
$erroGeral = $erros['_geral'] ?? null;
$errosDeCampo = array_diff_key($erros, ['_geral' => true]);
$erroArquivo = $errosDeCampo['userfile'] ?? null;
?>
<div class="flex max-w-3xl flex-col gap-4 pt-2 pb-8">
    <header>
        <h1 class="font-display text-heading-xl text-text"><?= e($editando ? 'Editar arquivo' : 'Enviar arquivo') ?></h1>
        <p class="text-caption text-muted"><?= e($editando ? $arquivo->documento : 'TXT, JPG, GIF, PNG ou PDF. Imagens de até 3000 × 2000 px.') ?></p>
    </header>

    <?php if ($erroGeral !== null) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => $erroGeral]) ?>
    <?php } elseif ($errosDeCampo !== []) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => count($errosDeCampo) === 1 ? 'Confira o campo destacado.' : 'Confira os ' . count($errosDeCampo) . ' campos destacados.']) ?>
    <?php } ?>

    <form method="post" action="<?= e($editando ? site_url('arquivos/editar/' . (int) $arquivo->idDocumentos) : site_url('arquivos/adicionar')) ?>"<?= $editando ? '' : ' enctype="multipart/form-data"' ?> novalidate class="flex flex-col gap-4" <?= js_module('formulario/padrao') ?>>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">

        <section class="grid gap-4 rounded-xl border border-border bg-surface p-4 sm:grid-cols-[minmax(0,1fr)_12rem] sm:p-6" aria-label="Dados do arquivo">
            <?= component('input', [
                'name' => 'nome',
                'label' => 'Nome',
                'required' => true,
                'value' => $valores['nome'] !== '' ? $valores['nome'] : null,
                'error' => $errosDeCampo['nome'] ?? null,
                'attrs' => ['maxlength' => 70, 'autofocus' => ! $editando, 'data-msg-vazio' => 'Informe o nome do arquivo.'],
            ]) ?>
            <?= component('input', [
                'name' => 'data',
                'label' => 'Data',
                'type' => 'date',
                'value' => $valores['data'] !== '' ? $valores['data'] : null,
                'error' => $errosDeCampo['data'] ?? null,
            ]) ?>
            <div class="sm:col-span-2">
                <?= component('textarea', [
                    'name' => 'descricao',
                    'label' => 'Descrição',
                    'rows' => 3,
                    'value' => $valores['descricao'] !== '' ? $valores['descricao'] : null,
                    'error' => $errosDeCampo['descricao'] ?? null,
                    'attrs' => ['maxlength' => 2000],
                ]) ?>
            </div>

            <div class="sm:col-span-2">
                <?php if ($editando) { ?>
                    <p class="text-label-md text-text">Arquivo</p>
                    <p class="mt-1 text-body-md [overflow-wrap:anywhere] text-text"><?= e($arquivo->file . ' · ' . tamanhoDeArquivo($arquivo->tamanho)) ?></p>
                    <div class="mt-2">
                        <?= component('button', ['label' => 'Baixar', 'icon' => 'download', 'variant' => 'outline', 'size' => 'sm', 'href' => site_url('arquivos/download/' . (int) $arquivo->idDocumentos)]) ?>
                    </div>
                <?php } else { ?>
                    <div class="flex min-w-0 flex-col gap-1.5">
                        <label for="arquivo-envio" class="text-label-md text-text">Arquivo <span class="text-danger-ink" aria-hidden="true">*</span></label>
                        <input type="file" id="arquivo-envio" name="userfile" required
                               accept=".txt,.jpg,.jpeg,.gif,.png,.pdf"
                               aria-describedby="arquivo-envio-ajuda<?= $erroArquivo !== null ? ' arquivo-envio-erro' : '' ?>"
                               <?= $erroArquivo !== null ? 'aria-invalid="true"' : '' ?>
                               data-msg-vazio="Escolha o arquivo."
                               class="block w-full rounded-sm border border-input bg-field px-3 py-2 text-body-md text-text file:mr-3 file:rounded-sm file:border-0 file:bg-surface-subtle file:px-3 file:py-1 file:text-label-md file:text-text focus:outline-3 focus:outline-offset-0 focus:outline-ring">
                        <p id="arquivo-envio-ajuda" class="text-caption text-muted">TXT, JPG, GIF, PNG ou PDF.</p>
                        <?php if ($erroArquivo !== null) { ?>
                            <p id="arquivo-envio-erro" class="flex items-start gap-1.5 text-caption text-danger-ink"><?= icon('circle-alert', ['class' => 'mt-0.5 size-4']) ?><span><?= e($erroArquivo) ?></span></p>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <?= component('button', ['label' => 'Cancelar', 'variant' => 'ghost', 'href' => site_url('arquivos')]) ?>
            <?= component('button', ['label' => $editando ? 'Salvar alterações' : 'Enviar arquivo', 'icon' => $editando ? 'save' : 'upload', 'type' => 'submit', 'attrs' => ['data-rotulo-carregando' => $editando ? 'Salvando…' : 'Enviando…']]) ?>
        </div>
    </form>
</div>
