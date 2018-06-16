<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12">
		<div class="card">
			<div class="card-title">
				<h4>
					<?= $button ?>
						<?= ucfirst($this->lang->line('product')); ?>
				</h4>
				<hr>
			</div>
			<div class="card-body">
				<div class="basic-form">
					<form action="<?= $action; ?>" method="post">
						<div class="form-group">
							<label for="descricao">
								<?= ucfirst($this->lang->line('product_name')) ?>
							</label>
							<input type="text" class="form-control" name="descricao" id="descricao" value="<?= $descricao; ?>" />
							<?= form_error('descricao') ?>
						</div>
						<div class="form-group">
							<label for="unidade">
								<?= ucfirst($this->lang->line('product_unity')) ?>
							</label>
							<input type="text" class="form-control" name="unidade" id="unidade" value="<?= $unidade; ?>" />
							<?= form_error('unidade') ?>
						</div>
						<div class="form-group">
							<label for="precoCompra">
								<?= ucfirst($this->lang->line('product_buy_price')) ?>
							</label>
							<input type="text" class="form-control" name="precoCompra" id="precoCompra" value="<?= $precoCompra; ?>" />
							<?= form_error('precoCompra') ?>
						</div>
						<div class="form-group">
							<label for="precoVenda">
								<?= ucfirst($this->lang->line('product_sell_price')) ?>
							</label>
							<input type="text" class="form-control" name="precoVenda" id="precoVenda" value="<?= $precoVenda; ?>" />
							<?= form_error('precoVenda') ?>
						</div>
						<div class="form-group">
							<label for="estoque">
								<?= ucfirst($this->lang->line('product_stock')) ?>
							</label>
							<input type="text" class="form-control" name="estoque" id="estoque" value="<?= $estoque; ?>" />
							<?= form_error('estoque') ?>
						</div>
						<div class="form-group">
							<label for="estoqueMinimo">
								<?= ucfirst($this->lang->line('product_min_stock')) ?>
							</label>
							<input type="text" class="form-control" name="estoqueMinimo" id="estoqueMinimo" value="<?= $estoqueMinimo; ?>" />
							<?= form_error('estoqueMinimo') ?>
						</div>
						<div class="checkbox">
							<label for=""><?= ucfirst($this->lang->line('product_mov_type')) ?></label> <br>
							<label for="saida">
								<input type="checkbox" name="saida" id="saida"  value="1" <?= $saida ? 'checked' : ''; ?> /> 
								<?= ucfirst($this->lang->line('product_out')) ?>
								<?= form_error('saida') ?>
							</label>
							
							<label for="entrada">
								<input type="checkbox" name="entrada" id="entrada" value="1" <?= $entrada ? 'checked' : ''; ?> /> 
								<?= ucfirst($this->lang->line('product_in')) ?>
								<?= form_error('entrada') ?>
							</label>
						</div>

						<br>

						<input type="hidden" name="idProdutos" value="<?= $idProdutos; ?>" />
						<button type="submit" class="btn btn-info">
							<?= $button ?>
						</button>
						<a href="<?= site_url('produtos') ?>" class="btn btn-dark">
							<i class="fa fa-reply"></i>
							<?= $this->lang->line('app_cancel'); ?>
						</a>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>