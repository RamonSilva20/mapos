<style>
    select {
        width: 70px;
    }
</style>
<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-user"></i>
        </span>
        <h5>Clientes</h5>
    </div>
    <div class="span12" style="margin-left: 0">
        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aCliente')) { ?>
            <div class="span3">
                <a href="<?= base_url() ?>index.php/clientes/adicionar" class="button btn btn-mini btn-success"
                    style="max-width: 165px">
                    <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">
                        Cliente / Fornecedor
                    </span>
                </a>
            </div>
        <?php } ?>
        <form class="span9" method="get" action="<?= base_url() ?>index.php/clientes"
            style="display: flex; justify-content: flex-end;">
            <div class="span3">
                <input type="text" name="pesquisa" id="pesquisa"
                    placeholder="Buscar por Nome, Doc, Email ou Telefone..." class="span12"
                    value="<?= $this->input->get('pesquisa') ?>">
            </div>
            <div class="span1">
                <button class="button btn btn-mini btn-warning" style="min-width: 30px">
                    <span class="button__icon"><i class='bx bx-search-alt'></i></span></button>
            </div>
        </form>
    </div>

    <div class="widget-box">
        <h5 style="padding: 3px 0"></h5>
        <div class="widget-content nopadding tab-content">
            <table id="tabela" class="table table-bordered ">
                <thead>
                    <tr>
                        <th>Cod.</th>
                        <th>Nome</th>
                        <th>Contato</th>
                        <th>CPF/CNPJ</th>
                        <th>Telefone</th>
                        <th>Celular</th>
                        <th>Email</th>
                        <th>Tipo</th> <!-- Nova coluna para Fornecedor/Cliente -->
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!$results) {
                        echo '<tr>
                    <td colspan="9">Nenhum Cliente Cadastrado</td>
                  </tr>';
                    }
                    foreach ($results as $r) {
                        echo '<tr>';
                        echo '<td>' . $r->idClientes . '</td>';
                        echo '<td><a href="' . base_url() . 'index.php/clientes/visualizar/' . $r->idClientes . '" style="margin-right: 1%">' . $r->nomeCliente . '</a></td>';
                        echo '<td>' . $r->contato . '</td>';
                        echo '<td>' . $r->documento . '</td>';
                        //Campo Telefone com link para WhatsApp
                        if (!empty($r->telefone)) {
                            $telefoneLimpo = preg_replace('/[^0-9]/', '', $r->telefone);
                            echo '<td><a href="https://api.whatsapp.com/send?phone=55' . $telefoneLimpo . '" target="_blank">' . $r->telefone . '</a></td>';
                        } else {
                            echo '<td>-</td>';
                        }                 
                        //Campo Celular com link para WhatsApp
                        if (!empty($r->celular)) {
                            $celularLimpo = preg_replace('/[^0-9]/', '', $r->celular);
                            echo '<td><a href="https://api.whatsapp.com/send?phone=55' . $celularLimpo . '" target="_blank">' . $r->celular . '</a></td>';
                        } else {
                            echo '<td>-</td>';
                        } 
                        echo '<td>' . $r->email . '</td>';

                        // Verifica se é Fornecedor ou Cliente
                        if ($r->fornecedor == 1) {
                            echo '<td><span class="label label-primary">Fornecedor</span></td>';
                        } else {
                            echo '<td><span class="label label-success">Cliente</span></td>';
                        }

                        echo '<td>';
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) {
                            echo '<a href="' . base_url() . 'index.php/clientes/visualizar/' . $r->idClientes . '" style="margin-right: 1%" class="btn-nwe" title="Ver mais detalhes"><i class="bx bx-show bx-xs"></i></a>';
                            echo '<a href="' . base_url() . 'index.php/mine?e=' . $r->email . '" target="new" style="margin-right: 1%" class="btn-nwe2" title="Área do cliente"><i class="bx bx-key bx-xs"></i></a>';
                        }
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')) {
                            echo '<a href="' . base_url() . 'index.php/clientes/editar/' . $r->idClientes . '" style="margin-right: 1%" class="btn-nwe3" title="Editar Cliente"><i class="bx bx-edit bx-xs"></i></a>';
                        }
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dCliente')) {
                            echo '<a href="#modal-excluir" role="button" data-toggle="modal" cliente="' . $r->idClientes . '" style="margin-right: 1%" class="btn-nwe4" title="Excluir Cliente"><i class="bx bx-trash-alt bx-xs"></i></a>';
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
<div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/clientes/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Excluir Cliente</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="idCliente" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir este cliente e os dados associados a ele (OS,
                Vendas, Receitas)?</h5>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true"><span class="button__icon"><i
                        class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
            <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-trash'></i></span> <span
                    class="button__text2">Excluir</span></button>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $(document).on('click', 'a', function (event) {
            var cliente = $(this).attr('cliente');
            $('#idCliente').val(cliente);
        });
    });
</script>
