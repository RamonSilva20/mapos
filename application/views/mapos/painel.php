<!--[if lt IE 9]><script language="javascript" type="text/javascript" src="<?php echo base_url(); ?>js/dist/excanvas.min.js"></script><![endif]-->

<script language="javascript" type="text/javascript" src="<?= base_url(); ?>assets/js/dist/jquery.jqplot.min.js"></script>
<script type="text/javascript" src="<?= base_url(); ?>assets/js/dist/plugins/jqplot.pieRenderer.min.js"></script>
<script type="text/javascript" src="<?= base_url(); ?>assets/js/dist/plugins/jqplot.donutRenderer.min.js"></script>
<script src='<?= base_url(); ?>assets/js/fullcalendar.min.js'></script>
<script src='<?= base_url(); ?>assets/js/fullcalendar/locales/pt-br.js'></script>

<link href='<?= base_url(); ?>assets/css/fullcalendar.min.css' rel='stylesheet' />
<link rel="stylesheet" type="text/css" href="<?= base_url(); ?>assets/js/dist/jquery.jqplot.min.css" />

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>

<!-- New Bem-vindos -->
<div id="content-bemv">
    <div class="bemv">Dashboard</div>
    <div></div>
</div>

<!--Action boxes-->
<ul class="cardBox">
    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vCliente')) : ?>
        <li class="card">
            <div class="grid-blak">
                <a href="<?= site_url('clientes') ?>">
                    <div class="numbers">Clientes</div>
                    <div class="cardName">F1</div>
                </a>
            </div>
            <a href="<?= site_url('clientes') ?>">
                <div class="lord-icon02">
                    <i class='bx bx-user iconBx02'></i>
                </div>
            </a>
        </li>
    <?php endif ?>

    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vProduto')) : ?>
        <li class="card">
            <div class="grid-blak">
                <a href="<?= site_url('produtos') ?>">
                    <div class="numbers">Produtos</div>
                    <div class="cardName">F2</div>
                </a>
            </div>
            <a href="<?= site_url('produtos') ?>">
                <div class="lord-icon02">
                    <i class='bx bx-basket iconBx02'></i>
                </div>
            </a>
        </li>
    <?php endif ?>

    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vServico')) : ?>
        <li class="card">
            <div class="grid-blak">
                <a href="<?= site_url('servicos') ?>">
                    <div class="numbers">Serviços</div>
                    <div class="cardName">F3</div>
                </a>
            </div>
            <a href="<?= site_url('servicos') ?>">
                <div class="lord-icon03">
                    <i class='bx bx-wrench iconBx03'></i>
                </div>
            </a>
        </li>
    <?php endif ?>

    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?>
        <li class="card">
            <div class="grid-blak">
                <a href="<?= site_url('os') ?>">
                    <div class="numbers N-tittle">Ordens</div>
                    <div class="cardName">F4</div>
                </a>
            </div>
            <a href="<?= site_url('os') ?>">
                <div class="lord-icon04">
                    <i class='bx bx-file iconBx04'></i>
                </div>
            </a>
        </li>
    <?php endif ?>

    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vVenda')) : ?>
        <li class="card">
            <div class="grid-blak">
                <a href="<?= site_url('vendas/') ?>">
                    <div class="numbers N-tittle">Vendas</div>
                    <div class="cardName">F6</div>
                </a>
            </div>
            <a href="<?= site_url('vendas/') ?>">
            <div class="lord-icon05">
                <i class='bx bx-cart-alt iconBx05'></i></span>
            </div>
            </a>
        </li>
    <?php endif ?>

    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vGarantia')) : ?>
        <li class="card">
            <div class="grid-blak">
                <a href="<?= site_url('financeiro/lancamentos') ?>">
                    <div class="numbers N-tittle">Lançamentos</div>
                    <div class="cardName">F7</div>
                </a>
            </div>
            <a href="<?= site_url('financeiro/lancamentos') ?>">
            <div class="lord-icon06">
                <i class="bx bx-bar-chart-alt-2 iconBx06"></i>
            </div>
            </a>
        </li>
    <?php endif ?>
