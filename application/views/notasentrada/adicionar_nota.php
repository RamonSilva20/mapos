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
                        <li><a href="#tabFila" data-toggle="tab">Consultar Fila</a></li>
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
                                <p>Digite a chave de acesso da nota fiscal (44 caracteres) para buscar na SEFAZ usando certificado digital.</p>
                                <p><small><strong>Nota:</strong> É necessário configurar o certificado digital nas configurações do sistema antes de usar esta funcionalidade.</small></p>
                                
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
                        
                        <!-- Tab Consultar Fila -->
                        <div class="tab-pane" id="tabFila">
                            <div class="span12 well" style="padding: 2%; margin-left: 0; margin-top: 20px;">
                                <h4>Consultar Fila de Notas na SEFAZ</h4>
                                <p>Consulte todas as notas fiscais disponíveis na fila de distribuição da SEFAZ usando seu CNPJ.</p>
                                <p><small><strong>Nota:</strong> É necessário configurar o certificado digital nas configurações do sistema antes de usar esta funcionalidade.</small></p>
                                
                                <form id="formConsultarFila">
                                    <div class="span12" style="margin-left: 0">
                                        <label for="cnpj_fila">CNPJ do Emitente (14 dígitos):</label>
                                        <input type="text" name="cnpj_fila" id="cnpj_fila" 
                                            maxlength="14" minlength="14" 
                                            placeholder="00000000000000" 
                                            required />
                                        <small>Digite apenas números (14 dígitos)</small>
                                    </div>
                                    <div class="span12" style="margin-left: 0; margin-top: 10px;">
                                        <label for="ult_nsu">Último NSU consultado (opcional):</label>
                                        <input type="text" name="ult_nsu" id="ult_nsu" 
                                            placeholder="0" 
                                            value="0" />
                                        <small>Deixe 0 para buscar desde o início, ou informe o último NSU consultado</small>
                                    </div>
                                    <div class="span12" style="margin-left: 0; margin-top: 20px;">
                                        <button type="submit" class="button btn btn-info">
                                            <span class="button__icon"><i class='bx bx-list-ul'></i></span>
                                            <span class="button__text2">Consultar Fila</span>
                                        </button>
                                        <a href="<?= base_url() ?>index.php/notasentrada" class="button btn btn-warning">
                                            <span class="button__icon"><i class='bx bx-undo'></i></span>
                                            <span class="button__text2">Voltar</span>
                                        </a>
                                    </div>
                                </form>
                                
                                <div id="resultadoFila" style="margin-top: 20px; display: none;">
                                    <h5>Notas Encontradas:</h5>
                                    <div id="listaNotas" style="max-height: 400px; overflow-y: auto;">
                                        <!-- Lista de notas será inserida aqui -->
                                    </div>
                                </div>
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
    
    // Formatar CNPJ (apenas números)
    $('#cnpj_fila').on('input', function() {
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
        
        // Consultar Fila
        $('#formConsultarFila').on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var cnpj = $('#cnpj_fila').val().replace(/[^0-9]/g, '');
            var ult_nsu = $('#ult_nsu').val() || '0';
            
            // Garantir que o CNPJ tem 14 dígitos
            if (cnpj.length != 14) {
                Swal.fire({
                    type: 'warning',
                    title: 'Atenção',
                    text: 'O CNPJ deve ter exatamente 14 dígitos.'
                });
                return false;
            }
            
            Swal.fire({
                title: 'Consultando...',
                text: 'Buscando notas na fila da SEFAZ...',
                icon: 'info',
                showConfirmButton: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '<?= base_url() ?>index.php/notasentrada/consultarFila',
                type: 'POST',
                data: { cnpj: cnpj, ult_nsu: ult_nsu },
                dataType: 'json',
                success: function(response) {
                    Swal.close();
                    if (response.result) {
                        if (response.notas && response.notas.length > 0) {
                            var html = '<table class="table table-bordered table-striped">';
                            html += '<thead><tr><th>Chave de Acesso</th><th>Data Emissão</th><th>Valor</th><th>Ações</th></tr></thead>';
                            html += '<tbody>';
                            
                            response.notas.forEach(function(nota) {
                                html += '<tr>';
                                html += '<td>' + nota.chave + '</td>';
                                html += '<td>' + (nota.data_emissao || '-') + '</td>';
                                html += '<td>' + (nota.valor || '-') + '</td>';
                                html += '<td>';
                                html += '<button class="btn btn-mini btn-success baixar-nota" data-chave="' + nota.chave + '">';
                                html += '<i class="bx bx-download"></i> Baixar e Processar';
                                html += '</button>';
                                html += '</td>';
                                html += '</tr>';
                            });
                            
                            html += '</tbody></table>';
                            html += '<p><strong>Total de notas encontradas: ' + response.notas.length + '</strong></p>';
                            if (response.max_nsu) {
                                html += '<p><small>Último NSU: ' + response.max_nsu + '</small></p>';
                            }
                            
                            $('#listaNotas').html(html);
                            $('#resultadoFila').show();
                            
                            // Atualizar último NSU
                            if (response.max_nsu) {
                                $('#ult_nsu').val(response.max_nsu);
                            }
                        } else {
                            Swal.fire({
                                type: 'info',
                                title: 'Nenhuma nota encontrada',
                                text: 'Não há notas disponíveis na fila para este CNPJ.'
                            });
                            $('#resultadoFila').hide();
                        }
                    } else {
                        Swal.fire({
                            type: 'error',
                            title: 'Erro',
                            text: response.message || 'Erro ao consultar fila.'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    var errorMsg = 'Erro ao consultar fila na SEFAZ.';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        errorMsg = response.message || errorMsg;
                    } catch(e) {
                        // Se não conseguir parsear, tentar pegar o texto da resposta
                        if (xhr.responseText) {
                            errorMsg = xhr.responseText.substring(0, 200);
                        }
                    }
                    
                    console.error('Erro na consulta de fila:', xhr);
                    
                    Swal.fire({
                        type: 'error',
                        title: 'Erro',
                        text: errorMsg,
                        confirmButtonText: 'OK'
                    });
                }
            });
            
            return false;
        });
        
        // Baixar e processar nota individual
        $(document).on('click', '.baixar-nota', function() {
            var chave = $(this).data('chave');
            var btn = $(this);
            
            btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Processando...');
            
            $.ajax({
                url: '<?= base_url() ?>index.php/notasentrada/buscarSEFAZ',
                type: 'POST',
                data: { chave_acesso: chave },
                dataType: 'json',
                success: function(response) {
                    if (response.result) {
                        Swal.fire({
                            type: 'success',
                            title: 'Sucesso!',
                            text: response.message || 'Nota processada com sucesso!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        btn.html('<i class="bx bx-check"></i> Processada').removeClass('btn-success').addClass('btn-default');
                    } else {
                        Swal.fire({
                            type: 'error',
                            title: 'Erro',
                            text: response.message || 'Erro ao processar nota.'
                        });
                        btn.prop('disabled', false).html('<i class="bx bx-download"></i> Baixar e Processar');
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'Erro ao processar nota.';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        errorMsg = response.message || errorMsg;
                    } catch(e) {}
                    
                    Swal.fire({
                        type: 'error',
                        title: 'Erro',
                        text: errorMsg
                    });
                    btn.prop('disabled', false).html('<i class="bx bx-download"></i> Baixar e Processar');
                }
            });
        });
    });
});
</script>

