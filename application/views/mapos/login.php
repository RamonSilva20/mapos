<!DOCTYPE html>
<html lang="pt-br">

<head>
  <title><?= $this->config->item('app_name') ?></title>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" href="<?= base_url() ?>assets/img/favicon.png" type="image/png" />
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap-responsive.min.css" />
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/matrix-login.css" />
  <link rel="stylesheet" href="<?= base_url() ?>assets/font-awesome/css/font-awesome.css" />
</head>

<body>
  <div class="main-login">
    <div class="left-login">
      <!-- Saudação -->
      <h1 class="h-one"><?=$saudacao?></h1>
      <h2 class="h-two">Ao Sistema de Controle de Ordens de Serviço</h2>
      <img src="<?=base_url()?>assets/img/dashboard-animate.svg" class="left-login-image" alt="<?= $this->config->item('app_name') ?> - Versão: <?= $this->config->item('app_version') ?>">
    </div>
    <form class="form-vertical" id="formLogin" method="post" action="<?= site_url('login/verificarLogin') ?>">
      <div id="loginbox">
          <?php if ($this->session->flashdata('error') != null) : ?>
            <div class="alert alert-danger">
              <button type="button" class="close" data-dismiss="alert">&times;</button>
              <?= $this->session->flashdata('error'); ?>
            </div>
          <?php endif; ?>
      </div>
      <div class="d-flex flex-column">
        <div class="right-login">
          <div class="container">
            <div class="card">
              <div class="content">
                <div id="newlog">
                  <div class="icon2">
                    <img src="<?=base_url()?>assets/img/logo-two.png">
                  </div>
                  <div class="title01">
                    <img src="<?=base_url()?>assets/img/logo-mapos-branco.png">
                  </div>
                </div>
                <div id="mcell">Versão: <?= $this->config->item('app_version'); ?></div>
                  <div class="input-field">
                    <label class="fas fa-user" for="nome"></label>
                    <input id="email" name="email" type="text" placeholder="Email">
                  </div>
                  <div class="input-field">
                    <label class="fas fa-lock" for="senha"></label>
                    <input name="senha" type="password" placeholder="Senha">
                  </div>
                  <div class="center">
                    <button id="btn-acessar">Acessar</button>
                  </div>
                  <div class="links-uteis"><a href="https://github.com/RamonSilva20/mapos">
                      <p><?= date('Y') ?> &copy; Ramon Silva</p>
                    </a>
                  </div>

                  <!-- Modal de notificação -->
                  <div id="notification" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-header">
                      <h4 id="myModalLabel"><?= $this->config->item('app_name') ?></h4>
                    </div>
                    <div class="modal-body">
                      <h5 style="text-align: center" id="message">Os dados de acesso estão incorretos, por favor tente novamente!</h5>
                    </div>
                    <div class="modal-footer">
                      <button class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Fechar</button>
                    </div>
                  </div>
                  <!-- Fim Modal de notificação -->

                </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <script type="text/javascript" src="<?= base_url() ?>assets/js/jquery-1.12.4.min.js"></script>
  <script type="text/javascript" src="<?= base_url() ?>assets/js/bootstrap.min.js"></script>
  <script type="text/javascript" src="<?= base_url() ?>assets/js/validate.js"></script>
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

          $.ajax({
            type: "POST",
            url: "<?= site_url('login/verificarLogin?ajax=true') ?>",
            data: dados,
            dataType: 'json',
            success: function(data) {
              if (data.result == true) {
                window.location.href = "<?= site_url($redirect) ?>";
              } else {
                $('#btn-acessar').removeClass('disabled');
                $('#message').text(data.message || 'Os dados de acesso estão incorretos, por favor tente novamente!');
                $('#notification').modal('show');
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
