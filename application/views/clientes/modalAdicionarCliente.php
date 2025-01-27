<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/funcoes.js"></script>

<style>
    #imgSenha {
        width: 18px;
        cursor: pointer;
    }

    /* Hiding the checkbox, but allowing it to be focused */
    .badgebox {
        opacity: 0;
    }

    .badgebox+.badge {
        /* Move the check mark away when unchecked */
        text-indent: -999999px;
        /* Makes the badge's width stay the same checked and unchecked */
        width: 27px;
    }

    .badgebox:focus+.badge {
        /* Set something to make the badge looks focused */
        /* This really depends on the application, in my case it was: */
        /* Adding a light border */
        box-shadow: inset 0px 0px 5px;
        /* Taking the difference out of the padding */
    }

    .badgebox:checked+.badge {
        /* Move the check mark back when checked */
        text-indent: 0;
    }

    .control-group.error .help-inline {
        display: flex;
    }

    /* .form-horizontal .control-group {
        border-bottom: 1px solid #ffffff;
    } */

    .form-horizontal .controls {
        margin-left: 20px;
        padding-bottom: 8px;
    }

    .form-horizontal .control-label {
        text-align: left;
        padding-top: 15px;
    }

    .nopadding {
        padding: 0 20px !important;
        /* margin-right: 20px; */
    }

    .widget-title h5 {
        padding-bottom: 30px;
        text-align-last: left;
        font-size: 2em;
        font-weight: 500;
    }

    @media (max-width: 480px) {
        form {
            display: contents !important;
        }

        .form-horizontal .control-label {
            margin-bottom: -6px;
        }

        .btn-xs {
            position: initial !important;
        }
    }
</style>

<div id="modalAdicionarCliente" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" >Adcionar Cliente</h4>
            </div>
            <form id="formAdicionarCliente" name="formAdicionarCliente" method="post" action="#">
                <div class="modal-body">
                    <div class="span6">
                        <div class="control-group">
                            <label for="tipoCliente" class="control-label">Tipo</label>
                            <div class="controls">
                                <select name="tipoCliente" id="tipoCliente">
                                    <option value="1" selected aria-selected="true">Pessoa Física</option>
                                    <option value="2">Pessoa Jurídica</option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="documento" class="control-label">CPF</label>
                            <div class="controls">
                                <input id="documento" class="" type="text" name="documento" value="<?php echo set_value('documento'); ?>" />
                                <button id="buscar_info_cnpj" class="btn btn-xs" type="button" style="display: none;">Buscar(CNPJ)</button>
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
                            <label for="email" class="control-label">Email</label>
                            <div class="controls">
                                <input id="email" type="text" name="email" value="<?php echo set_value('email'); ?>" autocomplete="off" />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="senha" class="control-label">Senha</label>
                            <div class="controls">
                                <input class="form-control" id="senha" type="password" name="senha" autocomplete="new-password" value="<?php echo set_value('senha'); ?>" />
                                <img id="imgSenha" src="<?php echo base_url() ?>assets/img/eye.svg" alt="">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label">Tipo de Cliente</label>
                            <div class="controls">
                                <label for="fornecedor" class="btn btn-default" style="padding: 4px 6px; width: 206px">Fornecedor
                                    <input type="checkbox" id="fornecedor" name="fornecedor" class="badgebox">
                                    <span class="badge">&check;</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="span6">
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
                        <div class="control-group">
                            <label for="estado" class="control-label">Estado</label>
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
                        <div class="control-group">
                            <label for="obsCliente" class="control-label">Observações</label>
                            <div class="controls">
                                <textarea id="obsCliente" name="obsCliente"><?php echo set_value('obsCliente'); ?></textarea>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="situacao" class="control-label">Situação</label>
                            <div class="controls">
                                <select id="situacao" name="situacao">
                                    <option value="1" selected aria-selected="true">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-actions">
                        <div class="span6 offset3" style="display:flex;justify-content: center">
                            <button type="submit" class="button btn btn-mini btn-success"><span class="button__icon"><i class='bx bx-save'></i></span> <span class="button__text2">Salvar</span></a></button>
                            <a title="Voltar" class="button btn btn-warning"><span class="button__icon"><i class="bx bx-undo"></i></span> <span class="button__text2">Voltar</span></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
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

        $.getJSON('<?php echo base_url() ?>assets/json/estados.json', function(data) {
            for (i in data.estados) {
                $('#estado').append(new Option(data.estados[i].nome, data.estados[i].sigla));
            }
            var curState = '<?php echo set_value('estado'); ?>';
            if (curState) {
                $("#estado option[value=" + curState + "]").prop("selected", true);
            }
        });
        
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

        // Função para fechar o modal e limpar os campos
        $('[title="Voltar"]').click(function() {
            $('#modalAdicionarCliente').modal('hide');
        });

        $('#formAdicionarCliente').validate({
            rules: {
                nomeCliente: {
                    required: true
                },
            },
            messages: {
                nomeCliente: {
                    required: 'Campo Requerido.'
                },
            },

            errorClass: "help-inline",
            errorElement: "span",
            highlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').addClass('error');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error');
                $(element).parents('.control-group').addClass('success');
            },

            submitHandler: function(form) {
                console.log("Enviando dados do formulário!");
                event.preventDefault();

                var dados = $('#formAdicionarCliente').serialize();
                $.ajax({
                    type: 'POST',
                    url: '<?php echo base_url() ?>index.php/os/adicionarCliente',
                    data: dados,
                    dataType: 'json',
                    success: function(data) {
                        console.log(data.success);
                        if (data.success) {
                            Swal.fire({
                                type: "success",
                                title: "Atenção",
                                text: "Cliente adicionado com sucesso!"
                            });
                            $('#modalAdicionarCliente').modal('hide');
                        } else {
                            Swal.fire({
                                type: "error",
                                title: "Atenção",
                                text: "Erro ao adicionar cliente!"
                            });
                            $('#modalAdicionarCliente').modal('hide');
                        }
                    }
                });
                return false;
            }
        });
    });
</script>