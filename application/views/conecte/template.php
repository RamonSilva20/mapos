<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Área do Cliente - <?php echo $this->config->item('app_name') ?></title>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="description" content="<?php echo $this->config->item('app_name') . ' - ' . $this->config->item('app_subname') ?>">
    <link rel="shortcut icon" type="image/png" href="<?php echo base_url(); ?>assets/img/favicon.png"/>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap-responsive.min.css"/>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-style.css"/>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-media.css"/>
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet"/>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/fullcalendar.css"/>
    <link href="<?php echo base_url(); ?>assets/css/bootstrap-responsive.min.css" rel="stylesheet">
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-1.12.4.min.js"></script>
    <script type="text/javascript" src="<?= base_url(); ?>assets/js/sweetalert.min.js"></script>
    <link rel="shortcut icon" href="<?php echo base_url(); ?>assets/img/fav.png">
</head>

<body>
<!--Header-part-->
<div id="header">
    <h1><a href="dashboard.html">
            <?php echo $this->config->item('app_name'); ?></a></h1>
</div>
<!--close-Header-part-->

<!--top-Header-menu-->
<div id="user-nav" class="navbar navbar-inverse">
    <ul class="nav">
      <li class="pull-right"><a href="https://github.com/RamonSilva20/mapos" target="_blank"><i class="fas fa-asterisk"></i> <span class="text">Versão:
            <?= $this->config->item('app_version'); ?></span></a></li>
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fas fa-user-cog"></i> <?= $this->session->userdata('nome') ?> <b class="caret"></b></a>
        <ul class="dropdown-menu">
          <li class=""><a title="Meu Perfil"  href="<?php echo base_url() ?>index.php/mine/conta"><i class="fas fa-user"></i> <span class="text">Meu Perfil</span></a></li>
          <li class="divider"></li>
          <li class=""><a title="Sair" href="<?php echo base_url() ?>index.php/mine/sair"><i class="fas fa-sign-out-alt"></i> <span class="text">Sair</span></a></li>
        </ul>
      </li>
    </ul>
</div>

<div id="sidebar"><a href="#" class="visible-phone"><i class="icon fas fa-bars"></i> Menu</a>
    <ul>
        <li class="<?php if (isset($menuPainel)) {
    echo 'active';
}; ?>"><a href="<?php echo base_url() ?>index.php/mine/painel"><i class="icon fas fa-home"></i> <span>Painel</span></a></li>
        <li class="<?php if (isset($menuConta)) {
    echo 'active';
}; ?>"><a href="<?php echo base_url() ?>index.php/mine/conta"><i class="icon fas fa-user"></i> <span>Minha Conta</span></a></li>
        <li class="<?php if (isset($menuOs)) {
    echo 'active';
}; ?>"><a href="<?php echo base_url() ?>index.php/mine/os"><i class="icon fas fa-diagnoses"></i> <span>Ordens de Serviço</span></a></li>
        <li class="<?php if (isset($menuVendas)) {
    echo 'active';
}; ?>"><a href="<?php echo base_url() ?>index.php/mine/compras"><i class="icon fas fa-shopping-cart"></i> <span>Compras</span></a></li>
        <li class="<?php if (isset($menuCobrancas)) {
    echo 'active';
}; ?>"><a href="<?php echo base_url() ?>index.php/mine/cobrancas"><i class="icon fas fa-barcode"></i> <span>Cobranças</span></a></li>
        <li class=""><a href="<?php echo base_url() ?>index.php/mine/sair"><i class="icon fas fa-sign-out-alt"></i> <span>Sair</span></a></li>

    </ul>
</div>


<div id="content">
    <div id="content-header">
        <div id="breadcrumb"><a href="<?php echo base_url(); ?>index.php/mine/painel" title="Painel" class="tip-bottom"><i class="fas fa-home"></i> Painel</a></div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid">

            <div class="span12">
                <?php if ($var = $this->session->flashdata('success')): ?><script>swal("Sucesso!", "<?php echo str_replace('"', '', $var); ?>", "success");</script><?php endif; ?>
                <?php if ($var = $this->session->flashdata('error')): ?><script>swal("Falha!", "<?php echo str_replace('"', '', $var); ?>", "error");</script><?php endif; ?>
                <?php if (isset($output)) {
    $this->load->view($output);
} ?>

            </div>
        </div>

    </div>
</div>
<!--Footer-part-->
<div class="row-fluid">
    <div id="footer" class="span12">
        <?= date('Y') ?> &copy;
        <?php echo $this->config->item('app_name'); ?> - Versão:
        <?php echo $this->config->item('app_version'); ?>
    </div>
</div>

<!-- javascript
================================================== -->

<script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>


</body>

</html>
