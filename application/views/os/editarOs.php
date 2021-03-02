<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<link rel="stylesheet" href="<?php echo base_url() ?>assets/trumbowyg/ui/trumbowyg.css">
<script type="text/javascript" src="<?php echo base_url() ?>assets/trumbowyg/trumbowyg.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/trumbowyg/langs/pt_br.js"></script>

<style>
    .ui-datepicker {
        z-index: 99999 !important;
    }

    .trumbowyg-box {
        margin-top: 0;
        margin-bottom: 0;
    }
</style>
 <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
                        echo '<a target="_new" title="Adicionar OS" class="btn btn-mini btn-success" href="' . base_url() . 'index.php/os/adicionar"><i class="fas fa-plus"></i> Adicionar OS</a>';
                    } ?>
                    
                    <a href="<?php echo base_url() ?>index.php/os/visualizar/<?php echo $result->idOs; ?>" title="Visualizar OS" class="btn btn-mini btn-secondary"><i class="fas fa-eye"></i> Visualizar OS</a>
                    
<a target="_blank" title="Imprimir" class="btn btn-mini btn-inverse" href="<?php echo site_url() ?>/os/imprimir/<?php echo $result->idOs; ?>"><i class="fas fa-print"></i> Imprimir A4</a>

<a target="_blank" title="Imprimir Termica" class="btn btn-mini btn-inverse" href="<?php echo site_url() ?>/os/imprimirTermica/<?php echo $result->idOs; ?>"><i class="fas fa-print"></i> Imprimir Termica</a>

<a target="_blank" title="Imprimir Termica 2" class="btn btn-mini btn-inverse" href="<?php echo site_url() ?>/os/imprimirTermica2/<?php echo $result->idOs; ?>"><i class="fas fa-print"></i> Imprimir Termica 2</a>

<?php if ($result->faturado == 0) { ?>
<a href="#modal-faturar" title="Faturar" id="btn-faturar" role="button" data-toggle="modal" class="btn btn-mini btn-danger"><i class="fas fa-cash-register"></i> Faturar</a>
                                            <?php } ?>
                                            
<a href="#modal-whatsapp" title="Enviar WhatsApp" id="btn-whatsapp" role="button" data-toggle="modal" class="btn btn-mini btn-success"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                        
<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-diagnoses"></i>
                </span>
                <h5>Editar OS</h5>
            </div>
          <div class="widget_box_Painel2">
                <div class="span12" id="divProdutosServicos" style=" margin-left: 0">
                    <ul class="nav nav-tabs">
                        <li class="active" id="tabDetalhes"><a href="#tab1" data-toggle="tab">Detalhes da OS</a></li>
                        <li id="tabProdutos"><a href="#tab2" data-toggle="tab">Produtos</a></li>
                        <li id="tabServicos"><a href="#tab3" data-toggle="tab">Serviços</a></li>
                        <li id="tabAnexos"><a href="#tab4" data-toggle="tab">Anexos</a></li>
                        <li id="tabAnotacoes"><a href="#tab5" data-toggle="tab">Anotações</a></li>
                        <li id="tabEquipamentos"><a href="#tab6" data-toggle="tab">Equipamentos</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab1">
                            <div class="span12" id="divCadastrarOs">
                                <form action="<?php echo current_url(); ?>" method="post" id="formOs">
                                    <?php echo form_hidden('idOs', $result->idOs) ?>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <h3>N° OS: #<?php echo $result->idOs; ?></h3>
                                        <div class="span6" style="margin-left: 0">
                                            <label for="cliente">Cliente<span class="required">*</span></label>
                                            <input id="cliente" class="span12" type="text" name="cliente"
                                                   value="<?php echo $result->nomeCliente ?>"/>
                                            <input id="clientes_id" class="span12" type="hidden" name="clientes_id"
                                                   value="<?php echo $result->clientes_id ?>"/>
                                            <input id="valorTotal" type="hidden" name="valorTotal" value=""/>
                                        </div>
                                        <div class="span6">
                                            <label for="tecnico">Técnico / Responsável<span
                                                        class="required">*</span></label>
                                            <input id="tecnico" class="span12" type="text" name="tecnico"
                                                   value="<?php echo $result->nome ?>"/>
                                            <input id="usuarios_id" class="span12" type="hidden" name="usuarios_id"
                                                   value="<?php echo $result->usuarios_id ?>"/>
                                        </div>
                                    </div>
                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span3">
                                            <label for="status">Status<span class="required">*</span></label>
                                            <select class="span12" name="status" id="status" value="">
