<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/funcoes.js"></script>
<script src="<?php echo base_url()?>assets/js/sweetalert2.all.min.js"></script>

<?php if (!isset($dados) || $dados == null) { ?>
    <div class="row-fluid" style="margin-top:0">
        <div class="span12">
            <div class="widget-box">
                <div class="widget-title">
                    <span class="icon">
                        <i class="fas fa-align-justify"></i>
                    </span>
                    <h5>Dados do Emitente</h5>
                </div>
                <div class="widget_content ">
                    <div class="alert alert-danger">Nenhum dado foi cadastrado até o momento. Essas informações
                        estarão disponíveis na tela de impressão de OS.</div>
                    <a href="#modalCadastrar" data-toggle="modal" role="button" class="btn btn-success">Cadastrar Dados</a>
                </div>
            </div>

        </div>
    </div>


    <div id="modalCadastrar" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <form action="<?= site_url('mapos/cadastrarEmitente'); ?>" id="formCadastrar" enctype="multipart/form-data" method="post" class="form-horizontal">
            <div class="modal_header_anexos">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">Cadastrar Dados do Emitente</h3>
            </div>
            <div class="modal-body">


                <div class="control-group">
                    <label for="nome" class="control-label">Razão Social<span class="required">*</span></label>
                    <div class="controls">
                        <input id="nomeEmitente" type="text" name="nome" value="" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="cnpj" class="control-label"><span class="required">CNPJ*</span></label>
                    <div class="controls">
                        <input class="cnpjEmitente" id="documento" type="text" name="cnpj" value="" />
                        <button id="buscar_info_cnpj" class="btn btn-xs" type="button"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                <div class="control-group">
                    <label for="descricao" class="control-label"><span class="required">IE</span></label>
                    <div class="controls">
                        <input type="text" name="ie" value="" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="cep" class="control-label"><span class="required">CEP*</span></label>
                    <div class="controls">
                        <input id="cep" type="text" name="cep" value="" />
                    </div>
                </div>

                <div class="control-group">
                    <label for="descricao" class="control-label"><span class="required">Endereço*</span></label>
                    <div class="controls">
                        <input id="rua" type="text" name="logradouro" value="" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="descricao" class="control-label"><span class="required">Número*</span></label>
                    <div class="controls">
                        <input type="text" id="numero" name="numero" value="" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="descricao" class="control-label"><span class="required">Bairro*</span></label>
                    <div class="controls">
                        <input id="bairro" type="text" name="bairro" value="" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="descricao" class="control-label"><span class="required">Cidade*</span></label>
                    <div class="controls">
                        <input id="cidade" type="text" name="cidade" value="" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="descricao" class="control-label"><span class="required">UF*</span></label>
                    <div class="controls">
                        <input id="estado" type="text" name="uf" value="" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="descricao" class="control-label"><span class="required">Telefone*</span></label>
                    <div class="controls">
                        <input id="telefone" class="telefone1" type="text" name="telefone" value="" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="descricao" class="control-label"><span class="required">E-mail*</span></label>
                    <div class="controls">
                        <input id="email" type="text" name="email" value="" />
                    </div>
                </div>

                <div class="control-group">
                    <label for="logo" class="control-label"><span class="required">Logotipo*</span></label>
                    <div class="controls">
                        <input type="file" name="userfile" value="" />
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true" id="btnCancelExcluir">Cancelar</button>
                <button class="btn btn-success">Cadastrar</button>
            </div>
        </form>
    </div>

<?php
} else { ?>

    <div class="row-fluid" style="margin-top:0">
        <div class="alert alert-info">Os dados abaixo serão utilizados no cabeçalho das telas de impressão.</div>

			<div class="row-fluid" style="margin-top:0">
			<div class="span12">
			<div class="widget_box_Painel">
			<div class="widget-title">
			<span class="icon"><i class="fas fa-align-justify"></i></span>
			<h5>Dados do Emitente</h5>
			</div>
			<div class="widget_content">
			<table width="100%" class="table_master_os">
			<tbody>
  			<tr>
			<td style="width: 25%"><div align="center"><span><b>Logo</b></span> <img src=" <?= $dados[0]->url_logo; ?> " alt="Logo"/></div></td>
            <td style="width: 25%"><div align="center"><span><b>Logo Térmica</b></span><img src=" <?= $dados[0]->url_termica; ?> " alt="Logo Térmica"/></div></td>
            <td>
	<span style="font-size: 20px; "><b><?= $dados[0]->nome; ?></b></span><br><span>
	<i class="fas fa-fingerprint" style="margin:5px 1px"></i> <?= $dados[0]->cnpj; ?><br>
    <i class="fas fa-map-marker-alt" style="margin:4px 3px"></i> <?= $dados[0]->rua . ', nº: ' . $dados[0]->numero . ', ' . $dados[0]->bairro; ?><br>
    <i class="fas fa-map-marker-alt" style="margin:4px 3px"></i> <?= $dados[0]->cidade . ' - ' . $dados[0]->uf; ?><br>
    <i class="fas fa-map-marker-alt" style="margin:4px 3px"></i> <?= 'CEP: ' . $dados[0]->cep; ?><br>
	<span><i class="fas fa-envelope" style="margin:5px 1px"></i> <?= $dados[0]->email; ?></span><br>
    <span><i class="fas fa-phone-alt" style="margin:5px 1px"></i> <?=  $dados[0]->telefone; ?></span>
    							</td>
                            </tr>
                            </tbody>
  <tr>
    <td colspan="3">
      <div align="center"  style="padding: 10px;" ><a href="#modalAlterar" data-toggle="modal" role="button" class="btn btn-primary">Atualizar Dados</a>
        <a href="#modalLogo" data-toggle="modal" role="button" class="btn btn-inverse">Atualizar Logo</a>
        <a href="#modalTermica" data-toggle="modal" role="button" class="btn btn-inverse">Atualizar Logo Termica</a></div></td>
  </tr>
</table>




</div>
</div>
</div>
</div>
    </div>


<!-- Editar Dados Emitente -->

    <div id="modalAlterar" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <form action="<?= site_url('mapos/editarEmitente'); ?>" id="formAlterar" enctype="multipart/form-data" method="post" class="form-horizontal">
            <div class="modal_header_anexos">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="">Editar Dados do Emitente</h3>
            </div>
            <div class="modal-body">


                <div class="control-group">
                    <label for="nome" class="control-label">Razão Social<span class="required">*</span></label>
                    <div class="controls">
                        <input id="nomeEmitente" type="text" name="nome" value="<?= $dados[0]->nome; ?>" />
                        <input id="nome" type="hidden" name="id" value="<?= $dados[0]->id; ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="cnpj" class="control-label"><span class="required">CNPJ*</span></label>
                    <div class="controls">
                        <input class="cnpjEmitente" type="text" id="documento" name="cnpj" value="<?= $dados[0]->cnpj; ?>" />
                        <button id="buscar_info_cnpj" class="btn btn-xs" type="button"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                <div class="control-group">
                    <label for="descricao" class="control-label"><span class="required">IE</span></label>
                    <div class="controls">
                        <input type="text" name="ie" value="<?= $dados[0]->ie; ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="cep" class="control-label"><span class="required">CEP*</span></label>
                    <div class="controls">
                        <input id="cep" type="text" name="cep" value="<?= $dados[0]->cep; ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="descricao" class="control-label"><span class="required">Endereço*</span></label>
                    <div class="controls">
                        <input type="text" id="rua" name="logradouro" value="<?= $dados[0]->rua; ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="descricao" class="control-label"><span class="required">Número*</span></label>
                    <div class="controls">
                        <input type="text" id="numero" name="numero" value="<?= $dados[0]->numero; ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="descricao" class="control-label"><span class="required">Bairro*</span></label>
                    <div class="controls">
                        <input type="text" id="bairro" name="bairro" value="<?= $dados[0]->bairro; ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="descricao" class="control-label"><span class="required">Cidade*</span></label>
                    <div class="controls">
                        <input type="text" id="cidade" name="cidade" value="<?= $dados[0]->cidade; ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="descricao" class="control-label"><span class="required">UF*</span></label>
                    <div class="controls">
                        <input type="text" id="estado" name="uf" value="<?= $dados[0]->uf; ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="descricao" class="control-label"><span class="required">Telefone*</span></label>
                    <div class="controls">
                        <input type="text" class="telefone1" id="telefone" name="telefone" value="<?= $dados[0]->telefone; ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="descricao" class="control-label"><span class="required">E-mail*</span></label>
                    <div class="controls">
                        <input id="email" type="text" name="email" value="<?= $dados[0]->email; ?>" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true" id="btnCancelExcluir">Cancelar</button>
                <button class="btn btn-primary">Atualizar</button>
            </div>
        </form>
    </div>

<!-- Atualizar Logo -->

    <div id="modalLogo" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <form action="<?= site_url('mapos/editarLogo'); ?>" id="formLogo" enctype="multipart/form-data" method="post" class="form-horizontal">
            <div class="modal_header_anexos">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="">Atualizar Logotipo</h3>
            </div>
            <div class="modal-body">
                <div class="span12 alert alert-info">Selecione uma nova imagem da logotipo. Tamanho indicado (130 X 130).</div>
                <div class="control-group">
                    <label for="logo" class="control-label"><span class="required">Logotipo*</span></label>
                    <div class="controls">
                        <input type="file" name="userfile" value="" />
                        <input id="nome" type="hidden" name="id" value="<?= $dados[0]->id; ?>" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true" id="btnCancelExcluir">Cancelar</button>
                <button class="btn btn-primary">Atualizar</button>
            </div>
        </form>
    </div>

<!-- Atualizar Logo Termica-->

<div id="modalTermica" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <form action="<?= site_url('mapos/editarLogoTermica'); ?>" id="formLogoTermica" enctype="multipart/form-data" method="post" class="form-horizontal">
            <div class="modal_header_anexos">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="">Atualizar Logotipo</h3>
            </div>
            <div class="modal-body">
                <div class="span12 alert alert-info">Selecione uma nova imagem da logotipo. Tamanho indicado (130 X 130).</div>
                <div class="control-group">
                    <label for="logotermica" class="control-label"><span class="required">Logotipo*</span></label>
                    <div class="controls">
                        <input type="file" name="userfile" value="" />
                        <input id="nome" type="hidden" name="id" value="<?= $dados[0]->id; ?>" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true" id="btnCancelExcluir">Cancelar</button>
                <button class="btn btn-primary">Atualizar</button>
            </div>
        </form>
    </div>

<!-- Final Atualizar Logo Termica-->

<?php } ?>


