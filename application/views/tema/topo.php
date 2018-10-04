<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!-- Tell the browser to be responsive to screen width -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Sistema de controle de Ordens de Serviço">
	<meta name="author" content="Ramon">
	<!-- Favicon icon -->
	<link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/images/favicon.png'); ?>">
	<title>MAPOS</title>
	<!-- Bootstrap Core CSS -->
	<link href="<?= base_url('assets/css/lib/bootstrap/bootstrap.min.css'); ?>" rel="stylesheet">
	<!-- Custom CSS -->
	<link href="<?= base_url('assets/css/helper.css'); ?>" rel="stylesheet">
	<link href="<?= base_url('assets/css/style.css'); ?>" rel="stylesheet">
	<link href="<?= base_url('assets/css/lib/toastr/toastr.min.css'); ?>" rel="stylesheet">
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:** -->
	<!--[if lt IE 9]>
		<script src="https:**oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https:**oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
	<!-- Jquery -->
	<script src="<?= base_url('assets/js/lib/jquery/jquery.min.js'); ?>"></script>
	<script src="<?= base_url('assets/js/lib/toastr/toastr.min.js'); ?>"></script>
</head>

<body class="fix-header fix-sidebar">
	<!-- Preloader - style you can find in spinners.css -->
	<div class="preloader">
		<svg class="circular" viewBox="25 25 50 50">
			<circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> </svg>
	</div>
	<!-- Main wrapper  -->
	<div id="main-wrapper">
		<!-- header header  -->
		<div class="header">
			<nav class="navbar top-navbar navbar-expand-md navbar-light">
				<!-- Logo -->
				<div class="navbar-header">
					<a class="navbar-brand" href="<?= base_url() ?> ">
						<!-- Logo icon -->
						<b>
							<img style="max-height: 24px; max-width: 60px" src="<?= base_url('assets/images/logo.png'); ?>" class="dark-logo img-responsive" />
						</b>
						<!--End Logo icon -->
						<!-- Logo text -->
						<span>
							<img style="max-height: 24px; max-width: 120px" src="<?= base_url('assets/images/logo-text.png'); ?>" alt="MapOS" class="dark-logo img-responsive"
							/>
						</span>
					</a>
				</div>
				<!-- End Logo -->
				<div class="navbar-collapse">
					<!-- toggle and nav items -->
					<ul class="navbar-nav mr-auto mt-md-0">
						<!-- This is  -->
						<li class="nav-item">
							<a class="nav-link nav-toggler hidden-md-up text-muted  " href="javascript:void(0)">
								<i class="mdi mdi-menu"></i>
							</a>
						</li>
						<li class="nav-item m-l-10">
							<a class="nav-link sidebartoggler hidden-sm-down text-muted  " href="javascript:void(0)">
								<i class="ti-menu"></i>
							</a>
						</li>
						<!-- Messages -->
						<li class="nav-item dropdown mega-dropdown">
							<a class="nav-link dropdown-toggle text-muted  " href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="fa fa-th-large"></i>
							</a>
							<div class="dropdown-menu animated zoomIn">
								<ul class="mega-dropdown-menu row">


									<li class="col-lg-2 m-b-30">
										<h4 class="m-b-20">ACESSO RÁPIDO</h4>
										<ul>
											<li>
												<a href="<?= site_url('os/create') ?>" class="btn btn-primary col-12">Adicionar O.S</a>
											</li>
											<li>
												<a href="<?= site_url('clientes/create') ?>" class="btn btn-primary col-12">Adicionar Cliente</a>
											</li>
											<li>
												<a href="<?= site_url('produtos/create') ?>" class="btn btn-primary col-12">Adicionar Produto</a>
											</li>

										</ul>
									</li>
									<li class="col-lg-3 col-xlg-3 m-b-30">
										<h4 class="m-b-20">Ordens de Serviço</h4>

										<form>
											<div class="form-group">
												<input type="text" class="form-control" id="" placeholder="Digite o nome do cliente ou o protocolo"> </div>
											<button type="submit" class="btn btn-info">Pesquisar O.S.</button>
										</form>

									</li>
									<li class="col-lg-3 col-xlg-3 m-b-30">
										<h4 class="m-b-20">Clientes</h4>

										<form>
											<div class="form-group">
												<input type="text" class="form-control" id="" placeholder="Digite o nome"> </div>
											<button type="submit" class="btn btn-info">Pesquisar Cliente</button>
										</form>
									</li>
									<li class="col-lg-3 col-xlg-3 m-b-30">
										<h4 class="m-b-20">Produtos</h4>

										<form>
											<div class="form-group">
												<input type="text" class="form-control" id="" placeholder="Digite o nome do produto"> </div>
											<button type="submit" class="btn btn-info">Pesquisar Produto</button>
										</form>
									</li>
								</ul>
							</div>
						</li>
						<!-- End Messages -->
					</ul>
					<!-- User profile and search -->
					<ul class="navbar-nav my-lg-0">

						<!-- Search -->
						<li class="nav-item hidden-sm-down search-box">
							<a class="nav-link hidden-sm-down text-muted  " href="javascript:void(0)">
								<i class="ti-search"></i>
							</a>
							<form class="app-search" action="<?= site_url('mapos/pesquisar'); ?>" method="GET" >
								<input type="text" name="termo" class="form-control" placeholder="Pesquise aqui">
								<a class="srh-btn">
									<i class="ti-close"></i>
								</a>
							</form>
						</li>

						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle text-muted" href="<?= site_url('mine') ?>" target="_blank" id="2" aria-expanded="false">
								<i class="fa fa-user"></i>
								Área do Cliente
							</a>
						</li>
						<!-- Comment -->
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle text-muted text-muted  " href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="fa fa-bell"></i>
								<div class="notify">
									<span class="heartbit"></span>
									<span class="point"></span>
								</div>
							</a>
							<div class="dropdown-menu dropdown-menu-right mailbox animated zoomIn">
								<ul>
									<li>
										<div class="drop-title">Notifications</div>
									</li>
									<li>
										<div class="message-center">
											<!-- Message -->
											<a href="#">
												<div class="btn btn-danger btn-circle m-r-10">
													<i class="fa fa-link"></i>
												</div>
												<div class="mail-contnet">
													<h5>This is title</h5>
													<span class="mail-desc">Just see the my new admin!</span>
													<span class="time">9:30 AM</span>
												</div>
											</a>
											<!-- Message -->
											<a href="#">
												<div class="btn btn-success btn-circle m-r-10">
													<i class="ti-calendar"></i>
												</div>
												<div class="mail-contnet">
													<h5>This is another title</h5>
													<span class="mail-desc">Just a reminder that you have event</span>
													<span class="time">9:10 AM</span>
												</div>
											</a>
											<!-- Message -->
											<a href="#">
												<div class="btn btn-info btn-circle m-r-10">
													<i class="ti-settings"></i>
												</div>
												<div class="mail-contnet">
													<h5>This is title</h5>
													<span class="mail-desc">You can customize this template as you want</span>
													<span class="time">9:08 AM</span>
												</div>
											</a>
											<!-- Message -->
											<a href="#">
												<div class="btn btn-primary btn-circle m-r-10">
													<i class="ti-user"></i>
												</div>
												<div class="mail-contnet">
													<h5>This is another title</h5>
													<span class="mail-desc">Just see the my admin!</span>
													<span class="time">9:02 AM</span>
												</div>
											</a>
										</div>
									</li>
									<li>
										<a class="nav-link text-center" href="javascript:void(0);">
											<strong>Check all notifications</strong>
											<i class="fa fa-angle-right"></i>
										</a>
									</li>
								</ul>
							</div>
						</li>
						<!-- End Comment -->
						<!-- Messages -->
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle text-muted  " href="#" id="2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="fa fa-envelope"></i>
								<div class="notify">
									<span class="heartbit"></span>
									<span class="point"></span>
								</div>
							</a>
							<div class="dropdown-menu dropdown-menu-right mailbox animated zoomIn" aria-labelledby="2">
								<ul>
									<li>
										<div class="drop-title">You have 4 new messages</div>
									</li>
									<li>
										<div class="message-center">
											<!-- Message -->
											<a href="#">
												<div class="user-img">
													<img src="images/users/5.jpg" alt="user" class="img-circle">
													<span class="profile-status online pull-right"></span>
												</div>
												<div class="mail-contnet">
													<h5>Michael Qin</h5>
													<span class="mail-desc">Just see the my admin!</span>
													<span class="time">9:30 AM</span>
												</div>
											</a>
											<!-- Message -->
											<a href="#">
												<div class="user-img">
													<img src="images/users/2.jpg" alt="user" class="img-circle">
													<span class="profile-status busy pull-right"></span>
												</div>
												<div class="mail-contnet">
													<h5>John Doe</h5>
													<span class="mail-desc">I've sung a song! See you at</span>
													<span class="time">9:10 AM</span>
												</div>
											</a>
											<!-- Message -->
											<a href="#">
												<div class="user-img">
													<img src="images/users/3.jpg" alt="user" class="img-circle">
													<span class="profile-status away pull-right"></span>
												</div>
												<div class="mail-contnet">
													<h5>Mr. John</h5>
													<span class="mail-desc">I am a singer!</span>
													<span class="time">9:08 AM</span>
												</div>
											</a>
											<!-- Message -->
											<a href="#">
												<div class="user-img">
													<img src="images/users/4.jpg" alt="user" class="img-circle">
													<span class="profile-status offline pull-right"></span>
												</div>
												<div class="mail-contnet">
													<h5>Michael Qin</h5>
													<span class="mail-desc">Just see the my admin!</span>
													<span class="time">9:02 AM</span>
												</div>
											</a>
										</div>
									</li>
									<li>
										<a class="nav-link text-center" href="javascript:void(0);">
											<strong>See all e-Mails</strong>
											<i class="fa fa-angle-right"></i>
										</a>
									</li>
								</ul>
							</div>
						</li>
						<!-- End Messages -->
						<!-- Profile -->
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle text-muted  " href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<img src="<?= base_url('assets/images/user.png'); ?>" alt="user" class="profile-pic" />
							</a>
							<div class="dropdown-menu dropdown-menu-right animated zoomIn">
								<ul class="dropdown-user">
									<li>
										<a href="<?= site_url('mapos/conta'); ?>">
											<i class="ti-user"></i> Perfil</a>
									</li>
									<li>
										<a href="#">
											<i class="ti-wallet"></i> Balance</a>
									</li>
									<li>
										<a href="#">
											<i class="ti-email"></i> Inbox</a>
									</li>
									<li>
										<a href="#">
											<i class="ti-settings"></i> Setting</a>
									</li>
									<li>
										<a href="<?= site_url('mapos/sair'); ?>">
											<i class="fa fa-power-off"></i> Sair do Sistema</a>
									</li>
								</ul>
							</div>
						</li>
					</ul>
				</div>
			</nav>
		</div>
		<!-- End header header -->
		<!-- Left Sidebar  -->
		<div class="left-sidebar">
			<!-- Sidebar scroll-->
			<div class="scroll-sidebar">
				<!-- Sidebar navigation-->
				<nav class="sidebar-nav">
					<ul id="sidebarnav">
						<li class="nav-devider"></li>

						<li class="nav-label">Menu</li>

						<li>
							<a href="<?= site_url() ?>" aria-expanded="false">
								<i class="fa fa-dashboard"></i>
								<span class="hide-menu">Painel</span>
							</a>
						</li>
						<li>
							<a href="<?= site_url('clientes') ?>" aria-expanded="false">
								<i class="fa fa-users"></i>
								<span class="hide-menu">Clientes</span>
							</a>
						</li>
						<li>
							<a href="<?= site_url('produtos') ?>" aria-expanded="false">
								<i class="fa fa-barcode"></i>
								<span class="hide-menu"><?= ucfirst($this->lang->line('products')) ?></span>
							</a>
						</li>
						<li>
							<a href="<?= site_url('servicos') ?>" aria-expanded="false">
								<i class="fa fa-wrench"></i>
								<span class="hide-menu"><?= ucfirst($this->lang->line('services')) ?></span>
							</a>
						</li>
						<li>
							<a href="<?= site_url('os') ?>" aria-expanded="false">
								<i class="fa fa-tags"></i>
								<span class="hide-menu">Ordens de Serviço</span>
							</a>
						</li>
						<li>
							<a href="<?= site_url('vendas') ?>" aria-expanded="false">
								<i class="fa fa-shopping-cart"></i>
								<span class="hide-menu">Vendas</span>
							</a>
						</li>
						<li>
							<a href="<?= site_url('arquivos') ?>" aria-expanded="false">
								<i class="fa fa-file"></i>
								<span class="hide-menu">Arquivos</span>
							</a>
						</li>

						<li>
							<a class="has-arrow  " href="#" aria-expanded="false">
								<i class="fa fa-money"></i>
								<span class="hide-menu">Financeiro</span>
							</a>
							<ul aria-expanded="false" class="collapse">
								<li>
									<a href="<?= site_url('financeiro/lancamentos'); ?>">Lançamentos</a>
								</li>
							</ul>
						</li>
						<li>
							<a class="has-arrow  " href="#" aria-expanded="false">
								<i class="fa fa-bar-chart"></i>
								<span class="hide-menu">Relatórios</span>
							</a>
							<ul aria-expanded="false" class="collapse">
								<li>
									<a href="<?= site_url('relatorios/clientes'); ?>">Clientes</a>
								</li>
								<li>
									<a href="<?= site_url('relatorios/produtos'); ?>">Produtos</a>
								</li>
								<li>
									<a href="<?= site_url('relatorios/servicos'); ?>"><?= ucfirst($this->lang->line('services')) ?></a>
								</li>
								<li>
									<a href="<?= site_url('relatorios/os'); ?>">Ordens de Serviço</a>
								</li>
								<li>
									<a href="<?= site_url('relatorios/vendas'); ?>">Vendas</a>
								</li>
								<li>
									<a href="<?= site_url('relatorios/financeiro'); ?>">Financeiro</a>
								</li>
							</ul>
						</li>

						<li>
							<a class="has-arrow  " href="#" aria-expanded="false">
								<i class="fa fa-cogs"></i>
								<span class="hide-menu">Configurações</span>
							</a>
							<ul aria-expanded="false" class="collapse">
								<li>
									<a href="<?= site_url('usuarios'); ?>">Usuários</a>
								</li>
								<li>
									<a href="<?= site_url('mapos/emitente'); ?>">Emitente</a>
								</li>
								<li>
									<a href="<?= site_url('permissoes'); ?>">Permissões</a>
								</li>
								<li>
									<a href="<?= site_url('mapos/backup'); ?>">Backup</a>
								</li>
			
							</ul>
						</li>

					</ul>
				</nav>
				<!-- End Sidebar navigation -->
			</div>
			<!-- End Sidebar scroll-->
		</div>
		<!-- End Left Sidebar  -->
		<!-- Page wrapper  -->
		<div class="page-wrapper">
			<!-- Bread crumb -->
			<div class="row page-titles">
				<div class="col-md-5 align-self-center">
					<h3 class="text-primary">Painel</h3>
				</div>
				<div class="col-md-7 align-self-center">
					<ol class="breadcrumb">
						<li class="breadcrumb-item">
							<a href="javascript:void(0)">Home</a>
						</li>
						<li class="breadcrumb-item active">Painel</li>
					</ol>
				</div>
			</div>
			<!-- End Bread crumb -->
			<!-- Container fluid  -->
			<div class="container-fluid">
				<!-- Start Page Content -->
				<div class="row">
					<div class="col-12">					
						<?php if(isset($view)){echo $this->load->view($view, null, true);}?>
					</div>
				</div>
				<!-- End PAge Content -->
			</div>
			<!-- End Container fluid  -->
			<!-- footer -->
			<footer class="footer text-center fixed-bottom" style="margin: 0">
				<a href="https://github.com/RamonSilva20/mapos" target="_blank">
				&copy; MAPOS - Versão: <?= $this->config->item('app_version'); ?></a>
			</footer>

			<!-- End footer -->
		</div>
		<!-- End Page wrapper  -->
	</div>
	<!-- End Wrapper -->
	
	<!-- Bootstrap tether Core JavaScript -->
	<script src="<?= base_url('assets/js/lib/bootstrap/js/popper.min.js'); ?>"></script>
	<script src="<?= base_url('assets/js/lib/bootstrap/js/bootstrap.min.js'); ?>"></script>
	<!-- slimscrollbar scrollbar JavaScript -->
	<script src="<?= base_url('assets/js/jquery.slimscroll.js'); ?>"></script>
	<!--Menu sidebar -->
	<script src="<?= base_url('assets/js/sidebarmenu.js'); ?>"></script>
	<!--stickey kit -->
	<script src="<?= base_url('assets/js/lib/sticky-kit-master/dist/sticky-kit.min.js'); ?>"></script>
	<!--Custom JavaScript -->
	<script src="<?= base_url('assets/js/scripts.js'); ?>"></script>

	<script type="text/javascript">  
		$(document).ready(function () {
			
			<?php if($this->session->flashdata('success') != null){ ?>
		
				toastr.success('<?= $this->session->flashdata('success');?>','Atenção',{
					timeOut: 8000,
					"closeButton": true,
					"newestOnTop": true,
					"progressBar": true,
					"positionClass": "toast-top-right",
					"onclick": null,
				});

			<?php } ?>

			<?php if($this->session->flashdata('error') != null){?>
		
				toastr.error('<?= $this->session->flashdata('error');?>','Atenção',{
					timeOut: 8000,
					"closeButton": true,
					"newestOnTop": true,
					"progressBar": true,
					"positionClass": "toast-top-right",
					"onclick": null,
				});

			<?php } ?>

		});  
	</script>

</body>

</html>