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
    <div class="row-fluid" style="margin-top:0">
        <div class="span6 offset3">
            <div class="widget-box">
                <div class="widget-title">
                    <span class="icon">
                        <i class="fas fa-user"></i>
                    </span>
                    <h5>Token</h5>
                </div>
                <div class="span8 offset2">
                    <h5>
                        <span class="required">Digite ou copie e cole o token enviado para seu email.</span>
                    </h5>
                </div>
                <div class="widget-content nopadding tab-content">
                    <form action="<?php echo base_url() . 'index.php/mine/tokenManual' ?>" id="formCliente" method="post" class="form-horizontal">

                        <div class="control-group">
                            <label for="token" class="control-label">Token<span class="required">*</span></label>
                            <div class="controls">
                                <input id="token" type="text" name="token" value="" />
                            </div>
                        </div>

                        <div class="form-actions">
                            <div class="span12">
                                <div class="span6 offset3" style="display:flex;justify-content: center">
                                    <button type="submit" class="button btn btn-success btn-large"><span class="button__icon"><i class='bx bx-certification'></i></span><span class="button__text2">Validar</span></button>
                                    <a href="<?php echo base_url() ?>index.php/mine" id="" class="button btn btn-warning"><span class="button__icon"><i class='bx bx-lock-alt'></i></span><span class="button__text2">Acessar</span></a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
    <?php if ($this->session->flashdata('success') != null) { ?>
        <script>
            Swal.fire({
                position: 'center',
                icon: 'success',
                title: '<?php echo $this->session->flashdata('success'); ?>',
                showConfirmButton: false,
                timer: 2000
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
                timer: 2000
            })
        </script>
    <?php } ?>

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
