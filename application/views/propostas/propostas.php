<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/table-custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<style>
    /* Estilos para abas de status */
    #statusTabs {
        margin-bottom: 0;
        border-bottom: 2px solid #ddd;
    }
    #statusTabs li {
        margin-bottom: -2px;
    }
    #statusTabs li a {
        border: 1px solid transparent;
        border-bottom: none;
        border-radius: 4px 4px 0 0;
        transition: all 0.3s ease;
    }
    #statusTabs li.active a {
        background-color: #fff;
        border-color: #ddd;
        border-bottom-color: #fff;
        font-weight: bold;
    }
    #statusTabs li:not(.active) a:hover {
        background-color: #f5f5f5;
        border-color: #ddd;
    }
    #statusTabs .badge {
        font-size: 11px;
        padding: 2px 6px;
    }
    /* Estilos para menu dropdown de ações */
    .dropdown-menu {
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        border: 1px solid #ddd;
    }
    .dropdown-menu li a {
        padding: 8px 15px;
        display: block;
        color: #333;
        text-decoration: none;
    }
    .dropdown-menu li a:hover {
        background-color: #f5f5f5;
    }
    .dropdown-menu li.divider {
        height: 1px;
        margin: 5px 0;
        overflow: hidden;
        background-color: #e5e5e5;
    }
    .dropdown-menu li a i {
        margin-right: 8px;
        width: 18px;
    }
    .table tbody tr td:first-child {
        cursor: default;
    }
    /* Estilos para menu de ações */
    .dropdown-menu-proposta {
        position: relative;
    }
    .menu-acoes-lista {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .menu-acoes-lista li a {
        display: block;
        padding: 8px 15px;
        color: #333;
        text-decoration: none;
    }
    .menu-acoes-lista li a:hover {
        background-color: #f5f5f5;
    }
    .menu-acoes-lista li a i {
        margin-right: 8px;
        width: 18px;
    }
    /* Garantir que o menu não seja cortado */
    .table-responsive {
        overflow: visible !important;
    }
    .widget-content {
        overflow: visible !important;
    }
    .table tbody tr td:first-child {
        overflow: visible !important;
    }
    /* Estilos responsivos para mobile */
    @media (max-width: 768px) {
        .menu-acoes-lista {
            min-width: 200px !important;
            max-width: calc(100vw - 20px);
            z-index: 10000 !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
        }
        .btn-menu-acoes {
            padding: 8px 12px !important;
            font-size: 20px !important;
        }
        .menu-acoes-lista li a {
            padding: 12px 15px !important;
            font-size: 14px !important;
        }
        .table tbody tr td:first-child {
            width: 60px !important;
        }
    }
    @media (max-width: 480px) {
        .menu-acoes-lista {
            min-width: 180px !important;
        }
        .menu-acoes-lista li a {
            padding: 10px 12px !important;
            font-size: 13px !important;
        }
    }
</style>

<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="bx bx-file"></i>
        </span>
        <h5>Propostas Comerciais</h5>
    </div>
    <div class="span12" style="margin-left: 0">
        <form method="get" action="<?php echo base_url(); ?>index.php/propostas/gerenciar">
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aPropostas')) { ?>
                <div class="span3">
                    <a href="<?php echo base_url(); ?>index.php/propostas/adicionar" class="button btn btn-mini btn-success" style="max-width: 160px">
                        <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">Nova Proposta</span></a>
                </div>
            <?php } ?>

            <div class="span3">
                <input type="text" name="pesquisa" id="pesquisa" placeholder="Nome do cliente" class="span12" value="<?=set_value('pesquisa')?>">
            </div>
            <div class="span2">
                <select name="status" id="" class="span12">
                    <option value="">Todos os status</option>
                    <option value="Em aberto" <?=$this->input->get('status') == 'Em aberto' ? 'selected' : ''?>>Em aberto</option>
                    <option value="Rascunho" <?=$this->input->get('status') == 'Rascunho' ? 'selected' : ''?>>Rascunho</option>
                    <option value="Pendente" <?=$this->input->get('status') == 'Pendente' ? 'selected' : ''?>>Pendente</option>
                    <option value="Aguardando" <?=$this->input->get('status') == 'Aguardando' ? 'selected' : ''?>>Aguardando</option>
                    <option value="Aprovada" <?=$this->input->get('status') == 'Aprovada' ? 'selected' : ''?>>Aprovada</option>
                    <option value="Não aprovada" <?=$this->input->get('status') == 'Não aprovada' ? 'selected' : ''?>>Não aprovada</option>
                    <option value="Concluído" <?=$this->input->get('status') == 'Concluído' ? 'selected' : ''?>>Concluído</option>
                    <option value="Modelo" <?=$this->input->get('status') == 'Modelo' ? 'selected' : ''?>>Modelo</option>
                </select>
            </div>

            <div class="span3">
                <input type="text" name="data" autocomplete="off" id="data" placeholder="Data Inicial" class="span6 datepicker" value="<?=$this->input->get('data')?>">
                <input type="text" name="data2" autocomplete="off" id="data2" placeholder="Data Final" class="span6 datepicker" value="<?=$this->input->get('data2')?>">
            </div>
            <div class="span1">
                <button class="button btn btn-mini btn-warning" style="min-width: 30px">
                    <span class="button__icon"><i class='bx bx-search-alt'></i></span></button>
            </div>
        </form>
    </div>

    <!-- Abas de Status para Filtragem Rápida -->
    <div class="span12" style="margin-left: 0; margin-top: 15px; margin-bottom: 10px;">
        <ul class="nav nav-tabs" id="statusTabs" style="border-bottom: 2px solid #ddd;">
            <li class="<?= !$this->input->get('status') ? 'active' : '' ?>">
                <a href="<?php echo base_url(); ?>index.php/propostas/gerenciar?<?php 
                    $params = $_GET;
                    unset($params['status']);
                    echo http_build_query($params);
                ?>" style="padding: 10px 15px; font-weight: bold;">
                    <i class="bx bx-list-ul"></i> Todas
                    <?php if (isset($contadores_status['total'])): ?>
                        <span class="badge badge-info" style="margin-left: 5px;"><?php echo $contadores_status['total']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="<?= $this->input->get('status') == 'Em aberto' ? 'active' : '' ?>">
                <a href="<?php echo base_url(); ?>index.php/propostas/gerenciar?<?php 
                    $params = $_GET;
                    $params['status'] = 'Em aberto';
                    echo http_build_query($params);
                ?>" style="padding: 10px 15px; color: #17a2b8; font-weight: bold;">
                    <i class="bx bx-circle" style="color: #17a2b8;"></i> Em aberto
                    <?php if (isset($contadores_status['Em aberto']) && $contadores_status['Em aberto'] > 0): ?>
                        <span class="badge" style="background-color: #17a2b8; margin-left: 5px;"><?php echo $contadores_status['Em aberto']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="<?= $this->input->get('status') == 'Rascunho' ? 'active' : '' ?>">
                <a href="<?php echo base_url(); ?>index.php/propostas/gerenciar?<?php 
                    $params = $_GET;
                    $params['status'] = 'Rascunho';
                    echo http_build_query($params);
                ?>" style="padding: 10px 15px; color: #6c757d; font-weight: bold;">
                    <i class="bx bx-circle" style="color: #6c757d;"></i> Rascunho
                    <?php if (isset($contadores_status['Rascunho']) && $contadores_status['Rascunho'] > 0): ?>
                        <span class="badge" style="background-color: #6c757d; margin-left: 5px;"><?php echo $contadores_status['Rascunho']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="<?= $this->input->get('status') == 'Pendente' ? 'active' : '' ?>">
                <a href="<?php echo base_url(); ?>index.php/propostas/gerenciar?<?php 
                    $params = $_GET;
                    $params['status'] = 'Pendente';
                    echo http_build_query($params);
                ?>" style="padding: 10px 15px; color: #ffc107; font-weight: bold;">
                    <i class="bx bx-circle" style="color: #ffc107;"></i> Pendente
                    <?php if (isset($contadores_status['Pendente']) && $contadores_status['Pendente'] > 0): ?>
                        <span class="badge" style="background-color: #ffc107; margin-left: 5px;"><?php echo $contadores_status['Pendente']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="<?= $this->input->get('status') == 'Aguardando' ? 'active' : '' ?>">
                <a href="<?php echo base_url(); ?>index.php/propostas/gerenciar?<?php 
                    $params = $_GET;
                    $params['status'] = 'Aguardando';
                    echo http_build_query($params);
                ?>" style="padding: 10px 15px; color: #fd7e14; font-weight: bold;">
                    <i class="bx bx-circle" style="color: #fd7e14;"></i> Aguardando
                    <?php if (isset($contadores_status['Aguardando']) && $contadores_status['Aguardando'] > 0): ?>
                        <span class="badge" style="background-color: #fd7e14; margin-left: 5px;"><?php echo $contadores_status['Aguardando']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="<?= $this->input->get('status') == 'Aprovada' ? 'active' : '' ?>">
                <a href="<?php echo base_url(); ?>index.php/propostas/gerenciar?<?php 
                    $params = $_GET;
                    $params['status'] = 'Aprovada';
                    echo http_build_query($params);
                ?>" style="padding: 10px 15px; color: #28a745; font-weight: bold;">
                    <i class="bx bx-circle" style="color: #28a745;"></i> Aprovada
                    <?php if (isset($contadores_status['Aprovada']) && $contadores_status['Aprovada'] > 0): ?>
                        <span class="badge" style="background-color: #28a745; margin-left: 5px;"><?php echo $contadores_status['Aprovada']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="<?= $this->input->get('status') == 'Não aprovada' ? 'active' : '' ?>">
                <a href="<?php echo base_url(); ?>index.php/propostas/gerenciar?<?php 
                    $params = $_GET;
                    $params['status'] = 'Não aprovada';
                    echo http_build_query($params);
                ?>" style="padding: 10px 15px; color: #dc3545; font-weight: bold;">
                    <i class="bx bx-circle" style="color: #dc3545;"></i> Não aprovada
                    <?php if (isset($contadores_status['Não aprovada']) && $contadores_status['Não aprovada'] > 0): ?>
                        <span class="badge" style="background-color: #dc3545; margin-left: 5px;"><?php echo $contadores_status['Não aprovada']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="<?= $this->input->get('status') == 'Concluído' ? 'active' : '' ?>">
                <a href="<?php echo base_url(); ?>index.php/propostas/gerenciar?<?php 
                    $params = $_GET;
                    $params['status'] = 'Concluído';
                    echo http_build_query($params);
                ?>" style="padding: 10px 15px; color: #28a745; font-weight: bold;">
                    <i class="bx bx-circle" style="color: #28a745;"></i> Concluído
                    <?php if (isset($contadores_status['Concluído']) && $contadores_status['Concluído'] > 0): ?>
                        <span class="badge" style="background-color: #28a745; margin-left: 5px;"><?php echo $contadores_status['Concluído']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="<?= $this->input->get('status') == 'Modelo' ? 'active' : '' ?>">
                <a href="<?php echo base_url(); ?>index.php/propostas/gerenciar?<?php 
                    $params = $_GET;
                    $params['status'] = 'Modelo';
                    echo http_build_query($params);
                ?>" style="padding: 10px 15px; color: #6f42c1; font-weight: bold;">
                    <i class="bx bx-circle" style="color: #6f42c1;"></i> Modelo
                    <?php if (isset($contadores_status['Modelo']) && $contadores_status['Modelo'] > 0): ?>
                        <span class="badge" style="background-color: #6f42c1; margin-left: 5px;"><?php echo $contadores_status['Modelo']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>
    </div>

    <div class="widget-box" style="margin-top: 8px">
        <div class="widget-content nopadding">
            <div class="table-responsive">
                <table class="table table-bordered ">
                    <thead>
                        <tr>
                            <th style="width: 50px;"></th>
                            <th>N°</th>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Validade</th>
                            <th>Valor Total</th>
                            <th>Status</th>
                            <th>Vendedor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$results) {
                            echo '<tr>
                            <td colspan="8" style="text-align: center;">Nenhuma proposta cadastrada</td>
                            </tr>';
                        } else {
                            foreach ($results as $r) {
                                $dataProposta = date('d/m/Y', strtotime($r->data_proposta));
                                $dataValidade = $r->data_validade ? date('d/m/Y', strtotime($r->data_validade)) : '-';
                                $valorTotal = number_format($r->valor_total, 2, ',', '.');
                                
                                // Cores dos status
                                $corStatus = '#6c757d'; // default
                                switch($r->status) {
                                    case 'Em aberto': $corStatus = '#17a2b8'; break;
                                    case 'Rascunho': $corStatus = '#6c757d'; break;
                                    case 'Pendente': $corStatus = '#ffc107'; break;
                                    case 'Aguardando': $corStatus = '#fd7e14'; break;
                                    case 'Aprovada': $corStatus = '#28a745'; break;
                                    case 'Não aprovada': $corStatus = '#dc3545'; break;
                                    case 'Concluído': $corStatus = '#28a745'; break;
                                    case 'Modelo': $corStatus = '#6f42c1'; break;
                                }
                                
                                // Número da proposta (apenas número)
                                $numeroProposta = $r->numero_proposta ?: $r->idProposta;
                                // Se tiver prefixo, remover
                                $numeroProposta = preg_replace('/[^0-9]/', '', $numeroProposta);
                                if (empty($numeroProposta)) {
                                    $numeroProposta = $r->idProposta;
                                }
                                ?>
                                <tr style="cursor: pointer;" onclick="if (!event.target.closest('.dropdown, .status-select-proposta')) { window.location.href='<?php echo base_url(); ?>index.php/propostas/visualizar/<?php echo $r->idProposta; ?>'; }" onmouseover="this.style.backgroundColor='#f5f5f5'" onmouseout="this.style.backgroundColor=''">
                                    <td style="width: 50px; text-align: center; position: relative;" onclick="event.stopPropagation();">
                                        <div class="dropdown-menu-proposta" style="position: relative; display: inline-block;">
                                            <button type="button" class="btn-menu-acoes" data-id="<?php echo $r->idProposta; ?>" onclick="event.stopPropagation(); toggleMenuAcoes(<?php echo $r->idProposta; ?>); return false;" style="background: none; border: none; padding: 5px 10px; font-size: 18px; color: #666; cursor: pointer;">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="menu-acoes-lista" id="menu-<?php echo $r->idProposta; ?>" style="display: none; position: absolute; right: 0; top: 100%; z-index: 10000; background: #fff; border: 1px solid #ccc; border-radius: 4px; padding: 5px 0; margin-top: 2px; min-width: 180px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); list-style: none; max-width: calc(100vw - 20px);">
                                                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vPropostas')) { ?>
                                                    <li><a href="<?php echo base_url(); ?>index.php/propostas/visualizar/<?php echo $r->idProposta; ?>" target="_blank" style="display: block; padding: 8px 15px; color: #333; text-decoration: none;"><i class="bx bx-show"></i> Visualizar</a></li>
                                                    <li><a href="<?php echo base_url(); ?>index.php/propostas/imprimir/<?php echo $r->idProposta; ?>" target="_blank" style="display: block; padding: 8px 15px; color: #333; text-decoration: none;"><i class="bx bx-printer"></i> Imprimir</a></li>
                                                <?php } ?>
                                                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'ePropostas')) { ?>
                                                    <li><a href="<?php echo base_url(); ?>index.php/propostas/editar/<?php echo $r->idProposta; ?>" style="display: block; padding: 8px 15px; color: #333; text-decoration: none;"><i class="bx bx-edit"></i> Editar</a></li>
                                                <?php } ?>
                                                <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dPropostas')) { ?>
                                                    <li style="height: 1px; margin: 5px 0; overflow: hidden; background-color: #e5e5e5;"></li>
                                                    <li><a href="#modalExcluir" role="button" data-toggle="modal" data-nome="<?php echo $numeroProposta; ?>" data-id="<?php echo $r->idProposta; ?>" class="link-excluir-proposta" style="display: block; padding: 8px 15px; color: #333; text-decoration: none;"><i class="bx bx-trash-alt"></i> Excluir</a></li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    </td>
                                    <td><?php echo $numeroProposta; ?></td>
                                    <td><?php echo $r->nomeCliente; ?></td>
                                    <td><?php echo $dataProposta; ?></td>
                                    <td><?php echo $dataValidade; ?></td>
                                    <td><strong>R$ <?php echo $valorTotal; ?></strong></td>
                                    <td onclick="event.stopPropagation();">
                                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'ePropostas')) { ?>
                                            <select class="status-select-proposta" data-proposta-id="<?php echo $r->idProposta; ?>" data-status-atual="<?php echo htmlspecialchars($r->status); ?>" style="background-color: <?php echo $corStatus; ?>; border-color: <?php echo $corStatus; ?>; color: white; padding: 4px 8px; border-radius: 4px; border: 1px solid <?php echo $corStatus; ?>; font-size: 12px; font-weight: bold; cursor: pointer; min-width: 140px;">
                                                <option value="Em aberto" <?= $r->status == 'Em aberto' ? 'selected' : '' ?> style="background: #17a2b8;">Em aberto</option>
                                                <option value="Rascunho" <?= $r->status == 'Rascunho' ? 'selected' : '' ?> style="background: #6c757d;">Rascunho</option>
                                                <option value="Pendente" <?= $r->status == 'Pendente' ? 'selected' : '' ?> style="background: #ffc107;">Pendente</option>
                                                <option value="Aguardando" <?= $r->status == 'Aguardando' ? 'selected' : '' ?> style="background: #fd7e14;">Aguardando</option>
                                                <option value="Aprovada" <?= $r->status == 'Aprovada' ? 'selected' : '' ?> style="background: #28a745;">Aprovada</option>
                                                <option value="Não aprovada" <?= $r->status == 'Não aprovada' ? 'selected' : '' ?> style="background: #dc3545;">Não aprovada</option>
                                                <option value="Concluído" <?= $r->status == 'Concluído' ? 'selected' : '' ?> style="background: #28a745;">Concluído</option>
                                                <option value="Modelo" <?= $r->status == 'Modelo' ? 'selected' : '' ?> style="background: #6f42c1;">Modelo</option>
                                            </select>
                                        <?php } else { ?>
                                            <span class="badge badge-<?php 
                                                $statusClass = 'default';
                                                switch($r->status) {
                                                    case 'Em aberto': $statusClass = 'info'; break;
                                                    case 'Rascunho': $statusClass = 'secondary'; break;
                                                    case 'Pendente': $statusClass = 'warning'; break;
                                                    case 'Aguardando': $statusClass = 'warning'; break;
                                                    case 'Aprovada': $statusClass = 'success'; break;
                                                    case 'Não aprovada': $statusClass = 'danger'; break;
                                                    case 'Concluído': $statusClass = 'success'; break;
                                                    case 'Modelo': $statusClass = 'primary'; break;
                                                }
                                                echo $statusClass;
                                            ?>"><?php echo $r->status; ?></span>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo $r->nome; ?></td>
                                </tr>
                            <?php }
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php echo $this->pagination->create_links(); ?>
</div>

