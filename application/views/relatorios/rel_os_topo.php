<table class="table table-mapos table-condensed">
    <tr>
        <td style="width: 180px">
            <img style="width: 150px;" src="<?=$em_logo?>">
        </td>

        <td><span style="font-size: 15px"><b> <?php echo $emitente[0]->nome; ?></b></span></br>
<i class="fas fa-fingerprint" style="margin:5px 1px"></i> <?php echo $emitente[0]->cnpj; ?></br>
<i class="fas fa-map-marker-alt" style="margin:4px 3px"></i> <?php echo $emitente[0]->rua . ', ' . $emitente[0]->numero . ' - ' . $emitente[0]->bairro . ' - ' . $emitente[0]->cidade . ' - ' . $emitente[0]->uf; ?></br>
<i class="fas fa-map-marker-alt" style="margin:4px 3px"></i> <?= 'CEP: ' . $emitente[0]->cep; ?><br>
<i class="fas fa-envelope" style="margin:5px 1px"></i> <?php echo $emitente[0]->email; ?></br>
<i class="fas fa-phone-alt" style="margin:5px 1px"></i> <?php echo $emitente[0]->telefone; ?></td>
    </tr>
</table>