</ul>
<!--End-Action boxes-->

<div class="row-fluid" style="margin-top: 0; display: flex">
    <div class="Sspan12">
        <div class="widget-box2">
            <div>
                <h5 class="cardHeader">Agenda</h5>
            </div>
            <div class="widget-content">
                <table>
                    <div id='source-calendar'>
                        <form method="post">
                            <select style="padding-left: 30px" class="span12" name="statusOsGet" id="statusOsGet" value="">
                                <option value="">Todos os Status</option>
                                <option value="Aberto">Aberto</option>
                                <option value="Faturado">Faturado</option>
                                <option value="Negociação">Negociação</option>
                                <option value="Orçamento">Orçamento</option>
                                <option value="Em Andamento">Em Andamento</option>
                                <option value="Finalizado">Finalizado</option>
                                <option value="Cancelado">Cancelado</option>
                                <option value="Aguardando Peças">Aguardando Peças</option>
                                <option value="Aprovado">Aprovado</option>
                            </select>
                            <button type="button" class="btn-xs" id="btn-calendar"><i class="bx bx-search iconX2"></i></button>
                        </form>
                    </div>
                </table>
            </div>
        </div>

        <!-- New widget right -->
        <div class="new-statisc">
            <div class="widget-box-new widbox-blak" style="height:100%">
                <div>
                    <h5 class="cardHeader">Estatísticas do Sistema</h5>
                </div>

                <div class="new-bottons">
                    <a href="<?php echo base_url(); ?>index.php/clientes/adicionar" class="card tip-top" title="Add Clientes e Fornecedores">
                        <div><i class='bx bxs-group iconBx'></i></div>
                        <div>
                            <div class="cardName2"><?= $this->db->count_all('clientes'); ?></div>
                            <div class="cardName">Clientes</div>
                        </div>
                    </a>

                    <a href="<?php echo base_url(); ?>index.php/produtos/adicionar" class="card tip-top" title="Adicionar Produtos">
                        <div><i class='bx bxs-package iconBx2'></i></div>
                        <div>
                            <div class="cardName2"><?= $this->db->count_all('produtos'); ?></div>
                            <div class="cardName">Produtos</div>
                        </div>
                    </a>

                    <a href="<?php echo base_url() ?>index.php/servicos/adicionar" class="card tip-top" title="Adicionar serviços">
                        <div><i class='bx bxs-stopwatch iconBx3'></i></div>
                        <div>
                            <div class="cardName2"><?= $this->db->count_all('servicos'); ?></div>
                            <div class="cardName">Serviços</div>
                        </div>
                    </a>

                    <a href="<?php echo base_url(); ?>index.php/os/adicionar" class="card tip-top" title="Adicionar OS">
                        <div><i class='bx bxs-spreadsheet iconBx4'></i></div>
                        <div>
                            <div class="cardName2"><?= $this->db->count_all('os'); ?></div>
                            <div class="cardName">Ordens</div>
                        </div>
                    </a>

                    <a href="<?php echo base_url(); ?>index.php/garantias" class="card tip-top" title="Adicionar garantia">
                        <div><i class='bx bxs-receipt iconBx6'></i></div>
                        <div>
                            <div class="cardName2"><?= $this->db->count_all('garantias'); ?></div>
                            <div class="cardName">Garantias</div>
                        </div>
                    </a>

                    <a href="<?php echo base_url() ?>index.php/vendas/adicionar" class="card tip-top" title="Adicionar Vendas">
                        <div><i class='bx bxs-cart-alt iconBx5'></i></div>
                        <div>
                            <div class="cardName2"><?= $this->db->count_all('vendas'); ?></div>
                            <div class="cardName">Vendas</div>
                        </div>
                    </a>

                    <!-- responsavel por fazer complementar a variavel "$financeiro_mes_dia->" de receita e despesa -->
                    <?php if ($estatisticas_financeiro != null) {
                        if ($estatisticas_financeiro->total_receita != null || $estatisticas_financeiro->total_despesa != null || $estatisticas_financeiro->total_receita_pendente != null || $estatisticas_financeiro->total_despesa_pendente != null) {  ?>

                            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) : ?>
                                <?php $diaRec = "VALOR_" . date('m') . "_REC";
                                $diaDes = "VALOR_" . date('m') . "_DES"; ?>

                                <a href="<?php echo base_url() ?>index.php/financeiro/lancamentos" class="card tip-top" title="Adicionar receita">
                                    <div><i class='bx bxs-up-arrow-circle iconBx7'></i></div>
                                    <div>
                                        <div class="cardName1 cardName2">R$ <?php echo number_format(($financeiro_mes_dia->$diaRec - $financeiro_mes_dia->$diaDes), 2, ',', '.'); ?></div>
                                        <div class="cardName">Receita do dia</div>
                                    </div>
                                </a>

                                <a href="<?php echo base_url() ?>index.php/financeiro/lancamentos" class="card tip-top" title="Adiciona despesa">
                                    <div><i class='bx bxs-down-arrow-circle iconBx8'></i></div>
                                    <div>
                                        <div class="cardName1 cardName2">R$ <?php echo number_format(($financeiro_mes_dia->$diaDes ? $financeiro_mes_dia->$diaDes : 0), 2, ',', '.'); ?></div>
                                        <div class="cardName">Despesa do dia</div>
                                    </div>
                                </a>
                            <?php endif ?>

                    <?php  }
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fim new widget right -->

