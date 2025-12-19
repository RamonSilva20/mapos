<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-tags"></i>
                </span>
                <h5>Editar Categoria</h5>
            </div>
            <?php if ($custom_error != '') {
                echo '<div class="alert alert-danger">' . $custom_error . '</div>';
            } ?>
            <form action="<?php echo current_url(); ?>" id="formCategoria" method="post" class="form-horizontal">
                <input type="hidden" name="idCategorias" value="<?php echo $result->idCategorias; ?>" />
                <div class="widget-content nopadding tab-content">
                    <div class="span6">
                        <div class="control-group">
                            <label for="categoria" class="control-label">Nome da Categoria<span class="required">*</span></label>
                            <div class="controls">
                                <input id="categoria" type="text" name="categoria" value="<?php echo $result->categoria; ?>" placeholder="Ex: Vendas, Compras, Salários..." />
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="tipo" class="control-label">Tipo<span class="required">*</span></label>
                            <div class="controls">
                                <select id="tipo" name="tipo">
                                    <option value="">Selecione...</option>
                                    <option value="receita" <?= $result->tipo == 'receita' ? 'selected' : '' ?>>Receita</option>
                                    <option value="despesa" <?= $result->tipo == 'despesa' ? 'selected' : '' ?>>Despesa</option>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="status" class="control-label">Status</label>
                            <div class="controls">
                                <label for="status" class="btn btn-default">Ativo
                                    <input type="checkbox" id="status" name="status" class="badgebox" value="1" <?= $result->status == 1 ? 'checked' : '' ?>>
                                    <span class="badge">&check;</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <div class="span12">
                        <div class="span6 offset3" style="display:flex;justify-content: center">
                            <button type="submit" class="button btn btn-mini btn-success"><span class="button__icon"><i class='bx bx-save'></i></span> <span class="button__text2">Salvar</span></button>
                            <a title="Voltar" class="button btn btn-warning" href="<?php echo site_url() ?>/categorias"><span class="button__icon"><i class="bx bx-undo"></i></span> <span class="button__text2">Voltar</span></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $("#categoria").focus();
        
        $('#formCategoria').validate({
            rules: {
                categoria: {
                    required: true
                },
                tipo: {
                    required: true
                },
            },
            messages: {
                categoria: {
                    required: 'Campo Requerido.'
                },
                tipo: {
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
            }
        });
    });
</script>


