<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/maskmoney.js"></script>

<style>
    .progress-container {
        background-color: #e9ecef;
        border-radius: 10px;
        height: 30px;
        overflow: hidden;
        margin: 15px 0;
    }
    .progress-bar {
        height: 100%;
        border-radius: 10px;
        transition: width 0.5s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
    }
    .progress-bar.verde { background: linear-gradient(135deg, #28a745, #218838); }
    .progress-bar.amarelo { background: linear-gradient(135deg, #ffc107, #e0a800); color: #333; }
    .progress-bar.vermelho { background: linear-gradient(135deg, #dc3545, #c82333); }
    
    .card-resumo {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .card-resumo h4 {
        margin: 0 0 15px 0;
        color: #333;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
    }
    .card-resumo .valor-grande {
        font-size: 28px;
        font-weight: bold;
    }
    .card-resumo .valor-grande.verde { color: #28a745; }
    .card-resumo .valor-grande.azul { color: #007bff; }
    .card-resumo .valor-grande.vermelho { color: #dc3545; }
    
    .info-linha {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }
    .info-linha:last-child { border-bottom: none; }
    .info-linha .label { color: #666; }
    .info-linha .valor { font-weight: 500; }
    
    .badge-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }
    .badge-status.pago { background: #28a745; color: white; }
    .badge-status.parcial { background: #ffc107; color: #333; }
    .badge-status.pendente { background: #6c757d; color: white; }
    
    .tabela-pagamentos {
        margin-top: 15px;
    }
    .tabela-pagamentos th {
        background: #f8f9fa;
    }
    .btn-excluir-pgto {
        color: #dc3545;
        cursor: pointer;
    }
    .btn-excluir-pgto:hover {
        color: #a71d2a;
    }
</style>

<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon"><i class="fas fa-file-invoice-dollar"></i></span>
                <h5>Detalhes do Lançamento #<?php echo $lancamento->idLancamentos; ?></h5>
            </div>
            <div class="widget-content">
                
                <!-- Resumo do Lançamento -->
                <div class="row-fluid">
                    <div class="span6">
                        <div class="card-resumo">
                            <h4><i class="bx bx-info-circle"></i> Informações do Lançamento</h4>
                            <div class="info-linha">
                                <span class="label">Tipo:</span>
                                <span class="valor">
                                    <?php if($lancamento->tipo == 'receita'): ?>
                                        <span style="color: #28a745;"><i class="bx bx-trending-up"></i> Receita</span>
                                    <?php else: ?>
                                        <span style="color: #dc3545;"><i class="bx bx-trending-down"></i> Despesa</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="info-linha">
                                <span class="label">Cliente/Fornecedor:</span>
                                <span class="valor"><?php echo htmlspecialchars($lancamento->cliente_fornecedor ?: $lancamento->nomeCliente ?: '-'); ?></span>
                            </div>
                            <div class="info-linha">
                                <span class="label">Descrição:</span>
                                <span class="valor"><?php echo htmlspecialchars($lancamento->descricao); ?></span>
                            </div>
                            <div class="info-linha">
                                <span class="label">Data Vencimento:</span>
                                <span class="valor"><?php echo date('d/m/Y', strtotime($lancamento->data_vencimento)); ?></span>
                            </div>
                            <div class="info-linha">
                                <span class="label">Forma de Pagamento:</span>
                                <span class="valor"><?php echo htmlspecialchars($lancamento->forma_pgto ?: 'Não informado'); ?></span>
                            </div>
                            <?php if($lancamento->observacoes): ?>
                            <div class="info-linha">
                                <span class="label">Observações:</span>
                                <span class="valor"><?php echo nl2br(htmlspecialchars($lancamento->observacoes)); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="span6">
                        <div class="card-resumo">
                            <h4><i class="bx bx-dollar"></i> Valores</h4>
                            
                            <div class="info-linha">
                                <span class="label">Valor Total:</span>
                                <span class="valor valor-grande azul">R$ <?php echo number_format($valorTotal, 2, ',', '.'); ?></span>
                            </div>
                            
                            <div class="info-linha">
                                <span class="label">Total Pago:</span>
                                <span class="valor valor-grande verde">R$ <?php echo number_format($totalPago, 2, ',', '.'); ?></span>
                            </div>
                            
                            <div class="info-linha">
                                <span class="label">Saldo Restante:</span>
                                <span class="valor valor-grande <?php echo $saldoRestante > 0 ? 'vermelho' : 'verde'; ?>">
                                    R$ <?php echo number_format($saldoRestante, 2, ',', '.'); ?>
                                </span>
                            </div>
                            
                            <div class="info-linha">
                                <span class="label">Status:</span>
                                <span class="valor">
                                    <?php 
                                    $statusPgto = $lancamento->status_pagamento ?? ($lancamento->baixado == 1 ? 'pago' : 'pendente');
                                    if ($percentualPago >= 100): ?>
                                        <span class="badge-status pago">Pago</span>
                                    <?php elseif ($percentualPago > 0): ?>
                                        <span class="badge-status parcial">Parcial</span>
                                    <?php else: ?>
                                        <span class="badge-status pendente">Pendente</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            
                            <!-- Barra de Progresso -->
                            <div style="margin-top: 20px;">
                                <label>Progresso do Pagamento:</label>
                                <div class="progress-container">
                                    <?php 
                                    $corBarra = 'vermelho';
                                    if ($percentualPago >= 100) $corBarra = 'verde';
                                    elseif ($percentualPago >= 50) $corBarra = 'amarelo';
                                    ?>
                                    <div class="progress-bar <?php echo $corBarra; ?>" style="width: <?php echo $percentualPago; ?>%;">
                                        <?php echo $percentualPago; ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Formulário para Novo Pagamento -->
                <?php if ($saldoRestante > 0 && $this->permission->checkPermission($this->session->userdata('permissao'), 'eLancamento')): ?>
                <div class="card-resumo">
                    <h4><i class="bx bx-plus-circle"></i> Registrar Pagamento</h4>
                    <form id="formPagamento" class="row-fluid">
                        <input type="hidden" name="lancamento_id" value="<?php echo $lancamento->idLancamentos; ?>">
                        
                        <div class="span12" style="margin-bottom: 15px;">
                            <div class="span3" style="margin-left: 0">
                                <label for="valor_pgto">Valor *</label>
                                <input type="text" id="valor_pgto" name="valor" class="span12 money" value="<?php echo number_format($saldoRestante, 2, ',', '.'); ?>" required>
                                <small style="color: #666;">Saldo restante: R$ <?php echo number_format($saldoRestante, 2, ',', '.'); ?></small>
                            </div>
                            
                            <div class="span2">
                                <label for="data_pgto">Data *</label>
                                <input type="text" id="data_pgto" name="data_pagamento" class="span12 datepicker" value="<?php 
                                    // Se o lançamento tem data de vencimento, usar ela; senão usar data atual
                                    echo $lancamento->data_vencimento && $lancamento->data_vencimento != '0000-00-00' 
                                        ? date('d/m/Y', strtotime($lancamento->data_vencimento)) 
                                        : date('d/m/Y'); 
                                ?>" required>
                            </div>
                            
                            <div class="span3">
                                <label for="forma_pgto">Forma de Pagamento</label>
                                <select id="forma_pgto" name="forma_pgto" class="span12">
                                    <option value="">Selecione...</option>
                                    <option value="Dinheiro" <?php echo ($lancamento->forma_pgto == 'Dinheiro') ? 'selected' : ''; ?>>Dinheiro</option>
                                    <option value="Pix" <?php echo (empty($lancamento->forma_pgto) || $lancamento->forma_pgto == 'Pix') ? 'selected' : ''; ?>>Pix</option>
                                    <option value="Cartão de Crédito" <?php echo ($lancamento->forma_pgto == 'Cartão de Crédito') ? 'selected' : ''; ?>>Cartão de Crédito</option>
                                    <option value="Cartão de Débito" <?php echo ($lancamento->forma_pgto == 'Cartão de Débito') ? 'selected' : ''; ?>>Cartão de Débito</option>
                                    <option value="Boleto" <?php echo ($lancamento->forma_pgto == 'Boleto') ? 'selected' : ''; ?>>Boleto</option>
                                    <option value="Transferência" <?php echo ($lancamento->forma_pgto == 'Transferência' || $lancamento->forma_pgto == 'Transferência DOC' || $lancamento->forma_pgto == 'Transferência TED') ? 'selected' : ''; ?>>Transferência</option>
                                    <option value="Cheque" <?php echo ($lancamento->forma_pgto == 'Cheque' || $lancamento->forma_pgto == 'Cheque Pré-datado') ? 'selected' : ''; ?>>Cheque</option>
                                    <option value="Depósito" <?php echo ($lancamento->forma_pgto == 'Depósito') ? 'selected' : ''; ?>>Depósito</option>
                                </select>
                            </div>
                            
                            <div class="span2">
                                <label for="obs_pgto">Observação</label>
                                <input type="text" id="obs_pgto" name="observacao" class="span12" placeholder="Opcional">
                            </div>
                            
                            <div class="span2" style="padding-top: 25px;">
                                <button type="submit" class="btn btn-success btn-block" id="btnAddPgto" style="width: 100%;">
                                    <i class="bx bx-check"></i> Registrar
                                </button>
                            </div>
                        </div>
                        
                        <!-- Botão rápido para pagar total -->
                        <div class="span12" style="margin-top: 10px; text-align: center;">
                            <button type="button" class="btn btn-link" id="btnPagarTotal" style="color: #28a745; text-decoration: none;">
                                <i class="bx bx-check-double"></i> Pagar valor total (R$ <?php echo number_format($saldoRestante, 2, ',', '.'); ?>)
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
                
                <!-- Histórico de Pagamentos -->
                <div class="card-resumo">
                    <h4><i class="bx bx-history"></i> Histórico de Pagamentos</h4>
                    
                    <?php if (empty($pagamentos)): ?>
                        <p style="text-align: center; color: #666; padding: 20px;">
                            <i class="bx bx-info-circle"></i> Nenhum pagamento registrado ainda.
                        </p>
                    <?php else: ?>
                        <table class="table table-bordered tabela-pagamentos">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Data</th>
                                    <th>Valor</th>
                                    <th>Forma</th>
                                    <th>Observação</th>
                                    <th>Registrado por</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagamentos as $pgto): ?>
                                <tr id="pgto-<?php echo $pgto->idPagamento; ?>">
                                    <td><?php echo $pgto->idPagamento; ?></td>
                                    <td><?php echo $pgto->data_pagamento && $pgto->data_pagamento != '0000-00-00' ? date('d/m/Y', strtotime($pgto->data_pagamento)) : '-'; ?></td>
                                    <td><strong style="color: #28a745;">R$ <?php echo number_format($pgto->valor, 2, ',', '.'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($pgto->forma_pgto ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($pgto->observacao ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($pgto->usuario_nome ?: '-'); ?></td>
                                    <td>
                                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eLancamento')): ?>
                                        <a href="javascript:void(0)" class="btn-excluir-pgto" data-id="<?php echo $pgto->idPagamento; ?>" title="Excluir pagamento">
                                            <i class="bx bx-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" style="text-align: right;"><strong>Total:</strong></td>
                                    <td colspan="5"><strong style="color: #28a745;">R$ <?php echo number_format($totalPago, 2, ',', '.'); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    <?php endif; ?>
                </div>
                
                <!-- Botões -->
                <div style="margin-top: 20px; display: flex; gap: 10px;">
                    <a href="<?php echo site_url('financeiro'); ?>" class="btn btn-warning">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                    <?php if ($lancamento->baixado == 1): ?>
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalOpcoesRecibo">
                        <i class="bx bx-printer"></i> Imprimir Recibo
                    </button>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $(".money").maskMoney({
        decimal: ",",
        thousands: ".",
        allowZero: true
    });
    
    $(".datepicker").datepicker({
        dateFormat: 'dd/mm/yy'
    });
    
    // Adicionar pagamento
    $('#formPagamento').on('submit', function(e) {
        e.preventDefault();
        
        var valor = $('#valor_pgto').val().replace(/\./g, '').replace(',', '.');
        var valorNum = parseFloat(valor) || 0;
        var saldoRestante = <?php echo $saldoRestante; ?>;
        
        if (valorNum <= 0) {
            Swal.fire('Erro', 'Informe um valor válido', 'error');
            return;
        }
        
        // Validar se valor não excede saldo restante
        if (valorNum > saldoRestante + 0.01) { // Margem para arredondamento
            Swal.fire({
                icon: 'warning',
                title: 'Valor excede saldo',
                text: 'O valor informado (R$ ' + valorNum.toFixed(2).replace('.', ',') + ') é maior que o saldo restante (R$ ' + saldoRestante.toFixed(2).replace('.', ',') + '). Deseja ajustar para o valor total?',
                showCancelButton: true,
                confirmButtonText: 'Sim, ajustar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $('#valor_pgto').val(saldoRestante.toFixed(2).replace('.', ','));
                    $('#formPagamento').submit();
                }
            });
            return;
        }
        
        // Se forma de pagamento não foi selecionada, usar a do lançamento ou Pix como padrão
        var formaPgto = $('#forma_pgto').val();
        if (!formaPgto) {
            var formaPgtoLancamento = '<?php echo addslashes($lancamento->forma_pgto ?? ''); ?>';
            if (formaPgtoLancamento) {
                formaPgto = formaPgtoLancamento;
                $('#forma_pgto').val(formaPgto);
            } else {
                formaPgto = 'Pix';
                $('#forma_pgto').val(formaPgto);
            }
        }
        
        $('#btnAddPgto').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Registrando...');
        
        $.ajax({
            url: '<?php echo site_url('financeiro/adicionarPagamentoParcial'); ?>',
            type: 'POST',
            data: {
                lancamento_id: $('input[name="lancamento_id"]').val(),
                valor: valorNum.toFixed(2).replace('.', ','),
                data_pagamento: $('#data_pgto').val(),
                forma_pgto: formaPgto,
                observacao: $('#obs_pgto').val() || ''
            },
            dataType: 'json',
            success: function(response) {
                if (response.result) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pagamento registrado!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire('Erro', response.message || 'Erro ao registrar pagamento', 'error');
                    $('#btnAddPgto').prop('disabled', false).html('<i class="bx bx-check"></i> Registrar');
                }
            },
            error: function(xhr) {
                var message = 'Erro ao comunicar com o servidor';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        message = response.message;
                    }
                } catch(e) {
                    if (xhr.responseText) {
                        message = xhr.responseText.substring(0, 200);
                    }
                }
                Swal.fire('Erro', message, 'error');
                $('#btnAddPgto').prop('disabled', false).html('<i class="bx bx-check"></i> Registrar');
            }
        });
    });
    
    // Excluir pagamento
    $(document).on('click', '.btn-excluir-pgto', function() {
        var id = $(this).data('id');
        var $row = $('#pgto-' + id);
        
        Swal.fire({
            title: 'Confirmar exclusão?',
            text: 'Deseja realmente excluir este pagamento?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?php echo site_url('financeiro/excluirPagamentoParcial'); ?>',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.result) {
                            Swal.fire('Sucesso', response.message, 'success').then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Erro', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Erro', 'Erro ao comunicar com o servidor', 'error');
                    }
                });
            }
        });
    });
    
    // Pagar valor total restante - preencher e submeter automaticamente
    $('#btnPagarTotal').on('click', function() {
        var saldoRestante = <?php echo $saldoRestante; ?>;
        $('#valor_pgto').val(saldoRestante.toFixed(2).replace('.', ','));
        
        // Se forma de pagamento não estiver selecionada, usar a do lançamento ou Pix como padrão
        if (!$('#forma_pgto').val()) {
            var formaPgtoLancamento = '<?php echo addslashes($lancamento->forma_pgto ?? ''); ?>';
            if (formaPgtoLancamento) {
                $('#forma_pgto').val(formaPgtoLancamento);
            } else {
                $('#forma_pgto').val('Pix');
            }
        }
        
        // Submeter automaticamente após um pequeno delay para garantir que o valor foi atualizado
        setTimeout(function() {
            $('#formPagamento').submit();
        }, 300);
    });
    
    // Auto-focus no campo de forma de pagamento quando valor for preenchido
    $('#valor_pgto').on('blur', function() {
        var valor = parseFloat($(this).val().replace(/\./g, '').replace(',', '.')) || 0;
        var saldoRestante = <?php echo $saldoRestante; ?>;
        
        // Se o valor digitado for igual ao saldo restante, focar na forma de pagamento
        if (Math.abs(valor - saldoRestante) < 0.01) {
            $('#forma_pgto').focus();
        }
    });
    
    // Atalho de teclado: Enter no campo de forma de pagamento submete o formulário
    $('#forma_pgto').on('keypress', function(e) {
        if (e.which === 13) { // Enter
            e.preventDefault();
            $('#formPagamento').submit();
        }
    });
});
</script>

<!-- Modal Opções de Impressão de Recibo -->
<div id="modalOpcoesRecibo" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modalOpcoesReciboLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="modalOpcoesReciboLabel"><i class="bx bx-printer"></i> Opções de Impressão do Recibo</h3>
    </div>
    <div class="modal-body">
        <div class="span12 alert alert-info" style="margin-left: 0; margin-bottom: 15px;">
            <i class="bx bx-info-circle"></i> Configure o que será exibido no recibo de pagamento.
        </div>
        
        <form id="formOpcoesRecibo">
            <input type="hidden" name="lancamento_id" value="<?php echo $lancamento->idLancamentos; ?>">
            
            <div class="span12" style="margin-left: 0; margin-bottom: 20px;">
                <h4 style="margin-bottom: 15px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
                    <i class="bx bx-cog"></i> Opções de Exibição
                </h4>
                
                <div class="span12" style="margin-left: 0; margin-bottom: 15px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="mostrar_servicos" id="mostrar_servicos" value="1" checked style="margin-right: 10px; width: 18px; height: 18px;">
                        <strong>Mostrar Serviços</strong>
                        <small style="color: #666; margin-left: 10px;">Exibe lista de serviços no recibo</small>
                    </label>
                </div>
                
                <div class="span12" style="margin-left: 0; margin-bottom: 15px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="mostrar_preco_servicos" id="mostrar_preco_servicos" value="1" checked style="margin-right: 10px; width: 18px; height: 18px;">
                        <strong>Mostrar Preço de cada Serviço</strong>
                        <small style="color: #666; margin-left: 10px;">Exibe preço unitário de cada serviço</small>
                    </label>
                </div>
                
                <div class="span12" style="margin-left: 0; margin-bottom: 15px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="mostrar_subtotais" id="mostrar_subtotais" value="1" checked style="margin-right: 10px; width: 18px; height: 18px;">
                        <strong>Mostrar Subtotais</strong>
                        <small style="color: #666; margin-left: 10px;">Exibe subtotal de cada item (quantidade × preço)</small>
                    </label>
                </div>
                
                <div class="span12" style="margin-left: 0; margin-bottom: 15px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="mostrar_detalhes_servicos" id="mostrar_detalhes_servicos" value="1" checked style="margin-right: 10px; width: 18px; height: 18px;">
                        <strong>Mostrar Detalhes dos Serviços</strong>
                        <small style="color: #666; margin-left: 10px;">Exibe informações detalhadas de cada serviço</small>
                    </label>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer" style="display:flex;justify-content: right">
        <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true" style="min-width: 110px">
            <span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span>
        </button>
        <button type="button" class="button btn btn-primary" id="btnImprimirRecibo" style="min-width: 110px">
            <span class="button__icon"><i class='bx bx-printer'></i></span><span class="button__text2">Imprimir</span>
        </button>
    </div>
</div>

<script>
$(document).ready(function() {
    // Imprimir recibo com opções
    $('#btnImprimirRecibo').on('click', function() {
        var opcoes = {
            mostrar_servicos: $('#mostrar_servicos').is(':checked') ? '1' : '0',
            mostrar_preco_servicos: $('#mostrar_preco_servicos').is(':checked') ? '1' : '0',
            mostrar_subtotais: $('#mostrar_subtotais').is(':checked') ? '1' : '0',
            mostrar_detalhes_servicos: $('#mostrar_detalhes_servicos').is(':checked') ? '1' : '0'
        };
        
        var queryString = $.param(opcoes);
        var url = '<?php echo site_url('financeiro/imprimirRecibo/' . $lancamento->idLancamentos); ?>?' + queryString;
        
        window.open(url, '_blank');
        $('#modalOpcoesRecibo').modal('hide');
    });
    
    // Desabilitar opções dependentes se "Mostrar Serviços" estiver desmarcado
    $('#mostrar_servicos').on('change', function() {
        var mostrar = $(this).is(':checked');
        $('#mostrar_preco_servicos, #mostrar_subtotais, #mostrar_detalhes_servicos').prop('disabled', !mostrar);
        if (!mostrar) {
            $('#mostrar_preco_servicos, #mostrar_subtotais, #mostrar_detalhes_servicos').prop('checked', false);
        }
    });
    
    // Inicializar estado dos checkboxes dependentes
    if (!$('#mostrar_servicos').is(':checked')) {
        $('#mostrar_preco_servicos, #mostrar_subtotais, #mostrar_detalhes_servicos').prop('disabled', true);
    }
});
</script>