<option <?php if($result->status == 'Orçamento'){echo 'selected';} ?> value="Orçamento">Orçamento</option>
<option <?php if($result->status == 'Orçamento Concluido'){echo 'selected';} ?> value="Orçamento Concluido">Orçamento Concluido</option>
<option <?php if($result->status == 'Orçamento Aprovado'){echo 'selected';} ?> value="Orçamento Aprovado">Orçamento Aprovado</option>
<option <?php if($result->status == 'Aguardando Peças'){echo 'selected';} ?> value="Aguardando Peças">Aguardando Peças</option>
<option <?php if($result->status == 'Em Andamento'){echo 'selected';} ?> value="Em Andamento">Em Andamento</option>
<option <?php if($result->status == 'Serviço Concluido'){echo 'selected';} ?> value="Serviço Concluido">Serviço Concluido</option>
<option <?php if($result->status == 'Sem Reparo'){echo 'selected';} ?> value="Sem Reparo">Sem Reparo</option>
<option <?php if($result->status == 'Não Autorizado'){echo 'selected';} ?> value="Não Autorizado">Não Autorizado</option>
<option <?php if($result->status == 'Contato sem Sucesso'){echo 'selected';} ?> value="Contato sem Sucesso">Contato sem Sucesso</option>
<option <?php if($result->status == 'Cancelado'){echo 'selected';} ?> value="Cancelado">Cancelado</option>
<option <?php if($result->status == 'Pronto-Despachar'){echo 'selected';} ?> value="Pronto-Despachar">Pronto-Despachar</option>
<option <?php if($result->status == 'Enviado'){echo 'selected';} ?> value="Enviado">Enviado</option>
<option <?php if($result->status == 'Aguardando Envio'){echo 'selected';} ?> value="Aguardando Envio">Aguardando Envio</option>
<option <?php if($result->status == 'Aguardando Entrega Correio'){echo 'selected';} ?> value="Aguardando Entrega Correio">Aguardando Entrega Correio</option>
<option <?php if($result->status == 'Entregue - A Receber'){echo 'selected';} ?> value="Entregue - A Receber">Entregue - A Receber</option>
<option <?php if($result->status == 'Garantia'){echo 'selected';} ?> value="Garantia">Garantia</option>
<option <?php if($result->status == 'Abandonado'){echo 'selected';} ?> value="Abandonado">Abandonado</option>
<option <?php if($result->status == 'Comprado pela Loja'){echo 'selected';} ?> value="Comprado pela Loja">Comprado pela Loja</option>
<option <?php if($result->status == 'Entregue - Faturado'){echo 'selected';} ?> value="Entregue - Faturado">Entregue - Faturado</option>
                                            </select>
                                            <label for="rastreio">Rastreio</label>
                                          <input name="rastreio" type="text" class="span12" id="rastreio" maxlength="13" value="<?php echo $result->rastreio ?>"  />
										  <a href="https://www.linkcorreios.com.br/<?php echo $result->rastreio ?>" title="Rastrear" target="_new" class="btn btn-warning"><i class="fas fa-envelope"></i> Rastrear</a>
                                        <button class="btn btn-primary" title="Atualizar" id="btnContinuar"><i class="fas fa-sync-alt"></i> Atualizar</button>
                                        </div>
                                        
                                        <div class="span3">
                                            <label for="dataInicial">Data de Entrtada<span class="required">*</span></label>
<input id="dataInicial" autocomplete="off" class="span12 datepicker" type="text" name="dataInicial" value="<?php echo date('d/m/Y', strtotime($result->dataInicial)); ?>" />
											<label for="dataFinal">Data Final<span class="required">*</span></label>
