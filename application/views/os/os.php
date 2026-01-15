<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/table-custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>
<style>
  select {
    width: 70px;
  }
  /* Dar mais espaço para a coluna Cliente */
  .table thead th:nth-child(2),
  .table tbody td:nth-child(2) {
    min-width: 250px;
    max-width: 400px;
    width: 30%;
  }
  .table tbody td.cli1 {
    white-space: normal;
    word-wrap: break-word;
    overflow-wrap: break-word;
  }
  .table tbody td.cli1 a {
    display: inline-block;
    max-width: 100%;
    word-wrap: break-word;
  }
  /* Coluna Ações - manter ícones em linha */
  .table tbody td:last-child {
    white-space: nowrap;
  }
  .table tbody td:last-child a {
    display: inline-block;
    white-space: nowrap;
  }
  /* Linha clicável para acessar OS */
  .table tbody tr.os-row-clickable:hover {
    background-color: #f5f5f5;
    transition: background-color 0.2s ease;
  }
  .table tbody tr.os-row-clickable td:last-child {
    cursor: default;
  }
  .table tbody tr.os-row-clickable td:last-child a {
    cursor: pointer;
  }
  /* Ordenação de colunas */
  .table thead th.sortable {
    cursor: pointer;
    user-select: none;
    position: relative;
    padding-right: 25px;
  }
  .table thead th.sortable:hover {
    background-color: #e8e8e8;
  }
  .table thead th.sortable .sort-icon {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 12px;
    opacity: 0.5;
  }
  .table thead th.sortable.sort-asc .sort-icon::before {
    content: "▲";
    opacity: 1;
  }
  .table thead th.sortable.sort-desc .sort-icon::before {
    content: "▼";
    opacity: 1;
  }
