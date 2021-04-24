<!DOCTYPE html>
<html lang="pt-br">

<head>
  <title><?= $configuration['app_name'] ?: 'Map-OS' ?></title>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" type="image/png" href="<?= base_url(); ?>assets/img/favicon.png" />
  <link rel="stylesheet" href="<?= base_url(); ?>assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="<?= base_url(); ?>assets/css/bootstrap-responsive.min.css" />
  <link rel="stylesheet" href="<?= base_url(); ?>assets/css/matrix-style.css" />
  <link rel="stylesheet" href="<?= base_url(); ?>assets/css/matrix-media.css" />
  <link href="<?= base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
  <link rel="stylesheet" href="<?= base_url(); ?>assets/css/fullcalendar.css" />
  <?php if ($configuration['app_theme'] == 'white') { ?>
    <link rel="stylesheet" href="<?= base_url(); ?>assets/css/tema.css" />
  <?php } ?>
  <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
  <script type="text/javascript" src="<?= base_url(); ?>assets/js/jquery-1.12.4.min.js"></script>
  <script type="text/javascript" src="<?= base_url(); ?>assets/js/shortcut.js"></script>
  <script type="text/javascript" src="<?= base_url(); ?>assets/js/funcoesGlobal.js"></script>
  <script type="text/javascript" src="<?= base_url(); ?>assets/js/datatables.min.js"></script>
  <script type="text/javascript" src="<?= base_url(); ?>assets/js/sweetalert.min.js"></script>
  <script type="text/javascript">
    shortcut.add("escape", function() {
      location.href = '<?= base_url(); ?>';
    });
    shortcut.add("F1", function() {
      location.href = '<?= site_url('clientes'); ?>';
    });
    shortcut.add("F2", function() {
      location.href = '<?= site_url('produtos'); ?>';
    });
    shortcut.add("F3", function() {
      location.href = '<?= site_url('servicos'); ?>';
    });
    shortcut.add("F4", function() {
      location.href = '<?= site_url('os'); ?>';
    });
    //shortcut.add("F5", function() {});
    shortcut.add("F6", function() {
      location.href = '<?= site_url('vendas'); ?>';
    });
    shortcut.add("F7", function() {
      location.href = '<?= site_url('garantias'); ?>';
    });
    shortcut.add("F8", function() {});
    shortcut.add("F9", function() {});
    shortcut.add("F10", function() {});
    shortcut.add("F11", function() {});
    shortcut.add("F12", function() {});
    window.BaseUrl = "<?= base_url() ?>";
  </script>

</head>

<body>
  <!--Header-part-->
  <div id="header">
    <h1><a href=""> <?= $configuration['app_name'] ?: 'Map-OS' ?> </a></h1>
  </div>
  <!--close-Header-part-->
  <!--top-Header-menu-->
  <div id="user-nav" class="navbar navbar-inverse">
    <ul class="nav">
      <li class=""><a title="" href="<?= site_url(); ?>/mine"><i class="fas fa-eye"></i> <span class="text">Área do Cliente</span></a></li>
      <li class="pull-right"><a href="https://github.com/RamonSilva20/mapos" target="_blank"><i class="fas fa-asterisk"></i> <span class="text">Versão:
            <?= $this->config->item('app_version'); ?></span></a></li>
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fas fa-user-cog"></i> <?= $this->session->userdata('nome') ?> <b class="caret"></b></a>
        <ul class="dropdown-menu">
          <li class=""><a title="Meu Perfil" href="<?= site_url('mapos/minhaConta'); ?>"><i class="fas fa-user"></i> <span class="text">Meu Perfil</span></a></li>
          <li class="divider"></li>
          <li class=""><a title="Sair do Sistema" href="<?= site_url('login/sair'); ?>"><i class="fas fa-sign-out-alt"></i> <span class="text">Sair do Sistema</span></a></li>
        </ul>
      </li>
    </ul>
  </div>
  <!--start-top-serch-->
  <div id="search">
    <form action="<?= site_url('mapos/pesquisar') ?>">
      <input type="text" name="termo" placeholder="Pesquisar..." />
      <button type="submit" class="tip-bottom" title="Pesquisar"><i class="fas fa-search fa-white"></i></button>
    </form>
  </div>
  <!--close-top-serch-->