<?php if ($estatisticas_financeiro != null) {
    if ($estatisticas_financeiro->total_receita != null || $estatisticas_financeiro->total_despesa != null || $estatisticas_financeiro->total_receita_pendente != null || $estatisticas_financeiro->total_despesa_pendente != null) {  ?>

        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'rFinanceiro')) : ?>
            <!-- Start Charts -->
            <div class="new-balance">
                <div class="widget-box0">
                    <div class="widget-title2">
                        <h5 class="cardHeader">Balanço Mensal do Ano</h5>
                        <form method="get" style="display:flex;margin-right:18px;justify-content:flex-end">
                            <input type="number" name="year" style="width:65px;margin-left:17px;margin-bottom:25px;margin-top:10px;padding-left: 35px" value="<?php echo intval(preg_replace('/[^0-9]/', '', $this->input->get('year'))) ?: date('Y') ?>">
                            <button type="submit" class="btn-xsx"><i class='bx bx-search iconX'></i></button>
                        </form>
                    </div>
                    <div class="widget-content" style="padding:10px 25px 5px 25px">
                        <div class="row-fluid" style="margin-top:-35px;">
                            <div class="span12">
                                <canvas id="myChart" style="overflow-x: scroll;margin-left: -14px"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="widget-box-statist">
                    <h5 class="cardHeader">Estatísticas Financeira</h5>
                    <div class="widget-content" style="padding:10px;margin:25px 0 0">
                        <canvas id="statusOS"> </canvas>
                    </div>
                </div>
            </div>
        <?php endif ?>

