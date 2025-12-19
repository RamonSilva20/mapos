<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/custom.css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<style>
    .dashboard-container {
        padding: 20px;
    }
    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .dashboard-card {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-left: 4px solid;
        transition: transform 0.2s;
    }
    .dashboard-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .dashboard-card.receber {
        border-left-color: #28a745;
    }
    .dashboard-card.pagar {
        border-left-color: #dc3545;
    }
    .dashboard-card.saldo {
        border-left-color: #007bff;
    }
    .dashboard-card.vencidas {
        border-left-color: #ff6b00;
    }
    .card-title {
        font-size: 14px;
        color: #666;
        margin-bottom: 10px;
        font-weight: 500;
    }
    .card-value {
        font-size: 28px;
        font-weight: bold;
        color: #333;
    }
    .card-icon {
        font-size: 40px;
        float: right;
        opacity: 0.3;
        margin-top: -10px;
    }
    .dashboard-charts {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .chart-container {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .chart-title {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #333;
    }
    .dashboard-alerts {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
    }
    .alert-container {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .alert-title {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #333;
    }
    .alert-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .alert-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .alert-item:last-child {
        border-bottom: none;
    }
    .alert-item:hover {
        background: #f5f5f5;
    }
    .alert-item-info {
        flex: 1;
    }
    .alert-item-desc {
        font-weight: 500;
        color: #333;
    }
    .alert-item-cliente {
        font-size: 12px;
        color: #666;
    }
    .alert-item-valor {
        font-weight: bold;
        color: #333;
    }
    .alert-item-data {
        font-size: 12px;
        color: #999;
        margin-left: 10px;
    }
    .badge-vencido {
        background: #dc3545;
        color: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        margin-left: 10px;
    }
    .badge-avenc {
        background: #ffc107;
        color: #333;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        margin-left: 10px;
    }
    .empty-state {
        text-align: center;
        padding: 40px;
        color: #999;
    }
    @media (max-width: 768px) {
        .dashboard-cards {
            grid-template-columns: 1fr;
        }
        .dashboard-charts {
            grid-template-columns: 1fr;
        }
        .dashboard-alerts {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-chart-line"></i>
        </span>
        <h5>Dashboard Financeiro</h5>
    </div>

    <div class="dashboard-container">
        <!-- Cards Superiores -->
        <div class="dashboard-cards">
            <div class="dashboard-card receber">
                <div class="card-title">Total a Receber</div>
                <div class="card-value">R$ <?= number_format($dashboardData['totalReceber'], 2, ',', '.') ?></div>
                <div class="card-icon"><i class='bx bx-trending-up'></i></div>
            </div>

            <div class="dashboard-card pagar">
                <div class="card-title">Total a Pagar</div>
                <div class="card-value">R$ <?= number_format($dashboardData['totalPagar'], 2, ',', '.') ?></div>
                <div class="card-icon"><i class='bx bx-trending-down'></i></div>
            </div>

            <div class="dashboard-card saldo">
                <div class="card-title">Saldo Atual</div>
                <div class="card-value" style="color: <?= $dashboardData['saldoAtual'] >= 0 ? '#28a745' : '#dc3545' ?>">
                    R$ <?= number_format($dashboardData['saldoAtual'], 2, ',', '.') ?>
                </div>
                <div class="card-icon"><i class='bx bx-wallet'></i></div>
            </div>

            <div class="dashboard-card vencidas">
                <div class="card-title">Contas Vencidas</div>
                <div class="card-value"><?= $dashboardData['contasVencidas'] ?></div>
                <div class="card-icon"><i class='bx bx-error-circle'></i></div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="dashboard-charts">
            <div class="chart-container">
                <div class="chart-title">Receitas vs Despesas - Mês Atual</div>
                <canvas id="chartReceitasDespesas"></canvas>
            </div>

            <div class="chart-container">
                <div class="chart-title">Fluxo de Caixa - Últimos 6 Meses</div>
                <canvas id="chartFluxoCaixa"></canvas>
            </div>
        </div>

        <!-- Alertas -->
        <div class="dashboard-alerts">
            <div class="alert-container">
                <div class="alert-title">
                    <i class='bx bx-time-five'></i> Contas a Vencer (Próximos 7 dias)
                    <span class="badge-avenc"><?= $dashboardData['contasAVencer'] ?> contas</span>
                </div>
                <ul class="alert-list">
                    <?php if (count($contasAVencer) > 0) : ?>
                        <?php foreach ($contasAVencer as $conta) : ?>
                            <li class="alert-item">
                                <div class="alert-item-info">
                                    <div class="alert-item-desc"><?= htmlspecialchars($conta->descricao) ?></div>
                                    <div class="alert-item-cliente"><?= htmlspecialchars($conta->cliente_fornecedor ?: $conta->nomeCliente ?: 'Cliente não informado') ?></div>
                                </div>
                                <div>
                                    <span class="alert-item-valor">
                                        R$ <?= number_format($conta->valor_desconto > 0 ? $conta->valor_desconto : $conta->valor, 2, ',', '.') ?>
                                    </span>
                                    <span class="alert-item-data">
                                        <?= date('d/m/Y', strtotime($conta->data_vencimento)) ?>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <li class="empty-state">Nenhuma conta a vencer nos próximos 7 dias</li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="alert-container">
                <div class="alert-title">
                    <i class='bx bx-error-circle'></i> Contas Vencidas
                    <span class="badge-vencido"><?= $dashboardData['contasVencidas'] ?> contas</span>
                </div>
                <ul class="alert-list">
                    <?php if (count($contasVencidas) > 0) : ?>
                        <?php foreach ($contasVencidas as $conta) : ?>
                            <li class="alert-item">
                                <div class="alert-item-info">
                                    <div class="alert-item-desc"><?= htmlspecialchars($conta->descricao) ?></div>
                                    <div class="alert-item-cliente"><?= htmlspecialchars($conta->cliente_fornecedor ?: $conta->nomeCliente ?: 'Cliente não informado') ?></div>
                                </div>
                                <div>
                                    <span class="alert-item-valor">
                                        R$ <?= number_format($conta->valor_desconto > 0 ? $conta->valor_desconto : $conta->valor, 2, ',', '.') ?>
                                    </span>
                                    <span class="alert-item-data">
                                        <?= date('d/m/Y', strtotime($conta->data_vencimento)) ?>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <li class="empty-state">Nenhuma conta vencida</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    // Gráfico Receitas vs Despesas
    const ctxReceitasDespesas = document.getElementById('chartReceitasDespesas').getContext('2d');
    new Chart(ctxReceitasDespesas, {
        type: 'bar',
        data: {
            labels: ['Receitas', 'Despesas'],
            datasets: [{
                label: 'Valor (R$)',
                data: [
                    <?= number_format($receitasDespesasMes->receitas ?? 0, 2, '.', '') ?>,
                    <?= number_format($receitasDespesasMes->despesas ?? 0, 2, '.', '') ?>
                ],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            }
        }
    });

    // Gráfico Fluxo de Caixa
    const ctxFluxoCaixa = document.getElementById('chartFluxoCaixa').getContext('2d');
    const meses = [<?php foreach ($fluxoCaixa as $mes) echo "'" . $mes['mes_nome'] . "',"; ?>];
    const receitas = [<?php foreach ($fluxoCaixa as $mes) echo $mes['receitas'] . ','; ?>];
    const despesas = [<?php foreach ($fluxoCaixa as $mes) echo $mes['despesas'] . ','; ?>];

    new Chart(ctxFluxoCaixa, {
        type: 'line',
        data: {
            labels: meses,
            datasets: [{
                label: 'Receitas',
                data: receitas,
                borderColor: 'rgba(40, 167, 69, 1)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Despesas',
                data: despesas,
                borderColor: 'rgba(220, 53, 69, 1)',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            }
        }
    });
</script>



