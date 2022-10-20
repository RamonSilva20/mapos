<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/funcoes.js"></script>

<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-user"></i>
                </span>
                <h5>Cadastro de Usuário</h5>
            </div>
            <div class="widget-content nopadding tab-content">
                <?php if ($custom_error != '') {
    echo '<div class="alert alert-danger">' . $custom_error . '</div>';
} ?>
                <form action="<?php echo current_url(); ?>" id="formUsuario" method="post" class="form-horizontal">
                    <div class="control-group">
                        <label for="nome" class="control-label">Nome<span class="required">*</span></label>
                        <div class="controls">
                            <input id="nome" type="text" name="nome" value="<?php echo set_value('nome'); ?>" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="rg" class="control-label">RG<span class="required">*</span></label>
                        <div class="controls">
                            <input id="rg" type="text" name="rg" value="<?php echo set_value('rg'); ?>" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="cpf" class="control-label">CPF<span class="required">*</span></label>
                        <div class="controls">
                            <input class="" type="text" id="cpfUser" name="cpf" value="<?php echo set_value('cpf'); ?>" />
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

                    <div class="control-group">
                        <label for="senha" class="control-label">Senha<span class="required">*</span></label>
                        <div class="controls">
                            <input id="senha" type="password" name="senha" value="<?php echo set_value('senha'); ?>" />
                        </div>
                    </div>

                    <div class="control-group" class="control-label">
                        <label for="cep" class="control-label">CEP<span class="required">*</span></label>
                        <div class="controls">
                            <input id="cep" type="text" name="cep" value="<?php echo set_value('cep'); ?>" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="rua" class="control-label">Rua<span class="required">*</span></label>
                        <div class="controls">
                            <input id="rua" type="text" name="rua" value="<?php echo set_value('rua'); ?>" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="numero" class="control-label">Numero<span class="required">*</span></label>
                        <div class="controls">
                            <input id="numero" type="text" name="numero" value="<?php echo set_value('numero'); ?>" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="bairro" class="control-label">Bairro<span class="required">*</span></label>
                        <div class="controls">
                            <input id="bairro" type="text" name="bairro" value="<?php echo set_value('bairro'); ?>" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="cidade" class="control-label">Cidade<span class="required">*</span></label>
                        <div class="controls">
                            <input id="cidade" type="text" name="cidade" value="<?php echo set_value('cidade'); ?>" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="estado" class="control-label">Estado<span class="required">*</span></label>
                        <div class="controls">
                            <input id="estado" type="text" name="estado" value="<?php echo set_value('estado'); ?>" />
                        </div>
                    </div>


                    <!-- Campo para inserir a data de validade de acesso do usuário-->
                    <div class="control-group">
                        <label for="dataExpiracao" class="control-label">Expira em <span class="required">*</span></label>
                        <div class="controls">
                            <input id="dataExpiracao" type="date" name="dataExpiracao" value="<?php echo set_value('dataExpiracao'); ?>" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">Situação*</label>
                        <div class="controls">
                            <select name="situacao" id="situacao">
                                <option value="1">Ativo</option>
                                <option value="0">Inativo</option>
                            </select>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">Permissões<span class="required">*</span></label>
                        <div class="controls">
                            <select name="permissoes_id" id="permissoes_id">
                                <?php foreach ($permissoes as $p) {
    echo '<option value="' . $p->idPermissao . '">' . $p->nome . '</option>';
} ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <div class="span12">
                            <div class="span6 offset3" style="display:flex">
                                <button type="submit" class="button btn btn-success">
                                  <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">Adicionar</span></button>
                                <a href="<?php echo base_url() ?>index.php/usuarios" id="" class="button btn btn-mini btn-warning">
                                  <span class="button__icon"><i class="bx bx-undo"></i></span> <span class="button__text2">Voltar</span></a>
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

        $('#formUsuario').validate({
            rules: {
                nome: {
                    required: true
                },
                dataExpiracao: {
                    required: true
                },
                cpf: {
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
                nome: {
                    required: 'Campo Requerido.'
                },
                dataExpiracao: {
                    required: 'Campo Requerido.'
                },
                cpf: {
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
