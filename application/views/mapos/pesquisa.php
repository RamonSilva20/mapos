<div class="span12" style="margin-left: 0; margin-top: 0">
    <div class="span12" style="margin-left: 0">
        <form action="<?php echo current_url()?>">
        <div class="span10" style="margin-left: 0">
            <input type="text" class="span12" name="termo" placeholder="Digite o termo a pesquisar" />
        </div>
        <div class="span2">
            <button class="span12 btn"><i class=" icon-search"></i> Pesquisar</button>
        </div>
        </form>
    </div>
    <div class="span12" style="margin-left: 0; margin-top: 0">
    <!--Produtoss-->
    <div class="span6" style="margin-left: 0; margin-top: 0">
        <div class="widget-box" style="min-height: 200px">
            <div class="widget-title">
                <span class="icon">
                    <i class="icon-barcode"></i>
                </span>
                <h5>Produtos</h5>

            </div>

            <div class="widget-content nopadding">

               
                <table class="table table-bordered ">
                    <thead>
                        <tr style="backgroud-color: #2D335B">
                            <th>#</th>
                            <th>Nome</th>
                            <th>Preço</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if($produtos == null){
                            echo '<tr><td colspan="4">Nenhum produto foi encontrado.</td></tr>';
                        }
                        foreach ($produtos as $r) {
                            echo '<tr>';
                            echo '<td>' . $r->idProdutos . '</td>';
                            echo '<td>' . $r->descricao . '</td>';
                            echo '<td>' . $r->precoVenda . '</td>';

                            echo '<td>';
                            if($this->permission->checkPermission($this->session->userdata('permissao'),'vProduto')){
                                echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/produtos/visualizar/' . $r->idProdutos . '" class="btn tip-top" title="Ver mais detalhes"><i class="icon-eye-open"></i></a>'; 
                            }
                            if($this->permission->checkPermission($this->session->userdata('permissao'),'eProduto')){
                                echo '<a href="' . base_url() . 'index.php/produtos/editar/' . $r->idProdutos . '" class="btn btn-info tip-top" title="Editar Produto"><i class="icon-pencil icon-white"></i></a>'; 
                            } 
                            
                            echo '</td>';
                            echo '</tr>';
                        } ?>
                        <tr>

                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!--Clientes-->
    <div class="span6">
        <div class="widget-box" style="min-height: 200px">
            <div class="widget-title">
                <span class="icon">
                    <i class="icon-user"></i>
                </span>
                <h5>Clientes</h5>

            </div>

            <div class="widget-content nopadding">


                <table class="table table-bordered ">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>CPF/CNPJ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if( ($clientes == null) && ($celular == null) ){
                            echo '<tr><td colspan="4">Nenhum cliente foi encontrado.</td></tr>';
                        }

                        if($celular == null){
                            foreach ($clientes as $r) {
                                echo '<tr>';
                                echo '<td>' . $r->idClientes . '</td>';
                                echo '<td>' . $r->nomeCliente . '</td>';
                                echo '<td>' . $r->documento . '</td>';
                                echo '<td>';

                                if($this->permission->checkPermission($this->session->userdata('permissao'),'vCliente')){
                                    echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/clientes/visualizar/' . $r->idClientes . '" class="btn tip-top" title="Ver mais detalhes"><i class="icon-eye-open"></i></a>'; 
                                } 
                                if($this->permission->checkPermission($this->session->userdata('permissao'),'eCliente')){
                                    echo '<a href="' . base_url() . 'index.php/clientes/editar/' . $r->idClientes . '" class="btn btn-info tip-top" title="Editar Cliente"><i class="icon-pencil icon-white"></i></a>'; 
                                }

                                if($this->permission->checkPermission($this->session->userdata('permissao'),'dCliente')){
                                    echo '<a href="#modal-excluir" role="button" data-toggle="modal" cliente="'.$r->idClientes.'" style="margin-right: 1%" class="btn btn-danger tip-top" title="Excluir Cliente"><i class="icon-remove icon-white"></i></a>'; 
                                }
                                
                                
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            foreach ($celular as $r) {
                                echo '<tr>';
                                echo '<td>' . $r->idClientes . '</td>';
                                echo '<td>' . $r->nomeCliente . '</td>';
                                echo '<td>' . $r->documento . '</td>';
                                echo '<td>';

                                if($this->permission->checkPermission($this->session->userdata('permissao'),'vCliente')){
                                    echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/clientes/visualizar/' . $r->idClientes . '" class="btn tip-top" title="Ver mais detalhes"><i class="icon-eye-open"></i></a>'; 
                                } 
                                if($this->permission->checkPermission($this->session->userdata('permissao'),'eCliente')){
                                    echo '<a href="' . base_url() . 'index.php/clientes/editar/' . $r->idClientes . '" class="btn btn-info tip-top" title="Editar Cliente"><i class="icon-pencil icon-white"></i></a>'; 
                                }

                                if($this->permission->checkPermission($this->session->userdata('permissao'),'dCliente')){
                                    echo '<a href="#modal-excluir" role="button" data-toggle="modal" cliente="'.$r->idClientes.'" style="margin-right: 1%" class="btn btn-danger tip-top" title="Excluir Cliente"><i class="icon-remove icon-white"></i></a>'; 
                                }
                                
                                
                                echo '</td>';
                                echo '</tr>';
                            }
                        }


                        ?>
                        <tr>

                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    </div>
    
    <!--Serviços-->
    <div class="span6" style="margin-left: 0">
        <div class="widget-box" style="min-height: 200px">
            <div class="widget-title">
                <span class="icon">
                    <i class="icon-wrench"></i>
                </span>
                <h5>Serviços</h5>

            </div>

            <div class="widget-content nopadding">


                <table class="table table-bordered ">
                    <thead>
                        <tr style="backgroud-color: #2D335B">
                            <th>#</th>
                            <th>Nome</th>
                            <th>Preço</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if($servicos == null){
                            echo '<tr><td colspan="4">Nenhum serviço foi encontrado.</td></tr>';
                        }
                        foreach ($servicos as $r) {
                            echo '<tr>';
                            echo '<td>' . $r->idServicos . '</td>';
                            echo '<td>' . $r->nome . '</td>';
                            echo '<td>' . $r->preco . '</td>';
                            echo '<td>';
                            if($this->permission->checkPermission($this->session->userdata('permissao'),'eServico')){
                                echo '<a href="' . base_url() . 'index.php/servicos/editar/' . $r->idServicos . '" class="btn btn-info tip-top" title="Editar Serviço"><i class="icon-pencil icon-white"></i></a>'; 
                            } 
                                
                            echo '</td>';
                            echo '</tr>';
                        }
                        ?>
                        <tr>

                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!--Ordens de Serviço-->
    <div class="span6">
         <div class="widget-box" style="min-height: 200px">
            <div class="widget-title">
                <span class="icon">
                    <i class="icon-tags"></i>
                </span>
                <h5>Ordens de Serviço</h5>

            </div>

            <div class="widget-content nopadding">


                <table class="table table-bordered ">
                    <thead>
                        <tr style="backgroud-color: #2D335B">
                            <th>#</th>
                            <th>Data Inicial</th>
                            <th>Defeito</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if($os == null){
                            echo '<tr><td colspan="4">Nenhuma os foi encontrado.</td></tr>';
                        }
                        foreach ($os as $r) {
                            $dataInicial = date(('d/m/Y'), strtotime($r->dataInicial));
                            $dataFinal = date(('d/m/Y'), strtotime($r->dataFinal));
                            echo '<tr>';
                            echo '<td>' . $r->idOs . '</td>';
                            echo '<td>' . $dataInicial . '</td>';
                            echo '<td>' . $r->defeito . '</td>';

                            echo '<td>';
                            if($this->permission->checkPermission($this->session->userdata('permissao'),'vOs')){
                                echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/visualizar/' . $r->idOs . '" class="btn tip-top" title="Ver mais detalhes"><i class="icon-eye-open"></i></a>'; 
                            } 
                            if($this->permission->checkPermission($this->session->userdata('permissao'),'eOs')){
                                echo '<a href="' . base_url() . 'index.php/os/editar/' . $r->idOs . '" class="btn btn-info tip-top" title="Editar OS"><i class="icon-pencil icon-white"></i></a>'; 
                            }  
                            echo '</td>';
                            echo '</tr>';
                        }
                        ?>
                        <tr>

                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>



</div>

<!-- Modal -->

<div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

  <form action="<?php echo base_url() ?>index.php/clientes/excluir" method="post" >

  <div class="modal-header">

    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

    <h5 id="myModalLabel">Excluir Cliente</h5>

  </div>

  <div class="modal-body">

    <input type="hidden" id="idCliente" name="id" value="" />

    <h5 style="text-align: center">Deseja realmente excluir este cliente e os dados associados a ele (OS, Vendas, Receitas)?</h5>

  </div>

  <div class="modal-footer">

    <button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>

    <button class="btn btn-danger">Excluir</button>

  </div>

  </form>

</div>













<script type="text/javascript">

$(document).ready(function(){





   $(document).on('click', 'a', function(event) {

        

        var cliente = $(this).attr('cliente');

        $('#idCliente').val(cliente);



    });



});



</script>