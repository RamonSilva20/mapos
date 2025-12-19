<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<link rel="stylesheet" href="<?php echo base_url() ?>assets/trumbowyg/ui/trumbowyg.css">
<script type="text/javascript" src="<?php echo base_url() ?>assets/trumbowyg/trumbowyg.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/trumbowyg/langs/pt_br.js"></script>

<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/custom.css" />

<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon"><i class="fas fa-diagnoses"></i></span>
                <h5>Editar Ordem de Serviço</h5>
                <div class="buttons">
                    <?php if ($result->faturado == 0) { ?>
                        <a href="#modal-faturar" id="btn-faturar" role="button" data-toggle="modal" class="button btn btn-mini btn-danger">
                            <span class="button__icon"><i class='bx bx-dollar'></i></span> <span class="button__text">Faturar</span>
                        </a>
                    <?php } ?>
                    <a title="Visualizar OS" class="button btn btn-primary" href="<?php echo site_url() ?>/os/visualizar/<?php echo $result->idOs; ?>">
                        <span class="button__icon"><i class="bx bx-show"></i></span><span class="button__text">Visualizar OS</span>
                    </a>
                    <div class="button-container">
                        <a target="_blank" title="Imprimir Ordem de Serviço" class="button btn btn-mini btn-inverse">
                            <span class="button__icon"><i class="bx bx-printer"></i></span><span class="button__text">Imprimir</span>
                        </a>
                        <div class="cascading-buttons">
                            <a target="_blank" title="Impressão em Papel A4" class="button btn btn-mini btn-inverse" href="<?php echo site_url() ?>/os/imprimir/<?php echo $result->idOs; ?>">
                                <span class="button__icon"><i class='bx bx-file'></i></span> <span class="button__text">Papel A4</span>
                            </a>
                            <a target="_blank" title="Impressão Cupom Não Fical" class="button btn btn-mini btn-inverse" href="<?php echo site_url() ?>/os/imprimirTermica/<?php echo $result->idOs; ?>">
                                <span class="button__icon"><i class='bx bx-receipt'></i></span> <span class="button__text">Cupom 80mm</span>
                            </a>
                            <?php if ($result->garantias_id) { ?>
                                <a target="_blank" title="Imprimir Termo de Garantia" class="button btn btn-mini btn-inverse" href="<?php echo site_url() ?>/garantias/imprimirGarantiaOs/<?php echo $result->garantias_id; ?>">
                                    <span class="button__icon"><i class="bx bx-paperclip"></i></span> <span class="button__text">Termo Garantia</span>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
                        $this->load->model('os_model');
                        $zapnumber = preg_replace("/[^0-9]/", "", $result->celular_cliente);
                        $troca = [$result->nomeCliente, $result->idOs, $result->status, 'R$ ' . ($result->desconto != 0 && $result->valor_desconto != 0 ? number_format($result->valor_desconto, 2, ',', '.') : number_format($totalProdutos + $totalServico, 2, ',', '.')), strip_tags($result->descricaoProduto), ($emitente ? $emitente->nome : ''), ($emitente ? $emitente->telefone : ''), strip_tags($result->observacoes), strip_tags($result->defeito), strip_tags($result->laudoTecnico), date('d/m/Y', strtotime($result->dataFinal)), date('d/m/Y', strtotime($result->dataInicial)), $result->garantia . ' dias'];
                        $texto_de_notificacao = $this->os_model->criarTextoWhats($texto_de_notificacao, $troca);
                        if (!empty($zapnumber)) {
                            echo '<a title="Via WhatsApp" class="button btn btn-mini btn-success" id="enviarWhatsApp" target="_blank" href="https://wa.me/send?phone=55' . $zapnumber . '&text=' . $texto_de_notificacao . '" ' . ($zapnumber == '' ? 'disabled' : '') . '>
                                <span class="button__icon"><i class="bx bxl-whatsapp"></i></span> <span class="button__text">WhatsApp</span>
                            </a>';
                        }
                    } ?>
                    <a title="Enviar por E-mail" class="button btn btn-mini btn-warning" href="<?php echo site_url() ?>/os/enviar_email/<?php echo $result->idOs; ?>">
                        <span class="button__icon"><i class="bx bx-envelope"></i></span> <span class="button__text">Via E-mail</span>
                    </a>
                </div>
            </div>
            <div class="widget-content nopadding tab-content">
                <div class="span12" id="divProdutosServicos" style=" margin-left: 0">
                    <ul class="nav nav-tabs">
                        <li class="active" id="tabDetalhes"><a href="#tab1" data-toggle="tab">Detalhes da OS</a></li>
                        <li id="tabDesconto"><a href="#tab2" data-toggle="tab">Desconto</a></li>
                        <li id="tabProdutos"><a href="#tab3" data-toggle="tab">Produtos</a></li>
                        <li id="tabServicos"><a href="#tab4" data-toggle="tab">Serviços</a></li>
                        <li id="tabOutros"><a href="#tab7" data-toggle="tab">Outros Produtos/Serviços</a></li>
                        <li id="tabAnexos"><a href="#tab5" data-toggle="tab">Anexos</a></li>
                        <li id="tabAnotacoes"><a href="#tab6" data-toggle="tab">Anotações</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab1">
                            <div class="span12" id="divCadastrarOs">
                                <form action="<?php echo current_url(); ?>" method="post" id="formOs">
                                    <?php echo form_hidden('idOs', $result->idOs) ?>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <h3>N° OS: <?php echo $result->idOs; ?></h3>
                                        <div class="span6" style="margin-left: 0">
                                            <label for="cliente">Cliente<span class="required">*</span></label>
                                            <div style="display: flex; gap: 5px; align-items: flex-start;">
                                                <input id="cliente" class="span10" type="text" name="cliente" value="<?php echo $result->nomeCliente ?>" style="margin-right: 5px;" />
                                                <button type="button" class="btn btn-mini btn-success" id="btnCadastrarClienteRapido" title="Cadastrar Cliente Rápido" style="white-space: nowrap; margin-top: 0;">
                                                    <i class="icon-plus"></i> Novo
                                                </button>
                                            </div>
                                            <input id="clientes_id" class="span12" type="hidden" name="clientes_id" value="<?php echo $result->clientes_id ?>" />
                                            <input id="valor" type="hidden" name="valor" value="" />
                                        </div>
                                        <div class="span6">
                                            <label for="tecnico">Técnico / Responsável<span class="required">*</span></label>
                                            <input id="tecnico" class="span12" type="text" name="tecnico" value="<?php echo $result->nome ?>" />
                                            <input id="usuarios_id" class="span12" type="hidden" name="usuarios_id" value="<?php echo $result->usuarios_id ?>" />
                                        </div>
                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span3">
                                            <label for="status">Status<span class="required">*</span></label>
                                            <select class="span12" name="status" id="status" value="">
                                                <option <?php if ($result->status == 'Aberto') { echo 'selected'; } ?> value="Aberto">Aberto</option>
                                                <option <?php if ($result->status == 'Orçamento') { echo 'selected'; } ?> value="Orçamento">Orçamento</option>
                                                <option <?php if ($result->status == 'Negociação') { echo 'selected'; } ?> value="Negociação">Negociação</option>
                                                <option <?php if ($result->status == 'Aprovado') { echo 'selected'; } ?> value="Aprovado">Aprovado</option>
                                                <option <?php if ($result->status == 'Aguardando Peças') { echo 'selected'; } ?> value="Aguardando Peças">Aguardando Peças</option>
                                                <option <?php if ($result->status == 'Em Andamento') { echo 'selected'; } ?> value="Em Andamento">Em Andamento</option>
                                                <option <?php if ($result->status == 'Finalizado') { echo 'selected'; } ?> value="Finalizado">Finalizado</option>
                                                <option <?php if ($result->status == 'Faturado') { echo 'selected'; } ?> value="Faturado">Faturado</option>
                                                <option <?php if ($result->status == 'Cancelado') { echo 'selected'; } ?> value="Cancelado">Cancelado</option>                                                          
                                            </select>
                                        </div>
                                        <div class="span3">
                                            <label for="dataInicial">Data Inicial<span class="required">*</span></label>
                                            <input id="dataInicial" autocomplete="off" class="span12 datepicker" type="text" name="dataInicial" value="<?php echo date('d/m/Y', strtotime($result->dataInicial)); ?>" />
                                        </div>
                                        <div class="span3">
                                            <label for="dataFinal">Data Final<span class="required">*</span></label>
                                            <input id="dataFinal" autocomplete="off" class="span12 datepicker" type="text" name="dataFinal" value="<?php echo date('d/m/Y', strtotime($result->dataFinal)); ?>" />
                                        </div>
                                        <div class="span3">
                                            <label for="garantia">Garantia (dias)</label>
                                            <input id="garantia" type="number" placeholder="Status s/g inserir nº/0" min="0" max="9999" class="span12" name="garantia" value="<?php echo $result->garantia ?>" />
                                            <?php echo form_error('garantia'); ?>
                                            <label for="termoGarantia">Termo Garantia</label>
                                            <input id="termoGarantia" class="span12" type="text" name="termoGarantia" value="<?php echo $result->refGarantia ?>" />
                                            <input id="garantias_id" class="span12" type="hidden" name="garantias_id" value="<?php echo $result->garantias_id ?>" />
                                        </div>
                                    </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="descricaoProduto"><h4>Descrição Produto/Serviço</h4></label>
                                        <textarea class="span12 editor" name="descricaoProduto" id="descricaoProduto" cols="30" rows="5"><?php echo $result->descricaoProduto ?></textarea>
                                        <label style="margin-top: 10px;">
                                            <input type="checkbox" name="imprimir_descricao" id="imprimir_descricao" value="1" <?php echo (isset($result->imprimir_descricao) && $result->imprimir_descricao == 1) ? 'checked' : ''; ?> />
                                            Exibir descrição na impressão
                                        </label>
                                    </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="defeito"><h4>Defeito</h4></label>
                                        <textarea class="span12 editor" name="defeito" id="defeito" cols="30" rows="5"><?php echo $result->defeito ?></textarea>
                                        <label style="margin-top: 10px;">
                                            <input type="checkbox" name="imprimir_defeito" id="imprimir_defeito" value="1" <?php echo (isset($result->imprimir_defeito) && $result->imprimir_defeito == 1) ? 'checked' : ''; ?> />
                                            Exibir defeito na impressão
                                        </label>
                                    </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="observacoes"><h4>Observações</h4></label>
                                        <textarea class="span12 editor" name="observacoes" id="observacoes" cols="30" rows="5"><?php echo $result->observacoes ?></textarea>
                                        <label style="margin-top: 10px;">
                                            <input type="checkbox" name="imprimir_observacoes" id="imprimir_observacoes" value="1" <?php echo (isset($result->imprimir_observacoes) && $result->imprimir_observacoes == 1) ? 'checked' : ''; ?> />
                                            Exibir observações na impressão
                                        </label>
                                    </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="laudoTecnico"><h4>Laudo Técnico</h4></label>
                                        <textarea class="span12 editor" name="laudoTecnico" id="laudoTecnico" cols="30" rows="5"><?php echo $result->laudoTecnico ?></textarea>
                                        <label style="margin-top: 10px;">
                                            <input type="checkbox" name="imprimir_laudo" id="imprimir_laudo" value="1" <?php echo (isset($result->imprimir_laudo) && $result->imprimir_laudo == 1) ? 'checked' : ''; ?> />
                                            Exibir laudo técnico na impressão
                                        </label>
                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0; border-top: 1px solid #ddd; margin-top: 15px; padding-top: 15px;">
                                        <h4 style="margin-bottom: 10px;">Parcelas de Pagamento</h4>
                                        <div class="span12" style="margin-left: 0; margin-bottom: 15px; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                                            <div class="span8" style="margin-left: 0;">
                                                <label for="geradorParcelas">Gerar Parcelas</label>
                                                <input type="text" class="span12" id="geradorParcelas" placeholder="Ex: 30 (30 dias) | 30 60 90 (vencimentos em 30, 60 e 90 dias) | 6x (6 parcelas a cada 30 dias)" />
                                                <small style="color: #666; display: block; margin-top: 5px;">
                                                    <strong>Exemplos:</strong><br>
                                                    • <code>30</code> = 1 parcela em 30 dias<br>
                                                    • <code>30 60 90</code> = 3 parcelas em 30, 60 e 90 dias<br>
                                                    • <code>6x</code> = 6 parcelas iguais a cada 30 dias
                                                </small>
                                            </div>
                                            <div class="span4" style="display: flex; align-items: flex-end; padding-bottom: 0;">
                                                <button type="button" class="button btn btn-success span12" id="btnGerarParcelas" style="max-width: 100%;">
                                                    <span class="button__icon"><i class='bx bx-calculator'></i></span>
                                                    <span class="button__text2">Gerar</span>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div id="tabelaParcelasContainer" style="display: none;">
                                            <div class="span12" style="margin-left: 0; margin-bottom: 10px;">
                                                <h5>Parcelas Configuradas</h5>
                                            </div>
                                            <div class="span12" style="margin-left: 0; overflow-x: auto;">
                                                <table class="table table-bordered" id="tabelaParcelas">
                                                    <thead>
                                                        <tr>
                                                            <th width="5%">Nº</th>
                                                            <th width="15%">Dias</th>
                                                            <th width="20%">Valor</th>
                                                            <th width="45%">Observação</th>
                                                            <th width="15%">Ações</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tbodyParcelas">
                                                        <!-- Parcelas serão inseridas aqui via JavaScript -->
                                                    </tbody>
                                                </table>
                                            </div>
                                            <input type="hidden" name="parcelas_json" id="parcelas_json" value="<?php echo isset($parcelas) && !empty($parcelas) ? htmlspecialchars(json_encode(array_map(function($p) { return ['numero' => $p->numero, 'dias' => $p->dias, 'valor' => floatval($p->valor), 'observacao' => $p->observacao]; }, $parcelas))) : ''; ?>" />
                                        </div>
                                    </div>
                                    <div class="span12" style="padding: 0; margin-left: 0">
                                        <div class="span12" style="display:flex; justify-content: center;">
                                            <button class="button btn btn-primary" id="btnContinuar"><span class="button__icon"><i class="bx bx-sync"></i></span><span class="button__text2">Atualizar</span></button>
                                            <a href="<?php echo base_url() ?>index.php/os" class="button btn btn-mini btn-warning"><span class="button__icon"><i class="bx bx-undo"></i></span> <span class="button__text2">Voltar</span></a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!--Desconto-->
                        <?php $total = 0; foreach ($produtos as $p) {$total = $total + $p->subTotal;}?>
                        <?php $totals = 0; foreach ($servicos as $s) { $preco = $s->preco ?: $s->precoVenda; $subtotals = $preco * ($s->quantidade ?: 1); $totals = $totals + $subtotals;}?>
                        <div class="tab-pane" id="tab2">
                            <div class="span12 well" style="padding: 1%; margin-left: 0">
                                <form id="formDesconto" action="<?php echo base_url(); ?>index.php/os/adicionarDesconto" method="POST">
                                    <div id="divValorTotal">
                                        <div class="span2">
                                            <label for="">Valor Total Da OS:</label>
                                            <input class="span12 money" id="valorTotal" name="valorTotal" type="text" data-affixes-stay="true" data-thousands="" data-decimal="." name="valor" value="<?php echo number_format($totals + $total, 2, '.', ''); ?>" readonly />
                                        </div>
                                    </div>
                                    <div class="span1">
                                        <label for="">Tipo Desc.</label>
                                        <select style="width: 4em;" name="tipoDesconto" id="tipoDesconto">
                                            <option value="real">R$</option>
                                            <option value="porcento" <?= $result->tipo_desconto == "porcento" ? "selected" : "" ?>>%</option>
                                        </select>
                                        <strong><span style="color: red" id="errorAlert"></span></strong>
                                    </div>
                                    <div class="span3">
                                        <input type="hidden" name="idOs" id="idOs"
                                            value="<?php echo $result->idOs; ?>" />
                                        <label for="">Desconto</label>
                                        <input style="width: 4em;" id="desconto" name="desconto" type="text"
                                            placeholder="" maxlength="6" size="2" value="<?= $result->desconto ?>" />
                                        <strong><span style="color: red" id="errorAlert"></span></strong>
                                    </div>
                                    <div class="span2">
                                        <label for="">Total com Desconto</label>
                                        <input class="span12 money" id="resultado" type="text" data-affixes-stay="true" data-thousands="" data-decimal="." name="resultado" value="<?php echo $result->valor_desconto ?>" readonly />
                                    </div>
                                    <div class="span2">
                                        <label for="">&nbsp;</label>
                                        <button class="button btn btn-success" id="btnAdicionarDesconto">
                                            <span class="button__icon"><i class='bx bx-plus-circle'></i></span> <span class="button__text2">Aplicar</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!--Produtos-->
                        <div class="tab-pane" id="tab3">
                            <div class="span12 well" style="padding: 1%; margin-left: 0">
                                <form id="formProdutos" action="<?php echo base_url() ?>index.php/os/adicionarProduto" method="post">
                                    <div class="span6">
                                        <input type="hidden" name="idProduto" id="idProduto" />
                                        <input type="hidden" name="idOsProduto" id="idOsProduto" value="<?php echo $result->idOs; ?>" />
                                        <input type="hidden" name="estoque" id="estoque" value="" />
                                        <label for="">Produto</label>
                                        <input type="text" class="span12" name="produto" id="produto" placeholder="Digite o nome do produto" />
                                    </div>
                                    <div class="span2">
                                        <label for="">Preço</label>
                                        <input type="text" placeholder="Preço" id="preco" name="preco" class="span12 money" data-affixes-stay="true" data-thousands="" data-decimal="." />
                                    </div>
                                    <div class="span2">
                                        <label for="">Quantidade</label>
                                        <input type="text" placeholder="Quantidade" id="quantidade" name="quantidade"
                                            class="span12" />
                                    </div>
                                    <div class="span2">
                                        <label for="">&nbsp;</label>
                                        <button class="button btn btn-success" id="btnAdicionarProduto">
                                            <span class="button__icon"><i class='bx bx-plus-circle'></i></span> <span class="button__text2">Adicionar</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="widget-box" id="divProdutos">
                                <div class="widget_content nopadding">
                                    <table width="100%" class="table table-bordered" id="tblProdutos">
                                        <thead>
                                            <tr>
                                                <th>Produto</th>
                                                <th width="8%">Quantidade</th>
                                                <th width="10%">Preço unit.</th>
                                                <th width="6%">Ações</th>
                                                <th width="10%">Sub-total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $total = 0;
                                            foreach ($produtos as $p) {
                                                $total = $total + $p->subTotal;
                                                echo '<tr>';
                                                echo '<td>' . $p->descricao . '</td>';
                                                echo '<td><div align="center">' . $p->quantidade . '</td>';
                                                echo '<td><div align="center">R$: ' . ($p->preco ?: $p->precoVenda) . '</td>';
                                                echo (strtolower($result->status) != "cancelado") ? '<td><div align="center"><a href="" idAcao="' . $p->idProdutos_os . '" prodAcao="' . $p->idProdutos . '" quantAcao="' . $p->quantidade . '" title="Excluir Produto" class="btn-nwe4"><i class="bx bx-trash-alt"></i></a></td>' : '<td></td>';
                                                echo '<td><div align="center">R$: ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                                                echo '</tr>';
                                            } ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" style="text-align: right"><strong>Total:</strong>
                                                </td>
                                                <td>
                                                    <div align="center"><strong>R$
                                                            <?php echo number_format($total, 2, ',', '.'); ?><input
                                                                type="hidden" id="total-venda"
                                                                value="<?php echo number_format($total, 2); ?>"></strong>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!--Serviços-->
                        <div class="tab-pane" id="tab4">
                            <div class="span12 well" style="padding: 1%; margin-left: 0">
                                <form id="formServicos" action="<?php echo base_url() ?>index.php/os/adicionarServico"
                                    method="post">
                                    <div class="span5">
                                        <input type="hidden" name="idServico" id="idServico" />
                                        <input type="hidden" name="idOsServico" id="idOsServico"
                                            value="<?php echo $result->idOs; ?>" />
                                        <label for="">Serviço</label>
                                        <input type="text" class="span12" name="servico" id="servico"
                                            placeholder="Digite o nome do serviço" />
                                    </div>
                                    <div class="span1">
                                        <label for="">&nbsp;</label>
                                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aServico')) : ?>
                                        <a href="#modal-novo-servico" role="button" data-toggle="modal" class="button btn btn-mini btn-info" style="width: 100%;" title="Criar Novo Serviço">
                                            <span class="button__icon"><i class='bx bx-plus'></i></span>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="span2">
                                        <label for="">Preço</label>
                                        <input type="text" placeholder="Preço" id="preco_servico" name="preco"
                                            class="span12 money" data-affixes-stay="true" data-thousands=""
                                            data-decimal="." />
                                    </div>
                                    <div class="span2">
                                        <label for="">Quantidade</label>
                                        <input type="text" placeholder="Quantidade" id="quantidade_servico"
                                            name="quantidade" class="span12" />
                                    </div>
                                    <div class="span2">
                                        <label for="">&nbsp;</label>
                                        <button class="button btn btn-success">
                                            <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span
                                                class="button__text2">Adicionar</span></button>
                                    </div>
                                    <div class="span12" style="margin-top: 10px;">
                                        <label for="">Detalhes</label>
                                        <textarea placeholder="Detalhes do serviço (opcional)" id="detalhes_servico" name="detalhes" class="span12" rows="2"></textarea>
                                    </div>
                                </form>
                            </div>
                            <div class="widget-box" id="divServicos">
                                <div class="widget_content nopadding">
                                    <table width="100%" class="table table-bordered" id="tblServicos">
                                        <thead>
                                            <tr>
                                                <th>Serviço</th>
                                                <th width="8%">Quantidade</th>
                                                <th width="10%">Preço</th>
                                                <th width="6%">Ações</th>
                                                <th width="10%">Sub-totals</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $totals = 0;
                                            foreach ($servicos as $s) {
                                                $preco = $s->preco ?: $s->precoVenda;
                                                $subtotals = $preco * ($s->quantidade ?: 1);
                                                $totals = $totals + $subtotals;
                                                echo '<tr id="servico-row-' . $s->idServicos_os . '">';
                                                echo '<td>';
                                                // Se não tem nome (serviço customizado), usar detalhes como nome
                                                if (empty($s->nome) && !empty($s->detalhes)) {
                                                    echo '<strong style="color: #007bff;">📝 ' . htmlspecialchars($s->detalhes) . '</strong>';
                                                    echo '<br><small style="color: #666;">(Produto/Serviço customizado)</small>';
                                                } else {
                                                    echo '<strong>' . htmlspecialchars($s->nome ?: 'Serviço') . '</strong>';
                                                    if (!empty($s->detalhes)) {
                                                        echo '<br><small style="color: #666;">' . htmlspecialchars($s->detalhes) . '</small>';
                                                    }
                                                }
                                                echo '</td>';
                                                echo '<td><div align="center">' . ($s->quantidade ?: 1) . '</div></td>';
                                                echo '<td><div align="center"><span class="preco-servico-display" id="preco-display-' . $s->idServicos_os . '">R$ ' . number_format($preco, 2, ',', '.') . '</span></div></td>';
                                                echo '<td><div align="center">';
                                                echo '<span idAcao="' . $s->idServicos_os . '" precoAtual="' . number_format($preco, 2, '.', '') . '" quantidadeAtual="' . ($s->quantidade ?: 1) . '" detalhesAtual="' . htmlspecialchars($s->detalhes ?? '', ENT_QUOTES) . '" title="Editar Serviço" class="btn-nwe4 editar-servico" style="margin-right: 5px; color: #007bff; cursor: pointer;"><i class="bx bx-edit"></i></span>';
                                                echo '<span idAcao="' . $s->idServicos_os . '" title="Excluir Serviço" class="btn-nwe4 servico" style="color: #dc3545; cursor: pointer;"><i class="bx bx-trash-alt"></i></span>';
                                                echo '</div></td>';
                                                echo '<td><div align="center"><span id="subtotal-' . $s->idServicos_os . '">R$: ' . number_format($subtotals, 2, ',', '.') . '</span></div></td>';
                                                echo '</tr>';
                                            } ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" style="text-align: right"><strong>Total:</strong>
                                                </td>
                                                <td>
                                                    <div align="center"><strong>R$
                                                            <?php echo number_format($totals, 2, ',', '.'); ?><input
                                                                type="hidden" id="total-servico"
                                                                value="<?php echo number_format($totals, 2); ?>"></strong>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!--Anexos-->
                        <div class="tab-pane" id="tab5">
                            <div class="span12" style="padding: 1%; margin-left: 0">
                                <div class="span12 well" style="padding: 1%; margin-left: 0" id="form-anexos">
                                    <form id="formAnexos" enctype="multipart/form-data" action="javascript:;"
                                        accept-charset="utf-8" s method="post">
                                        <div class="span10">
                                            <input type="hidden" name="idOsServico" id="idOsServico"
                                                value="<?php echo $result->idOs; ?>" />
                                            <label for="">Anexo</label>
                                            <input type="file" class="span12" name="userfile[]" multiple="multiple"
                                                size="20" />
                                        </div>
                                        <div class="span2">
                                            <label for="">.</label>
                                            <button class="button btn btn-success">
                                                <span class="button__icon"><i class='bx bx-paperclip'></i></span><span
                                                    class="button__text2">Anexar</span></button>
                                        </div>
                                    </form>
                                </div>
                                <div class="span12 pull-left" id="divAnexos" style="margin-left: 0">
                                    <?php
                                    foreach ($anexos as $a) {
                                        if ($a->thumb == null) {
                                            $thumb = base_url() . 'assets/img/icon-file.png';
                                            $link = base_url() . 'assets/img/icon-file.png';
                                        } else {
                                            $thumb = $a->url . '/thumbs/' . $a->thumb;
                                            $link = $a->url . '/' . $a->anexo;
                                        }
                                        echo '<div class="span3" style="min-height: 150px; margin-left: 0">
                                                    <a style="min-height: 150px;" href="#modal-anexo" imagem="' . $a->idAnexos . '" link="' . $link . '" role="button" class="btn anexo span12" data-toggle="modal">
                                                        <img src="' . $thumb . '" alt="">
                                                    </a>
                                                </div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!--Outros Produtos/Serviços-->
                        <div class="tab-pane" id="tab7">
                            <div class="span12 well" style="padding: 1%; margin-left: 0">
                                <form id="formOutros">
                                    <input type="hidden" name="idOsOutros" id="idOsOutros" value="<?php echo $result->idOs; ?>" />
                                    <input type="hidden" name="idOutros" id="idOutrosEditar" value="" />
                                    <div class="span12" style="margin-left: 0; margin-bottom: 15px;">
                                        <label for="descricao_outros"><strong>Descrição</strong> <small style="color: #666;">(Use o editor para formatar o texto)</small></label>
                                        <textarea placeholder="Digite aqui produtos ou serviços que não estão cadastrados no sistema. Ex: 'Instalação de ar condicionado', 'Troca de peças diversas', etc." id="descricao_outros" name="descricao" class="span12 editor" rows="6"></textarea>
                                    </div>
                                    <div class="span12" style="margin-left: 0; display: flex; gap: 10px; align-items: flex-end;">
                                        <div class="span4" style="margin-left: 0;">
                                            <label for="preco_outros">Preço *</label>
                                            <input type="text" placeholder="0,00" id="preco_outros" name="preco" class="span12 money" required />
                                        </div>
                                        <div class="span8">
                                            <label for="">&nbsp;</label>
                                            <button type="submit" class="button btn btn-success span12">
                                                <span class="button__icon"><i class='bx bx-plus-circle'></i></span>
                                                <span class="button__text2">Adicionar</span>
                                            </button>
                                        </div>
                                    </div>
                                    <small style="color: #666; display: block; margin-top: 5px;">
                                        <i class="bx bx-info-circle"></i> Este campo permite adicionar descrições de produtos/serviços que não estão cadastrados. Será impresso na proposta com o preço informado.
                                    </small>
                                </form>
                            </div>
                            <div class="widget-box" id="divOutros">
                                <div class="widget_content nopadding">
                                    <table width="100%" class="table table-bordered" id="tblOutros">
                                        <thead>
                                            <tr>
                                                <th>Descrição</th>
                                                <th width="15%">Preço</th>
                                                <th width="10%">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if (!isset($outros)) {
                                                $this->load->model('outros_produtos_servicos_os_model');
                                                $outros = $this->outros_produtos_servicos_os_model->getByOs($result->idOs);
                                            }
                                            $totalOutros = 0;
                                            foreach ($outros as $o) {
                                                $totalOutros += $o->preco;
                                                echo '<tr id="outros-row-' . $o->idOutros . '">';
                                                echo '<td>' . $o->descricao . '</td>';
                                                echo '<td><div align="center"><strong>R$ ' . number_format($o->preco, 2, ',', '.') . '</strong></div></td>';
                                                echo '<td><div align="center">';
                                                echo '<span idAcao="' . $o->idOutros . '" descricao="' . htmlspecialchars($o->descricao, ENT_QUOTES) . '" preco="' . number_format($o->preco, 2, '.', '') . '" title="Editar" class="btn-nwe4 editar-outros" style="color: #007bff; cursor: pointer; margin-right: 5px;"><i class="bx bx-edit"></i></span>';
                                                echo '<span idAcao="' . $o->idOutros . '" title="Excluir" class="btn-nwe4 excluir-outros" style="color: #dc3545; cursor: pointer;"><i class="bx bx-trash-alt"></i></span>';
                                                echo '</div></td>';
                                                echo '</tr>';
                                            } ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td style="text-align: right"><strong>Total:</strong></td>
                                                <td><div align="center"><strong>R$ <?php echo number_format($totalOutros, 2, ',', '.'); ?></strong></div></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!--Anotações-->
                        <div class="tab-pane" id="tab6">
                            <div class="span12" style="padding: 1%; margin-left: 0">

                                <div class="span12" id="divAnotacoes" style="margin-left: 0">

                                    <a href="#modal-anotacao" id="btn-anotacao" role="button" data-toggle="modal"
                                        class="button btn btn-success" style="max-width: 160px">
                                        <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span
                                            class="button__text2">Adicionar anotação</span></a>
                                    <hr>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Data/Hora</th>
                                                <th>Anotação</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($anotacoes as $a) {
                                                echo '<tr>';
                                                echo '<td>' . date('d/m/Y H:i:s', strtotime($a->data_hora)) . '</td>';
                                                echo '<td>' . $a->anotacao . '</td>';
                                                echo '<td><span idAcao="' . $a->idAnotacoes . '" title="Excluir Anotação" class="btn-nwe4 anotacao"><i class="bx bx-trash-alt"></i></span></td>';
                                                echo '</tr>';
                                            }
                                            if (!$anotacoes) {
                                                echo '<tr><td colspan="3">Nenhuma anotação cadastrada</td></tr>';
                                            }

                                            ?>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                        <!-- Fim tab anotações -->
                    </div>
                </div>
                &nbsp
            </div>
        </div>
    </div>
</div>

<!-- Modal visualizar anexo -->
<div id="modal-anexo" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Visualizar Anexo</h3>
    </div>
    <div class="modal-body">
        <div class="span12" id="div-visualizar-anexo" style="text-align: center">
            <div class='progress progress-info progress-striped active'>
                <div class='bar' style='width: 100%'></div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
        <a href="" id-imagem="" class="btn btn-inverse" id="download">Download</a>
        <a href="" link="" class="btn btn-danger" id="excluir-anexo">Excluir Anexo</a>
    </div>
</div>

<!-- Modal cadastro anotações -->
<div id="modal-anotacao" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <form action="#" method="POST" id="formAnotacao">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Adicionar Anotação</h3>
        </div>
        <div class="modal-body">
            <div class="span12" id="divFormAnotacoes" style="margin-left: 0"></div>
            <div class="span12" style="margin-left: 0">
                <label for="anotacao">Anotação</label>
                <textarea class="span12" name="anotacao" id="anotacao" cols="30" rows="3"></textarea>
                <input type="hidden" name="os_id" value="<?php echo $result->idOs; ?>">
            </div>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button class="btn" data-dismiss="modal" aria-hidden="true" id="btn-close-anotacao">Fechar</button>
            <button class="btn btn-primary">Adicionar</button>
        </div>
    </form>
</div>

<!-- Modal Editar Preço Serviço -->
<div id="modal-editar-preco-servico" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <form id="formEditarPrecoServico" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Editar Serviço</h3>
        </div>
        <div class="modal-body">
            <div class="control-group">
                <label for="servico_nome_editar" class="control-label">Serviço</label>
                <div class="controls">
                    <input type="text" id="servico_nome_editar" class="span12" readonly />
                </div>
            </div>
            <div class="control-group">
                <label for="preco_servico_editar" class="control-label">Novo Preço<span class="required">*</span></label>
                <div class="controls">
                    <input type="text" id="preco_servico_editar" name="preco" class="span12 money" 
                        data-affixes-stay="true" data-thousands="" data-decimal="." placeholder="0.00" />
                </div>
            </div>
            <div class="control-group">
                <label for="quantidade_servico_editar" class="control-label">Quantidade</label>
                <div class="controls">
                    <input type="text" id="quantidade_servico_editar" name="quantidade" class="span12" readonly />
                </div>
            </div>
            <div class="control-group">
                <label for="detalhes_servico_editar" class="control-label">Detalhes</label>
                <div class="controls">
                    <textarea id="detalhes_servico_editar" name="detalhes" class="span12" rows="3" placeholder="Detalhes do serviço (opcional)"></textarea>
                </div>
            </div>
            <input type="hidden" id="idServicos_os_editar" name="idServicos_os" />
            <input type="hidden" id="idOs_editar" name="idOs" value="<?php echo $result->idOs; ?>" />
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div>

<!-- Modal Novo Serviço Rápido -->
<div id="modal-novo-servico" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <form id="formNovoServico" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Criar Novo Serviço</h3>
        </div>
        <div class="modal-body">
            <div class="control-group">
                <label for="nome_servico_rapido" class="control-label">Nome do Serviço<span class="required">*</span></label>
                <div class="controls">
                    <input type="text" id="nome_servico_rapido" name="nome" class="span12" placeholder="Ex: Instalação de Sistema" />
                </div>
            </div>
            <div class="control-group">
                <label for="preco_servico_rapido" class="control-label">Preço<span class="required">*</span></label>
                <div class="controls">
                    <input type="text" id="preco_servico_rapido" name="preco" class="span12 money" 
                        data-affixes-stay="true" data-thousands="" data-decimal="." placeholder="0.00" />
                </div>
            </div>
            <div class="control-group">
                <label for="descricao_servico_rapido" class="control-label">Descrição</label>
                <div class="controls">
                    <input type="text" id="descricao_servico_rapido" name="descricao" class="span12" placeholder="Descrição opcional" />
                </div>
            </div>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button type="button" class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button type="submit" class="btn btn-primary">Criar Serviço</button>
        </div>
    </form>
</div>

<!-- Modal Faturar-->
<div id="modal-faturar" class="modal hide fade " tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <form id="formFaturar" action="<?php echo current_url() ?>" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Faturar OS</h3>
        </div>
        <div class="modal-body">
            <div class="span12 alert alert-info" style="margin-left: 0"> Obrigatório o preenchimento dos campos com
                asterisco.</div>
            <div class="span12" style="margin-left: 0">
                <label for="descricao">Descrição</label>
                <input class="span12" id="descricao" type="text" name="descricao"
                    value="Fatura de OS Nº: <?php echo $result->idOs; ?> " />
            </div>
            <div class="span12" style="margin-left: 0">
                <div class="span12" style="margin-left: 0">
                    <label for="cliente">Cliente*</label>
                    <input class="span12" id="cliente" type="text" name="cliente"
                        value="<?php echo $result->nomeCliente ?>" />
                    <input type="hidden" name="clientes_id" id="clientes_id" value="<?php echo $result->clientes_id ?>">
                    <input type="hidden" name="os_id" id="os_id" value="<?php echo $result->idOs; ?>">
                    <input type="hidden" name="tipoDesconto" id="tipoDesconto"
                        value="<?php echo $result->tipo_desconto; ?>">
                </div>
            </div>
            <div class="span12" style="margin-left: 0">
                <div class="span6" style="margin-left: 0">
                    <label for="valor">Valor*</label>
                    <input type="hidden" id="tipo" name="tipo" value="receita" />
                    <input class="span12 money" id="valor" type="text" data-affixes-stay="true" data-thousands=""
                        data-decimal="." name="valor"
                        value="<?php echo number_format($totals + $total, 2, '.', ''); ?>" />
                </div>
                <div class="span6" style="margin-left: 2;">
                    <label for="valor">Valor Com Desconto*</label>
                    <input class="span12 money" id="faturar-desconto" type="text" name="faturar-desconto"
                        value="<?php echo number_format($result->valor_desconto, 2, '.', ''); ?> " />
                    <strong><span style="color: red" id="resultado"></span></strong>
                </div>
            </div>
            <div class="span12" style="margin-left: 0">
                <div class="span4" style="margin-left: 0">
                    <label for="vencimento">Data Entrada*</label>
                    <input class="span12 datepicker" autocomplete="off" id="vencimento" type="text" name="vencimento" />
                </div>
            </div>
            <div class="span12" style="margin-left: 0">
                <div class="span4" style="margin-left: 0">
                    <label for="recebido">Recebido?</label>
                    &nbsp &nbsp &nbsp &nbsp <input id="recebido" type="checkbox" name="recebido" value="1" />
                </div>
                <div id="divRecebimento" class="span8" style=" display: none">
                    <div class="span6">
                        <label for="recebimento">Data Recebimento</label>
                        <input class="span12 datepicker" autocomplete="off" id="recebimento" type="text"
                            name="recebimento" />
                    </div>
                    <div class="span6">
                        <label for="formaPgto">Forma Pgto</label>
                        <select name="formaPgto" id="formaPgto" class="span12">
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Cartão de Crédito">Cartão de Crédito</option>
                            <option value="Cartão de Débito">Cartão de Débito</option>
                            <option value="Boleto">Boleto</option>
                            <option value="Depósito">Depósito</option>
                            <option value="Pix">Pix</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true"
                id="btn-cancelar-faturar"><span class="button__icon"><i class="bx bx-x"></i></span><span
                    class="button__text2">Cancelar</span></button>
            <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-dollar'></i></span>
                <span class="button__text2">Faturar</span></button>
        </div>
    </form>
</div>

<script src="<?php echo base_url(); ?>assets/js/maskmoney.js"></script>

<script type="text/javascript">
    function calcDesconto(valor, desconto, tipoDesconto) {
        var resultado = 0;
        if (tipoDesconto == 'real') {
            resultado = valor - desconto;
        }
        if (tipoDesconto == 'porcento') {
            resultado = (valor - desconto * valor / 100).toFixed(2);
        }
        return resultado;
    }

    function validarDesconto(resultado, valor) {
        if (resultado == valor) {
            return resultado = "";
        } else {
            return resultado.toFixed(2);
        }
    }
    var valorBackup = $("#valorTotal").val();

    $("#quantidade").keyup(function () {
        this.value = this.value.replace(/[^0-9.]/g, '');
    });

    $("#quantidade_servico").keyup(function () {
        this.value = this.value.replace(/[^0-9.]/g, '');
    });
    $('#tipoDesconto').on('change', function () {
        if (Number($("#desconto").val()) >= 0) {
            $('#resultado').val(calcDesconto(Number($("#valorTotal").val()), Number($("#desconto").val()), $("#tipoDesconto").val()));
            $('#resultado').val(validarDesconto(Number($('#resultado').val()), Number($("#valorTotal").val())));
        }
    });
    $("#desconto").keyup(function () {
        this.value = this.value.replace(/[^0-9.]/g, '');
        if ($("#valorTotal").val() == null || $("#valorTotal").val() == '') {
            $('#errorAlert').text('Valor não pode ser apagado.').css("display", "inline").fadeOut(5000);
            $('#desconto').val('');
            $('#resultado').val('');
            $("#valorTotal").val(valorBackup);
            $("#desconto").focus();

        } else if (Number($("#desconto").val()) >= 0) {
            $('#resultado').val(calcDesconto(Number($("#valorTotal").val()), Number($("#desconto").val()), $("#tipoDesconto").val()));
            $('#resultado').val(validarDesconto(Number($('#resultado').val()), Number($("#valorTotal").val())));
        } else {
            $('#errorAlert').text('Erro desconhecido.').css("display", "inline").fadeOut(5000);
            $('#desconto').val('');
            $('#resultado').val('');
        }
    });

    $("#valorTotal").focusout(function () {
        $("#valorTotal").val(valorBackup);
        if ($("#valorTotal").val() == '0.00' && $('#resultado').val() != '') {
            $('#errorAlert').text('Você não pode apagar o valor.').css("display", "inline").fadeOut(6000);
            $('#resultado').val('');
            $("#valorTotal").val(valorBackup);
            $('#resultado').val(calcDesconto(Number($("#valorTotal").val()), Number($("#desconto").val())));
            $('#resultado').val(validarDesconto(Number($('#resultado').val()), Number($("#valorTotal").val())));
            $("#desconto").focus();
        } else {
            $('#resultado').val(calcDesconto(Number($("#valorTotal").val()), Number($("#desconto").val())));
            $('#resultado').val(validarDesconto(Number($('#resultado').val()), Number($("#valorTotal").val())));
        }
    });

    $('#resultado').focusout(function () {
        if (Number($('#resultado').val()) > Number($("#valorTotal").val())) {
            $('#errorAlert').text('Desconto não pode ser maior que o Valor.').css("display", "inline").fadeOut(6000);
            $('#resultado').val('');
        }
        if ($("#desconto").val() != "" || $("#desconto").val() != null) {
            $('#resultado').val(calcDesconto(Number($("#valorTotal").val()), Number($("#desconto").val())));
            $('#resultado').val(validarDesconto(Number($('#resultado').val()), Number($("#valorTotal").val())));
        }
    });
    $(document).ready(function () {

        $(".money").maskMoney({
            decimal: ",",
            thousands: ".",
            allowZero: true
        });
        
        // Inicializar maskMoney no modal quando abrir
        $('#modal-novo-servico').on('shown', function() {
            $('#preco_servico_rapido').maskMoney();
        });
        
        // Inicializar maskMoney no modal de editar preço quando abrir
        $('#modal-editar-preco-servico').on('shown', function() {
            $('#preco_servico_editar').maskMoney();
        });

        $('#recebido').click(function (event) {
            var flag = $(this).is(':checked');
            if (flag == true) {
                $('#divRecebimento').show();
            } else {
                $('#divRecebimento').hide();
            }
        });

        $("#formFaturar").validate({
            rules: {
                descricao: {
                    required: true
                },
                cliente: {
                    required: true
                },
                valor: {
                    required: true
                },
                vencimento: {
                    required: true
                }

            },
            messages: {
                descricao: {
                    required: 'Campo Requerido.'
                },
                cliente: {
                    required: 'Campo Requerido.'
                },
                valor: {
                    required: 'Campo Requerido.'
                },
                vencimento: {
                    required: 'Campo Requerido.'
                }
            },
            submitHandler: function (form) {
                var dados = $(form).serialize();
                var qtdProdutos = $('#tblProdutos >tbody >tr').length;
                var qtdServicos = $('#tblServicos >tbody >tr').length;
                var qtdTotalProdutosServicos = qtdProdutos + qtdServicos;

                $('#btn-cancelar-faturar').trigger('click');

                if (qtdTotalProdutosServicos <= 0) {
                    Swal.fire({
                        type: "error",
                        title: "Atenção",
                        text: "Não é possível faturar uma OS sem serviços e/ou produtos"
                    });
                } else if (qtdTotalProdutosServicos > 0) {
                    $.ajax({
                        type: "POST",
                        url: "<?php echo base_url(); ?>index.php/os/faturar",
                        data: dados,
                        dataType: 'json',
                        success: function (data) {
                            if (data.result == true) {
                                window.location.reload(true);
                            } else {
                                Swal.fire({
                                    type: "error",
                                    title: "Atenção",
                                    text: "Ocorreu um erro ao tentar faturar OS."
                                });
                                $('#progress-fatura').hide();
                            }
                        }
                    });

                    return false;
                }
            }
        });
        $('#formDesconto').submit(function (e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                beforeSend: function () {
                    Swal.fire({
                        title: 'Processando',
                        text: 'Registrando desconto...',
                        icon: 'info',
                        showCloseButton: false,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    });
                },
                success: function (response) {
                    if (response.result) {
                        Swal.fire({
                            type: "success",
                            title: "Sucesso",
                            text: response.messages
                        });
                        setTimeout(function () {
                            window.location.href = window.BaseUrl + 'index.php/os/editar/' + <?php echo $result->idOs ?>;
                        }, 2000);
                    } else {
                        Swal.fire({
                            type: "error",
                            title: "Atenção",
                            text: response.messages
                        });
                    }

                },
                error: function (response) {
                    Swal.fire({
                        type: "error",
                        title: "Atenção",
                        text: response.responseJSON.messages
                    });
                }
            });
        });

        $("#formwhatsapp").validate({
            rules: {
                descricao: {
                    required: true
                },
                cliente: {
                    required: true
                },
                valor: {
                    required: true
                },
                vencimento: {
                    required: true
                }

            },
            messages: {
                descricao: {
                    required: 'Campo Requerido.'
                },
                cliente: {
                    required: 'Campo Requerido.'
                },
                valor: {
                    required: 'Campo Requerido.'
                },
                vencimento: {
                    required: 'Campo Requerido.'
                }
            },
            submitHandler: function (form) {
                var dados = $(form).serialize();
                $('#btn-cancelar-faturar').trigger('click');
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/os/faturar",
                    data: dados,
                    dataType: 'json',
                    success: function (data) {
                        if (data.result == true) {

                            window.location.reload(true);
                        } else {
                            Swal.fire({
                                type: "error",
                                title: "Atenção",
                                text: "Ocorreu um erro ao tentar  OS."
                            });
                            $('#progress-fatura').hide();
                        }
                    }
                });

                return false;
            }
        });

        $("#produto").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteProduto",
            minLength: 2,
            select: function (event, ui) {
                $("#codDeBarra").val(ui.item.codbar);
                $("#idProduto").val(ui.item.id);
                $("#estoque").val(ui.item.estoque);
                $("#preco").val(ui.item.preco);
                $("#quantidade").focus();
            }
        });

        $("#servico").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteServico",
            minLength: 2,
            select: function (event, ui) {
                $("#idServico").val(ui.item.id);
                $("#preco_servico").val(ui.item.preco);
                $("#quantidade_servico").focus();
            }
        });

        // Formulário para criar novo serviço rapidamente
        $("#formNovoServico").validate({
            rules: {
                nome_servico_rapido: {
                    required: true
                },
                preco_servico_rapido: {
                    required: true
                }
            },
            messages: {
                nome_servico_rapido: {
                    required: 'Campo obrigatório.'
                },
                preco_servico_rapido: {
                    required: 'Campo obrigatório.'
                }
            },
            submitHandler: function(form) {
                var dados = $(form).serialize();
                
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/os/criarServicoRapido",
                    data: dados,
                    dataType: 'json',
                    success: function (data) {
                        if (data.result == true) {
                            // Fechar modal
                            $('#modal-novo-servico').modal('hide');
                            
                            // Limpar formulário
                            $('#formNovoServico')[0].reset();
                            
                            // Preencher campos com o serviço recém-criado
                            $("#idServico").val(data.servico.id);
                            $("#servico").val(data.servico.nome);
                            $("#preco_servico").val(data.servico.preco);
                            $("#quantidade_servico").focus();
                            
                            // Mostrar mensagem de sucesso
                            Swal.fire({
                                type: "success",
                                title: "Sucesso!",
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            
                            // Forçar atualização do autocomplete (destruir e recriar)
                            $("#servico").autocomplete("destroy");
                            $("#servico").autocomplete({
                                source: "<?php echo base_url(); ?>index.php/os/autoCompleteServico",
                                minLength: 2,
                                select: function (event, ui) {
                                    $("#idServico").val(ui.item.id);
                                    $("#preco_servico").val(ui.item.preco);
                                    $("#quantidade_servico").focus();
                                }
                            });
                        } else {
                            Swal.fire({
                                type: "error",
                                title: "Erro",
                                text: data.message || "Ocorreu um erro ao criar o serviço."
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            type: "error",
                            title: "Erro",
                            text: "Ocorreu um erro ao comunicar com o servidor."
                        });
                    }
                });
                return false;
            }
        });


        $("#cliente").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteCliente",
            minLength: 2,
            select: function (event, ui) {
                $("#clientes_id").val(ui.item.id);
            }
        });

        $("#tecnico").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteUsuario",
            minLength: 2,
            select: function (event, ui) {
                $("#usuarios_id").val(ui.item.id);
            }
        });

        $("#termoGarantia").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteTermoGarantia",
            minLength: 1,
            select: function (event, ui) {
                if (ui.item.id) {
                    $("#garantias_id").val(ui.item.id);
                }
            }
        });

        $('#termoGarantia').on('change', function () {
            if (!$(this).val() && $("#garantias_id").val()) {
                $("#garantias_id").val('');
                Swal.fire({
                    type: "success",
                    title: "Sucesso",
                    text: "Termo de garantia removido"
                });
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
                }
            },
            errorClass: "help-inline",
            errorElement: "span",
            highlight: function (element, errorClass, validClass) {
                $(element).parents('.control-group').addClass('error');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error');
                $(element).parents('.control-group').addClass('success');
            }
        });

        $("#formProdutos").validate({
            rules: {
                preco: {
                    required: true
                },
                quantidade: {
                    required: true
                }
            },
            messages: {
                preco: {
                    required: 'Inserir o preço'
                },
                quantidade: {
                    required: 'Insira a quantidade'
                }
            },
            submitHandler: function (form) {
                var quantidade = parseInt($("#quantidade").val());
                var estoque = parseInt($("#estoque").val());

                <?php if (!$configuration['control_estoque']) {
                    echo 'estoque = 1000000';
                }
                ; ?>

                if (estoque < quantidade) {
                    Swal.fire({
                        type: "error",
                        title: "Atenção",
                        text: "Você não possui estoque suficiente."
                    });
                } else {
                    var dados = $(form).serialize();
                    $("#divProdutos").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                    $.ajax({
                        type: "POST",
                        url: "<?php echo base_url(); ?>index.php/os/adicionarProduto",
                        data: dados,
                        dataType: 'json',
                        success: function (data) {
                            if (data.result == true) {
                                $("#divProdutos").load("<?php echo current_url(); ?> #divProdutos");
                                $("#quantidade").val('');
                                $("#preco").val('');
                                $("#resultado").val('');
                                $("#desconto").val('');
                                $("#divValorTotal").load("<?php echo current_url(); ?> #divValorTotal", function() {
                                    // Atualizar valores das parcelas após adicionar produto
                                    if (typeof atualizarValoresParcelas === 'function') {
                                        setTimeout(atualizarValoresParcelas, 300);
                                    }
                                });
                                $("#produto").val('').focus();
                            } else {
                                Swal.fire({
                                    type: "error",
                                    title: "Atenção",
                                    text: "Ocorreu um erro ao tentar adicionar produto."
                                });
                            }
                        }
                    });
                    return false;
                }
            }
        });

        $("#formServicos").validate({
            rules: {
                servico: {
                    required: true
                },
                preco: {
                    required: true
                },
                quantidade: {
                    required: true
                },
            },
            messages: {
                servico: {
                    required: 'Insira um serviço'
                },
                preco: {
                    required: 'Insira o preço'
                },
                quantidade: {
                    required: 'Insira a quantidade'
                },
            },
            submitHandler: function (form) {
                var dados = $(form).serialize();

                $("#divServicos").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/os/adicionarServico",
                    data: dados,
                    dataType: 'json',
                    success: function (data) {
                        if (data.result == true) {
                            $("#divServicos").load("<?php echo current_url(); ?> #divServicos");
                            $("#quantidade_servico").val('');
                            $("#preco_servico").val('');
                            $("#detalhes_servico").val('');
                            $("#resultado").val('');
                            $("#desconto").val('');
                            $("#divValorTotal").load("<?php echo current_url(); ?> #divValorTotal", function() {
                                // Atualizar valores das parcelas após adicionar serviço
                                if (typeof atualizarValoresParcelas === 'function') {
                                    setTimeout(atualizarValoresParcelas, 300);
                                }
                            });
                            $("#servico").val('').focus();
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Atenção",
                                text: data.message || "Ocorreu um erro ao tentar adicionar serviço."
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        var errorMsg = "Ocorreu um erro ao tentar adicionar serviço.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: "error",
                            title: "Atenção",
                            text: errorMsg
                        });
                    }
                });
                return false;
            }
        });

        $("#formAnotacao").validate({
            rules: {
                anotacao: {
                    required: true
                }
            },
            messages: {
                anotacao: {
                    required: 'Insira a anotação'
                }
            },
            submitHandler: function (form) {
                var dados = $(form).serialize();
                $("#divFormAnotacoes").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");

                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/os/adicionarAnotacao",
                    data: dados,
                    dataType: 'json',
                    success: function (data) {
                        if (data.result == true) {
                            $("#divAnotacoes").load("<?php echo current_url(); ?> #divAnotacoes");
                            $("#anotacao").val('');
                            $('#btn-close-anotacao').trigger('click');
                            $("#divFormAnotacoes").html('');
                        } else {
                            Swal.fire({
                                type: "error",
                                title: "Atenção",
                                text: "Ocorreu um erro ao tentar adicionar anotação."
                            });
                        }
                    }
                });
                return false;
            }
        });

        $("#formAnexos").validate({
            submitHandler: function (form) {
                //var dados = $( form ).serialize();
                var dados = new FormData(form);
                $("#form-anexos").hide('1000');
                $("#divAnexos").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/os/anexar",
                    data: dados,
                    mimeType: "multipart/form-data",
                    contentType: false,
                    cache: false,
                    processData: false,
                    dataType: 'json',
                    success: function (data) {
                        if (data.result == true) {
                            $("#divAnexos").load("<?php echo current_url(); ?> #divAnexos");
                            $("#userfile").val('');

                        } else {
                            $("#divAnexos").html('<div class="alert fade in"><button type="button" class="close" data-dismiss="alert">×</button><strong>Atenção!</strong> ' + data.mensagem + '</div>');
                        }
                    },
                    error: function () {
                        $("#divAnexos").html('<div class="alert alert-danger fade in"><button type="button" class="close" data-dismiss="alert">×</button><strong>Atenção!</strong> Ocorreu um erro. Verifique se você anexou o(s) arquivo(s).</div>');
                    }
                });
                $("#form-anexos").show('1000');
                return false;
            }
        });

        $(document).on('click', 'a', function (event) {
            var idProduto = $(this).attr('idAcao');
            var quantidade = $(this).attr('quantAcao');
            var produto = $(this).attr('prodAcao');
            var idOS = "<?php echo $result->idOs ?>"
            if ((idProduto % 1) == 0) {
                $("#divProdutos").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/os/excluirProduto",
                    data: "idProduto=" + idProduto + "&quantidade=" + quantidade + "&produto=" + produto + "&idOs=" + idOS,
                    dataType: 'json',
                    success: function (data) {
                        if (data.result == true) {
                            $("#divProdutos").load("<?php echo current_url(); ?> #divProdutos");
                            $("#divValorTotal").load("<?php echo current_url(); ?> #divValorTotal", function() {
                                if (typeof atualizarValoresParcelas === 'function') {
                                    setTimeout(atualizarValoresParcelas, 300);
                                }
                            });
                            $("#resultado").val('');
                            $("#desconto").val('');

                        } else {
                            Swal.fire({
                                type: "error",
                                title: "Atenção",
                                text: "Ocorreu um erro ao tentar excluir produto."
                            });
                        }
                    }
                });
                return false;
            }

        });

        // Editar preço do serviço
        $(document).on('click', '.editar-servico', function (event) {
            event.preventDefault();
            var idServicosOs = $(this).attr('idAcao');
            var precoAtual = $(this).attr('precoAtual');
            var quantidadeAtual = $(this).attr('quantidadeAtual');
            var detalhesAtual = $(this).attr('detalhesAtual') || '';
            
            // Buscar nome do serviço da linha (remover detalhes se existirem)
            var nomeServico = $(this).closest('tr').find('td:first').find('strong').text() || $(this).closest('tr').find('td:first').text();
            
            // Preencher modal
            $('#servico_nome_editar').val(nomeServico.trim());
            $('#preco_servico_editar').val(precoAtual);
            $('#quantidade_servico_editar').val(quantidadeAtual);
            $('#detalhes_servico_editar').val(detalhesAtual);
            $('#idServicos_os_editar').val(idServicosOs);
            
            // Abrir modal
            $('#modal-editar-preco-servico').modal('show');
        });
        
        // Formulário para editar preço do serviço
        $("#formEditarPrecoServico").validate({
            rules: {
                preco: {
                    required: true
                }
            },
            messages: {
                preco: {
                    required: 'Campo obrigatório.'
                }
            },
            submitHandler: function(form) {
                var dados = $(form).serialize();
                
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/os/editarPrecoServico",
                    data: dados,
                    dataType: 'json',
                    success: function (data) {
                        if (data.result == true) {
                            // Fechar modal
                            $('#modal-editar-preco-servico').modal('hide');
                            
                            // Recarregar tabela de serviços e total
                            $("#divServicos").load("<?php echo current_url(); ?> #divServicos");
                            $("#divValorTotal").load("<?php echo current_url(); ?> #divValorTotal", function() {
                                if (typeof atualizarValoresParcelas === 'function') {
                                    setTimeout(atualizarValoresParcelas, 300);
                                }
                            });
                            $("#resultado").val('');
                            $("#desconto").val('');
                            
                            // Mostrar mensagem de sucesso
                            Swal.fire({
                                type: "success",
                                title: "Sucesso!",
                                text: data.message || "Preço atualizado com sucesso!",
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                type: "error",
                                title: "Erro",
                                text: data.message || "Ocorreu um erro ao atualizar o preço."
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            type: "error",
                            title: "Erro",
                            text: "Ocorreu um erro ao comunicar com o servidor."
                        });
                    }
                });
                return false;
            }
        });

        $(document).on('click', '.servico', function (event) {
            var idServico = $(this).attr('idAcao');
            var idOS = "<?php echo $result->idOs ?>"
            if ((idServico % 1) == 0) {
                $("#divServicos").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/os/excluirServico",
                    data: "idServico=" + idServico + "&idOs=" + idOS,
                    dataType: 'json',
                    success: function (data) {
                        if (data.result == true) {
                            $("#divServicos").load("<?php echo current_url(); ?> #divServicos");
                            $("#divValorTotal").load("<?php echo current_url(); ?> #divValorTotal", function() {
                                if (typeof atualizarValoresParcelas === 'function') {
                                    setTimeout(atualizarValoresParcelas, 300);
                                }
                            });
                            $("#resultado").val('');
                            $("#desconto").val('');

                        } else {
                            Swal.fire({
                                type: "error",
                                title: "Atenção",
                                text: "Ocorreu um erro ao tentar excluir serviço."
                            });
                        }
                    }
                });
                return false;
            }
        });

        $(document).on('click', '.anexo', function (event) {
            event.preventDefault();
            var link = $(this).attr('link');
            var id = $(this).attr('imagem');
            var url = '<?php echo base_url(); ?>index.php/os/excluirAnexo/';
            $("#div-visualizar-anexo").html('<img src="' + link + '" alt="">');
            $("#excluir-anexo").attr('link', url + id);

            $("#download").attr('href', "<?php echo base_url(); ?>index.php/os/downloadanexo/" + id);

        });

        $(document).on('click', '#excluir-anexo', function (event) {
            event.preventDefault();
            var link = $(this).attr('link');
            var idOS = "<?php echo $result->idOs ?>"
            $('#modal-anexo').modal('hide');
            $("#divAnexos").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");

            $.ajax({
                type: "POST",
                url: link,
                dataType: 'json',
                data: "idOs=" + idOS,
                success: function (data) {
                    if (data.result == true) {
                        $("#divAnexos").load("<?php echo current_url(); ?> #divAnexos");
                    } else {
                        Swal.fire({
                            type: "error",
                            title: "Atenção",
                            text: data.mensagem
                        });
                    }
                }
            });
        });

        $(document).on('click', '.anotacao', function (event) {
            var idAnotacao = $(this).attr('idAcao');
            var idOS = "<?php echo $result->idOs ?>"
            if ((idAnotacao % 1) == 0) {
                $("#divAnotacoes").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/os/excluirAnotacao",
                    data: "idAnotacao=" + idAnotacao + "&idOs=" + idOS,
                    dataType: 'json',
                    success: function (data) {
                        if (data.result == true) {
                            $("#divAnotacoes").load("<?php echo current_url(); ?> #divAnotacoes");

                        } else {
                            Swal.fire({
                                type: "error",
                                title: "Atenção",
                                text: "Ocorreu um erro ao tentar excluir Anotação."
                            });
                        }
                    }
                });
                return false;
            }
        });

        $(".datepicker").datepicker({
            dateFormat: 'dd/mm/yy'
        });
        
        // Máscara para campo de entrada
        $('.money').mask('#.##0,00', {reverse: true});

        // Variável para armazenar parcelas
        var parcelas = [];
        var contadorParcela = 0;
        
        // Carregar parcelas existentes se houver
        <?php if (isset($parcelas) && !empty($parcelas)): ?>
        parcelas = <?php echo json_encode(array_map(function($p) { 
            return [
                'numero' => intval($p->numero),
                'dias' => intval($p->dias),
                'valor' => floatval($p->valor),
                'observacao' => $p->observacao ?? ''
            ];
        }, $parcelas)); ?>;
        if (parcelas.length > 0) {
            setTimeout(function() { atualizarTabelaParcelas(); }, 500);
        }
        <?php endif; ?>
        
        // Função para gerar parcelas
        $('#btnGerarParcelas').on('click', function() {
            var input = $('#geradorParcelas').val().trim();
            if (!input) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Digite a configuração de parcelas!'
                });
                return;
            }
            
            parcelas = [];
            contadorParcela = 0;
            
            // Verificar se é formato "Nx" (ex: 6x)
            if (/^\d+x$/i.test(input)) {
                var numParcelas = parseInt(input.replace(/x/i, ''));
                if (numParcelas > 0 && numParcelas <= 24) {
                    for (var i = 1; i <= numParcelas; i++) {
                        parcelas.push({
                            numero: i,
                            dias: i * 30,
                            valor: 0,
                            observacao: ''
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Número de parcelas inválido! Use entre 1 e 24.'
                    });
                    return;
                }
            } else {
                // Verificar se são números separados por espaço (ex: 30 60 90)
                var diasArray = input.split(/\s+/).map(function(d) {
                    return parseInt(d);
                }).filter(function(d) {
                    return !isNaN(d) && d > 0;
                });
                
                if (diasArray.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Formato inválido! Use: 30, 30 60 90 ou 6x'
                    });
                    return;
                }
                
                diasArray.forEach(function(dias, index) {
                    parcelas.push({
                        numero: index + 1,
                        dias: dias,
                        valor: 0,
                        observacao: ''
                    });
                });
            }
            
            atualizarTabelaParcelas();
            $(document).trigger('parcelasGeradas');
        });
        
        // Função para atualizar tabela de parcelas
        function atualizarTabelaParcelas() {
            var tbody = $('#tbodyParcelas');
            tbody.empty();
            
            if (parcelas.length === 0) {
                $('#tabelaParcelasContainer').hide();
                $('#parcelas_json').val('');
                return;
            }
            
            parcelas.forEach(function(parcela, index) {
                var row = $('<tr>');
                row.append('<td>' + parcela.numero + '</td>');
                row.append('<td><input type="number" class="span12 dias-parcela" data-index="' + index + '" value="' + parcela.dias + '" min="1" /></td>');
                row.append('<td><input type="text" class="span12 money valor-parcela" data-index="' + index + '" value="' + (parcela.valor > 0 ? parcela.valor.toFixed(2).replace('.', ',') : '0,00') + '" /></td>');
                row.append('<td><input type="text" class="span12 obs-parcela" data-index="' + index + '" value="' + (parcela.observacao || '') + '" placeholder="Observação..." /></td>');
                row.append('<td><button type="button" class="btn btn-mini btn-danger btn-remover-parcela" data-index="' + index + '"><i class="bx bx-trash"></i></button></td>');
                tbody.append(row);
            });
            
            // Aplicar máscaras
            $('.money').mask('#.##0,00', {reverse: true});
            
            // Eventos de edição (usando delegação para elementos dinâmicos)
            $(document).off('change', '.dias-parcela').on('change', '.dias-parcela', function() {
                var index = $(this).data('index');
                parcelas[index].dias = parseInt($(this).val()) || 0;
                salvarParcelasJSON();
            });
            
            $(document).off('blur', '.valor-parcela').on('blur', '.valor-parcela', function() {
                var index = $(this).data('index');
                var valor = $(this).val().replace('.', '').replace(',', '.');
                parcelas[index].valor = parseFloat(valor) || 0;
                salvarParcelasJSON();
            });
            
            $(document).off('blur', '.obs-parcela').on('blur', '.obs-parcela', function() {
                var index = $(this).data('index');
                parcelas[index].observacao = $(this).val();
                salvarParcelasJSON();
            });
            
            $(document).off('click', '.btn-remover-parcela').on('click', '.btn-remover-parcela', function() {
                var index = $(this).data('index');
                parcelas.splice(index, 1);
                // Renumerar parcelas
                parcelas.forEach(function(p, i) {
                    p.numero = i + 1;
                });
                atualizarTabelaParcelas();
                atualizarValoresParcelas();
            });
            
            $('#tabelaParcelasContainer').show();
            salvarParcelasJSON();
            adicionarBotaoAtualizar();
        }
        
        // Função para salvar parcelas em JSON
        function salvarParcelasJSON() {
            $('#parcelas_json').val(JSON.stringify(parcelas));
        }
        
        // Função para atualizar valores das parcelas automaticamente
        function atualizarValoresParcelas() {
            if (parcelas.length === 0) {
                return;
            }
            
            // Buscar valor total da OS
            var valorTotal = 0;
            
            if ($('#valorTotal').length && $('#valorTotal').val()) {
                var valorStr = $('#valorTotal').val().toString().replace(/[^\d,.-]/g, '').replace('.', '').replace(',', '.');
                valorTotal = parseFloat(valorStr) || 0;
            }
            
            // Se não tem valor total ainda, não atualiza (mas não mostra erro)
            if (valorTotal <= 0) {
                return;
            }
            
            // Calcular valor por parcela (distribuição igual)
            var valorPorParcela = valorTotal / parcelas.length;
            
            // Atualizar valores das parcelas
            parcelas.forEach(function(parcela) {
                parcela.valor = parseFloat(valorPorParcela.toFixed(2));
            });
            
            // Atualizar tabela
            atualizarTabelaParcelas();
        }
        
        // Adicionar botão para atualizar valores manualmente
        function adicionarBotaoAtualizar() {
            if ($('#btnAtualizarValoresParcelas').length === 0 && parcelas.length > 0) {
                var btn = $('<button type="button" class="button btn btn-info btn-mini" id="btnAtualizarValoresParcelas" style="margin-top: 10px;">' +
                    '<span class="button__icon"><i class="bx bx-refresh"></i></span>' +
                    '<span class="button__text2">Atualizar Valores</span></button>');
                $('#tabelaParcelasContainer').append(btn);
                btn.on('click', function() {
                    atualizarValoresParcelas();
                    Swal.fire({
                        icon: 'success',
                        title: 'Valores Atualizados!',
                        text: 'Os valores das parcelas foram atualizados com base no valor total da OS.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            }
        }
        
        // Observar mudanças no valor total
        $(document).on('parcelasGeradas', function() {
            setTimeout(function() {
                atualizarValoresParcelas();
            }, 500);
        });

        $('.editor').trumbowyg({
            lang: 'pt_br',
            semantic: { 'strikethrough': 's', }
        });
        
        // Formulário de Outros Produtos/Serviços
        $('#formOutros').on('submit', function(e) {
            e.preventDefault();
            
            var descricao = $('#descricao_outros').val().trim();
            var preco = $('#preco_outros').val();
            
            // Validações
            if (!descricao) {
                Swal.fire({
                    icon: "error",
                    title: "Atenção",
                    text: "Preencha a descrição."
                });
                return false;
            }
            
            if (!preco || parseFloat(preco.replace(/\./g, '').replace(',', '.')) <= 0) {
                Swal.fire({
                    icon: "error",
                    title: "Atenção",
                    text: "Preço é obrigatório e deve ser maior que zero."
                });
                return false;
            }
            
            var dados = $(this).serialize();
            var isEditando = $(this).data('editando');
            var url = isEditando ? "<?php echo base_url(); ?>index.php/os/editarOutros" : "<?php echo base_url(); ?>index.php/os/adicionarOutros";
            
            $("#divOutros").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
            
            $.ajax({
                type: "POST",
                url: url,
                data: dados,
                dataType: 'json',
                success: function (data) {
                    if (data.result == true) {
                        $("#divOutros").load("<?php echo current_url(); ?> #divOutros");
                        $("#descricao_outros").trumbowyg('empty');
                        $("#preco_outros").val('');
                        $("#idOutrosEditar").val('');
                        $('#formOutros button[type="submit"]').html('<span class="button__icon"><i class="bx bx-plus-circle"></i></span><span class="button__text2">Adicionar</span>');
                        $('#formOutros').data('editando', false);
                        $("#divValorTotal").load("<?php echo current_url(); ?> #divValorTotal", function() {
                            // Atualizar valores das parcelas após adicionar
                            if (typeof atualizarValoresParcelas === 'function') {
                                setTimeout(atualizarValoresParcelas, 300);
                            }
                        });
                        Swal.fire({
                            icon: "success",
                            title: "Sucesso!",
                            text: data.message || (isEditando ? "Item atualizado com sucesso!" : "Item adicionado com sucesso!"),
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Atenção",
                            text: data.message || "Ocorreu um erro ao tentar " + (isEditando ? "atualizar" : "adicionar") + " item."
                        });
                    }
                },
                error: function(xhr, status, error) {
                    var errorMsg = "Ocorreu um erro ao tentar adicionar item.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: "error",
                        title: "Atenção",
                        text: errorMsg
                    });
                }
            });
        });
        
        // Excluir outros produtos/serviços
        $(document).on('click', '.excluir-outros', function() {
            var id = $(this).attr('idAcao');
            var idOs = $('#idOsOutros').val();
            
            Swal.fire({
                title: 'Confirmar exclusão?',
                text: 'Deseja realmente excluir este item?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: "<?php echo base_url(); ?>index.php/os/excluirOutros",
                        data: { id: id, idOs: idOs },
                        dataType: 'json',
                        success: function (data) {
                            if (data.result == true) {
                                $("#divOutros").load("<?php echo current_url(); ?> #divOutros");
                                $("#divValorTotal").load("<?php echo current_url(); ?> #divValorTotal", function() {
                                    if (typeof atualizarValoresParcelas === 'function') {
                                        setTimeout(atualizarValoresParcelas, 300);
                                    }
                                });
                                Swal.fire({
                                    icon: "success",
                                    title: "Sucesso!",
                                    text: data.message || "Item excluído com sucesso!",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Atenção",
                                    text: data.message || "Ocorreu um erro ao tentar excluir item."
                                });
                            }
                        }
                    });
                }
            });
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
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
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
