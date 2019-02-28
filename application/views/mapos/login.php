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
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('tema/assets/images/favicon.png'); ?>">
    <title>Order de serviços</title>
    <!-- Bootstrap Core CSS -->
    <link href="<?= base_url('tema/assets/css/lib/bootstrap/bootstrap.min.css'); ?>" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= base_url('tema/assets/css/helper.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('tema/assets/css/style.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('tema/assets/css/lib/sweetalert/sweetalert.css'); ?>" rel="stylesheet">
    <style>
        .error {
            border-color: #fc6180;
        }
    </style>
</head>

<body class="fix-header fix-sidebar">
    <!-- Preloader - style you can find in spinners.css -->
    <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" /> </svg>
    </div>
    <!-- Main wrapper  -->
    <div id="main-wrapper">
        <div class="unix-login">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-lg-4">
                        <div class="login-content card">
                            <div class="login-form">
                                <h4>
                                    <img class="img-responsive" src="<?= base_url('tema/assets/images/teste-3.png') ?>" alt="LOGIN" />
                                </h4>
                                <form id="form-login" method="post" action="<?= site_url('tema/mapos/verificarLogin') ?>">
                                    <div class="form-group" id="progress-acessar" style="display: none">
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <br>
                                        <input id="email" name="email" class="form-control" type="text" placeholder="Email" />
                                    </div>
                                    <div class="form-group">
                                        <br>
                                        <input name="senha" class="form-control" type="password" placeholder="Senha" />
                                    </div>
                                    <button type="submit" id="btn-acessar" class="btn btn-info btn-flat m-b-30 m-t-30">Acessar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Wrapper -->
    <script src="<?= base_url('tema/assets/js/lib/jquery/jquery.min.js'); ?>"></script>
    <script src="<?= base_url('tema/assets/js/lib/form-validation/jquery.validate.min.js') ?>"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="<?= base_url('tema/assets/js/jquery.slimscroll.js'); ?>"></script>
    <!--Menu sidebar -->
    <script src="<?= base_url('tema/assets/js/sidebarmenu.js'); ?>"></script>
    <!--stickey kit -->
    <script src="<?= base_url('tema/assets/js/lib/sticky-kit-master/dist/sticky-kit.min.js'); ?>"></script>
    <!--Custom JavaScript -->
    <script src="<?= base_url('tema/assets/js/scripts.js'); ?>"></script>
    <script src="<?= base_url('tema/assets/js/lib/sweetalert/sweetalert.min.js'); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#email').focus();
            $("#form-login").validate({
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
                        required: 'Insira o e-mail.',
                        email: 'Insira um email válido'
                    },
                    senha: {
                        required: 'Insira sua senha.'
                    }
                },
                submitHandler: function(form) {
                    var dados = $(form).serialize();
                    $('#progress-acessar').show();
                    $.ajax({
                        type: "POST",
                        url: "<?= site_url('mapos/verificarLogin?ajax=true'); ?>",
                        data: dados,
                        dataType: 'json',
                        success: function(data) {
                            if (data.result == true) {
                                window.location.href = "<?= site_url('mapos'); ?>";
                            } else {
                                $('#progress-acessar').hide();
                                sweetAlert("Oops...", "Dados de acesso são inválidos! Tente novamente.", "error");
                            }
                        },
                        fail: function() {
                            sweetAlert("Oops...", "Ocorreu um problema ao tentar efetuar o login! Tente novamente.", "error");
                        }
                    });
                    return false;
                },
                errorClass: "help-inline",
                errorElement: "span",
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass('error');
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass('error');
                }
            });
        });
    </script>
</body>

</html> 