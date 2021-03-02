<script src="<?php echo base_url()?>assets/js/jquery.mask.min.js"></script>
<script src="<?php echo base_url()?>assets/js/sweetalert2.all.min.js"></script>
<script src="<?php echo base_url()?>assets/js/funcoes.js"></script>
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<link rel="stylesheet" href="<?php echo base_url() ?>assets/trumbowyg/ui/trumbowyg.css">
<script type="text/javascript" src="<?php echo base_url() ?>assets/trumbowyg/trumbowyg.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/trumbowyg/langs/pt_br.js"></script>

<div class="row-fluid" style="margin-top:0">
			<div class="span12">
			
            <div class="widget-title">
            <span class="icon">
            <i class="fas fa-wrench"></i>
            </span>
                <h5>Configurações do Sistema</h5>
            </div>
            <div class="widget_box_Painel2"> <!-- Borda Geral -->
            <ul class="nav nav-tabs ">
                <li class="active"><a data-toggle="tab" href="#home">Gerais</a></li>
                <li><a data-toggle="tab" href="#menu5">Misc</a></li>
                <li><a data-toggle="tab" href="#menu1">Financeiro</a></li>
                <li><a data-toggle="tab" href="#menu2">Termo de Uso OS</a></li>
           <!-- <li><a data-toggle="tab" href="#menu3">Notificações</a></li> -->
                <li><a data-toggle="tab" href="#menu4">Mensagem WhatsApp</a></li>
            </ul>
            <form action="<?php echo current_url(); ?>" id="formConfigurar" method="post" class="form-horizontal">
            <div class="widget_content nopadding tab-content">
            <?php echo $custom_error; ?>
            <!-- Menu Gerais -->
                    <div id="home" class="tab-pane fade in active">
                        <div class="control-group">
                            <label for="app_name" class="control-label">Nome do Sistema</label>
                            <div class="controls">
                                <input type="text" required name="app_name" value="<?= $configuration['app_name']?>">
                                <span class="help-inline">Nome do sistema</span>
                            </div></div>
                        <div class="control-group">
                            <label for="app_theme" class="control-label">Tema do Sistema</label>
                            <div class="controls">
                                <select name="app_theme" id="app_theme">
                                    <option value="novo" <?= $configuration['app_theme'] == 'novo' ? 'selected' : ''; ?> >Padrão</option>
                                </select>
                                <span class="help-inline">Selecione o tema que que deseja usar no sistema</span>
                            </div>
                        </div>
                        <div class="control-group">
                        <label for="gerenciador_arquivos" class="control-label">Gerenciador de Arquivos</label>
                        <div class="controls">
                            <select name="gerenciador_arquivos" id="gerenciador_arquivos">
                                <option value="arquivos_old/arquivos" <?= $configuration['gerenciador_arquivos'] == 'arquivos_old/arquivos' ? 'selected' : ''; ?> >Classico</option>
                                <option value="arquivos/arquivos" <?= $configuration['gerenciador_arquivos'] == 'arquivos/arquivos' ? 'selected' : ''; ?> >Novo</option>
                            </select>
                            <span class="help-inline">Versão do Gerenciador de Arquivos.</span>
                        </div>
                    </div>
                    <div class="control-group">
                            <label for="masteros_0" class="control-label">Complemento de eMail</label>
                            <div class="controls">
                                <input type="text" required name="masteros_0" value="<?= $configuration['masteros_0']?>">
                                <span class="help-inline">
                                <span class="help-inline">Complemento de eMail em Cadastro de Cliente</span>
                            </div></div>
                            
                        <div class="form_actions">
                        <label for="app_name" class="control-label">Mensagem Rápida</label>
                        <div class="controls">
                        <input id="telefone" class="telefone1" type="text" name="masteros_1" value="" />
                            <span class="help-inline"><a href="https://web.whatsapp.com/send?phone=55<?= $configuration['masteros_1']?>" title="Enviar WhatsWapp" target="_new" class="btn btn-success"><i class="fab fa-whatsapp"></i> Enviar WhatsWapp</a></span>
                            <span class="help-inline"><input disabled="disabled" value=" <?= $configuration['masteros_1']?>" readonly="readonly" /></span>
                            </div></div>
                        <div class="form_actions" align="center">
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                    </div>
                    <!-- Fim Menu Gerais -->
                    
                    <!-- Menu Misc -->