<script type="text/javascript" src="<?= base_url() ?>assets/js/jquery.validate.js"></script>
<script type="text/javascript">
    $(document).ready(function() {

        $("#formLogo").validate({
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


        $("#formLogoTermica").validate({
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


        $("#formCadastrar").validate({
            rules: {
                userfile: {
                    required: true
                },
                nome: {
                    required: true
                },
                cnpj: {
                    required: true
                },
                logradouro: {
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
                uf: {
                    required: true
                },
                telefone: {
                    required: true
                },
                email: {
                    required: true
                }
            },
            messages: {
                userfile: {
                    required: 'Campo Requerido.'
                },
                nome: {
                    required: 'Campo Requerido.'
                },
                cnpj: {
                    required: 'Campo Requerido.'
                },
                logradouro: {
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
                uf: {
                    required: 'Campo Requerido.'
                },
                telefone: {
                    required: 'Campo Requerido.'
                },
                email: {
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


        $("#formAlterar").validate({
            rules: {
                userfile: {
                    required: true
                },
                nome: {
                    required: true
                },
                cnpj: {
                    required: true
                },
                logradouro: {
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
                uf: {
                    required: true
                },
                telefone: {
                    required: true
                },
                email: {
                    required: true
                }
            },
            messages: {
                userfile: {
                    required: 'Campo Requerido.'
                },
                nome: {
                    required: 'Campo Requerido.'
                },
                cnpj: {
                    required: 'Campo Requerido.'
                },
                logradouro: {
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
                uf: {
                    required: 'Campo Requerido.'
                },
                telefone: {
                    required: 'Campo Requerido.'
                },
                email: {
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

    });
</script>