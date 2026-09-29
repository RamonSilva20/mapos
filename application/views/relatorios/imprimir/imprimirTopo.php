<?php if ($emitente): ?>
    <div>
        <br>
        <div style="width: 50%; float: left" class="float-left col-md-3">
            <?php if (file_exists(convertUrlToUploadsPath($emitente->url_logo))) { ?>
                <img style="width: 150px" src="<?= esc_img_src(convertUrlToUploadsPath($emitente->url_logo)) ?>" alt="<?= esc($emitente->nome) ?>"><br><br>
            <?php } else { ?>
                <div style="width: 150px;"><p></p></div>
            <?php } ?>
        </div>
        <div style="float: right">
            <b>EMPRESA: </b> <?= esc($emitente->nome) ?> <b>CNPJ: </b> <?= esc($emitente->cnpj) ?><br>
            <b>ENDEREÇO: </b> <?= esc($emitente->rua) ?>, <?= esc($emitente->numero) ?>, <?= esc($emitente->bairro) ?>, <?= esc($emitente->cidade) ?> - <?= esc($emitente->uf) ?> <br>

            <?php if (isset($title)): ?>
                <b>RELATÓRIO: </b> <?= esc($title) ?> <br>
            <?php endif ?>

            <?php if (isset($dataInicial)): ?>
                <b>DATA INICIAL: </b> <?= esc($dataInicial) ?>
            <?php endif ?>

            <?php if (isset($dataFinal)): ?>
                <b>DATA FINAL: </b> <?= esc($dataFinal) ?>
            <?php endif ?>
        </div>
    </div>
<?php endif ?>
