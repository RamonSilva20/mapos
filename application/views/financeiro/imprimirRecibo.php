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

                        <!-- Produtos e Serviços -->
                        <?php if (!empty($produtos) || (!empty($servicos) && $opcoes['mostrar_servicos'])): ?>
                        <div style="margin: 30px 0;">
                            <?php if (!empty($produtos)): ?>
                            <h4 style="margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 5px;">PRODUTOS</h4>
                            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                                <thead>
                                    <tr style="background-color: #f0f0f0; border-bottom: 2px solid #333;">
                                        <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Descrição</th>
                                        <th style="text-align: center; padding: 10px; border: 1px solid #ddd; width: 10%;">Qtd</th>
                                        <th style="text-align: center; padding: 10px; border: 1px solid #ddd; width: 15%;">Preço Unit.</th>
                                        <th style="text-align: right; padding: 10px; border: 1px solid #ddd; width: 15%;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $totalProdutos = 0;
                                    foreach ($produtos as $p): 
                                        $subtotal = floatval($p->quantidade) * floatval($p->preco);
                                        $totalProdutos += $subtotal;
                                    ?>
                                    <tr>
                                        <td style="padding: 8px; border: 1px solid #ddd;"><?= htmlspecialchars($p->descricao ?? $p->nome ?? '') ?></td>
                                        <td style="text-align: center; padding: 8px; border: 1px solid #ddd;"><?= number_format($p->quantidade, 2, ',', '.') ?></td>
                                        <td style="text-align: center; padding: 8px; border: 1px solid #ddd;">R$ <?= number_format($p->preco, 2, ',', '.') ?></td>
                                        <td style="text-align: right; padding: 8px; border: 1px solid #ddd;">R$ <?= number_format($subtotal, 2, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="background-color: #f9f9f9; font-weight: bold;">
                                        <td colspan="3" style="text-align: right; padding: 10px; border: 1px solid #ddd;">Total Produtos:</td>
                                        <td style="text-align: right; padding: 10px; border: 1px solid #ddd;">R$ <?= number_format($totalProdutos, 2, ',', '.') ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <?php endif; ?>

                            <?php if (!empty($servicos) && $opcoes['mostrar_servicos']): ?>
                            <h4 style="margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 5px;">SERVIÇOS</h4>
                            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                                <thead>
                                    <tr style="background-color: #f0f0f0; border-bottom: 2px solid #333;">
                                        <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Descrição</th>
                                        <?php if ($opcoes['mostrar_preco_servicos']): ?>
                                        <th style="text-align: center; padding: 10px; border: 1px solid #ddd; width: 10%;">Qtd</th>
                                        <th style="text-align: center; padding: 10px; border: 1px solid #ddd; width: 15%;">Preço Unit.</th>
                                        <?php endif; ?>
                                        <?php if ($opcoes['mostrar_subtotais']): ?>
                                        <th style="text-align: right; padding: 10px; border: 1px solid #ddd; width: 15%;">Subtotal</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $totalServicos = 0;
                                    foreach ($servicos as $s): 
                                        $preco = $s->preco ?? 0;
                                        $quantidade = $s->quantidade ?? 1;
                                        $subtotal = $preco * $quantidade;
                                        $totalServicos += $subtotal;
                                    ?>
                                    <tr>
                                        <td style="padding: 8px; border: 1px solid #ddd;">
                                            <strong><?= htmlspecialchars($s->nome ?? $s->descricao ?? $s->nome_servico ?? '') ?></strong>
                                            <?php if ($opcoes['mostrar_detalhes_servicos'] && !empty($s->detalhes)): ?>
                                            <br><small style="color: #666;"><?= htmlspecialchars($s->detalhes) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($opcoes['mostrar_preco_servicos']): ?>
                                        <td style="text-align: center; padding: 8px; border: 1px solid #ddd;"><?= number_format($quantidade, 2, ',', '.') ?></td>
                                        <td style="text-align: center; padding: 8px; border: 1px solid #ddd;">R$ <?= number_format($preco, 2, ',', '.') ?></td>
                                        <?php endif; ?>
                                        <?php if ($opcoes['mostrar_subtotais']): ?>
                                        <td style="text-align: right; padding: 8px; border: 1px solid #ddd;">R$ <?= number_format($subtotal, 2, ',', '.') ?></td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <?php if ($opcoes['mostrar_subtotais']): ?>
                                <tfoot>
                                    <tr style="background-color: #f9f9f9; font-weight: bold;">
                                        <td colspan="<?= ($opcoes['mostrar_preco_servicos'] ? 3 : 1) ?>" style="text-align: right; padding: 10px; border: 1px solid #ddd;">Total Serviços:</td>
                                        <td style="text-align: right; padding: 10px; border: 1px solid #ddd;">R$ <?= number_format($totalServicos, 2, ',', '.') ?></td>
                                    </tr>
                                </tfoot>
                                <?php endif; ?>
                            </table>
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
                                <?= $emitente->nome ?><br>
                                <?= $lancamento->usuario_nome ? 'Emitido por: ' . $lancamento->usuario_nome : '' ?>
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

