<?php
/**
 * Anexos da OS na área do cliente (#2848): miniatura das imagens ou o ícone
 * de arquivo, e o botão de baixar (mine/downloadanexo, que confere se o anexo
 * é de uma OS do cliente).
 *
 * @var list<object> $anexos
 */
if ($anexos === []) {
    return;
}
?>
<section class="flex flex-col gap-3" aria-labelledby="secao-anexos">
    <h2 id="secao-anexos" class="text-heading-sm text-text">Anexos</h2>
    <ul class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4" aria-label="Anexos da OS">
        <?php foreach ($anexos as $indice => $anexo) { ?>
            <?php
            $extensao = strtoupper((string) pathinfo((string) $anexo->anexo, PATHINFO_EXTENSION));
            // Registro sem arquivo no disco: sem link nem miniatura quebrada.
            $ausente = armazenamentoArquivoDoAnexo($anexo) === null;
            $miniatura = ! $ausente && $anexo->thumb && armazenamentoArquivoDoAnexo($anexo, true) !== null ? site_url('mine/anexo/' . (int) $anexo->idAnexos . '/thumb') : null;
            $nome = 'Anexo ' . ($indice + 1) . ($extensao !== '' ? ' (' . $extensao . ')' : '');
            $baixar = site_url('mine/downloadanexo/' . (int) $anexo->idAnexos);
            ?>
            <li class="flex min-w-0 flex-col overflow-hidden rounded-xl border border-border bg-surface">
                <?php if ($ausente) { ?>
                    <div class="flex aspect-[8/5] flex-col items-center justify-center gap-1 bg-surface-subtle p-2 text-center text-muted">
                        <?= icon('triangle-alert', ['class' => 'size-8']) ?>
                        <span class="text-caption">Arquivo não encontrado</span>
                    </div>
                <?php } else { ?>
                <a href="<?= e($baixar) ?>" class="flex aspect-[8/5] items-center justify-center bg-surface-subtle text-muted focus-visible:outline-3 focus-visible:-outline-offset-3 focus-visible:outline-ring">
                    <?php if ($miniatura !== null) { ?>
                        <img src="<?= e(componenteUrl($miniatura)) ?>" alt="<?= e($nome) ?>" loading="lazy" class="size-full object-cover">
                    <?php } else { ?>
                        <?= icon('file', ['class' => 'size-10']) ?>
                        <span class="sr-only"><?= e('Baixar ' . $nome) ?></span>
                    <?php } ?>
                </a>
                <?php } ?>
                <div class="flex items-center justify-between gap-1 p-2 pl-3">
                    <span class="min-w-0 truncate text-caption text-muted"><?= e($nome) ?></span>
                    <?php if (! $ausente) { ?>
                        <?= component('button', ['label' => 'Baixar ' . $nome, 'icon' => 'download', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => $baixar]) ?>
                    <?php } ?>
                </div>
            </li>
        <?php } ?>
    </ul>
</section>
