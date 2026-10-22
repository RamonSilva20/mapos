<?php
/**
 * Atualização do Map-OS (#2871): versão atual, última release do GitHub, o
 * changelog dela e o botão de aplicar (POST com CSRF e a tag, confirmado em
 * modal). O changelog é texto do GitHub e sai escapado.
 *
 * @var string      $versao_atual
 * @var array|null  $release         Github_updater::ultimaRelease()
 * @var bool        $tem_atualizacao
 * @var list<string> $problemas       Requisitos que faltam no servidor
 * @var string|null $erro
 */
$caixa = 'rounded-xl border border-border bg-surface p-4 sm:p-6';
$notas = $release !== null ? trim($release['notas']) : '';
?>
<div class="flex max-w-3xl flex-col gap-4 pt-2 pb-8">
    <header>
        <h1 class="font-display text-heading-xl text-text">Atualizar o Map-OS</h1>
        <p class="text-caption text-muted">Versão instalada: <?= e($versao_atual) ?></p>
    </header>

    <?php if ($erro !== null) { ?>
        <?= component('alert', ['variant' => 'danger', 'message' => $erro]) ?>
    <?php } elseif ($release === null) { ?>
        <?= component('empty-state', ['icon' => 'package', 'title' => 'Nenhuma versão publicada', 'message' => 'O GitHub ainda não tem uma release com tag para atualizar.']) ?>
    <?php } elseif (! $tem_atualizacao) { ?>
        <?= component('empty-state', ['icon' => 'circle-check', 'title' => 'O Map-OS está atualizado', 'message' => 'A versão mais recente publicada é a ' . $release['versao'] . '.']) ?>
    <?php } else { ?>
        <section class="<?= e($caixa) ?>" aria-labelledby="secao-versao">
            <div class="flex flex-wrap items-center gap-2">
                <h2 id="secao-versao" class="text-heading-sm text-text">Versão <?= e($release['versao']) ?></h2>
                <?php if ($release['prerelease']) { ?>
                    <?= component('pill-status', ['label' => 'Pré-release', 'variant' => 'warning']) ?>
                <?php } ?>
            </div>
            <?php if ($release['publicada_em'] !== '') { ?>
                <p class="mt-1 text-caption text-muted">Publicada em <?= e(dataBr(substr($release['publicada_em'], 0, 10))) ?></p>
            <?php } ?>

            <h3 class="mt-4 text-label-md text-text">O que mudou</h3>
            <?php if ($notas !== '') { ?>
                <pre class="mt-2 max-h-96 overflow-y-auto rounded-md border border-border bg-surface-subtle p-3 font-sans text-caption whitespace-pre-wrap text-text"><?= e(mb_strimwidth($notas, 0, 8000, '…')) ?></pre>
            <?php } else { ?>
                <p class="mt-2 text-caption text-muted">Esta release não tem descrição.</p>
            <?php } ?>

            <?php foreach ($problemas as $problema) { ?>
                <div class="mt-4"><?= component('alert', ['variant' => 'warning', 'message' => $problema]) ?></div>
            <?php } ?>

            <ul class="mt-4 list-disc space-y-1 pl-5 text-caption text-muted">
                <li>O zip da release é baixado do GitHub e só é aplicado se o SHA256 conferir com o publicado.</li>
                <li>Os arquivos substituídos ficam guardados em <code>application/cache/atualizacao</code>; se algo falhar, tudo volta ao que era.</li>
                <li>O <code>.env</code>, os uploads (anexos, arquivos, logos e fotos) e os logs não são alterados.</li>
                <li>Faça um backup do banco antes, e depois clique em "Atualizar banco de dados".</li>
            </ul>

            <div class="mt-4 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <?= component('button', ['label' => 'Voltar', 'variant' => 'ghost', 'href' => site_url('mapos/configurar?aba=sistema')]) ?>
                <?= component('button', ['label' => 'Atualizar para a ' . $release['versao'], 'icon' => 'download', 'disabled' => $problemas !== [], 'attrs' => ['data-modal-abrir' => 'atualizar-mapos']]) ?>
            </div>
        </section>

        <form id="form-atualizar-mapos" method="post" action="<?= e(site_url('mapos/atualizarMapos')) ?>" hidden>
            <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
            <input type="hidden" name="versao" value="<?= e($release['tag']) ?>">
        </form>
        <?= component('modal-confirm', ['id' => 'atualizar-mapos', 'title' => 'Atualizar para a ' . $release['versao'] . '?', 'message' => 'Os arquivos do sistema são substituídos pelos da release. Confirme que você fez o backup do banco.', 'confirm_label' => 'Atualizar', 'icon' => 'triangle-alert', 'confirm_attrs' => ['form' => 'form-atualizar-mapos']]) ?>
    <?php } ?>
</div>
