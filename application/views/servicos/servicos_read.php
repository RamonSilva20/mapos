
<div class="col-lg-12 col-md-12 col-sm-12">
	<div class="card">
		<div class="card-title">
			<h4><i class="fa fa-eye"></i> <?= $this->lang->line('app_view').' '.ucfirst($this->lang->line('service')); ?></h2> </h4>

		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered table-striped">
					<tr>
						<td style="width: 30%"><?= ucfirst($this->lang->line('service_name')) ?></td>
						<td class="text-left">
							<?= $nome; ?>
						</td>
					</tr>
					<tr>
						<td><?= ucfirst($this->lang->line('service_description')) ?></td>
						<td class="text-left">
							<?= $descricao; ?>
						</td>
					</tr>
					<tr>
						<td><?= ucfirst($this->lang->line('service_price')) ?></td>
						<td class="text-left">
							<?= $preco; ?>
						</td>
					</tr>
				</table>
			</div>
			<hr>
			<a href="<?= site_url('servicos/create') ?>" class="btn btn-success">
				<i class="fa fa-plus"></i> <?= $this->lang->line('app_create'); ?>
			</a>
			<a href="<?= site_url('servicos/update/'.$idServicos) ?>" class="btn btn-info">
				<i class="fa fa-edit"></i> <?= $this->lang->line('app_edit'); ?>
			</a>
			<a href="<?= site_url('servicos') ?>" class="btn btn-dark">
				<i class="fa fa-reply"></i> <?= $this->lang->line('app_back'); ?>
			</a>
		</div>
	</div>
</div>