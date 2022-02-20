 <div class="span12" style="margin-left: 0; margin-top: 0">
    <div class="span12" style="margin-left: 0">
        <form action="<?php echo current_url() ?>">
            <div class="span10" style="margin-left: 0">
                <input type="text" class="span12" name="termo" placeholder="Digite o termo a pesquisar"/>
            </div>
            <div class="span2">
                <button class="button btn btn-mini btn-warning">
                  <span class="button__icon"><i class='bx bx-search-alt'></i></span> <span class="button__text2">Pesquisar</span></button>
            </div>
        </form>
    </div>
    <div class="span12" style="margin-left: 0; margin-top: 0">
        <!--Produtoss-->
        <div class="span6" style="margin-left: 0; margin-top: 0">
            <div class="widget-box" style="min-height: 200px">
                <div class="widget-title" style="margin: -20px 0 0">
                    <span class="icon">
                        <i class="fas fa-shopping-bag"></i>
                    </span>
                    <h5>Produtos</h5>
                </div>
                <div class="widget-content nopadding tab-content">
                    <table class="table table-bordered ">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($produtos == null) {
                            echo '<tr><td colspan="4">Nenhum produto foi encontrado.</td></tr>';
                        }
                        foreach ($produtos as $r) {
                            echo '<tr>';
                            echo '<td>' . $r->idProdutos . '</td>';
                            echo '<td>' . $r->descricao . '</td>';
                            echo '<td>' . $r->precoVenda . '</td>';
                            echo '<td>' . $r->estoque . '</td>';
                            echo '<td>';
                            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vProduto')) {
                                echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/produtos/visualizar/' . $r->idProdutos . '" class="btn-nwe" title="Ver mais detalhes"><i class="bx bx-show"></i></a>';
                            }
                            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eProduto')) {
                                echo '<a href="' . base_url() . 'index.php/produtos/editar/' . $r->idProdutos . '" class="btn-nwe3" title="Editar produto"><i class="bx bx-edit"></i></a>';
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
                <div class="widget-title" style="margin: -20px 0 0">
                    <span class="icon">
                        <i class="fas fa-user"></i>
                    </span>
                    <h5>Clientes</h5>
                </div>
                <div class="widget-content nopadding tab-content">
                    <table class="table table-bordered ">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>CPF/CNPJ</th>
                            <th>Cliente / Fornecedor</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($clientes == null) {
                            echo '<tr><td colspan="4">Nenhum cliente foi encontrado.</td></tr>';
                        }
                        foreach ($clientes as $r) {
                            echo '<tr>';
                            echo '<td>' . $r->idClientes . '</td>';
                            echo '<td>' . $r->nomeCliente . '</td>';
                            echo '<td>' . $r->documento . '</td>';
                            $cor = ($r->fornecedor ? '#CDB380' : '#CD0000');
                            echo '<td><span class="badge" style="background-color: ' . $cor . '; border-color: ' . $cor . '">' . ($r->fornecedor ? 'Fornecedor' : 'Cliente') . '</span> </td>';
                            echo '<td>';
                            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) {
                                echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/clientes/visualizar/' . $r->idClientes . '" class="btn tip-top" title="Ver mais detalhes"><i class="fas fa-eye"></i></a>';
                            }
                            if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')) {
                                echo '<a href="' . base_url() . 'index.php/clientes/editar/' . $r->idClientes . '" class="btn btn-info tip-top" title="Editar Cliente"><i class="fas fa-edit"></i></a>';
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
    <!--Serviços-->
    <div class="span6" style="margin-left: 0">
        <div class="widget-box" style="min-height: 200px">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-wrench"></i>
                </span>
                <h5>Serviços</h5>
            </div>
            <div class="widget-content nopadding tab-content">
                <table class="table table-bordered ">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>Preço</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($servicos == null) {
                        echo '<tr><td colspan="4">Nenhum serviço foi encontrado.</td></tr>';
                    }
                    foreach ($servicos as $r) {
                        echo '<tr>';
                        echo '<td>' . $r->idServicos . '</td>';
                        echo '<td>' . $r->nome . '</td>';
                        echo '<td>' . $r->preco . '</td>';
                        echo '<td>';
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eServico')) {
                            echo '<a href="' . base_url() . 'index.php/servicos/editar/' . $r->idServicos . '" class="btn btn-info tip-top" title="Editar Serviço"><i class="fas fa-edit"></i></a>';
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
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-diagnoses"></i>
                </span>
                <h5>Ordens de Serviço</h5>
            </div>
            <div class="widget-content nopadding tab-content">
                <table class="table table-bordered ">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Data Inicial</th>
                        <th>Descrição</th>
                        <th>Defeito</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($os == null) {
                        echo '<tr><td colspan="4">Nenhuma os foi encontrado.</td></tr>';
                    }
                    foreach ($os as $r) {
                        $dataInicial = date(('d/m/Y'), strtotime($r->dataInicial));
                        $dataFinal = date(('d/m/Y'), strtotime($r->dataFinal));
                        echo '<tr>';
                        echo '<td>' . $r->idOs . '</td>';
                        echo '<td>' . $dataInicial . '</td>';
                        echo '<td>' . $r->descricaoProduto . '</td>';
                        echo '<td>' . $r->defeito . '</td>';
                        echo '<td>';
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
                            echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/visualizar/' . $r->idOs . '" class="btn-nwe" title="Ver mais detalhes"><i class="bx bx-show"></i></a>';
                        }
                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
                            echo '<a href="' . base_url() . 'index.php/os/editar/' . $r->idOs . '" class="btn-nwe3" title="Editar OS"><i class="bx bx-edit"></i></a>';
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
