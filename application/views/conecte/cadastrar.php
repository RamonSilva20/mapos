<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Área do Cliente - <?= $this->config->item('app_name') ?></title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="<?= $this->config->item('app_name') . ' - ' . $this->config->item('app_subname') ?>">
    <meta name="csrf-token-name" content="<?= config_item("csrf_token_name") ?>">
    <meta name="csrf-cookie-name" content="<?= config_item("csrf_cookie_name") ?>">
    <link rel="shortcut icon" type="image/png" href="<?= base_url(); ?>assets/img/favicon.png" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/matrix-style.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/matrix-media.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/fullcalendar.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css">
</head>

<body>
    <style>
        #imgSenha {
            width: 18px;
            cursor: pointer;
        }

        #buscar_info_cnpj {
            display:none;
            width:18px;
            height:18px;
            font-size:16px;
            border:none;
            background-color:transparent;
            padding-left: 0;
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

        

        .widget-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            /* gap:20px; */
        }

        @media (max-width: 480px) {
            .row-fluid {
                display: block;
            }
            
            form {
                display: block !important;
            }

            .form-horizontal .control-label {
                margin-bottom: -6px;
            }

            .btn-xs {
                position: initial !important;
            }
            .controls {
                margin-left: 50%;
            }

            .btn {
                position: absolute; /* Reposiciona o botão na posição absoluta */
                width: auto; /* Define a largura automática do botão */
            }
        }

        @media (max-width: 767px) {
            .widget-content {
                display: block; /* Exibe os campos em bloco */
            }

            .control-group {
                margin-bottom: 15px; /* Espaçamento entre os campos */
            }

            .controls {
                margin-left: 0; /* Remove o deslocamento à esquerda */
            }

            .control-group input[type="text"],
            .control-group input[type="password"],
            .control-group select {
                width: 100%; /* Faz os inputs ocuparem toda a largura disponível */
            }

            .btn {
                position: static; /* Remove a posição absoluta do botão */
                width: 100%; /* Faz o botão ocupar a largura total do campo */
                margin-top: 10px; /* Adiciona espaçamento acima do botão */
            }

            .form-actions {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .form-actions .span12 {
                display: flex;
                justify-content: center;
            }
        }
    </style>

    <div class="row-fluid" style="width: 100vw;height: 96%;display: grid;align-items: center;justify-content: center">
        <div class="widget-box">
            <div class="widget-title">
                <h5>Cadastre-se no Sistema</h5>
            </div>
            <form action="<?= current_url() ?>" id="formCliente" method="post" class="form-horizontal">
                <div class="widget-content nopadding tab-content">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <div class="control-group">
                        <label for="email" class="control-label">Email</label>
                        <div class="controls">
                            <input id="email" type="text" name="email" value="<?php echo set_value('email'); ?>" autocomplete="off" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="senha" class="control-label">Senha<span class="required">*</span></label>
                        <div class="controls">
                            <input id="senha" type="password" placeholder="Insira sua senha" name="senha" value="<?php echo set_value('senha'); ?>" />
                            <img id="imgSenha" src="<?= base_url() ?>assets/img/eye.svg" alt="">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="tipoCliente" class="control-label">Tipo<span class="required">*</span></label>
                        <div class="controls">
                            <select id="tipoCliente" name="tipoCliente">
                                <option value="1" selected aria-selected="true">Pessoa Física</option>
                                <option value="2">Pessoa Jurídica</option>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <?php if (isset($custom_error) && $custom_error != '') {
                            echo '<div class="alert alert-danger">' . $custom_error . '</div>';
                        } ?>
                        <label for="documento" class="control-label">CPF</label>
                        <div class="controls">
                            <input id="documento" class="" type="text" name="documento" value="<?php echo set_value('documento'); ?>" />
                            <button id="buscar_info_cnpj" class="btn btn-mini" type="button">
                                <span class="button_icon"><i class="bx bx-search-alt-2"></i></span></button>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="rg_ie" class="control-label">RG</label>
                        <div class="controls">
                            <input id="rg_ie" type="text" name="rg_ie" value="<?php echo set_value('rg_ie'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="nomeCliente" class="control-label">Nome Completo<span class="required">*</span></label>
                        <div class="controls">
                            <input id="nomeCliente" type="text" name="nomeCliente" value="<?php echo set_value('nomeCliente'); ?>" />
                        </div>
                    </div>
                    <div class="control-group" style="display: none;">
                        <label for="nomeFantasia" class="control-label">Nome Fantasia</label>
                        <div class="controls">
                            <input id="nomeFantasia" type="text" name="nomeFantasia" value="<?php echo set_value('nomeFantasia'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="contato" class="control-label">Contato:</label>
                        <div class="controls">
                            <input class="contato" type="text" name="contato" value="<?php echo set_value('contato'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="telefone" class="control-label">Telefone</label>
                        <div class="controls">
                            <input id="telefone" type="text" name="telefone" value="<?php echo set_value('telefone'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="celular" class="control-label">Celular</label>
                        <div class="controls">
                            <input id="celular" type="text" name="celular" value="<?php echo set_value('celular'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="cep" class="control-label">CEP</label>
                        <div class="controls">
                            <input id="cep" type="text" name="cep" value="<?php echo set_value('cep'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="rua" class="control-label">Rua</label>
                        <div class="controls">
                            <input id="rua" type="text" name="rua" value="<?php echo set_value('rua'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="numero" class="control-label">Número</label>
                        <div class="controls">
                            <input id="numero" type="text" name="numero" value="<?php echo set_value('numero'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="complemento" class="control-label">Complemento</label>
                        <div class="controls">
                            <input id="complemento" type="text" name="complemento" value="<?php echo set_value('complemento'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="bairro" class="control-label">Bairro</label>
                        <div class="controls">
                            <input id="bairro" type="text" name="bairro" value="<?php echo set_value('bairro'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="cidade" class="control-label">Cidade</label>
                        <div class="controls">
                            <input id="cidade" type="text" name="cidade" value="<?php echo set_value('cidade'); ?>" />
                        </div>
                    </div>

                    <div class="control-group" class="control-label">
                        <label for="estado" class="control-label"><span class="required"></span></label>
                        <div class="controls">
                            <select id="estado" name="estado">
                                <option value="">Selecione...</option>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="dataNascimento" class="control-label">Data de Nascimento</label>
                        <div class="controls">
                            <input id="dataNascimento" type="date" name="dataNascimento" value="<?php echo set_value('dataNascimento'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="sexo" class="control-label">Sexo</label>
                        <div class="controls">
                            <select id="sexo" name="sexo">
                                <option value="Masculino" selected aria-selected="true">Masculino</option>
                                <option value="Feminino">Feminino</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="control-group span12" style="background-color:transparent;border:none;padding: 10px;margin-left: 0;margin-bottom: 0;">
                    <div style="display:flex; justify-content: center; flex-direction: column; align-items: center;">
                        <img src="<?= base_url() ?>index.php/mine/captcha" alt="">
                        <div class="controls" style="margin-left: 0;">
                            <input id="captcha" type="text" placeholder="Digite o texto da imagem*" name="captcha" value="" />
                        </div>
                    </div>
                </div>

                <div class="form-actions" style="background-color:transparent;border:none;padding: 10px;margin-bottom: 0">
                    <div class="span12">
                        <div class="span6 offset3" style="display:flex;justify-content: center">
                            <button type="submit" class="button btn btn-success btn-large"><span class="button__icon"><i class='bx bx-user-plus'></i></span><span class="button__text2">Cadastrar</span></button>
                            <a href="<?= base_url() ?>index.php/mine" id="" class="button btn btn-warning"><span class="button__icon"><i class='bx bx-lock-alt'></i></span><span class="button__text2">Acessar</span></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <script type="text/javascript" src="<?= base_url() ?>assets/js/jquery-1.12.4.min.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/js/jquery.mask.min.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/js/sweetalert2.all.min.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/js/jquery.validate.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>assets/js/funcoes.js"></script>
    <script type="text/javascript" src="<?= base_url(); ?>assets/js/funcoesGlobal.js"></script>
    <script type="text/javascript" src="<?= base_url(); ?>assets/js/csrf.js"></script>
    <script type="text/javascript">
        <?php if ($this->session->flashdata('error') != null) { ?>
            console.log('Erro');
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: '<?= $this->session->flashdata('error') ?>',
                showConfirmButton: false,
                timer: 4000
            });
        <?php } ?>

        $(document).ready(function() {
            $.getJSON('<?= base_url() ?>assets/json/estados.json', function(data) {
                for (i in data.estados) {
                    $('#estado').append(new Option(data.estados[i].nome, data.estados[i].sigla));
                    var curState = '<?= set_value('estado'); ?>';
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
                    icon.src = '<?= base_url() ?>assets/img/eye-off.svg';
                    input.type = 'text';
                } else {
                    icon.src = '<?= base_url() ?>assets/img/eye.svg'
                    input.type = 'password';
                }
            });

            $("#email").focus();
            $(function() {
                $("#documento").mask("000.000.000-00", {
                    onpaste: function (e) {
                        e.preventDefault();
                        var clipboardCurrentData = (e.originalEvent || e).clipboardData.getData('text/plain');
                        $("#documento").val(clipboardCurrentData);
                    }
                });
            });

            $("#tipoCliente").change(function() {
                // Definir máscara e exibir campos pertinente ao tipo de cliente selecionado.
                if ($("#tipoCliente").val() == "1") {
                    var mascara = "000.000.000-00";
                    $("#documento").parent().prev(".control-label").text("CPF");
                    $("#buscar_info_cnpj").css("display", "none");
                    $("#rg_ie").parent().prev(".control-label").text("RG");
                    $("#nomeCliente").parent().prev(".control-label").text("Nome Completo");
                    $("#nomeFantasia").val("");
                    $("#nomeFantasia").parent().parent().css("display", "none");
                    $("#dataNascimento").val("");
                    $("#dataNascimento").parent().parent().css("display", "block");
                    $("#sexo").parent().parent().css("display", "block");
                } else if ($("#tipoCliente").val() == "2") {
                    var mascara = "00.000.000/0000-00";
                    $("#documento").parent().prev(".control-label").text("CNPJ");
                    $("#buscar_info_cnpj").css("display", "inline-block");
                    $("#rg_ie").parent().prev(".control-label").text("IE");
                    $("#nomeCliente").parent().prev(".control-label").text("Razão Social");
                    $("#nomeFantasia").parent().parent().css("display", "block");
                    $("#dataNascimento").val("");
                    $("#dataNascimento").parent().parent().css("display", "none");
                    $("#sexo").parent().parent().css("display", "none");
                };
                var inputOptions = {
                    onpaste: function (e) {
                        e.preventDefault();
                        var clipboardCurrentData = (e.originalEvent || e).clipboardData.getData('text/plain');
                        $("#documento").val(clipboardCurrentData);
                    }
                };
                $("#documento").val("");
                $("#documento").mask(mascara, inputOptions);
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
                    },
                    captcha: {
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
                    },
                    captcha: {
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
        <div id="footer" class="span12" style="padding: 10px">
            <a class="pecolor" href="https://github.com/RamonSilva20/mapos" target="_blank">
                <?= date('Y') ?> &copy; Ramon Silva - <?= $this->config->item('app_name') ?> - Versão: <?= $this->config->item('app_version'); ?>
            </a>
        </div>
    </div>

</body>

</html>
