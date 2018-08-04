<link rel="stylesheet" href="<?php echo base_url();?>js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url()?>js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="icon-align-justify"></i>
                </span>
                <h5>Cadastro de Compras</h5>
            </div>
            <div class="widget-content nopadding">
                <?php echo $custom_error; ?>
                <form action="<?php echo current_url(); ?>" id="formCompra" method="post" >
                    <div class="span12" style="padding: 1%; margin-left: 0">
                        <div class="span4" style="margin-left: 0">
                        <div class="control-group">
                            <label for="solicitante">Solicitante<span class="required">*</span></label>
                            <select class="span12" name="solicitante" id="solicitante">
                                <option value="selecione">Selecione</option>
                                <option value="Alan Silveira">Alan Silveira</option>
                                <option value="Celso Torok">Celso Torok</option>
                                <option value="José Marques">José Marques</option>
                                <option value="Rafael Marques">Rafael Marques</option>
                                <option value="Rosiane Ribeiro">Rosiane Ribeiro</option>
                                <option value="Shirley Marques">Shirley Marques</option>
                            </select>
                        </div>
                        </div>
                        <div class="span4">
                        <div class="control-group">
                            <label for="comprador">Comprador</label>
                            <select class="span12" name="comprador" id="comprador">
                                <option value="selecione">Selecione</option>
                                <option value="Alan Silveira">Alan Silveira</option>
                                <option value="Celso Torok">Celso Torok</option>
                                <option value="José Marques">José Marques</option>
                                <option value="Rafael Marques">Rafael Marques</option>
                                <option value="Rosiane Ribeiro">Rosiane Ribeiro</option>
                                <option value="Shirley Marques">Shirley Marques</option>
                            </select>
                        </div>
                        </div>
                        <div class="span4">
                        <div class="control-group">
                            <label for="fornecedor">Fornecedor</label>
                            <input type="text" class="span12" name="fornecedor" id="fornecedor" placeholder="Digite o nome do fornecedor" value="<?php echo set_value('fornecedor'); ?>" />
                        </div>
                        </div>
                    </div>

                    <div class="span12" style="padding: 1%; margin-left: 0">
                        <div class="span2">
                            <label for="idOs" class="control-label">Nro OS<span class="required">*</span></label>
                            <input type="text" class="span12" name="idOs" id="idOs" value="<?php echo set_value('idOs'); ?>" />
                        </div>
                        <div class="span6">
                            <label for="descricao" class="control-label">Descrição<span class="required">*</span></label>
                            <input type="text" class="span12" name="descricao" id="descricao" placeholder="Digite o nome do produto" value="<?php echo set_value('descricao'); ?>" />
                        </div>
                        <div class="span4">
                            <label for="preco" class="control-label">Preço</label>
                            <input id="valor" class="money" type="text" name="valor" value="<?php echo set_value('valor'); ?>"  />
                        </div>
                    </div>

                    <div class="span12" style="padding: 1%; margin-left: 0">
                        <div class="span3">
                            <label for="status">Status<span class="required">*</span></label>
                            <select class="span12" name="status" id="status" value="">
                                <option value="Orçamento">Orçamento</option>
                                <option value="Pedido Efetuado">Pedido Efetuado</option>
                                <option value="Faturado">Faturado</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>
                        <div class="span3">
                        <div class="control-group">
                            <label for="dataPedido" class="control-label">Data do Pedido</label>
                            <input id="dataPedido" class="span12 datepicker" type="text" name="dataPedido" value=""  />
                        </div>
                        </div>
                        <div class="span3">
                        <div class="control-group">
                            <label for="dataPrevista" class="control-label">Data Prevista</label>
                            <input id="dataPrevista" class="span12 datepicker" type="text" name="dataPrevista" value=""  />
                        </div>
                        </div>
                        <div class="span3">
                        <div class="control-group">
                            <label for="rastreio" class="control-label">Rastreio</label>
                            <input id="rastreio" class="span12" type="text" name="rastreio" value=""  />
                        </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="span12">
                            <div class="span6 offset3">
                                <button type="submit" class="btn btn-success"><i class="icon-plus icon-white"></i> Adicionar</button>
                                <a href="<?php echo base_url() ?>index.php/compras" id="btnAdicionar" class="btn"><i class="icon-arrow-left"></i> Voltar</a>
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
        $('#formCompra').validate({
            rules :{
                solicitante:{ required: true},
                comprador:{ required: true},
                descricao:{ required: true},
                valor:{ required: true}
            },
            messages:{
                solicitante :{ required: 'Campo Requerido.'},
                comprador :{ required: 'Campo Requerido.'},
                descricao :{ required: 'Campo Requerido.'},
                valor :{ required: 'Campo Requerido.'}
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
    });
    $(".datepicker" ).datepicker({ dateFormat: 'dd/mm/yy' });
</script>