<div id="menu5" class="tab-pane fade">
                        <div class="control-group">
                        <label for="per_page" class="control-label">Registros por Página</label>
                        <div class="controls">
                            <select name="per_page" id="theme">
                                <option value="10" <?= $configuration['per_page'] == '10' ? 'selected' : ''; ?> >10</option>
                                <option value="20" <?= $configuration['per_page'] == '20' ? 'selected' : ''; ?> >20</option>
                                <option value="30" <?= $configuration['per_page'] == '30' ? 'selected' : ''; ?> >30</option>
                                <option value="50" <?= $configuration['per_page'] == '50' ? 'selected' : ''; ?> >50</option>
                                <option value="75" <?= $configuration['per_page'] == '75' ? 'selected' : ''; ?> >75</option>
                                <option value="100" <?= $configuration['per_page'] == '100' ? 'selected' : ''; ?> >100</option>
                                <option value="150" <?= $configuration['per_page'] == '150' ? 'selected' : ''; ?> >150</option>
                                <option value="200" <?= $configuration['per_page'] == '200' ? 'selected' : ''; ?> >200</option>
                                <option value="300" <?= $configuration['per_page'] == '300' ? 'selected' : ''; ?> >300</option>
                                <option value="500" <?= $configuration['per_page'] == '500' ? 'selected' : ''; ?> >500</option>
                                <option value="750" <?= $configuration['per_page'] == '750' ? 'selected' : ''; ?> >750</option>
                                <option value="1000" <?= $configuration['per_page'] == '1000' ? 'selected' : ''; ?> >1000</option>
                            </select>
                            <span class="help-inline">Selecione quantos registros deseja exibir nas listas</span>
                        </div>
                    </div>
                        <div class="control-group">
                            <label for="control_estoque" class="control-label">Controlar Estoque</label>
                            <div class="controls">
                                <select name="control_estoque" id="control_estoque">
                                    <option value="1">Sim</option>
                                    <option value="0" <?= $configuration['control_estoque'] == '0' ? 'selected' : ''; ?> >Não</option>
                                </select>
                                <span class="help-inline">Ativar ou desativar o controle de estoque.</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="control_datatable" class="control-label">Visualização em Tabelas Dinâmicas</label>
                            <div class="controls">
                                <select name="control_datatable" id="control_datatable">
                                    <option value="1">Sim</option>
                                    <option value="0" <?= $configuration['control_datatable'] == '0' ? 'selected' : ''; ?> >Não</option>
                                </select>
                                <span class="help-inline">Ativar ou desativar a visualização em tabelas dinâmicas</span>
                            </div>
                        </div>
                        <div class="form_actions" align="center">
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                    </div>
                    <!-- Fim Menu Misc -->
                    
                    <!-- Menu Financeiro -->
                    <div id="menu1" class="tab-pane fade">
                        <div class="control-group">
                            <label for="control_baixa" class="control-label">Controle de baixa retroativa</label>
                            <div class="controls">
                                <select name="control_baixa" id="control_baixa">
                                    <option value="1">Sim</option>
                                    <option value="0" <?= $configuration['control_baixa'] == '0' ? 'selected' : ''; ?> >Não</option>
                                </select>
                                <span class="help-inline">Ativar ou desativar o controle de baixa financeira, com data retroativa.</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="control_editos" class="control-label">Controle de edição de OS</label>
                            <div class="controls">
                                <select name="control_editos" id="control_editos">
                                    <option value="1">Sim</option>
                                    <option value="0" <?= $configuration['control_editos'] == '0' ? 'selected' : ''; ?> >Não</option>
                                </select>
                                <span class="help-inline">Ativar ou desativar a permissão para alterar OS faturada e/ou cancelada.</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label for="pix_key" class="control-label">Chave Pix para Recebimento de Pagamentos</label>
                            <div class="controls">
                                <input type="text" name="pix_key" value="<?= $configuration['pix_key']?>">
                                <span class="help-inline">Chave Pix para Recebimento de Pagamentos</span>
                            </div>
                        </div>
                        <div class="form_actions" align="center">
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                    </div>
                    <!-- Fim Menu Financeiro -->
                    
                    <!-- Menu Termo de Uso OS -->
                    <div id="menu2" class="tab-pane fade">
                    <div class="control-group">
                        <label for="app_name" class="control-label">Termo de Uso OS</label>
                        <div class="span8">