<script type="text/javascript">
    if (window.outerWidth > 2000) {
        Chart.defaults.font.size = 15;
    };
    if (window.outerWidth < 2000 && window.outerWidth > 1367) {
        Chart.defaults.font.size = 11;
    };
    if (window.outerWidth < 1367 && window.outerWidth > 480) {
        Chart.defaults.font.size = 9.5;
    };
    if (window.outerWidth < 480) {
        Chart.defaults.font.size = 8.5;
    };

    var ctx = document.getElementById('myChart').getContext('2d');
    var StatusOS = document.getElementById('statusOS').getContext('2d');

    var myChart = new Chart(ctx, {
        data: {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            datasets: [{
                    label: 'Receita Líquida',
                    data: [<?php echo($financeiro_mes->VALOR_JAN_REC - $financeiro_mes->VALOR_JAN_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_FEV_REC - $financeiro_mes->VALOR_FEV_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_MAR_REC - $financeiro_mes->VALOR_MAR_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_ABR_REC - $financeiro_mes->VALOR_ABR_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_MAI_REC - $financeiro_mes->VALOR_MAI_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_JUN_REC - $financeiro_mes->VALOR_JUN_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_JUL_REC - $financeiro_mes->VALOR_JUL_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_AGO_REC - $financeiro_mes->VALOR_AGO_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_SET_REC - $financeiro_mes->VALOR_SET_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_OUT_REC - $financeiro_mes->VALOR_OUT_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_NOV_REC - $financeiro_mes->VALOR_NOV_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_DEZ_REC - $financeiro_mes->VALOR_DEZ_DES); ?>
                    ],

                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderRadius: 15,
                },

                {
                    label: 'Receita Bruta',
                    data: [<?php echo($financeiro_mes->VALOR_JAN_REC); ?>,
                        <?php echo($financeiro_mes->VALOR_FEV_REC); ?>,
                        <?php echo($financeiro_mes->VALOR_MAR_REC); ?>,
                        <?php echo($financeiro_mes->VALOR_ABR_REC); ?>,
                        <?php echo($financeiro_mes->VALOR_MAI_REC); ?>,
                        <?php echo($financeiro_mes->VALOR_JUN_REC); ?>,
                        <?php echo($financeiro_mes->VALOR_JUL_REC); ?>,
                        <?php echo($financeiro_mes->VALOR_AGO_REC); ?>,
                        <?php echo($financeiro_mes->VALOR_SET_REC); ?>,
                        <?php echo($financeiro_mes->VALOR_OUT_REC); ?>,
                        <?php echo($financeiro_mes->VALOR_NOV_REC); ?>,
                        <?php echo($financeiro_mes->VALOR_DEZ_REC); ?>
                    ],

                    backgroundColor: 'rgba(255, 206, 86, 0.5)',
                    borderRadius: 15,
                },

                {
                    label: 'Despesas',
                    data: [<?php echo($financeiro_mes->VALOR_JAN_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_FEV_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_MAR_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_ABR_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_MAI_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_JUN_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_JUL_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_AGO_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_SET_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_OUT_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_NOV_DES); ?>,
                        <?php echo($financeiro_mes->VALOR_DEZ_DES); ?>
                    ],

                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderRadius: 15,
                },

                {
                    label: 'Inadimplência',
                    data: [<?php echo($financeiro_mesinadipl->VALOR_JAN_REC); ?>,
                        <?php echo($financeiro_mesinadipl->VALOR_FEV_REC); ?>,
                        <?php echo($financeiro_mesinadipl->VALOR_MAR_REC); ?>,
                        <?php echo($financeiro_mesinadipl->VALOR_ABR_REC); ?>,
                        <?php echo($financeiro_mesinadipl->VALOR_MAI_REC); ?>,
                        <?php echo($financeiro_mesinadipl->VALOR_JUN_REC); ?>,
                        <?php echo($financeiro_mesinadipl->VALOR_JUL_REC); ?>,
                        <?php echo($financeiro_mesinadipl->VALOR_AGO_REC); ?>,
                        <?php echo($financeiro_mesinadipl->VALOR_SET_REC); ?>,
                        <?php echo($financeiro_mesinadipl->VALOR_OUT_REC); ?>,
                        <?php echo($financeiro_mesinadipl->VALOR_NOV_REC); ?>,
                        <?php echo($financeiro_mesinadipl->VALOR_DEZ_REC); ?>
                    ],

                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderRadius: 15,
                }
            ]

        },
        // configuração
        type: 'bar',
        options: {
            locale: 'pt-BR',
            scales: {
                y: {
                    ticks: {
                        callback: (value, index, values) => {
                            return new Intl.NumberFormat('pt-BR', {
                                style: 'currency',
                                currency: 'BRL',
                                maximumSignificantDidits: 1
                            }).format(value);
                        }
                    }
                },
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Meses'
                    }
                }
            },

            plugins: {
                tooltip: {
                    callbacks: {
                        beforeTitle: function(context) {
                            return 'Referente ao mês de';
                        }
                    }
                },

                legend: {
                    position: "bottom",
                    labels: {
                        usePointStyle: true,
                    }
                }
            }
        }
    });

    var myChart = new Chart(statusOS, {
        data: {
            labels: [
                'Receita total', 'Receita pendente',
                'Previsto em caixa', 'Despesa total',
                'Despesa pendente', 'Previsto a entrar'
            ],
            datasets: [{
                label: 'Total',
                data: [
                    <?php echo ($estatisticas_financeiro->total_receita != null) ?  $estatisticas_financeiro->total_receita : '0.00'; ?>,
                    <?php echo ($estatisticas_financeiro->total_receita_pendente != null) ?  $estatisticas_financeiro->total_receita_pendente : '0.00'; ?>,
                    <?php echo($estatisticas_financeiro->total_receita - $estatisticas_financeiro->total_despesa); ?>,
                    <?php echo ($estatisticas_financeiro->total_despesa != null) ?  $estatisticas_financeiro->total_despesa : '0.00'; ?>,
                    <?php echo ($estatisticas_financeiro->total_despesa_pendente != null) ?  $estatisticas_financeiro->total_despesa_pendente : '0.00'; ?>,
                    <?php echo($estatisticas_financeiro->total_receita_pendente - $estatisticas_financeiro->total_despesa_pendente); ?>
                ],

                backgroundColor: [
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(255, 159, 64, 0.5)',
                    'rgba(153, 102, 255, 0.5)'
                ],
                borderWidth: 1
            }]
        },

        // configuração
        type: 'polarArea',
        options: {
            locale: 'pt-BR',
            scales: {
                r: {
                    ticks: {
                        callback: (value, index, values) => {
                            return new Intl.NumberFormat('pt-BR', {
                                style: 'currency',
                                currency: 'BRL',
                                maximumSignificantDidits: 1
                            }).format(value);
                        }
                    },
                    beginAtZero: true,
                }
            },
            plugins: {
                legend: {
                    position: "bottom",
                    labels: {
                        usePointStyle: true,

                    }
                }
            }
        }
    });

    function responsiveFonts() {
        myChart.update();
    }
