<div class="col-lg-12 col-md-12 col-sm-12">
	<div class="card">
		<div class="card-title">
			<h4>
				<i class="fa fa-eye"></i>
				<?= $this->lang->line('app_view').' '.ucfirst($this->lang->line('file')); ?>
			</h4>
		</div>
		<div class="card-body">
			<div class="table-responsive">

				<table class="table table-bordered table-striped">
					<tr>
						<td style="width: 30%">
							<?= ucfirst($this->lang->line('file_name')) ?>
						</td>
						<td class="text-left">
							<?= $documento; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?= ucfirst($this->lang->line('file_description')) ?>
						</td>
						<td class="text-left">
							<?= $descricao; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?= ucfirst($this->lang->line('file')) ?>
						</td>
						<td class="text-left">
							<?= $file; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?= ucfirst($this->lang->line('file_date')) ?>
						</td>
						<td class="text-left">
							<?= date($this->config->item('date_format'), strtotime($cadastro)); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?= ucfirst($this->lang->line('file_type')) ?>
						</td>
						<td class="text-left">
							<?= $tipo; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?= ucfirst($this->lang->line('file_size')) ?>
						</td>
						<td class="text-left">
							<?= $tamanho; ?> KB
						</td>
					</tr>
				</table>

			</div>
			<hr>
			<a href="<?= site_url('arquivos/create') ?>" class="btn btn-success">
				<i class="fa fa-plus"></i>
				<?= $this->lang->line('app_create'); ?>
			</a>
			<a href="<?= site_url('arquivos/update/'.$idDocumentos) ?>" class="btn btn-info">
				<i class="fa fa-edit"></i>
				<?= $this->lang->line('app_edit'); ?>
			</a>
			<a href="<?= site_url('arquivos/download/'.$idDocumentos) ?>" class="btn btn-primary">
				<i class="fa fa-download"></i>
				<?= $this->lang->line('file_download'); ?>
			</a>
			<a href="<?= site_url('arquivos') ?>" class="btn btn-dark">
				<i class="fa fa-reply"></i>
				<?= $this->lang->line('app_back'); ?>
			</a>

		</div>
	</div>
</div>