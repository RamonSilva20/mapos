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

    .panel-body {
        padding: 15px;
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        grid-gap: 30px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    button,
    input,
    select,
    optgroup,
    textarea {
        font-family: inherit;
        font-size: inherit;
        line-height: inherit;
        margin: 0;
        font: inherit;
        color: inherit;
    }

    .table {
        width: 96%;
    }

    input {
        line-height: normal;
    }

    input[type=file] {
        display: block;
    }

    .input-group {
        position: relative;
        display: table;
        border-collapse: separate;
    }

    .input-group .form-control,
    .input-group-addon,
    .input-group-btn {
        display: table-cell;
    }

    .input-group .form-control {
        position: relative;
        z-index: 2;
        float: left;
        width: 100%;
        margin-bottom: 0;
    }

    .form-control[disabled],
    fieldset[disabled] .form-control {
        cursor: not-allowed;
    }

    .form-control[disabled],
    .form-control[readonly],
    fieldset[disabled] .form-control {
        background-color: #eee;
        opacity: 1;
    }

    button[disabled],
    html input[disabled] {
        cursor: default;
    }

    .input-sm {
        height: 30px;
        padding: 5px 10px;
        font-size: 12px;
        line-height: 1.5;
        border-radius: 3px;
    }

    .form-control {
        display: block;
        width: 100%;
        height: 34px;
        padding: 6px 12px;
        font-size: 14px;
        line-height: 1.42857143;
        color: #555;
        background-color: #fff;
        background-image: none;
        border: 1px solid #ccc;
        border-radius: 4px;
        -webkit-box-shadow: inset 0 1px 1px rgb(0 0 0 / 8%);
        box-shadow: inset 0 1px 1px rgb(0 0 0 / 8%);
        -webkit-transition: border-color ease-in-out .15s, -webkit-box-shadow ease-in-out .15s;
        -o-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
        transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
    }

    .input-group-btn {
        position: relative;
        font-size: 0;
        white-space: nowrap;
    }

    .input-group-addon,
    .input-group-btn {
        width: 1%;
        white-space: nowrap;
        vertical-align: middle;
    }

    .panel-footer {
        padding: 10px 15px;
        background-color: #f5f5f5;
        border-top: 1px solid #ddd;
        border-bottom-right-radius: 3px;
        border-bottom-left-radius: 3px;
    }

    .text-center {
        text-align: center;
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

    .widget-box {
        border-radius: 12px;
    }

    .widget-title h5 {
        top: 0;
        position: absolute;
        color: #ffffff;
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

    .widget-perf {
        width: 100%;
        height: 75px;
        background: linear-gradient(169deg, #f78a40, #08c);
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
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

<div class="">
    <div class="widget-box">
        <form method="post" id="perfil">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 toppad">
                <div class="panel panel-success">
                    <div class="widget-perf">
                        <div></div>
                    </div>
                    <div class="widget-title" style="margin: -20px 0 0">
                        <h5>Meu Perfil</h5>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-3 col-lg-3 " style="text-align:center">
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

                        <div class=" col-md-9 col-lg-9 ">
                            <table class="table table-condensed">
                                <tbody>
                                    <tr>
                                        <td class='col-md-3'>Nome:</td>
                                        <td><input class="form-control input-sm" id="fone" type="text" name="fone" value="<?= $usuario->nome ?>" /></td>
                                    </tr>
                                    <tr>
                                        <td>Email:</td>
                                        <td><input class="form-control input-sm" id="fone" type="text" name="fone" value="<?= $usuario->email ?>" /></td>
                                    </tr>
                                    <tr>
                                        <td>Telefone:</td>
                                        <td><input class="form-control input-sm" id="fone" type="text" name="fone" value="<?= $usuario->telefone ?>" /></td>
                                    </tr>
                                    <tr>
                                        <td>Nível</td>
                                        <td><input class="form-control input-sm" id="fone" type="text" name="fone" value="<?= $usuario->permissao; ?>" /></td>
                                    </tr>
                                    <tr>
                                        <td>Expira em:</td>
                                        <td><input class="form-control input-sm" id="fone" type="text" name="fone" value="<?= date('d/m/Y', strtotime($usuario->dataExpiracao)); ?>" /></td>
                                    </tr>
                                    <tr>
                                        <td>Senha</td>
                                        <td><input class="form-control input-sm" id="fone" type="text" name="fone" value="*******" /></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="control-group">
                                <label for="user" class="">
                                    <a href="#formSenhaModal" data-toggle="modal" role="button" class="">
                                        <span class="tip-top pass-user button__icon" title="Alterar Senha"><i class='bx bxs-edit'></i></span></a>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="formSenhaModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <form id="formSenha" action="<?= site_url('mapos/alterarSenha'); ?>" method="post">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h3 id="">Aterar senha de usuário</h3>
    </div>
    <div class="modal-body">
      <div class="span12" style="">
        <div class="span12" style="margin-left: 0">
          <label for="">Senha atual</label>
          <input type="password" id="oldSenha" name="oldSenha" class="span12" />
      </div>
      <div class="span12" style="margin-left: 0">
        <label for="">Nova senha</label>
        <input type="password" id="novaSenha" name="novaSenha" class="span12" />
      </div>
      <div class="span12" style="margin-left: 0">
        <label for="">Confirmar senha</label>
        <input type="password" name="confirmarSenha" class="span12" />
      </div>
      <div class="span11 alert alert-info" style="text-align: center;">Colocar uma senha que você possa lembrar.</div>
    </div>
    </div>
    <div class="modal-footer" style="display:flex;justify-content: center">
      <button class="button btn btn-primary" style="max-width: 140px;text-align: center"><span class="button__icon"><i class='bx bx-lock-alt'></i></span><span class="button__text2">Alterar Senha</span></button>
    </div>
  </form>
</div>

<div id="modalImageUser" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <form action="<?= site_url('mapos/uploadUserImage'); ?>" id="formImageUser" enctype="multipart/form-data" method="post" class="form-horizontal">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h3 id="">Atualizar Imagem do Usuario</h3>
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