</script>
<?php  }
} ?>
</div>
</div>

<!-- Start Staus OS -->
<div class="span12A" style="margin-left: 0">
    <div class="AAA">
        <div class="widget-box0 widbox-blak">
            <div>
                <h5 class="cardHeader">Produtos Com Estoque Mínimo</h5>
            </div>
            <div class="widget-content">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Cod.</th>
                            <th>Produto</th>
                            <th>Preço de Venda</th>
                            <th>Estoque</th>
                            <th class="ph3">Estoque Mínimo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($produtos != null) : ?>
                            <?php foreach ($produtos as $p) : ?>
                                <tr>
                                    <td>
                                        <?= $p->idProdutos ?>
                                    </td>
                                    <td class="cli1">
                                        <?= $p->descricao ?>
                                    </td>
                                    <td>R$
                                        <?= $p->precoVenda ?>
                                    </td>
                                    <td>
                                        <?= $p->estoque ?>
                                    </td>
                                    <td class="ph3">
                                        <?= $p->estoqueMinimo ?>
                                    </td>
                                    <td>
                                        <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eProduto')) : ?>
                                            <a href="<?= base_url() ?>index.php/produtos/editar/<?= $p->idProdutos ?>" class="btn-nwe3 tip-top" title="Editar">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <a href="#atualizar-estoque" role="button" data-toggle="modal" produto="<?= $p->idProdutos ?>" estoque="<?= $p->estoque ?>" class="btn-nwe5 tip-top" title="Atualizar Estoque">
                                                <i class="bx bx-plus-circle"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6">Nenhum produto com estoque baixo.</td>
                            </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="widget-box0 widbox-blak">
        <div>
            <h5 class="cardHeader">Ordens de Serviço Em Aberto</h5>
        </div>
        <div class="widget-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Data Inicial</th>
                        <th>Data Final</th>
                        <th>Cliente</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($ordens != null) : ?>
                        <?php foreach ($ordens as $o) : ?>
                            <tr>
                                <td>
                                    <?= $o->idOs ?>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($o->dataInicial)) ?>
                                </td>

                                <td><?php if ($o->dataFinal != null) {
                                    echo date('d/m/Y', strtotime($o->dataFinal));
                                } else {
                                    echo "";
                                } ?></td>

                                <td class="cli1">
                                    <?= $o->nomeCliente ?>
                                </td>
                                <td>
                                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?>
                                        <a href="<?= base_url() ?>index.php/os/visualizar/<?= $o->idOs ?>" class="btn-nwe tip-top" title="Visualizar">
                                            <i class="bx bx-show"></i> </a>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5">Nenhuma OS em aberto.</td>
                        </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>


    <div class="widget-box0 widbox-blak">
        <div>
            <h5 class="cardHeader">Ordens de Serviço Aguardando Peças</h5>
        </div>
        <div class="widget-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Data Inicial</th>
                        <th>Data Final</th>
                        <th>Cliente</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($ordens1 != null) : ?>
                        <?php foreach ($ordens1 as $o) : ?>
                            <tr>
                                <td>
                                    <?= $o->idOs ?>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($o->dataInicial)) ?>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($o->dataFinal)) ?>
                                </td>
                                <td class="cli1">
                                    <?= $o->nomeCliente ?>
                                </td>
                                <td>
                                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?>
                                        <a href="<?= base_url() ?>index.php/os/visualizar/<?= $o->idOs ?>" class="btn-nwe tip-top" title="Visualizar">
                                            <i class="bx bx-show"></i></a>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5">Nenhuma OS Aguardando Peças.</td>
                        </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="widget-box0 widbox-blak">
        <div>
            <h5 class="cardHeader">Ordens de Serviço Em Andamento</h5>
        </div>
        <div class="widget-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Data Inicial</th>
                        <th>Data Final</th>
                        <th>Cliente</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($ordens_andamento != null) : ?>
                        <?php foreach ($ordens_andamento as $o) : ?>
                            <tr>
                                <td>
                                    <?= $o->idOs ?>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($o->dataInicial)) ?>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($o->dataFinal)) ?>
                                </td>
                                <td class="cli1">
                                    <?= $o->nomeCliente ?>
                                </td>
                                <td>
                                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) : ?>
                                        <a href="<?= base_url() ?>index.php/os/visualizar/<?= $o->idOs ?>" class="btn-nwe tip-top" title="Visualizar">
                                            <i class="bx bx-show"></i></a>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5">Nenhuma OS em Andamento.</td>
                        </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Fim Staus OS -->

