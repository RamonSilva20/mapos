<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/table-custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
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
</div>

<script type="text/javascript">
    $(document).ready(function() {
        console.log('jQuery carregado, versão:', $.fn.jquery);
        console.log('Selects de status:', $('.status-select').length);
        
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
        $('body').on('change', '.status-select', function(e) {
            e.stopPropagation();
            
            var $select = $(this);
            var idOs = $select.data('os-id');
            var statusAtual = $select.data('status-atual');
            var novoStatus = $select.val();
            
            console.log('Evento change disparado!', {idOs: idOs, statusAtual: statusAtual, novoStatus: novoStatus});
            
            // Se não mudou, não fazer nada
            if (novoStatus === statusAtual) {
                console.log('Status não mudou');
                return;
            }
            
            // Confirmar mudança
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
                            alert('Status atualizado com sucesso!');
                            location.reload();
                        } else {
                            alert('Erro: ' + (response.message || 'Erro ao atualizar status'));
                            $select.val(statusAtual);
                            $select.prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erro AJAX:', error);
                        alert('Erro ao comunicar com o servidor: ' + error);
                        $select.val(statusAtual);
                        $select.prop('disabled', false);
                    }
                });
            } else {
                console.log('Usuário cancelou mudança');
                $select.val(statusAtual);
            }
        });
        
        $(".datepicker").datepicker({
            dateFormat: 'dd/mm/yy'
        });
    });
</script>
