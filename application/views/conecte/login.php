<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?php echo $this->config->item('app_name') ?></title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="<?php echo $this->config->item('app_name') . ' - ' . $this->config->item('app_subname') ?>">
    <link rel="stylesheet" href="<?php echo base_url() ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url() ?>assets/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="<?php echo base_url() ?>assets/css/matrix-login.css" />
    <link href="<?= base_url('assets/css/particula.css'); ?>" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <script src="<?php echo base_url() ?>assets/js/jquery-1.12.4.min.js"></script>
    <link rel="shortcut icon" href="<?php echo base_url(); ?>assets/img/favicon.png">
    <script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
    <script src="<?php echo base_url() ?>assets/js/funcoes.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <!-- Script webeddy.com.br -->
    <script>
        function formatar(mascara, documento) {
            var i = documento.value.length;
            var saida = mascara.substring(0, 1);
            var texto = mascara.substring(i)

            if (texto.substring(0, 1) != saida) {
                documento.value += texto.substring(0, 1);
            }
        }
    </script>
</head>
<?php
$parse_email = $this->input->get('e');
    $parse_cpfcnpj = $this->input->get('c');
    ?>

<body>
    <div class="main-login">
        <div class="left-login">
            <h1 class="h-one">Área do Cliente</h1>
            <img src="<?php echo base_url() ?>assets/img/forms-animate.svg" class="left-login-imagec" alt="Map-OS 5.0">
        </div>

        <div id="loginbox">
            <form class="form-vertical" id="formLogin" method="post" action="<?php echo site_url() ?>/mine/login">

                <div class="d-flex flex-column">
                    <div class="right-login">
                        <div class="container">
                            <div class="card card-cad">
                                <div class="content">
                                    <div id="newlog">
                                        <div class="icon2">
                                            <img src="<?php echo base_url() ?>assets/img/logo-two.png">
                                        </div>
                                        <div class="title01">
                                            <img src="<?php echo base_url() ?>assets/img/logo-mapos-branco.png">
                                        </div>
                                    </div>
                                    <div id="mcell">Versão: <?= $this->config->item('app_version'); ?></div>
                                    <div class="control-group">
                                        <div class="controls">
                                            <div class="main_input_box">
                                                <span class="add-on bg_lg"><i class='bx bx-user-plus iconU'></i></span>
                                                <input id="email" name="email" type="text" placeholder="Email" value="<?php echo trim($parse_email); ?>" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="control-group">
                                        <div class="controls">
                                            <div class="main_input_box">
                                                <span class="add-on bg_ly"><i class='bx bx-id-card iconU'></i></span>
                                                <input class="" maxlength="18" size="18" name="senha" type="password" placeholder="Senha" value="" />
                                            </div>
                                        </div>
                                    </div>

                                    <button style="margin: 0" class="btn btn-info btn-large"> Acessar</button>
                                    <a href="<?= site_url('mine/cadastrar') ?>" class="btn btn-success btn-large">Cadastrar-me</a>
                                    <div class="links-uteis"><a href="<?= site_url('mine/resetarSenha') ?>">
                                            <p style="margin:0px 0 18px">Esqueceu a senha?</p>
                                        </a></div>
                                    <div class="links-uteis"><a href="https://github.com/RamonSilva20/mapos">
                                            <p><?= date('Y'); ?> &copy; Ramon Silva</p>
                                        </a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>

    <script src="<?php echo base_url() ?>assets/js/bootstrap.min.js"></script>
    <script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
    <script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
    <?php if ($this->session->flashdata('success') != null) { ?>
        <script>
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: '<?php echo $this->session->flashdata('success'); ?>',
                showConfirmButton: false,
                timer: 4000
            })
        </script>
    <?php } ?>

    <?php if ($this->session->flashdata('error') != null) { ?>
        <script>
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: '<?php echo $this->session->flashdata('error'); ?>',
                showConfirmButton: false,
                timer: 4000
            })
        </script>
    <?php } ?>

    <script type="text/javascript">
        $(document).ready(function() {

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
                        required: 'Campo Requerido.',
                        email: 'Insira Email válido'
                    },
                    senha: {
                        required: 'Campo Requerido.'
                    }
                },
                submitHandler: function(form) {
                    var dados = $(form).serialize();


                    $.ajax({
                        type: "POST",
                        url: "<?php echo base_url(); ?>index.php/mine/login?ajax=true",
                        data: dados,
                        dataType: 'json',
                        success: function(data) {
                            if (data.result == true) {
                                window.location.href = "<?php echo base_url(); ?>index.php/mine/painel";
                            } else {
                                Swal.fire({
                                    position: 'center',
                                    icon: 'error',
                                    title: 'Os dados de acesso estão incorretos.\n Por favor tente novamente!',
                                    showConfirmButton: false,
                                    timer: 4000
                                })
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
