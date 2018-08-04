<!DOCTYPE html>
<html lang="pt-BR">
<!-- PHP-Part -->
<?php $operador = $this->session->userdata('nome'); ?>
<head>
  <title>MapOS</title>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  
  <!-- CSS -->
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.3.2/css/bootstrap.min.css" />
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.3.2/css/bootstrap-responsive.min.css" />
  <!-- <link rel="stylesheet" href="<?php //echo base_url();?>assets/css/bootstrap.min.css" /> -->
  <!-- <link rel="stylesheet" href="<?php //echo base_url();?>assets/css/bootstrap-responsive.min.css" /> -->
  <link rel="stylesheet" href="<?php echo base_url();?>assets/css/matrix-style.css" />
  <link rel="stylesheet" href="<?php echo base_url();?>assets/css/matrix-media.css" />
  <link rel="stylesheet" href="<?php echo base_url();?>assets/css/ui.print.css" media="print" type="text/css" >
  <link rel="stylesheet" href="<?php echo base_url();?>assets/font-awesome/css/font-awesome.css" />
  <link rel="stylesheet" href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' type='text/css'>
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.9.0/fullcalendar.min.css" /> 
  <link rel="stylesheet" href="//cdn.jsdelivr.net/qtip2/3.0.3/basic/jquery.qtip.min.css" /> 
  <!-- <link rel="stylesheet" href="<?php //echo base_url();?>assets/css/fullcalendar.css" />  -->

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  
</head>

<body>
<input type="hidden" id="user_name" name="user_name" value="<?php echo $operador ?>" />

<!--Header-part-->

<div id="header">

  <h1><a href="">MapOS</a></h1>

</div>

<!--close-Header-part--> 



<!--top-Header-menu-->

<div id="user-nav" class="navbar navbar-inverse">

  <ul class="nav">

   
    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vOs')){ ?>
    <li class=""><a title="" href="<?php echo base_url();?>index.php/mapos/minhaConta"><i class="icon icon-star"></i> <span class="text"><?php echo $operador ?></span></a></li>
    <?php } ?>
    <li class=""><a title="" href="<?php echo base_url();?>index.php/mapos/sair"><i class="icon icon-share-alt"></i> <span class="text">Sair do Sistema</span></a></li>

  </ul>

</div>



<!--start-top-serch-->
<?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vOs')){ ?>
<div id="search">
  <form action="<?php echo base_url()?>index.php/mapos/pesquisar">
    <input type="text" name="termo" placeholder="Pesquisar..."/>
    <button type="submit"  class="tip-bottom" title="Pesquisar"><i class="icon-search icon-white"></i></button>
  </form>
</div>
<?php } ?>
<!--close-top-serch--> 



<!--sidebar-menu-->


