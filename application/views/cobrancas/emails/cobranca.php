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
                                <img src="<?= $emitente->url_logo; ?>" style="width:100%; max-width:120px;">
                            </td>
                            <td style="text-align: right">
                                Cobrança #<?= $cobranca->idCobranca ?><br>
                                Expiração: <?= $cobranca->expire_at ? date('d/m/Y', strtotime($cobranca->expire_at)) : ''; ?>
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
                                Cliente: <?= $cobranca->nomeCliente ?><br>
                                <?= $cobranca->rua ?>, <?= $cobranca->numero ?>, <?= $cobranca->bairro ?><br>
                                <?= $cobranca->cidade ?> - <?= $cobranca->estado ?> <br>
                                <?= $cobranca->email ?> <br>
                            </td>

                            <td style="text-align: right">
                                <?= $emitente->nome; ?> <br>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading">
                <td colspan="4">
                    <br>
                </td>
            </tr>

            <?php if ($cobranca->pdf) : ?>
                <tr>
                    <td colspan="2">
                        PDF
                    </td>
                    <td colspan="3">
                        <a href="<?= $cobranca->pdf ?>" target="_blank">Abrir em outra aba</a>
                    </td>
                </tr>
            <?php endif ?>

            <?php if ($cobranca->link) : ?>
                <tr>
                    <td colspan="1">
                        Link
                    </td>
                    <td colspan="3">
                        <a href="<?= $cobranca->link ?>" target="_blank">Abrir em outra aba</a>
                    </td>
                </tr>
            <?php endif ?>

            <?php if ($cobranca->barcode) : ?>
                <tr>
                    <td colspan="1">
                        Código de Barras
                    </td>
                    <td colspan="3">
                        <?= $cobranca->barcode ?>
                    </td>
                </tr>
            <?php endif ?>

            <tr class="heading">
                <td colspan="3"></td>
                <td style="text-align: center">
                    <strong>Total: R$ <?= number_format($cobranca->total / 100, 2, ',', '.') ?></strong>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
