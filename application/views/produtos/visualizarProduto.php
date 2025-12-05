<div class="accordion" id="collapse-group">
    <div class="accordion-group widget-box">
        <div class="accordion-heading">
            <div class="widget-title" style="margin: -20px 0 0">
                <a data-parent="#collapse-group" href="#collapseGOne" data-toggle="collapse">
                    <span class="icon"><i class="fas fa-shopping-bag"></i></span>
                    <h5>Dados do Produto</h5>
                </a>
            </div>
        </div>
        <div class="collapse in accordion-body" id="collapseGOne">
            <div class="widget-content">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td style="text-align: center; width: 30%"><strong>Código de Barra</strong></td>
                            <td>
                                <?php echo $result->codDeBarra ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; width: 30%"><strong>Descrição</strong></td>
                            <td>
                                <?php echo $result->descricao ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right"><strong>Unidade</strong></td>
                            <td>
                                <?php echo $result->unidade ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right"><strong>Preço de Compra</strong></td>
                            <td>R$
                                <?php echo $result->precoCompra; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right"><strong>Preço de Venda</strong></td>
                            <td>R$
                                <?php echo $result->precoVenda; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right"><strong>Estoque</strong></td>
                            <td>
                                <?php echo $result->estoque; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right"><strong>Estoque Mínimo</strong></td>
                            <td>
                                <?php echo $result->estoqueMinimo; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Informações Fiscais -->
    <div class="accordion-group widget-box">
        <div class="accordion-heading">
            <div class="widget-title" style="margin: -20px 0 0">
                <a data-parent="#collapse-group" href="#collapseGTwo" data-toggle="collapse">
                    <span class="icon"><i class="fas fa-file-invoice"></i></span>
                    <h5>Informações Fiscais</h5>
                </a>
            </div>
        </div>
        <div class="collapse accordion-body" id="collapseGTwo">
            <div class="widget-content">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td style="text-align: right; width: 30%"><strong>NCM</strong></td>
                            <td>
                                <?php echo isset($result->ncm) && !empty($result->ncm) ? $result->ncm : '<span style="color: #999;">Não informado</span>'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right"><strong>CEST</strong></td>
                            <td>
                                <?php echo isset($result->cest) && !empty($result->cest) ? $result->cest : '<span style="color: #999;">Não informado</span>'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right"><strong>CFOP</strong></td>
                            <td>
                                <?php echo isset($result->cfop) && !empty($result->cfop) ? $result->cfop : '<span style="color: #999;">Não informado</span>'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right"><strong>Origem</strong></td>
                            <td>
                                <?php 
                                $origens = [
                                    '0' => '0 - Nacional',
                                    '1' => '1 - Estrangeira - Importação direta',
                                    '2' => '2 - Estrangeira - Adquirida no mercado interno',
                                    '3' => '3 - Nacional - Mercadoria com mais de 40% de conteúdo estrangeiro',
                                    '4' => '4 - Nacional - Produção em conformidade com processos produtivos básicos',
                                    '5' => '5 - Nacional - Mercadoria com menos de 40% de conteúdo estrangeiro',
                                    '6' => '6 - Estrangeira - Importação direta sem similar nacional',
                                    '7' => '7 - Estrangeira - Adquirida no mercado interno sem similar nacional',
                                    '8' => '8 - Nacional - Mercadoria com mais de 70% de conteúdo estrangeiro'
                                ];
                                $origem = isset($result->origem) ? $result->origem : '0';
                                echo isset($origens[$origem]) ? $origens[$origem] : $origem;
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right"><strong>Tributação ICMS</strong></td>
                            <td>
                                <?php 
                                $tributacoes = [
                                    '00' => '00 - Tributada integralmente',
                                    '10' => '10 - Tributada com cobrança de ICMS por substituição tributária',
                                    '20' => '20 - Com redução de base de cálculo',
                                    '30' => '30 - Isenta ou não tributada com cobrança de ICMS por substituição tributária',
                                    '40' => '40 - Isenta',
                                    '41' => '41 - Não tributada',
                                    '50' => '50 - Suspensa',
                                    '51' => '51 - Diferimento',
                                    '60' => '60 - ICMS cobrado anteriormente por substituição tributária',
                                    '70' => '70 - Com redução de base de cálculo e cobrança de ICMS por substituição tributária',
                                    '90' => '90 - Outras'
                                ];
                                $tributacao = isset($result->tributacao) ? $result->tributacao : '';
                                echo !empty($tributacao) && isset($tributacoes[$tributacao]) ? $tributacoes[$tributacao] : '<span style="color: #999;">Não informado</span>';
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