<textarea name="termo_uso" class="editor"><?= $configuration['termo_uso']?></textarea>
                  </div></div>
                    <div class="form_actions" align="center">
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                    </div>
                    <!-- Fim Menu Termo de Uso OS -->
                    
                    <!-- Menu Notificações -->
                    <div id="menu3" class="tab-pane fade">
                        <div class="control-group">
                            <label for="os_notification" class="control-label">Notificação de OS</label>
                            <div class="controls">
                                <select name="os_notification" id="os_notification">
                   <option value="todos">Notificar a Todos</option>
                   <option value="cliente" <?= $configuration['os_notification'] == 'cliente' ? 'selected' : ''; ?> >Somente o Cliente</option>
                   <option value="tecnico" <?= $configuration['os_notification'] == 'tecnico' ? 'selected' : ''; ?> >Somente o Técnico</option>
                   <option value="emitente" <?= $configuration['os_notification'] == 'emitente' ? 'selected' : ''; ?> >Somente o Emitente</option>
                   <option value="nenhum" <?= $configuration['os_notification'] == 'nenhum' ? 'selected' : ''; ?> >Não Notificar</option>
                                </select>
                                <span class="help-inline">Selecione a opção de notificação por e-mail no cadastro de OS.</span>
                            </div>
                        </div>
                        
                        <div class="form_actions" align="center">
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                    </div>
                    <!-- Fim Menu Notificações -->
                    
                    <!-- Menu Mensagem WhatsApp -->
                    <div id="menu4" class="tab-pane fade">
                        <div class="control-group">
                        <label for="app_name" class="control-label">Mensagem WhatsApp</label>
                        <div class="controls">
                            <input class="span5" name="whats_app1" type="text" value="<?= $configuration['whats_app1']?>
                            " size="50" />
                            <span class="help-inline">Mensagem</span>
                        </div>
                        <div class="controls">
                            <input class="span5" name="whats_app2" type="text" value="<?= $configuration['whats_app2']?>" size="50">
                            <span class="help-inline">Nome</span>
                        </div></div>
                        <div class="controls">
                            <input class="span5 telefone1" name="whats_app3" type="text" id="telefone" value="<?= $configuration['whats_app3']?>" size="50" />
                            <span class="help-inline">Telefone</span>
                        </div>
                        <div class="controls">
                            <input class="span5" name="whats_app4" type="text" value="<?= $configuration['whats_app4']?>
                            " size="50" widg="50" />
                            <span class="help-inline">URL Area do Usuário</span>
                        </div>
                       <div class="form_actions" align="center">
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                  </div>
                  <!-- Fim Menu Mensagem WhatsApp -->
</div>







<!-- Modal -->
<div id="modal-confirmaratualiza" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/clientes/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Atualização de sistema</h5>
        </div>
        <div class="modal-body">
            <h5 style="text-align: left">Deseja realmente fazer a atualização de sistema?</h5>
            <h7 style="text-align: left">Recomendamos que faça um backup antes de prosseguir!</h7>
            <h7 style="text-align: left"><br>Faça o backup dos seguintes arquivos pois os mesmo serão excluídos:</h7>
            <h7 style="text-align: left"><br>* ./assets/anexos</h7>
            <h7 style="text-align: left"><br>* ./assets/arquivos</h7>
        </div>
        <div class="modal-footer">
            <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button id="update-mapos" type="button" class="btn btn-danger"><i class="fas fa-sync-alt"></i>Atualizar</button>
        </div>
    </form>
</div></div></div></div>

<!-- Modal -->
<div id="modal-confirmabanco" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/clientes/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Atualização de sistema</h5>
        </div>
        <div class="modal-body">
            <h5 style="text-align: left">Deseja realmente fazer a atualização do banco de dados?</h5>
            <h7 style="text-align: left">Recomendamos que faça um backup antes de prosseguir!
                <a target="_blank" title="Fazer Bakup" class="btn btn-mini btn-inverse" href="<?php echo site_url() ?>/mapos/backup">Fazer Backup</a>
            </h7>
        </div>
        <div class="modal-footer">
            <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button id="update-database" type="button" class="btn btn-warning"><i class="fas fa-sync-alt"></i>Atualizar</button>
        </div>
    </form>
</div>
<script>
    $('#update-database').click(function () {
        window.location = "<?= site_url('mapos/atualizarBanco') ?>"
    });
    $('#update-mapos').click(function() {
        window.location = "<?= site_url('mapos/atualizarMapos') ?>"
    });
    $(document).ready(function() {
        $('#notifica_whats_select').change(function() {
            if ($(this).val() != "0")
                document.getElementById("notifica_whats").value += $(this).val();
            $(this).prop('selectedIndex', 0);
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.editor').trumbowyg({
            lang: 'pt_br'
        });
    });
</script>