<input id="dataFinal" autocomplete="off" class="span12 datepicker" type="text" name="dataFinal" value="<?php echo date('d/m/Y', strtotime($result->dataFinal)); ?>" />
                                      </div>
                                      <div class="span3">Nº Série
                             <input id="serial" type="text" class="span12" name="serial" maxlength="30" value="<?php echo $result->serial ?>" />
                             <label for="dataSaida">Data de Saida</label>
                             <input id="dataSaida" autocomplete="off" class="span12 datepicker" type="text" name="dataSaida" value="<?php echo $result->dataSaida ?>" />
                                        </div>
                                        
                                        <div class="span3">
                                        <label for="marca">Marca</label>
                                          <input id="marca" type="text" class="span12" name="marca" maxlength="30" value="<?php echo $result->marca ?>" />
                                        <label for="garantia">Garantia até</label>
                                            <input id="garantia" type="text" class="span12 datepicker" name="garantia" value="<?php echo $result->garantia ?>" />
                                        </div>
                                        
                                  </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="descricaoProduto">
                                            <h4>Descrição Produto/Serviço</h4>
                                        </label>
                                        <textarea class="span12 editor" name="descricaoProduto" id="descricaoProduto"
                                                  cols="30" rows="5"><?php echo $result->descricaoProduto ?></textarea>
                                    </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="defeito">
                                            <h4>Problema Informado</h4>
                                        </label>
                                        <textarea class="span12 editor" name="defeito" id="defeito" cols="30"
                                                  rows="5"><?php echo $result->defeito ?></textarea>
                                    </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="observacoes">
                                            <h4>Observações</h4>
                                        </label>
                                        <textarea class="span12 editor" name="observacoes" id="observacoes" cols="30"
                                                  rows="5"><?php echo $result->observacoes ?></textarea>
                                    </div>
                                    <div class="span6" style="padding: 1%; margin-left: 0">
                                        <label for="laudoTecnico">
                                            <h4>Relatório Técnico</h4>
                                        </label>
                                        <textarea class="span12 editor" name="laudoTecnico" id="laudoTecnico" cols="30"
                                                  rows="5"><?php echo $result->laudoTecnico ?></textarea>
			</div>
            <!-- Botoes de Ação -->
	<div class="span12" style="padding: 0px; margin-left: 0">
    <div align="center" style="padding: 3px">
    <?php if ($result->faturado == 0) { ?>
    <a href="#modal-faturar" title="Faturar" id="btn-faturar" role="button" data-toggle="modal" class="btn btn-danger"><i class="fas fa-cash-register"></i> Faturar</a><?php } ?>
    
	<button class="btn btn-primary" title="Atualizar" id="btnContinuar"><i class="fas fa-sync-alt"></i> Atualizar</button>

	<a href="<?php echo base_url() ?>index.php/os/visualizar/<?php echo $result->idOs; ?>" title="Visualizar OS" class="btn btn-secondary"><i class="fas fa-eye"></i> Visualizar OS</a>
    
	<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
		echo '<a target="_blank" title="Adicionar OS" class="btn btn-success" href=' . base_url() . 'index.php/os/adicionar><i class="fas fa-plus"></i> Adicionar OS</a>';} ?>
                        
	<a href="#modal-whatsapp" title="Enviar WhatsApp" id="btn-whatsapp" role="button" data-toggle="modal" class="btn btn-success"><i class="fab fa-whatsapp"></i> WhatsApp</a>
    </div>
    <div align="center" style="padding: 3px">
    <a target="_blank" title="Imprimir" class="btn btn-inverse" href="<?php echo site_url() ?>/os/imprimir/<?php echo $result->idOs; ?>"><i class="fas fa-print"></i> Imprimir A4</a>
    
    <a target="_blank" title="Imprimir Termica" class="btn btn-inverse" href="<?php echo site_url() ?>/os/imprimirTermica/<?php echo $result->idOs; ?>"><i class="fas fa-print"></i> Imprimir Termica</a>
    
    <a target="_blank" title="Imprimir Termica 2" class="btn btn-inverse" href="<?php echo site_url() ?>/os/imprimirTermica2/<?php echo $result->idOs; ?>"><i class="fas fa-print"></i> Imprimir Termica 2</a>
    </div>
    
    <div align="center" style="padding: 3px">
    <a href="<?php echo base_url() ?>index.php/os" class="btn btn-warning"><i class="fas fa-backward"></i> Voltar</a>
    </div>
    </div>
    <!-- Fim Botoes de Ação -->
			</form>
            </div>
            </div>
                        
                        <!--Produtos-->
                        <div class="tab-pane" id="tab2">
                        <div class="span12 well" style="padding: 1%; margin-left: 0">
                                <form id="formProdutos" action="<?php echo base_url() ?>index.php/os/adicionarProduto"
                                      method="post">
                                    <div class="span6">
                                        <input type="hidden" name="idProduto" id="idProduto"/>
                                        <input type="hidden" name="idOsProduto" id="idOsProduto"
                                               value="<?php echo $result->idOs; ?>"/>
                                        <input type="hidden" name="estoque" id="estoque" value=""/>
                                        <label for="">Produto</label>
                                        <input type="text" class="span12" name="produto" id="produto"
                                               placeholder="Digite o nome do produto"/>
                                    </div>
                                    <div class="span2">
                                        <label for="">Preço</label>
                                        <input type="text" placeholder="Preço" id="preco" name="preco"
                                               class="span12 money"/>
                                    </div>
                                    <div class="span2">
                                        <label for="">Quantidade</label>
                                        <input type="text" placeholder="Quantidade" id="quantidade" name="quantidade"
                                               class="span12"/>
                                    </div>
                                    <div class="span2">
                                        <label for="">.</label>
                                        <button class="btn btn-success span12" id="btnAdicionarProduto"><i
                                                    class="fas fa-plus"></i> Adicionar
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="widget-box" id="divProdutos">
                            <div class="widget_content nopadding">
                                <table width="100%" class="table_p" id="tblProdutos">
                                    <thead>
                                    <tr>
                                       		<th width="10%">Cod. Produto</th>
                                            <th width="10%">Cod. Barras</th>
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
											echo '<td><div align="center">' . $p->idProdutos . '</td>';
                                            echo '<td><div align="center">' . $p->codDeBarra . '</td>';
                                            echo '<td>' . $p->descricao . '</td>';
                                            echo '<td><div align="center">' . $p->quantidade . '</td>';
                                            echo '<td><div align="center">R$: ' . ($p->preco ?: $p->precoVenda)  . '</td>';
                                            echo '<td><div align="center"><a href="" idAcao="' . $p->idProdutos_os . '" prodAcao="' . $p->idProdutos . '" quantAcao="' . $p->quantidade . '" title="Excluir Produto" class="btn btn-danger"><i class="fas fa-trash-alt"></i></a></td>';
                                            echo '<td><div align="center">R$: ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                                            echo '</tr>';
                                    } ?>
                                    <tr>
                                        <td colspan="6" style="text-align: right"><strong>Total:</strong></td>
                                        <td><strong>R$
                                                <?php echo number_format($total, 2, ',', '.'); ?><input type="hidden"
                                                                                                        id="total-venda"
                                                                                                        value="<?php echo number_format($total, 2); ?>"></strong>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        </div>
                                
                        <!--Serviços-->
                        <div class="tab-pane" id="tab3">
            			<div class="span12 well" style="padding: 1%; margin-left: 0">
                                    <form id="formServicos" action="<?php echo base_url() ?>index.php/os/adicionarServico" method="post">
                                        <div class="span6">
                                            <input type="hidden" name="idServico" id="idServico" />
                                            <input type="hidden" name="idOsServico" id="idOsServico" value="<?php echo $result->idOs; ?>" />
                                            <label for="">Serviço</label>
                                            <input type="text" class="span12" name="servico" id="servico" placeholder="Digite o nome do serviço" />
                                        </div>
                                        <div class="span2">
                                            <label for="">Preço</label>
                                            <input type="text" placeholder="Preço" id="preco_servico" name="preco" class="span12 money" />
                                        </div>
                                        <div class="span2">
                                            <label for="">Quantidade</label>
                                            <input type="text" placeholder="Quantidade" id="quantidade_servico" name="quantidade" class="span12" />
                                        </div>
                                        <div class="span2">
                                            <label for="">.</label>
                                            <button class="btn btn-success span12"><i class="fas fa-plus"></i> Adicionar</button>
                                        </div>
                                    </form>
                          </div>
                        <div class="widget-box" id="divServicos">
            			<div class="widget_content nopadding">
									<table width="100%" class="table_p">
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
                                                echo '<tr>';
                                                echo '<td>' . $s->nome . '</td>';
                                                echo '<td><div align="center">' . ($s->quantidade ?: 1) . '</div></td>';
                                                echo '<td><div align="center">R$ ' . $preco  . '</div></td>';
                                                echo '<td><div align="center"><span idAcao="' . $s->idServicos_os . '" title="Excluir Serviço" class="btn btn-danger servico"><i class="fas fa-trash-alt"></i></span></div></td>';
                                                echo '<td><div align="center">R$ ' . number_format($subtotals, 2, ',', '.') . '</div></td>';
                                                echo '</tr>';
                                            } ?>
                                            <tr>
                                                <td colspan="4" style="text-align: right"><strong>Total:</strong></td>
                                                <td><strong>R$
                                                <?php echo number_format($totals, 2, ',', '.'); ?><input type="hidden" id="total-servico" value="
												<?php echo number_format($totals, 2); ?>"></strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                          </div>
                          </div>
                      </div>
                                
                        <!--Anexos-->
                        <div class="tab-pane" id="tab4">
                            <div class="span12 well" style="padding: 1%; margin-left: 0" id="form-anexos">
                                    <form id="formAnexos" enctype="multipart/form-data" action="javascript:;" accept-charset="utf-8" s method="post">
                                        <div class="span10">
                                            <input type="hidden" name="idOsServico" id="idOsServico" value="<?php echo $result->idOs; ?>" />
                                            <label for="">Anexo</label>
                                            <input type="file" class="span12" name="userfile[]" multiple="multiple" />
                                        </div>
                                        <div class="span2">
                                            <label for="">.</label>
                                            <button class="btn btn-success span12"><i class="fas fa-paperclip"></i> Anexar</button>
                                        </div>
                                    </form>
                          </div>
                          <div class="span12" id="divAnexos" style="margin-left: 0">
                          
                                    <?php
                                    foreach ($anexos as $a) {
                                        if ($a->thumb == null) {
                                            $thumb = base_url() . 'assets/img/icon-file.png';
                                            $link = base_url() . 'assets/img/icon-file.png';
                                        } else {
                                            $thumb = $a->url . '/thumbs/' . $a->thumb;
                                            $link = $a->url . '/' . $a->anexo;
                                        }
                                        echo '<div class="span3" style="min-height: 230px; margin-left: 0; overflow-y:hiden;  padding: 5px;">
										<a style="min-height: 200px; border: 1px solid #bbbbbb;" href="#modal-anexo" imagem="' . $a->idAnexos . '" link="' . $link . '" role="button" class="btn anexo span12" data-toggle="modal">
										<img src="' . $thumb . '" alt="">
										</a>
										<span>' . $a->anexo . '</span>
										</div>';} ?>
                                </div>
                            </div>
                        
						<!--Anotações-->
                        <div class="tab-pane" id="tab5">
                        <div class="span12 well" style="padding: 1%; margin-left: 0">
            			<div class="span12" style="padding: 1%; margin-left: 0">
                        <a href="#modal-anotacao" id="btn-anotacao" role="button" data-toggle="modal" class="btn btn-success"><i class="fas fa-plus"></i> Adicionar anotação</a>
                        </div>
                        </div>
            			<div class="widget-box" id="divAnotacoes">
            			<div class="widget_content nopadding">
            			<table width="100%" class="table_p">
                                        <thead>
                                            <tr>
                                                <th width="73%">Anotação</th>
                                                <th width="20%">Data/Hora</th>
                                                <th width="7%">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
											// DEFINE O FUSO HORARIO COMO O HORARIO DE BRASILIA
											date_default_timezone_set('America/Sao_Paulo');
											// CRIA UMA VARIAVEL E ARMAZENA A HORA ATUAL DO FUSO-HORÀRIO DEFINIDO (BRASÍLIA)
											$dataLocal = date('d/m/Y H:i:s', time());
                                            foreach ($anotacoes as $a) {
                                                echo '<tr>';
                                                echo '<td>' . $a->anotacao . '</td>';
                                                echo '<td><div align="center">' . date('d/m/Y H:i:s', strtotime($a->data_hora))  . '</div></td>';
                                                echo '<td><div align="center"><span idAcao="' . $a->idAnotacoes . '" title="Excluir Anotação" class="btn btn-danger anotacao"><i class="fas fa-trash-alt"></i></span></div></td>';
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
                        
                        <!--Equipamentos-->
                       	<div class="tab-pane" id="tab6">
                        <div class="span12 well" style="padding: 1%; margin-left: 0">
                        <div class="span12" style="padding: 1%; margin-left: 0">
                        <a href="#modal-equipamento" id="btn-equipamento" role="button" data-toggle="modal" class="btn btn-success"><i class="fas fa-plus"></i> Adicionar Equipamento</a>
                        </div>
                        </div>
                            <div class="widget-box" id="divEquipamento">
                            <div class="widget_content nopadding">
                                    <table  width="100%" class="table_p">
                                        <thead>
                                            <tr>
                                                <th>Equipamento</th>
                                                <th>Modelo/Cor</th>
                                                <th>Nº Série</th>
                                                <th>Voltagem</th>
                                                <th>Observação</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($equipamento as $a) {
                                                echo '<tr>';
                                                echo '<td><div align="center">' . $a->equipamento . '</div></td>';
                                                echo '<td><div align="center">' . $a->modelo . '</div></td>';
												echo '<td><div align="center">' . $a->num_serie . '</div></td>';
												echo '<td><div align="center">' . $a->voltagem . '</div></td>';
												echo '<td><div align="center">' . $a->observacao . '</div></td>';
                                                echo '<td><div align="center"><span idAcao="' . $a->idEquipamento . '" title="Excluir Equipamento" class="btn btn-danger equipamento"><i class="fas fa-trash-alt"></i></span></div></td>';
                                                echo '</tr>';
                                            }
                                            if (!$equipamento) {
                                                echo '<tr><td colspan="6">Nenhum Equipamento cadastrado</td></tr>';
                                            }

                                            ?>
                                        </tbody>
                                    </table>
                 </div></div></div></div></div>&nbsp
                 </div></div></div></div></div>
                        <!-- Fim tab Equipamentos -->
                 

<!-- Modal visualizar anexo -->
<div id="modal-anexo" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal_header_anexos">
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
<div id="modal-anotacao" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="#" method="POST" id="formAnotacao">
        <div class="modal_header_anexos">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Adicionar Anotação</h3>
        </div>
        <div class="modal-body">
        <div class="span12">
        <textarea class="span12 editor" name="anotacao" id="anotacao" cols="30" rows="3"></textarea>
        <input type="hidden" name="os_id" value="<?php echo $result->idOs; ?>">
        </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true" id="btn-close-anotacao">Fechar</button>
            <button class="btn btn-primary">Adicionar</button>
        </div>
    </form>
</div>

<!-- Modal cadastro Equipamentos -->
<div id="modal-equipamento" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="#" method="POST" id="formEquipamento">
        <div class="modal_header_anexos">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Adicionar Equipamento</h3>
        </div>
        <div class="modal-body">
          <div class="span6" style="margin-left: 0">
                <label for="equipamento">Equipamento<span class="required">*</span></label>
                <input name="equipamento" type="text" class="span12" id="equipamento" value="" />
          </div>
          <div class="span6">
                <label for="modelo">Modelo/Cor</label>
                <input name="modelo" type="text" class="span12" id="equipamento" value="" />
          </div>
          <div class="span6" style="margin-left: 0">
                <label for="num_serie">Nº Série</label>
                <input name="num_serie" type="text" class="span12" id="equipamento" value="" />
          </div>
          <div class="span6">
                <label for="voltagem">Voltagem</label>
                <input name="voltagem" type="text" class="span12" id="equipamento" value="" />
          </div>
            <div class="span12" style="margin-left: 0">
              <label for="observacao">Observação</label>
                <input name="observacao" type="text" class="span12" id="equipamento" value="" />
                <input type="hidden" name="os_id" value="<?php echo $result->idOs; ?>">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true" id="btn-close-equipamento">Fechar</button>
            <button class="btn btn-primary">Adicionar</button>
        </div>
    </form>
</div>

<!-- Modal Faturar-->
<div id="modal-faturar" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form id="formFaturar" action="<?php echo current_url() ?>" method="post">
        <div class="modal_header_anexos">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Faturar OS</h3>
        </div>
        <div class="modal-body">
            <div class="span12 alert alert-info" style="margin-left: 0"> Obrigatório o preenchimento dos campos com asterisco.</div>
            <div class="span12" style="margin-left: 0">
                <label for="descricao">Descrição</label>
                <input class="span12" id="descricao" type="text" name="descricao" value="Fatura de OS Nº: <?php echo $result->idOs; ?> " />
            </div>
            <div class="span12" style="margin-left: 0">
                <div class="span12" style="margin-left: 0">
                    <label for="cliente">Cliente*</label>
                    <input class="span12" id="cliente" type="text" name="cliente" value="<?php echo $result->nomeCliente ?>" />
                    <input type="hidden" name="clientes_id" id="clientes_id" value="<?php echo $result->clientes_id ?>">
                    <input type="hidden" name="os_id" id="os_id" value="<?php echo $result->idOs; ?>">
                </div>
            </div>
            <div class="span12" style="margin-left: 0">
                <div class="span4" style="margin-left: 0">
                    <label for="valor">Valor*</label>
                    <input type="hidden" id="tipo" name="tipo" value="receita" />
                    <input class="span12 money" id="valor" type="text" name="valor"
                           value="<?php echo number_format($total, 2); ?> "/>
                </div>
                <div class="span4">
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
                        <input class="span12 datepicker" autocomplete="off" id="recebimento" type="text" name="recebimento" />
                    </div>
                    <div class="span6">
                        <label for="formaPgto">Forma Pgto</label>
                        <select name="formaPgto" id="formaPgto" class="span12">
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Cartão de Crédito">Cartão de Crédito</option>
                            <option value="Boleto">Boleto</option>
                            <option value="Depósito">Depósito</option>
                            <option value="Débito">Débito</option>
                            <option value="Pix">Pix</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true" id="btn-cancelar-faturar">Cancelar</button>
                <button class="btn btn-primary">Faturar</button>
            
            </div>
</div>
    </form>
</div>

<!-- Modal WhatsApp-->
<div id="modal-whatsapp" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo current_url() ?>" method="post">
        <div class="modal_header_anexos">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <div align="center">
              <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
												$zapnumber = preg_replace("/[^0-9]/", "", $result->celular_cliente);
												$totalOS = number_format($totals + $total, 2, ',', '.');
                        echo '<a title="Enviar Por WhatsApp" class="btn btn-success" id="enviarWhatsApp" target="_blank" href="https://web.whatsapp.com/send?phone=55' . $zapnumber . '&text=Prezado(a)%20*' . $result->nomeCliente . '*.%0d%0a%0d%0aSua%20*O.S%20' . $result->idOs . '*%20referente%20ao%20equipamento%20*' . strip_tags($result->descricaoProduto) . '*%20foi%20atualizada%20para%20*' . $result->status . '*.%0d%0a%0d%0a' . strip_tags($result->defeito) . '%0d%0a%0d%0a' . strip_tags($result->laudoTecnico) . '%0d%0a%0d%0a' . strip_tags($result->observacoes) . '%0d%0a%0d%0aValor%20Total%20*R$&#58%20'. $totalOS . '*%0d%0a%0d%0a' . $configuration['whats_app1'] .'%0d%0a%0d%0aAtenciosamente,%20*' . $configuration['whats_app2'] . '*%20-%20*' . $configuration['whats_app3'] .'*%0d%0a%0d%0aAcesse%20a%20área%20do%20cliente%20pelo%20link%0d%0a'. $configuration['whats_app4'] .'%0d%0aE%20utilize%20estes%20dados%20para%20fazer%20Log-in%0d%0aEmail:%20*' . strip_tags($result->email) . '*%0d%0aSenha:%20*' . strip_tags($result->senha) . '*%0d%0aVocê%20poderá%20edita-la%20no%20menu%20*Minha%20Conta*"><i class="fab fa-whatsapp"></i> Enviar WhatsApp</a>';} ?>
              
            </div>
        </div>
        <div class="modal-body">
        <div class="span12" style="margin-left: 0">
          <font size='2'>Prezado(a) <b><?php echo $result->nomeCliente ?></b>
          <br><br>
        <div>Sua <b>O.S <?php echo $result->idOs ?></b> referente ao equipamento <b><?php echo $result->descricaoProduto ?></b> foi atualizada para <b><?php echo $result->status ?></b></div>
        <br>
        <?php echo $result->defeito ?>
        <br><br>
        <?php echo $result->laudoTecnico ?>
        <br><br>
        <?php echo $result->observacoes ?>
        <br><br>
        <div>Valor Total <b>R$: <?php echo number_format($totals + $total, 2, ',', '.') ?></b></div>
        <br>
		<?php echo $configuration['whats_app1'] ?>
        <br><br>
        <div>Atenciosamente <b><?php echo $configuration['whats_app2'] ?></b> - <b><?php echo $configuration['whats_app3'] ?></b></div>
        <br>
        <div>Acesse a área do cliente pelo link <font color='#1E90FF'><?php echo $configuration['whats_app4'] ?></font></div>
        <div>E utilize estes dados para fazer Log-In.
        <br>Email: <b><?php echo $result->email ?></b>
        <br>Senha: <b><?php echo $result->senha ?></b></div>
        <div>Você poderá edita-los no menu <b>Minha Conta</b></div>
        <br>
        </font>
      </div>
    </form>
</div>


<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<script src="<?php echo base_url(); ?>assets/js/maskmoney.js"></script>

<script type="text/javascript">
    $(document).ready(function() {

        $(".money").maskMoney();

        $('#recebido').click(function(event) {
            var flag = $(this).is(':checked');
            if (flag == true) {
                $('#divRecebimento').show();
            } else {
                $('#divRecebimento').hide();
            }
        });

        $(document).on('click', '#btn-faturar', function(event) {
            event.preventDefault();
            valor = $('#total-venda').val();
            total_servico = $('#total-servico').val();
            valor = valor.replace(',', '');
            total_servico = total_servico.replace(',', '');
            total_servico = parseFloat(total_servico);
            valor = parseFloat(valor);
            $('#valor').val(valor + total_servico);
        });

        $(document).on('click', '#btn-whatsapp', function(event) {
            event.preventDefault();
            valor = $('#total-venda').val();
            total_servico = $('#total-servico').val();
            valor = valor.replace(',', '');
            total_servico = total_servico.replace(',', '');
            total_servico = parseFloat(total_servico);
            valor = parseFloat(valor);
            $('#valor').val(valor + total_servico);
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
            submitHandler: function(form) {
                var dados = $(form).serialize();
                $('#btn-cancelar-faturar').trigger('click');
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/os/faturar",
                    data: dados,
                    dataType: 'json',
                    success: function(data) {
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
            submitHandler: function(form) {
                var dados = $(form).serialize();
                $('#btn-cancelar-faturar').trigger('click');
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/os/faturar",
                    data: dados,
                    dataType: 'json',
                    success: function(data) {
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
        });

        $("#produto").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteProduto",
            minLength: 2,
            select: function(event, ui) {
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
            select: function(event, ui) {
                $("#idServico").val(ui.item.id);
                $("#preco_servico").val(ui.item.preco);
                $("#quantidade_servico").focus();
            }
        });


        $("#cliente").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteCliente",
            minLength: 2,
            select: function(event, ui) {
                $("#clientes_id").val(ui.item.id);
            }
        });

        $("#tecnico").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteUsuario",
            minLength: 2,
            select: function(event, ui) {
                $("#usuarios_id").val(ui.item.id);
            }
        });

        $("#termoGarantia").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteTermoGarantia",
            minLength: 1,
            select: function(event, ui) {
                if (ui.item.id) {
                    $("#garantias_id").val(ui.item.id);
                }
            }
        });

        $('#termoGarantia').on('change', function() {
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
            highlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').addClass('error');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error');
                $(element).parents('.control-group').addClass('success');
            }
        });

        $("#formProdutos").validate({
            rules: {
                quantidade: {
                    required: true
                }
            },
            messages: {
                quantidade: {
                    required: 'Insira a quantidade'
                }
            },
            submitHandler: function(form) {
                var quantidade = parseInt($("#quantidade").val());
                var estoque = parseInt($("#estoque").val());
                
                <?php if(!$configuration['control_estoque']){ 
                    echo 'estoque = 1000000';
                }; ?>

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
                        success: function(data) {
                            if (data.result == true) {
                                $("#divProdutos").load("<?php echo current_url(); ?> #divProdutos");
                                $("#quantidade").val('');
                                $("#preco").val('');
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
                }
            },
            messages: {
                servico: {
                    required: 'Insira um serviço'
                }
            },
            submitHandler: function(form) {
                var dados = $(form).serialize();

                $("#divServicos").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/os/adicionarServico",
                    data: dados,
                    dataType: 'json',
                    success: function(data) {
                        if (data.result == true) {
                            $("#divServicos").load("<?php echo current_url(); ?> #divServicos");
                            $("#quantidade_servico").val('');
                            $("#preco_servico").val('');
                            $("#servico").val('').focus();
                        } else {
                            Swal.fire({
                                type: "error",
                                title: "Atenção",
                                text: "Ocorreu um erro ao tentar adicionar serviço."
                            });
                        }
                    }
                });
                return false;
            }
        });

        $("#formEquipamento").validate({
            rules: {
                equipamento: {
                    required: true
                }
            },
            messages: {
                equipamento: {
                    required: 'Insira o Equipamento'
                }
            },
            submitHandler: function(form) {
                var dados = $(form).serialize();
                $("#divFormEquipamento").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");

                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/os/adicionarEquipamento",
                    data: dados,
                    dataType: 'json',
                    success: function(data) {
                        if (data.result == true) {
                            $("#divEquipamento").load("<?php echo current_url(); ?> #divEquipamento");
                            $("#equipamento").val('');
                            $('#btn-close-equipamento').trigger('click');
                            $("#divFormEquipamento").html('');
                        } else {
                            Swal.fire({
                                type: "error",
                                title: "Atenção",
                                text: "Ocorreu um erro ao tentar adicionar Equipamento."
                            });
                        }
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
            submitHandler: function(form) {
                var dados = $(form).serialize();
                $("#divFormAnotacoes").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");

                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/os/adicionarAnotacao",
                    data: dados,
                    dataType: 'json',
                    success: function(data) {
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
            submitHandler: function(form) {
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
                    success: function(data) {
                        if (data.result == true) {
                            $("#divAnexos").load("<?php echo current_url(); ?> #divAnexos");
                            $("#userfile").val('');

                        } else {
                            $("#divAnexos").html('<div class="alert fade in"><button type="button" class="close" data-dismiss="alert">×</button><strong>Atenção!</strong> ' + data.mensagem + '</div>');
                        }
                    },
                    error: function() {
                        $("#divAnexos").html('<div class="alert alert-danger fade in"><button type="button" class="close" data-dismiss="alert">×</button><strong>Atenção!</strong> Ocorreu um erro. Verifique se você anexou o(s) arquivo(s).</div>');
                    }
                });
                $("#form-anexos").show('1000');
                return false;
            }
        });

        $(document).on('click', 'a', function(event) {
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
                    success: function(data) {
                        if (data.result == true) {
                            $("#divProdutos").load("<?php echo current_url(); ?> #divProdutos");

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

        $(document).on('click', '.servico', function(event) {
            var idServico = $(this).attr('idAcao');
			var idOS = "<?php echo $result->idOs ?>"
            if ((idServico % 1) == 0) {
                $("#divServicos").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/os/excluirServico",
					data: "idServico=" + idServico + "&idOs=" + idOS,
                    data: "idServico=" + idServico,
                    dataType: 'json',
                    success: function(data) {
                        if (data.result == true) {
                            $("#divServicos").load("<?php echo current_url(); ?> #divServicos");

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

        $(document).on('click', '.anexo', function(event) {
            event.preventDefault();
            var link = $(this).attr('link');
            var id = $(this).attr('imagem');
            var url = '<?php echo base_url(); ?>index.php/os/excluirAnexo/';
            $("#div-visualizar-anexo").html('<img src="' + link + '" alt="">');
            $("#excluir-anexo").attr('link', url + id);

            $("#download").attr('href', "<?php echo base_url(); ?>index.php/os/downloadanexo/" + id);

        });

        $(document).on('click', '#excluir-anexo', function(event) {
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
                success: function(data) {
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

        $(document).on('click', '.equipamento', function(event) {
            var idEquipamento = $(this).attr('idAcao');
			var idOS = "<?php echo $result->idOs ?>"
            if ((idEquipamento % 1) == 0) {
                $("#divEquipamento").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/os/excluirEquipamento",
					data: "idEquipamento=" + idEquipamento + "&idOs=" + idOS,
                    dataType: 'json',
                    success: function(data) {
                        if (data.result == true) {
                            $("#divEquipamento").load("<?php echo current_url(); ?> #divEquipamento");

                        } else {
                            Swal.fire({
                                type: "error",
                                title: "Atenção",
                                text: "Ocorreu um erro ao tentar excluir Equipamento."
                            });
                        }
                    }
                });
                return false;
            }
        });

        $(document).on('click', '.anotacao', function(event) {
            var idAnotacao = $(this).attr('idAcao');
			var idOS = "<?php echo $result->idOs ?>"
            if ((idAnotacao % 1) == 0) {
                $("#divAnotacoes").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/os/excluirAnotacao",
					data: "idAnotacao=" + idAnotacao + "&idOs=" + idOS,
                    dataType: 'json',
                    success: function(data) {
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

        $('.editor').trumbowyg({
            lang: 'pt_br'
        });
    });
</script>