<?php $totalServico = 0;
$totalProdutos = 0; ?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        .invoice-box {
            max-width: 1100px;
            margin: auto;
            padding: 10px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }

        /** RTL **/
        .rtl {
            direction: rtl;
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }

        .rtl table {
            text-align: right;
        }

        .rtl table tr td:nth-child(2) {
            text-align: left;
        }

        .justify {
            text-align: justify;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="4">
                    <table>
                        <tr>
                            <td class="title">
                                <img src="<?= $emitente[0]->url_logo; ?>" style="width:100%; max-width:120px;">
                            </td>
                            <td style="text-align: right">
                                OS #: <?= $result->idOs ?><br>
                                Data Inicial: <?= date('d/m/Y', strtotime($result->dataInicial)); ?> <br>
                                Data Final: <?= $result->dataFinal ? date('d/m/Y', strtotime($result->dataFinal)) : ''; ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="4">
                    <table>
                        <tr>
                            <td>
                                Cliente: <?= $result->nomeCliente ?><br>
                                <?= $result->rua ?>, <?= $result->numero ?>, <?= $result->bairro ?><br>
                                <?= $result->cidade ?> - <?= $result->estado ?> <br>
                                <?= $result->email ?> <br>
                                <?= $result->celular_cliente ?>
                            </td>

                            <td style="text-align: right">
                                <?= $emitente[0]->nome; ?> <br>
                                <?= $emitente[0]->rua ?>, <?= $emitente[0]->numero ?>, <?= $emitente[0]->bairro ?><br>
                                <?= $emitente[0]->cidade ?> - <?= $emitente[0]->uf ?> CEP: <?= $emitente[0]->cep ?> <br>
                                Responsável: <?= $result->nome ?><br>
                                <?= $result->email_usuario ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading">
                <td colspan="2">
                    Status
                </td>
                <td colspan="2" style="text-align: center">
                    <?= $result->status ?>
                </td>
            </tr>

            <?php if ($result->garantia) { ?>
                <tr class="details">
                    <td colspan="2">
                        Garantia
                    </td>

                    <td colspan="2" style="text-align: center">
                        <?= $result->garantia ?>
                    </td>
                </tr>
            <?php } ?>

            <?php if ($result->descricaoProduto) { ?>
                <tr class="heading">
                    <td colspan="4">
                        <b>Descrição</b>
                    </td>
                </tr>
                <tr>
                    <td class="justify" colspan="4">
                        <?= htmlspecialchars_decode($result->descricaoProduto) ?>
                    </td>
                </tr>
            <?php } ?>

            <?php if ($result->defeito) { ?>
                <tr class="heading">
                    <td colspan="4">
                        <b>Defeito Apresentado</b>
                    </td>
                </tr>
                <tr>
                    <td class="justify" colspan="4">
                        <?= htmlspecialchars_decode($result->defeito) ?>
                    </td>
                </tr>
            <?php } ?>

            <?php if ($result->observacoes) { ?>
                <tr class="heading">
                    <td colspan="4">
                        <b>Observações</b>
                    </td>
                </tr>
                <tr>
                    <td class="justify" colspan="4">
                        <?= htmlspecialchars_decode($result->observacoes) ?>
                    </td>
                </tr>
            <?php } ?>

            <?php if ($result->laudoTecnico) { ?>
                <tr class="heading">
                    <td colspan="4">
                        <b>Laudo Técnico</b>
                    </td>
                </tr>
                <tr>
                    <td class="justify" colspan="4">
                        <?= htmlspecialchars_decode($result->laudoTecnico) ?>
                    </td>
                </tr>
            <?php } ?>

            <?php if ($produtos) { ?>

                <tr class="heading">
                    <td>Produto</td>
                    <td>Quantidade</td>
                    <td>Preço unit.</td>
                    <td style="text-align: center">Sub-total</td>
                </tr>

                <?php foreach ($produtos as $p) {
    $totalProdutos = $totalProdutos + $p->subTotal;
    echo '<tr class="item">';
    echo '<td>' . $p->descricao . '</td>';
    echo '<td>' . $p->quantidade . '</td>';
    echo '<td>' . $p->preco ?: $p->precoVenda . '</td>';
    echo '<td style="text-align: center">R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
    echo '</tr>';
} ?>

                <tr class="item">
                    <td colspan="3"></td>
                    <td style="text-align: center"><strong>Total em Produtos: R$ <?= number_format($totalProdutos, 2, ',', '.'); ?></strong></td>
                </tr>
            <?php } ?>

            <?php if ($servicos) { ?>

                <tr class="heading">
                    <td>Serviço</td>
                    <td>Quantidade</td>
                    <td>Preço unit.</td>
                    <td style="text-align: center">Sub-total</td>
                </tr>

                <?php foreach ($servicos as $s) {
    $preco = $s->preco ?: $s->precoVenda;
    $subtotal = $preco * ($s->quantidade ?: 1);
    $totalServico = $totalServico + $subtotal;
    echo '<tr class="item">';
    echo '<td>' . $s->nome . '</td>';
    echo '<td>' . ($s->quantidade ?: 1) . '</td>';
    echo '<td>' . $preco . '</td>';
    echo '<td>R$ ' . number_format($subtotal, 2, ',', '.') . '</td>';
    echo '</tr>';
} ?>

                <tr class="item">
                    <td colspan="3"></td>
                    <td style="text-align: center"><strong>Total em Serviços: R$ <?= number_format($totalServico, 2, ',', '.'); ?></strong></td>
                </tr>
            <?php } ?>
            <tr class="heading">
                <td colspan="4">
                    <br>
                </td>
            </tr>

            <tr class="heading">
                <td colspan="3"></td>
                <td style="text-align: center">
                    <strong>Total: R$ <?= number_format($totalProdutos + $totalServico, 2, ',', '.') ?></strong>
                </td>
            </tr>
            <?php if ($result->desconto != 0 && $result->valor_desconto != 0) { ?>
                <tr class="heading">
                    <td colspan="3"></td>
                    <td style="text-align: center">
                        <strong>Desconto: R$ <?= number_format($result->valor_desconto - ($totalProdutos + $totalServico), 2, ',', '.') ?></strong>
                    </td>
                </tr>
                <tr class="heading">
                    <td colspan="3"></td>
                    <td style="text-align: center">
                        <strong>Total com Desconto: R$ <?= number_format($result->valor_desconto, 2, ',', '.') ?></strong>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>

</html>
