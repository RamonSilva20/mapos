<link rel="stylesheet" href="<?php echo base_url();?>js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url()?>js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="icon-align-justify"></i>
                </span>
                <h5>Cadastro de Histórico</h5>
            </div>
            <div class="widget-content nopadding">
                <?php echo $custom_error; ?>
                <form action="<?php echo current_url(); ?>" id="formHistorico" method="post" >
                    <div class="span12" style="padding: 1%; margin-left: 0">
                        <div class="span4" style="margin-left: 0">
                            <div class="control-group">
                                <label for="responsavel">Quem?<span class="required">*</span></label>
                                <select class="span12" name="responsavel" id="responsavel">
                                    <option value="selecione">Selecione</option>
                                    <option value="Celso Torok">Celso Torok</option>
                                    <option value="José Marques">José Marques</option>
                                    <option value="Rafael Marques">Rafael Marques</option>
                                    <option value="Rosiane Ribeiro">Rosiane Ribeiro</option>
                                    <option value="Shirley Marques">Shirley Marques</option>
                                </select>
                            </div>
                        </div>

                        <div class="span2">
                            <label for="idOs" class="control-label">Nro OS<span class="required">*</span></label>
                            <input type="text" class="span12" name="idOs" id="idOs" value="<?php echo set_value('idOs'); ?>" />
                        </div>
                        <div class="span2">
                            <label for="status">Status<span class="required">*</span></label>
                            <select class="span12" name="status" id="status" value="">
                                <option value="Orçamento">Orçamento</option>
                                <option value="Aguardando">Aguardando Aprovação</option>
                                <option value="Aprovado">Aprovado</option>
                                <option value="Pronto">Pronto</option>
                                <option value="Cancelado">Cancelado</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div class="span2">
                            <label for="canal">Canal<span class="required">*</span></label>
                            <select class="span12" name="canal" id="canal" value="">
                                <option value="selecione">Selecione</option>
                                <option value="Telefone">Telefone</option>
                                <option value="Email">Email</option>
                                <option value="Whatsapp">Whatsapp</option>
                                <option value="Pessoalmente">Pessoalmente</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div class="span2">
                        <div class="control-group">
                            <label for="data" class="control-label">Data do Contato</label>
                            <input id="data" class="span12 datepicker" type="text" name="data" value=""  />
                        </div>
                        </div>
                    </div>

                    <div class="span12" style="padding: 1%; margin-left: 0">
                        <div class="span12">
                            <label for="comentarios">Comentários<span class="required">*</span></label>
                            <textarea class="span12" name="comentarios" id="comentarios" cols="60" rows="5"></textarea>
                            <span></span>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="span12">
                            <div class="span6 offset3">
                                <button type="submit" class="btn btn-success"><i class="icon-plus icon-white"></i> Adicionar</button>
                                <a href="<?php echo base_url() ?>index.php/historico" id="btnAdicionar" class="btn"><i class="icon-arrow-left"></i> Voltar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url()?>js/jquery.validate.js"></script>
<script src="<?php echo base_url();?>js/maskmoney.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $(".money").maskMoney();
        $('#formHistorico').validate({
            rules :{
                responsavel:{ required: true},
                data:{ required: true},
                status:{ required: true},
                comentarios:{ required: true}
            },
            messages:{
                responsavel :{ required: 'Campo Requerido.'},
                data :{ required: 'Campo Requerido.'},
                status :{ required: 'Campo Requerido.'},
                comentarios :{ required: 'Campo Requerido.'}
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

        //contador de caracteres
        var text_max = 255;

        $('textarea').on({
            focus: function() {
                var text_length = $(this).val().length;
                var text_remaining = text_max - text_length;
                $(this).next().html(text_remaining + ' caracteres restantes.');
            },
            keyup: function() {
                var text_length = $(this).val().length;
                var text_remaining = text_max - text_length;
                $(this).next().html(text_remaining + ' caracteres restantes.');
            }
        });
    });
    $(".datepicker" ).datepicker({ dateFormat: 'dd/mm/yy' });
</script>