<!-- Modal Status OS Calendar -->
<div id="calendarModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Status OS Detalhada</h3>
    </div>
    <div class="modal-body">
        <div class="span5" id="divFormStatusOS" style="margin-left: 0"></div>
        <h4><b>OS:</b> <span id="modalId" class="modal-id"></span></h4>
        <h5 id="modalCliente" class="modal-cliente"></h5>
        <div id="modalDataInicial" class="modal-DataInicial"></div>
        <div id="modalDataFinal" class="modal-DataFinal"></div>
        <div id="modalGarantia" class="modal-Garantia"></div>
        <div id="modalStatus" class="modal-Status"></div>
        <div id="modalDescription" class="modal-Description"></div>
        <div id="modalDefeito" class="modal-Defeito"></div>
        <div id="modalObservacoes" class="modal-Observacoes"></div>
        <div id="modalTotal" class="modal-Total"></div>
        <div id="modalDesconto" class="modal-Total"></div>
        <div id="modalValorFaturado" class="modal-ValorFaturado"></div>
    </div>
    <div class="modal-footer">
        <?php
                                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
                                            echo '<a id="modalIdVisualizar" style="margin-right: 1%" href="" class="btn tip-top" title="Ver mais detalhes"><i class="fas fa-eye"></i></a>';
                                        }
                                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eOs')) {
                                            echo '<a id="modalIdEditar" style="margin-right: 1%" href="" class="btn btn-info tip-top" title="Editar OS"><i class="fas fa-edit"></i></a>';
                                        }
                                        if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dOs')) {
                                            echo '<a id="linkExcluir" href="#modal-excluir-os" role="button" data-toggle="modal" os="" class="btn btn-danger tip-top" title="Excluir OS"><i class="fas fa-trash-alt"></i></a>  ';
                                        }
?>
    </div>
</div>

