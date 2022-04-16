<!DOCTYPE html>
<html lang="pt-br">
<head>
<title><?= $this->config->item('app_name') ?> </title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/matrix-login.css" />
    <link href="<?= base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link rel="shortcut icon" type="image/png" href="<?= base_url(); ?>assets/img/favicon.png" />
    <script src="<?= base_url() ?>assets/js/jquery-1.12.4.min.js"></script>
</head>

<style>
p {
  color     : #aaa6b1;
  text-align: left;
  font-size : 0.9em;
}
.modal-dialog {
  position: relative;
  width   : 600px;
  margin  : 50px auto;
}
.links-uteis {
  margin-top: -5px;
}
</style>

<body>
<div class="main-login">
  <div class="left-login">

<!-- Saudação -->
<h1 class="h-one">
    <?php
    function saudacao($nome = '')
    {
        date_default_timezone_set('America/Sao_Paulo');
        $hora = date('H');
        if ($hora >= 6 && $hora <= 12) {
            return 'Olá! Bom dia' . (empty($nome) ? '' : ', ' . $nome);
        } elseif ($hora > 12 && $hora <=18) {
            return 'Olá! Boa tarde' . (empty($nome) ? '' : ', ' . $nome);
        } else {
            return 'Olá! Boa noite' . (empty($nome) ? '' : ', ' . $nome);
        }
    }
$login = 'bem-vindos';
echo saudacao($login);

// Irá retornar conforme o horário:
?></h1>

<h2 class="h-two"> Ao Sistema de Controle de Ordens de Serviço</h2>
    <img src="<?php echo base_url() ?>assets/img/dashboard-animate.svg" class="left-login-image" alt="Map-OS - Versão: <?= $this->config->item('app_version'); ?>">
</div>
<div id="loginbox">
    <form class="form-vertical" id="formLogin" method="post" action="<?= site_url('login/verificarLogin') ?>">
    <?php if ($this->session->flashdata('error') != null) { ?>
        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?= $this->session->flashdata('error'); ?>
        </div>
    </div>
  <?php } ?>
  <div class="d-flex flex-column">
      <div class="right-login">
        <div class="container">
          <div class="card">
            <div class="content">
              <div id="newlog">
                <div class="icon2">
                  <img src="<?php echo base_url() ?>assets/img/logo-two.png">
                </div>
                <div class="title01">
                  <?= $configuration['app_theme'] == 'white' ? '<img src="'. base_url() .'assets/img/logo-mapos.png">' : '<img src="'. base_url() .'assets/img/logo-mapos-branco.png">'; ?>
                </div>
              </div>
              <div id="mcell">Versão: <?= $this->config->item('app_version'); ?></div>
              <form action="index.html" method="post">
                <div class="input-field">
                  <label class="fas fa-user" for="nome"></label>
                  <input id="email" name="email" type="text" placeholder="Email">
                </div>
                <div class="input-field">
                  <label class="fas fa-lock" for="senha"></label>
                <input name="senha" type="password" placeholder="Senha">
              </div>
              <div class="center"><button id="btn-acessar">Acessar</button>
              </div>
              <div class="links-uteis"><p style="color: var(--cinza1);font-size:0.9em"><p><span class="text-danger">Esqueceu sua senha?</span><br>
                Não se preocupe! <a href="#" style="color: #d9d5df" class="text-danger" data-toggle="modal" data-target="#myModal">Clique aqui</a> para redefinir </p>
              </div>
              <a href="#notification" id="call-modal" role="button" class="btn" data-toggle="modal" style="display: none ">notification</a>
              <div id="notification" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-header">
                  <h4 id="myModalLabel">Map-OS</h4>
                </div>
                <div class="modal-body">
                  <h5 style="text-align: center" id="message">Os dados de acesso estão incorretos, por favor tente novamente!</h5>
                </div>
                <div class="modal-footer">
                  <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Fechar</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <a href="#notification" id="call-modal" role="button" class="btn" data-toggle="modal" style="display: none ">notification</a>
    <div id="notification" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-header">
        <h4 id="myModalLabel">Map-OS</h4>
      </div>
      <div class="modal-body">
        <h5 style="text-align: center" id="message">Os dados de acesso estão incorretos, por favor tente novamente!</h5>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Fechar</button>
      </div>

     <!-- modal recuperar senha-->
    <div class="modal-dialog">
            <div class="modal-content">
                <form action="http://localhost/pos/auth/forgot_password" method="post" accept-charset="utf-8">
                  <input type="hidden" name="spos_token" value="8892d1e3e0d3ede7c12a12b8489a550a">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Trocar senha?</h4>
                </div>
                <div class="modal-body">
                    <p>Entre com seu endereço de e-mail para enviarmos outra senha</p>
                    <input type="email" name="forgot_email" placeholder="E-mail" autocomplete="off" class="form-control placeholder-no-fix">
                </div>
                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-default pull-left" type="button">Fechar</button>
                    <button class="btn btn-primary" type="submit">Enviar</button>
                </div>
            </div>

<script src="<?= base_url() ?>assets/js/bootstrap.min.js"></script>
<script src="<?= base_url() ?>assets/js/validate.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    $('#email').focus();
    $("#formLogin").validate({
        rules: {
            email: {
                required: true,
                email: true
            },
            senha: {
                required: true
            }
        },
        messages: {
            email: {
                required: '',
                email: 'Insira Email válido'
            },
            senha: {
                required: 'Campos Requeridos.'
            }
        },
        submitHandler: function(form) {
                    var dados = $(form).serialize();
                    $('#btn-acessar').addClass('disabled');
                    $('#progress-acessar').removeClass('hide');

                    $.ajax({
                        type: "POST",
                        url: "<?= site_url('login/verificarLogin?ajax=true'); ?>",
                        data: dados,
                        dataType: 'json',
                        success: function(data) {
                            if (data.result == true) {
                                window.location.href = "<?= site_url('mapos'); ?>";
                            } else {


                                $('#btn-acessar').removeClass('disabled');
                                $('#progress-acessar').addClass('hide');

                                $('#message').text(data.message || 'Os dados de acesso estão incorretos, por favor tente novamente!');
                                $('#call-modal').trigger('click');
                            }
                        }
                    });

                    return false;
                },

                errorClass: "help-inline",
                errorElement: "span",
                highlight: function(element, errorClass, validClass) {
                    $(element).parents('.control-group').addClass('error');
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).parents('.control-group').removeClass('error');
                    $(element).parents('.control-group').addClass('success');
                }
            });
        });

</script>
</body>
</html>
