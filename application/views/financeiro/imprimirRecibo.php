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
            padding: 20px;
        }
        .recibo-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .recibo-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        .recibo-content {
            margin: 15px 0;
            line-height: 1.5;
        }
        .recibo-texto {
            font-size: 14px;
            text-align: justify;
            margin: 10px 0;
            padding: 12px;
            background-color: #f9f9f9;
            border-left: 3px solid #333;
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
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #000;
        }
        .recibo-assinatura {
            margin-top: 30px;
            text-align: center;
        }
        .recibo-assinatura-line {
            border-top: 2px solid #000;
            width: 300px;
            margin: 0 auto;
            padding-top: 5px;
            font-size: 12px;
        }
        .recibo-data {
            text-align: right;
            margin-top: 10px;
            font-size: 12px;
        }
        @media print {
            .recibo-container {
                padding: 15px;
            }
            .no-print {
                display: none;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .recibo-content ul {
                page-break-inside: avoid;
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
                            
                            // Determinar texto referente conforme conteúdo
                            $temProdutos = !empty($produtos);
                            $temServicos = !empty($servicos) && $opcoes['mostrar_servicos'];
                            
                            $textoReferente = '';
                            if ($temProdutos && $temServicos) {
                                $textoReferente = 'referente aos seguintes serviços e materiais:';
                            } elseif ($temProdutos) {
                                $textoReferente = 'referente aos seguintes materiais:';
                            } elseif ($temServicos) {
                                $textoReferente = 'referente aos seguintes serviços:';
                            } else {
                                $textoReferente = 'referente a:';
                            }
                            ?>
                            <p style="font-size: 13px; line-height: 1.6; text-align: justify; margin: 0;">
                                Declaro que recebi de <strong><?= htmlspecialchars($nomeCliente) ?></strong>, com endereço em <strong><?= htmlspecialchars($enderecoCliente) ?></strong>, o valor de <strong>R$ <?= number_format($valorFinal, 2, ',', '.') ?></strong> em <strong><?= $dataPagamento ?></strong>, <?= $textoReferente ?>
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
                        <div style="margin: 12px 0; font-size: 12px; line-height: 1.4;">
                            <?php if (!empty($servicos) && $opcoes['mostrar_servicos']): ?>
                            <p style="margin: 5px 0 3px 0; font-size: 13px;"><strong>Serviços:</strong></p>
                            <ul style="margin-left: 15px; margin-bottom: 10px; padding-left: 10px; list-style: none;">
                                <?php foreach ($servicos as $s): 
                                    $preco = $s->preco ?? 0;
                                    $quantidade = $s->quantidade ?? 1;
                                    $subtotal = $preco * $quantidade;
                                ?>
                                <li style="margin-bottom: 3px; font-size: 11px; display: flex; justify-content: space-between;">
                                    <span>
                                        <strong><?= htmlspecialchars($s->nome ?? $s->descricao ?? $s->nome_servico ?? '') ?></strong>
                                        <?php if ($opcoes['mostrar_detalhes_servicos'] && !empty($s->detalhes)): ?>
                                            - <?= htmlspecialchars($s->detalhes) ?>
                                        <?php endif; ?>
                                    </span>
                                    <span style="margin-left: 10px; white-space: nowrap;"><strong>R$ <?= number_format($subtotal, 2, ',', '.') ?></strong></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>

                            <?php if (!empty($produtos)): ?>
                            <p style="margin: 5px 0 3px 0; font-size: 13px;"><strong>Materiais:</strong></p>
                            <ul style="margin-left: 15px; margin-bottom: 10px; padding-left: 10px; list-style: none;">
                                <?php foreach ($produtos as $p): 
                                    $subtotal = floatval($p->quantidade) * floatval($p->preco);
                                ?>
                                <li style="margin-bottom: 3px; font-size: 11px; display: flex; justify-content: space-between;">
                                    <span>
                                        <?= htmlspecialchars($p->descricao ?? $p->nome ?? '') ?>
                                    </span>
                                    <span style="margin-left: 10px; white-space: nowrap;"><strong>R$ <?= number_format($subtotal, 2, ',', '.') ?></strong></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                            
                            <!-- Totais no final -->
                            <div style="margin-top: 10px; padding-top: 8px; border-top: 1px solid #ddd; font-size: 13px;">
                                <?php if ($totalServicos > 0): ?>
                                <p style="margin: 2px 0;"><strong>Total de Serviços: R$ <?= number_format($totalServicos, 2, ',', '.') ?></strong></p>
                                <?php endif; ?>
                                <?php if ($totalProdutos > 0): ?>
                                <p style="margin: 2px 0;"><strong>Total de Materiais: R$ <?= number_format($totalProdutos, 2, ',', '.') ?></strong></p>
                                <?php endif; ?>
                                <?php 
                                $totalGeral = $totalProdutos + $totalServicos;
                                if ($totalGeral > 0): 
                                ?>
                                <p style="margin: 5px 0 0 0; font-size: 14px; font-weight: bold; border-top: 1px solid #333; padding-top: 5px;">
                                    <strong>TOTAL GERAL: R$ <?= number_format($totalGeral, 2, ',', '.') ?></strong>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Forma de Pagamento e Dados -->
                        <?php if ($lancamento->forma_pgto || !empty($pagamentos) || $pixKey): ?>
                        <div style="margin: 12px 0; font-size: 12px; line-height: 1.5;">
                            <?php if ($lancamento->forma_pgto): ?>
                            <p style="margin: 3px 0;"><strong>Forma de Pagamento:</strong> <?= htmlspecialchars($lancamento->forma_pgto) ?></p>
                            <?php endif; ?>
                            
                            <!-- Chave PIX em texto simples -->
                            <?php if ($pixKey): ?>
                            <p style="margin: 3px 0; font-size: 11px;"><strong>Chave PIX:</strong> <?= htmlspecialchars($pixKey) ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($pagamentos)): ?>
                            <ul style="margin-left: 15px; padding-left: 10px; margin-top: 5px;">
                                <?php foreach ($pagamentos as $pgto): ?>
                                <li style="margin-bottom: 2px; font-size: 11px;">
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

