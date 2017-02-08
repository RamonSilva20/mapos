<div class="span12" style="padding: 1%; margin-left: 0">
    <div class="span6" style="margin-left: 0">
        <label for="cliente">Cliente<span class="required">*</span></label>
        <input id="cliente" class="span12" type="text" name="cliente" value="<?=isset($result) ? $result->nomeCliente : '' ?>"  />
        <input id="clientes_id" class="span12" type="hidden" name="clientes_id" value="<?= isset($result->clientes_id)? $result->clientes_id : '' ?>"  />
        <input id="valorTotal" type="hidden" name="valorTotal" value=""  />
    </div>
    <div class="span6">
        <label for="tecnico">Técnico / Responsável<span class="required">*</span></label>
        <input id="tecnico" class="span12" type="text" name="tecnico" value="<?=isset($result->nome) ? $result->nome : '' ?>"  />
        <input id="usuarios_id" class="span12" type="hidden" name="usuarios_id" value="<?=isset($result->usuarios_id) ? $result->usuarios_id : ''?>"  />
    </div>
</div>
<div class="span12" style="padding: 1%; margin-left: 0">
    <div class="span3">
        <label for="status">Status<span class="required">*</span></label>
        <?=form_dropdown('status', $lista_status, isset($result->status)? $result->status : 'Orçamento', ['class' => 'span12'])?>     
    </div>
    <div class="span3">
        <label for="dataInicial">Data Inicial<span class="required">*</span></label>
        <input id="dataInicial" class="span12 datepicker" type="text" name="dataInicial" value="<?=isset($result->dataInicial)? date('d/m/Y', strtotime($result->dataInicial)) : '' ?>"  />
    </div>
    <div class="span3">
        <label for="dataFinal">Data Final</label>
        <input id="dataFinal" class="span12 datepicker" type="text" name="dataFinal" value="<?=isset($result->dataFinal)?date('d/m/Y', strtotime($result->dataFinal)) : ''?>"  />
    </div>
    <div class="span3">
        <label for="garantia">Garantia</label>
        <input id="garantia" type="text" class="span12" name="garantia" value="<?=isset($result->garantia)? html_escape($result->garantia) :'' ?>"  />
    </div>
</div>
<div class="span12" style="padding: 1%; margin-left: 0">
    <div class="span6">
        <label for="descricaoProduto">Descrição Produto/Serviço</label>
        <textarea class="span12" name="descricaoProduto" id="descricaoProduto" cols="30" rows="5"><?=isset($result->descricaoProduto)? html_escape($result->descricaoProduto):''?></textarea>
    </div>
    <div class="span6">
        <label for="defeito">Defeito</label>
        <textarea class="span12" name="defeito" id="defeito" cols="30" rows="5"><?=isset($result->defeito)? html_escape($result->defeito) :'' ?></textarea>
    </div>
</div>
<div class="span12" style="padding: 1%; margin-left: 0">
    <div class="span6">
        <label for="observacoes">Observações</label>
        <textarea class="span12" name="observacoes" id="observacoes" cols="30" rows="5"><?=isset($result->observacoes)? html_escape($result->observacoes) :'' ?></textarea>
    </div>
    <div class="span6">
        <label for="laudoTecnico">Laudo Técnico</label>
        <textarea class="span12" name="laudoTecnico" id="laudoTecnico" cols="30" rows="5"><?=isset($result->laudoTecnico)? html_escape($result->laudoTecnico) :'' ?></textarea>
    </div>
</div>
