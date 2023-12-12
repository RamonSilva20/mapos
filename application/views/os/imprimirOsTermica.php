<?php $totalServico = 0;
$totalProdutos = 0; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Map_OS_<?php echo $result->idOs ?>_<?php echo $result->nomeCliente ?></title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/matrix-style.css" />
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="<?= base_url('assets/css/custom.css'); ?>" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
    <style>
        .table {

            width: 72mm;
            margin: auto;
        }
    </style>
</head>

<body id=body class="body">
<div id ="principal">
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div class="invoice-content">
                    <div class="invoice-head" style="margin-bottom: 0">
                        <table class="table table-condensed">
                            <tbody>
                                <?php if ($emitente == null) { ?>
                                    <tr>
                                        <td colspan="5" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a>
                                            <<<</td> </tr> <?php } else { ?> 
                                    <td style="width: 25% ;text-align: center" ><img src=" <?php echo $emitente->url_logo; ?> " style="max-height: 100px"></td>
                                    <tr>
                                        <td colspan="5" style="text-align: center; font-size: 11px;" >
                                            <span style="font-size: 12px; text-transform: uppercase"><b><?php echo $emitente->nome; ?></b></br></span>
                                            <?php if($emitente->cnpj != "00.000.000/0000-00") { ?><span class="icon"><i class="fas fa-fingerprint" style="margin:5px 1px"></i> <?php echo $emitente->cnpj; ?></span></br><?php } ?>
                                            <span><?php echo $emitente->rua . ', ' . $emitente->numero . ', ' . $emitente->bairro . ', ' . $emitente->cidade . ' - ' . $emitente->uf; ?></span></br>
                                            <span><?php echo $emitente->email; ?> - <?php echo $emitente->telefone; ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: center; width: 100%; font-size: 12px;">
                                            <b>N° OS: </b><span><?php echo $result->idOs ?></span>
                                            <span style="padding-left: 5%;"><b>Emissão:</b> <?php echo date('d/m/Y') ?></span>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
    
                        <table class="table table-condensend">
                            <tbody>
                                <tr>
                                    <td style="width: 50%; padding-left: 0; font-size: 11px;">
                                        <ul>
                                            <li>
                                                <span style="text-align: center;"><b>CLIENTE</b></br></span>
                                                <span><?php echo $result->nomeCliente ?></br></span>
                                                <?php if (!empty($result->contato_cliente)) : ?>
                                                    <span><?php echo $result->contato_cliente; ?>
                                                <?php endif; ?>
                                                <?php if (!empty($result->celular_cliente) || !empty($result->telefone_cliente)) : ?>
                                                        <span><?php if (!empty($result->telefone_cliente) && $result->celular_cliente != $result->telefone_cliente) : ?><?php echo $result->telefone_cliente; ?> / <?php endif; ?><?php echo $result->celular_cliente; ?></span></br>
                                                <?php endif; ?>
                                                <?php if (!empty($result->email)) : ?>
                                                        <span><?php echo $result->email ?></span><br>
                                                <?php endif; ?>
                                                <?php
                                                    $retorno_end = array_filter([$result->rua, $result->numero, $result->complemento, $result->bairro]);
                                                    $endereco = implode(', ', $retorno_end);
                                                    if (!empty($endereco)) {echo $endereco . '<br>';}
                                                    if (!empty($result->cidade) || !empty($result->estado) || !empty($result->cep)) { echo "<span>{$result->cidade} - {$result->estado}, {$result->cep}</span><br>";}
                                                ?>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top: 0; padding-top: 0; font-size: 12px;">
                        <table class="table table-condensed">
                            <tbody>
                                <tr>
                                    <td><b>Status: </b><?php echo $result->status ?></td>
                                </tr>                                
                            </tbody>
                        </table>
                        <table class="table table-condensed">
                            <tbody>

                                <?php if ($result->dataInicial != null) { ?>
                                    <tr>
                                        <td><b>Inicial: </b><?php echo date('d/m/Y', strtotime($result->dataInicial)); ?></td>
                                        <td><b>Final: </b><?php echo $result->dataFinal ? date('d/m/Y', strtotime($result->dataFinal)) : ''; ?></td>
                                        <td><?php if ($result->garantia != null) { ?><b>Garantia:</b></br><?php echo $result->garantia . ' dia(s)'; ?><?php } ?></td><?php } ?>                                    
                                        <?php if ($result->descricaoProduto != null) { ?>
                                            <tr>
                                                <td colspan="5"><b>Descrição: </b><?php echo htmlspecialchars_decode($result->descricaoProduto) ?></td>
                                            </tr>
                                        <?php } ?>
                                        <?php if ($result->defeito != null) { ?>
                                            <tr>
                                                <td colspan="5"><b>Defeito Apresentado: </b><?php echo htmlspecialchars_decode($result->defeito) ?></td>
                                            </tr>
                                        <?php } ?>
                                <?php if ($result->observacoes != null) { ?>
                                    <tr>
                                        <td colspan="5"><b>Observações: </b><?php echo htmlspecialchars_decode($result->observacoes) ?></td>
                                    </tr>
                                <?php } ?>
                                <?php if ($result->status != 'Aberto') { ?>
                                    <?php if ($result->laudoTecnico != null) { ?>
                                        <tr>
                                            <td colspan="5"><b>Laudo Técnico: </b><?php echo htmlspecialchars_decode($result->laudoTecnico) ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                                <?php if ($result->garantias_id != null) { ?>
                                    <tr>
                                        <td colspan="5">
                                            <strong>Termo de Garantia: </strong><br>
                                            <?php echo htmlspecialchars_decode($result->textoGarantia) ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <?php if ($produtos != null) { ?>
                            <table style='font-size: 11px;' class="table table-bordered table-condensed" id="tblProdutos">
                                <thead>
                                    <tr>
                                        <th>Qtd</th>
                                        <th>Produto</th>
                                        <th>Unitário</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($produtos as $p) {
                                        $totalProdutos = $totalProdutos + $p->subTotal;
                                        echo '<tr>';
                                        echo '<td>' . $p->quantidade . '</td>';
                                        echo '<td>' . $p->descricao . '</td>';
                                        echo '<td>R$ ' . $p->preco ?: $p->precoVenda . '</td>';
                                        echo '<td>R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                                        echo '</tr>';
                                    } ?>

                                    <tr>
                                        <td colspan="3" style="text-align: right"><strong>Total:</strong></td>
                                        <td><strong>R$ <?php echo number_format($totalProdutos, 2, ',', '.'); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php } ?>

                        <?php if ($servicos != null) { ?>
                            <table style='font-size: 11px;' class="table table-bordered table-condensed">
                                <thead>
                                    <tr>
                                        <th>Qtd</th>
                                        <th>Serviço</th>
                                        <th>Unitário</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php setlocale(LC_MONETARY, 'en_US'); foreach ($servicos as $s) {
                                        $preco = $s->preco ?: $s->precoVenda;
                                        $subtotal = $preco * ($s->quantidade ?: 1);
                                        $totalServico = $totalServico + $subtotal;
                                        echo '<tr>';
                                        echo '<td>' . ($s->quantidade ?: 1) . '</td>';
                                        echo '<td>' . $s->nome . '</td>';
                                        echo '<td>R$ ' . $preco . '</td>';
                                        echo '<td>R$ ' . number_format($subtotal, 2, ',', '.') . '</td>';
                                        echo '</tr>';
                                    } ?>
                                    <tr>
                                        <td colspan="3" style="text-align: right"><strong>Total:</strong></td>
                                        <td><strong>R$ <?php echo number_format($totalServico, 2, ',', '.'); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php } ?>

                        <table class="table table-bordered table-condensed">
                            <tbody>
                                <tr>
                                    <td colspan="5"> <?php
                                        if ($totalProdutos != 0 || $totalServico != 0) {
                                            if ($result->valor_desconto != 0) {
                                                echo "<h4 style='text-align: right; font-size: 13px;'>Subtotal: R$ " . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>";
                                                echo $result->valor_desconto != 0 ? "<h4 style='text-align: right; font-size: 13px;'> Desconto: R$ " . number_format($result->valor_desconto != 0 ? $result->valor_desconto - ($totalProdutos + $totalServico) : 0.00, 2, ',', '.') . "</h4>" : "";
                                                echo $result->valor_desconto != 0 ? "<h4 style='text-align: right; font-size: 13px;'> Total: R$ " . number_format($result->valor_desconto, 2, ',', '.') . "</h4>" : "";
                                            } else { echo "<h4 style='text-align: right; font-size: 13px;'>Total: R$ " . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>"; }
                                        } ?>
                                    </td>
                                </tr>
                            </tbody>
                            <?php if ($qrCode) : ?>
                                <td style="width: 15%; padding: 0;text-align:center;">
                                    <img style="margin:12px 0px 0px 0px" src="<?php echo base_url(); ?>assets/img/logo_pix.png" width="64px" alt="QR Code de Pagamento" /></br>
                                    <img style="margin:5px 0px 0px 0px" width="94px" src="<?= $qrCode ?>" alt="QR Code de Pagamento" /></br>
                                    <?php function validarCPF($cpf) {
                                            $cpf = preg_replace('/[^0-9]/', '', $cpf);
                                            if (strlen($cpf) !== 11 || preg_match('/^(\d)\1+$/', $cpf)) {
                                                return false;
                                            }
                                            $soma1 = 0;
                                            for ($i = 0; $i < 9; $i++) {
                                                $soma1 += $cpf[$i] * (10 - $i);
                                            }
                                            $resto1 = $soma1 % 11;
                                            $dv1 = ($resto1 < 2) ? 0 : 11 - $resto1;
                                            if ($dv1 != $cpf[9]) {
                                                return false;
                                            }
                                            $soma2 = 0;
                                            for ($i = 0; $i < 10; $i++) {
                                                $soma2 += $cpf[$i] * (11 - $i);
                                            }
                                            $resto2 = $soma2 % 11;
                                            $dv2 = ($resto2 < 2) ? 0 : 11 - $resto2;

                                            return $dv2 == $cpf[10];
                                        }
                                        function validarCNPJ($cnpj) {
                                            $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
                                            if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1+$/', $cnpj)) {
                                                return false;
                                            }
                                            $soma1 = 0;
                                            for ($i = 0, $pos = 5; $i < 12; $i++, $pos--) {
                                                $pos = ($pos < 2) ? 9 : $pos;
                                                $soma1 += $cnpj[$i] * $pos;
                                            }
                                            $dv1 = ($soma1 % 11 < 2) ? 0 : 11 - ($soma1 % 11);
                                            if ($dv1 != $cnpj[12]) {
                                                return false;
                                            }
                                            $soma2 = 0;
                                            for ($i = 0, $pos = 6; $i < 13; $i++, $pos--) {
                                                $pos = ($pos < 2) ? 9 : $pos;
                                                $soma2 += $cnpj[$i] * $pos;
                                            }
                                            $dv2 = ($soma2 % 11 < 2) ? 0 : 11 - ($soma2 % 11);

                                            return $dv2 == $cnpj[13];
                                        }
                                        function formatarChave($chave) {
                                            if (validarCPF($chave)) {
                                                return substr($chave, 0, 3) . '.' . substr($chave, 3, 3) . '.' . substr($chave, 6, 3) . '-' . substr($chave, 9);
                                            }
                                            elseif (validarCNPJ($chave)) {
                                                return substr($chave, 0, 2) . '.' . substr($chave, 2, 3) . '.' . substr($chave, 5, 3) . '/' . substr($chave, 8, 4) . '-' . substr($chave, 12);
                                            }
                                            elseif (strlen($chave) === 11) {
                                                return '(' . substr($chave, 0, 2) . ') ' . substr($chave, 2, 5) . '-' . substr($chave, 7);
                                            }
                                            return $chave;
                                        }
                                        $chaveOriginal = $this->data['configuration']['pix_key'];
                                        $chaveFormatada = formatarChave($chaveOriginal);
                                        echo '<span style="margin:0px;font-size: 80%;text-align:center;">Chave PIX: ' . $chaveFormatada . '</span><hr>';
                                    ?>
                                </td>
                            <?php endif ?>
                        </table>
                        <table class="table table-bordered table-condensed" style="font-size: 15px">
                            <tbody>
                                <tr>

                                    <td colspan="5">
                                        <b><p class="text-center">Assinatura do Cliente</p></b><br />
                                        <hr>
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                      
                        <!-- Via Da Empresa  -->
                        <?php $totalServico = 0; $totalProdutos = 0; ?>
                    <div id="ViaEmpresa" <?php echo (!$configuration['control_2vias']) ? "style='display: none;'" : "style='display: block;'" ?>>
                        <div class="invoice-head" style="margin-bottom: 0">
                                <table class="table table-condensed">
                                    <tbody>
                                        <?php if ($emitente == null) { ?>
                                            <tr>
                                                <td colspan="5" class="alert">Você precisa configurar os dados do emitente. >>><a href="<?php echo base_url(); ?>index.php/mapos/emitente">Configurar</a><<<</td>
                                            </tr>
                                        <?php } else { ?>
                                            <td style="width: 25% ;text-align: center" ><img src=" <?php echo $emitente->url_logo; ?> " style="max-height: 100px"></td>
                                            <tr>
                                                <td colspan="5" style="text-align: center; font-size: 11px;" >
                                                    <span style="font-size: 12px; text-transform: uppercase"><b><?php echo $emitente->nome; ?></b></br></span>
                                                    <?php if($emitente->cnpj != "00.000.000/0000-00") { ?><span class="icon"><i class="fas fa-fingerprint" style="margin:5px 1px"></i> <?php echo $emitente->cnpj; ?></span></br><?php } ?>
                                                    <span><?php echo $emitente->rua . ', ' . $emitente->numero . ', ' . $emitente->bairro . ', ' . $emitente->cidade . ' - ' . $emitente->uf; ?></span></br>
                                                    <span><?php echo $emitente->email; ?> - <?php echo $emitente->telefone; ?></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center; width: 100%; font-size: 12px;">
                                                    <b>N° OS: </b><span><?php echo $result->idOs ?></span>
                                                    <span style="padding-left: 5%;"><b>Emissão:</b> <?php echo date('d/m/Y') ?></span>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                                <table class="table table-condensend">
                                    <tbody>
                                        <tr>
                                            <td style="width: 50%; padding-left: 0; font-size: 11px;">
                                                <ul>
                                                    <li>
                                                        <span style="text-align: center;"><b>CLIENTE</b></br></span>
                                                        <span><?php echo $result->nomeCliente ?></br></span>
                                                        <?php if (!empty($result->contato_cliente)) : ?>
                                                            <span><?php echo $result->contato_cliente; ?>
                                                        <?php endif; ?>
                                                        <?php if (!empty($result->celular_cliente) || !empty($result->telefone_cliente)) : ?>
                                                                <span><?php if (!empty($result->telefone_cliente) && $result->celular_cliente != $result->telefone_cliente) : ?><?php echo $result->telefone_cliente; ?> / <?php endif; ?><?php echo $result->celular_cliente; ?></span></br>
                                                        <?php endif; ?>
                                                        <?php if (!empty($result->email)) : ?>
                                                                <span><?php echo $result->email ?></span><br>
                                                        <?php endif; ?>
                                                        <?php
                                                            $retorno_end = array_filter([$result->rua, $result->numero, $result->complemento, $result->bairro]);
                                                            $endereco = implode(', ', $retorno_end);
                                                            if (!empty($endereco)) {echo $endereco . '<br>';}
                                                            if (!empty($result->cidade) || !empty($result->estado) || !empty($result->cep)) { echo "<span>{$result->cidade} - {$result->estado}, {$result->cep}</span><br>";}
                                                        ?>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                </div>
                                <div style="margin-top: 0; padding-top: 0">
                                <table class="table table-condensed">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <b>Status: </b>
                                                <?php echo $result->status ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="table table-condensed">
                                    <tbody>
                                        <?php if ($result->dataInicial != null) { ?>
                                            <tr>
                                                <td>
                                                    <b>Inicial: </b>
                                                    <?php echo date('d/m/Y', strtotime($result->dataInicial)); ?>
                                                </td>
                                                <td>
                                                    <b>Final: </b>
                                                    <?php echo $result->dataFinal ? date('d/m/Y', strtotime($result->dataFinal)) : ''; ?>
                                                </td>
                                                <td>
                                                    <?php if ($result->garantia != null) { ?>
                                                        <b>Garantia: </b>
                                                        <?php echo $result->garantia . ' dia(s)'; ?>
                                                    <?php } ?>
                                                </td>
                                            <?php } ?>
                                                <?php if ($result->descricaoProduto != null) { ?>
                                            <tr>
                                                <td colspan="5">
                                                    <b>Descrição: </b>
                                                    <?php echo htmlspecialchars_decode($result->descricaoProduto) ?>
                                                </td>
                                            </tr>
                                        <?php } ?>

                                        <?php if ($result->defeito != null) { ?>
                                            <tr>
                                                <td colspan="5">
                                                    <b>Defeito Apresentado: </b>
                                                    <?php echo htmlspecialchars_decode($result->defeito) ?>
                                                </td>
                                            </tr>
                                        <?php } ?>

                                        <?php if ($result->observacoes != null) { ?>
                                            <tr>
                                                <td colspan="5">
                                                    <b>Observações: </b>
                                                    <?php echo htmlspecialchars_decode($result->observacoes) ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php if ($result->status != 'Aberto') { ?>
                                        <?php if ($result->laudoTecnico != null) { ?>
                                            <tr>
                                                <td colspan="5">
                                                    <b>Laudo Técnico: </b>
                                                    <?php echo htmlspecialchars_decode($result->laudoTecnico) ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if ($result->garantias_id != null) { ?>
                                    <tr>
                                        <td colspan="5">
                                            <strong>Termo de Garantia: </strong><br>
                                            <?php echo htmlspecialchars_decode($result->textoGarantia) ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                                    </tbody>
                                </table>
                                <?php if ($produtos != null) { ?>
                            <table style='font-size: 11px;' class="table table-bordered table-condensed" id="tblProdutos">
                                <thead>
                                    <tr>
                                        <th>Qtd</th>
                                        <th>Produto</th>
                                        <th>Unitário</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($produtos as $p) {
                                        $totalProdutos = $totalProdutos + $p->subTotal;
                                        echo '<tr>';
                                        echo '<td>' . $p->quantidade . '</td>';
                                        echo '<td>' . $p->descricao . '</td>';
                                        echo '<td>R$ ' . $p->preco ?: $p->precoVenda . '</td>';
                                        echo '<td>R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                                        echo '</tr>';
                                    } ?>

                                    <tr>
                                        <td colspan="3" style="text-align: right"><strong>Total:</strong></td>
                                        <td><strong>R$ <?php echo number_format($totalProdutos, 2, ',', '.'); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php } ?>

                        <?php if ($servicos != null) { ?>
                            <table style='font-size: 11px;' class="table table-bordered table-condensed">
                                <thead>
                                    <tr>
                                        <th>Qtd</th>
                                        <th>Serviço</th>
                                        <th>Unitário</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php setlocale(LC_MONETARY, 'en_US'); foreach ($servicos as $s) {
                                        $preco = $s->preco ?: $s->precoVenda;
                                        $subtotal = $preco * ($s->quantidade ?: 1);
                                        $totalServico = $totalServico + $subtotal;
                                        echo '<tr>';
                                        echo '<td>' . ($s->quantidade ?: 1) . '</td>';
                                        echo '<td>' . $s->nome . '</td>';
                                        echo '<td>R$ ' . $preco . '</td>';
                                        echo '<td>R$ ' . number_format($subtotal, 2, ',', '.') . '</td>';
                                        echo '</tr>';
                                    } ?>
                                    <tr>
                                        <td colspan="3" style="text-align: right"><strong>Total:</strong></td>
                                        <td><strong>R$ <?php echo number_format($totalServico, 2, ',', '.'); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php } ?>

                        <table class="table table-bordered table-condensed">
                            <tbody>
                                <tr>
                                    <td colspan="5"> <?php
                                        if ($totalProdutos != 0 || $totalServico != 0) {
                                            if ($result->valor_desconto != 0) {
                                                echo "<h4 style='text-align: right; font-size: 13px;'>Subtotal: R$ " . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>";
                                                echo $result->valor_desconto != 0 ? "<h4 style='text-align: right; font-size: 13px;'> Desconto: R$ " . number_format($result->valor_desconto != 0 ? $result->valor_desconto - ($totalProdutos + $totalServico) : 0.00, 2, ',', '.') . "</h4>" : "";
                                                echo $result->valor_desconto != 0 ? "<h4 style='text-align: right; font-size: 13px;'> Total: R$ " . number_format($result->valor_desconto, 2, ',', '.') . "</h4>" : "";
                                            } else { echo "<h4 style='text-align: right; font-size: 13px;'>Total: R$ " . number_format($totalProdutos + $totalServico, 2, ',', '.') . "</h4>"; }
                                        } ?>
                                    </td>
                                </tr>
                            </tbody>
                            <?php if ($qrCode) : ?>
                                <td style="width: 15%; padding: 0;text-align:center;">
                                    <img style="margin:12px 0px 0px 0px" src="<?php echo base_url(); ?>assets/img/logo_pix.png" width="64px" alt="QR Code de Pagamento" /></br>
                                    <img style="margin:5px 0px 0px 0px" width="94px" src="<?= $qrCode ?>" alt="QR Code de Pagamento" /></br>
                                    <?php 
                                        $chaveOriginal = $this->data['configuration']['pix_key'];
                                        $chaveFormatada = formatarChave($chaveOriginal);
                                        echo '<span style="margin:0px;font-size: 80%;text-align:center;">Chave PIX: ' . $chaveFormatada . '</span><hr>';
                                    ?>
                                </td>
                            <?php endif ?>
                        </table>
                        <table class="table table-bordered table-condensed" style="font-size: 15px">
                            <tbody>
                                <tr>

                                    <td colspan="5">
                                        <b><p class="text-center">Assinatura do Recebedor</p></b><br />
                                        <hr>
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>

    <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/matrix.js"></script>

</html>
