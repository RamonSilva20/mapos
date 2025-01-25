<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/funcoes.js"></script>

<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-user"></i>
                </span>
                <h5>Editar Meus Dados</h5>
            </div>
            <div class="widget-content nopadding tab-content">

                <form action="<?php echo current_url(); ?>" id="formCliente" method="post" class="form-horizontal">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <input type="hidden" name="idClientes" id="idClientes" value="<?php echo $result->idClientes; ?>" />
                    <div class="control-group">
                        <label for="email" class="control-label">Email</label>
                        <div class="controls">
                            <input id="email" type="text" name="email" value="<?= $result->email ; ?>" autocomplete="off" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="contato" class="control-label">Contato</label>
                        <div class="controls">
                            <input id="contato" type="text" name="contato" value="<?php echo $result->contato; ?>" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="senha" class="control-label">Senha<span class="required">*</span></label>
                        <div class="controls">
                            <input id="senha" type="password" placeholder="Não preencha se não quiser alterar." name="senha" value="" />
                            <img id="imgSenha" src="<?= base_url() ?>assets/img/eye.svg" alt="">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="tipoCliente" class="control-label">Tipo<span class="required">*</span></label>
                        <div class="controls">
                            <select id="tipoCliente" name="tipoCliente" style="width:219.6px">
                                <option value="1" <?= $result->pessoa_fisica ? 'selected' : ''; ?> >Pessoa Física</option>
                                <option value="2" <?= $result->pessoa_fisica ? '' : 'selected'; ?> >Pessoa Jurídica</option>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <?php if (isset($custom_error) && $custom_error != '') {
                            echo '<div class="alert alert-danger">' . $custom_error . '</div>';
                        } ?>
                        <label for="documento" class="control-label">CPF<span class="required">*</span></label>
                        <div class="controls">
                            <input id="documento" class="" type="text" name="documento" value="<?= $result->documento; ?>" />
                            <button id="buscar_info_cnpj" class="btn btn-mini" type="button" style="display:none;">
                                <span class="button_icon"><i class="bx bx-search-alt-2"></i></span></button>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="rg_ie" class="control-label">RG</label>
                        <div class="controls">
                            <input id="rg_ie" type="text" name="rg_ie" value="<?= $result->rg_ie; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="nomeCliente" class="control-label">Nome Completo<span class="required">*</span></label>
                        <div class="controls">
                            <input id="nomeCliente" type="text" name="nomeCliente" value="<?= $result->nomeCliente; ?>" />
                        </div>
                    </div>
                    <div class="control-group" style="display: none;">
                        <label for="nomeFantasia" class="control-label">Nome Fantasia</label>
                        <div class="controls">
                            <input id="nomeFantasia" type="text" name="nomeFantasia" value="<?= $result->nomeFantasia; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="contato" class="control-label">Contato:</label>
                        <div class="controls">
                            <input class="contato" type="text" name="contato" value="<?= $result->contato; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="telefone" class="control-label">Telefone</label>
                        <div class="controls">
                            <input id="telefone" type="text" name="telefone" value="<?= $result->telefone; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="celular" class="control-label">Celular</label>
                        <div class="controls">
                            <input id="celular" type="text" name="celular" value="<?= $result->celular; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="cep" class="control-label">CEP</label>
                        <div class="controls">
                            <input id="cep" type="text" name="cep" value="<?= $result->cep; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="rua" class="control-label">Rua</label>
                        <div class="controls">
                            <input id="rua" type="text" name="rua" value="<?= $result->rua; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="numero" class="control-label">Número</label>
                        <div class="controls">
                            <input id="numero" type="text" name="numero" value="<?= $result->numero; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="complemento" class="control-label">Complemento</label>
                        <div class="controls">
                            <input id="complemento" type="text" name="complemento" value="<?= $result->complemento; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="bairro" class="control-label">Bairro</label>
                        <div class="controls">
                            <input id="bairro" type="text" name="bairro" value="<?= $result->bairro; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="cidade" class="control-label">Cidade</label>
                        <div class="controls">
                            <input id="cidade" type="text" name="cidade" value="<?= $result->cidade; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="estado" class="control-label">Estado</label>
                        <div class="controls">
                            <select id="estado" name="estado" style="width: 219.6px;">
                                <option value="">Selecione...</option>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="dataNascimento" class="control-label">Data de Nascimento</label>
                        <div class="controls">
                            <input id="dataNascimento" type="date" name="dataNascimento" value="<?= $result->dataNascimento; ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="sexo" class="control-label">Sexo</label>
                        <div class="controls">
                            <select id="sexo" name="sexo" style="width: 219.6px;">
                                <option value="Masculino" <?= $result->sexo === 'Masculino' ? 'selected' : '';?> >Masculino</option>
                                <option value="Feminino" <?= $result->sexo === 'Feminino' ? 'selected' : '';?> >Feminino</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <div class="span12">
                            <div class="span6 offset3" style="display:flex;justify-content: center">
                                <button type="submit" class="button btn btn-primary">
                                    <span class="button__icon"><i class="bx bx-sync"></i></span><span class="button__text2">Atualizar</span></button>
                                <a href="<?php echo base_url() ?>index.php/mine/conta" id="" class="button btn btn-mini btn-warning">
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
        $.getJSON('<?= base_url() ?>assets/json/estados.json', function(data) {
            for (i in data.estados) {
                $('#estado').append(new Option(data.estados[i].nome, data.estados[i].sigla));
                var curState = '<?= $result->estado; ?>';
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

        $("#email").focus();
        $(function() {
            var pessoa_fisica = <?php echo $result->pessoa_fisica ; ?>;
            if (pessoa_fisica) {
                $("#documento").mask("000.000.000-00", {
                    onpaste: function (e) {
                        e.preventDefault();
                        var clipboardCurrentData = (e.originalEvent || e).clipboardData.getData('text/plain');
                        $("#documento").val(clipboardCurrentData);
                    }
                });
            } else {
                $("#documento").mask("00.000.000/0000-00", {
                    onpaste: function (e) {
                        e.preventDefault();
                        var clipboardCurrentData = (e.originalEvent || e).clipboardData.getData('text/plain');
                        $("#documento").val(clipboardCurrentData);
                    }
                });
                $("#documento").parent().prev(".control-label").text("CNPJ");
                $("#buscar_info_cnpj").css("display", "inline-block");
                $("#rg_ie").parent().prev(".control-label").text("IE");
                $("#nomeCliente").parent().prev(".control-label").text("Razão Social");
                $("#nomeFantasia").parent().parent().css("display", "block");
                $("#dataNascimento").val("");
                $("#dataNascimento").parent().parent().css("display", "none");
                $("#sexo").parent().parent().css("display", "none");
            }
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
