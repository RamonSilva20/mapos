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
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-1.12.4.min.js"></script>
    <script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
    <script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
    <script src="<?php echo base_url() ?>assets/js/funcoes.js"></script>
</head>

<body>
    <div class="row-fluid" style="margin-top:0">
        <div class="span6 offset3">
            <div class="widget-box">
                <div class="widget-title">
                    <span class="icon">
                        <i class="fas fa-user"></i>
                    </span>
                    <h5>Cadastre-se no sistema</h5>
                </div>
                <div class="widget-content nopadding tab-content">

                    <form action="<?php echo current_url(); ?>" id="formCliente" method="post" class="form-horizontal">
                        <div class="control-group">
                            <label for="nomeCliente" class="control-label">Nome<span class="required">*</span></label>
                            <div class="controls">
                                <input id="nomeCliente" type="text" name="nomeCliente" value="<?php echo set_value('nomeCliente'); ?>" />
                            </div>
                        </div>
                        <div class="control-group">
                            <?php if (isset($custom_error) && $custom_error != '') {
    echo '<div class="alert alert-danger">' . $custom_error . '</div>';
} ?>
                            <label for="documento" class="control-label">CPF/CNPJ<span class="required">*</span></label>
                            <div class="controls">
                                <input id="documento" class="cpfcnpj" type="text" name="documento" value="<?php echo set_value('documento'); ?>" />
                                <button id="buscar_info_cnpj" class="btn btn-xs" type="button"><i class="fas fa-search"></i></button>
                            </div>
                        </div>

                        <div class="control-group">
                            <label for="telefone" class="control-label">Telefone<span class="required">*</span></label>
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
                            <label for="email" class="control-label">Email<span class="required">*</span></label>
                            <div class="controls">
                                <input id="email" type="text" name="email" value="<?php echo set_value('email'); ?>" />
                            </div>
                        </div>

                        <div class="control-group" class="control-label">
                            <label for="cep" class="control-label">CEP<span class="required">*</span></label>
                            <div class="controls">
                                <input id="cep" type="text" name="cep" value="<?php echo set_value('cep'); ?>" />
                            </div>
                        </div>


                        <div class="control-group" class="control-label">
                            <label for="rua" class="control-label">Rua<span class="required">*</span></label>
                            <div class="controls">
                                <input id="rua" type="text" name="rua" value="<?php echo set_value('rua'); ?>" />
                            </div>
                        </div>

                        <div class="control-group">
                            <label for="numero" class="control-label">Número<span class="required">*</span></label>
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
                        <div class="control-group" class="control-label">
                            <label for="bairro" class="control-label">Bairro<span class="required">*</span></label>
                            <div class="controls">
                                <input id="bairro" type="text" name="bairro" value="<?php echo set_value('bairro'); ?>" />
                            </div>
                        </div>

                        <div class="control-group" class="control-label">
                            <label for="cidade" class="control-label">Cidade<span class="required">*</span></label>
                            <div class="controls">
                                <input id="cidade" type="text" name="cidade" value="<?php echo set_value('cidade'); ?>" />
                            </div>
                        </div>

                        <div class="control-group" class="control-label">
                            <label for="estado" class="control-label">Estado<span class="required">*</span></label>
                            <div class="controls">
                                <input id="estado" type="text" name="estado" value="<?php echo set_value('estado'); ?>" />
                            </div>
                        </div>


                        <div class="form-actions">
                            <div class="span12">
                                <div class="span6 offset3">
                                    <button type="submit" class="btn btn-success btn-large"><i class="fas fa-plus"></i> Cadastrar</button>
                                    <a href="<?php echo base_url() ?>index.php/mine" id="" class="btn btn-large"><i class="fas fa-lock"></i> Acessar</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
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
        <div id="footer" class="span12">
            <?= date('Y') ?> &copy; <?php echo $this->config->item('app_name') ?>
        </div>
    </div>

    <!-- javascript
================================================== -->

    <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
</body>

</html>
