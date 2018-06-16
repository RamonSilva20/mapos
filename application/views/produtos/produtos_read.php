<div class="col-lg-12 col-md-12 col-sm-12">
	<div class="card">
		<div class="card-title">
			<h4>
				<i class="fa fa-eye"></i>
				<?= $this->lang->line('app_view').' '.ucfirst($this->lang->line('product')); ?>
			</h4>

		</div>
		<div class="card-body">
			<div class="table-responsive">

				<table class="table table-bordered table-striped">
					<tr>
						<td style="width: 30%">
							<?= ucfirst($this->lang->line('product_name')) ?>
						</td>
						<td class="text-left">
							<?= $descricao; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?= ucfirst($this->lang->line('product_unity')) ?>
						</td>
						<td class="text-left">
							<?= $unidade; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?= ucfirst($this->lang->line('product_buy_price')) ?>
						</td>
						<td class="text-left">
							<?= $precoCompra; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?= ucfirst($this->lang->line('product_sell_price')) ?>
						</td>
						<td class="text-left">
							<?= $precoVenda; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?= ucfirst($this->lang->line('product_stock')) ?>
						</td>
						<td class="text-left">
							<?= $estoque; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?= ucfirst($this->lang->line('product_min_stock')) ?>
						</td>
						<td class="text-left">
							<?= $estoqueMinimo; ?>
						</td>
					</tr>
					<tr>
						<td>
						<?= ucfirst($this->lang->line('product_mov_type')).' - '.ucfirst($this->lang->line('product_out')) ?>
						</td>
						<td class="text-left">
						<?= $saida ? $this->lang->line('app_yes') : $this->lang->line('app_no'); ?>
						</td>
					</tr>
					<tr>
						<td>
						<?= ucfirst($this->lang->line('product_mov_type')).' - '.ucfirst($this->lang->line('product_in')) ?>
						</td>
						<td class="text-left">
							<?= $entrada ? $this->lang->line('app_yes') : $this->lang->line('app_no'); ?>
						</td>
					</tr>
				</table>

			</div>
			<hr>
			<a href="<?= site_url('produtos/create') ?>" class="btn btn-success">
				<i class="fa fa-plus"></i>
				<?= $this->lang->line('app_create'); ?>
			</a>
			<a href="<?= site_url('produtos/update/'.$idProdutos) ?>" class="btn btn-info">
				<i class="fa fa-edit"></i>
				<?= $this->lang->line('app_edit'); ?>
			</a>
			<a href="<?= site_url('produtos') ?>" class="btn btn-dark">
				<i class="fa fa-reply"></i>
				<?= $this->lang->line('app_back'); ?>
			</a>

		</div>
	</div>
</div>