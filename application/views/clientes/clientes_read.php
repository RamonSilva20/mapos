<div class="col-lg-12 col-md-12 col-sm-12">
	<div class="card">
		<div class="card-title">
			<h4>
				<i class="fa fa-eye"></i>
				<?= $this->lang->line('app_view').' '.ucfirst($this->lang->line('client')); ?>
			</h4>
		</div>
		<div class="card-body">

			<div class="vtabs">
				<ul class="nav nav-tabs tabs-vertical" role="tablist">
					<li class="nav-item"> <a class="nav-link active show" data-toggle="tab" href="#info" role="tab" aria-selected="true"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down">Dados</span> </a> </li>
					<li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#contact" role="tab" aria-selected="false"><span class="hidden-sm-up"><i class="ti-email"></i></span> <span class="hidden-xs-down">Contato</span></a> </li>
					<li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#address" role="tab" aria-selected="false"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down">Endereço</span></a> </li>
				</ul>
				<!-- Tab panes -->
				<div class="tab-content col-12">
					<div class="tab-pane active show" id="info" role="tabpanel">
						<table class="table table-bordered table-striped">
							<tr>
								<td style="width: 30%">
									<?= ucfirst($this->lang->line('client_name')) ?>
								</td>
								<td class="text-left">
									<?= $nomeCliente; ?>
								</td>
							</tr>
							<!-- <tr>
								<td>
									<?= ucfirst($this->lang->line('client_sex')) ?>
								</td>
								<td class="text-left">
									<?= $sexo; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?= ucfirst($this->lang->line('client_type')) ?>
								</td>
								<td class="text-left">
									<?= $pessoa_fisica; ?>
								</td>
							</tr> -->
							<tr>
								<td>
									<?= ucfirst($this->lang->line('client_doc')) ?>
								</td>
								<td class="text-left">
									<?= $documento; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?= ucfirst($this->lang->line('client_created')) ?>
								</td>
								<td class="text-left">
									<?= date('d/m/Y', strtotime($dataCadastro)); ?>
								</td>
							</tr>
							<tr>
								<td>
									<?= ucfirst($this->lang->line('client_obs')) ?>
								</td>
								<td class="text-left">
									<?= $obs; ?>
								</td>
							</tr>
						</table>
					</div>
					<div class="tab-pane" id="contact" role="tabpanel">
						<table class="table table-bordered table-striped">
							<tr>
								<td style="width: 30%">
									<?= ucfirst($this->lang->line('client_phone')) ?>
								</td>
								<td class="text-left">
									<?= $telefone; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?= ucfirst($this->lang->line('client_cel')) ?>
								</td>
								<td class="text-left">
									<?= $celular; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?= ucfirst($this->lang->line('client_mail')) ?>
								</td>
								<td class="text-left">
									<?= $email; ?>
								</td>
							</tr>
						</table>	
					</div>
					<div class="tab-pane" id="address" role="tabpanel">
						<table class="table table-bordered table-striped">
							<tr>
								<td style="width: 30%">
									<?= ucfirst($this->lang->line('client_street')) ?>
								</td>
								<td class="text-left">
									<?= $rua; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?= ucfirst($this->lang->line('client_number')) ?>
								</td>
								<td class="text-left">
									<?= $numero; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?= ucfirst($this->lang->line('client_district')) ?>
								</td>
								<td class="text-left">
									<?= $bairro; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?= ucfirst($this->lang->line('client_city')) ?>
								</td>
								<td class="text-left">
									<?= $cidade; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?= ucfirst($this->lang->line('client_state')) ?>
								</td>
								<td class="text-left">
									<?= $estado; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?= ucfirst($this->lang->line('client_zip')) ?>
								</td>
								<td class="text-left">
									<?= $cep; ?>
								</td>
							</tr>
						</table>	
					</div>
				</div>
			</div>


			<hr>
			<a href="<?= site_url('clientes/create') ?>" class="btn btn-success">
				<i class="fa fa-plus"></i>
				<?= $this->lang->line('app_create'); ?>
			</a>
			<a href="<?= site_url('clientes/update/'.$idClientes) ?>" class="btn btn-info">
				<i class="fa fa-edit"></i>
				<?= $this->lang->line('app_edit'); ?>
			</a>
			<a href="<?= site_url('clientes') ?>" class="btn btn-dark">
				<i class="fa fa-reply"></i>
				<?= $this->lang->line('app_back'); ?>
			</a>

		</div>
	</div>
</div>


                             

