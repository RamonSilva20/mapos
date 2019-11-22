<!DOCTYPE html>
<html lang="en">

<head>
  <title>Map OS</title>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap-responsive.min.css" />
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-style.css" />
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-media.css" />
  <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/fullcalendar.css" />
  <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
  <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-1.12.4.min.js"></script>
  <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/shortcut.js"></script>
  <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/funcoesGlobal.js"></script>

  <script type="text/javascript">
    shortcut.add("escape", function() {
      location.href = '<?php echo base_url(); ?>';
    });
    shortcut.add("F1", function() {
      location.href = '<?php echo base_url(); ?>index.php/clientes';
    });
    shortcut.add("F2", function() {
      location.href = '<?php echo base_url(); ?>index.php/produtos';
    });
    shortcut.add("F3", function() {
      location.href = '<?php echo base_url(); ?>index.php/servicos';
    });
    shortcut.add("F4", function() {
      location.href = '<?php echo base_url(); ?>index.php/os';
    });
    //shortcut.add("F5", function() {});
    shortcut.add("F6", function() {
      location.href = '<?php echo base_url(); ?>index.php/vendas';
    });
    shortcut.add("F7", function() {
      location.href = '<?php echo base_url(); ?>index.php/garantias';
    });
    shortcut.add("F8", function() {});
    shortcut.add("F9", function() {});
    shortcut.add("F10", function() {});
    shortcut.add("F11", function() {});
    shortcut.add("F12", function() {});
  </script>

</head>

