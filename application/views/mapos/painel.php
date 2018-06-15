
<div class="row">

    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vCliente')){ ?>
    <div class="col-md-3 col-sm-6">
        <a href="<?= site_url('clientes') ; ?>">
            <div class="card p-30">
                <div class="media">
                    <div class="media-left meida media-middle">
                        <span>
                            <i class="fa fa-users f-s-40 color-primary"></i>
                        </span>
                    </div>
                    <div class="media-body media-text-right">
                        <h2>5000</h2>
                        <p class="m-b-0">Clientes</p>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <?php } ?>
    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vProduto')){ ?>

    <div class="col-md-3 col-sm-6">
        <a href="<?= site_url('produtos') ; ?>">
            <div class="card p-30">
                <div class="media">
                    <div class="media-left meida media-middle">
                        <span>
                            <i class="fa fa-barcode f-s-40 color-success"></i>
                        </span>
                    </div>
                    <div class="media-body media-text-right">
                        <h2>5000</h2>
                        <p class="m-b-0">Produtos</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <?php } ?>
    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vServico')){ ?>

    <div class="col-md-3 col-sm-6">
        <a href="<?= site_url('servicos') ; ?>">
            <div class="card p-30">
                <div class="media">
                    <div class="media-left meida media-middle">
                        <span>
                            <i class="fa fa-wrench f-s-40 color-warning"></i>
                        </span>
                    </div>
                    <div class="media-body media-text-right">
                        <h2>5000</h2>
                        <p class="m-b-0">Serviços</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <?php } ?>
    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vOs')){ ?>

    <div class="col-md-3 col-sm-6">
        <a href="<?= site_url('os') ; ?>">
            <div class="card p-30">
                <div class="media">
                    <div class="media-left meida media-middle">
                        <span>
                            <i class="fa fa-tags f-s-40 color-danger"></i>
                        </span>
                    </div>
                    <div class="media-body media-text-right">
                        <h2>5000</h2>
                        <p class="m-b-0">OS</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <?php } ?>
    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vVenda')){ ?>

    <div class="col-md-3 col-sm-6">
        <a href="<?= site_url('os') ; ?>">
            <div class="card p-30">
                <div class="media">
                    <div class="media-left meida media-middle">
                        <span>
                            <i class="fa fa-shopping-cart f-s-40 color-inverse"></i>
                        </span>
                    </div>
                    <div class="media-body media-text-right">
                        <h2>5000</h2>
                        <p class="m-b-0">Vendas</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <?php } ?>


    <?php if($this->permission->checkPermission($this->session->userdata('permissao'),'vArquivo')){ ?>

    <div class="col-md-3 col-sm-6">
        <a href="<?= site_url('arquivos') ; ?>">
            <div class="card p-30">
                <div class="media">
                    <div class="media-left meida media-middle">
                        <span>
                            <i class="fa fa-file f-s-40 color-dark"></i>
                        </span>
                    </div>
                    <div class="media-body media-text-right">
                        <h2>5000</h2>
                        <p class="m-b-0">Arquivos</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <?php } ?>

</div>


<?php if($os != null){ ?>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="card">
                <div class="card-title">
                    <h4>Estatísticas de OS</h4>
                </div>
                <div class="card-toggle-body">
                    <div id="chart-os" style="max-height: 600px"></div>
                </div>
            </div>
            <!-- /# card -->
        </div>
    </div>
<?php } ?>

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-title">
                <h4>Produtos Com Estoque Mínimo</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Produto</th>
                                <th>Preço de Venda</th>
                                <th>Estoque</th>
                                <th>Estoque Mínimo</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if($produtos != null){
                                foreach ($produtos as $p) {
                                    echo '<tr>';
                                    echo '<td>'.$p->idProdutos.'</td>';
                                    echo '<td>'.$p->descricao.'</td>';
                                    echo '<td>R$ '.$p->precoVenda.'</td>';
                                    echo '<td>'.$p->estoque.'</td>';
                                    echo '<td>'.$p->estoqueMinimo.'</td>';
                                    echo '<td>';
                                    if($this->permission->checkPermission($this->session->userdata('permissao'),'eProduto')){
                                        echo '<a href="'.base_url().'index.php/produtos/editar/'.$p->idProdutos.'" class="btn btn-info"> <i class="icon-pencil" ></i> </a>  '; 
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            }
                            else{
                                echo '<tr><td colspan="5" class="text-center">Nenhum produto com estoque baixo.</td></tr>';
                            }    
    
                            ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>


    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-title">
                <h4>Ordens de Serviço Em Aberto</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Data Inicial</th>
                                <th>Data Final</th>
                                <th>Cliente</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                    if($ordens != null){
                                        foreach ($ordens as $o) {
                                            echo '<tr>';
                                            echo '<td>'.$o->idOs.'</td>';
                                            echo '<td>'.date('d/m/Y' ,strtotime($o->dataInicial)).'</td>';
                                            echo '<td>'.date('d/m/Y' ,strtotime($o->dataFinal)).'</td>';
                                            echo '<td>'.$o->nomeCliente.'</td>';
                                            echo '<td>';
                                            if($this->permission->checkPermission($this->session->userdata('permissao'),'vOs')){
                                                echo '<a href="'.site_url('os/visualizar/').$o->idOs.'" class="btn btn-dark"> <i class="fa fa-eye" ></i> </a> '; 
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                        }
                                    }
                                    else{
                                        echo '<tr><td colspan="5" class="text-center">Nenhuma OS em aberto.</td></tr>';
                                    }    
            
                                    ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

</div>


<script src="<?= base_url('assets/js/lib/highchart/highcharts.js') ?>"></script>

<?php if($os != null) {?>
<script type="text/javascript">

    $(document).ready(function () {
    
        Highcharts.chart('chart-os', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Ordens de Serviço por status'
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                        style: {
                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                        }
                    }
                }
            },
            colors: ['#1987FF','#7DBAFD','#4BA1FE','#E8E8E8','#38AE53','#E56773'],
            series: [{
                name: 'Status',
                colorByPoint: true,
                data: [
                    <?php foreach($os as $o) {
                        echo "{ name: '".$o->status."', y: ".$o->total."},";
                    } ?>
                ]
            }]
        });


    });

</script>

<?php } ?>
