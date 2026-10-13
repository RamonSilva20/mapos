<?php if ($emitente): ?>
    <div>
        <br>
        <div style="width: 50%; float: left" class="float-left col-md-3">
            <?php if (file_exists(convertUrlToUploadsPath($emitente->url_logo))) { ?>
                <img style="width: 150px" src="<?= e(convertUrlToUploadsPath($emitente->url_logo)) ?>" alt="<?= e($emitente->nome) ?>"><br><br>
            <?php } else { ?>
                <div style="width: 150px;"><p></p></div>
            <?php } ?>
        </div>
        <div style="float: right">
            <b>EMPRESA: </b> <?= e($emitente->nome) ?> <b>CNPJ: </b> <?= e($emitente->cnpj) ?><br>
            <b>ENDEREÇO: </b> <?= e($emitente->rua) ?>, <?= e($emitente->numero) ?>, <?= e($emitente->bairro) ?>, <?= e($emitente->cidade) ?> - <?= e($emitente->uf) ?> <br>

            <?php if (isset($title)): ?>
                <b>RELATÓRIO: </b> <?= e($title) ?> <br>
            <?php endif ?>

            <?php if (isset($dataInicial)): ?>
                <b>DATA INICIAL: </b> <?= e($dataInicial) ?>
            <?php endif ?>

            <?php if (isset($dataFinal)): ?>
                <b>DATA FINAL: </b> <?= e($dataFinal) ?>
            <?php endif ?>
        </div>
    </div>
<?php endif ?>
