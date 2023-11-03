<?php if ($emitente): ?>
    <div>
        <br>
        <div style="width: 50%; float: left" class="float-left col-md-3">
            <?php if(file_exists(convertUrlToUploadsPath($emitente->url_logo))) { ?>
                <img style="width: 150px" src="<?= convertUrlToUploadsPath($emitente->url_logo) ?>" alt="<?= $emitente->nome ?>"><br><br>
            <?php } else { ?>
                <svg width="150px" xmlns="http://www.w3.org/2000/svg"> <rect width="150px" height="50px" x="50" y="50" fill="transparent" /></svg>
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
