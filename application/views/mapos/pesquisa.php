<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-title">
                <form action="<?php echo current_url() ?>" class="form-group">

                    <div class="row form-group">
                        <div class="col col-md-12">
                            <div class="input-group">
                                <div class="input-group-btn">
                                    <button class="btn btn-primary">
                                        <i class="fa fa-search"></i> <?= $this->lang->line('app_search'); ?>
                                    </button>
                                </div>
                                <input type="text" class="form-control" name="termo" placeholder="<?= $this->lang->line('app_input_search'); ?>" />
                            </div>
                        </div>
                    </div>


                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <p class="text-center lead"> <?= ucfirst($this->lang->line('products')); ?></p>
                    <table class="table table-bordered ">
                        <thead>
                            <tr style="backgroud-color: #2D335B">
                                <th>#</th>
                                <th><?= ucfirst($this->lang->line('product_name')); ?></th>
                                <th><?= ucfirst($this->lang->line('product_sell_price')); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if ($produtos == null) {
                                    echo '<tr><td colspan="4">'.$this->lang->line('app_not_found').'</td></tr>';
                                }
                                foreach ($produtos as $r) {
                                    echo '<tr>';
                                    echo '<td>' . $r->idProdutos . '</td>';
                                    echo '<td>' . $r->descricao . '</td>';
                                    echo '<td>' . $r->precoVenda . '</td>';
                                
                                    echo '<td>';
                                    if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vProduto')) {
                                        echo '<a href="' . site_url('produtos/read/'). $r->idProdutos . '" class="btn btn-dark tip-top" title="'.$this->lang->line('app_view').'"><i class="fa fa-eye"></i></a>';
                                    }
                                    if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eProduto')) {
                                        echo '<a href="' . site_url('produtos/update/'). $r->idProdutos . '" class="btn btn-info tip-top" title="'.$this->lang->line('app_edit').'"><i class="icon-pencil icon-white"></i></a>';
                                    }
                                
                                    echo '</td>';
                                    echo '</tr>';
                                }?>
                        </tbody>
                    </table>
                </div>
                <hr>

                <p class="text-center lead"> <?= ucfirst($this->lang->line('clients')); ?></p>
                <div class="table-responsive">

                    <table class="table table-bordered ">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?= ucfirst($this->lang->line('client_name')); ?></th>
                                <th><?= ucfirst($this->lang->line('client_doc')); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if ($clientes == null) {
                                    echo '<tr><td colspan="4">'.$this->lang->line('app_not_found').'</td></tr>';
                                }
                                foreach ($clientes as $r) {
                                    echo '<tr>';
                                    echo '<td>' . $r->idClientes . '</td>';
                                    echo '<td>' . $r->nomeCliente . '</td>';
                                    echo '<td>' . $r->documento . '</td>';
                                    echo '<td>';
                                
                                    if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) {
                                        echo '<a href="' . site_url('clientes/read/'). $r->idClientes . '" class="btn btn-dark" title="'.$this->lang->line('app_view').'"><i class="fa fa-eye"></i></a>';
                                    }
                                    if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eCliente')) {
                                        echo '<a href="' . site_url('clientes/update/') . $r->idClientes . '" class="btn btn-info tip-top" title="'.$this->lang->line('app_edit').'"><i class="icon-pencil icon-white"></i></a>';
                                    }
                                
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            ?>
                        </tbody>
                    </table>

                </div>
                <hr>

                <p class="text-center lead"> <?= ucfirst($this->lang->line('services')); ?></p>
                <div class="table-responsive">
                    <table class="table table-bordered ">
                        <thead>
                            <tr style="backgroud-color: #2D335B">
                                <th>#</th>
                                <th><?= ucfirst($this->lang->line('service_name')); ?></th>
                                <th><?= ucfirst($this->lang->line('service_price')); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if ($servicos == null) {
                                    echo '<tr><td colspan="4">'.$this->lang->line('app_not_found').'</td></tr>';
                                }
                                foreach ($servicos as $r) {
                                    echo '<tr>';
                                    echo '<td>' . $r->idServicos . '</td>';
                                    echo '<td>' . $r->nome . '</td>';
                                    echo '<td>' . $r->preco . '</td>';
                                    echo '<td>';
                                    if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eServico')) {
                                        echo '<a href="' . site_url('servicos/update/'). $r->idServicos . '" class="btn btn-info tip-top" title="'.$this->lang->line('app_edit').'"><i class="icon-pencil icon-white"></i></a>';
                                    }
                                
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            ?>
                        </tbody>
                    </table>
                </div>

                <hr>

                <p class="text-center lead"> <?= ucfirst($this->lang->line('os')); ?></p>
                <div class="table-responsive">
                    <table class="table table-bordered ">
                        <thead>
                            <tr style="backgroud-color: #2D335B">
                                <th>#</th>
                                <th><?= ucfirst($this->lang->line('os_initial_date')); ?></th>
                                <th><?= ucfirst($this->lang->line('os_defect')); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if ($os == null) {
                                    echo '<tr><td colspan="4">'.$this->lang->line('app_not_found').'</td></tr>';
                                }
                                foreach ($os as $r) {
                                    $dataInicial = date(('d/m/Y'), strtotime($r->dataInicial));
                                    $dataFinal = date(('d/m/Y'), strtotime($r->dataFinal));
                                    echo '<tr>';
                                    echo '<td>' . $r->idOs . '</td>';
                                    echo '<td>' . $dataInicial . '</td>';
                                    echo '<td>' . $r->defeito . '</td>';
    
                                    echo '<td>';
                                    if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
                                        echo '<a href="' . site_url('os/read/'). $r->idOs . '" class="btn tip-top" title="'.$this->lang->line('app_view').'"><i class="icon-eye-open"></i></a>';
                                    }
                                    if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
                                        echo '<a href="' . site_url('os/update/'). $r->idOs . '" class="btn btn-info tip-top" title="'.$this->lang->line('app_edit').'"><i class="icon-pencil icon-white"></i></a>';
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            ?>
                        </tbody>
                    </table>
                </div>


            </div>
        </div>
    </div>
</div>
