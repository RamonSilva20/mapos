<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/table-custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>

<div class="new122">
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
                <input type="text" name="pesquisa" id="pesquisa" placeholder="Nome do cliente a pesquisar" class="span12" value="">
            </div>
            <div class="span2">
                <select name="status" id="" class="span12">
                    <option value="">Selecione status</option>
                    <option value="Aberto">Aberto</option>
                    <option value="Faturado">Faturado</option>
                    <option value="Negociação">Negociação</option>
                    <option value="Em Andamento">Em Andamento</option>
                    <option value="Orçamento">Orçamento</option>
                    <option value="Finalizado">Finalizado</option>
                    <option value="Cancelado">Cancelado</option>
                    <option value="Aguardando Peças">Aguardando Peças</option>
                    <option value="Aprovado">Aprovado</option>
                </select>

            </div>

            <div class="span3">
                <input type="text" name="data" autocomplete="off" id="data" placeholder="Data Inicial" class="span6 datepicker" value="">
                <input type="text" name="data2" autocomplete="off" id="data2" placeholder="Data Final" class="span6 datepicker" value="">
            </div>
            <div class="span1">
                <button class="button btn btn-mini btn-warning" style="min-width: 30px">
                    <span class="button__icon"><i class='bx bx-search-alt'></i></span></button>
            </div>
        </form>
    </div>

    <div class="widget-box" style="margin-top: 8px">
        <div class="widget-title" style="margin: -20px 0 0">
            <span class="icon">
                <i class="fas fa-diagnoses"></i>
            </span>
            <h5>Ordens de Serviço</h5>
        </div>
        <div class="widget-content nopadding">
            <div class="table-responsive">
                <table class="table table-bordered ">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Cliente</th>
                            <th class="ph1">Responsável</th>
                            <th>Data Inicial</th>
                            <th class="ph2">Data Final</th>
                            <th class="ph3">Venc. Garantia</th>
                            <th>Valor Total</th>
                            <th>Valor com Desconto</th>
                            <th class="ph4">V.T (Faturado)</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        if (!$results) {
                            echo '<tr>
                                    <td colspan="10">Nenhuma OS Cadastrada</td>
                                  </tr>';
                        }
                        $this->load->model('os_model');
                        foreach ($results as $r) {
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

                            echo '<tr>';
                            echo '<td>' . $r->idOs . '</td>';
                            echo '<td class="cli1"><a href="' . base_url() . 'index.php/clientes/visualizar/' . $r->idClientes . '" style="margin-right: 1%">' . $r->nomeCliente . '</a></td>';
                            echo '<td class="ph1">' . $r->nome . '</td>';
                            echo '<td>' . $dataInicial . '</td>';
                            echo '<td class="ph2">' . $dataFinal . '</td>';
                            echo '<td class="ph3"><span class="badge" style="background-color: ' . $corGarantia . '; border-color: ' . $corGarantia . '">' . $vencGarantia . '</span> </td>';
                            echo '<td>R$ ' . number_format($r->totalProdutos + $r->totalServicos, 2, ',', '.') . '</td>';
                            echo '<td>R$ ' . number_format(floatval($r->valor_desconto), 2, ',', '.') . '</td>';
                            echo '<td class="ph4">R$ R$ ' . number_format($r->valor_desconto ? : $r->valorTotal, 2, ',', '.') . '</td>';
                            echo '<td><span class="badge" style="background-color: ' . $cor . '; border-color: ' . $cor . '">' . $r->status . '</span> </td>';
                            echo '<td>';

                            $editavel = $this->os_model->isEditable($r->idOs);

                            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
                                echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/visualizar/' . $r->idOs . '" class="btn-nwe" title="Ver mais detalhes"><i class="bx bx-show"></i></a>';
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
        $(".datepicker").datepicker({
            dateFormat: 'dd/mm/yy'
        });
    });
</script>
