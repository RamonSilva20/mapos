<style>
    .col-lg-12,
    .col-lg-3,
    .col-lg-9,
    .col-md-12,
    .col-md-3,
    .col-md-9,
    .col-sm-12,
    .col-xs-12 {
        position: relative;
        min-height: 1px;
    }

    .img-user {
      bottom: 15px;
      right: 18px;
      padding: 6px;
      background: #d4d7df;
      color: #333649;
      border-radius: 50%;
      width: 15px;
      height: 15px;
      align-items: center;
      opacity: 0.8;
      position: absolute;
    }

    .pass-user {
        bottom: 27px;
        right: 40px;
        padding: 6px;
        background: transparent;
        border-radius: 50%;
        width: 15px;
        height: 15px;
        align-items: center;
        opacity: 0.7;
        position: absolute;
    }

    .img-user:before {
        opacity: 1;
    }

    .profileMC {
        margin-top: -60px;
    }

    section .profileMC .profile-img {
        border : 4px solid #e6e9f3;
        padding: 0;
        height: 100px;
        width: 100px;
    }

    @media (min-width: 1200px) {
        .col-lg-3 {
            width: 25%;
        }
    }

    @media (min-width: 1200px) {
        .col-lg-12,
        .col-lg-3,
        .col-lg-9 {
            float: left;
            width: 100%;
        }
    }

    @media (min-width: 480px) and (max-width: 992px) {
        .col-md-3 {
            width: 25%;
        }

        .col-lg-9 {
            width: 85%;
        }
    }

    @media (max-width: 480px) {
        .table-condensed td {
            padding: 4px 5px;
        }

        .table {
            width: 100%;
        }

        .panel-body {
            padding: 0;
        }
    }
    

</style>
<div class="span6" style="margin-left: 0">
    <div class="widget-box">
        <div class="widget-title" style="margin: -10px 0 0">
            <span class="icon">
                <i class="fas fa-user"></i>
            </span>
            <h5>Minha Conta</h5>
        </div>
        <div class="widget-contentMC" style="margin: 20px 0 0;">
            <div id="userMC">
                <div class="row">
                    <div class="col-md-3 col-lg-3 " style="text-align:center;">
                        <section>
                            <div class="profileMC">
                                <div class="profile-img">
                                    <img src="<?= (!$usuario->url_image_user || !is_file(FCPATH . "assets/userImage/" . $usuario->url_image_user)) ?  base_url() . "assets/img/User.png" : base_url() . "assets/userImage/" . $usuario->url_image_user ?>" alt="">
                                    <a href="#modalImageUser" data-toggle="modal" role="button"><span class="tip-top img-user button__icon" title="Alterar Foto"><i class='bx bxs-camera'></i></span></a>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>

            <div class="row-fluid">
                <div class="span12">
                    <ul class="site-stats">
                        <li class="bg_ls span12"><strong>Nome:
                                <?= $usuario->nome ?></strong></li>
                        <li class="bg_lb span12" style="margin-left: 0"><strong>Telefone:
                                <?= $usuario->telefone ?></strong></li>
                        <li class="bg_lg span12" style="margin-left: 0"><strong>Email:
                                <?= $usuario->email ?></strong></li>
                        <li class="bg_lo span12" style="margin-left: 0"><strong>Nível:
                                <?= $usuario->permissao; ?></strong></li>
                        <li class="bg_lh span12" style="margin-left: 0; border-bottom-left-radius: 9px;border-bottom-right-radius: 9px"><strong>Acesso expira em:
                                <?= date('d/m/Y', strtotime($usuario->dataExpiracao)); ?></strong></li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="span6">
    <div class="widget-box">
        <div class="widget-title" style="margin: -20px 0 0">
            <span class="icon">
                <i class="fas fa-lock"></i>
            </span>
            <h5>Alterar Minha Senha</h5>
        </div>
        <div class="widget-content">
            <div class="row-fluid">
                <div class="span12" style="min-height: 260px">
                    <form id="formSenha" action="<?= site_url('mapos/alterarSenha'); ?>" method="post">

                        <div class="span12" style="margin-left: 0">
                            <label for="">Senha Atual</label>
                            <input type="password" id="oldSenha" name="oldSenha" class="span12" />
                        </div>
                        <div class="span12" style="margin-left: 0">
                            <label for="">Nova Senha</label>
                            <input type="password" id="novaSenha" name="novaSenha" class="span12" />
                        </div>
                        <div class="span12" style="margin-left: 0">
                            <label for="">Confirmar Senha</label>
                            <input type="password" name="confirmarSenha" class="span12" />
                        </div>
                            <button class="button btn btn-primary" style="max-width: 140px;text-align: center">
                              <span class="button__icon"><i class='bx bx-lock-alt'></i></span><span class="button__text2">Alterar Senha</span></button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<div id="modalImageUser" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?= site_url('mapos/uploadUserImage'); ?>" id="formImageUser" enctype="multipart/form-data" method="post" class="form-horizontal">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="">MapOS - Atualizar Imagem do Usuario</h3>
        </div>
        <div class="modal-body">
            <div class="span12 alert alert-info">Selecione uma nova imagem do usuario. Tamanho indicado (130 X 130).</div>
            <div class="control-group">
                <label for="userfile" class="control-label"><span class="required">Foto*</span></label>
                <div class="controls">
                    <input type="file" name="userfile" value="" />
                </div>
            </div>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true" id="btnCancelExcluir"><span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
            <button class="button btn btn-primary"><span class="button__icon"><i class="bx bx-sync"></i></span><span class="button__text2">Atualizar</span></button>
        </div>
    </form>
</div>


<script src="<?= base_url() ?>assets/js/jquery.validate.js"></script>
<script type="text/javascript">
    $(document).ready(function() {

        $("#formImageUser").validate({
            rules: {
                userfile: {
                    required: true
                }
            },
            messages: {
                userfile: {
                    required: 'Campo Requerido.'
                }
            },

            errorClass: "help-inline",
            errorElement: "span",
            highlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').addClass('error');
                $(element).parents('.control-group').removeClass('success');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error');
                $(element).parents('.control-group').addClass('success');
            }
        });

        $('#formSenha').validate({
            rules: {
                oldSenha: {
                    required: true
                },
                novaSenha: {
                    required: true
                },
                confirmarSenha: {
                    equalTo: "#novaSenha"
                }
            },
            messages: {
                oldSenha: {
                    required: 'Campo Requerido'
                },
                novaSenha: {
                    required: 'Campo Requerido.'
                },
                confirmarSenha: {
                    equalTo: 'As senhas não combinam.'
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
