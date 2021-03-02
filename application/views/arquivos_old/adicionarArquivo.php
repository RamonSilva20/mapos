<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<link rel="stylesheet" href="<?php echo base_url() ?>assets/trumbowyg/ui/trumbowyg.css">
<script type="text/javascript" src="<?php echo base_url() ?>assets/trumbowyg/trumbowyg.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/trumbowyg/langs/pt_br.js"></script>

<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-hdd"></i>
                </span>
                <h5>Cadastro de Arquivo</h5>
            </div>
            <div class="widget_box_Painel2">
                <?= $custom_error ?>
                <form action="<?= current_url() ?>" id="formArquivo" enctype="multipart/form-data" method="post" class="form-horizontal">

                    <div class="control-group">
                        <label for="preco" class="control-label"><span class="required">Arquivo*</span></label>
                        <div class="controls">
                            <input id="arquivo" type="file" name="userfile" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="nome" class="control-label">Nome do Arquivo*</label>
                        <div class="controls">
                            <input id="nome" type="text" name="nome" />
                        </div>
                    </div>

                    <div class="control-group">
				<label for="descricao" class="control-label">Descrição</label>
                        <div class="span7">
				<textarea name="descricao" id="descricao" class="editor"></textarea>
                  </div></div>

                    <div class="control-group">
                        <label for="descricao" class="control-label">Data</label>
                        <div class="controls">
                            <input id="data" type="text" class="datepicker" name="data" />
                        </div>
                    </div>

                    <div class="form-actions">
                        <div class="span12">
                            <div class="span6 offset3">
                                <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> Adicionar</button>
                                <a href="<?= base_url() ?>index.php/arquivos" class="btn btn-warning"><i class="fas fa-backward"></i> Voltar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url() ?>assets/js/jquery.validate.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#formArquivo').validate({
            rules: {
                nome: {
                    required: true
                },
                userfile: {
                    required: true
                }
            },
            messages: {
                nome: {
                    required: 'Campo Requerido.'
                },
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
        $(".datepicker").datepicker({
            dateFormat: 'dd/mm/yy'
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.editor').trumbowyg({
            lang: 'pt_br'
        });
    });
</script>