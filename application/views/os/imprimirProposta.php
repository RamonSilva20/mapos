<?php
    $totalServico  = 0;
    $totalProdutos = 0;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Proposta Comercial - <?= $result->nomeCliente ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap5.3.2.min.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/font-awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/imprimir.css">
</head>
<body>
    <div class="main-page">
        <div class="sub-page">
            <header>
                <?php if ($emitente == null) : ?>
                    <div class="alert alert-danger" role="alert">
                        Você precisa configurar os dados do emitente. >>> <a href="<?=base_url()?>index.php/mapos/emitente">Configurar</a>
                    </div>
                <?php else : ?>
                    <div class="imgLogo" class="align-middle">
                        <img src="<?= $emitente->url_logo ?>" class="img-fluid" style="width:140px;">
                    </div>
                    <div class="emitente">
                        <span style="font-size: 16px;"><b><?= $emitente->nome ?></b></span></br>
                        <?php if($emitente->cnpj != "00.000.000/0000-00") : ?>
                            <span class="align-middle">CNPJ: <?= $emitente->cnpj ?></span></br>
                        <?php endif; ?>
                        <span class="align-middle">
                            <?= $emitente->rua.', '.$emitente->numero.', '.$emitente->bairro ?><br>
                            <?= $emitente->cidade.' - '.$emitente->uf.' - '.$emitente->cep ?>
                        </span>
                    </div>
                    <div class="contatoEmitente">
                        <span style="font-weight: bold;">Tel: <?= $emitente->telefone ?></span></br>
                        <span style="font-weight: bold;"><?= $emitente->email ?></span></br>
                        <span style="word-break: break-word;">Responsável: <b><?= $result->nome ?></b></span>
                    </div>
                <?php endif; ?>
            </header>
            <section>
                <div class="title">
                    <?php if ($configuration['control_2vias']) : ?><span class="via">Via cliente</span><?php endif; ?>
                    PROPOSTA COMERCIAL #<?= str_pad($result->idOs, 4, 0, STR_PAD_LEFT) ?>
                    <span class="emissao">Emissão: <?= date('d/m/Y H:i:s') ?></span>
                </div>

                <div class="subtitle">DADOS DO CLIENTE</div>
                <div class="dados">
                    <div>
                        <span><b><?= $result->nomeCliente ?></b></span><br />
                        <span>CPF/CNPJ: <?= $result->documento ?></span><br />
                        <span><?= $result->contato_cliente.' '.$result->telefone ?><?= $result->telefone && $result->celular ? ' / '.$result->celular : $result->celular ?></span><br />
                        <span><?= $result->email ?></span><br />
                    </div>
                    <div style="text-align: right;">
                        <span><?= $result->rua.', '.$result->numero.', '.$result->bairro ?></span><br />
                        <span><?= $result->complemento.' - '.$result->cidade.' - '.$result->estado ?></span><br />
                        <span>CEP: <?= $result->cep ?></span><br />
                    </div>
                </div>

                <?php if ($result->descricaoProduto && (isset($result->imprimir_descricao) && $result->imprimir_descricao == 1)) : ?>
                    <div class="subtitle">DESCRIÇÃO DOS SERVIÇOS</div>
                    <div class="dados">
                        <div style="text-align: justify;">
                            <?= htmlspecialchars_decode($result->descricaoProduto) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($result->defeito && isset($result->imprimir_defeito) && $result->imprimir_defeito == 1) : ?>
                    <div class="subtitle">SITUAÇÃO ATUAL / NECESSIDADE</div>
                    <div class="dados">
                        <div style="text-align: justify;">
                            <?= htmlspecialchars_decode($result->defeito) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($result->observacoes && isset($result->imprimir_observacoes) && $result->imprimir_observacoes == 1) : ?>
                    <div class="subtitle">OBSERVAÇÕES</div>
                    <div class="dados">
                        <div style="text-align: justify;">
                            <?= htmlspecialchars_decode($result->observacoes) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($result->laudoTecnico && isset($result->imprimir_laudo) && $result->imprimir_laudo == 1) : ?>
                    <div class="subtitle">SOLUÇÃO PROPOSTA</div>
                    <div class="dados">
                        <div style="text-align: justify;">
                            <?= htmlspecialchars_decode($result->laudoTecnico) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($produtos) : ?>
                    <div class="tabela">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="table-secondary">
                                    <th>PRODUTO(S)</th>
                                    <th class="text-center" width="10%">QTD</th>
                                    <th class="text-center" width="10%">UNT</th>
                                    <th class="text-end" width="15%" >SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos as $p) :
                                    $totalProdutos = $totalProdutos + $p->subTotal;
                                    echo '<tr>';
                                    echo '  <td>' . $p->descricao . '</td>';
                                    echo '  <td class="text-center">' . $p->quantidade . '</td>';
                                    echo '  <td class="text-center">' . number_format($p->preco ?: $p->precoVenda, 2, ',', '.') . '</td>';
                                    echo '  <td class="text-end">R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>';
                                    echo '</tr>';
                                endforeach; ?>
                                <tr>
                                    <td colspan="3" class="text-end"><b>TOTAL PRODUTOS:</b></td>
                                    <td class="text-end"><b>R$ <?= number_format($totalProdutos, 2, ',', '.') ?></b></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <?php if ($servicos) : ?>
                    <div class="tabela">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="table-secondary">
                                    <th>SERVIÇO(S)</th>
                                    <th class="text-center" width="10%">QTD</th>
                                    <th class="text-center" width="10%">UNT</th>
                                    <th class="text-end" width="15%" >SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    setlocale(LC_MONETARY, 'en_US'); 
                                    foreach ($servicos as $s) :
                                        $preco = $s->preco ?: $s->precoVenda;
                                        $subtotal = $preco * ($s->quantidade ?: 1);
                                        $totalServico = $totalServico + $subtotal;
                                        echo '<tr>';
                                        echo '  <td>';
                                        echo '    <strong>' . $s->nome . '</strong>';
                                        if (!empty($s->detalhes)) {
                                            echo '<br><small style="color: #666;">' . htmlspecialchars($s->detalhes) . '</small>';
                                        }
                                        echo '  </td>';
                                        echo '  <td class="text-center">' . ($s->quantidade ?: 1) . '</td>';
                                        echo '  <td class="text-center">' . number_format($preco, 2, ',', '.') . '</td>';
                                        echo '  <td class="text-end">R$ ' . number_format($subtotal, 2, ',', '.') . '</td>';
                                        echo '</tr>';
                                    endforeach; ?>
                                <tr>
                                    <td colspan="3" class="text-end"><b>TOTAL SERVIÇOS:</b></td>
                                    <td class="text-end"><b>R$ <?= number_format($totalServico, 2, ',', '.') ?></b></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if ($totalProdutos != 0 || $totalServico != 0) : ?>
                    <div class="pagamento">
                        <div class="qrcode">
                            <?php if ($this->data['configuration']['pix_key']) : ?>
                                <div><img width="130px" src="<?= $qrCode ?>" alt="QR Code de Pagamento" /></div>
                                <div style="display: flex; flex-wrap: wrap; align-content: center;">
                                    <div style="width: 100%; text-align:center;"><i class="fas fa-camera"></i><br />Escaneie o QRCode ao lado para pagar por Pix</div>
                                    <div class="chavePix">Chave Pix: <b><?= $chaveFormatada ?></b></div>
                                </div>
                            <?php else: ?>
                                <div></div>
                                <div></div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="tabela">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr class="table-secondary">
                                            <th colspan="2">RESUMO DOS VALORES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result->valor_desconto != 0) : ?>
                                            <tr>
                                                <td width="65%">SUBTOTAL</td>
                                                <td>R$ <b><?= number_format($totalProdutos + $totalServico, 2, ',', '.') ?></b></td>
                                            </tr>
                                            <tr>
                                                <td>DESCONTO</td>
                                                <td>R$ <b><?= number_format($result->valor_desconto != 0 ? $result->valor_desconto - ($totalProdutos + $totalServico) : 0.00, 2, ',', '.') ?></b></td>
                                            </tr>
                                            <tr>
                                                <td>TOTAL</td>
                                                <td>R$ <?= number_format($result->valor_desconto, 2, ',', '.') ?></td>
                                            </tr>
                                        <?php else : ?>
                                            <tr>
                                                <td style="width:290px">TOTAL</td>
                                                <td>R$ <?= number_format($totalProdutos + $totalServico, 2, ',', '.') ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($parcelas) && !empty($parcelas)) : ?>
                    <div class="subtitle">CONDIÇÕES DE PAGAMENTO</div>
                    <div class="tabela">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="table-secondary">
                                    <th width="8%">Nº</th>
                                    <th width="12%">Dias</th>
                                    <th width="20%">Valor</th>
                                    <th width="60%">Observação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalParcelas = 0;
                                foreach ($parcelas as $p) :
                                    $totalParcelas += floatval($p->valor);
                                    // Calcular data de vencimento
                                    $dataVencimento = '';
                                    if ($p->data_vencimento) {
                                        $dataVencimento = date('d/m/Y', strtotime($p->data_vencimento));
                                    } elseif ($p->dias > 0 && $result->dataFinal) {
                                        $dataBase = new DateTime($result->dataFinal);
                                        $dataBase->modify('+' . intval($p->dias) . ' days');
                                        $dataVencimento = $dataBase->format('d/m/Y');
                                    }
                                ?>
                                <tr>
                                    <td style="text-align: center;"><?= $p->numero ?></td>
                                    <td style="text-align: center;">
                                        <?= $p->dias ?> dias
                                        <?php if ($dataVencimento): ?>
                                            <br><small style="color: #666;">(<?= $dataVencimento ?>)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">R$ <?= number_format(floatval($p->valor), 2, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($p->observacao ?: '-') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr style="background: #f8f9fa; font-weight: bold;">
                                    <td colspan="2" style="text-align: right;">TOTAL:</td>
                                    <td style="text-align: right;">R$ <?= number_format($totalParcelas, 2, ',', '.') ?></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
            <footer>
                <div class="detalhes">
                    <span>Validade: <b><?= $result->dataFinal ? date('d/m/Y', strtotime($result->dataFinal)) : 'A combinar' ?></b></span>
                    <span>PROPOSTA COMERCIAL</span>
                    <span>Emissão: <b><?= date('d/m/Y') ?></b></span>
                </div>
 
            </footer><br>
            <div class="detalhes">
                    <span>Atencionamente<br>
                    Departamento de Vendas</span>
                </div>
        </div>
    </div>
    <script type="text/javascript">
        window.print();
    </script>
</body>
</html>

