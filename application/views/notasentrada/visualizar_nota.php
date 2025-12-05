<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-file-invoice"></i>
                </span>
                <h5>Visualizar Nota de Entrada - NFe <?= $nota->numero_nf ?></h5>
                <div class="buttons">
                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eNotaEntrada')) { ?>
                        <a href="<?= base_url() ?>index.php/notasentrada/reprocessar/<?= $nota->idNotaEntrada ?>" 
                           class="button btn btn-mini btn-info" 
                           onclick="return confirm('Deseja reprocessar os itens desta nota? Os itens existentes serão removidos e recriados.')">
                            <span class="button__icon"><i class='bx bx-refresh'></i></span>
                            <span class="button__text2">Reprocessar Itens</span>
                        </a>
                    <?php } ?>
                    <a href="<?= base_url() ?>index.php/notasentrada" class="button btn btn-mini btn-warning">
                        <span class="button__icon"><i class='bx bx-undo'></i></span>
                        <span class="button__text2">Voltar</span>
                    </a>
                </div>
            </div>
            <div class="widget-content nopadding">
                <div class="span12" style="padding: 1%; margin-left: 0">
                    <!-- Dados da Nota -->
                    <div class="span12 well" style="margin-left: 0">
                        <h4>Dados da Nota Fiscal</h4>
                        <div class="span6">
                            <p><strong>Número:</strong> <?= $nota->numero_nf ?></p>
                            <p><strong>Série:</strong> <?= $nota->serie_nf ?></p>
                            <p><strong>Chave de Acesso:</strong> <?= $nota->chave_acesso ?></p>
                            <p><strong>Modelo:</strong> <?= $nota->modelo_nf ?></p>
                            <p><strong>Natureza da Operação:</strong> <?= $nota->natureza_operacao ?></p>
                        </div>
                        <div class="span6">
                            <p><strong>Data de Emissão:</strong> <?= date('d/m/Y', strtotime($nota->data_emissao)) ?></p>
                            <p><strong>Data de Entrada:</strong> <?= date('d/m/Y', strtotime($nota->data_entrada)) ?></p>
                            <p><strong>Status:</strong> <?= $nota->status ?></p>
                        </div>
                    </div>
                    
                    <!-- Emitente -->
                    <div class="span12 well" style="margin-left: 0">
                        <h4>Emitente</h4>
                        <p><strong>CNPJ:</strong> <?= $nota->cnpj_emitente ?></p>
                        <p><strong>Nome:</strong> <?= $nota->nome_emitente ?></p>
                    </div>
                    
                    <!-- Destinatário -->
                    <div class="span12 well" style="margin-left: 0">
                        <h4>Destinatário</h4>
                        <p><strong>CNPJ:</strong> <?= $nota->cnpj_destinatario ?></p>
                        <p><strong>Nome:</strong> <?= $nota->nome_destinatario ?></p>
                    </div>
                    
                    <!-- Totais -->
                    <div class="span12 well" style="margin-left: 0">
                        <h4>Totais</h4>
                        <div class="span6">
                            <p><strong>Valor dos Produtos:</strong> R$ <?= number_format($nota->valor_produtos, 2, ',', '.') ?></p>
                            <p><strong>Valor do Frete:</strong> R$ <?= number_format($nota->valor_frete, 2, ',', '.') ?></p>
                            <p><strong>Valor do Desconto:</strong> R$ <?= number_format($nota->valor_desconto, 2, ',', '.') ?></p>
                        </div>
                        <div class="span6">
                            <p><strong>Valor do ICMS:</strong> R$ <?= number_format($nota->valor_icms, 2, ',', '.') ?></p>
                            <p><strong>Valor do IPI:</strong> R$ <?= number_format($nota->valor_ipi, 2, ',', '.') ?></p>
                            <p><strong>Valor Total:</strong> <strong>R$ <?= number_format($nota->valor_total, 2, ',', '.') ?></strong></p>
                        </div>
                    </div>
                    
                    <!-- Itens -->
                    <div class="span12" style="margin-left: 0">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <h4 style="margin: 0;">Itens da Nota</h4>
                            <?php if ($itens && count($itens) > 0 && $this->permission->checkPermission($this->session->userdata('permissao'), 'aProduto')) { ?>
                                <a href="#modal-adicionar-estoque" role="button" data-toggle="modal" class="button btn btn-success">
                                    <span class="button__icon"><i class='bx bx-box'></i></span>
                                    <span class="button__text2">Adicionar ao Estoque</span>
                                </a>
                            <?php } ?>
                        </div>
                        <table class="table table-bordered" id="tabela-itens">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Descrição</th>
                                    <th>NCM</th>
                                    <th>CFOP</th>
                                    <th>Unidade</th>
                                    <th>Quantidade</th>
                                    <th>Valor Unit.</th>
                                    <th>Valor Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($itens) {
                                    foreach ($itens as $item) {
                                        echo '<tr data-item-id="' . $item->idItemNotaEntrada . '">';
                                        echo '<td>' . $item->codigo_produto . '</td>';
                                        echo '<td>' . $item->descricao_produto . '</td>';
                                        echo '<td>' . $item->ncm . '</td>';
                                        echo '<td>' . $item->cfop . '</td>';
                                        echo '<td>' . $item->unidade . '</td>';
                                        echo '<td>' . number_format($item->quantidade, 3, ',', '.') . '</td>';
                                        echo '<td>R$ ' . number_format($item->valor_unitario, 2, ',', '.') . '</td>';
                                        echo '<td>R$ ' . number_format($item->valor_total, 2, ',', '.') . '</td>';
                                        echo '</tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="8">Nenhum item encontrado.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Adicionar ao Estoque -->
<div id="modal-adicionar-estoque" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form id="formAdicionarEstoque" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Adicionar Itens ao Estoque</h3>
        </div>
        <div class="modal-body">
            <div class="span12" style="margin-left: 0">
                <div class="alert alert-info">
                    <strong>Configuração Global:</strong>
                    <div style="margin-top: 10px;">
                        <label>
                            <input type="radio" name="tipo_calculo" value="markup" checked> Usar Markup (%)
                        </label>
                        <label style="margin-left: 20px;">
                            <input type="radio" name="tipo_calculo" value="preco"> Definir Preço de Venda
                        </label>
                    </div>
                    <div style="margin-top: 10px;">
                        <label for="markup_global">Markup (%):</label>
                        <input type="text" id="markup_global" name="markup_global" value="30" class="span2" style="margin-left: 10px;" />
                        <small style="margin-left: 10px;">Ex: 30 = 30% de lucro sobre o preço de compra</small>
                    </div>
                </div>
            </div>
            <div class="span12" style="margin-left: 0; margin-top: 20px;">
                <h5>Itens da Nota:</h5>
                <table class="table table-bordered table-condensed" style="font-size: 12px;">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="25%">Descrição</th>
                            <th width="10%">Qtd</th>
                            <th width="12%">Preço Compra</th>
                            <th width="12%">Markup (%)</th>
                            <th width="12%">Preço Venda</th>
                            <th width="12%">Ação</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-itens-estoque">
                        <?php
                        if ($itens) {
                            foreach ($itens as $index => $item) {
                                $preco_compra = $item->valor_unitario;
                                $markup_padrao = 30;
                                $preco_venda_calculado = $preco_compra * (1 + ($markup_padrao / 100));
                                
                                echo '<tr data-item-id="' . $item->idItemNotaEntrada . '">';
                                echo '<td>' . ($index + 1) . '</td>';
                                echo '<td><small>' . substr($item->descricao_produto, 0, 30) . (strlen($item->descricao_produto) > 30 ? '...' : '') . '</small></td>';
                                echo '<td>' . number_format($item->quantidade, 3, ',', '.') . '</td>';
                                echo '<td>R$ ' . number_format($preco_compra, 2, ',', '.') . '</td>';
                                echo '<td><input type="text" class="span12 markup-item" name="markup[' . $item->idItemNotaEntrada . ']" value="' . $markup_padrao . '" data-preco-compra="' . number_format($preco_compra, 2, '.', '') . '" /></td>';
                                echo '<td><input type="text" class="span12 money preco-venda-item" name="preco_venda[' . $item->idItemNotaEntrada . ']" value="' . number_format($preco_venda_calculado, 2, ',', '.') . '" /></td>';
                                echo '<td><input type="checkbox" name="processar_item[' . $item->idItemNotaEntrada . ']" value="1" checked /></td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <input type="hidden" name="nota_id" value="<?= $nota->idNotaEntrada ?>" />
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btn-adicionar-estoque">Adicionar ao Estoque</button>
        </div>
    </form>
</div>

<script src="<?php echo base_url(); ?>assets/js/maskmoney.js"></script>
<script type="text/javascript">
// Flag para prevenir duplicação
var processandoEstoque = false;

// Função para processar o formulário
function processarAdicionarEstoque() {
    // Prevenir duplicação
    if (processandoEstoque) {
        return false;
    }
    
    var $form = $('#formAdicionarEstoque');
    
    if (!$form.length) {
        alert('Erro: Formulário não encontrado!');
        return false;
    }
    
    // Verificar se há itens selecionados
    var itensSelecionados = $('input[name^="processar_item"]:checked').length;
    if (itensSelecionados === 0) {
        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                type: 'warning',
                title: 'Atenção',
                text: 'Selecione pelo menos um item para processar.'
            });
        } else {
            alert('Selecione pelo menos um item para processar.');
        }
        return false;
    }
    
    // Marcar como processando
    processandoEstoque = true;
    
    var dados = $form.serialize();
    
    if (typeof Swal !== 'undefined' && Swal.fire) {
        Swal.fire({
            title: 'Processando...',
            text: 'Adicionando itens ao estoque...',
            icon: 'info',
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
        });
    }
    
    $.ajax({
        url: '<?= base_url() ?>index.php/notasentrada/adicionarEstoque',
        type: 'POST',
        data: dados,
        dataType: 'json',
        success: function(response) {
            processandoEstoque = false; // Resetar flag
            
            if (response && response.result) {
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        type: 'success',
                        title: 'Sucesso!',
                        text: response.message || 'Itens adicionados ao estoque com sucesso!',
                        confirmButtonText: 'OK'
                    }).then(function() {
                        window.location.reload();
                    });
                } else {
                    alert('Sucesso: ' + (response.message || 'Itens adicionados ao estoque!'));
                    window.location.reload();
                }
            } else {
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        type: 'error',
                        title: 'Erro',
                        text: response.message || 'Erro ao processar os itens.'
                    });
                } else {
                    alert('Erro: ' + (response.message || 'Erro ao processar os itens.'));
                }
            }
        },
        error: function(xhr, status, error) {
            processandoEstoque = false; // Resetar flag em caso de erro
            
            var errorMsg = 'Erro ao processar.';
            try {
                if (xhr.responseText) {
                    var response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                }
            } catch(e) {
                if (xhr.status === 403) {
                    errorMsg = 'Acesso negado. Verifique suas permissões.';
                } else if (xhr.status === 404) {
                    errorMsg = 'Endpoint não encontrado.';
                } else if (xhr.status === 500) {
                    errorMsg = 'Erro interno do servidor.';
                }
            }
            
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({
                    type: 'error',
                    title: 'Erro',
                    text: errorMsg
                });
            } else {
                alert('Erro: ' + errorMsg);
            }
        }
    });
    
    return false;
}

