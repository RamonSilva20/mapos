
<link rel="stylesheet" href="<?php echo base_url();?>js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url()?>js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="<?php echo base_url()?>js/jquery.validate.js"></script>
<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="icon-tags"></i>
                </span>
                <h5>Editar OS</h5>
            </div>
            <div class="widget-content nopadding">


                <div class="span12" id="divProdutosServicos" style=" margin-left: 0">
                    <ul class="nav nav-tabs">
                        <li class="active" id="tabDetalhes"><a href="#tab1" data-toggle="tab">Detalhes da OS</a></li>
                        <li id="tabServicos"><a href="#tab3" data-toggle="tab">Serviços</a></li>
                        <li id="tabCompras"><a href="#tab5" data-toggle="tab">Compras</a></li>
                        <li id="tabHistorico"><a href="#tab6" data-toggle="tab">Histórico</a></li>
                        <li id="tabAnexos"><a href="#tab4" data-toggle="tab">Anexos</a></li>
                        <li id="tabProdutos"><a href="#tab2" data-toggle="tab">Produtos</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab1">

                            <div class="span12" id="divCadastrarOs">
                                
                                <form action="<?php echo current_url(); ?>" method="post" id="formOs">
                                    <?php echo form_hidden('idOs',$result->idOs) ?>
                                    
                                    <div class="span12 tblHistorico" id="tblHistorico" style="padding: 0 1%; margin-left: 0">
                                    </div>
                                    <div class="span12" style="padding: 0 1% 1% 1%; margin-left: 0">
                                        <h5>Anotações Gerais</h5>
                                        
                                        <textarea class="span12" name="anotacoesOs" id="anotacoesOs" rows="3" maxlength="1024"><?php echo $result->anotacoesOs ?></textarea>
                                        <span></span>
                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <h4>#Protocolo: <?php echo $result->idOs ?></h4>
                                        
                                        <div class="span4" style="margin-left: 0">
                                            <label for="cliente">Cliente<span class="required">*</span></label>
                                            <input id="cliente" class="span12" type="text" name="cliente" value="<?php echo $result->nomeCliente ?>"  />
                                            <input id="clientes_id" class="span12" type="hidden" name="clientes_id" value="<?php echo $result->clientes_id ?>"  />
                                            <input id="valorTotal" type="hidden" name="valorTotal" value=""  />
                                        </div>
                                        <div class="span4">
                                            <label for="operador_id">Atendente<span class="required">*</span></label>
                                            <select class="span12" name="operador_id" id="operador_id">
                                                <option <?php if($result->operador_id == 'selecione'){echo 'selected';} ?> value="selecione">Selecione</option>
                                                <option <?php if($result->operador_id == '9'){echo 'selected';} ?> value="9">Alan Silveira</option>
                                                <option <?php if($result->operador_id == '5'){echo 'selected';} ?> value="5">Celso Torok</option>
                                                <option <?php if($result->operador_id == '6'){echo 'selected';} ?> value="6">José Marques</option>
                                                <option <?php if($result->operador_id == '3'){echo 'selected';} ?> value="3">Rafael Marques</option>
                                                <option <?php if($result->operador_id == '7'){echo 'selected';} ?> value="7">Rosiane Ribeiro</option>
                                                <option <?php if($result->operador_id == '4'){echo 'selected';} ?> value="4">Shirley Marques</option>
                                            </select>
                                        </div>
                                        <div class="span4">
                                            <label for="tecnico">Técnico / Responsável<span class="required">*</span></label>
                                            <input id="tecnico" class="span12" type="text" name="tecnico" value="<?php echo $result->nome ?>"  />
                                            <input id="usuarios_id" class="span12" type="hidden" name="usuarios_id" value="<?php echo $result->usuarios_id ?>"  />
                                        </div>
                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span3">
                                            <label for="status">Status<span class="required">*</span></label>
                                            <select class="span12" name="status" id="status" value="">
                                                <option <?php if($result->status == 'Orçamento'){echo 'selected';} ?> value="Orçamento">Orçamento</option>
                                                <option <?php if($result->status == 'Aprovado'){echo 'selected';} ?> value="Aprovado">Aprovado</option>
                                                <option <?php if($result->status == 'Em Andamento'){echo 'selected';} ?> value="Em Andamento">Em Andamento</option>
                                                <option <?php if($result->status == 'Aguardando Peça'){echo 'selected';} ?> value="Aguardando Peça">Aguardando Peça</option>
                                                <option <?php if($result->status == 'Aguardando Cliente'){echo 'selected';} ?> value="Aguardando Cliente">Aguardando Cliente</option>
                                                <option <?php if($result->status == 'Finalizado'){echo 'selected';} ?> value="Finalizado">Finalizado</option>
                                                <option <?php if($result->status == 'Faturado'){echo 'selected';} ?> value="Faturado">Faturado</option>
                                                <option <?php if($result->status == 'Cancelado'){echo 'selected';} ?> value="Cancelado">Cancelado</option>
                                            </select>
                                        </div>
                                        <div class="span3">
                                            <label for="dataInicial">Data Inicial<span class="required">*</span></label>
                                            <input id="dataInicial" class="span12 datepicker" type="text" name="dataInicial" value="<?php echo date('d/m/Y', strtotime($result->dataInicial)); ?>"  />
                                        </div>
                                        <div class="span3">
                                            <label for="dataFinal">Data Final</label>
                                            <input id="dataFinal" class="span12 datepicker" type="text" name="dataFinal" value="<?php echo date('d/m/Y', strtotime($result->dataFinal)); ?>"  />
                                        </div>

                                        <div class="span3">
                                            <label for="garantia">Garantia</label>
                                            <select class="span12" name="garantia" id="garantia" value="">
                                                <option <?php if($result->garantia == 'selecione'){echo 'selected';} ?> value="selecione">Selecione</option>
                                                <option <?php if($result->garantia == '30'){echo 'selected';} ?> value="30">30 Dias</option>
                                                <option <?php if($result->garantia == '60'){echo 'selected';} ?> value="60">60 Dias</option>
                                                <option <?php if($result->garantia == '90'){echo 'selected';} ?> value="90">90 Dias</option>
                                                <option <?php if($result->garantia == '180'){echo 'selected';} ?> value="180">6 Meses</option>
                                                <option <?php if($result->garantia == '360'){echo 'selected';} ?> value="360">1 ano</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span3">
                                            <label for="equipamento">Equipamento</label>
                                            <select class="span12" name="equipamento" id="equipamento" value="">
                                                <option <?php if($result->equipamento == 'selecione'){echo 'selected';} ?> value="selecione">Selecione</option>
                                                <option <?php if($result->equipamento == 'Notebook'){echo 'selected';} ?> value="Notebook">Notebook</option>
                                                <option <?php if($result->equipamento == 'Netbook'){echo 'selected';} ?> value="Netbook">Netbook</option>
                                                <option <?php if($result->equipamento == 'Desktop'){echo 'selected';} ?> value="Desktop">Desktop</option>
                                                <option <?php if($result->equipamento == 'All-In-One'){echo 'selected';} ?> value="All-In-One">All-In-One</option>
                                                <option <?php if($result->equipamento == 'Tablet'){echo 'selected';} ?> value="Tablet">Tablet</option>
                                                <option <?php if($result->equipamento == 'Impressora'){echo 'selected';} ?> value="Impressora">Impressora</option>
                                                <option <?php if($result->equipamento == 'Monitor'){echo 'selected';} ?> value="Monitor">Monitor</option>
                                                <option <?php if($result->equipamento == 'Fonte'){echo 'selected';} ?> value="Fonte">Fonte</option>
                                                <option <?php if($result->equipamento == 'Carregador'){echo 'selected';} ?> value="Carregador">Carregador</option>
                                                <option <?php if($result->equipamento == 'Bateria'){echo 'selected';} ?> value="Bateria">Bateria</option>
                                                <option <?php if($result->equipamento == 'Estabilizador'){echo 'selected';} ?> value="Estabilizador">Estabilizador</option>
                                                <option <?php if($result->equipamento == 'Nobreak'){echo 'selected';} ?> value="Nobreak">Nobreak</option>
                                                <option <?php if($result->equipamento == 'HD-Externo'){echo 'selected';} ?> value="HD-Externo">HD-Externo</option>
                                                <option <?php if($result->equipamento == 'Cartucho'){echo 'selected';} ?> value="Cartucho">Cartucho</option>
                                                <option <?php if($result->equipamento == 'Outros'){echo 'selected';} ?> value="Outros">Outros</option>
                                            </select>
                                        </div>
                                        <div class="span3">
                                            <label for="marca">Marca</label>
                                            <select class="span12" name="marca" id="marca" value="">
                                                <option <?php if($result->marca == 'selecione'){echo 'selected';} ?> value="selecione">Selecione</option>
                                                <option <?php if($result->marca == 'Acer'){echo 'selected';} ?> value="Acer">Acer</option>
                                                <option <?php if($result->marca == 'Amazon'){echo 'selected';} ?> value="Amazon">Amazon</option>
                                                <option <?php if($result->marca == 'Aoc'){echo 'selected';} ?> value="Aoc">Aoc</option>
                                                <option <?php if($result->marca == 'APC'){echo 'selected';} ?> value="APC">APC</option>
                                                <option <?php if($result->marca == 'Apple'){echo 'selected';} ?> value="Apple">Apple</option>
                                                <option <?php if($result->marca == 'Asus'){echo 'selected';} ?>  value="Asus">Asus</option>
                                                <option <?php if($result->marca == 'Canon'){echo 'selected';} ?> value="Canon">Canon</option>
                                                <option <?php if($result->marca == 'Cce'){echo 'selected';} ?> value="Cce">Cce</option>
                                                <option <?php if($result->marca == 'Cecomil'){echo 'selected';} ?> value="Cecomil">Cecomil</option>
                                                <option <?php if($result->marca == 'Compaq'){echo 'selected';} ?> value="Compaq">Compaq</option>
                                                <option <?php if($result->marca == 'Dell'){echo 'selected';} ?> value="Dell">Dell</option>
                                                <option <?php if($result->marca == 'Goldentech'){echo 'selected';} ?> value="Goldentech">Goldentech</option>
                                                <option <?php if($result->marca == 'HP'){echo 'selected';} ?> value="HP">HP</option>
                                                <option <?php if($result->marca == 'iByte'){echo 'selected';} ?> value="iByte">iByte</option>
                                                <option <?php if($result->marca == 'Lenovo'){echo 'selected';} ?> value="Lenovo">Lenovo</option>
                                                <option <?php if($result->marca == 'Lexmark'){echo 'selected';} ?> value="Lexmark">Lexmark</option>
                                                <option <?php if($result->marca == 'LG'){echo 'selected';} ?> value="LG">LG</option>
                                                <option <?php if($result->marca == 'Megaware'){echo 'selected';} ?> value="Megaware">Megaware</option>
                                                <option <?php if($result->marca == 'Microboard'){echo 'selected';} ?> value="Microboard">Microboard</option>
                                                <option <?php if($result->marca == 'Microsol'){echo 'selected';} ?> value="Microsol">Microsol</option>
                                                <option <?php if($result->marca == 'Philco'){echo 'selected';} ?> value="Philco">Philco</option>
                                                <option <?php if($result->marca == 'Philips'){echo 'selected';} ?> value="Philips">Philips</option>
                                                <option <?php if($result->marca == 'Positvo'){echo 'selected';} ?> value="Positvo">Positvo</option>
                                                <option <?php if($result->marca == 'Samsung'){echo 'selected';} ?> value="Samsung">Samsung</option>
                                                <option <?php if($result->marca == 'Sim'){echo 'selected';} ?> value="Sim">Sim</option>
                                                <option <?php if($result->marca == 'SMS'){echo 'selected';} ?> value="SMS">SMS</option>
                                                <option <?php if($result->marca == 'Sony'){echo 'selected';} ?> value="Sony">Sony</option>
                                                <option <?php if($result->marca == 'Sti'){echo 'selected';} ?> value="Sti">Sti</option>
                                                <option <?php if($result->marca == 'Toshiba'){echo 'selected';} ?> value="Toshiba">Toshiba</option>
                                                <option <?php if($result->marca == 'Win'){echo 'selected';} ?> value="Win">Win</option>
                                                <option <?php if($result->marca == 'Outros'){echo 'selected';} ?> value="Outros">Outros</option>
                                            </select>
                                        </div>
                                        <div class="span3">
                                            <label for="modelo">Modelo</label>
                                            <input class="span12" type="text" class="form-control" name="modelo" id="modelo" value="<?php echo $result->modelo ?>">
                                        </div>
                                        <div class="span3">
                                            <label for="nronumber">Nro. Série</label>
                                            <input class="span12" type="text" class="form-control" name="nronumber" id="nronumber" value="<?php echo $result->nronumber ?>">
                                        </div>
                                    </div>


                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span4">
                                            <div class="input-group">
                                              <label for="senha">Senha</label>
                                              <input class="span12" type="text" class="form-control" name="senha" id="senha" value="<?php echo $result->senha ?>">
                                            </div><!-- /input-group -->
                                        </div>
                                        <div class="span4">
                                            <label for="backup">Backup</label>
                                            <select class="span12" name="backup" id="backup" value="">
                                                <option <?php if($result->backup == 'selecione'){echo 'selected';} ?> value="selecione">Selecione</option>
                                                <option <?php if($result->backup == 'Sim'){echo 'selected';} ?> value="Sim">Sim</option>
                                                <option <?php if($result->backup == 'Não'){echo 'selected';} ?> value="Não">Não</option>
                                            </select>
                                        </div>
                                        <div class="span4">
                                            <label for="cabos">Acessórios</label>
                                            <select class="span12" name="cabos" id="cabos" value="">
                                                <option <?php if($result->cabos == 'selecione'){echo 'selected';} ?> value="selecione">Selecione</option>
                                                <option <?php if($result->cabos == 'Sim'){echo 'selected';} ?> value="Sim">Sim</option>
                                                <option <?php if($result->cabos == 'Não'){echo 'selected';} ?> value="Não">Não</option>
                                            </select>
                                        </div>
                                        <div id="descricaoCabosSim" class="span12" style="margin-left: 0; display: none;">
                                            <label for="descricaoCabos">Descrição dos Acessórios</label>
                                            <input class="span12" type="text" class="form-control" name="descricaoCabos" value="<?php echo $result->descricaoCabos ?>">
                                        </div>
                                        <div id="descricaoCabosNao" class="span12" style="margin-left: 0; display: none;">
                                            <label for="descricaoCabos">Descrição dos Acessórios</label>
                                            <input class="span12" type="text" class="form-control" name="descricaoCabos" value="Nenhum acessório (fonte, cabo, case e etc) foi entregue com o equipamento.">
                                        </div>
                                    </div>

                                    <div class="span12" style="padding: 1%; margin-left: 0">

                                        <div class="span6">
                                            <label for="descricaoProduto">Descrição do Serviço</label>
                                            <textarea class="span12" name="descricaoProduto" id="descricaoProduto" cols="30" rows="5" maxlength="255"><?php echo $result->descricaoProduto?></textarea>
                                            <span></span>
                                        </div>
                                        <div class="span6">
                                            <label for="defeito">Defeito Relatado</label>
                                            <textarea class="span12" name="defeito" id="defeito" cols="30" rows="5" maxlength="255"><?php echo $result->defeito?></textarea>
                                            <span></span>
                                        </div>

                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span6">
                                            <label for="observacoes">Observações</label>
                                            <textarea class="span12" name="observacoes" id="observacoes" cols="30" rows="5" maxlength="255"><?php echo $result->observacoes ?></textarea>
                                            <span></span>
                                        </div>
                                        <div class="span6">
                                            <label for="laudoTecnico">Laudo Técnico</label>
                                            <textarea class="span12" name="laudoTecnico" id="laudoTecnico" cols="30" rows="5" maxlength="255"><?php echo $result->laudoTecnico ?></textarea>
                                            <span></span>
                                        </div>
                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span6 offset3" style="text-align: center">
                                            <button class="btn btn-primary" id="btnContinuar"><i class="icon-white icon-ok"></i> Alterar</button>
                                            <a href="<?php echo base_url() ?>index.php/os/visualizar/<?php echo $result->idOs; ?>" class="btn btn-inverse"><i class="icon-eye-open"></i> Visualizar OS</a>
                                            <a href="<?php echo base_url() ?>index.php/os" class="btn"><i class="icon-arrow-left"></i> Voltar</a>
                                        </div>
                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span6 offset3" style="text-align: center">
                                            <?php if($result->faturado == 0 && $result->status == 'Faturado'){ ?>
                                                <a href="#modal-faturar" id="btn-faturar" role="button" data-toggle="modal" class="btn btn-success"><i class="icon-file"></i> Faturar</a>
                                            <?php } else if($result->faturado == 1 && $result->status == 'Faturado') { ?>
                                                <a class="btn btn-success disabled"><i class="icon-file"></i> Faturado</a>
                                            <?php } else { ?>
                                                <div class="alert alert-danger">
                                                    <span><strong>ANTES DE FATURAR</strong><br>- Altere o campo <strong>Status</strong> para <strong>Faturado</strong>.<br>- Defina o <strong>Técnico/Responsável</strong> pela execução do serviço.</span>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>


                        <!--Produtos-->
                        <div class="tab-pane" id="tab2">
                            <div class="span12 well" style="padding: 1%; margin-left: 0">
                                <form id="formProdutos" action="<?php echo base_url() ?>index.php/os/adicionarProduto" method="post">
                                    <div class="span8">
                                        <input type="hidden" name="idProduto" id="idProduto" />
                                        <input type="hidden" name="idOsProduto" id="idOsProduto" value="<?php echo $result->idOs?>" />
                                        <input type="hidden" name="estoque" id="estoque" value=""/>
                                        <input type="hidden" name="preco" id="preco" value=""/>
                                        <label for="">Produto</label>
                                        <input type="text" class="span12" name="produto" id="produto" placeholder="Digite o nome do produto" />
                                    </div>
                                    <div class="span2">
                                        <label for="">Quantidade</label>
                                        <input type="text" placeholder="Quantidade" id="quantidade" name="quantidade" class="span12" />
                                    </div>
                                    <div class="span2">
                                        <label for="">.</label>
                                        <button class="btn btn-success span12" id="btnAdicionarProduto"><i class="icon-white icon-plus"></i> Adicionar</button>
                                    </div>
                                </form>
                            </div>
                            <div class="span12" id="divProdutos" style="margin-left: 0">
                                <table class="table table-bordered" id="tblProdutos">
                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th>Quantidade</th>
                                            <th>Ações</th>
                                            <th>Sub-total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total = 0;
                                        foreach ($produtos as $p) {
                                            
                                            $total = $total + $p->subTotal;
                                            echo '<tr>';
                                            echo '<td>'.$p->descricao.'</td>';
                                            echo '<td>'.$p->quantidade.'</td>';
                                            echo '<td><a href="" idAcao="'.$p->idProdutos_os.'" prodAcao="'.$p->idProdutos.'" quantAcao="'.$p->quantidade.'" title="Excluir Produto" class="btn btn-danger"><i class="icon-remove icon-white"></i></a></td>';
                                            echo '<td>R$ '.number_format($p->subTotal,2,',','.').'</td>';
                                            echo '</tr>';
                                        }?>
                                       
                                        <tr>
                                            <td colspan="3" style="text-align: right"><strong>Total:</strong></td>
                                            <td><strong>R$ <?php echo number_format($total,2,',','.');?><input type="hidden" id="total-venda" value="<?php echo number_format($total,2); ?>"></strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>

                        <!--Serviços-->
                        <div class="tab-pane" id="tab3">
                            <div class="span12" style="padding: 1%; margin-left: 0">
                                <div class="span12 well" style="padding: 1%; margin-left: 0">
                                    <form id="formServicos" action="<?php echo base_url() ?>index.php/os/adicionarServico" method="post">
                                    <div class="span10">
                                        <input type="hidden" name="idServico" id="idServico" />
                                        <input type="hidden" name="idOsServico" id="idOsServico" value="<?php echo $result->idOs?>" />
                                        <input type="hidden" name="precoServico" id="precoServico" value=""/>
                                        <label for="">Serviço</label>
                                        <input type="text" class="span12" name="servico" id="servico" placeholder="Digite o nome do serviço" />
                                    </div>
                                    <div class="span2">
                                        <label for="">.</label>
                                        <button class="btn btn-success span12"><i class="icon-white icon-plus"></i> Adicionar</button>
                                    </div>
                                    </form>
                                </div>
                                <div class="span12" id="divServicos" style="margin-left: 0">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Serviço</th>
                                                <th>Ações</th>
                                                <th>Sub-total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                        $total = 0;
                                        foreach ($servicos as $s) {
                                            $preco = $s->preco;
                                            $total = $total + $preco;
                                            echo '<tr>';
                                            echo '<td>'.$s->nome.'</td>';
                                            echo '<td><span idAcao="'.$s->idServicos_os.'" title="Excluir Serviço" class="btn btn-danger"><i class="icon-remove icon-white"></i></span></td>';
                                            echo '<td>R$ '.number_format($s->preco,2,',','.').'</td>';
                                            echo '</tr>';
                                        }?>

                                        <tr>
                                            <td colspan="2" style="text-align: right"><strong>Total:</strong></td>
                                            <td><strong>R$ <?php echo number_format($total,2,',','.');?><input type="hidden" id="total-servico" value="<?php echo number_format($total,2); ?>"></strong></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>

                        <!--Historico-->
                        <div class="tab-pane" id="tab6">

                            <div class="span12" style="padding: 1%; margin-left: 0">
                                
                                <div class="span12" id="divHistorico" style="margin-left: 0">
                                    <h5>Histórico de Contato</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Responsável</th>
                                                <th>Data</th>
                                                <th>Status</th>
                                                <th>Comentários</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        foreach ($historico as $h) {
                                            $data = date('d/m/Y', strtotime($h->data));
                                            echo '<tr>';
                                            echo '<td>'.$h->responsavel.'</td>';
                                            echo '<td>'.$data.'</td>';
                                            echo '<td>'.$h->status.'</td>';
                                            echo '<td>'.$h->comentarios.'</td>';
                                            echo '</tr>';
                                        }?>
                                        </tbody>
                                    </table>
                                </div>

                            </div>

                        </div>


                        <!--Anexos-->
                        <div class="tab-pane" id="tab4">
                            <div class="span12" style="padding: 1%; margin-left: 0">
                                <div class="span12 well" style="padding: 1%; margin-left: 0" id="form-anexos">
                                    <form id="formAnexos" enctype="multipart/form-data" action="javascript:;" accept-charset="utf-8"s method="post">
                                    <div class="span10">
                                
                                        <input type="hidden" name="idOsServico" id="idOsServico" value="<?php echo $result->idOs?>" />
                                        <label for="">Anexo</label>
                                        <input type="file" class="span12" name="userfile[]" multiple="multiple" size="20" />
                                    </div>
                                    <div class="span2">
                                        <label for="">.</label>
                                        <button class="btn btn-success span12"><i class="icon-white icon-plus"></i> Anexar</button>
                                    </div>
                                    </form>
                                </div>
                
                                <div class="span12" id="divAnexos" style="margin-left: 0">
                                    <?php 
                                    $cont = 1;
                                    $flag = 5;
                                    foreach ($anexos as $a) {

                                        if($a->thumb == null){
                                            $thumb = base_url().'assets/img/icon-file.png';
                                            $link = base_url().'assets/img/icon-file.png';
                                        }
                                        else{
                                            $thumb = base_url().'assets/anexos/thumbs/'.$a->thumb;
                                            $link = $a->url.$a->anexo;
                                        }

                                        if($cont == $flag){
                                           echo '<div style="margin-left: 0" class="span3"><a href="#modal-anexo" imagem="'.$a->idAnexos.'" link="'.$link.'" role="button" class="btn anexo" data-toggle="modal"><img src="'.$thumb.'" alt=""></a></div>'; 
                                           $flag += 4;
                                        }
                                        else{
                                           echo '<div class="span3"><a href="#modal-anexo" imagem="'.$a->idAnexos.'" link="'.$link.'" role="button" class="btn anexo" data-toggle="modal"><img src="'.$thumb.'" alt=""></a></div>'; 
                                        }
                                        $cont ++;
                                    } ?>
                                </div>

                            </div>
                        </div>

                        <!--Compras-->
                        <div class="tab-pane" id="tab5">
                            <div class="span12" style="padding: 1%; margin-left: 0">
                                <div class="span12" id="divCompras" style="margin-left: 0">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Comprador</th>
                                                <th>Fornecedor</th>
                                                <th>Descrição</th>
                                                <th>Data Pedido</th>
                                                <th>Data Prevista</th>
                                                <th>Rastreio</th>
                                                <th>Sub-total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                        $total = 0;
                                        foreach ($compras as $s) {
                                            $valor = $s->valor;
                                            $total = $total + $valor;
                                            $dataPedido = date(('d/m/Y'),strtotime($s->dataPedido));
                                            $dataPrevista = date(('d/m/Y'),strtotime($s->dataPrevista));

                                            echo '<tr>';
                                            echo '<td>'.$s->idCompras.'</td>';
                                            echo '<td>'.$s->comprador.'</td>';
                                            echo '<td>'.$s->fornecedor.'</td>';
                                            echo '<td><a href="'.base_url().'index.php/compras/editar/'.$s->idCompras.'">'.$s->descricao.'</a></td>';
                                            echo '<td>'.$dataPedido.'</td>';
                                            echo '<td>'.$dataPrevista.'</td>';
                                            if($s->rastreio){
                                            echo '<td style="text-align:center"><a target="_blank" href="http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI='.$s->rastreio.'" class="tip-bottom" data-original-title="Pesquisar"><i class="icon-truck icon-white"></i></a></td>';
                                            } else {
                                                echo "<td></td>";
                                            }
                                            echo '<td>R$ '.number_format($s->valor,2,',','.').'</td>';
                                            echo '</tr>';
                                        }?>

                                        <tr>
                                            <td colspan="7" style="text-align: right"><strong>Total:</strong></td>
                                            <td><strong>R$ <?php echo number_format($total,2,',','.');?><input type="hidden" id="total-servico" value="<?php echo number_format($total,2); ?>"></strong></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                


                    </div>

                </div>


.

        </div>

    </div>
</div>
</div>




 
<!-- Modal visualizar anexo -->
<div id="modal-anexo" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Visualizar Anexo</h3>
  </div>
  <div class="modal-body">
    <div class="span12" id="div-visualizar-anexo" style="text-align: center">
        <div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>
    </div>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
    <a href="" id-imagem="" class="btn btn-inverse" id="download">Download</a>
    <a href="" link="" class="btn btn-danger" id="excluir-anexo">Excluir Anexo</a>
  </div>
</div>





<!-- Modal Faturar-->
<div id="modal-faturar" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
<form id="formFaturar" action="<?php echo current_url() ?>" method="post">
<input type="hidden" name="usuarios_id" id="usuarios_id" value="<?php echo $result->usuarios_id ?>">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
  <h3 id="myModalLabel">Faturar Venda</h3>
</div>
<div class="modal-body">
    
    <div class="span12 alert alert-info" style="margin-left: 0"> Obrigatório o preenchimento dos campos com asterisco.</div>
    <div class="span12" style="margin-left: 0"> 
      <label for="descricao">Descrição</label>
      <input class="span12" id="descricao" type="text" name="descricao" value="Fatura de Venda - #<?php echo $result->idOs; ?> "  />
    </div>  

    <div class="span12" style="margin-left: 0"> 
      <div class="span12" style="margin-left: 0"> 
        <label for="usuarios_id">Recurso<span class="required">*</span></label>
        <select class="span12" name="usuarios_id" id="usuarios_id">
            <option <?php if($result->usuarios_id == 'selecione'){echo 'selected';} ?> value="">Selecione</option>
            <option <?php if($result->usuarios_id == '1'){echo 'selected';} ?> value="1">Loja</option>
            <option <?php if($result->usuarios_id == '5'){echo 'selected';} ?> value="5">Celso Torok</option>
            <option <?php if($result->usuarios_id == '6'){echo 'selected';} ?> value="6">José Marques</option>
            <option <?php if($result->usuarios_id == '3'){echo 'selected';} ?> value="3">Rafael Marques</option>
        </select>
      </div>
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
        <input class="span12 money" id="valor" type="text" name="valor" value="<?php echo number_format($total,2); ?> "  />
      </div>
      <div class="span4" >
        <label for="vencimento">Data Vencimento*</label>
        <input class="span12 datepicker" id="vencimento" type="text" name="vencimento"  />
      </div>
      
    </div>
    
    <div class="span12" style="margin-left: 0"> 
      <div class="span4" style="margin-left: 0">
        <label for="recebido">Recebido?</label>
        &nbsp &nbsp &nbsp &nbsp <input  id="recebido" type="checkbox" name="recebido" value="1" /> 
      </div>
      <div id="divRecebimento" class="span8" style=" display: none">
        <div class="span6">
          <label for="recebimento">Data Recebimento</label>
          <input class="span12 datepicker" id="recebimento" type="text" name="recebimento" /> 
        </div>
        <div class="span6">
          <label for="formaPgto">Forma Pgto</label>
          <select name="formaPgto" id="formaPgto" class="span12">
            <option value="Dinheiro">Dinheiro</option>
            <option value="Débito - Visa">Débito - Visa</option>
            <option value="Débito - Master">Débito - Master</option>
            <option value="Débito - Elo">Débito - Elo</option>
            <option value="Cartão de Crédito">Cartão de Crédito</option>
            <option value="Cheque">Cheque</option>
            <option value="Boleto">Boleto</option>
            <option value="Depósito">Depósito</option>
          </select> 
      </div>
      
    </div>
    
    
</div>
<div class="modal-footer">
  <button class="btn" data-dismiss="modal" aria-hidden="true" id="btn-cancelar-faturar">Cancelar</button>
  <button class="btn btn-primary">Faturar</button>
</div>
</form>
</div>



<script type="text/javascript" src="<?php echo base_url()?>js/jquery.validate.js"></script>
<script src="<?php echo base_url();?>js/maskmoney.js"></script>

<script type="text/javascript">
$(document).ready(function(){
    
    $(".money").maskMoney(); 

    $( "#divHistorico" ).clone().appendTo( "#tblHistorico" );

     $('#recebido').click(function(event) {
        var flag = $(this).is(':checked');
        if(flag == true){
          $('#divRecebimento').show();
        }
        else{
          $('#divRecebimento').hide();
        }
     });

     $(document).on('click', '#btn-faturar', function(event) {
       event.preventDefault();
         valor = $('#total-venda').val();
         total_servico = $('#total-servico').val();
         valor = valor.replace(',', '' );
         total_servico = total_servico.replace(',', '' );
         total_servico = parseFloat(total_servico); 
         valor = parseFloat(valor);
         $('#valor').val(valor + total_servico);
     });
     
     $("#formFaturar").validate({
          rules:{
             descricao: {required:true},
             cliente: {required:true},
             valor: {required:true},
             vencimento: {required:true}
      
          },
          messages:{
             descricao: {required: 'Campo Requerido.'},
             cliente: {required: 'Campo Requerido.'},
             valor: {required: 'Campo Requerido.'},
             vencimento: {required: 'Campo Requerido.'}
          },
          submitHandler: function( form ){       
            var dados = $( form ).serialize();
            $('#btn-cancelar-faturar').trigger('click');
            $.ajax({
              type: "POST",
              url: "<?php echo base_url();?>index.php/os/faturar",
              data: dados,
              dataType: 'json',
              success: function(data)
              {
                if(data.result == true){
                    
                    window.location.reload(true);
                }
                else{
                    alert('Ocorreu um erro ao tentar faturar OS.');
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
            select: function( event, ui ) {

                 $("#idProduto").val(ui.item.id);
                 $("#estoque").val(ui.item.estoque);
                 $("#preco").val(ui.item.preco);
                 $("#quantidade").focus();
                 

            }
      });

      $("#servico").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteServico",
            minLength: 2,
            select: function( event, ui ) {

                 $("#idServico").val(ui.item.id);
                 $("#precoServico").val(ui.item.preco);
                 

            }
      });

      $("#compra").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteCompra",
            minLength: 1,
            select: function( event, ui ) {

                 $("#idCompra").val(ui.item.id);
                 $("#precoCompra").val(ui.item.preco);
                 

            }
      });


      $("#cliente").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteCliente",
            minLength: 2,
            select: function( event, ui ) {

                 $("#clientes_id").val(ui.item.id);


            }
      });

      $("#tecnico").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteUsuario",
            minLength: 2,
            select: function( event, ui ) {

                 $("#usuarios_id").val(ui.item.id);


            }
      });




      $("#formOs").validate({
          rules:{
             cliente: {required:true},
             tecnico: {required:true},
             dataInicial: {required:true}
          },
          messages:{
             cliente: {required: 'Campo Requerido.'},
             tecnico: {required: 'Campo Requerido.'},
             dataInicial: {required: 'Campo Requerido.'}
          },

            errorClass: "help-inline",
            errorElement: "span",
            highlight:function(element, errorClass, validClass) {
                $(element).parents('.control-group').addClass('error');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error');
                $(element).parents('.control-group').addClass('success');
            }
       });




      $("#formProdutos").validate({
          rules:{
             quantidade: {required:true}
          },
          messages:{
             quantidade: {required: 'Insira a quantidade'}
          },
          submitHandler: function( form ){
             var quantidade = parseInt($("#quantidade").val());
             var estoque = parseInt($("#estoque").val());
             if(estoque < quantidade){
                alert('Você não possui estoque suficiente.');
             }
             else{
                 var dados = $( form ).serialize();
                $("#divProdutos").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                $.ajax({
                  type: "POST",
                  url: "<?php echo base_url();?>index.php/os/adicionarProduto",
                  data: dados,
                  dataType: 'json',
                  success: function(data)
                  {
                    if(data.result == true){
                        $( "#divProdutos" ).load("<?php echo current_url();?> #divProdutos" );
                        $("#quantidade").val('');
                        $("#produto").val('').focus();
                    }
                    else{
                        alert('Ocorreu um erro ao tentar adicionar produto.');
                    }
                  }
                  });

                  return false;
                }

             }
             
       });

       $("#formServicos").validate({
          rules:{
             servico: {required:true}
          },
          messages:{
             servico: {required: 'Insira um serviço'}
          },
          submitHandler: function( form ){       
                 var dados = $( form ).serialize();
                 
                $("#divServicos").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                $.ajax({
                  type: "POST",
                  url: "<?php echo base_url();?>index.php/os/adicionarServico",
                  data: dados,
                  dataType: 'json',
                  success: function(data)
                  {
                    if(data.result == true){
                        $( "#divServicos" ).load("<?php echo current_url();?> #divServicos" );
                        $("#servico").val('').focus();
                    }
                    else{
                        alert('Ocorreu um erro ao tentar adicionar serviço.');
                    }
                  }
                  });

                  return false;
                }

       });


       $("#formCompras").validate({
          rules:{
             solicitante: {required:true},
             descricao: {required:true}
          },
          messages:{
             solicitante: {required: 'Campo Requerido.'},
             descricao: {required: 'Campo Requerido.'}
          },
          submitHandler: function( form ){       
                 var dados = $( form ).serialize();
                 
                $("#divCompras").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                $.ajax({
                  type: "POST",
                  url: "<?php echo base_url();?>index.php/os/adicionarCompra",
                  data: dados,
                  dataType: 'json',
                  success: function(data)
                  {
                    if(data.result == true){
                        $( "#divCompras" ).load("<?php echo current_url();?> #divCompras" );
                    }
                    else{
                        alert('Ocorreu um erro ao tentar adicionar a compra.');
                    }
                  }
                  });

                  return false;
                }

       });


        $("#formAnexos").validate({
         
          submitHandler: function( form ){       
                //var dados = $( form ).serialize();
                var dados = new FormData(form); 
                $("#form-anexos").hide('1000');
                $("#divAnexos").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                $.ajax({
                  type: "POST",
                  url: "<?php echo base_url();?>index.php/os/anexar",
                  data: dados,
                  mimeType:"multipart/form-data",
                  contentType: false,
                  cache: false,
                  processData:false,
                  dataType: 'json',
                  success: function(data)
                  {
                    if(data.result == true){
                        $("#divAnexos" ).load("<?php echo current_url();?> #divAnexos" );
                        $("#userfile").val('');

                    }
                    else{
                        $("#divAnexos").html('<div class="alert fade in"><button type="button" class="close" data-dismiss="alert">×</button><strong>Atenção!</strong> '+data.mensagem+'</div>');      
                    }
                  },
                  error : function() {
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
            if((idProduto % 1) == 0){
                $("#divProdutos").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                $.ajax({
                  type: "POST",
                  url: "<?php echo base_url();?>index.php/os/excluirProduto",
                  data: "idProduto="+idProduto+"&quantidade="+quantidade+"&produto="+produto,
                  dataType: 'json',
                  success: function(data)
                  {
                    if(data.result == true){
                        $( "#divProdutos" ).load("<?php echo current_url();?> #divProdutos" );
                        
                    }
                    else{
                        alert('Ocorreu um erro ao tentar excluir produto.');
                    }
                  }
                  });
                  return false;
            }
            
       });



       $(document).on('click', 'span', function(event) {
            var idServico = $(this).attr('idAcao');
            if((idServico % 1) == 0){
                $("#divServicos").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                $.ajax({
                  type: "POST",
                  url: "<?php echo base_url();?>index.php/os/excluirServico",
                  data: "idServico="+idServico,
                  dataType: 'json',
                  success: function(data)
                  {
                    if(data.result == true){
                        $("#divServicos").load("<?php echo current_url();?> #divServicos" );

                    }
                    else{
                        alert('Ocorreu um erro ao tentar excluir serviço.');
                    }
                  }
                  });
                  return false;
            }

       });


       $(document).on('click', '#excluir-compra', function(event) {
            var idCompra = $(this).attr('idAcao');
            if((idCompra % 1) == 0){
                $("#divCompras").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                $.ajax({
                  type: "POST",
                  url: "<?php echo base_url();?>index.php/os/excluirCompra",
                  data: "idCompra="+idCompra,
                  dataType: 'json',
                  success: function(data)
                  {
                    if(data.result == true){
                        $("#divCompras").load("<?php echo current_url();?> #divCompras" );

                    }
                    else{
                        alert('Ocorreu um erro ao tentar excluir a compra.');
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
           var url = '<?php echo base_url(); ?>os/excluirAnexo/';
           $("#div-visualizar-anexo").html('<img src="'+link+'" alt="">');
           $("#excluir-anexo").attr('link', url+id);

           $("#download").attr('href', "<?php echo base_url(); ?>index.php/os/downloadanexo/"+id);

       });

       $(document).on('click', '#excluir-anexo', function(event) {
           event.preventDefault();

           var link = $(this).attr('link'); 
           $('#modal-anexo').modal('hide');
           $("#divAnexos").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");

           $.ajax({
                  type: "POST",
                  url: link,
                  dataType: 'json',
                  success: function(data)
                  {
                    if(data.result == true){
                        $("#divAnexos" ).load("<?php echo current_url();?> #divAnexos" );
                    }
                    else{
                        alert(data.mensagem);
                    }
                  }
            });
       });



       $(".datepicker" ).datepicker({ dateFormat: 'dd/mm/yy' });

        if ( $("#cabos").val() == "Sim") {
              $( "#descricaoCabosSim > input" ).attr('name','descricaoCabos');
              $( "#descricaoCabosSim" ).show();
              $( "#descricaoCabosNao > input" ).attr('name','');
              $( "#descricaoCabosNao" ).hide();
          } else {
              $( "#descricaoCabosNao > input" ).attr('name','descricaoCabos');
              $( "#descricaoCabosNao" ).show();
              $( "#descricaoCabosSim > input" ).attr('name','');
              $( "#descricaoCabosSim" ).hide();
          }

        $( "#cabos" ).change(function() {
          if ( $(this).val() == "Sim") {
              $( "#descricaoCabosSim > input" ).attr('name','descricaoCabos');
              $( "#descricaoCabosSim" ).show();
              $( "#descricaoCabosNao > input" ).attr('name','');
              $( "#descricaoCabosNao" ).hide();
          } else {
              $( "#descricaoCabosNao > input" ).attr('name','descricaoCabos');
              $( "#descricaoCabosNao" ).show();
              $( "#descricaoCabosSim > input" ).attr('name','');
              $( "#descricaoCabosSim" ).hide();
          }
        });

        //contador de caracteres
        $('textarea').on({
            focus: function() {
                var text_max = $(this).attr('maxlength');
                var text_length = $(this).val().length;
                var text_remaining = text_max - text_length;
                $(this).next().html(text_remaining + ' caracteres restantes.');
            },
            keyup: function() {
                var text_max = $(this).attr('maxlength');
                var text_length = $(this).val().length;
                var text_remaining = text_max - text_length;
                $(this).next().html(text_remaining + ' caracteres restantes.');
            }
        });

});

</script>




