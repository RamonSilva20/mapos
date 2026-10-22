<?php
/**
 * PIX da OS (#2842): QR Code e o código "copia e cola", os dois gerados no
 * servidor com o total atual (Os_model::getQrCode() e getPixPayload()). Na
 * v4 o copia e cola saía da imagem do QR, decodificada no navegador pelo
 * jsQR (carregado do rawgit).
 *
 * @var array{payload: string, qr: string|null, chave: string}|null $pix
 * @var array{telefone: string, texto: string}|null $whatsapp
 * @var array $totais
 */
if ($pix === null) {
    return;
}
?>
<section class="rounded-xl border border-border bg-surface p-4 sm:p-6" aria-labelledby="secao-pix">
    <h2 id="secao-pix" class="text-heading-sm text-text">Pagamento por PIX</h2>
    <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start">
        <?php if (! empty($pix['qr'])) { ?>
            <img src="<?= e($pix['qr']) ?>" alt="<?= e('QR Code do PIX de ' . dinheiro($totais['total'])) ?>" class="size-44 shrink-0 rounded-md border border-border bg-field p-2">
        <?php } ?>
        <div class="flex min-w-0 flex-1 flex-col gap-3">
            <dl class="grid gap-3 sm:grid-cols-2">
                <div>
                    <dt class="text-caption text-muted">Chave PIX</dt>
                    <dd class="text-body-md [overflow-wrap:anywhere] text-text"><?= e($pix['chave']) ?></dd>
                </div>
                <div>
                    <dt class="text-caption text-muted">Valor</dt>
                    <dd class="text-body-md tabular-nums text-text"><?= e(dinheiro($totais['total'])) ?></dd>
                </div>
            </dl>
            <?= component('input', [
                'name' => 'pix_copia_cola',
                'id' => 'os-pix-payload',
                'label' => 'PIX copia e cola',
                'value' => $pix['payload'],
                'readonly' => true,
                'help' => 'O cliente cola este código no app do banco.',
                'class' => 'font-mono text-caption',
            ]) ?>
            <div class="flex flex-wrap gap-2">
                <?= component('button', ['label' => 'Copiar código', 'icon' => 'copy', 'variant' => 'outline', 'attrs' => ['data-copiar' => 'os-pix-payload']]) ?>
                <?php if ($whatsapp !== null) { ?>
                    <?= component('button', [
                        'label' => 'Enviar pelo WhatsApp',
                        'icon' => 'message-circle',
                        'variant' => 'ghost',
                        'href' => 'https://wa.me/' . $whatsapp['telefone'] . '?text=' . rawurlencode($pix['payload']),
                        'attrs' => ['target' => '_blank', 'rel' => 'noopener'],
                    ]) ?>
                <?php } ?>
            </div>
        </div>
    </div>
</section>
