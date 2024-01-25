<?php if ($emitente): ?>
    <div>
        <br>
        <div style="width: 50%; float: left" class="float-left col-md-3">
            <?php if(file_exists(convertUrlToUploadsPath($emitente->url_logo))) { ?>
                <img style="width: 150px" src="<?= convertUrlToUploadsPath($emitente->url_logo) ?>" alt="<?= $emitente->nome ?>"><br><br>
            <?php } else { ?>
                <div style="width: 150px;"><p></p></div>
            <?php } ?>
        </div>
        <div style="float: right">
            <b>EMPRESA: </b> <?= $emitente->nome ?> <b>CNPJ: </b> <?= $emitente->cnpj ?><br>
            <b>ENDEREÇO: </b> <?= $emitente->rua ?>, <?= $emitente->numero ?>, <?= $emitente->bairro ?>, <?= $emitente->cidade ?> - <?= $emitente->uf ?> <br>

            <?php if (isset($title)): ?>
                <b>RELATÓRIO: </b> <?= $title ?> <br>
            <?php endif ?>

            <?php if (isset($dataInicial)): ?>
                <b>DATA INICIAL: </b> <?= $dataInicial ?>
            <?php endif ?>

            <?php if (isset($dataFinal)): ?>
                <b>DATA FINAL: </b> <?= $dataFinal ?>
            <?php endif ?>
        </div>
    </div>
<?php endif ?>
