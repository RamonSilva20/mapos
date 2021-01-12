<div class="accordion" id="collapse-group">
    <div class="accordion-group widget-box">
        <div class="accordion-heading">
            <div class="widget-title">
                <a data-parent="#collapse-group" href="#collapseGOne" data-toggle="collapse">
                    <span class="icon"><i class="fas fa-shopping-bag"></i></span>
                    <h5>Detalhes da Cobrança</h5>
                </a>
            </div>
        </div>
        <div class="collapse in accordion-body">
            <div class="widget-content">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td style="text-align: right; width: 30%"><strong>Código da Transação</strong></td>
                            <td>
                                <?php echo $result->charge_id ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; width: 30%"><strong>Emitida em</strong></td>
                            <td>
                                <?php  echo date(('d/m/Y H:i:s'), strtotime($gerencianet->data->created_at)); ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right"><strong>Cliente</strong></td>
                            <td>
                                <?php echo $result->nomeCliente ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right"><strong>Valor da cobrança</strong></td>
                            <td>R$
                                <?php echo number_format($result->total, 2, ',', '.'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right"><strong>Status atual</strong></td>
                            <td>
                                <?php
                                $transactions_status = [
                            "new" => "Cobrança / Assinatura gerada.",
                            "waiting" => "Aguardando a confirmação do pagamento.",
                            "paid" => "Pagamento confirmado.",
                            "unpaid" => "Não foi possível confirmar o pagamento da cobrança.",
                            "refunded" => "Pagamento devolvido pelo lojista ou pelo intermediador Gerencianet. ",
                            "contested" => "Pagamento em processo de contestação.",
                            "canceled" => "Cobrança/Assinatura cancelada pelo vendedor ou pelo pagador. ",
                            "settled" => "Cobrança/Pagamento foi confirmada manualmente. ",
                            "link" => "Link de pagamento.",
                            "expired" => "Link/Assinatura de pagamento expirado.",
                            "active" => "Assinatura ativa. Todas as cobranças estão sendo geradas.",
                            "finished" => "Carnê está finalizado.",
                            "up_to_date" => "Carnê encontra-se em dia.",
                        ];
                        echo $transactions_status[$gerencianet->data->status]; ?>
                            </td>
                        </tr>
                        <?php
                        foreach ($gerencianet->data->items as $items) {
                            echo '<tr><td style="text-align: right"><strong>Referência</strong></td><td>'.$items->name.'</td></tr>';
                        }
                         ?>
                    </tbody>
                </table>
            <div class="accordion-heading">
            <div class="widget-title">
                <a data-parent="#collapse-group" href="#collapseGOne" data-toggle="collapse">
                    <span class="icon"><i class="fas fa-history"></i></span>
                    <h5>Histórico da cobrança</h5>
                </a>
            </div>
            </div>
                <table class="table table-bordered">
                    <tbody>
                        <?php
                        foreach ($gerencianet->data->history as $history) {
                            $dataInicial = date(('d/m/Y H:i:s'), strtotime($history->created_at));
                            echo '<tr><td style="text-align: right"><strong>'.$dataInicial.'</strong></td><td>'.$history->message.'</td></tr>';
                        }
                         ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
