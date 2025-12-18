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
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .recibo-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .recibo-numero {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
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
                        <div class="recibo-title">Recibo de Pagamento</div>
                        <div class="recibo-numero">Nº <?= str_pad($lancamento->idLancamentos, 6, 0, STR_PAD_LEFT) ?></div>
                    </div>

                    <div class="recibo-content">
                        <div class="recibo-texto">
                            <p>
                                Recebi de <strong><?= $lancamento->cliente_fornecedor ? htmlspecialchars($lancamento->cliente_fornecedor) : ($lancamento->nomeCliente ? htmlspecialchars($lancamento->nomeCliente) : 'Cliente') ?></strong>
                                <?php if ($lancamento->documento) : ?>
                                    , CPF/CNPJ: <strong><?= $lancamento->documento ?></strong>
                                <?php endif; ?>
                                , a quantia de:
                            </p>

                            <div class="recibo-valor">
                                R$ <?= number_format($valorFinal, 2, ',', '.') ?>
                                <div class="recibo-valor-extenso">
                                    (<?= valorPorExtenso($valorFinal) ?>)
                                </div>
                            </div>

                            <p>
                                Referente a: <strong><?= htmlspecialchars($lancamento->descricao) ?></strong>
                            </p>

                            <?php if ($lancamento->forma_pgto) : ?>
                                <p>
                                    Forma de pagamento: <strong><?= htmlspecialchars($lancamento->forma_pgto) ?></strong>
                                </p>
                            <?php endif; ?>

                            <?php if ($lancamento->observacoes) : ?>
                                <p style="margin-top: 15px;">
                                    <em><?= nl2br(htmlspecialchars($lancamento->observacoes)) ?></em>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="recibo-info">
                            <?php if ($lancamento->data_pagamento && $lancamento->data_pagamento != "0000-00-00") : ?>
                                <div class="recibo-info-item">
                                    <span class="recibo-info-label">Data do pagamento:</span>
                                    <?= date('d/m/Y', strtotime($lancamento->data_pagamento)) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($lancamento->data_vencimento) : ?>
                                <div class="recibo-info-item">
                                    <span class="recibo-info-label">Data de vencimento:</span>
                                    <?= date('d/m/Y', strtotime($lancamento->data_vencimento)) ?>
                                </div>
                            <?php endif; ?>

                            <div class="recibo-info-item">
                                <span class="recibo-info-label">Tipo:</span>
                                <?= ucfirst($lancamento->tipo) ?>
                            </div>

                            <?php if ($lancamento->valor_desconto > 0 && $lancamento->valor_desconto < $lancamento->valor) : ?>
                                <div class="recibo-info-item">
                                    <span class="recibo-info-label">Valor original:</span>
                                    R$ <?= number_format($lancamento->valor, 2, ',', '.') ?>
                                </div>
                                <div class="recibo-info-item">
                                    <span class="recibo-info-label">Desconto:</span>
                                    R$ <?= number_format($lancamento->valor - $lancamento->valor_desconto, 2, ',', '.') ?>
                                </div>
                            <?php endif; ?>
                        </div>
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