<body onLoad="initTimer();">
  <!--Header-part-->
  <div id="header">
    <h1><a href=""> Map OS </a></h1>
  </div>
  <!--close-Header-part-->
  <!--top-Header-menu-->
  <div id="user-nav" class="navbar navbar-inverse">
    <ul class="nav">
      <li class=""><a title="" href="<?php echo site_url(); ?>/mine"><i class="fas fa-eye"></i> <span class="text">Área do Cliente</span></a></li>
      <li class="pull-right"><a href="https://github.com/RamonSilva20/mapos" target="_blank"><i class="fas fa-asterisk"></i> <span class="text">Versão:
            <?php echo $this->config->item('app_version'); ?></span></a></li>
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fas fa-user-cog"></i> <?php echo $this->session->userdata('nome') ?> <b class="caret"></b></a>
        <ul class="dropdown-menu">
          <li class=""><a title="Meu Perfil" href="<?php echo site_url(); ?>/mapos/minhaConta"><i class="fas fa-user"></i> <span class="text">Meu Perfil</span></a></li>
          <li class="divider"></li>
          <li class=""><a title="Sair do Sistema" href="<?php echo site_url(); ?>/mapos/sair"><i class="fas fa-sign-out-alt"></i> <span class="text">Sair do Sistema</span></a></li>
        </ul>
      </li>
      <li style="margin-left: 100px;margin-top: 10px;"><span class="text" style="font-size:13px;color: #fff;"><i class="far fa-calendar-alt"></i>

          <?php
          setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
          date_default_timezone_set('America/Sao_Paulo');
          $uppercaseMonth = ucfirst(gmstrftime('%B'));
          echo ucfirst(strftime('%A, %d de ' . $uppercaseMonth . ' de %Y', strtotime('today')));
          ?>
          <i class="far fa-clock"></i></i> <span id="timer"></span></span></li>
    </ul>
  </div>
  <!--start-top-serch-->
  <div id="search">
    <form action="<?php echo base_url() ?>index.php/mapos/pesquisar">
      <input type="text" name="termo" placeholder="Pesquisar..." />
      <button type="submit" class="tip-bottom" title="Pesquisar"><i class="fas fa-search fa-white"></i></button>
    </form>
  </div>
  <!--close-top-serch-->
  <!--sidebar-menu-->
  <div id="sidebar"> <a href="#" class="visible-phone"><i class="fas fa-list"></i> Menu</a>
    <ul>
      <li class="<?php if (isset($menuPainel)) {
                    echo 'active';
                  }; ?>"><a href="<?php echo base_url() ?>"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
      <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) { ?>
        <li class="<?php if (isset($menuClientes)) {
                        echo 'active';
                      }; ?>"><a href="<?php echo base_url() ?>index.php/clientes"><i class="fas fa-users"></i> <span>Clientes</span></a></li>
      <?php
      } ?>
      <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vProduto')) { ?>
        <li class="<?php if (isset($menuProdutos)) {
                        echo 'active';
                      }; ?>"><a href="<?php echo base_url() ?>index.php/produtos"><i class="fas fa-shopping-bag"></i> <span>Produtos</span></a></li>
      <?php
      } ?>
      <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vServico')) { ?>
        <li class="<?php if (isset($menuServicos)) {
                        echo 'active';
                      }; ?>"><a href="<?php echo base_url() ?>index.php/servicos"><i class="fas fa-wrench"></i> <span>Serviços</span></a></li>
      <?php
      } ?>
      <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) { ?>
        <li class="<?php if (isset($menuOs)) {
                        echo 'active';
                      }; ?>"><a href="<?php echo base_url() ?>index.php/os"><i class="fas fa-diagnoses"></i> <span>Ordens de Serviço</span></a></li>
      <?php
      } ?>
      <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) { ?>
        <li class="<?php if (isset($menuVendas)) {
                        echo 'active';
                      }; ?>"><a href="<?php echo base_url() ?>index.php/vendas"><i class="fas fa-cash-register"></i> <span>Vendas</span></a></li>
      <?php
      } ?>
      <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vGarantia')) { ?>
        <li class="<?php if (isset($menuGarantia)) {
                        echo 'active';
                      }; ?>"><a href="<?php echo base_url() ?>index.php/garantias"><i class="fas fa-book"></i> <span>Termos de Garantias</span></a></li>
      <?php
      } ?>
      <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vArquivo')) { ?>
        <li class="<?php if (isset($menuArquivos)) {
                        echo 'active';
                      }; ?>"><a href="<?php echo base_url() ?>index.php/arquivos"><i class="fas fa-hdd"></i> <span>Arquivos</span></a></li>
      <?php
      } ?>
      <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vLancamento')) { ?>
        <li class="submenu <?php if (isset($menuFinanceiro)) {
                                echo 'active open';
                              }; ?>">
          <a href="#"><i class="fas fa-hand-holding-usd"></i> <span>Financeiro</span> <span class="label"><i class="fas fa-chevron-down"></i></span></a>
          <ul>
            <li><a href="<?php echo base_url() ?>index.php/financeiro/lancamentos">Lançamentos</a></li>
          </ul>
        </li>
      <?php
      } ?>
      <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rCliente') || $this->permission->checkPermission($this->session->userdata('permissao'), 'rProduto') || $this->permission->checkPermission($this->session->userdata('permissao'), 'rServico') || $this->permission->checkPermission($this->session->userdata('permissao'), 'rOs') || $this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro') || $this->permission->checkPermission($this->session->userdata('permissao'), 'rVenda')) { ?>
        <li class="submenu <?php if (isset($menuRelatorios)) {
                                echo 'active open';
                              }; ?>">
          <a href="#"><i class="fas fa-list-alt"></i> <span>Relatórios</span> <span class="label"><i class="fas fa-chevron-down"></i></span></a>
          <ul>
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rCliente')) { ?>
              <li><a href="<?php echo base_url() ?>index.php/relatorios/clientes">Clientes</a></li>
            <?php
              } ?>
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rProduto')) { ?>
              <li><a href="<?php echo base_url() ?>index.php/relatorios/produtos">Produtos</a></li>
            <?php
              } ?>
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rServico')) { ?>
              <li><a href="<?php echo base_url() ?>index.php/relatorios/servicos">Serviços</a></li>
            <?php
              } ?>
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rOs')) { ?>
              <li><a href="<?php echo base_url() ?>index.php/relatorios/os">Ordens de Serviço</a></li>
            <?php
              } ?>
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rVenda')) { ?>
              <li><a href="<?php echo base_url() ?>index.php/relatorios/vendas">Vendas</a></li>
            <?php
              } ?>
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rGarantia')) { ?>
              <li><a href="<?php echo base_url() ?>index.php/relatorios/Garantias">Termo Garantia</a></li>
            <?php
              } ?>
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) { ?>
              <li><a href="<?php echo base_url() ?>index.php/relatorios/financeiro">Financeiro</a></li>
            <?php
              } ?>
          </ul>
        </li>
      <?php
      } ?>
      <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'cUsuario')  || $this->permission->checkPermission($this->session->userdata('permissao'), 'cEmitente') || $this->permission->checkPermission($this->session->userdata('permissao'), 'cPermissao') || $this->permission->checkPermission($this->session->userdata('permissao'), 'cBackup')) { ?>
        <li class="submenu <?php if (isset($menuConfiguracoes)) {
                                echo 'active open';
                              }; ?>">
          <a href="#"><i class="fas fa-cog"></i> <span>Configurações</span> <span class="label"><i class="fas fa-chevron-down"></i></span></a>
          <ul>
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'cUsuario')) { ?>
              <li><a href="<?php echo site_url('usuarios') ?>">Usuários</a></li>
            <?php } ?>
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'cEmitente')) { ?>
              <li><a href="<?php echo site_url('mapos/emitente') ?>">Emitente</a></li>
            <?php } ?>
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'cPermissao')) { ?>
              <li><a href="<?php echo site_url('permissoes') ?>">Permissões</a></li>
            <?php } ?>
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'cAuditoria')) { ?>
              <li><a href="<?php echo site_url('auditoria') ?>">Auditoria</a></li>
            <?php } ?>
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'cEmail')) { ?>
              <li><a href="<?php echo site_url('email') ?>">Emails</a></li>
            <?php } ?>
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'cBackup')) { ?>
              <li><a href="<?php echo site_url('mapos/backup') ?>">Backup</a></li>
            <?php } ?>

          </ul>
        </li>
      <?php
      } ?>
    </ul>
  </div>
  <div id="content">
    <div id="content-header">
      <div id="breadcrumb"> <a href="<?php echo base_url() ?>" title="Dashboard" class="tip-bottom"><i class="fas fa-home"></i> Dashboard</a>
        <?php if ($this->uri->segment(1) != null) {
          ?><a href="<?php echo base_url() . 'index.php/' . $this->uri->segment(1) ?>" class="tip-bottom" title="<?php echo ucfirst($this->uri->segment(1)); ?>">
            <?php echo ucfirst($this->uri->segment(1)); ?></a>
          <?php if ($this->uri->segment(2) != null) {
              ?><a href="<?php echo base_url() . 'index.php/' . $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/' . $this->uri->segment(3) ?>" class="current tip-bottom" title="<?php echo ucfirst($this->uri->segment(2)); ?>">
            <?php echo ucfirst($this->uri->segment(2));
              } ?></a>
          <?php

          } ?>
      </div>
    </div>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span12">
          <?php if ($this->session->flashdata('error') != null) { ?>
            <div class="alert alert-danger">
              <button type="button" class="close" data-dismiss="alert">&times;</button>
              <?php echo $this->session->flashdata('error'); ?>
            </div>
          <?php
          } ?>
          <?php if ($this->session->flashdata('success') != null) { ?>
            <div class="alert alert-success">
              <button type="button" class="close" data-dismiss="alert">&times;</button>
              <?php echo $this->session->flashdata('success'); ?>
            </div>
          <?php
          } ?>
          <?php if (isset($view)) {
            echo $this->load->view($view, null, true);
          } ?>
        </div>
      </div>
    </div>
  </div>
  <!--Footer-part-->
  <div class="row-fluid">
    <div id="footer" class="span12"> <a href="https://github.com/RamonSilva20/mapos" target="_blank">
        <?php echo date('Y'); ?> &copy; MAP OS - Ramon Silva </a></div>
  </div>
  <!--end-Footer-part-->
  <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
  <script src="<?php echo base_url(); ?>assets/js/matrix.js"></script>
</body>

</html>
