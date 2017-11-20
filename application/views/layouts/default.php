<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> <?php echo $this->config->item('app_name'); ?></title>


    <link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url(); ?>assets/fonts/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/nprogress.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/custom.min.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/datatables.min.css" rel="stylesheet">

  </head>

    
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">
          <div class="left_col scroll-view">
            <div class="navbar nav_title text-center" style="border: 0;">
              <a href="<?= base_url(); ?>" class="site_title"><span><img src="<?php echo base_url(); ?>assets/img/logo.png" alt="" style="max-width: 200px; max-height: 80px"></span></a>
            </div>

            <div class="clearfix"></div>

            <br />

            <!-- sidebar menu -->
            <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
              <div class="menu_section">
                <h3>Menu</h3>
                <ul class="nav side-menu">
                    
                    <li class="<?php if(isset($menu_dashboard)){ echo 'active'; } ?>">
                      <a href="<?= base_url(); ?>">
                        <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                      </a>
                    </li>

                    <li class="">
                      <a href="<?= site_url('persons'); ?>">
                        <i class="fa fa-user"></i> <span>Pessoas</span>
                      </a>
                    </li>

                    <li><a><i class="fa fa-edit"></i> Cadastros <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu" style="">
                        <li><a href="<?= site_url('brands'); ?>">Marcas</a></li>
                        <li><a href="index2.html">Equipamentos</a></li>
                        <li><a href="index3.html">Veículos</a></li>
                        <li><a href="<?= site_url('services') ?>">Serviços</a></li>
                        <li><a href="index3.html">Produtos</a></li>
                      </ul>
                    </li>
                    <li><a><i class="fa fa-ticket"></i> Ordens de Serviço <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu">
                        <li><a href="form_advanced.html">Advanced Components</a></li>
                        <li><a href="form_validation.html">Form Validation</a></li>
                        <li><a href="form_wizards.html">Form Wizard</a></li>
                        <li><a href="form_upload.html">Form Upload</a></li>
                        <li><a href="form_buttons.html">Form Buttons</a></li>
                      </ul>
                    </li>
                    <li class=""><a><i class="fa fa-shopping-cart"></i> Vendas <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu" style="display: none;">
                        <li><a href="general_elements.html">General Elements</a></li>
                        <li><a href="media_gallery.html">Media Gallery</a></li>
                       
                      </ul>
                    </li>
                    <li><a><i class="fa fa-dollar"></i> Financeiro <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu" style="">
                        <li><a href="tables.html">Tables</a></li>
                        <li><a href="tables_dynamic.html">Table Dynamic</a></li>
                      </ul>
                    </li>
                    <li><a><i class="fa fa-pie-chart"></i> Relatórios <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu" style="">
                        <li><a href="tables.html">Tables</a></li>
                        <li><a href="tables_dynamic.html">Table Dynamic</a></li>
                      </ul>
                    </li>

                    <li><a><i class="fa fa-cogs"></i> Configurações <span class="fa fa-chevron-down"></span></a>
                      <ul class="nav child_menu" style="">
                        <li><a href="<?= site_url('users') ?>">Usuários</a></li>
                        <li><a href="<?= site_url('groups') ?>">Grupos</a></li>
                        <li><a href="<?= site_url('config/email') ?>">E-mail</a></li>
                        <li><a href="<?= site_url('config/syste,') ?>">Sistema</a></li>
                      </ul>
                    </li>


                </ul>
              </div>

            </div>
            <!-- /sidebar menu -->

          </div>
        </div>

        <!-- top navigation -->
        <div class="top_nav">
          <div class="nav_menu">
            <nav>
              <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
              </div>

              <ul class="nav navbar-nav pull-right">
                <li role="presentation" class="dropdown">
                  <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-envelope-o"></i>
                    <span class="badge bg-green">6</span>
                  </a>
                  <ul id="menu1" class="dropdown-menu list-unstyled msg_list" role="menu">
                    <li>
                      <a>
                        <span class="image"><img src="<?php echo base_url() ?>assets/img/user.png" alt="Profile Image"></span>
                        <span>
                          <span>John Smith</span>
                          <span class="time">3 mins ago</span>
                        </span>
                        <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                      </a>
                    </li>
                    <li>
                      <a>
                        <span class="image"><img src="<?php echo base_url() ?>assets/img/user.png" alt="Profile Image"></span>
                        <span>
                          <span>John Smith</span>
                          <span class="time">3 mins ago</span>
                        </span>
                        <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                      </a>
                    </li>
                    <li>
                      <a>
                        <span class="image"><img src="<?php echo base_url() ?>assets/img/user.png" alt="Profile Image"></span>
                        <span>
                          <span>John Smith</span>
                          <span class="time">3 mins ago</span>
                        </span>
                        <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                      </a>
                    </li>
                    <li>
                      <a>
                        <span class="image"><img src="<?php echo base_url() ?>assets/img/user.png" alt="Profile Image"></span>
                        <span>
                          <span>John Smith</span>
                          <span class="time">3 mins ago</span>
                        </span>
                        <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                      </a>
                    </li>
                    <li>
                      <div class="text-center">
                        <a>
                          <strong>See All Alerts</strong>
                          <i class="fa fa-angle-right"></i>
                        </a>
                      </div>
                    </li>
                  </ul>
                </li>

                <li class="">
                  <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo base_url() ?>assets/img/user.png" alt="">Admin
                    <span class=" fa fa-angle-down"></span>
                  </a>
                  <ul class="dropdown-menu dropdown-usermenu pull-right">
                    <li><a href="javascript:;"> Perfil <i class="fa fa-user pull-right"></i></a></li>
                    <li>
                      <a href="javascript:;"> <i class="fa fa-cogs pull-right"></i>
                        <span>Configurações</span>
                      </a>
                    </li>
                    <li><a href="javascript:;">Ajuda <i class="fa fa-question-circle pull-right"></i></a></li>
                    <li><a href="<?= site_url('auth/logout'); ?>"><i class="fa fa-sign-out pull-right"></i> Sair</a></li>
                  </ul>
                </li>

              </ul>
            </nav>
          </div>
        </div>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="page-title">
              <div class="title_left">

                <section class="content-header">
                    <ol class="breadcrumb">
                        <?= get_breadcrumb(); ?>
                    </ol>
                </section>
              </div>

            </div>

            <!-- jQuery, Bootstrap e Datatables-->
            <script src="<?php echo base_url(); ?>assets/js/datatables.min.js"></script>

            <!-- Bootstrap Notify-->
            <script type="text/javascript" src="<?php echo base_url() ?>assets/js/bootstrap-notify.min.js"></script>

            <div class="row">
              <div class="col-md-12">
                <?php echo $template['body']; ?>                
              </div>
            </div>
          </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
        <footer>
          <div class="pull-right">
            @Ramon Silva - <?= $this->config->item('app_name')  ?> [2013 - <?php echo date('Y') ?>] - <?= $this->lang->line('app_version') ?>: <?= $this->config->item('app_version');  ?>
          </div>
          <div class="clearfix"><?= $this->lang->line('app_load_time')  ?>: <?php echo $this->benchmark->elapsed_time();?> <?= $this->lang->line('app_seconds') ?></div>
        </footer>
        <!-- /footer content -->
      </div>
    </div>


    <!-- FastClick -->
    <script src="<?php echo base_url(); ?>assets/js/fastclick.js"></script>
    <!-- NProgress -->
    <script src="<?php echo base_url(); ?>assets/js/nprogress.js"></script>

    <!-- Custom Theme Scripts -->
    <script src="<?php echo base_url(); ?>assets/js/custom.min.js"></script>

    <script src="<?= base_url() ?>assets/js/jquery-ui-1.12.1/jquery-ui.min.js"></script>

    <script type="text/javascript">
      
      $.datepicker.regional['pt-BR'] = {
          closeText: 'Fechar',
          prevText: 'Anterior',
          nextText: 'Pr&oacute;ximo',
          currentText: 'Hoje',
          monthNames: ['Jan','Fev','Mar','Abr','Mai','Jun',
          'Jul','Ago','Set','Out','Nov','Dez'],
          monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun',
          'Jul','Ago','Set','Out','Nov','Dez'],
          dayNames: ['Domingo','Segunda-feira','Ter&ccedil;a-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sabado'],
          dayNamesShort: ['D','S','T','Q','Q','S','S'],
          dayNamesMin: ['D','S','T','Q','Q','S','S'],
          weekHeader: 'Sm',
          dateFormat: 'dd/mm/yy',
          firstDay: 0,
          isRTL: false,
          showMonthAfterYear: false,
          yearSuffix: ''};
      $.datepicker.setDefaults($.datepicker.regional['pt-BR']);
      $(".datepicker" ).datepicker({ dateFormat: 'dd/mm/yy' });


      jQuery(document).ready(function($) {
          
          // notifications
          <?php if($this->session->flashdata('success') != null) {?>
            $.notify({ message: '<?php echo $this->session->flashdata('success'); ?>' },{ type: 'success' });
          <?php } ?>

          <?php if($this->session->flashdata('error') != null) { ?>
            $.notify({ message: '<?php echo $this->session->flashdata('error'); ?>' },{ type: 'danger' });
          <?php } ?>

      });


  </script>

  </body>
</html>