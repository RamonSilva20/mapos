<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/table-custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>

<div class="span12" style="margin-left: 0">
    <form method="get" action="<?php echo base_url(); ?>index.php/os/gerenciar">
        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aOs')) { ?>
            <div class="span3">
                <a href="<?php echo base_url(); ?>index.php/os/adicionar" class="btn btn-success span12"><i class="fas fa-plus"></i> Adicionar OS</a>
            </div>
        <?php
        } ?>

        <div class="span3">
            <input type="text" name="pesquisa" id="cliente" placeholder="Nome do cliente a pesquisar" class="span12" value="">
        </div>
        <div class="span2">
            <select name="status" id="" class="span12">
            	<option value="">Todos os Status</option>
				<option value="Orçamento">Orçamento</option>
				<option value="Orçamento Concluido">Orçamento Concluido</option>
				<option value="Orçamento Aprovado">Orçamento Aprovado</option>
				<option value="Em Andamento">Em Andamento</option>
                <option value="Aguardando Peças">Aguardando Peças</option>
                <option value="Serviço Concluido">Serviço Concluido</option>
				<option value="Sem Reparo">Sem Reparo</option>
				<option value="Não Autorizado">Não Autorizado</option>
                <option value="Contato sem Sucesso">Contato sem Sucesso</option>
                <option value="Cancelado">Cancelado</option>
				<option value="Pronto-Despachar">Pronto-Despachar</option>
                <option value="Enviado">Enviado</option>
                <option value="Aguardando Envio">Aguardando Envio</option>
                <option value="Aguardando Entrega Correio">Aguardando Entrega Correio</option>
                <option value="Entregue - A Receber">Entregue - A Receber</option>
				<option value="Garantia">Garantia</option>
                <option value="Abandonado">Abandonado</option>
				<option value="Comprado pela Loja">Comprado pela Loja</option>
                <option value="Entregue - Faturado">Entregue - Faturado</option>
                
            </select>

        </div>

        <div class="span3">
            <input type="text" name="data" autocomplete="off" id="data" placeholder="Data de Entrada" class="span6 datepicker" value="">
        </div>
        <div class="span1">
            <button class="span12 btn"> <i class="fas fa-search"></i> </button>
        </div>
    </form>
</div>

