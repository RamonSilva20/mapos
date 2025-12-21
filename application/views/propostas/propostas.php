<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/table-custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<script src="<?php echo base_url() ?>assets/js/jquery.mask.min.js"></script>

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
                    <option value="Rascunho" <?=$this->input->get('status') == 'Rascunho' ? 'selected' : ''?>>Rascunho</option>
                    <option value="Enviada" <?=$this->input->get('status') == 'Enviada' ? 'selected' : ''?>>Enviada</option>
                    <option value="Aprovada" <?=$this->input->get('status') == 'Aprovada' ? 'selected' : ''?>>Aprovada</option>
                    <option value="Recusada" <?=$this->input->get('status') == 'Recusada' ? 'selected' : ''?>>Recusada</option>
                    <option value="Convertida" <?=$this->input->get('status') == 'Convertida' ? 'selected' : ''?>>Convertida</option>
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
                            <th>N°</th>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Validade</th>
                            <th>Valor Total</th>
                            <th>Status</th>
                            <th>Vendedor</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$results) {
                            echo '<tr>
                            <td colspan="8">Nenhuma proposta cadastrada</td>
                            </tr>';
                        } else {
                            foreach ($results as $r) {
                                $dataProposta = date('d/m/Y', strtotime($r->data_proposta));
                                $dataValidade = $r->data_validade ? date('d/m/Y', strtotime($r->data_validade)) : '-';
                                $valorTotal = number_format($r->valor_total, 2, ',', '.');
                                
                                // Status badge
                                $statusClass = 'default';
                                switch($r->status) {
                                    case 'Rascunho': $statusClass = 'secondary'; break;
                                    case 'Enviada': $statusClass = 'info'; break;
                                    case 'Aprovada': $statusClass = 'success'; break;
                                    case 'Recusada': $statusClass = 'danger'; break;
                                    case 'Convertida': $statusClass = 'primary'; break;
                                }
                                ?>
                                <tr>
                                    <td><?php echo $r->numero_proposta ?: '#' . $r->idProposta; ?></td>
                                    <td><?php echo $r->nomeCliente; ?></td>
                                    <td><?php echo $dataProposta; ?></td>
                                    <td><?php echo $dataValidade; ?></td>
                                    <td><strong>R$ <?php echo $valorTotal; ?></strong></td>
                                    <td><span class="badge badge-<?php echo $statusClass; ?>"><?php echo $r->status; ?></span></td>
                                    <td><?php echo $r->nome; ?></td>
                                    <td>
                                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vPropostas')) { ?>
                                            <a href="<?php echo base_url(); ?>index.php/propostas/visualizar/<?php echo $r->idProposta; ?>" target="_blank" class="btn-nwe4" title="Visualizar/Imprimir">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        <?php } ?>
                                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'ePropostas')) { ?>
                                            <a href="<?php echo base_url(); ?>index.php/propostas/editar/<?php echo $r->idProposta; ?>" class="btn-nwe4" title="Editar">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                        <?php } ?>
                                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dPropostas')) { ?>
                                            <a href="#modalExcluir" role="button" data-toggle="modal" data-nome="<?php echo $r->numero_proposta ?: '#' . $r->idProposta; ?>" data-id="<?php echo $r->idProposta; ?>" class="btn-nwe4" title="Excluir">
                                                <i class="bx bx-trash-alt"></i>
                                            </a>
                                        <?php } ?>
                                    </td>
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

        $('#modalExcluir').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var nome = button.data('nome');
            var modal = $(this);
            modal.find('#id').val(id);
            modal.find('#nome').text(nome);
            console.log('Modal aberto - ID:', id, 'Nome:', nome);
        });
        
        // Submeter formulário de exclusão
        $('#modalExcluir form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var id = form.find('#id').val();
            
            if (!id || id === '') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'ID da proposta não encontrado.'
                });
                return;
            }
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: { id: id },
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
                error: function(xhr) {
                    $('#modalExcluir').modal('hide');
                    var message = 'Erro ao excluir proposta.';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            message = response.message;
                        }
                    } catch(e) {
                        if (xhr.responseText) {
                            message = xhr.responseText;
                        }
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: message
                    });
                }
            });
        });
    });
</script>

