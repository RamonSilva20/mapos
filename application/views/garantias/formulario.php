<?php
/**
 * Formulário do termo de garantia, para cadastrar e editar (#2845), no padrão
 * dos formulários (#2851): POST comum, validação no navegador e no servidor
 * com o erro em cada campo. O texto é um campo de texto simples (as quebras
 * de linha são mantidas); as impressões o exibem formatado.
 *
 * @var object|null           $garantia  null ao cadastrar
 * @var array<string, string> $valores
 * @var array<string, string> $erros     Por campo; _geral para falha ao salvar
 */
$editando = $garantia !== null;
$erroGeral = $erros['_geral'] ?? null;
$errosDeCampo = array_diff_key($erros, ['_geral' => true]);
?>
<div class="flex max-w-3xl flex-col gap-4 pt-2 pb-8">
    <header>
        <h1 class="font-display text-heading-xl text-text"><?= e($editando ? 'Editar termo de garantia' : 'Novo termo de garantia') ?></h1>
        <p class="text-caption text-muted"><?= e($editando ? 'Termo #' . (int) $garantia->idGarantias . ' · ' . dataBr($garantia->dataGarantia) . ' · ' . $garantia->nome : 'O texto sai na impressão da OS e da venda que usarem este termo.') ?></p>
    </header>

    <?php if ($erroGeral !== null) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => $erroGeral]) ?>
    <?php } elseif ($errosDeCampo !== []) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => count($errosDeCampo) === 1 ? 'Confira o campo destacado.' : 'Confira os ' . count($errosDeCampo) . ' campos destacados.']) ?>
    <?php } ?>

    <form method="post" action="<?= e($editando ? site_url('garantias/editar/' . (int) $garantia->idGarantias) : site_url('garantias/adicionar')) ?>" novalidate class="flex flex-col gap-4" <?= js_module('formulario/padrao') ?>>
        <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">

        <section class="flex flex-col gap-4 rounded-xl border border-border bg-surface p-4 sm:p-6" aria-label="Dados do termo">
            <div class="sm:max-w-xs">
                <?= component('input', [
                    'name' => 'refGarantia',
                    'label' => 'Referência',
                    'help' => 'Nome curto para achar o termo, até 15 caracteres. Ex.: 90 dias.',
                    'value' => $valores['refGarantia'] !== '' ? $valores['refGarantia'] : null,
                    'error' => $errosDeCampo['refGarantia'] ?? null,
                    'attrs' => ['maxlength' => 15, 'autofocus' => ! $editando],
                ]) ?>
            </div>
            <?= component('textarea', [
                'name' => 'textoGarantia',
                'label' => 'Termo de garantia',
                'rows' => 12,
                'required' => true,
                'value' => $valores['textoGarantia'] !== '' ? $valores['textoGarantia'] : null,
                'error' => $errosDeCampo['textoGarantia'] ?? null,
                'help' => 'Cada linha em branco separa um parágrafo na impressão.',
                'attrs' => ['maxlength' => 20000, 'data-msg-vazio' => 'Escreva o texto do termo.'],
            ]) ?>
        </section>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <?= component('button', ['label' => 'Cancelar', 'variant' => 'ghost', 'href' => site_url('garantias')]) ?>
            <?= component('button', ['label' => $editando ? 'Salvar alterações' : 'Cadastrar termo', 'icon' => 'save', 'type' => 'submit', 'attrs' => ['data-rotulo-carregando' => 'Salvando…']]) ?>
        </div>
    </form>
</div>
