<div class="row-fluid" style="margin-top:0">

    <div class="span12">

        <div class="widget-box">

            <div class="widget-title">

                <span class="icon">

                    <i class="icon-user"></i>

                </span>

                <h5>Editar Cliente</h5>

            </div>

            <div class="widget-content nopadding">

                <?php if ($custom_error != '') {

                    echo '<div class="alert alert-danger">' . $custom_error . '</div>';

                } ?>

                <form action="<?php echo current_url(); ?>" id="formCliente" method="post" class="form-horizontal" >

                    <div class="control-group">

                        <?php echo form_hidden('idClientes',$result->idClientes) ?>

                        <label for="nomeCliente" class="control-label">Nome<span class="required">*</span></label>

                        <div class="controls">

                            <input id="nomeCliente" type="text" name="nomeCliente" value="<?php echo $result->nomeCliente; ?>"  />

                        </div>

                    </div>

                    <div class="control-group">

                        <label for="documento" class="control-label">CPF/CNPJ<span class="required">*</span></label>

                        <div class="controls">

                            <input id="documento" type="text" name="documento" value="<?php echo $result->documento; ?>"  />

                        </div>

                    </div>

                    <div class="control-group">

                        <label for="celular" class="control-label">Telefone<span class="required">*</span></label>

                        <div class="controls">

                            <select id="tipoCelular" name="tipoCelular" style="width: 100px;">

                                <option <?php if($result->tipoCelular == 'Residencial'){echo 'selected';} ?> value="Residencial">Residencial</option>
                                <option <?php if($result->tipoCelular == 'Comercial'){echo 'selected';} ?> value="Comercial">Comercial</option>

                                <option <?php if($result->tipoCelular == 'Oi'){echo 'selected';} ?> value="Oi">Oi</option>

                                <option <?php if($result->tipoCelular == 'Tim'){echo 'selected';} ?> value="Tim">Tim</option>

                                <option <?php if($result->tipoCelular == 'Claro'){echo 'selected';} ?> value="Claro">Claro</option>

                                <option <?php if($result->tipoCelular == 'Vivo'){echo 'selected';} ?> value="Vivo">Vivo</option>

                            </select>&nbsp;

                            <input id="celular" type="text" name="celular" value="<?php echo $result->celular; ?>"  />

                        </div>

                    </div>

                    <div class="control-group">

                        <label for="telefone" class="control-label">Telefone</label>

                        <div class="controls">

                            <select id="tipoTelefone" name="tipoTelefone" style="width: 100px;">

                                <option <?php if($result->tipoTelefone == 'Residencial'){echo 'selected';} ?> value="Residencial">Residencial</option>
                                <option <?php if($result->tipoTelefone == 'Comercial'){echo 'selected';} ?> value="Comercial">Comercial</option>
                                <option <?php if($result->tipoTelefone == 'Recado'){echo 'selected';} ?> value="Recado">Recado</option>

                                <option <?php if($result->tipoTelefone == 'Oi'){echo 'selected';} ?> value="Oi">Oi</option>

                                <option <?php if($result->tipoTelefone == 'Tim'){echo 'selected';} ?> value="Tim">Tim</option>

                                <option <?php if($result->tipoTelefone == 'Claro'){echo 'selected';} ?> value="Claro">Claro</option>

                                <option <?php if($result->tipoTelefone == 'Vivo'){echo 'selected';} ?> value="Vivo">Vivo</option>

                            </select>&nbsp;

                            <input id="telefone" type="text" name="telefone" value="<?php echo $result->telefone; ?>"  />
                            <input id="recado" type="text" name="recado" value="<?php echo $result->recado; ?>" style="display: none;"  />

                        </div>

                    </div>

                    <div class="control-group">

                        <label for="email" class="control-label">Email<span class="required">*</span></label>

                        <div class="controls">

                            <input id="email" type="text" name="email" value="<?php echo $result->email; ?>"  />

                        </div>

                    </div>

                    <div class="control-group">

                        <label for="canalEntrada" class="control-label">Canal de Entrada<span class="required">*</span></label>

                        <div class="controls">
                            <select id="canalEntrada" name="canalEntrada">
                                <option <?php if($result->canalEntrada == ''){echo 'selected';} ?> value="">Onde nos conheceu?</option>
                                <option <?php if($result->canalEntrada == 'Base'){echo 'selected';} ?> value="Base">Já sou cliente</option>
                                <option <?php if($result->canalEntrada == 'Direto'){echo 'selected';} ?> value="Direto">Direto</option>
                                <option <?php if($result->canalEntrada == 'Indicação'){echo 'selected';} ?> value="Indicação">Indicação</option>
                                <option <?php if($result->canalEntrada == 'Google'){echo 'selected';} ?> value="Google">Google</option>
                                <option <?php if($result->canalEntrada == 'Facebook'){echo 'selected';} ?> value="Facebook">Facebook</option>
                                <option <?php if($result->canalEntrada == 'Internet'){echo 'selected';} ?> value="Internet">Internet</option>
                                <option <?php if($result->canalEntrada == 'Email'){echo 'selected';} ?> value="Email">Email</option>
                                <option <?php if($result->canalEntrada == 'Panfleto'){echo 'selected';} ?> value="Panfleto">Panfleto</option>
                                <option <?php if($result->canalEntrada == 'Outros'){echo 'selected';} ?> value="Outros">Outros</option>
                            </select>&nbsp;
                            <input id="canalEntradaIndicacao" type="text" name="canalEntradaIndicacao" value="<?php echo $result->canalEntradaIndicacao; ?>" style="display: none;">
                        </div>

                    </div>



                    <div class="control-group" class="control-label">

                        <label for="rua" class="control-label">Rua<span class="required">*</span></label>

                        <div class="controls">

                            <input id="rua" type="text" name="rua" value="<?php echo $result->rua; ?>"  />

                        </div>

                    </div>



                    <div class="control-group">

                        <label for="numero" class="control-label">Número<span class="required">*</span></label>

                        <div class="controls">

                            <input id="numero" type="text" name="numero" value="<?php echo $result->numero; ?>"  />

                        </div>

                    </div>



                    <div class="control-group" class="control-label">

                        <label for="bairro" class="control-label">Bairro<span class="required">*</span></label>

                        <div class="controls">

                            <input id="bairro" type="text" name="bairro" value="<?php echo $result->bairro; ?>"  />

                        </div>

                    </div>



                    <div class="control-group" class="control-label">

                        <label for="cidade" class="control-label">Cidade<span class="required">*</span></label>

                        <div class="controls">

                            <input id="cidade" type="text" name="cidade" value="<?php echo $result->cidade; ?>"  />

                        </div>

                    </div>



                    <div class="control-group" class="control-label">

                        <label for="estado" class="control-label">Estado<span class="required">*</span></label>

                        <div class="controls">

                            <input id="estado" type="text" name="estado" value="<?php echo $result->estado; ?>"  />

                        </div>

                    </div>



                    <div class="control-group" class="control-label">

                        <label for="cep" class="control-label">CEP<span class="required">*</span></label>

                        <div class="controls">

                            <input id="cep" type="text" name="cep" value="<?php echo $result->cep; ?>"  />

                        </div>

                    </div>







                    <div class="form-actions">

                        <div class="span12">

                            <div class="span6 offset3">

                                <button type="submit" class="btn btn-primary"><i class="icon-ok icon-white"></i> Alterar</button>

                                <a href="<?php echo base_url() ?>index.php/clientes" id="" class="btn"><i class="icon-arrow-left"></i> Voltar</a>

                            </div>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>







