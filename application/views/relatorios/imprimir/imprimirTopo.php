<?php if ($emitente[0]) : ?>
    <div>
        <br>
        <div style="width: 50%; float: left" class="float-left col-md-3">
            <img style="width: 150px" src="<?php echo convertUrlToUploadsPath($emitente[0]->url_logo); ?>" alt=""><br><br>
        </div>
        <div style="float: right">
            <b>EMPRESA: </b> <?php echo $emitente[0]->nome; ?> <b>CNPJ: </b> <?php echo $emitente[0]->cnpj; ?><br>
            <b>ENDEREÇO: </b> <?php echo $emitente[0]->rua; ?>, <?php echo $emitente[0]->numero; ?>, <?php echo $emitente[0]->bairro; ?>, <?php echo $emitente[0]->cidade; ?> - <?php echo $emitente[0]->uf; ?> <br>

            <?php if (isset($title)) : ?>
                <b>RELATÓRIO: </b> <?php echo $title; ?> <br>
            <?php endif; ?>

            <?php if (isset($dataInicial)) : ?>
                <b>DATA INICIAL: </b> <?php echo $dataInicial; ?>
            <?php endif; ?>

            <?php if (isset($dataFinal)) : ?>
                <b>DATA FINAL: </b> <?php echo $dataFinal; ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