<?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vOs')){ ?>
<div id="sidebar"> <a href="#" class="visible-phone"><i class="icon icon-list"></i> Menu</a>

  <ul>
    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vOs')){ ?>
    <li class="<?php if(isset($menuPainel)){echo 'active';};?>"><a href="<?php echo base_url()?>"><i class="icon icon-home"></i> <span>Dashboard</span></a></li>
    <?php } ?>
    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vCliente')){ ?>
        <li class="<?php if(isset($menuClientes)){echo 'active';};?>"><a href="<?php echo base_url()?>index.php/clientes"><i class="icon icon-group"></i> <span>Clientes</span></a></li>
    <?php } ?>

    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vProduto')){ ?>
        <li class="<?php if(isset($menuProdutos)){echo 'active';};?>"><a href="<?php echo base_url()?>index.php/produtos"><i class="icon icon-barcode"></i> <span>Produtos</span></a></li>
    <?php } ?>

    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vServico')){ ?>
        <li class="<?php if(isset($menuServicos)){echo 'active';};?>"><a href="<?php echo base_url()?>index.php/servicos"><i class="icon icon-wrench"></i> <span>Serviços</span></a></li>
    <?php } ?>

    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vCompra')){ ?>
        <li class="<?php if(isset($menuCompras)){echo 'active';};?>"><a href="<?php echo base_url()?>index.php/compras"><i class="icon icon-shopping-cart"></i> <span>Compras</span></a></li>
    <?php } ?>

    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vOs')){ ?>
        <li class="submenu <?php if(isset($menuOs)){echo 'active open';};?>">
          <a href="#"><i class="icon icon-tags"></i> <span>Ordens de Serviço</span> <span class="label"><i class="icon-chevron-down"></i></span></a>
          <ul>
            <li><a href="<?php echo base_url()?>index.php/os/adicionar">Adicionar</a></li>
            <li><a href="<?php echo base_url()?>index.php/os">Todas</a></li>
            <li><a href="<?php echo base_url()?>index.php/os/minhas">Minhas</a></li>
            <li><a href="<?php echo base_url()?>index.php/historico">Histórico</a></li>
          </ul>
        </li>
    <?php } ?>

    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vVenda')){ ?>
        <li class="<?php if(isset($menuVendas)){echo 'active';};?>"><a href="<?php echo base_url()?>index.php/vendas"><i class="icon icon-shopping-cart"></i> <span>Vendas</span></a></li>
    <?php } ?>

    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vArquivo')){ ?>
        <li class="<?php if(isset($menuArquivos)){echo 'active';};?>"><a href="<?php echo base_url()?>index.php/arquivos"><i class="icon icon-hdd"></i> <span>Arquivos</span></a></li>
    <?php } ?>

    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vLancamento')){ ?>
        <li class="submenu <?php if(isset($menuFinanceiro)){echo 'active open';};?>">
          <a href="#"><i class="icon icon-money"></i> <span>Financeiro</span> <span class="label"><i class="icon-chevron-down"></i></span></a>
          <ul>
            <li><a href="<?php echo base_url()?>index.php/financeiro/lancamentos">Lançamentos</a></li>
            <li><a href="<?php echo base_url()?>index.php/financeiro/folha">Folha</a></li>
          </ul>
        </li>

    <?php } ?>

    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'rCliente') || $this->permission->checkPermission($this->session->userdata('permissao'),'rProduto') || $this->permission->checkPermission($this->session->userdata('permissao'),'rServico') || $this->permission->checkPermission($this->session->userdata('permissao'),'rCompra') || $this->permission->checkPermission($this->session->userdata('permissao'),'rOs') || $this->permission->checkPermission($this->session->userdata('permissao'),'rFinanceiro') || $this->permission->checkPermission($this->session->userdata('permissao'),'rVenda')){ ?>
        <li class="submenu <?php if(isset($menuRelatorios)){echo 'active open';};?>" >
          <a href="#"><i class="icon icon-list-alt"></i> <span>Relatórios</span> <span class="label"><i class="icon-chevron-down"></i></span></a>
          <ul>
            <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'rCliente')){ ?>
                <li><a href="<?php echo base_url()?>index.php/relatorios/clientes">Clientes</a></li>
            <?php } ?>

            <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'rProduto')){ ?>
                <li><a href="<?php echo base_url()?>index.php/relatorios/produtos">Produtos</a></li>
            <?php } ?>

            <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'rServico')){ ?>
                <li><a href="<?php echo base_url()?>index.php/relatorios/servicos">Serviços</a></li>
            <?php } ?>

            <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'rOs')){ ?>
                 <li><a href="<?php echo base_url()?>index.php/relatorios/os">Ordens de Serviço</a></li>
            <?php } ?>

            <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'rCompra')){ ?>
                <li><a href="<?php echo base_url()?>index.php/relatorios/compras">Compras</a></li>
            <?php } ?>

            <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'rVenda')){ ?>
                <li><a href="<?php echo base_url()?>index.php/relatorios/vendas">Vendas</a></li>
            <?php } ?>

            <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'rFinanceiro')){ ?>
                <li><a href="<?php echo base_url()?>index.php/relatorios/financeiro">Financeiro</a></li>
            <?php } ?>
          </ul>
        </li>
    <?php } ?>

    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'cUsuario')  || $this->permission->checkPermission($this->session->userdata('permissao'),'cEmitente') || $this->permission->checkPermission($this->session->userdata('permissao'),'cPermissao') || $this->permission->checkPermission($this->session->userdata('permissao'),'cBackup')){ ?>
        <li class="submenu <?php if(isset($menuConfiguracoes)){echo 'active open';};?>">
          <a href="#"><i class="icon icon-cog"></i> <span>Configurações</span> <span class="label"><i class="icon-chevron-down"></i></span></a>
          <ul>
            <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'cUsuario')){ ?>
                <li><a href="<?php echo base_url()?>index.php/usuarios">Usuários</a></li>
            <?php } ?>

            <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'cEmitente')){ ?>
                <li><a href="<?php echo base_url()?>index.php/mapos/emitente">Emitente</a></li>
            <?php } ?>

            <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'cPermissao')){ ?>
                <li><a href="<?php echo base_url()?>index.php/permissoes">Permissões</a></li>
            <?php } ?>

            <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'cBackup')){ ?>
                <li><a href="<?php echo base_url()?>index.php/mapos/backup">Backup</a></li>
            <?php } ?>
          </ul>
        </li>
    <?php } ?>
  </ul>

