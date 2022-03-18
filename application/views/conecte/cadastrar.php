<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Mine - Área do Cliente - <?php echo $this->config->item('app_name') ?></title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="<?php echo $this->config->item('app_name') . ' - ' . $this->config->item('app_subname') ?>">
    <link rel="shortcut icon" type="image/png" href="<?php echo base_url(); ?>assets/img/favicon.png" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-style.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-media.css" />
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/fullcalendar.css" />
    <link href="<?php echo base_url(); ?>assets/css/bootstrap-responsive.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-1.12.4.min.js"></script>
    <script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
    <script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
    <script src="<?php echo base_url() ?>assets/js/funcoes.js"></script>
</head>

<body>
    <style>
        #imgSenha {
            width: 18px;
            cursor: pointer;
        }

        .control-group.error .help-inline {
            display: flex;
        }

        .form-horizontal .control-group {
            border-bottom: 1px solid #ffffff;
        }

        .form-horizontal .controls {
            margin-left: 20px;
            padding-bottom: 8px 0;
        }

        .form-horizontal .control-label {
            text-align: left;
            padding-top: 0;
        }

        .nopadding {
            padding: 0 20px !important;
            margin-right: 20px;
        }

        .widget-title h5 {
            padding-bottom: 15px;
            text-align-last: center;
            font-size: 1.5em;
            font-weight: 500;
        }

        @media (max-width: 480px) {
            form {
                display: block !important;
            }

            .form-horizontal .control-label {
                margin-bottom: -6px;
            }

            .btn-xs {
                position: initial !important;
            }
        }
    </style>

    <div class="row-fluid" style="width: 100vw;height: 100vh;display: flex;align-items: center;justify-content: center">
        <div class="widget-box">
            <div class="widget-title">
                <h5>Cadastre-se no Sistema</h5>
            </div>
            <div class="widget-content nopadding tab-content">

                <form action="<?php echo current_url(); ?>" id="formCliente" method="post" class="form-horizontal" style="display: grid;grid-template-columns: 1fr 1fr">
                    <div class="control-group">
                        <label for="nomeCliente" class="control-label"><span class="required"></span></label>
                        <div class="controls">
                            <input id="nomeCliente" type="text" placeholder="Nome*" name="nomeCliente" value="<?php echo set_value('nomeCliente'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <?php if (isset($custom_error) && $custom_error != '') {
                            echo '<div class="alert alert-danger">' . $custom_error . '</div>';
                        } ?>
                        <label for="documento" class="control-label"><span class="required"></span></label>
                        <div class="controls">
                            <input id="documento" class="cpfcnpj" type="text" placeholder="CPF/CNPJ*" name="documento" value="<?php echo set_value('documento'); ?>" />
                            <button style="top:70px;right:40px;position:absolute" id="buscar_info_cnpj" class="btn btn-xs" type="button"><i class="fas fa-search"></i></button>
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="telefone" class="control-label"><span class="required"></span></label>
                        <div class="controls">
                            <input id="telefone" type="text" placeholder="Telefone*" name="telefone" value="<?php echo set_value('telefone'); ?>" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="celular" class="control-label"></label>
                        <div class="controls">
                            <input id="celular" type="text" placeholder="Celular" name="celular" value="<?php echo set_value('celular'); ?>" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="email" class="control-label"><span class="required"></span></label>
                        <div class="controls">
                            <input id="email" type="text" placeholder="Email*" name="email" value="<?php echo set_value('email'); ?>" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="senha" class="control-label"><span class="required"></span></label>
                        <div class="controls">
                            <input id="senha" type="password" placeholder="Senha*" name="senha" value="<?php echo set_value('senha'); ?>" />
                            <img id="imgSenha" src="<?php echo base_url() ?>assets/img/eye.svg" alt="">
                        </div>
                    </div>

                    <div class="control-group" class="control-label">
                        <label for="cep" class="control-label"><span class="required"></span></label>
                        <div class="controls">
                            <input id="cep" type="text" placeholder="CEP*" name="cep" value="<?php echo set_value('cep'); ?>" />
                        </div>
                    </div>


                    <div class="control-group" class="control-label">
                        <label for="rua" class="control-label"><span class="required"></span></label>
                        <div class="controls">
                            <input id="rua" type="text" placeholder="Rua*" name="rua" value="<?php echo set_value('rua'); ?>" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="numero" class="control-label"><span class="required"></span></label>
                        <div class="controls">
                            <input id="numero" type="text" placeholder="Número*" name="numero" value="<?php echo set_value('numero'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="complemento" class="control-label"></label>
                        <div class="controls">
                            <input id="complemento" type="text" placeholder="Complemento" name="complemento" value="<?php echo set_value('complemento'); ?>" />
                        </div>
                    </div>
                    <div class="control-group" class="control-label">
                        <label for="bairro" class="control-label"><span class="required"></span></label>
                        <div class="controls">
                            <input id="bairro" type="text" placeholder="Bairro*" name="bairro" value="<?php echo set_value('bairro'); ?>" />
                        </div>
                    </div>

                    <div class="control-group" class="control-label">
                        <label for="cidade" class="control-label"><span class="required"></span></label>
                        <div class="controls">
                            <input id="cidade" type="text" placeholder="Cidade*" name="cidade" value="<?php echo set_value('cidade'); ?>" />
                        </div>
                    </div>

                    <div class="control-group" class="control-label">
                        <label for="estado" class="control-label"><span class="required"></span></label>
                        <div class="controls">
                            <select id="estado" name="estado">
                                <option value="">Selecione Seu Estado...</option>
                            </select>
                        </div>
                    </div>
            </div>
            <div class="form-actions" style="background-color:transparent;border:none;padding: 10px;margin-bottom: 0">
                <div class="span12">
                    <div class="span6 offset3" style="display:flex;justify-content: center">
                        <button type="submit" class="button btn btn-success btn-large"><span class="button__icon"><i class='bx bx-user-plus'></i></span><span class="button__text2">Cadastrar</span></button>
                        <a href="<?php echo base_url() ?>index.php/mine" id="" class="button btn btn-warning"><span class="button__icon"><i class='bx bx-lock-alt'></i></span><span class="button__text2">Acessar</span></a>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>


    <script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {

            $.getJSON('<?php echo base_url() ?>assets/json/estados.json', function(data) {
                for (i in data.estados) {
                    $('#estado').append(new Option(data.estados[i].nome, data.estados[i].sigla));
                    var curState = '<?php echo set_value('estado'); ?>';
                    if (curState) {
                        $("#estado option[value=" + curState + "]").prop("selected", true);
                    }
                }
            });

            let container = document.querySelector('div');
            let input = document.querySelector('#senha');
            let icon = document.querySelector('#imgSenha');

            icon.addEventListener('click', function() {
                container.classList.toggle('visible');
                if (container.classList.contains('visible')) {
                    icon.src = '<?php echo base_url() ?>assets/img/eye-off.svg';
                    input.type = 'text';
                } else {
                    icon.src = '<?php echo base_url() ?>assets/img/eye.svg'
                    input.type = 'password';
                }
            });
            $('#formCliente').validate({
                rules: {
                    nomeCliente: {
                        required: true
                    },
                    documento: {
                        required: true
                    },
                    telefone: {
                        required: true
                    },
                    email: {
                        required: true
                    },
                    senha: {
                        required: true
                    },
                    rua: {
                        required: true
                    },
                    numero: {
                        required: true
                    },
                    bairro: {
                        required: true
                    },
                    cidade: {
                        required: true
                    },
                    estado: {
                        required: true
                    },
                    cep: {
                        required: true
                    }
                },
                messages: {

                    nomeCliente: {
                        required: 'Campo Requerido.'
                    },
                    documento: {
                        required: 'Campo Requerido.'
                    },
                    telefone: {
                        required: 'Campo Requerido.'
                    },
                    email: {
                        required: 'Campo Requerido.'
                    },
                    senha: {
                        required: 'Campo Requerido.'
                    },
                    rua: {
                        required: 'Campo Requerido.'
                    },
                    numero: {
                        required: 'Campo Requerido.'
                    },
                    bairro: {
                        required: 'Campo Requerido.'
                    },
                    cidade: {
                        required: 'Campo Requerido.'
                    },
                    estado: {
                        required: 'Campo Requerido.'
                    },
                    cep: {
                        required: 'Campo Requerido.'
                    }

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


    <!--Footer-part-->
    <div class="row-fluid">
        <div id="footer" class="span12" style="padding: 10px"> <a class="pecolor" href="https://github.com/RamonSilva20/mapos" target="_blank">
                <?= date('Y') ?> &copy; Ramon Silva - <?php echo $this->config->item('app_name') ?> - Versão: <?= $this->config->item('app_version'); ?>
        </div>
    </div>

    <!-- javascript
================================================== -->

    <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
</body>

</html>