<!-- Modal Excluir -->
<div id="modalExcluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url(); ?>index.php/propostas/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Excluir Proposta</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="id" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir a proposta <span id="nome"></span>?</h5>
        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button class="btn btn-danger">Excluir</button>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(".datepicker").datepicker({
            dateFormat: 'dd/mm/yy'
        });
        
        // Função para toggle do menu
        window.toggleMenuAcoes = function(id) {
            var $menu = $('#menu-' + id);
            var $allMenus = $('.menu-acoes-lista');
            var $button = $('.btn-menu-acoes[data-id="' + id + '"]');
            
            // Fechar todos os menus
            $allMenus.not($menu).hide();
            
            // Toggle do menu atual
            if ($menu.is(':visible')) {
                $menu.hide();
            } else {
                // Calcular posição para mobile
                if ($(window).width() <= 768) {
                    var buttonOffset = $button.offset();
                    var buttonHeight = $button.outerHeight();
                    var buttonWidth = $button.outerWidth();
                    var scrollTop = $(window).scrollTop();
                    var windowHeight = $(window).height();
                    var windowWidth = $(window).width();
                    
                    // Calcular posição do menu alinhado ao botão
                    var estimatedMenuHeight = 250;
                    var menuWidth = 200; // largura estimada do menu
                    
                    // Top: logo abaixo do botão
                    var menuTop = buttonOffset.top + buttonHeight + 5;
                    
                    // Left: alinhar menu com a borda direita do botão
                    // O menu deve começar onde o botão termina (buttonOffset.left + buttonWidth)
                    // Mas como o menu tem largura, precisamos ajustar para não sair da tela
                    var menuLeft = buttonOffset.left + buttonWidth - menuWidth; // Alinhar borda direita do menu com borda direita do botão
                    
                    // Se o menu sair da tela à esquerda, alinhar à esquerda do botão
                    if (menuLeft < 10) {
                        menuLeft = buttonOffset.left;
                        // Se ainda sair, ajustar para não sair da tela
                        if (menuLeft + menuWidth > windowWidth) {
                            menuLeft = windowWidth - menuWidth - 10;
                        }
                    }
                    // Se o menu sair da tela à direita, ajustar
                    if (menuLeft + menuWidth > windowWidth - 10) {
                        menuLeft = windowWidth - menuWidth - 10;
                    }
                    
                    // Se não houver espaço abaixo, posicionar acima
                    if (menuTop + estimatedMenuHeight > scrollTop + windowHeight) {
                        menuTop = buttonOffset.top - estimatedMenuHeight - 5;
                        if (menuTop < scrollTop + 10) {
                            menuTop = scrollTop + 10;
                        }
                    }
                    
                    // Garantir que o menu não saia da tela no topo
                    if (menuTop < scrollTop + 10) {
                        menuTop = scrollTop + 10;
                    }
                    // Garantir que o menu não saia da tela embaixo
                    if (menuTop + estimatedMenuHeight > scrollTop + windowHeight) {
                        menuTop = Math.max(scrollTop + 10, scrollTop + windowHeight - estimatedMenuHeight - 10);
                    }
                    
                    $menu.css({
                        'position': 'fixed',
                        'top': menuTop + 'px',
                        'left': menuLeft + 'px',
                        'right': 'auto',
                        'bottom': 'auto'
                    });
                } else {
                    // Desktop: usar posição absoluta padrão
                    $menu.css({
                        'position': 'absolute',
                        'top': '100%',
                        'right': '0',
                        'left': 'auto',
                        'bottom': 'auto'
                    });
                }
                
                $menu.show();
            }
        };
        
        // Menu de ações simplificado (backup)
        $(document).on('click', '.btn-menu-acoes', function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            var id = $(this).attr('data-id');
            toggleMenuAcoes(id);
        });
        
        // Fechar menu ao clicar fora
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown-menu-proposta').length) {
                $('.menu-acoes-lista').hide();
            }
        });
        
        // Fechar menu ao clicar em um link (exceto excluir)
        $(document).on('click', '.menu-acoes-lista a', function(e) {
            if (!$(this).hasClass('link-excluir-proposta')) {
                $('.menu-acoes-lista').hide();
            }
        });

        // Capturar clique no link de excluir ANTES de abrir o modal
        $('a[href="#modalExcluir"]').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var id = $(this).data('id') || $(this).attr('data-id');
            var nome = $(this).data('nome') || $(this).attr('data-nome');
            
            // Preencher o modal antes de abrir
            $('#modalExcluir #id').val(id);
            $('#modalExcluir #nome').text(nome);
            
            // Abrir modal
            $('#modalExcluir').modal('show');
        });
        
        // Submeter formulário de exclusão
        $('#modalExcluir form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var id = form.find('#id').val();
            
            if (!id || id === '' || id === 'undefined' || id === null) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'ID da proposta não encontrado.'
                });
                return false;
            }
            
            // Obter token CSRF
            var csrfTokenName = '<?php echo $this->security->get_csrf_token_name(); ?>';
            var csrfTokenValue = '<?php echo $this->security->get_csrf_hash(); ?>';
            
            var postData = {};
            postData.id = id;
            postData[csrfTokenName] = csrfTokenValue;
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: postData,
                dataType: 'json',
                success: function(response) {
                    $('#modalExcluir').modal('hide');
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: response.message || 'Proposta excluída com sucesso!',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: response.message || 'Erro ao excluir proposta.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    $('#modalExcluir').modal('hide');
                    var message = 'Erro ao excluir proposta.';
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: message
                    });
                }
            });
            
            return false;
        });
        
        // Atualizar status da proposta via dropdown
        $(document).on('change', '.status-select-proposta', function(e) {
            e.stopPropagation();
            
            var $select = $(this);
            var idProposta = $select.data('proposta-id');
            var statusAtual = $select.data('status-atual');
            var novoStatus = $select.val();
            
            // Limpar espaços e normalizar
            novoStatus = novoStatus ? novoStatus.trim() : '';
            statusAtual = statusAtual ? statusAtual.trim() : '';
            
            // Se não mudou, não fazer nada
            if (novoStatus === statusAtual) {
                $select.val(statusAtual);
                return false;
            }
            
            // Confirmar mudança
            Swal.fire({
                title: 'Alterar Status?',
                text: 'Deseja alterar o status de "' + statusAtual + '" para "' + novoStatus + '"?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, alterar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545'
            }).then(function(result) {
                if (result.isConfirmed) {
                    // Desabilitar select
                    $select.prop('disabled', true);
                    
                    // Obter token CSRF
                    var csrfTokenName = '<?php echo $this->security->get_csrf_token_name(); ?>';
                    var csrfTokenValue = '<?php echo $this->security->get_csrf_hash(); ?>';
                    
                    var postData = {};
                    postData.idProposta = idProposta;
                    postData.status = novoStatus;
                    postData[csrfTokenName] = csrfTokenValue;
                    
                    $.ajax({
                        url: '<?php echo base_url(); ?>index.php/propostas/atualizarStatus',
                        type: 'POST',
                        data: postData,
                        dataType: 'json',
                        success: function(response) {
                            if (response.result) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sucesso!',
                                    text: response.message || 'Status atualizado com sucesso!',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro!',
                                    text: response.message || 'Erro ao atualizar status'
                                });
                                $select.val(statusAtual);
                                $select.prop('disabled', false);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Erro AJAX:', error);
                            var message = 'Erro ao comunicar com o servidor: ' + error;
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
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro!',
                                text: message
                            });
                            $select.val(statusAtual);
                            $select.prop('disabled', false);
                        }
                    });
                } else {
                    // Usuário cancelou, restaurar valor
                    $select.val(statusAtual);
                }
            });
        });
    });
</script>

