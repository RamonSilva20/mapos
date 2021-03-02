<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/funcoes.js"></script>
<style>
    /* Hiding the checkbox, but allowing it to be focused */
    .badgebox {
        opacity: 0;
    }

    .badgebox + .badge {
        /* Move the check mark away when unchecked */
        text-indent: -999999px;
        /* Makes the badge's width stay the same checked and unchecked */
        width: 27px;
    }

    .badgebox:focus + .badge {
        /* Set something to make the badge looks focused */
        /* This really depends on the application, in my case it was: */
        /* Adding a light border */
        box-shadow: inset 0px 0px 5px;
        /* Taking the difference out of the padding */
    }

    .badgebox:checked + .badge {
        /* Move the check mark back when checked */
        text-indent: 0;
    }
</style>
<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
            <span class="icon">
            <i class="fas fa-user"></i>
            </span>
                <h5>Cadastro de Cliente</h5>
                <div class="buttons">
                    <a title="Voltar" class="btn btn-mini btn-inverse" href="<?php echo site_url() ?>/clientes"><i
                                class="fas fa-arrow-left"></i> Voltar</a>
                </div>
            </div>
            <div class="widget_box_Painel2">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#home">Informações Pessoais</a></li>
                <li><a data-toggle="tab" href="#menu2">Endereço</a></li>
            </ul>
            <form action="<?php echo current_url(); ?>" id="formCliente" method="post" class="form-horizontal">
                <div class="widget-content nopadding tab-content">
                    <?php if ($custom_error != '') {
                        echo '<div class="alert alert-danger">' . $custom_error . '</div>';
                    } ?>
                    <div id="home" class="tab-pane fade in active">
                        <div class="control-group">
                            <label for="documento" class="control-label">CPF/CNPJ</label>
                            <div class="controls">
                                <input id="documento" class="cpfcnpj" type="text" name="documento"
                                       value="<?php echo set_value('documento'); ?>"/>
                                <button id="buscar_info_cnpj" class="btn btn-xs" type="button">Buscar Informações
                                    (CNPJ)
                                </button>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="nomeCliente" class="control-label">Nome/Razão Social<span class="required">*</span></label>
                            <div class="controls">
                                <input id="nomeCliente" type="text" name="nomeCliente"
                                       value="<?php echo set_value('nomeCliente'); ?>"/>
                            </div>
                        </div>
                        <div class="control-group">
                    <label for="senha" class="control-label">Senha<span class="required">*</span></label>
					<?php function gerar_senha($tamanho, $maiusculas, $minusculas, $numeros, $simbolos){
  $ma = "ABCDEFGHIJKLMNOPQRSTUVYXWZ"; // $ma contem as letras maiúsculas
  $mi = "abcdefghijklmnopqrstuvyxwz"; // $mi contem as letras minusculas
  $nu = "0123456789"; // $nu contem os números
  $si = "!@#$%¨&*()_+="; // $si contem os símbolos

  if ($maiusculas){
        // se $maiusculas for "true", a variável $ma é embaralhada e adicionada para a variável $senha
        $senha .= str_shuffle($ma);
  }

    if ($minusculas){
        // se $minusculas for "true", a variável $mi é embaralhada e adicionada para a variável $senha
        $senha .= str_shuffle($mi);
    }

    if ($numeros){
        // se $numeros for "true", a variável $nu é embaralhada e adicionada para a variável $senha
        $senha .= str_shuffle($nu);
    }

    if ($simbolos){
        // se $simbolos for "true", a variável $si é embaralhada e adicionada para a variável $senha
        $senha .= str_shuffle($si);
    }

    // retorna a senha embaralhada com "str_shuffle" com o tamanho definido pela variável $tamanho
    return substr(str_shuffle($senha),0,$tamanho);
}
?>
                        <div class="controls">
                        
                            <input id="senha" class="senha" type="password" name="senha" value="<?php echo gerar_senha(10, true, true, true, false); ?>" />
                      </div>
                  </div>
                        <div class="control-group">
                            <label for="telefone" class="control-label">Telefone<span class="required">*</span></label>
                            <div class="controls">
                                <input id="telefone" class="telefone1" type="text" name="telefone"
                                       value="<?php echo set_value('telefone'); ?>"/>
                            </div>
                        </div>
                        <div class="control-group">
                          <label for="celular" class="control-label">Telefone 2</label>
                          <div class="controls">
                                <input id="celular" class="telefone1" type="text" name="celular"
                                       value="<?php echo set_value('celular'); ?>"/>
                            </div>
                        </div>
                        <div class="control-group">
                    <label for="email" class="control-label">Email<span class="required">*</span></label>
					<?php function gerar_email($tamanho, $maiusculas, $minusculas, $numeros, $simbolos){
  $ma = "ABCDEFGHIJKLMNOPQRSTUVYXWZ"; // $ma contem as letras maiúsculas
  $mi = "abcdefghijklmnopqrstuvyxwz"; // $mi contem as letras minusculas
  $nu = "0123456789"; // $nu contem os números
  $si = "!@#$%¨&*()_+="; // $si contem os símbolos

  if ($maiusculas){
        // se $maiusculas for "true", a variável $ma é embaralhada e adicionada para a variável $email
        $email .= str_shuffle($ma);
  }

    if ($minusculas){
        // se $minusculas for "true", a variável $mi é embaralhada e adicionada para a variável $email
        $email .= str_shuffle($mi);
    }

    if ($numeros){
        // se $numeros for "true", a variável $nu é embaralhada e adicionada para a variável $email
        $email .= str_shuffle($nu);
    }

    if ($simbolos){
        // se $simbolos for "true", a variável $si é embaralhada e adicionada para a variável $email
        $email .= str_shuffle($si);
    }

    // retorna a senha embaralhada com "str_shuffle" com o tamanho definido pela variável $tamanho
    return substr(str_shuffle($email),0,$tamanho);
}
?>
                        <div class="controls">
                        
                            <input id="email" type="text" name="email" value="<?php echo gerar_email(15, false, true, false, false); ?><?php echo $configuration['masteros_0'] ?>" />
                      </div>
                  </div>
                        <div class="control-group">
                            <label class="control-label">Tipo de Cliente</label>
                            <div class="controls">
                                <label for="fornecedor" class="btn btn-default" style="margin-top: 5px;">Fornecedor
                                    <input type="checkbox" id="fornecedor" name="fornecedor" class="badgebox" value="0">
                                    <span class="badge">&check;</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="span12">
                                <div class="span6 offset3">
                                    <button type="submit" class="btn btn-primary">Salvar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Menu Endereços -->
                    <div id="menu2" class="tab-pane fade">
                        <div class="control-group" class="control-label">
                            <label for="cep" class="control-label">CEP<span class="required">*</span></label>
                            <div class="controls">
                                <input id="cep" type="text" name="cep" value="<?php echo set_value('cep'); ?>"/>
                            </div>
                        </div>
                        <div class="control-group" class="control-label">
                            <label for="rua" class="control-label">Rua</label>
                            <div class="controls">
                                <input id="rua" type="text" name="rua" value="<?php echo set_value('rua'); ?>"/>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="numero" class="control-label">Número</label>
                            <div class="controls">
                                <input id="numero" type="text" name="numero"
                                       value="<?php echo set_value('numero'); ?>"/>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="complemento" class="control-label">Complemento</label>
                            <div class="controls">
                                <input id="complemento" type="text" name="complemento"
                                       value="<?php echo set_value('complemento'); ?>"/>
                            </div>
                        </div>
                        <div class="control-group" class="control-label">
                            <label for="bairro" class="control-label">Bairro</label>
                            <div class="controls">
                                <input id="bairro" type="text" name="bairro"
                                       value="<?php echo set_value('bairro'); ?>"/>
                            </div>
                        </div>
                        <div class="control-group" class="control-label">
                            <label for="cidade" class="control-label">Cidade<span class="required">*</span></label>
                            <div class="controls">
                                <input id="cidade" type="text" name="cidade"
                                       value="<?php echo set_value('cidade'); ?>"/>
                            </div>
                        </div>
                        <div class="control-group" class="control-label">
                            <label for="estado" class="control-label">Estado<span class="required">*</span></label>
                            <div class="controls">
                                <select id="estado" name="estado">
                                    <option value="">Selecione...</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="span12">
                                <div class="span6 offset3">
                                    <button type="submit" class="btn btn-primary">Salvar</button>
                                </div>
                            </div>
                        </div>
            </form>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $.getJSON('<?php echo base_url() ?>assets/json/estados.json', function (data) {
            for (i in data.estados) {
                $('#estado').append(new Option(data.estados[i].nome, data.estados[i].sigla));
                var curState = '<?php echo set_value('estado'); ?>';
                if (curState) {
                    $("#estado option[value=" + curState + "]").prop("selected", true);
                }
            }
        });
        $("#nomeCliente").focus();
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
                senha: {
                    required: true
                },
                email: {
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
                senha: {
                    required: 'Campo Requerido.'
                },
                email: {
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
            },

            errorClass: "help-inline",
            errorElement: "span",
            highlight: function (element, errorClass, validClass) {
                $(element).parents('.control-group').addClass('error');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error');
                $(element).parents('.control-group').addClass('success');
            }
        });
    });
</script>
