<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/funcoes.js"></script>

<link rel="stylesheet" href="<?php echo base_url() ?>assets/trumbowyg/ui/trumbowyg.css">
<script type="text/javascript" src="<?php echo base_url() ?>assets/trumbowyg/trumbowyg.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/trumbowyg/langs/pt_br.js"></script>

<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/custom.css" />

<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <h5>Cadastro de OS</h5>
            </div>
            <div class="widget-content nopadding tab-content">
                <div class="span12" id="divProdutosServicos" style=" margin-left: 0">

                    <ul class="nav nav-tabs">
                        <li class="active" id="tabDetalhes"><a href="#tab1" data-toggle="tab">Detalhes da OS</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab1">
                            <div class="span12" id="divCadastrarOs">
                                <?php if ($custom_error == true) { ?>
                                    <div class="span12 alert alert-danger" id="divInfo" style="padding: 1%;">Dados incompletos, verifique os campos com asterisco ou se selecionou corretamente cliente, responsável e garantia.<br />Ou se tem um cliente e um termo de garantia cadastrado.</div>
                                <?php
                                } ?>
                                <form action="<?php echo current_url(); ?>" method="post" id="formOs">
                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" id="csrf_token">
                                    <div class="span12" style="padding: 1%">
                                        <div class="span6">
                                            <label for="cliente">Cliente<span class="required">*</span></label>
                                            <div style="display: flex; gap: 5px; align-items: flex-start;">
                                                <input id="cliente" class="span10" type="text" name="cliente" value="" style="margin-right: 5px;" />
                                                <button type="button" class="btn btn-mini btn-success" id="btnCadastrarClienteRapido" title="Cadastrar Cliente Rápido" style="white-space: nowrap; margin-top: 0;">
                                                    <i class="icon-plus"></i> Novo
                                                </button>
                                            </div>
                                            <input id="clientes_id" class="span12" type="hidden" name="clientes_id" value="" />
                                        </div>
                                        <div class="span6">
                                            <label for="tecnico">Técnico / Responsável<span class="required">*</span></label>
                                            <input id="tecnico" class="span12" type="text" name="tecnico" value="<?= $this->session->userdata('nome_admin'); ?>" />
                                            <input id="usuarios_id" class="span12" type="hidden" name="usuarios_id" value="<?= $this->session->userdata('id_admin'); ?>" />
                                        </div>
                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span3">
                                            <label for="status">Status<span class="required">*</span></label>
                                            <select class="span12" name="status" id="status" value="">
                                                <option value="Aberto">Aberto</option>
                                                <option value="Orçamento">Orçamento</option>
                                                <option value="Negociação">Negociação</option>
                                                <option value="Aprovado">Aprovado</option>
                                                <option value="Aguardando Peças">Aguardando Peças</option>
                                                <option value="Em Andamento">Em Andamento</option>
                                                <option value="Finalizado">Finalizado</option>
                                                <option value="Faturado">Faturado</option>
                                                <option value="Cancelado">Cancelado</option>
                                            </select>
                                        </div>
                                        <div class="span3">
                                            <label for="dataInicial">Data Inicial<span class="required">*</span></label>
                                            <input id="dataInicial" autocomplete="off" class="span12 datepicker" type="text" name="dataInicial" value="<?php echo date('d/m/Y'); ?>" />
                                        </div>
                                        <div class="span3">
                                            <label for="dataFinal">Data Final<span class="required">*</span></label>
                                            <input id="dataFinal" autocomplete="off" class="span12 datepicker" type="text" name="dataFinal" value="" />
                                        </div>
                                        <div class="span3">
                                            <label for="garantiaTipo">Garantia</label>
                                            <select id="garantiaTipo" class="span12" style="font-size: 16px; padding: 8px 12px; height: auto; min-height: 44px; -webkit-appearance: menulist; -moz-appearance: menulist; appearance: menulist; margin-bottom: 8px;">
                                                <option value="">Selecione a garantia</option>
                                                <option value="30">1 Mês (30 dias)</option>
                                                <option value="60">2 Meses (60 dias)</option>
                                                <option value="90">3 Meses (90 dias)</option>
                                                <option value="180">6 Meses (180 dias)</option>
                                                <option value="365">1 Ano (365 dias)</option>
                                                <option value="730">2 Anos (730 dias)</option>
                                                <option value="custom">Personalizado</option>
                                            </select>
                                            <div id="garantiaCustom" style="display: none; margin-top: 8px;">
                                                <div style="display: flex; gap: 5px; align-items: center;">
                                                    <input id="garantiaValor" type="number" placeholder="Valor" min="0" max="9999" class="span12" style="flex: 1; font-size: 16px; padding: 8px 12px;" inputmode="numeric" />
                                                    <select id="garantiaUnidade" class="span12" style="flex: 1; font-size: 16px; padding: 8px 12px; height: auto; min-height: 44px; -webkit-appearance: menulist; -moz-appearance: menulist; appearance: menulist;">
                                                        <option value="dias">Dias</option>
                                                        <option value="meses">Meses</option>
                                                        <option value="anos">Anos</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <input id="garantia" type="hidden" name="garantia" value="" />
                                            <?php echo form_error('garantia'); ?>
                                            <label for="termoGarantia" style="margin-top: 5px;">Termo Garantia</label>
                                            <input id="termoGarantia" class="span12" type="text" name="termoGarantia" value="" placeholder="Termo Garantia" />
                                            <input id="garantias_id" class="span12" type="hidden" name="garantias_id" value="" />
                                        </div>
                                    </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="descricaoProduto">
                                            <h4>Descrição Produto/Serviço</h4>
                                        </label>
                                        <textarea class="span12 editor" name="descricaoProduto" id="descricaoProduto" cols="30" rows="5"></textarea>
                                        <label style="margin-top: 10px;">
                                            <input type="checkbox" name="imprimir_descricao" id="imprimir_descricao" value="1" />
                                            Exibir descrição na impressão
                                        </label>
                                    </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="defeito">
                                            <h4>Defeito</h4>
                                        </label>
                                        <textarea class="span12 editor" name="defeito" id="defeito" cols="30" rows="5"></textarea>
                                        <label style="margin-top: 10px;">
                                            <input type="checkbox" name="imprimir_defeito" id="imprimir_defeito" value="1" />
                                            Exibir defeito na impressão
                                        </label>
                                    </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="observacoes">
                                            <h4>Observações</h4>
                                        </label>
                                        <textarea class="span12 editor" name="observacoes" id="observacoes" cols="30" rows="5"></textarea>
                                        <label style="margin-top: 10px;">
                                            <input type="checkbox" name="imprimir_observacoes" id="imprimir_observacoes" value="1" />
                                            Exibir observações na impressão
                                        </label>
                                    </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="laudoTecnico">
                                            <h4>Laudo Técnico</h4>
                                        </label>
                                        <textarea class="span12 editor" name="laudoTecnico" id="laudoTecnico" cols="30" rows="5"></textarea>
                                        <label style="margin-top: 10px;">
                                            <input type="checkbox" name="imprimir_laudo" id="imprimir_laudo" value="1" />
                                            Exibir laudo técnico na impressão
                                        </label>
                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0; border-top: 1px solid #ddd; margin-top: 15px; padding-top: 15px;">
                                        <h4 style="margin-bottom: 10px;">Forma de Pagamento</h4>
                                        <div class="span4" style="margin-left: 0;">
                                            <label for="formaPgto">Forma de Pagamento</label>
                                            <select class="span12" name="formaPgto" id="formaPgto">
                                                <option value="">Selecione...</option>
                                                <option value="Dinheiro">Dinheiro</option>
                                                <option value="Pix">Pix</option>
                                                <option value="Cartão de Crédito">Cartão de Crédito</option>
                                                <option value="Cartão de Débito">Cartão de Débito</option>
                                                <option value="Boleto">Boleto</option>
                                                <option value="Transferência">Transferência</option>
                                                <option value="Cheque">Cheque</option>
                                            </select>
                                        </div>
                                        <div class="span4">
                                            <label for="parcelas">Parcelas</label>
                                            <select class="span12" name="parcelas" id="parcelas">
                                                <option value="1">À Vista</option>
                                                <option value="2">2x</option>
                                                <option value="3">3x</option>
                                                <option value="4">4x</option>
                                                <option value="5">5x</option>
                                                <option value="6">6x</option>
                                                <option value="7">7x</option>
                                                <option value="8">8x</option>
                                                <option value="9">9x</option>
                                                <option value="10">10x</option>
                                                <option value="11">11x</option>
                                                <option value="12">12x</option>
                                            </select>
                                        </div>
                                        <div class="span4">
                                            <label for="valorEntrada">Entrada (Sinal)</label>
                                            <input type="text" class="span12 money" name="valorEntrada" id="valorEntrada" value="0,00" placeholder="0,00" />
                                            <small style="color: #999; display: block; margin-top: 5px;">Valor da entrada/sinal (opcional)</small>
                                        </div>
                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span12" style="display:flex; justify-content: center;">
                                            <button class="button btn btn-success" id="btnContinuar">
                                              <span class="button__icon"><i class='bx bx-chevrons-right'></i></span><span class="button__text2">Continuar</span></button>
                                            <a href="<?php echo base_url() ?>index.php/os" class="button btn btn-mini btn-warning" style="max-width: 160px">
                                              <span class="button__icon"><i class="bx bx-undo"></i></span><span class="button__text2">Voltar</span></a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                .
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $("#cliente").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteCliente",
            minLength: 1,
            select: function(event, ui) {
                $("#clientes_id").val(ui.item.id);
            }
        });
        $("#tecnico").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteUsuario",
            minLength: 1,
            select: function(event, ui) {
                $("#usuarios_id").val(ui.item.id);
            }
        });
        $("#termoGarantia").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteTermoGarantia",
            minLength: 1,
            select: function(event, ui) {
                $("#garantias_id").val(ui.item.id);
            }
        });

        // Função para converter garantia para dias
        function converterGarantiaParaDias() {
            var tipo = $('#garantiaTipo').val();
            var dias = 0;
            
            if (tipo === 'custom') {
                // Personalizado: converter conforme unidade
                var valor = parseFloat($('#garantiaValor').val()) || 0;
                var unidade = $('#garantiaUnidade').val();
                
                if (valor > 0) {
                    switch(unidade) {
                        case 'dias':
                            dias = Math.round(valor);
                            break;
                        case 'meses':
                            dias = Math.round(valor * 30); // 1 mês = 30 dias
                            break;
                        case 'anos':
                            dias = Math.round(valor * 365); // 1 ano = 365 dias
                            break;
                    }
                }
            } else if (tipo && tipo !== '') {
                // Opção pré-definida: valor já está em dias
                dias = parseInt(tipo);
            }
            
            // Atualizar campo hidden
            $('#garantia').val(dias);
            return dias;
        }
        
        // Mostrar/ocultar campo personalizado
        $('#garantiaTipo').on('change', function() {
            if ($(this).val() === 'custom') {
                $('#garantiaCustom').slideDown(200);
                $('#garantiaValor').focus();
            } else {
                $('#garantiaCustom').slideUp(200);
                $('#garantiaValor').val('');
                converterGarantiaParaDias();
            }
        });
        
        // Atualizar dias quando valor ou unidade mudar
        $('#garantiaValor, #garantiaUnidade').on('input change', function() {
            converterGarantiaParaDias();
        });
        
        // Converter na carga inicial
        converterGarantiaParaDias();

        $("#formOs").validate({
            rules: {
                cliente: {
                    required: true
                },
                tecnico: {
                    required: true
                },
                dataInicial: {
                    required: true
                },
                dataFinal: {
                    required: true
                }

            },
            messages: {
                cliente: {
                    required: 'Campo Requerido.'
                },
                tecnico: {
                    required: 'Campo Requerido.'
                },
                dataInicial: {
                    required: 'Campo Requerido.'
                },
                dataFinal: {
                    required: 'Campo Requerido.'
                }
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

        // Converter garantia antes de enviar o formulário
        $('#formOs').on('submit', function(e) {
            converterGarantiaParaDias();
        });
        $(".datepicker").datepicker({
            dateFormat: 'dd/mm/yy'
        });
        
        // Máscara para campo de entrada
        $('.money').mask('#.##0,00', {reverse: true});
        
        $('.editor').trumbowyg({
            lang: 'pt_br',
            semantic: { 'strikethrough': 's', }
        });

        // Modal para cadastro rápido de cliente
        $('#btnCadastrarClienteRapido').on('click', function() {
            $('#modalClienteRapido').modal('show');
        });

        // Inicializar máscaras quando o modal for aberto
        $('#modalClienteRapido').on('shown', function() {
            // Máscara de telefone e celular
            if ($.fn.mask) {
                $('#telefoneRapido').mask('(00) 0000-0000');
                $('#celularRapido').mask('(00) 00000-0000');
                $('#cepRapido').mask('00000-000');
            }
            // Aplicar máscara dinâmica de CPF/CNPJ (já está no funcoes.js, mas garantir)
            $('#documentoRapido').on('input', function () {
                let v = $(this).val().replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                let result = '';
                // CPF: 11 dígitos numéricos
                if (/^\d{0,11}$/.test(v)) {
                    for (let i = 0; i < v.length && i < 11; i++) {
                        if (i === 3 || i === 6) result += '.';
                        if (i === 9) result += '-';
                        result += v[i];
                    }
                }
                // CNPJ tradicional: 14 dígitos numéricos
                else if (/^\d{12,14}$/.test(v) && !/[A-Z]/.test(v)) {
                    for (let i = 0; i < v.length && i < 14; i++) {
                        if (i === 2 || i === 5) result += '.';
                        if (i === 8) result += '/';
                        if (i === 12) result += '-';
                        result += v[i];
                    }
                }
                // CNPJ alfanumérico: 14 caracteres (letras e números)
                else {
                    for (let i = 0; i < v.length && i < 14; i++) {
                        if (i === 2 || i === 5) result += '.';
                        if (i === 8) result += '/';
                        if (i === 12) result += '-';
                        result += v[i];
                    }
                }
                $(this).val(result);
            });
            // Focar no campo documento
            setTimeout(function() {
                $('#documentoRapido').focus();
            }, 300);
        });

        // Salvar cliente rápido
        $('#formClienteRapido').on('submit', function(e) {
            e.preventDefault();
            
            var nomeCliente = $('#nomeClienteRapido').val().trim();
            
            if (!nomeCliente) {
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'O nome do cliente é obrigatório!'
                    });
                } else {
                    alert('O nome do cliente é obrigatório!');
                }
                return false;
            }

            var dados = {
                documento: $('#documentoRapido').val().trim() || '',
                nomeCliente: nomeCliente,
                telefone: $('#telefoneRapido').val().trim() || '',
                celular: $('#celularRapido').val().trim() || '',
                email: $('#emailRapido').val().trim() || '',
                rua: $('#ruaRapido').val().trim() || '',
                numero: $('#numeroRapido').val().trim() || '',
                complemento: $('#complementoRapido').val().trim() || '',
                bairro: $('#bairroRapido').val().trim() || '',
                cidade: $('#cidadeRapido').val().trim() || '',
                estado: $('#estadoRapido').val().trim() || '',
                cep: $('#cepRapido').val().trim() || ''
            };

            $.ajax({
                url: '<?php echo base_url(); ?>index.php/os/cadastrarClienteRapido',
                type: 'POST',
                data: dados,
                dataType: 'json',
                beforeSend: function() {
                    $('#btnSalvarClienteRapido').prop('disabled', true).html('<i class="icon-spinner icon-spin"></i> Salvando...');
                },
                success: function(response) {
                    if (response.success) {
                        // Atualizar campo de cliente
                        $('#cliente').val(response.cliente.nomeCliente);
                        $('#clientes_id').val(response.cliente.idClientes);
                        
                        // Atualizar token CSRF se fornecido na resposta
                        if (response.csrf_token) {
                            $('#csrf_token').val(response.csrf_token);
                        }
                        
                        // Fechar modal e limpar formulário
                        $('#modalClienteRapido').modal('hide');
                        $('#formClienteRapido')[0].reset();
                        
                        if (typeof Swal !== 'undefined' && Swal.fire) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso!',
                                text: 'Cliente cadastrado com sucesso!',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            alert('Cliente cadastrado com sucesso!');
                        }
                    } else {
                        if (typeof Swal !== 'undefined' && Swal.fire) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: response.message || 'Erro ao cadastrar cliente.'
                            });
                        } else {
                            alert(response.message || 'Erro ao cadastrar cliente.');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    var errorMsg = 'Erro ao comunicar com o servidor.';
                    console.log('Erro AJAX:', xhr, status, error);
                    console.log('Response:', xhr.responseText);
                    
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }
                    } else if (xhr.responseText) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                errorMsg = response.message;
                            }
                        } catch (e) {
                            // Se não for JSON, pode ser HTML de erro
                            if (xhr.status === 403) {
                                errorMsg = 'Você não tem permissão para realizar esta ação.';
                            } else if (xhr.status === 500) {
                                errorMsg = 'Erro interno do servidor. Verifique os logs.';
                            } else if (xhr.status === 0) {
                                errorMsg = 'Erro de conexão. Verifique sua internet.';
                            }
                        }
                    }
                    
                    if (typeof Swal !== 'undefined' && Swal.fire) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: errorMsg
                        });
                    } else {
                        alert(errorMsg);
                    }
                },
                complete: function() {
                    $('#btnSalvarClienteRapido').prop('disabled', false).html('<i class="icon-save"></i> Salvar');
                }
            });
        });

        // Carregar estados no select
        $.getJSON('<?php echo base_url() ?>assets/json/estados.json', function(data) {
            for (i in data.estados) {
                $('#estadoRapido').append(new Option(data.estados[i].nome, data.estados[i].sigla));
            }
        });

        // Função auxiliar para capitalizar
        function capital_letter_rapido(str) {
            if (typeof str === 'undefined') { return; }
            str = str.toLocaleLowerCase().split(" ");
            for (var i = 0, x = str.length; i < x; i++) {
                str[i] = str[i][0].toUpperCase() + str[i].substr(1);
            }
            return str.join(" ");
        }

        // Função para validar CNPJ (copiada de funcoes.js)
        function validarCNPJ_rapido(cnpj) {
            cnpj = cnpj.replace(/[^\w]/g, '').toUpperCase();
            if (/^\d{14}$/.test(cnpj)) {
                if (/^(\d)\1{13}$/.test(cnpj)) {
                    return false;
                }
                let tamanho = cnpj.length - 2;
                let numeros = cnpj.substring(0, tamanho);
                let digitos = cnpj.substring(tamanho);
                let soma = 0;
                let pos = tamanho - 7;
                for (let i = tamanho; i >= 1; i--) {
                    soma += parseInt(numeros.charAt(tamanho - i)) * pos--;
                    if (pos < 2) pos = 9;
                }
                let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
                if (resultado != parseInt(digitos.charAt(0))) {
                    return false;
                }
                tamanho = tamanho + 1;
                numeros = cnpj.substring(0, tamanho);
                soma = 0;
                pos = tamanho - 7;
                for (let i = tamanho; i >= 1; i--) {
                    soma += parseInt(numeros.charAt(tamanho - i)) * pos--;
                    if (pos < 2) pos = 9;
                }
                resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
                return resultado == parseInt(digitos.charAt(1));
            }
            return false;
        }

        // Busca de CNPJ
        $('#buscar_info_cnpj_rapido').on('click', function () {
            var ndocumento = $('#documentoRapido').val().trim();
            if (validarCNPJ_rapido(ndocumento)) {
                var ndocumento_limpo = $('#documentoRapido').val().replace(/\D/g, '');
                var emailAtual = $("#emailRapido").val();
                $("#nomeClienteRapido").val("...");
                if (!emailAtual || emailAtual.trim() === '') {
                    $("#emailRapido").val("...");
                }
                $("#cepRapido").val("...");
                $("#ruaRapido").val("...");
                $("#numeroRapido").val("...");
                $("#bairroRapido").val("...");
                $("#cidadeRapido").val("...");
                $("#estadoRapido").val("...");
                $("#complementoRapido").val("...");
                $("#telefoneRapido").val("...");
                
                $.ajax({
                    url: "https://www.receitaws.com.br/v1/cnpj/" + ndocumento_limpo,
                    dataType: 'jsonp',
                    crossDomain: true,
                    contentType: "text/javascript",
                    success: function (dados) {
                        if (dados.status == "OK") {
                            $("#nomeClienteRapido").val(capital_letter_rapido(dados.nome));
                            $("#cepRapido").val(dados.cep.replace(/\./g, ''));
                            var emailAtual = $("#emailRapido").val() || '';
                            var emailValido = dados.email && dados.email.trim() !== '' && dados.email !== '...' && dados.email.indexOf('@exemplo.com') === -1;
                            var podePreencher = (emailAtual === '' || emailAtual === '...' || !emailAtual) && emailAtual.indexOf('@exemplo.com') === -1;
                            if (emailValido && podePreencher) {
                                $("#emailRapido").val(dados.email.toLocaleLowerCase());
                            } else if (emailAtual === '...') {
                                $("#emailRapido").val('');
                            }
                            $("#telefoneRapido").val(dados.telefone.split("/")[0].replace(/\ /g, ''));
                            $("#ruaRapido").val(capital_letter_rapido(dados.logradouro));
                            $("#numeroRapido").val(dados.numero);
                            $("#bairroRapido").val(capital_letter_rapido(dados.bairro));
                            $("#cidadeRapido").val(capital_letter_rapido(dados.municipio));
                            $("#estadoRapido").val(dados.uf);
                            if (dados.complemento != "") {
                                $("#complementoRapido").val(capital_letter_rapido(dados.complemento));
                            } else {
                                $("#complementoRapido").val("");
                            }
                            $("#nomeClienteRapido").focus();
                        } else {
                            $("#nomeClienteRapido").val("");
                            $("#cepRapido").val("");
                            $("#emailRapido").val("");
                            $("#numeroRapido").val("");
                            $("#complementoRapido").val("");
                            $("#telefoneRapido").val("");
                            if (typeof Swal !== 'undefined' && Swal.fire) {
                                Swal.fire({
                                    icon: "warning",
                                    title: "Atenção",
                                    text: "CNPJ não encontrado."
                                });
                            }
                        }
                    },
                    error: function () {
                        $("#nomeClienteRapido").val("");
                        $("#cepRapido").val("");
                        $("#emailRapido").val("");
                        $("#numeroRapido").val("");
                        $("#complementoRapido").val("");
                        $("#telefoneRapido").val("");
                        if (typeof Swal !== 'undefined' && Swal.fire) {
                            Swal.fire({
                                icon: "warning",
                                title: "Atenção",
                                text: "CNPJ não encontrado."
                            });
                        }
                    },
                    timeout: 2000,
                });
            } else {
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        icon: "warning",
                        title: "Atenção",
                        text: "CNPJ inválido!"
                    });
                }
            }
        });

        // Busca de CEP
        $("#cepRapido").blur(function () {
            var cep = $(this).val().replace(/\D/g, '');
            if (cep != "") {
                var validacep = /^[0-9]{8}$/;
                if (validacep.test(cep)) {
                    $("#ruaRapido").val("...");
                    $("#bairroRapido").val("...");
                    $("#cidadeRapido").val("...");
                    $("#estadoRapido").val("...");
                    
                    $.getJSON("https://viacep.com.br/ws/" + cep.replace(/\./g, '') + "/json/?callback=?", function (dados) {
                        if (!("erro" in dados)) {
                            $("#ruaRapido").val(dados.logradouro);
                            $("#bairroRapido").val(dados.bairro);
                            $("#cidadeRapido").val(dados.localidade);
                            $("#estadoRapido").val(dados.uf);
                        } else {
                            $("#ruaRapido").val("");
                            $("#bairroRapido").val("");
                            $("#cidadeRapido").val("");
                            $("#estadoRapido").val("");
                            if (typeof Swal !== 'undefined' && Swal.fire) {
                                Swal.fire({
                                    icon: "warning",
                                    title: "Atenção",
                                    text: "CEP não encontrado."
                                });
                            }
                        }
                    });
                } else {
                    $("#ruaRapido").val("");
                    $("#bairroRapido").val("");
                    $("#cidadeRapido").val("");
                    $("#estadoRapido").val("");
                    if (typeof Swal !== 'undefined' && Swal.fire) {
                        Swal.fire({
                            icon: "error",
                            title: "Atenção",
                            text: "Formato de CEP inválido."
                        });
                    }
                }
            }
        });
    });