<script src="<?php echo base_url()?>js/jquery.validate.js"></script>

<script src="<?php echo base_url()?>js/jquery.mask.min.js"></script>

<script type="text/javascript">

    $(document).ready(function(){

        $('#formCliente').validate({

            rules :{

                nomeCliente:{ required: true},

                documento:{ required: true},

                celular:{ required: true},

                email:{ required: true},

                rua:{ required: true},

                numero:{ required: true},

                bairro:{ required: true},

                cidade:{ required: true},

                estado:{ required: true},

                cep:{ required: true}

            },

            messages:{

                nomeCliente :{ required: 'Campo Requerido.'},

                documento :{ required: 'Campo Requerido.'},

                celular:{ required: 'Campo Requerido.'},

                email:{ required: 'Campo Requerido.'},

                rua:{ required: 'Campo Requerido.'},

                numero:{ required: 'Campo Requerido.'},

                bairro:{ required: 'Campo Requerido.'},

                cidade:{ required: 'Campo Requerido.'},

                estado:{ required: 'Campo Requerido.'},

                cep:{ required: 'Campo Requerido.'}

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

        if ( $("#tipoTelefone").val() == "Recado") {
            $( "#recado" ).show();
        } else {
            $( "#recado" ).hide();
        }

        $( "#tipoTelefone" ).change(function() {
            $( "#recado" ).attr('value','');
            if ( $(this).val() == "Recado") {
                $( "#recado" ).show();
            } else {
                $( "#recado" ).hide();
            }
        });

        $("#documento").mask("999.999.999-99");

        var SPMaskBehavior = function (val) {

            return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';

        },

        spOptions = {

            onKeyPress: function(val, e, field, options) {

                field.mask(SPMaskBehavior.apply({}, arguments), options);

            }

        };

        $('#telefone, #celular').mask(SPMaskBehavior, spOptions);

        $("#cep").mask("99999-999");

        if ( $("#canalEntrada").val() == "Indicação" ) {
            $( "#canalEntradaIndicacao" ).show();
        };
        $( "#canalEntrada" ).change(function() {
          if ( $(this).val() != "" && $(this).val() != "Indicação" ) {
            $( "#canalEntradaIndicacao" ).attr('value','').hide();
          } else if ( $(this).val() != "" && $(this).val() == "Indicação" ) {
            $( "#canalEntradaIndicacao" ).show();
          } else {
            $( "#canalEntradaIndicacao" ).hide();
          }
        });



    });

</script>



