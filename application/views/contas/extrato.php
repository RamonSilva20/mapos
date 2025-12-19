<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-university"></i>
        </span>
        <h5>Extrato - <?php echo $conta->conta; ?></h5>
    </div>
    
    <div class="span12" style="margin-left: 0; margin-bottom: 10px;">
        <div class="alert alert-info">
            <strong>Saldo Atual:</strong> 
            <span style="font-size: 18px; font-weight: bold; color: <?php echo ($conta->saldo ?? 0) >= 0 ? 'green' : 'red'; ?>;">
                R$ <?php echo number_format($conta->saldo ?? 0, 2, ',', '.'); ?>
            </span>
        </div>
    </div>

    <div class="span12" style="margin-left: 0">
        <form method="get" action="<?php echo base_url(); ?>index.php/contas/extrato/<?php echo $conta->idContas; ?>">
            <div class="span3">
                <input type="text" name="data_inicio" autocomplete="off" id="data_inicio" placeholder="Data Inicial" class="span12 datepicker" value="<?php echo $data_inicio ? date('d/m/Y', strtotime($data_inicio)) : ''; ?>">
            </div>
            <div class="span3">
                <input type="text" name="data_fim" autocomplete="off" id="data_fim" placeholder="Data Final" class="span12 datepicker" value="<?php echo $data_fim ? date('d/m/Y', strtotime($data_fim)) : ''; ?>">
            </div>
            <div class="span1">
                <button class="button btn btn-mini btn-warning" style="min-width: 30px">
                    <span class="button__icon"><i class='bx bx-search-alt'></i></span></button>
            </div>
            <div class="span5" style="text-align: right;">
                <a href="<?php echo base_url(); ?>index.php/contas" class="button btn btn-mini">
                    <span class="button__icon"><i class="bx bx-undo"></i></span><span class="button__text2">Voltar</span>
                </a>
            </div>
        </form>
    </div>

    <div class="widget-box" style="margin-top: 8px">
        <div class="widget-content nopadding tab-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!$extrato) {
                        echo '<tr><td colspan="6">Nenhum lançamento encontrado no período.</td></tr>';
                    } else {
                        foreach ($extrato as $l) {
                            $data = date('d/m/Y', strtotime($l->data_vencimento));
                            $valor = $l->valor_desconto > 0 ? $l->valor_desconto : $l->valor;
                            $tipo = $l->tipo == 'receita' ? 'Receita' : 'Despesa';
                            $cor = $l->tipo == 'receita' ? 'green' : 'red';
                            $status = $l->baixado == 1 ? 'Pago' : 'Pendente';
                            $badgeStatus = $l->baixado == 1 ? 'label-success' : 'label-important';
                            
                            echo '<tr>';
                            echo '<td>' . $data . '</td>';
                            echo '<td>' . htmlspecialchars($l->descricao) . '</td>';
                            echo '<td>' . ($l->nome_categoria ?: '-') . '</td>';
                            echo '<td><span style="color: ' . $cor . '; font-weight: bold;">' . $tipo . '</span></td>';
                            echo '<td style="color: ' . $cor . '; font-weight: bold;">R$ ' . number_format($valor, 2, ',', '.') . '</td>';
                            echo '<td><span class="label ' . $badgeStatus . '">' . $status . '</span></td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(".datepicker").datepicker({
            dateFormat: 'dd/mm/yy'
        });
    });
</script>