$(document).ready(function() {
    // Inicializar maskMoney nos campos de preço
    $('.money').maskMoney({
        prefix: 'R$ ',
        allowNegative: false,
        thousands: '.',
        decimal: ',',
        affixesStay: true
    });
    
    // Calcular preço de venda quando markup mudar
    $(document).on('keyup change', '.markup-item', function() {
        var markup = parseFloat($(this).val()) || 0;
        var precoCompra = parseFloat($(this).data('preco-compra')) || 0;
        var precoVenda = precoCompra * (1 + (markup / 100));
        var row = $(this).closest('tr');
        var $precoInput = row.find('.preco-venda-item');
        $precoInput.maskMoney('mask', precoVenda);
    });
    
    // Calcular markup quando preço de venda mudar (se tipo for preço)
    $(document).on('keyup change', '.preco-venda-item', function() {
        if ($('input[name="tipo_calculo"]:checked').val() == 'preco') {
            var $input = $(this);
            var precoVenda = parseFloat($input.maskMoney('unmasked')[0]) || 0;
            var precoCompra = parseFloat($input.closest('tr').find('.markup-item').data('preco-compra')) || 0;
            if (precoCompra > 0) {
                var markup = ((precoVenda / precoCompra) - 1) * 100;
                $input.closest('tr').find('.markup-item').val(markup.toFixed(2));
            }
        }
    });
    
    // Mudar tipo de cálculo
    $(document).on('change', 'input[name="tipo_calculo"]', function() {
        if ($(this).val() == 'markup') {
            var markup = parseFloat($('#markup_global').val()) || 0;
            $('.markup-item').each(function() {
                $(this).val(markup);
                $(this).trigger('change');
            });
        }
    });
    
    // Aplicar markup global quando mudar
    $(document).on('keyup change', '#markup_global', function() {
        if ($('input[name="tipo_calculo"]:checked').val() == 'markup') {
            var markup = parseFloat($(this).val()) || 0;
            $('.markup-item').each(function() {
                $(this).val(markup);
                $(this).trigger('change');
            });
        }
    });
    
    // Submeter formulário - usando delegação de eventos (apenas um listener)
    $(document).off('submit', '#formAdicionarEstoque').on('submit', '#formAdicionarEstoque', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        return processarAdicionarEstoque();
    });
    
    // Listener direto no botão - usando delegação de eventos (apenas um listener)
    $(document).off('click', '#btn-adicionar-estoque').on('click', '#btn-adicionar-estoque', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        return processarAdicionarEstoque();
    });
    
    // Inicializar maskMoney quando o modal for aberto
    $('#modal-adicionar-estoque').on('shown', function() {
        $('.money', this).maskMoney({
            prefix: 'R$ ',
            allowNegative: false,
            thousands: '.',
            decimal: ',',
            affixesStay: true
        });
    });
});
</script>

