<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/maskmoney.js"></script>
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/custom.css" />

<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <h5>Editar Proposta Comercial - <?php echo $result->numero_proposta ?: '#' . $result->idProposta; ?></h5>
            </div>
            <div class="widget-content nopadding">
                <?php if ($custom_error == true) { ?>
                    <div class="span12 alert alert-danger" id="divInfo" style="padding: 1%;">Dados incompletos, verifique os campos obrigatórios.</div>
                <?php } ?>
                <form action="<?php echo current_url(); ?>" method="post" id="formProposta">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" id="csrf_token">
                    <input type="hidden" name="idProposta" value="<?php echo $result->idProposta; ?>" />
                    
                    <!-- Dados Básicos -->
                    <div class="span12" style="padding: 1%; margin-left: 0; border-bottom: 2px solid #ddd; margin-bottom: 15px;">
                        <h4 style="margin-bottom: 15px;">Dados da Proposta</h4>
                        <div class="span12" style="margin-left: 0;">
                            <div class="span6">
                                <label for="cliente">Cliente<span class="required">*</span></label>
                                <input id="cliente" class="span12" type="text" name="cliente" value="<?php echo $result->clientes_id ? ($result->nomeCliente ?? '') : ($result->cliente_nome ?? ''); ?>" />
                                <input id="clientes_id" class="span12" type="hidden" name="clientes_id" value="<?php echo $result->clientes_id ?? ''; ?>" />
                                <small class="help-block">Digite apenas o nome. O cliente pode ser cadastrado depois.</small>
                            </div>
                            <div class="span6">
                                <label for="vendedor">Vendedor/Responsável<span class="required">*</span></label>
                                <input id="vendedor" class="span12" type="text" name="vendedor" value="<?php echo $result->nome; ?>" />
                                <input id="usuarios_id" class="span12" type="hidden" name="usuarios_id" value="<?php echo $result->usuarios_id; ?>" />
                            </div>
                        </div>
                        <div class="span12" style="margin-left: 0; margin-top: 10px;">
                            <div class="span3">
                                <label for="data_proposta">Data da Proposta<span class="required">*</span></label>
                                <input id="data_proposta" autocomplete="off" class="span12 datepicker" type="text" name="data_proposta" value="<?php echo date('d/m/Y', strtotime($result->data_proposta)); ?>" />
                            </div>
                            <div class="span3">
                                <label for="data_validade">Validade</label>
                                <input id="data_validade" autocomplete="off" class="span12 datepicker" type="text" name="data_validade" value="<?php echo $result->data_validade ? date('d/m/Y', strtotime($result->data_validade)) : ''; ?>" placeholder="Opcional" />
                            </div>
                            <div class="span3">
                                <label for="status">Status</label>
                                <select class="span12" name="status" id="status">
                                    <option value="Rascunho" <?= $result->status == 'Rascunho' ? 'selected' : '' ?>>Rascunho</option>
                                    <option value="Enviada" <?= $result->status == 'Enviada' ? 'selected' : '' ?>>Enviada</option>
                                    <option value="Aprovada" <?= $result->status == 'Aprovada' ? 'selected' : '' ?>>Aprovada</option>
                                    <option value="Recusada" <?= $result->status == 'Recusada' ? 'selected' : '' ?>>Recusada</option>
                                    <option value="Convertida" <?= $result->status == 'Convertida' ? 'selected' : '' ?>>Convertida</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Produtos -->
                    <div class="span12" style="padding: 1%; margin-left: 0; border-bottom: 1px solid #ddd; margin-bottom: 15px;">
                        <h4 style="margin-bottom: 15px;">Produtos</h4>
                        <div class="span12 well" style="margin-left: 0; padding: 15px;">
                            <div class="span12" style="margin-left: 0;">
                                <div class="span5">
                                    <label>Produto</label>
                                    <input type="text" class="span12" id="produto" placeholder="Digite o nome do produto" />
                                    <input type="hidden" id="idProduto" />
                                </div>
                                <div class="span2">
                                    <label>Preço</label>
                                    <input type="text" id="preco_produto" name="preco" class="span12 money" placeholder="0,00" />
                                </div>
                                <div class="span2">
                                    <label>Quantidade</label>
                                    <input type="text" id="quantidade_produto" name="quantidade" class="span12" placeholder="1" value="1" />
                                </div>
                                <div class="span3" style="display: flex; align-items: flex-end;">
                                    <button type="button" class="button btn btn-success span12" id="btnAdicionarProduto">
                                        <span class="button__icon"><i class='bx bx-plus-circle'></i></span>
                                        <span class="button__text2">Adicionar</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="span12" style="margin-left: 0;">
                            <table class="table table-bordered" id="tabelaProdutos">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th width="15%">Quantidade</th>
                                        <th width="15%">Preço Unit.</th>
                                        <th width="15%">Subtotal</th>
                                        <th width="10%">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyProdutos">
                                    <!-- Produtos serão adicionados aqui -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" style="text-align: right;"><strong>Total Produtos:</strong></td>
                                        <td><strong id="totalProdutos">R$ 0,00</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <input type="hidden" name="produtos_json" id="produtos_json" value="[]" />
                        </div>
                    </div>

                    <!-- Serviços -->
                    <div class="span12" style="padding: 1%; margin-left: 0; border-bottom: 1px solid #ddd; margin-bottom: 15px;">
                        <h4 style="margin-bottom: 15px;">Serviços</h4>
                        <div class="span12 well" style="margin-left: 0; padding: 15px;">
                            <div class="span12" style="margin-left: 0;">
                                <div class="span5">
                                    <label>Serviço</label>
                                    <input type="text" class="span12" id="servico" placeholder="Digite o nome do serviço" />
                                    <input type="hidden" id="idServico" />
                                </div>
                                <div class="span2">
                                    <label>Preço</label>
                                    <input type="text" id="preco_servico" class="span12 money" placeholder="0,00" />
                                </div>
                                <div class="span2">
                                    <label>Quantidade</label>
                                    <input type="text" id="quantidade_servico" class="span12" placeholder="1" value="1" />
                                </div>
                                <div class="span3" style="display: flex; align-items: flex-end;">
                                    <button type="button" class="button btn btn-success span12" id="btnAdicionarServico">
                                        <span class="button__icon"><i class='bx bx-plus-circle'></i></span>
                                        <span class="button__text2">Adicionar</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="span12" style="margin-left: 0;">
                            <table class="table table-bordered" id="tabelaServicos">
                                <thead>
                                    <tr>
                                        <th>Serviço</th>
                                        <th width="15%">Quantidade</th>
                                        <th width="15%">Preço Unit.</th>
                                        <th width="15%">Subtotal</th>
                                        <th width="10%">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyServicos">
                                    <!-- Serviços serão adicionados aqui -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" style="text-align: right;"><strong>Total Serviços:</strong></td>
                                        <td><strong id="totalServicos">R$ 0,00</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <input type="hidden" name="servicos_json" id="servicos_json" value="[]" />
                        </div>
                    </div>

                    <!-- Outros Produtos/Serviços -->
                    <div class="span12" style="padding: 1%; margin-left: 0; border-bottom: 1px solid #ddd; margin-bottom: 15px;">
                        <h4 style="margin-bottom: 15px;">Outros Produtos/Serviços</h4>
                        <div class="span12 well" style="margin-left: 0; padding: 15px;">
                            <div class="span12" style="margin-left: 0; margin-bottom: 10px;">
                                <label for="descricao_outros">Descrição</label>
                                <textarea id="descricao_outros" name="descricao_outros" class="span12" rows="3" placeholder="Digite produtos ou serviços que não estão cadastrados..."></textarea>
                            </div>
                            <div class="span12" style="margin-left: 0;">
                                <div class="span4">
                                    <label for="preco_outros">Preço</label>
                                    <input type="text" value="0,00" id="preco_outros" name="preco_outros" class="span12 money" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Parcelas -->
                    <div class="span12" style="padding: 1%; margin-left: 0; border-bottom: 1px solid #ddd; margin-bottom: 15px;">
                        <h4 style="margin-bottom: 15px;">Condições de Pagamento</h4>
                        <div class="span12 well" style="margin-left: 0; padding: 15px;">
                            <div class="span8">
                                <label for="geradorParcelas">Gerar Parcelas</label>
                                <input type="text" class="span12" id="geradorParcelas" placeholder="Ex: 30 (30 dias) | 30 60 90 (vencimentos em 30, 60 e 90 dias) | 6x (6 parcelas a cada 30 dias)" />
                                <small style="color: #666; display: block; margin-top: 5px;">
                                    <strong>Exemplos:</strong> • <code>30</code> = 1 parcela em 30 dias • <code>30 60 90</code> = 3 parcelas • <code>6x</code> = 6 parcelas iguais a cada 30 dias
                                </small>
                            </div>
                            <div class="span4" style="display: flex; align-items: flex-end;">
                                <button type="button" class="button btn btn-success span12" id="btnGerarParcelas">
                                    <span class="button__icon"><i class='bx bx-calculator'></i></span>
                                    <span class="button__text2">Gerar</span>
                                </button>
                            </div>
                        </div>
                        <div id="tabelaParcelasContainer" style="display: none;">
                            <div class="span12" style="margin-left: 0;">
                                <table class="table table-bordered" id="tabelaParcelas">
                                    <thead>
                                        <tr>
                                            <th width="5%">Nº</th>
                                            <th width="15%">Dias</th>
                                            <th width="20%">Valor</th>
                                            <th width="50%">Observação</th>
                                            <th width="10%">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyParcelas"></tbody>
                                </table>
                                <input type="hidden" name="parcelas_json" id="parcelas_json" value="[]" />
                            </div>
                        </div>
                    </div>

                    <!-- Observações -->
                    <div class="span12" style="padding: 1%; margin-left: 0; border-bottom: 1px solid #ddd; margin-bottom: 15px;">
                        <h4 style="margin-bottom: 15px;">Observações</h4>
                        <div class="span12" style="margin-left: 0;">
                            <textarea name="observacoes" id="observacoes" class="span12" rows="4" placeholder="Observações adicionais sobre a proposta..."></textarea>
                        </div>
                    </div>

                    <!-- Resumo -->
                    <div class="span12" style="padding: 1%; margin-left: 0; background: #f8f9fa; border-radius: 5px;">
                        <div class="span6" style="margin-left: 0;">
                            <h4>Resumo</h4>
                            <table class="table">
                                <tr>
                                    <td><strong>Total Produtos:</strong></td>
                                    <td><span id="resumoProdutos">R$ 0,00</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Serviços:</strong></td>
                                    <td><span id="resumoServicos">R$ 0,00</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Subtotal:</strong></td>
                                    <td><span id="resumoSubtotal">R$ 0,00</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Desconto:</strong></td>
                                    <td><span id="resumoDesconto">R$ 0,00</span></td>
                                </tr>
                                <tr style="background: #fff; font-size: 18px;">
                                    <td><strong>TOTAL:</strong></td>
                                    <td><strong><span id="resumoTotal">R$ 0,00</span></strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <input type="hidden" name="valor_total" id="valor_total" value="0" />
                    <input type="hidden" name="desconto" id="desconto" value="0" />
                    <input type="hidden" name="valor_desconto" id="valor_desconto" value="0" />
                    <input type="hidden" name="tipo_desconto" id="tipo_desconto" value="" />

                    <!-- Botões -->
                    <div class="span12" style="padding: 1%; margin-left: 0; margin-top: 20px;">
                        <div class="span12" style="display:flex; justify-content: center; gap: 10px;">
                            <button type="submit" class="button btn btn-success">
                                <span class="button__icon"><i class='bx bx-save'></i></span><span class="button__text2">Atualizar Proposta</span>
                            </button>
                            <a href="<?php echo base_url() ?>index.php/propostas" class="button btn btn-mini btn-warning">
                                <span class="button__icon"><i class="bx bx-undo"></i></span><span class="button__text2">Voltar</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Máscaras e datepicker
    $(".datepicker").datepicker({ dateFormat: 'dd/mm/yy' });
    $(".money").maskMoney({
        prefix: '',
        suffix: '',
        decimal: ",",
        thousands: ".",
        allowZero: true,
        allowNegative: false
    });

    // Autocomplete de cliente (opcional - pode digitar livremente)
    $("#cliente").autocomplete({
        source: "<?php echo base_url(); ?>index.php/propostas/autoCompleteCliente",
        minLength: 1,
        select: function(event, ui) {
            $("#clientes_id").val(ui.item.id);
            return true;
        },
        change: function(event, ui) {
            // Se não foi selecionado do autocomplete, limpar o ID
            if (!ui.item) {
                $("#clientes_id").val('');
            }
        }
    });
    
    // Permitir digitação livre
    $("#cliente").on('keyup', function() {
        // Se o usuário está digitando livremente, permitir
        console.log('Cliente digitando: ' + $(this).val());
    });

    $("#vendedor").autocomplete({
        source: "<?php echo base_url(); ?>index.php/propostas/autoCompleteUsuario",
        minLength: 1,
        select: function(event, ui) {
            $("#usuarios_id").val(ui.item.id);
        }
    });

    $("#produto").autocomplete({
        source: "<?php echo base_url(); ?>index.php/propostas/autoCompleteProduto",
        minLength: 2,
        select: function(event, ui) {
            $("#idProduto").val(ui.item.id);
            // Converter preço corretamente (já vem como número do banco)
            var preco = parseFloat(ui.item.preco);
            $("#preco_produto").val(preco.toFixed(2).replace('.', ',')).maskMoney('mask');
            $("#quantidade_produto").focus();
        }
    });

    $("#servico").autocomplete({
        source: "<?php echo base_url(); ?>index.php/propostas/autoCompleteServico",
        minLength: 2,
        select: function(event, ui) {
            $("#idServico").val(ui.item.id);
            // Converter preço corretamente (já vem como número do banco)
            var preco = parseFloat(ui.item.preco);
            $("#preco_servico").val(preco.toFixed(2).replace('.', ',')).maskMoney('mask');
            $("#quantidade_servico").focus();
        }
    });

    // Variáveis globais
    var produtos = [];
    var servicos = [];
    var parcelas = [];
    
    // Carregar produtos existentes (convertendo valores corretamente)
    <?php if (!empty($produtos)) { ?>
        <?php foreach ($produtos as $p) { ?>
            produtos.push({
                produtos_id: <?php echo $p->produtos_id ? $p->produtos_id : 'null'; ?>,
                descricao: "<?php echo addslashes($p->descricao); ?>",
                quantidade: <?php echo floatval($p->quantidade); ?>,
                preco: <?php echo floatval($p->preco); ?>,
                subtotal: <?php echo floatval($p->subtotal); ?>
            });
        <?php } ?>
    <?php } ?>
    
    // Carregar serviços existentes (convertendo valores corretamente)
    <?php if (!empty($servicos)) { ?>
        <?php foreach ($servicos as $s) { ?>
            servicos.push({
                servicos_id: <?php echo $s->servicos_id ? $s->servicos_id : 'null'; ?>,
                descricao: "<?php echo addslashes($s->descricao); ?>",
                quantidade: <?php echo floatval($s->quantidade); ?>,
                preco: <?php echo floatval($s->preco); ?>,
                subtotal: <?php echo floatval($s->subtotal); ?>
            });
        <?php } ?>
    <?php } ?>
    
    // Carregar parcelas existentes
    <?php if (!empty($parcelas)) { ?>
        <?php foreach ($parcelas as $p) { ?>
            parcelas.push({
                numero: <?php echo intval($p->numero); ?>,
                dias: <?php echo intval($p->dias); ?>,
                valor: <?php echo floatval($p->valor); ?>,
                observacao: "<?php echo addslashes($p->observacao); ?>"
            });
        <?php } ?>
    <?php } ?>

    // Carregar dados existentes
    if (produtos.length > 0) {
        atualizarTabelaProdutos();
    }
    if (servicos.length > 0) {
        atualizarTabelaServicos();
    }
    if (parcelas.length > 0) {
        atualizarTabelaParcelas();
    }
    
    // Carregar outros produtos/serviços
    <?php if (!empty($outros) && count($outros) > 0) { ?>
        $("#descricao_outros").val("<?php echo addslashes($outros[0]->descricao); ?>");
        $("#preco_outros").val("<?php echo number_format($outros[0]->preco, 2, ',', '.'); ?>").maskMoney('mask');
    <?php } ?>
    
    // Carregar observações
    $("#observacoes").val("<?php echo addslashes($result->observacoes ?: ''); ?>");
    
    // Carregar valores totais
    $("#valor_total").val("<?php echo $result->valor_total; ?>");
    $("#desconto").val("<?php echo $result->desconto ?: 0; ?>");
    $("#valor_desconto").val("<?php echo $result->valor_desconto ?: 0; ?>");
    $("#tipo_desconto").val("<?php echo $result->tipo_desconto ?: ''; ?>");
    atualizarResumo();

    // Adicionar produto
    $("#btnAdicionarProduto").on('click', function() {
        var produto = $("#produto").val();
        var idProduto = $("#idProduto").val();
        var precoStr = $("#preco_produto").val().replace(/\./g, '').replace(',', '.');
        var preco = parseFloat(precoStr);
        var quantidade = parseFloat($("#quantidade_produto").val()) || 1;

        if (!produto || !precoStr || preco <= 0) {
            Swal.fire({ icon: "error", title: "Atenção", text: "Preencha produto e preço válidos." });
            return;
        }

        // Verificar se produto já existe e atualizar quantidade
        var produtoExistente = produtos.find(function(p) {
            return p.produtos_id == idProduto && idProduto != null && idProduto != '';
        });

        if (produtoExistente) {
            // Atualizar quantidade do produto existente
            produtoExistente.quantidade += quantidade;
            produtoExistente.subtotal = produtoExistente.quantidade * produtoExistente.preco;
        } else {
            // Adicionar novo produto
            produtos.push({
                produtos_id: idProduto || null,
                descricao: produto,
                quantidade: quantidade,
                preco: preco,
                subtotal: quantidade * preco
            });
        }

        atualizarTabelaProdutos();
        limparFormProduto();
    });

    // Adicionar serviço
    $("#btnAdicionarServico").on('click', function() {
        var servico = $("#servico").val();
        var idServico = $("#idServico").val();
        var preco = $("#preco_servico").val().replace(/\./g, '').replace(',', '.');
        var quantidade = parseFloat($("#quantidade_servico").val()) || 1;

        if (!servico || !preco || parseFloat(preco) <= 0) {
            Swal.fire({ icon: "error", title: "Atenção", text: "Preencha serviço e preço válidos." });
            return;
        }

        servicos.push({
            servicos_id: idServico || null,
            descricao: servico,
            quantidade: quantidade,
            preco: parseFloat(preco),
            subtotal: quantidade * parseFloat(preco)
        });

        atualizarTabelaServicos();
        limparFormServico();
    });

    // Atualizar tabelas e resumo
    function atualizarTabelaProdutos() {
        var html = '';
        var total = 0;
        produtos.forEach(function(p, index) {
            total += p.subtotal;
            html += '<tr><td>' + p.descricao + '</td><td>' + p.quantidade + '</td><td>R$ ' + p.preco.toFixed(2).replace('.', ',') + '</td><td>R$ ' + p.subtotal.toFixed(2).replace('.', ',') + '</td><td><a href="#" class="btn-remover-produto" data-index="' + index + '"><i class="bx bx-trash" style="color: #dc3545;"></i></a></td></tr>';
        });
        $("#tbodyProdutos").html(html);
        $("#totalProdutos").text('R$ ' + total.toFixed(2).replace('.', ','));
        $("#produtos_json").val(JSON.stringify(produtos));
        atualizarResumo();
    }

    function atualizarTabelaServicos() {
        var html = '';
        var total = 0;
        servicos.forEach(function(s, index) {
            total += s.subtotal;
            html += '<tr><td>' + s.descricao + '</td><td>' + s.quantidade + '</td><td>R$ ' + s.preco.toFixed(2).replace('.', ',') + '</td><td>R$ ' + s.subtotal.toFixed(2).replace('.', ',') + '</td><td><a href="#" class="btn-remover-servico" data-index="' + index + '"><i class="bx bx-trash" style="color: #dc3545;"></i></a></td></tr>';
        });
        $("#tbodyServicos").html(html);
        $("#totalServicos").text('R$ ' + total.toFixed(2).replace('.', ','));
        $("#servicos_json").val(JSON.stringify(servicos));
        atualizarResumo();
    }

    function atualizarResumo() {
        var totalProdutos = produtos.reduce((sum, p) => sum + p.subtotal, 0);
        var totalServicos = servicos.reduce((sum, s) => sum + s.subtotal, 0);
        
        // Incluir outros produtos/serviços
        var outrosPreco = 0;
        var outrosValor = $("#preco_outros").val();
        if (outrosValor) {
            outrosPreco = parseFloat(outrosValor.replace(/\./g, '').replace(',', '.')) || 0;
        }
        
        var subtotal = totalProdutos + totalServicos + outrosPreco;
        var desconto = parseFloat($("#valor_desconto").val()) || 0;
        var total = subtotal - desconto;

        $("#resumoProdutos").text('R$ ' + totalProdutos.toFixed(2).replace('.', ','));
        $("#resumoServicos").text('R$ ' + totalServicos.toFixed(2).replace('.', ','));
        $("#resumoOutros").text('R$ ' + outrosPreco.toFixed(2).replace('.', ','));
        $("#resumoSubtotal").text('R$ ' + subtotal.toFixed(2).replace('.', ','));
        $("#resumoDesconto").text('R$ ' + desconto.toFixed(2).replace('.', ','));
        $("#resumoTotal").text('R$ ' + total.toFixed(2).replace('.', ','));
        $("#valor_total").val(total.toFixed(2));
        
        // Atualizar valores das parcelas proporcionalmente
        if (parcelas.length > 0) {
            var totalParcelas = parcelas.reduce((sum, p) => sum + parseFloat(p.valor || 0), 0);
            if (totalParcelas > 0) {
                parcelas.forEach(function(p) {
                    p.valor = (parseFloat(p.valor) / totalParcelas) * total;
                });
                atualizarTabelaParcelas();
            }
        }
    }
    
    // Atualizar resumo quando mudar outros produtos/serviços
    $("#preco_outros").on('blur', function() {
        atualizarResumo();
    });

    // Remover itens
    $(document).on('click', '.btn-remover-produto', function(e) {
        e.preventDefault();
        var index = $(this).data('index');
        produtos.splice(index, 1);
        atualizarTabelaProdutos();
    });

    $(document).on('click', '.btn-remover-servico', function(e) {
        e.preventDefault();
        var index = $(this).data('index');
        servicos.splice(index, 1);
        atualizarTabelaServicos();
    });

    function limparFormProduto() {
        $("#produto").val('');
        $("#idProduto").val('');
        $("#preco_produto").val('0,00').maskMoney('mask');
        $("#quantidade_produto").val('1');
    }

    function limparFormServico() {
        $("#servico").val('');
        $("#idServico").val('');
        $("#preco_servico").val('0,00').maskMoney('mask');
        $("#quantidade_servico").val('1');
    }

    // Gerar parcelas (lógica simplificada)
    $("#btnGerarParcelas").on('click', function() {
        var input = $("#geradorParcelas").val().trim();
        if (!input) {
            Swal.fire({ icon: 'warning', title: 'Atenção', text: 'Digite a configuração de parcelas!' });
            return;
        }

        parcelas = [];
        var total = parseFloat($("#valor_total").val()) || 0;
        
        // Lógica simplificada para gerar parcelas (similar à OS)
        if (input.includes('x')) {
            var numParcelas = parseInt(input.replace('x', ''));
            var valorParcela = total / numParcelas;
            for (var i = 1; i <= numParcelas; i++) {
                parcelas.push({
                    numero: i,
                    dias: i * 30,
                    valor: valorParcela.toFixed(2),
                    observacao: ''
                });
            }
        } else {
            var dias = input.split(' ').map(d => parseInt(d));
            var valorParcela = total / dias.length;
            dias.forEach(function(d, index) {
                parcelas.push({
                    numero: index + 1,
                    dias: d,
                    valor: valorParcela.toFixed(2),
                    observacao: ''
                });
            });
        }

        atualizarTabelaParcelas();
    });

    function atualizarTabelaParcelas() {
        var html = '';
        parcelas.forEach(function(p, index) {
            html += '<tr><td>' + p.numero + '</td><td>' + p.dias + '</td><td>R$ ' + parseFloat(p.valor).toFixed(2).replace('.', ',') + '</td><td><input type="text" class="span12 obs-parcela" data-index="' + index + '" value="' + (p.observacao || '') + '" /></td><td><a href="#" class="btn-remover-parcela" data-index="' + index + '"><i class="bx bx-trash" style="color: #dc3545;"></i></a></td></tr>';
        });
        $("#tbodyParcelas").html(html);
        $("#tabelaParcelasContainer").show();
        $("#parcelas_json").val(JSON.stringify(parcelas));
    }

    // Validação do formulário
    $("#formProposta").validate({
        rules: {
            cliente: { required: true },
            usuarios_id: { required: true },
            data_proposta: { required: true }
        },
        messages: {
            cliente: { required: 'Digite o nome do cliente' },
            usuarios_id: { required: 'Selecione um vendedor' },
            data_proposta: { required: 'Data da proposta é obrigatória' }
        }
    });
});
</script>

