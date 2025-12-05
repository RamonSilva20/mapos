<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/custom.css" />
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>

<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-file-invoice"></i>
                </span>
                <h5>Adicionar Nota de Entrada</h5>
            </div>
            <div class="widget-content nopadding">
                <div class="span12" style="padding: 1%; margin-left: 0">
                    <ul class="nav nav-tabs" id="tabsNota">
                        <li class="active"><a href="#tabUpload" data-toggle="tab">Upload de XML</a></li>
                        <li><a href="#tabSEFAZ" data-toggle="tab">Buscar na SEFAZ</a></li>
                    </ul>
                    
                    <div class="tab-content">
                        <!-- Tab Upload XML -->
                        <div class="tab-pane active" id="tabUpload">
                            <div class="span12 well" style="padding: 2%; margin-left: 0; margin-top: 20px;">
                                <h4>Upload de Arquivo XML</h4>
                                <p>Faça o upload do arquivo XML da nota fiscal de entrada (NFe).</p>
                                
                                <form id="formUploadXML" enctype="multipart/form-data">
                                    <div class="span12" style="margin-left: 0">
                                        <label for="arquivo_xml">Selecione o arquivo XML:</label>
                                        <input type="file" name="arquivo_xml" id="arquivo_xml" accept=".xml" required />
                                    </div>
                                    <div class="span12" style="margin-left: 0; margin-top: 20px;">
                                        <button type="submit" class="button btn btn-success">
                                            <span class="button__icon"><i class='bx bx-upload'></i></span>
                                            <span class="button__text2">Enviar XML</span>
                                        </button>
                                        <a href="<?= base_url() ?>index.php/notasentrada" class="button btn btn-warning">
                                            <span class="button__icon"><i class='bx bx-undo'></i></span>
                                            <span class="button__text2">Voltar</span>
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Tab Buscar SEFAZ -->
                        <div class="tab-pane" id="tabSEFAZ">
                            <div class="span12 well" style="padding: 2%; margin-left: 0; margin-top: 20px;">
                                <h4>Buscar Nota na SEFAZ</h4>
                                <p>Digite a chave de acesso da nota fiscal (44 caracteres) para buscar na SEFAZ.</p>
                                <p><small><strong>Nota:</strong> Para processar completamente a nota, será necessário fazer o upload do XML.</small></p>
                                
                                <form id="formBuscarSEFAZ">
                                    <div class="span12" style="margin-left: 0">
                                        <label for="chave_acesso">Chave de Acesso (44 caracteres):</label>
                                        <input type="text" name="chave_acesso" id="chave_acesso" 
                                            maxlength="44" minlength="44" 
                                            placeholder="00000000000000000000000000000000000000000000" 
                                            required />
                                        <small>Digite apenas números (44 dígitos)</small>
                                    </div>
                                    <div class="span12" style="margin-left: 0; margin-top: 20px;">
                                        <button type="submit" class="button btn btn-info">
                                            <span class="button__icon"><i class='bx bx-search'></i></span>
                                            <span class="button__text2">Buscar na SEFAZ</span>
                                        </button>
                                        <a href="<?= base_url() ?>index.php/notasentrada" class="button btn btn-warning">
                                            <span class="button__icon"><i class='bx bx-undo'></i></span>
                                            <span class="button__text2">Voltar</span>
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Formatar chave de acesso (apenas números)
    $('#chave_acesso').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    
    // Upload XML
    $('#formUploadXML').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        Swal.fire({
            title: 'Processando...',
            text: 'Enviando e processando XML...',
            icon: 'info',
            showConfirmButton: false,
            allowOutsideClick: false
        });
        
        $.ajax({
            url: '<?= base_url() ?>index.php/notasentrada/uploadXML',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.result) {
                    Swal.fire({
                        type: 'success',
                        title: 'Sucesso!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then(function() {
                        window.location.href = '<?= base_url() ?>index.php/notasentrada/visualizar/' + response.nota_id;
                    });
                } else {
                    Swal.fire({
                        type: 'error',
                        title: 'Erro',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                var errorMsg = 'Erro ao processar XML.';
                try {
                    var response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch(e) {}
                
                Swal.fire({
                    type: 'error',
                    title: 'Erro',
                    text: errorMsg
                });
            }
        });
    });
    
    // Buscar SEFAZ
    $('#formBuscarSEFAZ').on('submit', function(e) {
        e.preventDefault();
        
        var chave = $('#chave_acesso').val();
        
        if (chave.length != 44) {
            Swal.fire({
                type: 'warning',
                title: 'Atenção',
                text: 'A chave de acesso deve ter exatamente 44 caracteres.'
            });
            return;
        }
        
        Swal.fire({
            title: 'Buscando...',
            text: 'Consultando SEFAZ...',
            icon: 'info',
            showConfirmButton: false,
            allowOutsideClick: false
        });
        
        $.ajax({
            url: '<?= base_url() ?>index.php/notasentrada/buscarSEFAZ',
            type: 'POST',
            data: { chave_acesso: chave },
            dataType: 'json',
            success: function(response) {
                if (response.result) {
                    Swal.fire({
                        type: 'info',
                        title: 'Nota Encontrada',
                        text: response.message,
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        type: 'error',
                        title: 'Erro',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                var errorMsg = 'Erro ao buscar nota na SEFAZ.';
                try {
                    var response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch(e) {}
                
                Swal.fire({
                    type: 'error',
                    title: 'Erro',
                    text: errorMsg
                });
            }
        });
    });
});
</script>

