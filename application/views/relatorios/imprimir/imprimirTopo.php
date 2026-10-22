<?php if ($emitente) : ?>
    <table class="w-full">
        <tr>
            <td class="w-1/3 align-top">
                <?php if (file_exists(convertUrlToUploadsPath($emitente->url_logo))) : ?>
                    <img class="w-36" src="<?= e(convertUrlToUploadsPath($emitente->url_logo)) ?>" alt="<?= e($emitente->nome) ?>">
                <?php endif; ?>
            </td>
            <td class="align-top text-right text-xs">
                <b>EMPRESA:</b> <?= e($emitente->nome) ?> <b>CNPJ:</b> <?= e($emitente->cnpj) ?><br>
                <b>ENDEREÇO:</b> <?= e($emitente->rua) ?>, <?= e($emitente->numero) ?>, <?= e($emitente->bairro) ?>, <?= e($emitente->cidade) ?> - <?= e($emitente->uf) ?><br>
                <?php if (isset($title)) : ?>
                    <b>RELATÓRIO:</b> <?= e($title) ?><br>
                <?php endif; ?>
                <?php if (isset($dataInicial)) : ?>
                    <b>DATA INICIAL:</b> <?= e($dataInicial) ?>
                <?php endif; ?>
                <?php if (isset($dataFinal)) : ?>
                    <b>DATA FINAL:</b> <?= e($dataFinal) ?>
                <?php endif; ?>
            </td>
        </tr>
    </table>
<?php endif; ?>
