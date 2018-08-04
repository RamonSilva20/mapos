<div class="row-fluid" style="margin-top:0">

    <div class="span12">

        <div class="widget-box">

            <div class="widget-title">

                <span class="icon">

                    <i class="icon-user"></i>

                </span>

                <h5>Cadastro de Cliente</h5>

            </div>

            <div class="widget-content nopadding">

                <?php if ($custom_error != '') {

                    echo '<div class="alert alert-danger">' . $custom_error . '</div>';

                } ?>

                <form action="<?php echo current_url(); ?>" id="formCliente" method="post" class="form-horizontal" >

                    <input id="documento" type="hidden" name="documento" value="999.999.999-99">

                    <div class="control-group">

                        <label for="nomeCliente" class="control-label">Nome<span class="required">*</span></label>

                        <div class="controls">

                            <input id="nomeCliente" type="text" name="nomeCliente" value="<?php echo set_value('nomeCliente'); ?>"  />

                        </div>

                    </div>

                    <!-- div class="control-group">

                        <label for="documento" class="control-label">CPF/CNPJ</label>

                        <div class="controls">

                            <input id="documento" type="text" name="documento" value="<?php //echo set_value('documento'); ?>"  />

                        </div>

                    </div -->

                    <div class="control-group">

                        <label for="celular" class="control-label">Celular<span class="required">*</span></label>

                        <div class="controls">

                            <select id="tipoCelular" name="tipoCelular" style="width: 100px;">

                                <option value="">SELECIONE</option>
                                <option value="Oi">Oi</option>
                                <option value="Tim">Tim</option>
                                <option value="Claro">Claro</option>
                                <option value="Vivo">Vivo</option>

                            </select>&nbsp;

                            <input id="celular" type="text" name="celular" value="<?php echo set_value('celular'); ?>"  />

                        </div>

                    </div>

                    <div class="control-group">

                        <label for="telefone" class="control-label">Telefone</label>

                        <div class="controls">

                            <select id="tipoTelefone" name="tipoTelefone" style="width: 100px;">

                                <option value="">SELECIONE</option>

                                <option value="Residencial">Residencial</option>
                                <option value="Comercial">Comercial</option>
                                <option value="Recado">Recado</option>
                                <option value="Oi">Oi</option>
                                <option value="Tim">Tim</option>
                                <option value="Claro">Claro</option>
                                <option value="Vivo">Vivo</option>

                            </select>&nbsp;

                            <input id="telefone" type="text" name="telefone" value="<?php echo set_value('telefone'); ?>"  />
                            <input id="recado" type="text" name="recado" placeholder="Pessoa para recado" value="<?php echo set_value('recado'); ?>" style="display: none;"  />

                        </div>

                    </div>

                    <div class="control-group">

                        <label for="email" class="control-label">Email<span class="required">*</span></label>

                        <div class="controls">

                            <input id="email" type="text" name="email" value="<?php echo set_value('email'); ?>"  />

                        </div>

                    </div>

                    <div class="control-group">

                        <label for="canalEntrada" class="control-label">Canal de Entrada<span class="required">*</span></label>

                        <div class="controls">
                            <select id="canalEntrada" name="canalEntrada">
                                <option value="">Onde nos conheceu?</option>
                                <option value="Base">Já sou cliente</option>
                                <option value="Direto">Direto</option>
                                <option value="Indicação">Indicação</option>
                                <option value="Google">Google</option>
                                <option value="Facebook">Facebook</option>
                                <option value="Internet">Internet</option>
                                <option value="Email">Email</option>
                                <option value="Panfleto">Panfleto</option>
                                <option value="Outros">Outros</option>
                            </select>&nbsp;
                            <input id="canalEntradaIndicacao" type="text" name="canalEntradaIndicacao" placeholder="Nome de quem indicou" style="display: none;">
                        </div>

                    </div>



                    <div id="cepDiv" class="control-group" class="control-label" style="display: none;">

                        <label for="cep" class="control-label">CEP<span class="required">*</span></label>

                        <div class="controls">

                            <input id="cep" type="text" name="cep" value="<?php echo set_value('cep'); ?>"  />&nbsp;

                            <a href="javascript:showZipFieldsErro();">Não sei meu CEP</a>

                        </div>

                    </div>



                    <div id="hide-address" style="position:absolute;left:-2000px">

                        <div class="control-group" class="control-label">

                            <label for="rua" class="control-label">Rua<span class="required">*</span></label>

                            <div class="controls">

                                <input id="rua" type="text" name="rua" value="<?php echo set_value('rua'); ?>"  />

                            </div>

                        </div>



                        <div class="control-group">

                            <label for="numero" class="control-label">Número<span class="required">*</span></label>

                            <div class="controls">

                                <input id="numero" type="text" name="numero" value="<?php echo set_value('numero'); ?>"  />

                            </div>

                        </div>



                        <div class="control-group" class="control-label">

                            <label for="bairro" class="control-label">Bairro<span class="required">*</span></label>

                            <div class="controls">

                                <input id="bairro" type="text" name="bairro" value="<?php echo set_value('bairro'); ?>"  />

                            </div>

                        </div>



                        <div class="control-group" class="control-label">

                            <label for="cidade" class="control-label">Cidade<span class="required">*</span></label>

                            <div class="controls">

                                <input id="cidade" type="text" name="cidade" value="<?php echo set_value('cidade'); ?>"  />

                            </div>

                        </div>



                        <div class="control-group" class="control-label">

                            <label for="estado" class="control-label">Estado<span class="required">*</span></label>

                            <div class="controls">

                                <input id="estado" type="text" name="estado" value="<?php echo set_value('estado'); ?>"  />

                            </div>

                        </div>

                    </div>



                    <div class="form-actions">

                        <div class="span12">

                            <div class="span6 offset3">

                                <button type="submit" class="btn btn-success"><i class="icon-plus icon-white"></i> Adicionar</button>

                                <a href="<?php echo base_url() ?>index.php/clientes" id="" class="btn"><i class="icon-arrow-left"></i> Voltar</a>

                            </div>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>



