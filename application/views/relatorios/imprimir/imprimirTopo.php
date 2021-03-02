<div>
    <br>
    <div style="width: 30%; float: left" class="float-left col-md-3"><br>
        <img style="width: 200px" src="<?= $emitente[0]->url_logo ?>" alt="LOGO">
    </div>
    <div style="float: right; font-size: 10px"><span style="font-size: 10px"><b>Empresa: </b><?= $emitente[0]->nome ?></span><br>
    <span style="font-size: 10px"><b>CNPJ: </b><?= $emitente[0]->cnpj ?></span><br>
    <span style="font-size: 10px"><b>Endereço: </b><?= $emitente[0]->rua ?>, <?= $emitente[0]->numero ?>, <?= $emitente[0]->bairro ?>, <?= $emitente[0]->cidade ?> - <?= $emitente[0]->uf ?></span><br>
    <span style="font-size: 10px"><b>CEP: </b><?= $emitente[0]->cep ?></span><br>
    <span style="font-size: 10px"><b>Email: </b><?= $emitente[0]->email ?></span><br>
    <span style="font-size: 10px"><b>Telefone: </b><?= $emitente[0]->telefone ?></span><br>
  </div>
</div>
