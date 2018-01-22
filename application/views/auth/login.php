<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $this->config->item('app_name'); ?></title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/login.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

</head>
<body class="hold-transition login-page" style="background-image: url(<?php echo base_url(); ?>assets/img/background.jpg)">
<div class="login-box">
  <div class="login-logo">
    <img src="<?php echo base_url(); ?>assets/img/logo.png" class="img-responsive">
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body">
    <h3 class="login-box-msg"><?php echo lang('login_heading');?></h3>
    <p class="alert alert-info"><?php echo lang('login_subheading');?></p>
    <hr>

    <form action="<?php echo site_url('auth/login') ?>" method="post">
      <?php if($message){ ?>
        <div id="infoMessage" class="alert alert-danger"><?php echo $message;?></div>
      <?php } ?>
      <div class="form-group has-feedback">
        <?php echo form_input($identity,'',array('class' => 'form-control', 'placeholder' => lang('login_identity_label'), 'type' => 'email'));?>
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <?php echo form_input($password,'',array('class' => 'form-control', 'placeholder' => lang('login_password_label') ));?>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="row">
        <div class="col-xs-8">
            <label>
              <input type="checkbox"> <?php echo lang('login_remember_label'); ?>
            </label>
        </div>
        <!-- /.col -->
        <div class="col-xs-4">
          <button type="submit" class="btn btn-primary btn-block btn-flat"><?php echo lang('login_submit_btn'); ?></button>
        </div>
        <!-- /.col -->
      </div>
    </form>

    <a href="<?php echo site_url('auth/forgot_password') ?>"><?php echo lang('login_forgot_password');?></a><br>

  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

<!-- jQuery 2.2.3 -->
<script src="<?php echo base_url(); ?>assets/js/jquery-2.1.4.min.js"></script>

</body>
</html>