<!--script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false"></script-->

<script src="<?php echo base_url()?>js/jquery.cep.js"></script>

<script src="<?php echo base_url()?>js/jquery.validate.js"></script>

<script src="<?php echo base_url()?>js/jquery.mask.min.js"></script>

<script type="text/javascript">

      $(document).ready(function(){

            $('#formCliente').validate({

                rules :{

                      nomeCliente:{ required: true},

                      //documento:{ required: true},

                      celular:{ required: true},

                      email: { required: true, email: true},

                      cep:{ required: true},

                      rua:{ required: true},

                      numero:{ required: true},

                      bairro:{ required: true},

                      cidade:{ required: true},

                      estado:{ required: true}

                },

                messages:{

                      nomeCliente :{ required: 'Campo Requerido.'},

                      //documento :{ required: 'Campo Requerido.'},

                      celular:{ required: 'Campo Requerido.'},

                      email:{ required: 'Campo Requerido.'},

                      cep:{ required: 'Campo Requerido.'},

                      rua:{ required: 'Campo Requerido.'},

                      numero:{ required: 'Campo Requerido.'},

                      bairro:{ required: 'Campo Requerido.'},

                      cidade:{ required: 'Campo Requerido.'},

                      estado:{ required: 'Campo Requerido.'}



                },



                errorClass: "help-inline",

                errorElement: "span",

                highlight:function(element, errorClass, validClass) {

                    $(element).parents('.control-group').addClass('error');

                },

                unhighlight: function(element, errorClass, validClass) {

                    $(element).parents('.control-group').removeClass('error');

                    $(element).parents('.control-group').addClass('success');

                }

            });

            $( "#tipoTelefone" ).change(function() {
                $( "#recado" ).attr('value','');
                if ( $(this).val() == "Recado") {
                    $( "#recado" ).show();
                } else {
                    $( "#recado" ).hide();
                }
            });


            var SPMaskBehavior = function (val) {
                return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
            },
            spOptions = {
                onKeyPress: function(val, e, field, options) {
                    field.mask(SPMaskBehavior.apply({}, arguments), options);
                }
            };

            if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
                console.log("mobile");
            } else {
                $('#telefone, #celular').mask(SPMaskBehavior, spOptions);
                $("#cep").mask("99999-999");
                $("#documento").mask("999.999.999-99");
            }
            


            $( "#canalEntrada" ).change(function() {
              if ( $(this).val() != "" && $(this).val() != "Indicação" ) {
                $( "#cepDiv" ).show();
                $( "#canalEntradaIndicacao" ).attr('value','').hide();
              } else if ( $(this).val() != "" && $(this).val() == "Indicação" ) {
                $( "#cepDiv" ).hide();
                $( "#canalEntradaIndicacao" ).show();
                $( "#canalEntradaIndicacao" ).change(function() {
                    if ( $(this).val() != "" ) {
                        $( "#cepDiv, #hide-address" ).show();
                    } else {
                        $( "#cepDiv, #hide-address" ).hide();
                    }
                });
              } else {
                $( "#cepDiv, #canalEntradaIndicacao, #hide-address" ).hide();
              }
            });

            // Copy #nomeCliente to clipboard
            document.querySelector("#nomeCliente").onchange = function() {
                // Select the content
                document.querySelector("#nomeCliente").select();
                // Copy to the clipboard
                document.execCommand('copy');
            };

      });

</script>