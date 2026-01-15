<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<!-- jQuery UI deve ser carregado após jQuery base (que já está no topo.php) -->
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
                <h5>Editar Proposta Comercial - <?php 
                    $numeroProposta = $result->numero_proposta ?: $result->idProposta;
                    $numeroProposta = preg_replace('/[^0-9]/', '', $numeroProposta);
                    if (empty($numeroProposta)) {
                        $numeroProposta = $result->idProposta;
                    }
                    echo $numeroProposta;
                ?></h5>
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
                                    <option value="Em aberto" <?= $result->status == 'Em aberto' ? 'selected' : '' ?>>Em aberto</option>
                                    <option value="Rascunho" <?= $result->status == 'Rascunho' ? 'selected' : '' ?>>Rascunho</option>
                                    <option value="Pendente" <?= $result->status == 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                                    <option value="Aguardando" <?= $result->status == 'Aguardando' ? 'selected' : '' ?>>Aguardando</option>
                                    <option value="Aprovada" <?= $result->status == 'Aprovada' ? 'selected' : '' ?>>Aprovada</option>
                                    <option value="Não aprovada" <?= $result->status == 'Não aprovada' ? 'selected' : '' ?>>Não aprovada</option>
                                    <option value="Concluído" <?= $result->status == 'Concluído' ? 'selected' : '' ?>>Concluído</option>
                                    <option value="Modelo" <?= $result->status == 'Modelo' ? 'selected' : '' ?>>Modelo</option>
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

                    <!-- Outros itens ou serviços -->
                    <div class="span12" style="padding: 1%; margin-left: 0; border-bottom: 1px solid #ddd; margin-bottom: 15px;">
                        <h4 style="margin-bottom: 15px;">Outros itens ou serviços</h4>
                        <div class="span12 well" style="margin-left: 0; padding: 15px;">
                            <div class="span12" style="margin-left: 0; margin-bottom: 10px;">
                                <label for="descricao_outros">Descrição</label>
                                <textarea id="descricao_outros" name="descricao_outros" class="span12" rows="3" placeholder="Digite produtos ou serviços que não estão cadastrados..."></textarea>
                            </div>
                            <div class="span12" style="margin-left: 0;">
                                <div class="span4">
                                    <label for="preco_outros">Total outros</label>
                                    <input type="text" value="0,00" id="preco_outros" name="preco_outros" class="span12 money" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desconto -->
                    <div class="span12" style="padding: 1%; margin-left: 0; border-bottom: 1px solid #ddd; margin-bottom: 15px;">
                        <h4 style="margin-bottom: 15px;">Desconto</h4>
                        <div class="span12" style="margin-left: 0;">
                            <div class="span3">
                                <label for="tipo_desconto">Tipo</label>
                                <select class="span12" name="tipo_desconto" id="tipo_desconto">
                                    <option value="">Nenhum</option>
                                    <option value="percentual">Percentual (%)</option>
                                    <option value="fixo">Valor Fixo (R$)</option>
                                </select>
                            </div>
                            <div class="span3">
                                <label for="desconto">Valor</label>
                                <input type="text" class="span12 money" name="desconto" id="desconto" value="0,00" placeholder="0,00" />
                            </div>
                        </div>
                    </div>

                    <!-- Condições Comerciais -->
                    <div class="span12" style="padding: 1%; margin-left: 0; border-bottom: 1px solid #ddd; margin-bottom: 15px;">
                        <h4 style="margin-bottom: 15px;">Condições Comerciais</h4>
                        <div class="span12" style="margin-left: 0; margin-bottom: 15px;">
                            <div class="span3">
                                <label for="tipo_cond_comerc">Tipo</label>
                                <select class="span12" name="tipo_cond_comerc" id="tipo_cond_comerc">
                                    <option value="N">Nenhuma</option>
                                    <option value="P">Parcelas</option>
                                    <option value="T">Texto livre</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Parcelas -->
                        <div id="slot_cond_comerc_parc" class="hide">
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
                        
                        <!-- Texto Livre -->
                        <div id="slot_cond_comerc_texto" class="hide">
                            <div class="span12" style="margin-left: 0;">
                                <label for="cond_comerc_texto">Condições de Pagamento (Texto Livre)</label>
                                <textarea name="cond_comerc_texto" id="cond_comerc_texto" class="span12" rows="5" placeholder="Digite as condições de pagamento..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Condições Gerais -->
                    <div class="span12" style="padding: 1%; margin-left: 0; border-bottom: 1px solid #ddd; margin-bottom: 15px;">
                        <h4 style="margin-bottom: 15px;">Condições Gerais</h4>
                        <div class="span12" style="margin-left: 0;">
                            <div class="span3">
                                <label for="validade_dias">Validade da Proposta</label>
                                <input type="number" class="span12" name="validade_dias" id="validade_dias" placeholder="Ex: 30" />
                                <small style="color: #666; display: block; margin-top: 5px;">Em dias</small>
                            </div>
                            <div class="span3">
                                <label for="prazo_entrega">Prazo de Entrega</label>
                                <input type="text" class="span12" name="prazo_entrega" id="prazo_entrega" placeholder="Ex: 15 dias úteis" />
                                <small style="color: #666; display: block; margin-top: 5px;">Texto livre</small>
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

    // Controlar tipo de condições comerciais
    $("#tipo_cond_comerc").on('change', function() {
        var tipo = $(this).val();
        $("#slot_cond_comerc_parc").addClass('hide');
        $("#slot_cond_comerc_texto").addClass('hide');
        
        if (tipo == 'P') {
            $("#slot_cond_comerc_parc").removeClass('hide');
        } else if (tipo == 'T') {
            $("#slot_cond_comerc_texto").removeClass('hide');
        }
    });

    // Calcular desconto
    $("#desconto, #tipo_desconto").on('change blur', function() {
        calcularDesconto();
    });

    function calcularDesconto() {
        var tipo = $("#tipo_desconto").val();
        var valorDesconto = $("#desconto").val().replace(/\./g, '').replace(',', '.');
        valorDesconto = parseFloat(valorDesconto) || 0;
        
        var totalProdutos = produtos.reduce((sum, p) => sum + p.subtotal, 0);
        var totalServicos = servicos.reduce((sum, s) => sum + s.subtotal, 0);
        var outrosPreco = 0;
        var outrosValor = $("#preco_outros").val();
        if (outrosValor) {
            outrosPreco = parseFloat(outrosValor.replace(/\./g, '').replace(',', '.')) || 0;
        }
        var subtotal = totalProdutos + totalServicos + outrosPreco;
        
        var valorDescontoCalculado = 0;
        if (tipo == 'percentual' && valorDesconto > 0) {
            valorDescontoCalculado = (subtotal * valorDesconto) / 100;
        } else if (tipo == 'fixo' && valorDesconto > 0) {
            valorDescontoCalculado = valorDesconto;
        }
        
        $("#valor_desconto").val(valorDescontoCalculado.toFixed(2));
        atualizarResumo();
    }

    // Inicializar autocompletes
    function inicializarAutocompletes() {
        // Autocomplete de cliente (opcional - pode digitar livremente)
        if ($("#cliente").length && !$("#cliente").hasClass('ui-autocomplete-input')) {
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
        }
        
        // Permitir digitação livre
        $("#cliente").off('keyup.autocomplete').on('keyup.autocomplete', function() {
            // Se o usuário está digitando livremente, permitir
        });

        if ($("#vendedor").length && !$("#vendedor").hasClass('ui-autocomplete-input')) {
            $("#vendedor").autocomplete({
                source: "<?php echo base_url(); ?>index.php/propostas/autoCompleteUsuario",
                minLength: 1,
                select: function(event, ui) {
                    $("#usuarios_id").val(ui.item.id);
                }
            });
        }

        if ($("#produto").length && !$("#produto").hasClass('ui-autocomplete-input')) {
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
        }

        if ($("#servico").length && !$("#servico").hasClass('ui-autocomplete-input')) {
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
        }
    }
    
    // Inicializar autocompletes imediatamente
    inicializarAutocompletes();

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

    // Aguardar um pouco para garantir que todos os elementos estejam prontos
    setTimeout(function() {
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
            if ($("#descricao_outros").length) {
                $("#descricao_outros").val("<?php echo addslashes($outros[0]->descricao); ?>");
            }
            if ($("#preco_outros").length) {
                $("#preco_outros").val("<?php echo number_format($outros[0]->preco, 2, ',', '.'); ?>").maskMoney('mask');
            }
        <?php } ?>
        
        // Carregar observações
        if ($("#observacoes").length) {
            $("#observacoes").val("<?php echo addslashes($result->observacoes ?: ''); ?>");
        }
        
        // Carregar valores totais
        if ($("#valor_total").length) {
            $("#valor_total").val("<?php echo $result->valor_total; ?>");
        }
        if ($("#desconto").length) {
            $("#desconto").val("<?php echo number_format($result->desconto ?: 0, 2, ',', '.'); ?>").maskMoney('mask');
        }
        if ($("#valor_desconto").length) {
            $("#valor_desconto").val("<?php echo $result->valor_desconto ?: 0; ?>");
        }
        if ($("#tipo_desconto").length) {
            $("#tipo_desconto").val("<?php echo $result->tipo_desconto ?: ''; ?>");
        }
        
        // Carregar condições comerciais
        if ($("#tipo_cond_comerc").length) {
            $("#tipo_cond_comerc").val("<?php echo $result->tipo_cond_comerc ?: 'N'; ?>");
            $("#tipo_cond_comerc").trigger('change');
        }
        if ($("#cond_comerc_texto").length) {
            $("#cond_comerc_texto").val("<?php echo addslashes($result->cond_comerc_texto ?: ''); ?>");
        }
        
        // Se tipo for Parcelas e já existirem parcelas, mostrar tabela
        if ($("#tipo_cond_comerc").length && $("#tipo_cond_comerc").val() == 'P' && parcelas.length > 0) {
            if ($("#tabelaParcelasContainer").length) {
                $("#tabelaParcelasContainer").show();
            }
        }
        
        // Carregar condições gerais
        if ($("#validade_dias").length) {
            $("#validade_dias").val("<?php echo $result->validade_dias ?: ''; ?>");
        }
        if ($("#prazo_entrega").length) {
            $("#prazo_entrega").val("<?php echo addslashes($result->prazo_entrega ?: ''); ?>");
        }
        
        atualizarResumo();
    }, 100);

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
            // Garantir que preco e subtotal são números
            var preco = typeof p.preco === 'number' ? p.preco : parseFloat(p.preco);
            var subtotal = typeof p.subtotal === 'number' ? p.subtotal : parseFloat(p.subtotal);
            var precoFormatado = preco.toFixed(2).replace('.', ',');
            var subtotalFormatado = subtotal.toFixed(2).replace('.', ',');
            html += '<tr><td>' + p.descricao + '</td><td>' + p.quantidade + '</td><td>R$ ' + precoFormatado + '</td><td>R$ ' + subtotalFormatado + '</td><td><a href="#" class="btn-remover-produto" data-index="' + index + '"><i class="bx bx-trash" style="color: #dc3545;"></i></a></td></tr>';
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
            // Garantir que preco e subtotal são números
            var preco = typeof s.preco === 'number' ? s.preco : parseFloat(s.preco);
            var subtotal = typeof s.subtotal === 'number' ? s.subtotal : parseFloat(s.subtotal);
            var precoFormatado = preco.toFixed(2).replace('.', ',');
            var subtotalFormatado = subtotal.toFixed(2).replace('.', ',');
            html += '<tr><td>' + s.descricao + '</td><td>' + s.quantidade + '</td><td>R$ ' + precoFormatado + '</td><td>R$ ' + subtotalFormatado + '</td><td><a href="#" class="btn-remover-servico" data-index="' + index + '"><i class="bx bx-trash" style="color: #dc3545;"></i></a></td></tr>';
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
        if (desconto > subtotal) desconto = subtotal; // Não permitir desconto maior que subtotal
        var total = subtotal - desconto;

        $("#resumoProdutos").text('R$ ' + totalProdutos.toFixed(2).replace('.', ','));
        $("#resumoServicos").text('R$ ' + totalServicos.toFixed(2).replace('.', ','));
        $("#resumoOutros").text('R$ ' + outrosPreco.toFixed(2).replace('.', ','));
        $("#resumoSubtotal").text('R$ ' + subtotal.toFixed(2).replace('.', ','));
        $("#resumoDesconto").text('R$ ' + desconto.toFixed(2).replace('.', ','));
        $("#resumoTotal").text('R$ ' + total.toFixed(2).replace('.', ','));
        $("#valor_total").val(total.toFixed(2));
        
        // Não atualizar parcelas automaticamente - o usuário deve regenerar se necessário
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

        // Calcular o total atual antes de gerar parcelas
        var totalProdutos = produtos.reduce((sum, p) => sum + p.subtotal, 0);
        var totalServicos = servicos.reduce((sum, s) => sum + s.subtotal, 0);
        var outrosPreco = 0;
        var outrosValor = $("#preco_outros").val();
        if (outrosValor) {
            outrosPreco = parseFloat(outrosValor.replace(/\./g, '').replace(',', '.')) || 0;
        }
        var subtotal = totalProdutos + totalServicos + outrosPreco;
        var desconto = parseFloat($("#valor_desconto").val()) || 0;
        if (desconto > subtotal) desconto = subtotal;
        var total = subtotal - desconto;
        
        if (total <= 0) {
            Swal.fire({ icon: 'error', title: 'Atenção', text: 'O total da proposta deve ser maior que zero para gerar parcelas!' });
            return;
        }

        parcelas = [];
        
        // Lógica simplificada para gerar parcelas (similar à OS)
        if (input.includes('x')) {
            var numParcelas = parseInt(input.replace('x', ''));
            if (isNaN(numParcelas) || numParcelas <= 0) {
                Swal.fire({ icon: 'error', title: 'Atenção', text: 'Número de parcelas inválido!' });
                return;
            }
            var valorParcela = total / numParcelas;
            // Ajustar última parcela para compensar arredondamentos
            var somaParcelas = 0;
            for (var i = 1; i <= numParcelas; i++) {
                var valor = (i == numParcelas) ? (total - somaParcelas) : valorParcela;
                somaParcelas += valor;
                // Garantir que valor seja sempre um número, não string
                var valorNumerico = parseFloat(valor.toFixed(2));
                parcelas.push({
                    numero: i,
                    dias: i * 30,
                    valor: valorNumerico, // Número puro, não string
                    observacao: ''
                });
            }
        } else {
            var dias = input.split(' ').map(d => parseInt(d)).filter(d => !isNaN(d) && d > 0);
            if (dias.length == 0) {
                Swal.fire({ icon: 'error', title: 'Atenção', text: 'Digite dias válidos! Ex: 30 ou 30 60 90' });
                return;
            }
            var valorParcela = total / dias.length;
            // Ajustar última parcela para compensar arredondamentos
            var somaParcelas = 0;
            dias.forEach(function(d, index) {
                var valor = (index == dias.length - 1) ? (total - somaParcelas) : valorParcela;
                somaParcelas += valor;
                // Garantir que valor seja sempre um número, não string
                var valorNumerico = parseFloat(valor.toFixed(2));
                parcelas.push({
                    numero: index + 1,
                    dias: d,
                    valor: valorNumerico, // Número puro, não string
                    observacao: ''
                });
            });
        }

        atualizarTabelaParcelas();
    });

    function atualizarTabelaParcelas() {
        var html = '';
        parcelas.forEach(function(p, index) {
            var valor = typeof p.valor === 'number' ? p.valor : parseFloat(p.valor);
            html += '<tr><td>' + p.numero + '</td><td>' + p.dias + '</td><td>R$ ' + valor.toFixed(2).replace('.', ',') + '</td><td><input type="text" class="span12 obs-parcela" data-index="' + index + '" value="' + (p.observacao || '') + '" /></td><td><a href="#" class="btn-remover-parcela" data-index="' + index + '"><i class="bx bx-trash" style="color: #dc3545;"></i></a></td></tr>';
        });
        $("#tbodyParcelas").html(html);
        $("#tabelaParcelasContainer").show();
        $("#parcelas_json").val(JSON.stringify(parcelas));
    }
    
    // Atualizar observação da parcela quando mudar
    $(document).on('blur', '.obs-parcela', function() {
        var index = $(this).data('index');
        if (parcelas[index]) {
            parcelas[index].observacao = $(this).val();
            $("#parcelas_json").val(JSON.stringify(parcelas));
        }
    });
    
    // Remover parcela
    $(document).on('click', '.btn-remover-parcela', function(e) {
        e.preventDefault();
        var index = $(this).data('index');
        parcelas.splice(index, 1);
        // Renumerar parcelas
        parcelas.forEach(function(p, i) {
            p.numero = i + 1;
        });
        atualizarTabelaParcelas();
    });

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

