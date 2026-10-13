<?php
/**
 * Pagamento por PIX na área do cliente (#2848): QR Code e o "copia e cola",
 * gerados no servidor com o total atual (getQrCode() e getPixPayload() dos
 * models de OS e venda). O botão de copiar é do módulo conecte/copiar.
 *
 * @var array{payload: string, qr: string|null, chave: string}|null $pix
 * @var array $totais
 */
if ($pix === null) {
    return;
}
?>
<section class="rounded-xl border border-border bg-surface p-4 sm:p-6" aria-labelledby="secao-pix" <?= js_module('conecte/copiar') ?>>
    <h2 id="secao-pix" class="text-heading-sm text-text">Pagar com PIX</h2>
    <p class="mt-0.5 text-caption text-muted">Leia o QR Code no app do banco ou copie o código.</p>
    <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start">
        <?php if (! empty($pix['qr'])) { ?>
            <img src="<?= e($pix['qr']) ?>" alt="<?= e('QR Code do PIX de ' . dinheiro($totais['total'])) ?>" class="size-44 shrink-0 self-center rounded-md border border-border bg-field p-2 sm:self-start">
        <?php } ?>
        <div class="flex min-w-0 flex-1 flex-col gap-3">
            <dl class="grid grid-cols-2 gap-3">
                <div>
                    <dt class="text-caption text-muted">Valor</dt>
                    <dd class="text-body-md tabular-nums text-text"><?= e(dinheiro($totais['total'])) ?></dd>
                </div>
                <div>
                    <dt class="text-caption text-muted">Chave PIX</dt>
                    <dd class="text-body-md [overflow-wrap:anywhere] text-text"><?= e($pix['chave']) ?></dd>
                </div>
            </dl>
            <?= component('input', [
                'name' => 'pix_copia_cola',
                'id' => 'pix-copia-cola',
                'label' => 'PIX copia e cola',
                'value' => $pix['payload'],
                'readonly' => true,
                'class' => 'font-mono text-caption',
            ]) ?>
            <div>
                <?= component('button', ['label' => 'Copiar código', 'icon' => 'copy', 'variant' => 'outline', 'attrs' => ['data-copiar' => 'pix-copia-cola']]) ?>
            </div>
        </div>
    </div>
</section>
