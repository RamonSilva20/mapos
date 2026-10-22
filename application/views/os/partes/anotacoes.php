<?php
/**
 * Anotações internas da OS (#2842), da mais recente à mais antiga. O autor
 * vem no começo do texto, entre colchetes, como a v4 grava.
 *
 * @var list<object> $anotacoes
 * @var array<string, bool> $pode
 */
if ($anotacoes === []) { ?>
    <?= component('empty-state', [
        'title' => 'Nenhuma anotação',
        'message' => 'Anotações internas (o cliente não vê) ficam registradas aqui com a data.',
        'icon' => 'pencil',
    ]) ?>
<?php return;
}
?>
<ol class="flex flex-col divide-y divide-border rounded-xl border border-border bg-surface" aria-label="Anotações da OS">
    <?php foreach ($anotacoes as $anotacao) { ?>
        <?php
        preg_match('/^\[([^\]]*)\]\s*(.*)$/s', (string) $anotacao->anotacao, $partes);
        $autor = $partes[1] ?? null;
        $texto = $partes[2] ?? (string) $anotacao->anotacao;
        $quando = $anotacao->data_hora ? date('d/m/Y \à\s H:i', strtotime((string) $anotacao->data_hora)) : '';
        ?>
        <li class="flex items-start justify-between gap-3 p-4">
            <div class="min-w-0">
                <p class="text-body-md whitespace-pre-line [overflow-wrap:anywhere] text-text"><?= e($texto) ?></p>
                <p class="mt-1 text-caption text-muted"><?= e(trim(($autor ? $autor . ' · ' : '') . $quando, ' ·')) ?></p>
            </div>
            <?php if ($pode['editar']) { ?>
                <?= component('button', [
                    'label' => 'Excluir anotação de ' . $quando,
                    'icon' => 'trash-2',
                    'icon_only' => true,
                    'variant' => 'ghost',
                    'size' => 'sm',
                    'attrs' => ['data-modal-abrir' => 'excluir-anotacao', 'data-valor-id' => (string) $anotacao->idAnotacoes, 'data-valor-nome' => mb_strimwidth($texto, 0, 60, '…', 'UTF-8')],
                ]) ?>
            <?php } ?>
        </li>
    <?php } ?>
</ol>
