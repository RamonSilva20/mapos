<div>
    <br>
    <div style="width: 25%; float: left" class="float-left col-md-3">
        <img style="width: 150px" src="<?= $emitente[0]->url_logo ?>" alt=""><br><br>
    </div>
    <div style="float: right">
        <b>EMPRESA: </b> <?= $emitente[0]->nome ?> <b>CNPJ: </b> <?= $emitente[0]->cnpj ?><br>
        <b>ENDEREÇO: </b> <?= $emitente[0]->rua ?>, <?= $emitente[0]->numero ?>, <?= $emitente[0]->bairro ?>, <?= $emitente[0]->cidade ?> - <?= $emitente[0]->uf ?> <br>
        <b>RELATÓRIO: </b> <?= $title ?> <br>
        <b>DATA INICIAL: </b> <?= isset($dataInicial) ? $dataInicial : '' ?> <b>DATA FINAL: </b> <?= isset($dataFinal) ? $dataFinal : '' ?>
    </div>
</div>