</script>

<!-- Modal para Cadastro Rápido de Cliente -->
<div id="modalClienteRapido" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Cadastrar Cliente Rápido</h3>
    </div>
    <form id="formClienteRapido">
        <div class="modal-body">
            <div class="span12" style="padding: 1%; margin-left: 0;">
                <div class="span12" style="margin-left: 0;">
                    <label for="documentoRapido">CPF/CNPJ</label>
                    <div style="display: flex; gap: 5px; align-items: flex-start;">
                        <input id="documentoRapido" class="span12 cpfcnpj" type="text" name="documento" placeholder="000.000.000-00 ou 00.000.000/0000-00" style="flex: 1;" />
                        <button id="buscar_info_cnpj_rapido" class="btn btn-xs" type="button" style="white-space: nowrap; margin-top: 0;">Buscar(CNPJ)</button>
                    </div>
                </div>
            </div>
            <div class="span12" style="padding: 1%; margin-left: 0;">
                <div class="span12" style="margin-left: 0;">
                    <label for="nomeClienteRapido">Nome/Razão Social<span class="required">*</span></label>
                    <input id="nomeClienteRapido" class="span12" type="text" name="nomeCliente" placeholder="Nome completo ou razão social *" required />
                </div>
            </div>
            <div class="span12" style="padding: 1%; margin-left: 0;">
                <div class="span6">
                    <label for="telefoneRapido">Telefone</label>
                    <input id="telefoneRapido" class="span12" type="text" name="telefone" placeholder="(00) 0000-0000" />
                </div>
                <div class="span6">
                    <label for="celularRapido">Celular</label>
                    <input id="celularRapido" class="span12" type="text" name="celular" placeholder="(00) 00000-0000" />
                </div>
            </div>
            <div class="span12" style="padding: 1%; margin-left: 0;">
                <div class="span12" style="margin-left: 0;">
                    <label for="emailRapido">E-mail</label>
                    <input id="emailRapido" class="span12" type="email" name="email" autocomplete="off" placeholder="email@exemplo.com" />
                </div>
            </div>
            <div class="span12" style="padding: 1%; margin-left: 0;">
                <div class="span12" style="margin-left: 0;">
                    <label for="cepRapido">CEP</label>
                    <input id="cepRapido" class="span12" type="text" name="cep" placeholder="00000-000" />
                </div>
            </div>
            <div class="span12" style="padding: 1%; margin-left: 0;">
                <div class="span8">
                    <label for="ruaRapido">Rua</label>
                    <input id="ruaRapido" class="span12" type="text" name="rua" placeholder="Nome da rua" />
                </div>
                <div class="span4">
                    <label for="numeroRapido">Número</label>
                    <input id="numeroRapido" class="span12" type="text" name="numero" placeholder="Nº" />
                </div>
            </div>
            <div class="span12" style="padding: 1%; margin-left: 0;">
                <div class="span4">
                    <label for="bairroRapido">Bairro</label>
                    <input id="bairroRapido" class="span12" type="text" name="bairro" placeholder="Bairro" />
                </div>
                <div class="span4">
                    <label for="cidadeRapido">Cidade</label>
                    <input id="cidadeRapido" class="span12" type="text" name="cidade" placeholder="Cidade" />
                </div>
                <div class="span2">
                    <label for="estadoRapido">Estado</label>
                    <select id="estadoRapido" name="estado" class="span12">
                        <option value="">Selecione...</option>
                    </select>
                </div>
                <div class="span2">
                    <label for="complementoRapido">Complemento</label>
                    <input id="complementoRapido" class="span12" type="text" name="complemento" placeholder="Apto, Bloco, etc." />
                </div>
            </div>
            <div class="span12" style="padding: 1%; margin-left: 0;">
                <small style="color: #666;">* Campos marcados são obrigatórios. Os demais podem ser preenchidos posteriormente.</small>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button type="submit" class="btn btn-success" id="btnSalvarClienteRapido">
                <i class="icon-save"></i> Salvar
            </button>
        </div>
    </form>
</div>