</style>
<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
            <span class="icon">
                <i class="fas fa-diagnoses"></i>
            </span>
            <h5>Ordens de Serviço</h5>
        </div>
    <div class="span12" style="margin-left: 0">
        <form method="get" action="<?php echo base_url(); ?>index.php/os/gerenciar">
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aOs')) { ?>
                <div class="span3">
                    <a href="<?php echo base_url(); ?>index.php/os/adicionar" class="button btn btn-mini btn-success" style="max-width: 160px">
                        <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">Ordem de Serviço</span></a>
                </div>
            <?php
            } ?>

            <div class="span3">
                <input type="text" name="pesquisa" id="pesquisa" placeholder="Nome do cliente a pesquisar" class="span12" value="<?=set_value('pesquisa')?>">
            </div>
            <div class="span2">
                <select name="status" id="" class="span12">
                    <option value="">Selecione status</option>
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

    <div class="widget-box" style="margin-top: 8px">
        <div class="widget-content nopadding">
            <div class="table-responsive">
                <table class="table table-bordered ">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="id" data-type="number">N° <i class="sort-icon"></i></th>
                            <th class="sortable" data-sort="nome" data-type="text">Cliente <i class="sort-icon"></i></th>
                            <th class="sortable" data-sort="data" data-type="date">Data Inicial <i class="sort-icon"></i></th>
                            <th class="ph3">Venc. Garantia</th>
                            <th class="sortable" data-sort="valor" data-type="number">Valor Total <i class="sort-icon"></i></th>
                            <th class="sortable" data-sort="status" data-type="text">Status <i class="sort-icon"></i></th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$results) {
                            echo '<tr>
                            <td colspan="7">Nenhuma OS Cadastrada</td>
                            </tr>';
                        }

                        $this->load->model('os_model'); foreach ($results as $r) {
                                $dataInicial = date(('d/m/Y'), strtotime($r->dataInicial));
                                if ($r->dataFinal != null) {
                                    $dataFinal = date(('d/m/Y'), strtotime($r->dataFinal));
                                } else {
                                    $dataFinal = "";
                                }
                                if ($this->input->get('pesquisa') === null && is_array(json_decode($configuration['os_status_list']))) {
                                    if (in_array($r->status, json_decode($configuration['os_status_list'])) != true) {
                                        continue;
                                    }
                                }

                                switch ($r->status) {
                                    case 'Aberto':
                                        $cor = '#00cd00';
                                        break;
                                    case 'Em Andamento':
                                        $cor = '#436eee';
                                        break;
                                    case 'Orçamento':
                                        $cor = '#CDB380';
                                        break;
                                    case 'Negociação':
                                        $cor = '#AEB404';
                                        break;
                                    case 'Cancelado':
                                        $cor = '#CD0000';
                                        break;
                                    case 'Finalizado':
                                        $cor = '#256';
                                        break;
                                    case 'Faturado':
                                        $cor = '#B266FF';
                                        break;
                                    case 'Aguardando Peças':
                                        $cor = '#FF7F00';
                                        break;
                                    case 'Aprovado':
                                        $cor = '#808080';
                                        break;
                                    default:
                                        $cor = '#E0E4CC';
                                        break;
                                }
                                $vencGarantia = '';

                                if ($r->garantia && is_numeric($r->garantia)) {
                                    $vencGarantia = dateInterval($r->dataFinal, $r->garantia);
                                }
                                $corGarantia = '';
                                if (!empty($vencGarantia)) {
                                    $dataGarantia = explode('/', $vencGarantia);
                                    $dataGarantiaFormatada = $dataGarantia[2] . '-' . $dataGarantia[1] . '-' . $dataGarantia[0];
                                    if (strtotime($dataGarantiaFormatada) >= strtotime(date('d-m-Y'))) {
                                        $corGarantia = '#4d9c79';
                                    } else {
                                        $corGarantia = '#f24c6f';
                                    }
                                } elseif ($r->garantia == "0") {
                                    $vencGarantia = 'Sem Garantia';
                                    $corGarantia = '';
                                } else {
                                    $vencGarantia = '';
                                    $corGarantia = '';
                                }

                                echo '<tr class="os-row-clickable" data-os-id="' . $r->idOs . '" style="cursor: pointer;" data-sort-id="' . $r->idOs . '" data-sort-nome="' . htmlspecialchars(strtolower($r->nomeCliente)) . '" data-sort-data="' . strtotime($r->dataInicial) . '" data-sort-valor="' . ($r->totalProdutos + $r->totalServicos) . '" data-sort-status="' . htmlspecialchars(strtolower($r->status)) . '">';
                                echo '<td>' . $r->idOs . '</td>';
                                echo '<td class="cli1">' . $r->nomeCliente . '</td>';
                                echo '<td>' . $dataInicial . '</td>';
                                echo '<td class="ph3"><span class="badge" style="background-color: ' . $corGarantia . '; border-color: ' . $corGarantia . '">' . $vencGarantia . '</span> </td>';
                                echo '<td>R$ ' . number_format($r->totalProdutos + $r->totalServicos, 2, ',', '.') . '</td>';
                                echo '<td>';
                                echo '<select class="status-select" data-os-id="' . $r->idOs . '" data-status-atual="' . htmlspecialchars($r->status) . '" style="background-color: ' . $cor . '; border-color: ' . $cor . '; color: white; padding: 4px 8px; border-radius: 4px; border: 1px solid ' . $cor . '; font-size: 12px; font-weight: bold; cursor: pointer; min-width: 120px;">';
                                echo '<option value="Aberto" ' . ($r->status == 'Aberto' ? 'selected' : '') . ' style="background: #00cd00;">Aberto</option>';
                                echo '<option value="Orçamento" ' . ($r->status == 'Orçamento' ? 'selected' : '') . ' style="background: #CDB380;">Orçamento</option>';
                                echo '<option value="Negociação" ' . ($r->status == 'Negociação' ? 'selected' : '') . ' style="background: #AEB404;">Negociação</option>';
                                echo '<option value="Aprovado" ' . ($r->status == 'Aprovado' ? 'selected' : '') . ' style="background: #808080;">Aprovado</option>';
                                echo '<option value="Em Andamento" ' . ($r->status == 'Em Andamento' ? 'selected' : '') . ' style="background: #436eee;">Em Andamento</option>';
                                echo '<option value="Aguardando Peças" ' . ($r->status == 'Aguardando Peças' ? 'selected' : '') . ' style="background: #FF7F00;">Aguardando Peças</option>';
                                echo '<option value="Finalizado" ' . ($r->status == 'Finalizado' ? 'selected' : '') . ' style="background: #256;">Finalizado</option>';
                                echo '<option value="Faturado" ' . ($r->status == 'Faturado' ? 'selected' : '') . ' style="background: #B266FF;">Faturado</option>';
                                echo '<option value="Cancelado" ' . ($r->status == 'Cancelado' ? 'selected' : '') . ' style="background: #CD0000;">Cancelado</option>';
                                echo '</select>';
                                echo '</td>';
                                echo '<td>';

                                $editavel = $this->os_model->isEditable($r->idOs);

                                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
                                    echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/visualizar/' . $r->idOs . '" class="btn-nwe" title="Ver mais detalhes"><i class="bx bx-show"></i></a>';
                                    echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/imprimir/' . $r->idOs . '" target="_blank" class="btn-nwe6" title="Imprimir A4"><i class="bx bx-printer bx-xs"></i></a>';
                                    echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/imprimirProposta/' . $r->idOs . '" target="_blank" class="btn-nwe6" title="Proposta Comercial"><i class="bx bx-file-blank bx-xs"></i></a>';
                                }
                                if ($editavel) {
                                    echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/editar/' . $r->idOs . '" class="btn-nwe3" title="Editar OS"><i class="bx bx-edit"></i></a>';
                                }
                                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aOs')) {
                                    echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/clonar/' . $r->idOs . '" class="btn-nwe" title="Clonar OS"><i class="bx bx-copy"></i></a>';
                                }
                                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dOs') && $editavel) {
                                    echo '<a href="#modal-excluir" role="button" data-toggle="modal" os="' . $r->idOs . '" class="btn-nwe4" title="Excluir OS"><i class="bx bx-trash-alt"></i></a>  ';
                                }
                                echo '</td>';
                                echo '</tr>';
                            } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php echo $this->pagination->create_links(); ?>

    <!-- Modal -->
    <div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <form action="<?php echo base_url() ?>index.php/os/excluir" method="post">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h5 id="myModalLabel">Excluir OS</h5>
            </div>
            <div class="modal-body">
                <input type="hidden" id="idOs" name="id" value="" />
                <h5 style="text-align: center">Deseja realmente excluir esta OS?</h5>
            </div>
            <div class="modal-footer" style="display:flex;justify-content: center">
                <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true">
                    <span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
                <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-trash'></i></span> <span class="button__text2">Excluir</span></button>
            </div>
        </form>
    </div>

    <!-- Modal Faturamento -->
    <div id="modal-faturar" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="modalFaturarLabel" aria-hidden="true" style="width: 90%; max-width: 1200px; margin-left: -45%;">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="modalFaturarLabel"><i class="bx bx-dollar-circle"></i> Faturar OS #<span id="faturar-os-id"></span></h5>
        </div>
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
            <input type="hidden" id="faturar-idOs" value="" />
            
            <div class="span12" style="margin-left: 0; background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <strong>Valor Total da OS:</strong> 
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
</div>

