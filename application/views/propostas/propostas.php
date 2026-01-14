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
        .menu-desktop {
            display: none !important;
        }
        .btn-menu-acoes {
            padding: 8px 12px !important;
            font-size: 20px !important;
        }
        .table tbody tr td:first-child {
            width: 60px !important;
        }
    }
    @media (min-width: 769px) {
        .modal-menu-mobile {
            display: none !important;
        }
    }
    /* Modal Menu Mobile */
    .modal-menu-mobile {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 10001;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-menu-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
    }
    .modal-menu-content {
        position: relative;
        background: #fff;
        border-radius: 8px;
        max-height: 80vh;
        max-width: 400px;
        width: 100%;
        overflow-y: auto;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        animation: fadeInScale 0.3s ease-out;
    }
    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    .modal-menu-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #e5e5e5;
    }
    .modal-menu-header h4 {
        margin: 0;
        font-size: 18px;
        font-weight: bold;
    }
    .btn-fechar-menu {
        background: none;
        border: none;
        font-size: 24px;
        color: #666;
        cursor: pointer;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .modal-menu-list {
        list-style: none;
        margin: 0;
        padding: 10px 0;
    }
    .modal-menu-list li {
        border-bottom: 1px solid #f5f5f5;
    }
    .modal-menu-list li:last-child {
        border-bottom: none;
    }
    .modal-menu-list li a {
        display: flex;
        align-items: center;
        padding: 18px 20px;
        color: #333;
        text-decoration: none;
        font-size: 16px;
    }
    .modal-menu-list li a:active {
        background-color: #f5f5f5;
    }
    .modal-menu-list li a i {
        margin-right: 15px;
        font-size: 20px;
        width: 24px;
    }
    .modal-menu-list li.divider {
        height: 1px;
        background-color: #e5e5e5;
        margin: 10px 0;
        border-bottom: none;
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
                    <option value="Aberto" <?=$this->input->get('status') == 'Aberto' ? 'selected' : ''?>>Aberto</option>
                    <option value="Faturado" <?=$this->input->get('status') == 'Faturado' ? 'selected' : ''?>>Faturado</option>
                    <option value="Negociação" <?=$this->input->get('status') == 'Negociação' ? 'selected' : ''?>>Negociação</option>
                    <option value="Em Andamento" <?=$this->input->get('status') == 'Em Andamento' ? 'selected' : ''?>>Em Andamento</option>
                    <option value="Orçamento" <?=$this->input->get('status') == 'Orçamento' ? 'selected' : ''?>>Orçamento</option>
                    <option value="Finalizado" <?=$this->input->get('status') == 'Finalizado' ? 'selected' : ''?>>Finalizado</option>
                    <option value="Cancelado" <?=$this->input->get('status') == 'Cancelado' ? 'selected' : ''?>>Cancelado</option>
                    <option value="Aguardando Peças" <?=$this->input->get('status') == 'Aguardando Peças' ? 'selected' : ''?>>Aguardando Peças</option>
                    <option value="Aprovado" <?=$this->input->get('status') == 'Aprovado' ? 'selected' : ''?>>Aprovado</option>
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
            <li class="<?= $this->input->get('status') == 'Aberto' ? 'active' : '' ?>">
                <a href="<?php echo base_url(); ?>index.php/propostas/gerenciar?<?php 
                    $params = $_GET;
                    $params['status'] = 'Aberto';
                    echo http_build_query($params);
                ?>" style="padding: 10px 15px; color: #00cd00; font-weight: bold;">
                    <i class="bx bx-circle" style="color: #00cd00;"></i> Aberto
                    <?php if (isset($contadores_status['Aberto']) && $contadores_status['Aberto'] > 0): ?>
                        <span class="badge" style="background-color: #00cd00; margin-left: 5px;"><?php echo $contadores_status['Aberto']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="<?= $this->input->get('status') == 'Faturado' ? 'active' : '' ?>">
                <a href="<?php echo base_url(); ?>index.php/propostas/gerenciar?<?php 
                    $params = $_GET;
                    $params['status'] = 'Faturado';
                    echo http_build_query($params);
                ?>" style="padding: 10px 15px; color: #B266FF; font-weight: bold;">
                    <i class="bx bx-circle" style="color: #B266FF;"></i> Faturado
                    <?php if (isset($contadores_status['Faturado']) && $contadores_status['Faturado'] > 0): ?>
                        <span class="badge" style="background-color: #B266FF; margin-left: 5px;"><?php echo $contadores_status['Faturado']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="<?= $this->input->get('status') == 'Negociação' ? 'active' : '' ?>">
                <a href="<?php echo base_url(); ?>index.php/propostas/gerenciar?<?php 
                    $params = $_GET;
                    $params['status'] = 'Negociação';
                    echo http_build_query($params);
                ?>" style="padding: 10px 15px; color: #AEB404; font-weight: bold;">
                    <i class="bx bx-circle" style="color: #AEB404;"></i> Negociação
                    <?php if (isset($contadores_status['Negociação']) && $contadores_status['Negociação'] > 0): ?>
                        <span class="badge" style="background-color: #AEB404; margin-left: 5px;"><?php echo $contadores_status['Negociação']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="<?= $this->input->get('status') == 'Em Andamento' ? 'active' : '' ?>">
                <a href="<?php echo base_url(); ?>index.php/propostas/gerenciar?<?php 
                    $params = $_GET;
                    $params['status'] = 'Em Andamento';
                    echo http_build_query($params);
                ?>" style="padding: 10px 15px; color: #436eee; font-weight: bold;">
                    <i class="bx bx-circle" style="color: #436eee;"></i> Em Andamento
                    <?php if (isset($contadores_status['Em Andamento']) && $contadores_status['Em Andamento'] > 0): ?>
                        <span class="badge" style="background-color: #436eee; margin-left: 5px;"><?php echo $contadores_status['Em Andamento']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="<?= $this->input->get('status') == 'Orçamento' ? 'active' : '' ?>">
                <a href="<?php echo base_url(); ?>index.php/propostas/gerenciar?<?php 
                    $params = $_GET;
                    $params['status'] = 'Orçamento';
                    echo http_build_query($params);
                ?>" style="padding: 10px 15px; color: #CDB380; font-weight: bold;">
                    <i class="bx bx-circle" style="color: #CDB380;"></i> Orçamento
                    <?php if (isset($contadores_status['Orçamento']) && $contadores_status['Orçamento'] > 0): ?>
                        <span class="badge" style="background-color: #CDB380; margin-left: 5px;"><?php echo $contadores_status['Orçamento']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="<?= $this->input->get('status') == 'Finalizado' ? 'active' : '' ?>">
                <a href="<?php echo base_url(); ?>index.php/propostas/gerenciar?<?php 
                    $params = $_GET;
                    $params['status'] = 'Finalizado';
                    echo http_build_query($params);
                ?>" style="padding: 10px 15px; color: #256; font-weight: bold;">
                    <i class="bx bx-circle" style="color: #256;"></i> Finalizado
                    <?php if (isset($contadores_status['Finalizado']) && $contadores_status['Finalizado'] > 0): ?>
                        <span class="badge" style="background-color: #256; margin-left: 5px;"><?php echo $contadores_status['Finalizado']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="<?= $this->input->get('status') == 'Cancelado' ? 'active' : '' ?>">
                <a href="<?php echo base_url(); ?>index.php/propostas/gerenciar?<?php 
                    $params = $_GET;
                    $params['status'] = 'Cancelado';
                    echo http_build_query($params);
                ?>" style="padding: 10px 15px; color: #CD0000; font-weight: bold;">
                    <i class="bx bx-circle" style="color: #CD0000;"></i> Cancelado
                    <?php if (isset($contadores_status['Cancelado']) && $contadores_status['Cancelado'] > 0): ?>
                        <span class="badge" style="background-color: #CD0000; margin-left: 5px;"><?php echo $contadores_status['Cancelado']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="<?= $this->input->get('status') == 'Aguardando Peças' ? 'active' : '' ?>">
                <a href="<?php echo base_url(); ?>index.php/propostas/gerenciar?<?php 
                    $params = $_GET;
                    $params['status'] = 'Aguardando Peças';
                    echo http_build_query($params);
                ?>" style="padding: 10px 15px; color: #FF7F00; font-weight: bold;">
                    <i class="bx bx-circle" style="color: #FF7F00;"></i> Aguardando Peças
                    <?php if (isset($contadores_status['Aguardando Peças']) && $contadores_status['Aguardando Peças'] > 0): ?>
                        <span class="badge" style="background-color: #FF7F00; margin-left: 5px;"><?php echo $contadores_status['Aguardando Peças']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="<?= $this->input->get('status') == 'Aprovado' ? 'active' : '' ?>">
                <a href="<?php echo base_url(); ?>index.php/propostas/gerenciar?<?php 
                    $params = $_GET;
                    $params['status'] = 'Aprovado';
                    echo http_build_query($params);
                ?>" style="padding: 10px 15px; color: #808080; font-weight: bold;">
                    <i class="bx bx-circle" style="color: #808080;"></i> Aprovado
                    <?php if (isset($contadores_status['Aprovado']) && $contadores_status['Aprovado'] > 0): ?>
                        <span class="badge" style="background-color: #808080; margin-left: 5px;"><?php echo $contadores_status['Aprovado']; ?></span>
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
                                
                                // Cores dos status (mesmas de OS)
                                $corStatus = '#E0E4CC'; // default
                                switch($r->status) {
                                    case 'Aberto': $corStatus = '#00cd00'; break;
                                    case 'Faturado': $corStatus = '#B266FF'; break;
                                    case 'Negociação': $corStatus = '#AEB404'; break;
                                    case 'Em Andamento': $corStatus = '#436eee'; break;
                                    case 'Orçamento': $corStatus = '#CDB380'; break;
                                    case 'Finalizado': $corStatus = '#256'; break;
                                    case 'Cancelado': $corStatus = '#CD0000'; break;
                                    case 'Aguardando Peças': $corStatus = '#FF7F00'; break;
                                    case 'Aprovado': $corStatus = '#808080'; break;
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
                                            <!-- Menu Desktop -->
                                            <ul class="menu-acoes-lista menu-desktop" id="menu-<?php echo $r->idProposta; ?>" style="display: none; position: absolute; right: 0; top: 100%; z-index: 10000; background: #fff; border: 1px solid #ccc; border-radius: 4px; padding: 5px 0; margin-top: 2px; min-width: 180px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); list-style: none;">
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
                                                <option value="Aberto" <?= $r->status == 'Aberto' ? 'selected' : '' ?> style="background: #00cd00;">Aberto</option>
                                                <option value="Faturado" <?= $r->status == 'Faturado' ? 'selected' : '' ?> style="background: #B266FF;">Faturado</option>
                                                <option value="Negociação" <?= $r->status == 'Negociação' ? 'selected' : '' ?> style="background: #AEB404;">Negociação</option>
                                                <option value="Em Andamento" <?= $r->status == 'Em Andamento' ? 'selected' : '' ?> style="background: #436eee;">Em Andamento</option>
                                                <option value="Orçamento" <?= $r->status == 'Orçamento' ? 'selected' : '' ?> style="background: #CDB380;">Orçamento</option>
                                                <option value="Finalizado" <?= $r->status == 'Finalizado' ? 'selected' : '' ?> style="background: #256;">Finalizado</option>
                                                <option value="Cancelado" <?= $r->status == 'Cancelado' ? 'selected' : '' ?> style="background: #CD0000;">Cancelado</option>
                                                <option value="Aguardando Peças" <?= $r->status == 'Aguardando Peças' ? 'selected' : '' ?> style="background: #FF7F00;">Aguardando Peças</option>
                                                <option value="Aprovado" <?= $r->status == 'Aprovado' ? 'selected' : '' ?> style="background: #808080;">Aprovado</option>
                                            </select>
                                        <?php } else { ?>
                                            <span class="badge" style="background-color: <?php echo $corStatus; ?>; border-color: <?php echo $corStatus; ?>; color: white;"><?php echo $r->status; ?></span>
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

<!-- Modal Menu Mobile -->
<div id="modalMenuMobile" class="modal-menu-mobile" style="display: none;">
    <div class="modal-menu-backdrop" onclick="fecharMenuMobile();"></div>
    <div class="modal-menu-content">
        <div class="modal-menu-header">
            <h4>Ações</h4>
            <button type="button" class="btn-fechar-menu" onclick="fecharMenuMobile();">
                <i class="bx bx-x"></i>
            </button>
        </div>
        <ul class="modal-menu-list" id="modalMenuList">
            <!-- Itens serão preenchidos via JavaScript -->
        </ul>
    </div>
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

<!-- Modal Faturamento -->
<div id="modal-faturar" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modalFaturarLabel" aria-hidden="true" style="width: 90%; max-width: 1200px; margin-left: -45%;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h5 id="modalFaturarLabel"><i class="bx bx-dollar-circle"></i> Faturar Proposta #<span id="faturar-proposta-id"></span></h5>
    </div>
    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
        <input type="hidden" id="faturar-idProposta" value="" />
        
        <div class="span12" style="margin-left: 0; background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            <strong>Valor Total da Proposta:</strong> 
            <span id="faturar-valor-total" style="font-size: 18px; color: #28a745; font-weight: bold;">R$ 0,00</span>
        </div>
        
        <div class="span12" style="margin-left: 0; margin-bottom: 10px;">
            <label><input type="checkbox" id="criar-lancamento" checked /> Gerar lançamento no financeiro</label>
        </div>
        
        <div id="opcoes-faturamento">
            <div class="span12" style="margin-left: 0; margin-bottom: 15px;">
                <h5>Parcelas de Pagamento</h5>
                <p style="color: #666; font-size: 12px;">Edite as informações de cada parcela antes de confirmar o faturamento.</p>
            </div>
            
            <div class="span12" style="margin-left: 0; overflow-x: auto;">
                <table class="table table-bordered" id="tabela-faturamento">
                    <thead>
                        <tr>
                            <th width="5%">Nº</th>
                            <th width="10%">Dias</th>
                            <th width="12%">Data Vencimento</th>
                            <th width="15%">Valor</th>
                            <th width="18%">Forma de Pagamento</th>
                            <th width="30%">Conta Bancária</th>
                            <th width="10%">Status</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-faturamento">
                        <!-- Parcelas serão inseridas aqui via JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <div class="span12" style="margin-left: 0; margin-top: 15px; padding: 10px; background: #e9ecef; border-radius: 5px;">
                <strong>Resumo:</strong>
                <div id="resumo-faturamento" style="margin-top: 5px; color: #666;">
                    <span id="total-parcelas">0</span> parcela(s) | Total: R$ <span id="total-valor">0,00</span>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer" style="display:flex;justify-content: center">
        <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true" id="btn-cancelar-faturar">
            <span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
        <button class="button btn btn-success" id="btn-confirmar-faturar">
            <span class="button__icon"><i class='bx bx-check'></i></span><span class="button__text2">Confirmar Faturamento</span></button>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(".datepicker").datepicker({
            dateFormat: 'dd/mm/yy'
        });
        
        // Função para toggle do menu
        window.toggleMenuAcoes = function(id) {
            // Verificar se é mobile
            if ($(window).width() <= 768) {
                // Mobile: usar modal
                abrirMenuMobile(id);
            } else {
                // Desktop: usar dropdown
                var $menu = $('#menu-' + id);
                var $allMenus = $('.menu-acoes-lista');
                
                // Fechar todos os menus
                $allMenus.not($menu).hide();
                
                // Toggle do menu atual
                if ($menu.is(':visible')) {
                    $menu.hide();
                } else {
                    $menu.css({
                        'position': 'absolute',
                        'top': '100%',
                        'right': '0',
                        'left': 'auto',
                        'bottom': 'auto'
                    }).show();
                }
            }
        };
        
        // Função para abrir menu mobile
        window.abrirMenuMobile = function(id) {
            var $button = $('.btn-menu-acoes[data-id="' + id + '"]');
            var $menu = $('#menu-' + id);
            var $modal = $('#modalMenuMobile');
            var $modalList = $('#modalMenuList');
            var $widgetContent = $('.widget-content').first();
            
            // Limpar lista anterior
            $modalList.empty();
            
            // Copiar itens do menu para o modal
            $menu.find('li').each(function() {
                var $item = $(this).clone();
                $item.find('a').on('click', function() {
                    fecharMenuMobile();
                });
                $modalList.append($item);
            });
            
            // Mostrar modal
            $modal.show();
            $('body').css('overflow', 'hidden');
        };
        
        // Função para fechar menu mobile
        window.fecharMenuMobile = function() {
            $('#modalMenuMobile').hide();
            $('body').css('overflow', '');
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
            
            // Se mudar para Faturado ou Finalizado, abrir modal de faturamento
            var isFaturado = novoStatus === 'Faturado' || 
                            novoStatus.trim() === 'Faturado' || 
                            (novoStatus && novoStatus.indexOf('Faturado') !== -1);
            
            var isFinalizado = novoStatus === 'Finalizado' || 
                            novoStatus.trim() === 'Finalizado' || 
                            (novoStatus && novoStatus.indexOf('Finalizado') !== -1);
            
            if (isFaturado || isFinalizado) {
                $select.prop('disabled', true);
                
                // Buscar dados da Proposta
                $.ajax({
                    url: '<?php echo base_url(); ?>index.php/propostas/getDadosPropostaJson/' + idProposta,
                    type: 'GET',
                    dataType: 'json',
                    timeout: 10000,
                    success: function(data) {
                        if (data && data.result) {
                            // Verificar se já tem lançamento
                            if (data.temLancamento) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Atenção',
                                    text: 'Esta proposta já possui um lançamento financeiro vinculado.',
                                    showCancelButton: true,
                                    confirmButtonText: 'Continuar mesmo assim',
                                    cancelButtonText: 'Cancelar'
                                }).then(function(result) {
                                    if (result.isConfirmed) {
                                        abrirModalFaturar(idProposta, data.valorTotal, $select, statusAtual, novoStatus);
                                    } else {
                                        $select.val(statusAtual);
                                        $select.prop('disabled', false);
                                    }
                                });
                            } else {
                                abrirModalFaturar(idProposta, data.valorTotal, $select, statusAtual, novoStatus);
                            }
                        } else {
                            Swal.fire('Erro', data && data.message ? data.message : 'Erro ao buscar dados da proposta', 'error');
                            $select.val(statusAtual);
                            $select.prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Erro', 'Erro ao buscar dados da proposta: ' + error, 'error');
                        $select.val(statusAtual);
                        $select.prop('disabled', false);
                    }
                });
                return false; // Impedir que continue para outros status
            }
            
            // Para outros status, confirmar e atualizar normalmente
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
        
        // Variável para armazenar parcelas do faturamento
        var parcelasFaturamento = [];
        var dataBaseVencimento = '<?php echo date('d/m/Y'); ?>';
        var contasBancarias = [];
        
        // Carregar contas bancárias
        $.ajax({
            url: '<?php echo base_url(); ?>index.php/contas/getAll',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                contasBancarias = data || [];
            },
            error: function() {
                console.error('Erro ao carregar contas bancárias');
            }
        });
        
        // Função para abrir modal de faturamento
        function abrirModalFaturar(idProposta, valorTotal, $select, statusAnterior, novoStatus) {
            // Salvar referência para reverter se cancelar
            faturamentoPendente = {
                $select: $select,
                statusAnterior: statusAnterior,
                novoStatus: novoStatus
            };
            
            // Preencher modal básico
            $('#faturar-idProposta').val(idProposta);
            $('#faturar-proposta-id').text(idProposta);
            $('#faturar-valor-total').text('R$ ' + valorTotal.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
            $('#criar-lancamento').prop('checked', true);
            
            // Buscar parcelas da Proposta
            $.ajax({
                url: '<?php echo base_url(); ?>index.php/propostas/getDadosPropostaJson/' + idProposta,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.result) {
                        if (data.parcelas && data.parcelas.length > 0) {
                            // Carregar parcelas existentes
                            parcelasFaturamento = data.parcelas.map(function(p) {
                                return {
                                    id: p.id,
                                    numero: p.numero,
                                    dias: p.dias,
                                    valor: p.valor,
                                    observacao: p.observacao || '',
                                    data_vencimento: p.data_vencimento || '',
                                    forma_pgto: p.forma_pgto || '',
                                    conta_id: '',
                                    status: p.status || 'pendente'
                                };
                            });
                        } else {
                            // Se não tem parcelas, criar uma parcela única
                            parcelasFaturamento = [{
                                id: null,
                                numero: 1,
                                dias: 0,
                                valor: valorTotal,
                                observacao: '',
                                data_vencimento: '',
                                forma_pgto: '',
                                conta_id: '',
                                status: 'pendente'
                            }];
                        }
                        
                        atualizarTabelaFaturamento();
                        $('#opcoes-faturamento').show();
                        
                        // Abrir modal
                        var $modal = $('#modal-faturar');
                        if ($modal.length > 0) {
                            $modal.removeClass('hide');
                            $modal.modal('show');
                            
                            setTimeout(function() {
                                if (!$modal.is(':visible')) {
                                    $modal.removeClass('hide fade').addClass('in');
                                    $modal.css({
                                        'display': 'block',
                                        'z-index': 1050
                                    });
                                    if ($('.modal-backdrop').length === 0) {
                                        $('body').append('<div class="modal-backdrop fade in"></div>');
                                    }
                                }
                            }, 200);
                        } else {
                            Swal.fire('Erro', 'Modal de faturamento não encontrado', 'error');
                        }
                    } else {
                        Swal.fire('Erro', data.message || 'Erro ao carregar dados da proposta', 'error');
                        if ($select) {
                            $select.val(statusAnterior);
                            $select.prop('disabled', false);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire('Erro', 'Erro ao carregar dados da proposta: ' + error, 'error');
                    if ($select) {
                        $select.val(statusAnterior);
                        $select.prop('disabled', false);
                    }
                }
            });
        }
        
        // Função para atualizar tabela de faturamento
        function atualizarTabelaFaturamento() {
            var tbody = $('#tbody-faturamento');
            tbody.empty();
            
            if (parcelasFaturamento.length === 0) {
                tbody.append('<tr><td colspan="7" style="text-align: center; color: #999;">Nenhuma parcela configurada</td></tr>');
                atualizarResumoFaturamento();
                return;
            }
            
            parcelasFaturamento.forEach(function(parcela, index) {
                // Calcular data de vencimento
                var dataVencimento = '';
                if (parcela.data_vencimento) {
                    var dataParts = parcela.data_vencimento.split('-');
                    if (dataParts.length === 3) {
                        dataVencimento = dataParts[2] + '/' + dataParts[1] + '/' + dataParts[0];
                    }
                } else if (parcela.dias > 0) {
                    var dataBase = new Date(dataBaseVencimento.split('/').reverse().join('-'));
                    dataBase.setDate(dataBase.getDate() + parcela.dias);
                    dataVencimento = ('0' + dataBase.getDate()).slice(-2) + '/' + 
                                   ('0' + (dataBase.getMonth() + 1)).slice(-2) + '/' + 
                                   dataBase.getFullYear();
                } else {
                    dataVencimento = dataBaseVencimento;
                }
                
                // Construir select de contas bancárias
                var selectContas = '<select class="span12 conta-faturar" data-index="' + index + '" style="width: 100%;"><option value="">Selecione a conta...</option>';
                contasBancarias.forEach(function(conta) {
                    var selected = (parcela.conta_id && parcela.conta_id == conta.idContas) ? ' selected' : '';
                    selectContas += '<option value="' + conta.idContas + '"' + selected + '>' + conta.conta + ' (' + conta.banco + ')' + '</option>';
                });
                selectContas += '</select>';
                
                var row = $('<tr>');
                row.append('<td style="text-align: center;">' + parcela.numero + '</td>');
                row.append('<td><input type="number" class="span12 dias-faturar" data-index="' + index + '" value="' + parcela.dias + '" min="0" style="width: 100%;" /></td>');
                row.append('<td><input type="text" class="span12 datepicker data-faturar" data-index="' + index + '" value="' + dataVencimento + '" style="width: 100%;" /></td>');
                row.append('<td><input type="text" class="span12 money valor-faturar" data-index="' + index + '" value="' + (parcela.valor > 0 ? parcela.valor.toFixed(2).replace('.', ',') : '0,00') + '" style="width: 100%;" /></td>');
                row.append('<td><select class="span12 forma-faturar" data-index="' + index + '" style="width: 100%;"><option value="">Selecione...</option><option value="Dinheiro"' + (parcela.forma_pgto === 'Dinheiro' ? ' selected' : '') + '>Dinheiro</option><option value="Pix"' + (parcela.forma_pgto === 'Pix' ? ' selected' : '') + '>Pix</option><option value="Cartão de Crédito"' + (parcela.forma_pgto === 'Cartão de Crédito' ? ' selected' : '') + '>Cartão de Crédito</option><option value="Cartão de Débito"' + (parcela.forma_pgto === 'Cartão de Débito' ? ' selected' : '') + '>Cartão de Débito</option><option value="Boleto"' + (parcela.forma_pgto === 'Boleto' ? ' selected' : '') + '>Boleto</option><option value="Transferência"' + (parcela.forma_pgto === 'Transferência' ? ' selected' : '') + '>Transferência</option><option value="Cheque"' + (parcela.forma_pgto === 'Cheque' ? ' selected' : '') + '>Cheque</option></select></td>');
                row.append('<td>' + selectContas + '</td>');
                row.append('<td style="text-align: center;"><span class="badge badge-' + (parcela.status === 'pago' ? 'success' : 'warning') + '">' + (parcela.status === 'pago' ? 'Pago' : 'Pendente') + '</span></td>');
                tbody.append(row);
            });
            
            // Aplicar máscaras e datepickers
            $('.money').mask('#.##0,00', {reverse: true});
            $('.datepicker').datepicker({
                dateFormat: 'dd/mm/yy'
            });
            
            // Eventos de edição
            $(document).off('change', '.dias-faturar').on('change', '.dias-faturar', function() {
                var index = $(this).data('index');
                parcelasFaturamento[index].dias = parseInt($(this).val()) || 0;
                var dataBase = new Date(dataBaseVencimento.split('/').reverse().join('-'));
                dataBase.setDate(dataBase.getDate() + parcelasFaturamento[index].dias);
                var novaData = ('0' + dataBase.getDate()).slice(-2) + '/' + 
                             ('0' + (dataBase.getMonth() + 1)).slice(-2) + '/' + 
                             dataBase.getFullYear();
                $(this).closest('tr').find('.data-faturar').val(novaData);
                atualizarResumoFaturamento();
            });
            
            $(document).off('blur', '.valor-faturar').on('blur', '.valor-faturar', function() {
                var index = $(this).data('index');
                var valor = $(this).val().replace('.', '').replace(',', '.');
                parcelasFaturamento[index].valor = parseFloat(valor) || 0;
                atualizarResumoFaturamento();
            });
            
            $(document).off('change', '.data-faturar').on('change', '.data-faturar', function() {
                var index = $(this).data('index');
                var dataStr = $(this).val();
                if (dataStr) {
                    var dataParts = dataStr.split('/');
                    if (dataParts.length === 3) {
                        parcelasFaturamento[index].data_vencimento = dataParts[2] + '-' + dataParts[1] + '-' + dataParts[0];
                    }
                }
            });
            
            $(document).off('change', '.forma-faturar').on('change', '.forma-faturar', function() {
                var index = $(this).data('index');
                parcelasFaturamento[index].forma_pgto = $(this).val();
            });
            
            $(document).off('change', '.conta-faturar').on('change', '.conta-faturar', function() {
                var index = $(this).data('index');
                parcelasFaturamento[index].conta_id = $(this).val();
            });
            
            atualizarResumoFaturamento();
        }
        
        // Função para atualizar resumo do faturamento
        function atualizarResumoFaturamento() {
            var totalParcelas = parcelasFaturamento.length;
            var totalValor = 0;
            
            parcelasFaturamento.forEach(function(p) {
                totalValor += p.valor || 0;
            });
            
            $('#total-parcelas').text(totalParcelas);
            $('#total-valor').text(totalValor.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
        }
        
        // Variável para armazenar dados do faturamento pendente
        var faturamentoPendente = null;
        
        // Mostrar/ocultar opções de faturamento
        $('#criar-lancamento').on('change', function() {
            if ($(this).is(':checked')) {
                $('#opcoes-faturamento').slideDown();
            } else {
                $('#opcoes-faturamento').slideUp();
            }
        });
        
        // Confirmar faturamento
        $('#btn-confirmar-faturar').on('click', function() {
            var idProposta = $('#faturar-idProposta').val();
            var criarLancamento = $('#criar-lancamento').is(':checked') ? 1 : 0;
            
            // Validar parcelas
            if (parcelasFaturamento.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Configure pelo menos uma parcela antes de faturar!'
                });
                return;
            }
            
            // Validar se todas as parcelas têm forma de pagamento
            var parcelasInvalidas = parcelasFaturamento.filter(function(p) {
                return !p.forma_pgto || p.forma_pgto === '';
            });
            
            if (parcelasInvalidas.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Todas as parcelas devem ter uma forma de pagamento selecionada!'
                });
                return;
            }
            
            $(this).prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Processando...');
            
            // Obter o status do faturamento pendente
            var statusParaAtualizar = faturamentoPendente && faturamentoPendente.novoStatus ? faturamentoPendente.novoStatus : 'Faturado';
            
            // Primeiro, atualiza o status
            $.ajax({
                url: '<?php echo base_url(); ?>index.php/propostas/atualizarStatus',
                type: 'POST',
                data: {
                    idProposta: idProposta,
                    status: statusParaAtualizar,
                    <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.result) {
                        // Se o status foi atualizado, gerar lançamento usando parcelas
                        $.ajax({
                            url: '<?php echo base_url(); ?>index.php/propostas/gerarLancamentoParcelas',
                            type: 'POST',
                            data: {
                                idProposta: idProposta,
                                parcelas: JSON.stringify(parcelasFaturamento),
                                criar_lancamento: criarLancamento,
                                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
                            },
                            dataType: 'json',
                            success: function(resp) {
                                $('#modal-faturar').modal('hide');
                                if (resp.result) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Sucesso!',
                                        text: resp.message,
                                        timer: 2000
                                    }).then(function() {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Aviso', resp.message, 'warning').then(function() {
                                        location.reload();
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire('Erro', 'Erro ao gerar lançamento', 'error');
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Erro', response.message || 'Erro ao atualizar status', 'error');
                        $('#btn-confirmar-faturar').prop('disabled', false).html('<span class="button__icon"><i class="bx bx-check"></i></span><span class="button__text2">Confirmar Faturamento</span>');
                    }
                },
                error: function() {
                    Swal.fire('Erro', 'Erro ao comunicar com o servidor', 'error');
                    $('#btn-confirmar-faturar').prop('disabled', false).html('<span class="button__icon"><i class="bx bx-check"></i></span><span class="button__text2">Confirmar Faturamento</span>');
                }
            });
        });
        
        // Cancelar faturamento - reverter select
        $('#btn-cancelar-faturar').on('click', function() {
            if (faturamentoPendente) {
                faturamentoPendente.$select.val(faturamentoPendente.statusAnterior);
                faturamentoPendente.$select.prop('disabled', false);
                faturamentoPendente = null;
            }
        });
        
        // Também reverter ao fechar modal
        $('#modal-faturar').on('hidden', function() {
            if (faturamentoPendente) {
                faturamentoPendente.$select.val(faturamentoPendente.statusAnterior);
                faturamentoPendente.$select.prop('disabled', false);
                faturamentoPendente = null;
            }
        });
    });
</script>

