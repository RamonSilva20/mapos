<?php
    $totalServico  = 0;
    $totalProdutos = 0;
    $totalOutros = 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Proposta Comercial - <?= $result->clientes_id ? ($result->nomeCliente ?? '') : ($result->cliente_nome ?? 'N/A'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 18px;
            color: #666;
        }
        .info-box {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }
        .info-box .left, .info-box .right {
            width: 48%;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .info-box p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #000;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PROPOSTA COMERCIAL</h1>
        <h2>N° <?= $result->numero_proposta ?: '#' . $result->idProposta; ?></h2>
        <p>Data: <?= date('d/m/Y', strtotime($result->data_proposta)); ?></p>
        <?php if ($result->data_validade) : ?>
            <p><strong>Validade: <?= date('d/m/Y', strtotime($result->data_validade)); ?></strong></p>
        <?php endif; ?>
    </div>

    <div class="info-box">
        <div class="left">
            <h3>DADOS DO CLIENTE</h3>
            <p><strong>Nome:</strong> <?= $result->clientes_id ? ($result->nomeCliente ?? '') : ($result->cliente_nome ?? 'N/A'); ?></p>
            <?php if ($result->documento) : ?>
                <p><strong>CPF/CNPJ:</strong> <?= $result->documento; ?></p>
            <?php endif; ?>
            <?php if ($result->telefone) : ?>
                <p><strong>Telefone:</strong> <?= $result->telefone; ?></p>
            <?php endif; ?>
            <?php if ($result->celular_cliente) : ?>
                <p><strong>Celular:</strong> <?= $result->celular_cliente; ?></p>
            <?php endif; ?>
            <?php if ($result->email) : ?>
                <p><strong>E-mail:</strong> <?= $result->email; ?></p>
            <?php endif; ?>
            <?php if ($result->rua || $result->cidade) : ?>
                <p><strong>Endereço:</strong> <?= trim(($result->rua ? $result->rua . ', ' : '') . ($result->numero ? $result->numero : '') . ($result->bairro ? ' - ' . $result->bairro : '') . ($result->cidade ? ' - ' . $result->cidade : '') . (isset($result->estado) && $result->estado ? '/' . $result->estado : '')); ?></p>
            <?php endif; ?>
        </div>
        <div class="right">
            <h3>DADOS DO VENDEDOR</h3>
            <p><strong>Nome:</strong> <?= $result->nome; ?></p>
            <?php if ($result->telefone_usuario) : ?>
                <p><strong>Telefone:</strong> <?= $result->telefone_usuario; ?></p>
            <?php endif; ?>
            <?php if ($result->email_usuario) : ?>
                <p><strong>E-mail:</strong> <?= $result->email_usuario; ?></p>
            <?php endif; ?>
            <p><strong>Status:</strong> <span style="padding: 5px 10px; background: #<?= $result->status == 'Aprovada' ? '28a745' : ($result->status == 'Recusada' ? 'dc3545' : ($result->status == 'Enviada' ? '17a2b8' : '6c757d')); ?>; color: white; border-radius: 3px;"><?= $result->status; ?></span></p>
        </div>
    </div>

    <?php if (!empty($produtos) && count($produtos) > 0) : ?>
        <h3>PRODUTOS</h3>
        <table>
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th width="10%" class="text-right">Quantidade</th>
                    <th width="15%" class="text-right">Preço Unit.</th>
                    <th width="15%" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalProdutos = 0;
                foreach ($produtos as $p) :
                    $subtotal = floatval($p->quantidade) * floatval($p->preco);
                    $totalProdutos += $subtotal;
                ?>
                    <tr>
                        <td><?= $p->descricao; ?></td>
                        <td class="text-right"><?= number_format($p->quantidade, 2, ',', '.'); ?></td>
                        <td class="text-right">R$ <?= number_format($p->preco, 2, ',', '.'); ?></td>
                        <td class="text-right">R$ <?= number_format($subtotal, 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="text-right"><strong>Total Produtos:</strong></td>
                    <td class="text-right"><strong>R$ <?= number_format($totalProdutos, 2, ',', '.'); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>

    <?php if (!empty($servicos) && count($servicos) > 0) : ?>
        <h3>SERVIÇOS</h3>
        <table>
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th width="10%" class="text-right">Quantidade</th>
                    <th width="15%" class="text-right">Preço Unit.</th>
                    <th width="15%" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalServicos = 0;
                foreach ($servicos as $s) :
                    $subtotal = floatval($s->quantidade) * floatval($s->preco);
                    $totalServicos += $subtotal;
                ?>
                    <tr>
                        <td><?= $s->descricao; ?></td>
                        <td class="text-right"><?= number_format($s->quantidade, 2, ',', '.'); ?></td>
                        <td class="text-right">R$ <?= number_format($s->preco, 2, ',', '.'); ?></td>
                        <td class="text-right">R$ <?= number_format($subtotal, 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="text-right"><strong>Total Serviços:</strong></td>
                    <td class="text-right"><strong>R$ <?= number_format($totalServicos, 2, ',', '.'); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>

    <?php if (!empty($outros) && count($outros) > 0) : ?>
        <h3>OUTROS PRODUTOS/SERVIÇOS</h3>
        <table>
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th width="15%" class="text-right">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalOutros = 0;
                foreach ($outros as $o) :
                    $totalOutros += floatval($o->preco);
                ?>
                    <tr>
                        <td><?= nl2br($o->descricao); ?></td>
                        <td class="text-right">R$ <?= number_format($o->preco, 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td class="text-right"><strong>Total:</strong></td>
                    <td class="text-right"><strong>R$ <?= number_format($totalOutros, 2, ',', '.'); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>

    <?php 
    $subtotal = ($totalProdutos ?? 0) + ($totalServicos ?? 0) + ($totalOutros ?? 0);
    $desconto = floatval($result->valor_desconto ?? 0);
    $total = $subtotal - $desconto;
    ?>
    
    <table style="width: 50%; margin-left: auto; margin-top: 20px;">
        <tr>
            <td class="text-right"><strong>Subtotal:</strong></td>
            <td class="text-right">R$ <?= number_format($subtotal, 2, ',', '.'); ?></td>
        </tr>
        <?php if ($desconto > 0) : ?>
            <tr>
                <td class="text-right"><strong>Desconto:</strong></td>
                <td class="text-right">- R$ <?= number_format($desconto, 2, ',', '.'); ?></td>
            </tr>
        <?php endif; ?>
        <tr class="total-row" style="font-size: 16px;">
            <td class="text-right"><strong>TOTAL:</strong></td>
            <td class="text-right"><strong>R$ <?= number_format($total, 2, ',', '.'); ?></strong></td>
        </tr>
    </table>

    <?php if ($result->tipo_cond_comerc == 'P' && !empty($parcelas) && count($parcelas) > 0) : ?>
        <h3>CONDIÇÕES DE PAGAMENTO</h3>
        <table>
            <thead>
                <tr>
                    <th width="5%">Nº</th>
                    <th width="15%">Dias</th>
                    <th width="20%">Vencimento</th>
                    <th width="20%" class="text-right">Valor</th>
                    <th>Observação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parcelas as $p) :
                    $dataVencimento = $p->data_vencimento ? date('d/m/Y', strtotime($p->data_vencimento)) : '-';
                ?>
                    <tr>
                        <td><?= $p->numero; ?></td>
                        <td><?= $p->dias; ?> dias</td>
                        <td><?= $dataVencimento; ?></td>
                        <td class="text-right">R$ <?= number_format($p->valor, 2, ',', '.'); ?></td>
                        <td><?= $p->observacao; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($result->tipo_cond_comerc == 'T' && $result->cond_comerc_texto) : ?>
        <h3>CONDIÇÕES DE PAGAMENTO</h3>
        <div style="padding: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">
            <?= nl2br(htmlspecialchars($result->cond_comerc_texto)); ?>
        </div>
    <?php endif; ?>

    <?php if ($result->validade_dias || $result->prazo_entrega) : ?>
        <div style="margin-top: 30px;">
            <h3>CONDIÇÕES GERAIS</h3>
            <table style="width: 100%;">
                <?php if ($result->validade_dias) : ?>
                    <tr>
                        <td style="width: 200px;"><strong>Validade da Proposta:</strong></td>
                        <td><?= $result->validade_dias; ?> dias</td>
                    </tr>
                <?php endif; ?>
                <?php if ($result->prazo_entrega) : ?>
                    <tr>
                        <td><strong>Prazo de Entrega:</strong></td>
                        <td><?= htmlspecialchars($result->prazo_entrega); ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    <?php endif; ?>

    <?php if ($result->observacoes) : ?>
        <div style="margin-top: 30px;">
            <h3>OBSERVAÇÕES</h3>
            <p style="text-align: justify; padding: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">
                <?= nl2br($result->observacoes); ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="footer">
        <p style="text-align: center; margin-top: 40px;">
            <?php if ($emitente) : ?>
                <strong><?= $emitente->nome; ?></strong><br>
                <?php if ($emitente->telefone) : echo $emitente->telefone . '<br>'; endif; ?>
                <?php if ($emitente->email) : echo $emitente->email . '<br>'; endif; ?>
                <?php if ($emitente->rua || $emitente->cidade) : 
                    echo trim(($emitente->rua ? $emitente->rua . ', ' : '') . ($emitente->numero ? $emitente->numero : '') . ($emitente->bairro ? ' - ' . $emitente->bairro : '') . ($emitente->cidade ? ' - ' . $emitente->cidade : '') . (isset($emitente->estado) && $emitente->estado ? '/' . $emitente->estado : '') . (isset($emitente->cep) && $emitente->cep ? ' - CEP: ' . $emitente->cep : '')); 
                endif; ?>
            <?php endif; ?>
        </p>
    </div>
</body>
</html>
