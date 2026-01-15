<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?= $this->config->item('app_name') ?> - Recibo de Pagamento Nº#<?= str_pad($lancamento->idLancamentos, 6, 0, STR_PAD_LEFT) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap5.3.2.min.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/imprimir.css">
    <style>
        .recibo-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }
        .recibo-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .recibo-title {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
        }
        .recibo-content {
            margin: 30px 0;
            line-height: 1.8;
        }
        .recibo-texto {
            font-size: 16px;
            text-align: justify;
            margin: 20px 0;
            padding: 20px;
            background-color: #f9f9f9;
            border-left: 4px solid #333;
        }
        .recibo-valor {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin: 30px 0;
            padding: 15px;
            background-color: #e8e8e8;
            border: 2px solid #333;
        }
        .recibo-valor-extenso {
            font-size: 18px;
            font-style: italic;
            margin-top: 10px;
        }
        .recibo-info {
            margin: 20px 0;
        }
        .recibo-info-item {
            margin: 10px 0;
            font-size: 15px;
        }
        .recibo-info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 150px;
        }
        .recibo-footer {
            margin-top: 60px;
            padding-top: 40px;
            border-top: 2px solid #000;
        }
        .recibo-assinatura {
            margin-top: 80px;
            text-align: center;
        }
        .recibo-assinatura-line {
            border-top: 2px solid #000;
            width: 300px;
            margin: 0 auto;
            padding-top: 5px;
        }
        .recibo-data {
            text-align: right;
            margin-top: 20px;
            font-size: 14px;
        }
        @media print {
            .recibo-container {
                padding: 20px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="main-page">
        <div class="sub-page">
            <header>
                <?php if ($emitente == null) : ?>
                    <div class="alert alert-danger" role="alert">
                        Você precisa configurar os dados do emitente. >>> <a href="<?= base_url() ?>index.php/mapos/emitente">Configurar</a>
                    </div>
                <?php else : ?>
                    <div class="imgLogo" class="align-middle">
                        <img src="<?= $emitente->url_logo ?>" class="img-fluid" style="width:140px;">
                    </div>
                    <div class="emitente">
                        <span style="font-size: 16px;"><b><?= $emitente->nome ?></b></span><br>
                        <?php if ($emitente->cnpj != "00.000.000/0000-00") : ?>
                            <span class="align-middle">CNPJ: <?= $emitente->cnpj ?></span><br>
                        <?php endif; ?>
                        <span class="align-middle">
                            <?= $emitente->rua . ', ' . $emitente->numero . ', ' . $emitente->bairro ?><br>
                            <?= $emitente->cidade . ' - ' . $emitente->uf . ' - ' . $emitente->cep ?>
                        </span>
                    </div>
                    <div class="contatoEmitente">
                        <span style="font-weight: bold;">Tel: <?= $emitente->telefone ?></span><br>
                        <span style="font-weight: bold;"><?= $emitente->email ?></span><br>
                    </div>
                <?php endif; ?>
            </header>

            <section>
                <div class="recibo-container">
                    <div class="recibo-header">
                        <div class="recibo-title">Recibo Nº <?= str_pad($lancamento->idLancamentos, 6, 0, STR_PAD_LEFT) ?></div>
                    </div>

                    <div class="recibo-content">
                        <div class="recibo-texto">
                            <?php
                            // Buscar dados do cliente
                            $nomeCliente = $lancamento->cliente_fornecedor ?: ($lancamento->nomeCliente ?: 'Cliente');
                            
                            // Montar endereço do cliente
                            $enderecoCliente = '';
                            if ($lancamento->rua || $lancamento->numero || $lancamento->bairro || $lancamento->cidade) {
                                $enderecoParts = array_filter([
                                    $lancamento->rua,
                                    $lancamento->numero,
                                    $lancamento->complemento,
                                    $lancamento->bairro,
                                    $lancamento->cidade,
                                    $lancamento->estado,
                                    $lancamento->cep
                                ]);
                                $enderecoCliente = implode(', ', $enderecoParts);
                            } else {
                                $enderecoCliente = 'Endereço não informado';
                            }
                            
                            // Data do pagamento
                            $dataPagamento = '';
                            if ($lancamento->data_pagamento && $lancamento->data_pagamento != "0000-00-00") {
                                $dataPagamento = date('d/m/Y', strtotime($lancamento->data_pagamento));
                            } else {
                                $dataPagamento = date('d/m/Y');
                            }
                            ?>
                            <p style="font-size: 16px; line-height: 1.8; text-align: justify;">
                                Declaro que recebi de <strong><?= htmlspecialchars($nomeCliente) ?></strong>, com endereço em <strong><?= htmlspecialchars($enderecoCliente) ?></strong>, o valor de <strong>R$ <?= number_format($valorFinal, 2, ',', '.') ?></strong> em <strong><?= $dataPagamento ?></strong>, referente aos seguintes serviços e materiais:
                            </p>
                        </div>

                        <!-- Produtos e Serviços em formato texto simples -->
                        <?php 
                        $totalProdutos = 0;
                        $totalServicos = 0;
                        
                        // Calcular totais
                        if (!empty($produtos)) {
                            foreach ($produtos as $p) {
                                $totalProdutos += floatval($p->quantidade) * floatval($p->preco);
                            }
                        }
                        
                        if (!empty($servicos) && $opcoes['mostrar_servicos']) {
                            foreach ($servicos as $s) {
                                $preco = $s->preco ?? 0;
                                $quantidade = $s->quantidade ?? 1;
                                $totalServicos += $preco * $quantidade;
                            }
                        }
                        ?>
                        
                        <?php if (!empty($produtos) || (!empty($servicos) && $opcoes['mostrar_servicos'])): ?>
                        <div style="margin: 25px 0; font-size: 16px; line-height: 1.8;">
                            <?php if (!empty($produtos)): ?>
                            <p style="margin-bottom: 10px;"><strong>Materiais:</strong></p>
                            <ul style="margin-left: 20px; margin-bottom: 20px;">
                                <?php foreach ($produtos as $p): ?>
                                <li style="margin-bottom: 8px;">
                                    <?= htmlspecialchars($p->descricao ?? $p->nome ?? '') ?>
                                    <?php if ($opcoes['mostrar_preco_servicos']): ?>
                                        - Qtd: <?= number_format($p->quantidade, 2, ',', '.') ?>
                                        - Unit: R$ <?= number_format($p->preco, 2, ',', '.') ?>
                                        <?php if ($opcoes['mostrar_subtotais']): ?>
                                            - Subtotal: R$ <?= number_format(floatval($p->quantidade) * floatval($p->preco), 2, ',', '.') ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>

                            <?php if (!empty($servicos) && $opcoes['mostrar_servicos']): ?>
                            <p style="margin-bottom: 10px;"><strong>Serviços:</strong></p>
                            <ul style="margin-left: 20px; margin-bottom: 20px;">
                                <?php foreach ($servicos as $s): ?>
                                <li style="margin-bottom: 8px;">
                                    <strong><?= htmlspecialchars($s->nome ?? $s->descricao ?? $s->nome_servico ?? '') ?></strong>
                                    <?php if ($opcoes['mostrar_detalhes_servicos'] && !empty($s->detalhes)): ?>
                                        - <?= htmlspecialchars($s->detalhes) ?>
                                    <?php endif; ?>
                                    <?php if ($opcoes['mostrar_preco_servicos']): ?>
                                        - Qtd: <?= number_format($s->quantidade ?? 1, 2, ',', '.') ?>
                                        - Unit: R$ <?= number_format($s->preco ?? 0, 2, ',', '.') ?>
                                        <?php if ($opcoes['mostrar_subtotais']): ?>
                                            - Subtotal: R$ <?= number_format(($s->preco ?? 0) * ($s->quantidade ?? 1), 2, ',', '.') ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                            
                            <!-- Totais no final -->
                            <div style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #ddd;">
                                <?php if ($totalProdutos > 0): ?>
                                <p style="margin: 5px 0;"><strong>Total de Materiais: R$ <?= number_format($totalProdutos, 2, ',', '.') ?></strong></p>
                                <?php endif; ?>
                                <?php if ($totalServicos > 0): ?>
                                <p style="margin: 5px 0;"><strong>Total de Serviços: R$ <?= number_format($totalServicos, 2, ',', '.') ?></strong></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Forma de Pagamento e Dados -->
                        <?php if ($lancamento->forma_pgto || !empty($pagamentos)): ?>
                        <div style="margin: 25px 0; font-size: 16px; line-height: 1.8;">
                            <?php if ($lancamento->forma_pgto): ?>
                            <p><strong>Forma de Pagamento:</strong> <?= htmlspecialchars($lancamento->forma_pgto) ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($pagamentos)): ?>
                            <p style="margin-top: 10px;"><strong>Pagamentos Realizados:</strong></p>
                            <ul style="margin-left: 20px;">
                                <?php foreach ($pagamentos as $pgto): ?>
                                <li style="margin-bottom: 5px;">
                                    R$ <?= number_format($pgto->valor, 2, ',', '.') ?> 
                                    em <?= $pgto->data_pagamento && $pgto->data_pagamento != '0000-00-00' ? date('d/m/Y', strtotime($pgto->data_pagamento)) : '-' ?>
                                    <?php if ($pgto->forma_pgto): ?>
                                        - <?= htmlspecialchars($pgto->forma_pgto) ?>
                                    <?php endif; ?>
                                    <?php if ($pgto->observacao): ?>
                                        (<?= htmlspecialchars($pgto->observacao) ?>)
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                    </div>

                    <div class="recibo-footer">
                        <div class="recibo-data">
                            <?php
                            $meses = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
                            $mesAtual = $meses[date('n') - 1];
                            ?>
                            <?= $emitente->cidade ?>, <?= date('d') ?> de <?= $mesAtual ?> de <?= date('Y') ?>
                        </div>

                        <div class="recibo-assinatura">
                            <div class="recibo-assinatura-line">
                                Tecnico Litoral<br>
                                Tiago Marques Bomfim
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script type="text/javascript">
        window.print();
    </script>
</body>

</html>