</div>
<?php } ?>


<div id="content">
  <div id="content-header">
    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vOs')){ ?>
    <div id="breadcrumb"> <a href="<?php echo base_url()?>" title="Dashboard" class="tip-bottom"><i class="icon-home"></i> Dashboard</a> <?php if($this->uri->segment(1) != null){?><a href="<?php echo base_url().'index.php/'.$this->uri->segment(1)?>" class="tip-bottom" title="<?php echo ucfirst($this->uri->segment(1));?>"><?php echo ucfirst($this->uri->segment(1));?></a> <?php if($this->uri->segment(2) != null){?><a href="<?php echo base_url().'index.php/'.$this->uri->segment(1).'/'.$this->uri->segment(2) ?>" class="current tip-bottom" title="<?php echo ucfirst($this->uri->segment(2)); ?>"><?php echo ucfirst($this->uri->segment(2));} ?></a> <?php }?></div>
    <?php } ?>
  </div>

  <div class="container-fluid">
    <div class="row-fluid">
      <div class="span12">
          <?php if($this->session->flashdata('error') != null){?>
            <div class="alert alert-danger">
              <button type="button" class="close" data-dismiss="alert">&times;</button>
              <?php echo $this->session->flashdata('error');?>
            </div>
          <?php }?>

          <?php if($this->session->flashdata('success') != null){?>
            <div class="alert alert-success">
              <button type="button" class="close" data-dismiss="alert">&times;</button>
              <?php echo $this->session->flashdata('success');?>
            </div>
          <?php }?>
          <?php if(isset($view)){echo $this->load->view($view);}?>
      </div>
    </div>
  </div>
</div><!-- Close id="content" -->

<!--Footer-part-->
<div class="row-fluid">
  <div id="footer" class="span12"> 2013 - <?php echo date('Y') ?> &copy; MapOS - Versão: 2.6.4 | Custom - Solução em Informática</div>
</div>
<!--end-Footer-part-->

<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<script src="<?php echo base_url();?>assets/js/matrix.js"></script>

<script src="<?php echo base_url();?>assets/js/moment.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.9.0/fullcalendar.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.9.0/lang/pt-br.js"></script>
<script src="//cdn.jsdelivr.net/qtip2/3.0.3/basic/jquery.qtip.min.js"></script>

<!-- BEGIN CODE FOR USER ACTIVITY LOG -->  
  <script type="text/javascript" src="<?php echo base_url();?>js/html2canvas/html2canvas.js"></script>
  <script type="text/javascript" src="<?php echo base_url();?>js/html2canvas/jquery.plugin.html2canvas.js"></script>
  <script type="text/javascript" src="<?php echo base_url();?>js/trackuser.jquery.js"></script>
<!-- END CODE FOR USER ACTIVITY LOG -->  

<div style="display:none;"><canvas id="image-canvas" style="display:none;"></canvas></div>
</body>
</html>