<div class="widget-box">
    <div class="widget-title">
        <span class="icon">
            <i class="fas fa-diagnoses"></i>
        </span>
        <h5>Ordens de Serviço</h5>
    </div>
    <div class="widget_content nopadding">
        <table id="tabela" width="100%" class="table_p">
                <thead>
                    <tr>
                        <th>N° OS</th>
                        <th>Cliente</th>
                        <th>Responsável</th>
                        <th>Data de Entrada</th>
                        <th>Garantia Até</th>
                        <th>Valor Total</th>
                        <!--
                        <th>Faturado</th>
                        -->
                     	<th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        
                        if(!$results){
                            echo '<tr>
                                    <td colspan="10">Nenhuma OS Cadastrada</td>
                                  </tr>';
                        }
                        foreach ($results as $r) {
                            $NomeClienteShort = mb_strimwidth(strip_tags($r->nomeCliente), 0, 25, "...");
							$dataInicial = date(('d/m/Y'), strtotime($r->dataInicial));
                            if ($r->dataFinal != null) {
                                $dataFinal = date(('d/m/Y'), strtotime($r->dataFinal));
                            } else {
                                $dataFinal = "";
                            }
                            switch ($r->status) {
				case 'Orçamento':
                    $cor = '#CCCC00';
                    break;
				case 'Orçamento Concluido':
                    $cor = '#CC9966';
                    break;
				case 'Orçamento Aprovado':
                    $cor = '#339999';
					break;
				case 'Em Andamento':
                    $cor = '#9933FF';
                    break;
				case 'Aguardando Peças':
                    $cor = '#FF6600';
                 	break;
				case 'Serviço Concluido':
                    $cor = '#0066FF';
                    break;
				case 'Sem Reparo':
                    $cor = '#999999';
                    break;
				case 'Não Autorizado':
                    $cor = '#990000';
                    break;
				case 'Contato sem Sucesso':
                    $cor = '#660099';
                    break;
				case 'Cancelado':
                    $cor = '#990000';
                    break;
				case 'Pronto-Despachar':
                    $cor = '#33CCCC';
                    break;
				case 'Enviado':
                    $cor = '#99CC33';
                    break;
				case 'Aguardando Envio':
                    $cor = '#CC66CC';
                    break;
				case 'Aguardando Entrega Correio':
                    $cor = '#996699';
                    break;
				case 'Entregue - A Receber':
                    $cor = '#FF0000';
                    break;
				case 'Garantia':
                    $cor = '#FF66CC';
                    break;
				case 'Abandonado':
                    $cor = '#000000';
                    break;
				case 'Comprado pela Loja':
                    $cor = '#666666';
                    break;
				case 'Entregue - Faturado':
                    $cor = '#006633';
                    break;
                            }
                    $vencGarantia = '';

                        if ($r->garantia && is_numeric($r->garantia)) {
                            $vencGarantia = dateInterval($r->dataFinal, $r->garantia);
                        }
                            echo '<tr>';
                            echo '<td><div align="center">' . $r->idOs . '</td>';
							echo '<td>' . $NomeClienteShort . '</td>';
                            echo '<td><div align="center">' . $r->nome . '</td>';
                            echo '<td><div align="center">' . $dataInicial . '</td>';
							echo '<td><div align="center">' . $r->garantia . '</td>';
							echo '<td><div align="center">R$: ' . number_format($r->totalProdutos + $r->totalServicos, 2, ',', '.') . '</td>';
							/*
							echo '<td><div align="center">R$: ' . number_format($r->valorTotal, 2, ',', '.') . '</div></td>';
							*/
					echo '<td><div align="center"><span class="badge" style="background-color: ' . $cor . '; border-color: ' . $cor . '">' . $r->status . '</span></div></td>';
                            echo '<td><div align="center">';
						if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
							echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/visualizar/' . $r->idOs . '" class="btn tip-top" title="Visualizar mais detalhes"><i class="fas fa-eye"></i></a>';
								}
						if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
                                echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/editar/' . $r->idOs . '" class="btn btn-info tip-top" title="Editar OS"><i class="fas fa-edit"></i></a>';
								}
								if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
                                $zapnumber = preg_replace("/[^0-9]/", "", $r->celular_cliente);
								$total_os = number_format($r->totalProdutos + $r->totalServicos, 2, ',', '.');
								echo '<a class="btn btn-success tip-top" style="margin-right: 1%" title="Enviar Por WhatsApp" id="enviarWhatsApp" target="_blank" href="https://web.whatsapp.com/send?phone=55' . $zapnumber . '&text=Prezado(a)%20*' . $r->nomeCliente . '*.%0d%0a%0d%0aSua%20*O.S%20' . $r->idOs . '*%20referente%20ao%20equipamento%20*' . strip_tags($r->descricaoProduto) . '*%20foi%20atualizada%20para%20*' . $r->status . '*.%0d%0a%0d%0a' . strip_tags($r->defeito) . '%0d%0a%0d%0a' . strip_tags($r->laudoTecnico) . '%0d%0a%0d%0a' . strip_tags($r->observacoes) . '%0d%0a%0d%0aValor%20Total%20R$&#58%20*'. $total_os . '*%0d%0a%0d%0a' . $configuration['whats_app1'] .'%0d%0a%0d%0aAtenciosamente,%20*' . $configuration['whats_app2'] . '*%20-%20*' . $configuration['whats_app3'] .'*%0d%0a%0d%0aAcesse%20a%20área%20do%20cliente%20pelo%20link%0d%0a'. $configuration['whats_app4'] .'%0d%0aE%20utilize%20esta%20senha%20para%20fazer%20Log-In%20*' . strip_tags($r->senha) . '*%0d%0aVocê%20poderá%20edita-la%20no%20menu%20*Minha%20Conta*"><i class="fab fa-whatsapp" style="font-size:16px;"></i></a>';
                            }
							if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
                                echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/imprimir/' . $r->idOs . '" target="_blank" class="btn btn-inverse tip-top" title="Imprimir Normal A4"><i class="fas fa-print"></i></a>';
                            }
							if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
                                echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/imprimirTermica2/' . $r->idOs . '" target="_blank" class="btn btn-inverse tip-top" title="Imprimir Termica 2"><i class="fas fa-print"></i></a>';
                            }
                            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dOs')) {
                                echo '<a href="#modal-excluir" role="button" data-toggle="modal" os="' . $r->idOs . '" class="btn btn-danger tip-top" title="Excluir OS"><i class="fas fa-trash-alt"></i></a>  ';
								}
                            echo  '</td>';
                            echo '</tr>';
                        } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php echo $this->pagination->create_links(); ?>

<!-- Modal -->
<div id="modal-excluir" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/os/excluir" method="post">
        <div class="modal_header_anexos">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Excluir OS</h3>
        </div>
        <div class="modal-body">
            <input type="hidden" id="idOs" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir esta OS?</h5>
        </div>
        <div class="modal-footer">
            <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button class="btn btn-danger">Excluir</button>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', 'a', function(event) {
            var os = $(this).attr('os');
            $('#idOs').val(os);
        });
		$("#cliente").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteClienteOs",
            minLength: 2,
            select: function(event, ui) {
                $("#clienteHide").val(ui.item.id);
            }
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
