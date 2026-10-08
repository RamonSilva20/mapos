<?php
/**
 * Anexos da OS (#2842): miniatura das imagens (gerada no upload) ou um ícone
 * de arquivo, com os links de abrir e baixar. Excluir abre o modal-confirm
 * "excluir-anexo" da tela.
 *
 * @var list<object> $anexos
 * @var array<string, bool> $pode
 */
if ($anexos === []) { ?>
    <?= component('empty-state', [
        'title' => 'Nenhum anexo',
        'message' => $pode['editar'] ? 'Fotos do equipamento, laudos e notas fiscais enviados pelo formulário acima aparecem aqui.' : 'Esta OS não tem anexos.',
        'icon' => 'paperclip',
    ]) ?>
<?php return;
}
?>
<ul class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4" aria-label="Anexos da OS">
    <?php foreach ($anexos as $indice => $anexo) { ?>
        <?php
        $extensao = strtoupper((string) pathinfo((string) $anexo->anexo, PATHINFO_EXTENSION));
        $endereco = rtrim((string) $anexo->url, '/') . '/' . rawurlencode((string) $anexo->anexo);
        $miniatura = $anexo->thumb ? rtrim((string) $anexo->url, '/') . '/thumbs/' . rawurlencode((string) $anexo->thumb) : null;
        $nome = 'Anexo ' . ($indice + 1) . ($extensao !== '' ? ' (' . $extensao . ')' : '');
        ?>
        <li class="flex min-w-0 flex-col overflow-hidden rounded-xl border border-border bg-surface">
            <a href="<?= e(componenteUrl($endereco)) ?>" target="_blank" rel="noopener" class="flex aspect-[8/5] items-center justify-center bg-surface-subtle text-muted focus-visible:outline-3 focus-visible:-outline-offset-3 focus-visible:outline-ring/50">
                <?php if ($miniatura !== null) { ?>
                    <img src="<?= e(componenteUrl($miniatura)) ?>" alt="<?= e($nome) ?>" loading="lazy" class="size-full object-cover">
                <?php } else { ?>
                    <?= icon('file', ['class' => 'size-10']) ?>
                    <span class="sr-only"><?= e('Abrir ' . $nome) ?></span>
                <?php } ?>
            </a>
            <div class="flex items-center justify-between gap-1 p-2 pl-3">
                <span class="min-w-0 truncate text-caption text-muted"><?= e($nome) ?></span>
                <div class="flex shrink-0">
                    <?= component('button', ['label' => 'Baixar ' . $nome, 'icon' => 'download', 'icon_only' => true, 'variant' => 'ghost', 'size' => 'sm', 'href' => site_url('os/downloadanexo/' . (int) $anexo->idAnexos)]) ?>
                    <?php if ($pode['editar']) { ?>
                        <?= component('button', [
                            'label' => 'Excluir ' . $nome,
                            'icon' => 'trash-2',
                            'icon_only' => true,
                            'variant' => 'ghost',
                            'size' => 'sm',
                            'attrs' => ['data-modal-abrir' => 'excluir-anexo', 'data-valor-id' => (string) $anexo->idAnexos, 'data-valor-nome' => $nome],
                        ]) ?>
                    <?php } ?>
                </div>
            </div>
        </li>
    <?php } ?>
</ul>