<!-- Modal Excluir Os -->
<div id="modal-excluir-os" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/os/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Excluir OS</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="modalIdExcluir" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir esta OS?</h5>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true"><span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
            <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-trash'></i></span> <span class="button__text2">Excluir</span></button>
        </div>
    </form>
</div>

<!-- Modal Estoque -->
<div id="atualizar-estoque" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/produtos/atualizar_estoque" method="post" id="formEstoque">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel"><i class="fas fa-plus-square"></i> Atualizar Estoque</h5>
        </div>
        <div class="modal-body">
            <div class="control-group">
                <label for="estoqueAtual" class="control-label">Estoque Atual</label>
                <div class="controls">
                    <input id="estoqueAtual" type="text" name="estoqueAtual" value="" readonly />
                </div>
            </div>

            <div class="control-group">
                <label for="estoque" class="control-label">Adicionar Produtos<span class="required">*</span></label>
                <div class="controls">
                    <input type="hidden" id="idProduto" class="idProduto" name="id" value="" />
                    <input id="estoque" type="text" name="estoque" value="" />
                </div>
            </div>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true"><span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
            <button class="button btn btn-primary"><span class="button__icon"><i class="bx bx-sync"></i></span><span class="button__text2">Atualizar</span></button>
        </div>
    </form>
</div>

<script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<!-- Modal Estoque-->
<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', 'a', function(event) {
            var produto = $(this).attr('produto');
            var estoque = $(this).attr('estoque');
            $('.idProduto').val(produto);
            $('#estoqueAtual').val(estoque);
        });

        $('#formEstoque').validate({
            rules: {
                estoque: {
                    required: true,
                    number: true
                }
            },
            messages: {
                estoque: {
                    required: 'Campo Requerido.',
                    number: 'Informe um número válido.'
                }
            },
            errorClass: "help-inline",
            errorElement: "span",
            highlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').addClass('error');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error');
                $(element).parents('.control-group').addClass('success');
            }
        });

        var srcCalendarEl = document.getElementById('source-calendar');
        var srcCalendar = new FullCalendar.Calendar(srcCalendarEl, {
            locale: 'pt-br',
            height: 500,
            editable: false,
            selectable: false,
            businessHours: true,
            dayMaxEvents: true, // allow "more" link when too many events
            displayEventTime: false,
            events: {
                url: "<?= base_url() . "index.php/mapos/calendario"; ?>",
                method: 'GET',
                extraParams: function() { // a function that returns an object
                    return {
                        status: $("#statusOsGet").val(),
                    };
                },
                failure: function() {
                    alert('Falha ao buscar OS de calendário!');
                },
            },
            eventClick: function(info) {
                var eventObj = info.event.extendedProps;
                $('#modalId').html(eventObj.id);
                $('#modalIdVisualizar').attr("href", "<?php echo base_url(); ?>index.php/os/visualizar/" + eventObj.id);
                if (eventObj.editar) {
                    $('#modalIdEditar').show();
                    $('#linkExcluir').show();
                    $('#modalIdEditar').attr("href", "<?php echo base_url(); ?>index.php/os/editar/" + eventObj.id);
                    $('#modalIdExcluir').val(eventObj.id);
                } else {
                    $('#modalIdEditar').hide();
                    $('#linkExcluir').hide();
                }
                $('#modalCliente').html(eventObj.cliente);
                $('#modalDataInicial').html(eventObj.dataInicial);
                $('#modalDataFinal').html(eventObj.dataFinal);
                $('#modalGarantia').html(eventObj.garantia);
                $('#modalStatus').html(eventObj.status);
                $('#modalDescription').html(eventObj.description);
                $('#modalDefeito').html(eventObj.defeito);
                $('#modalObservacoes').html(eventObj.observacoes);
                $('#modalTotal').html(eventObj.total);
                $('#modalDesconto').html(eventObj.desconto);
                $('#modalValorFaturado').html(eventObj.valorFaturado);

                $('#eventUrl').attr('href', event.url);
                $('#calendarModal').modal();
            },
        });

        srcCalendar.render();

        $('#btn-calendar').on('click', function() {
            srcCalendar.refetchEvents();
        });
    });
</script>