<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aCliente')) { ?>
    <a href="<?php echo base_url(); ?>index.php/clientes/adicionar" class="btn btn-success"><i class="fas fa-plus"></i> Adicionar Cliente</a>
<?php } ?>

<div class="widget-box">
	<div class="widget-title">
    <span class="icon"><i class="fas fa-user"></i></span>
    <h5>Clientes</h5>
    </div>
    <div class="widget-content nopadding">
    <!--
    <div class="widget_box_Painel2">
    -->
        <table id="tabela" width="100%" class="table_p">
            <thead>
                <tr>
                    <th>Cod.</th>
                    <th>Nome</th>
                    <th>CPF/CNPJ</th>
                    <th>Senha</th>
                    <th>Telefone</th>
                    <th>Email</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    
                    if(!$results){
                        echo '<tr>
                                <td colspan="5">Nenhum Cliente Cadastrado</td>
                                </tr>';
                    }
                    foreach ($results as $r) {
						$NomeClienteShort = mb_strimwidth(strip_tags($r->nomeCliente), 0, 44, "...");
						
                        echo '<tr>';
                        echo '<td><div align="center">' . $r->idClientes . '</div></td>';
						echo '<td>' . $NomeClienteShort . '</td>';
                        echo '<td><div align="center">' . $r->documento . '</div></td>';
                        echo '<td><div align="center">' . $r->senha . '</div></td>';
                        echo '<td><div align="center">' . $r->telefone . '</div></td>';
                        echo '<td><div align="center">' . $r->email . '</div></td>';
                        echo '<td><div align="center">';
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) {
                            echo '<a href="' . base_url() . 'index.php/clientes/visualizar/' . $r->idClientes . '" style="margin-right: 1%" class="btn tip-top" title="Visualizar mais detalhes"><i class="fas fa-eye"></i></a>';
							echo '<a href="' . base_url() . 'index.php/mine?e=' . $r->email . '&c=' . $r->senha . '" target="new" style="margin-right: 1%" class="btn btn-warning tip-top" title="Área do cliente"><i class="fas fa-key"></i></a>';
                        }
						if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) {
                                $zapnumber = preg_replace("/[^0-9]/", "", $r->telefone);
								echo '<a class="btn btn-success tip-top" style="margin-right: 1%" title="Enviar Msg WhatsApp" id="enviarWhatsApp" target="_blank" href="https://web.whatsapp.com/send?phone=55' . $zapnumber . '"><i class="fab fa-whatsapp" style="font-size:16px;"></i></a>';
                            }
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')) {
                            echo '<a href="' . base_url() . 'index.php/clientes/editar/' . $r->idClientes . '" style="margin-right: 1%" class="btn btn-info tip-top" title="Editar Cliente"><i class="fas fa-edit"></i></a>';
                        }
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dCliente')) {
                            echo '<a href="#modal-excluir" role="button" data-toggle="modal" cliente="' . $r->idClientes . '" style="margin-right: 1%" class="btn btn-danger tip-top" title="Excluir Cliente"><i class="fas fa-trash-alt"></i></a>';
                        }
                        echo '</td>';
                        echo '</tr>';
                    } ?>
          
            </tbody>
        </table>
    </div>
</div>
<?php echo $this->pagination->create_links(); ?>


<!-- Modal -->
<div id="modal-excluir" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/clientes/excluir" method="post">
        <div class="modal_header_anexos">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Excluir Cliente</h3>
        </div>
        <div class="modal-body">
            <input type="hidden" id="idCliente" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir este cliente e os dados associados a ele (OS, Vendas, Receitas)?</h5>
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
            var cliente = $(this).attr('cliente');
            $('#idCliente').val(cliente);
        });
    });
</script>