<script type="text/javascript">
    $(document).ready(function() {
        console.log('jQuery carregado, versão:', $.fn.jquery);
        console.log('Selects de status:', $('.status-select').length);
        console.log('Modal faturar existe?', $('#modal-faturar').length > 0);
        
        // Carregar contas bancárias
        $.ajax({
            url: '<?php echo base_url(); ?>index.php/contas/getAll',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                contasBancarias = data || [];
                console.log('Contas bancárias carregadas:', contasBancarias.length);
            },
            error: function() {
                console.error('Erro ao carregar contas bancárias');
            }
        });
        
        // Teste: verificar se o evento está sendo capturado
        $('.status-select').each(function() {
            console.log('Select encontrado:', $(this).data('os-id'), $(this).data('status-atual'));
        });
        
        // Funcionalidade de ordenação de colunas
        var currentSort = {
            column: null,
            direction: 'asc'
        };
        
        $(document).on('click', '.table thead th.sortable', function(e) {
            e.stopPropagation(); // Evitar que dispare o evento da linha
            e.preventDefault();
            
            var $th = $(this);
            var sortType = $th.attr('data-type') || $th.data('type');
            var sortAttr = $th.attr('data-sort') || $th.data('sort');
            
            // Remover classes de ordenação de todas as colunas
            $('.table thead th.sortable').removeClass('sort-asc sort-desc');
            
            // Determinar direção da ordenação
            if (currentSort.column === sortAttr && currentSort.direction === 'asc') {
                currentSort.direction = 'desc';
                $th.addClass('sort-desc');
            } else {
                currentSort.direction = 'asc';
                $th.addClass('sort-asc');
            }
            currentSort.column = sortAttr;
            
            // Ordenar as linhas
            var $tbody = $('.table tbody');
            var $rows = $tbody.find('tr.os-row-clickable').get();
            
            $rows.sort(function(a, b) {
                var $a = $(a);
                var $b = $(b);
                var valA, valB;
                
                // Obter valores usando attr() diretamente (mais confiável que data())
                var dataAttrName = 'data-sort-' + sortAttr;
                var rawA = $a.attr(dataAttrName);
                var rawB = $b.attr(dataAttrName);
                
                switch(sortType) {
                    case 'number':
                        valA = parseFloat(rawA) || 0;
                        valB = parseFloat(rawB) || 0;
                        break;
                    case 'date':
                        valA = parseInt(rawA) || 0;
                        valB = parseInt(rawB) || 0;
                        break;
                    case 'text':
                    default:
                        valA = (rawA || '').toString().toLowerCase();
                        valB = (rawB || '').toString().toLowerCase();
                        break;
                }
                
                // Comparar valores
                var result;
                if (sortType === 'text') {
                    if (currentSort.direction === 'asc') {
                        result = valA.localeCompare(valB);
                    } else {
                        result = valB.localeCompare(valA);
                    }
                } else {
                    if (currentSort.direction === 'asc') {
                        result = valA - valB;
                    } else {
                        result = valB - valA;
                    }
                }
                
                return result;
            });
            
            // Reordenar as linhas no DOM
            $tbody.empty();
            $.each($rows, function(index, row) {
                $tbody.append(row);
            });
        });
        
        // Tornar linha clicável para acessar OS (exceto quando clicar em links, botões ou selects)
        $(document).on('click', '.os-row-clickable', function(e) {
            // Não redirecionar se clicar em links, botões ou selects
            if ($(e.target).is('a, button, select, input') || $(e.target).closest('a, button, select, input').length > 0) {
                return;
            }
            var osId = $(this).data('os-id');
            if (osId) {
                window.location.href = '<?php echo base_url(); ?>index.php/os/visualizar/' + osId;
            }
        });
        
        $(document).on('click', 'a', function(event) {
            var os = $(this).attr('os');
            $('#idOs').val(os);
        });
        $(document).on('click', '#excluir-notificacao', function(event) {
            event.preventDefault();
            $.ajax({
                    url: '<?php echo site_url() ?>/os/excluir_notificacao',
                    type: 'GET',
                    dataType: 'json',
                })
                .done(function(data) {
                    if (data.result == true) {
                        Swal.fire({
                            type: "success",
                            title: "Sucesso",
                            text: "Notificação excluída com sucesso."
                        });
                        location.reload();
                    } else {
                        Swal.fire({
                            type: "success",
                            title: "Sucesso",
                            text: "Ocorreu um problema ao tentar exlcuir notificação."
                        });
                    }
                });
        });
        
        // Atualizar status da OS via dropdown
        $(document).on('change', '.status-select', function(e) {
            e.stopPropagation();
            
            var $select = $(this);
            var idOs = $select.data('os-id');
            var statusAtual = $select.data('status-atual');
            var novoStatus = $select.val();
            
            // Limpar espaços e normalizar
            novoStatus = novoStatus ? novoStatus.trim() : '';
            statusAtual = statusAtual ? statusAtual.trim() : '';
            
            console.log('Evento change disparado!', {
                idOs: idOs, 
                statusAtual: statusAtual, 
                novoStatus: novoStatus,
                'novoStatus === "Faturado"': novoStatus === 'Faturado',
                'novoStatus.length': novoStatus.length,
                'statusAtual.length': statusAtual.length
            });
            
            // Se não mudou, não fazer nada
            if (novoStatus === statusAtual) {
                console.log('Status não mudou');
                $select.val(statusAtual); // Forçar valor original
                return false;
            }
            
            // Se mudar para Faturado ou Finalizado, abrir modal de faturamento
            console.log('Verificando se status é Faturado ou Finalizado...', novoStatus, typeof novoStatus);
            
            // Verificar se é Faturado ou Finalizado
            var isFaturado = novoStatus === 'Faturado' || 
                            novoStatus.trim() === 'Faturado' || 
                            (novoStatus && novoStatus.indexOf('Faturado') !== -1);
            
            var isFinalizado = novoStatus === 'Finalizado' || 
                            novoStatus.trim() === 'Finalizado' || 
                            (novoStatus && novoStatus.indexOf('Finalizado') !== -1);
            
            console.log('isFaturado:', isFaturado, 'isFinalizado:', isFinalizado, 'novoStatus:', JSON.stringify(novoStatus));
            
            if (isFaturado || isFinalizado) {
                console.log('✓ Status é Faturado ou Finalizado! Abrindo modal...');
                $select.prop('disabled', true);
                
                // Verificar se função existe
                if (typeof abrirModalFaturar === 'undefined') {
                    console.error('ERRO: Função abrirModalFaturar não existe!');
                    Swal.fire('Erro', 'Função de abrir modal não encontrada. Recarregue a página.', 'error');
                    $select.val(statusAtual);
                    $select.prop('disabled', false);
                    return false;
                }
                
                // Buscar dados da OS
                console.log('Fazendo requisição AJAX para:', '<?php echo base_url(); ?>index.php/os/getDadosOsJson/' + idOs);
                $.ajax({
                    url: '<?php echo base_url(); ?>index.php/os/getDadosOsJson/' + idOs,
                    type: 'GET',
                    dataType: 'json',
                    timeout: 10000,
                    success: function(data) {
                        console.log('✓ Dados da OS recebidos:', data);
                        if (data && data.result) {
                            // Verificar se já tem lançamento
                            if (data.temLancamento) {
                                console.log('OS já tem lançamento, mostrando aviso...');
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Atenção',
                                    text: 'Esta OS já possui um lançamento financeiro vinculado.',
                                    showCancelButton: true,
                                    confirmButtonText: 'Continuar mesmo assim',
                                    cancelButtonText: 'Cancelar'
                                }).then(function(result) {
                                    if (result.isConfirmed) {
                                        console.log('✓ Abrindo modal de faturamento (com lançamento existente)');
                                        abrirModalFaturar(idOs, data.valorTotal, $select, statusAtual, novoStatus);
                                    } else {
                                        console.log('Usuário cancelou');
                                        $select.val(statusAtual);
                                        $select.prop('disabled', false);
                                    }
                                });
                            } else {
                                console.log('✓ Abrindo modal de faturamento (sem lançamento)');
                                abrirModalFaturar(idOs, data.valorTotal, $select, statusAtual, novoStatus);
                            }
                        } else {
                            console.error('✗ Erro na resposta:', data);
                            Swal.fire('Erro', data && data.message ? data.message : 'Erro ao buscar dados da OS', 'error');
                            $select.val(statusAtual);
                            $select.prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('✗ Erro AJAX ao buscar dados da OS:', {
                            xhr: xhr,
                            status: status,
                            error: error,
                            responseText: xhr.responseText
                        });
                        Swal.fire('Erro', 'Erro ao buscar dados da OS: ' + error, 'error');
                        $select.val(statusAtual);
                        $select.prop('disabled', false);
                    }
                });
                return false; // Impedir que continue para outros status
            }
            
            console.log('Status não é Faturado, continuando com fluxo normal...');
            
            // Para outros status, confirmar e atualizar normalmente
            if (confirm('Deseja alterar o status de "' + statusAtual + '" para "' + novoStatus + '"?')) {
                console.log('Usuário confirmou mudança');
                
                // Desabilitar select
                $select.prop('disabled', true);
                
                $.ajax({
                    url: '<?php echo base_url(); ?>index.php/os/atualizarStatus',
                    type: 'POST',
                    data: {
                        idOs: idOs,
                        status: novoStatus,
                        <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('Resposta:', response);
                        if (response.result) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso!',
                                text: 'Status atualizado com sucesso!',
                                timer: 1500
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Erro', response.message || 'Erro ao atualizar status', 'error');
                            $select.val(statusAtual);
                            $select.prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erro AJAX:', error);
                        Swal.fire('Erro', 'Erro ao comunicar com o servidor: ' + error, 'error');
                        $select.val(statusAtual);
                        $select.prop('disabled', false);
                    }
                });
            } else {
                console.log('Usuário cancelou mudança');
                $select.val(statusAtual);
            }
        });
        
        // Variável para armazenar parcelas do faturamento
        var parcelasFaturamento = [];
        var dataBaseVencimento = '<?php echo date('d/m/Y'); ?>';
        var contasBancarias = [];
        
        // Função para abrir modal de faturamento
        function abrirModalFaturar(idOs, valorTotal, $select, statusAnterior, novoStatus) {
            console.log('abrirModalFaturar chamado', {idOs: idOs, valorTotal: valorTotal, novoStatus: novoStatus});
            
            // Salvar referência para reverter se cancelar
            faturamentoPendente = {
                $select: $select,
                statusAnterior: statusAnterior,
                novoStatus: novoStatus
            };
            
            // Preencher modal básico
            $('#faturar-idOs').val(idOs);
            $('#faturar-os-id').text(idOs);
            $('#faturar-valor-total').text('R$ ' + valorTotal.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
            $('#criar-lancamento').prop('checked', true);
            
            // Buscar parcelas da OS
            $.ajax({
                url: '<?php echo base_url(); ?>index.php/os/getDadosOsJson/' + idOs,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log('Parcelas recebidas:', data);
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
                                    detalhes: p.detalhes || '',
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
                        
                        console.log('Parcelas processadas:', parcelasFaturamento);
                        atualizarTabelaFaturamento();
                        $('#opcoes-faturamento').show();
                        
                        // Abrir modal
                        console.log('Abrindo modal...');
                        var $modal = $('#modal-faturar');
                        console.log('Modal encontrado?', $modal.length > 0);
                        if ($modal.length > 0) {
                            // Remover classe 'hide' se existir
                            $modal.removeClass('hide');
                            // Abrir modal
                            $modal.modal('show');
                            console.log('Modal.show() chamado');
                            
                            // Verificar se modal abriu após um tempo
                            setTimeout(function() {
                                if (!$modal.is(':visible')) {
                                    console.log('Modal não visível, tentando forçar...');
                                    $modal.removeClass('hide fade').addClass('in');
                                    $modal.css({
                                        'display': 'block',
                                        'z-index': 1050
                                    });
                                    // Adicionar backdrop
                                    if ($('.modal-backdrop').length === 0) {
                                        $('body').append('<div class="modal-backdrop fade in"></div>');
                                    }
                                } else {
                                    console.log('Modal visível com sucesso!');
                                }
                            }, 200);
                        } else {
                            console.error('Modal não encontrado!');
                            Swal.fire('Erro', 'Modal de faturamento não encontrado', 'error');
                        }
                    } else {
                        console.error('Erro na resposta:', data);
                        Swal.fire('Erro', data.message || 'Erro ao carregar dados da OS', 'error');
                        if ($select) {
                            $select.val(statusAnterior);
                            $select.prop('disabled', false);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro AJAX ao buscar parcelas:', xhr, status, error);
                    Swal.fire('Erro', 'Erro ao carregar dados da OS: ' + error, 'error');
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
                // Recalcular data
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
        
        $(".datepicker").datepicker({
            dateFormat: 'dd/mm/yy'
        });
        
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
            var idOs = $('#faturar-idOs').val();
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
                url: '<?php echo base_url(); ?>index.php/os/atualizarStatus',
                type: 'POST',
                data: {
                    idOs: idOs,
                    status: statusParaAtualizar,
                    <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.result) {
                        // Se o status foi atualizado, gerar lançamento usando parcelas
                        $.ajax({
                            url: '<?php echo base_url(); ?>index.php/os/gerarLancamentoParcelas',
                            type: 'POST',
                            data: {
                                idOs: idOs,
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
