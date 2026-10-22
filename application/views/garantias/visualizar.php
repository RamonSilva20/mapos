<?php
/**
 * Ficha do termo de garantia (#2845): referência, responsável, data e o texto
 * como sai na impressão. "Editar termo" é a ação principal, na topbar;
 * imprimir é secundária.
 *
 * @var object $result  Termo, com nome, telefone e e-mail do responsável
 * @var array{editar: bool, excluir: bool} $pode
 */
$nome = trim((string) $result->refGarantia) !== '' ? $result->refGarantia : 'Termo #' . (int) $result->idGarantias;
$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';
$dados = array_filter([
    'Código' => '#' . (int) $result->idGarantias,
    'Data' => dataBr($result->dataGarantia),
    'Responsável' => (string) $result->nome,
    'Telefone' => (string) $result->telefone,
    'E-mail' => (string) $result->email,
], static fn ($valor) => trim($valor) !== '');
?>
<div class="flex flex-col gap-4 pt-2 pb-8">
    <div>
        <?= component('button', ['label' => 'Termos de garantia', 'icon' => 'chevron-left', 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('garantias'), 'class' => '-ml-3']) ?>
    </div>

    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h1 class="font-display text-heading-xl [overflow-wrap:anywhere] text-text"><?= e($nome) ?></h1>
            <p class="text-caption text-muted">Termo de garantia</p>
        </div>
        <?= component('button', ['label' => 'Imprimir', 'icon' => 'printer', 'variant' => 'outline', 'href' => site_url('garantias/imprimir/' . (int) $result->idGarantias), 'attrs' => ['target' => '_blank', 'rel' => 'noopener']]) ?>
    </header>

    <div class="grid gap-4 lg:grid-cols-3">
        <section class="<?= e($caixa) ?>" aria-labelledby="secao-dados">
            <h2 id="secao-dados" class="text-heading-sm text-text">Dados</h2>
            <dl class="mt-3 flex flex-col gap-3">
                <?php foreach ($dados as $rotulo => $valor) { ?>
                    <div>
                        <dt class="text-caption text-muted"><?= e($rotulo) ?></dt>
                        <dd class="text-body-md [overflow-wrap:anywhere] text-text"><?= e($valor) ?></dd>
                    </div>
                <?php } ?>
            </dl>
        </section>
        <section class="<?= e($caixa) ?> lg:col-span-2" aria-labelledby="secao-texto">
            <h2 id="secao-texto" class="text-heading-sm text-text">Texto do termo</h2>
            <div class="mt-3 text-body-md [overflow-wrap:anywhere] text-text"><?= componenteConteudo(osTextoExibicao($result->textoGarantia)) ?></div>
        </section>
    </div>
</div>
