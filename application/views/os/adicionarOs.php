<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>

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
                                            <label for="garantia">Garantia (dias)</label>
                                            <input id="garantia" type="number" placeholder="Status s/g inserir nº/0" min="0" max="9999" class="span12" name="garantia" value="" />
                                            <?php echo form_error('garantia'); ?>
                                            <label for="termoGarantia">Termo Garantia</label>
                                            <input id="termoGarantia" class="span12" type="text" name="termoGarantia" value="" />
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
                                    </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="observacoes">
                                            <h4>Observações</h4>
                                        </label>
                                        <textarea class="span12 editor" name="observacoes" id="observacoes" cols="30" rows="5"></textarea>
                                    </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="laudoTecnico">
                                            <h4>Laudo Técnico</h4>
                                        </label>
                                        <textarea class="span12 editor" name="laudoTecnico" id="laudoTecnico" cols="30" rows="5"></textarea>
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
        $(".datepicker").datepicker({
            dateFormat: 'dd/mm/yy'
        });
        $('.editor').trumbowyg({
            lang: 'pt_br',
            semantic: { 'strikethrough': 's', }
        });

        // Modal para cadastro rápido de cliente
        $('#btnCadastrarClienteRapido').on('click', function() {
            $('#modalClienteRapido').modal('show');
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
                nomeCliente: nomeCliente,
                telefone: $('#telefoneRapido').val().trim() || '',
                celular: $('#celularRapido').val().trim() || '',
                email: $('#emailRapido').val().trim() || '',
                rua: $('#ruaRapido').val().trim() || '',
                numero: $('#numeroRapido').val().trim() || '',
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
                    <label for="nomeClienteRapido">Nome do Cliente<span class="required">*</span></label>
                    <input id="nomeClienteRapido" class="span12" type="text" name="nomeCliente" required />
                </div>
            </div>
            <div class="span12" style="padding: 1%; margin-left: 0;">
                <div class="span6">
                    <label for="telefoneRapido">Telefone</label>
                    <input id="telefoneRapido" class="span12" type="text" name="telefone" />
                </div>
                <div class="span6">
                    <label for="celularRapido">Celular</label>
                    <input id="celularRapido" class="span12" type="text" name="celular" />
                </div>
            </div>
            <div class="span12" style="padding: 1%; margin-left: 0;">
                <div class="span12" style="margin-left: 0;">
                    <label for="emailRapido">E-mail</label>
                    <input id="emailRapido" class="span12" type="email" name="email" />
                </div>
            </div>
            <div class="span12" style="padding: 1%; margin-left: 0;">
                <div class="span8">
                    <label for="ruaRapido">Rua</label>
                    <input id="ruaRapido" class="span12" type="text" name="rua" />
                </div>
                <div class="span4">
                    <label for="numeroRapido">Número</label>
                    <input id="numeroRapido" class="span12" type="text" name="numero" />
                </div>
            </div>
            <div class="span12" style="padding: 1%; margin-left: 0;">
                <div class="span4">
                    <label for="bairroRapido">Bairro</label>
                    <input id="bairroRapido" class="span12" type="text" name="bairro" />
                </div>
                <div class="span4">
                    <label for="cidadeRapido">Cidade</label>
                    <input id="cidadeRapido" class="span12" type="text" name="cidade" />
                </div>
                <div class="span2">
                    <label for="estadoRapido">Estado</label>
                    <input id="estadoRapido" class="span12" type="text" name="estado" maxlength="2" placeholder="UF" />
                </div>
                <div class="span2">
                    <label for="cepRapido">CEP</label>
                    <input id="cepRapido" class="span12" type="text" name="cep" />
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
