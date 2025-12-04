<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-upload"></i>
                </span>
                <h5>Importação em Massa de Produtos</h5>
            </div>
            <div class="widget-content nopadding tab-content">
                <div class="span12 alert alert-info" style="margin-left: 0; margin-top: 20px;">
                    <h4><i class="icon-info-sign"></i> Instruções para Importação</h4>
                    <ol>
                        <li>Baixe o arquivo modelo CSV clicando no botão abaixo</li>
                        <li>Preencha o arquivo com os dados dos produtos seguindo o formato do modelo</li>
                        <li>Salve o arquivo em formato CSV (separado por vírgulas)</li>
                        <li>Faça o upload do arquivo preenchido usando o formulário abaixo</li>
                    </ol>
                    <p><strong>Campos obrigatórios:</strong> Descrição, Preço de Venda</p>
                    <p><strong>Campos opcionais:</strong> Código de Barras, Unidade (padrão: UN), Preço de Compra, Estoque, Estoque Mínimo, Entrada, Saída</p>
                    <p><strong>Campos para Nota Fiscal (opcionais):</strong> NCM, CEST, CFOP, Origem, Tributação ICMS</p>
                </div>

                <div class="span12" style="margin-left: 0; margin-top: 20px;">
                    <div class="span6" style="margin-left: 0;">
                        <a href="<?= base_url() ?>index.php/produtos/downloadModelo" class="button btn btn-mini btn-info" style="max-width: 300px;">
                            <span class="button__icon"><i class='bx bx-download'></i></span>
                            <span class="button__text2">Baixar Arquivo Modelo CSV</span>
                        </a>
                    </div>
                </div>

                <form action="<?= base_url() ?>index.php/produtos/processarImportacao" method="post" enctype="multipart/form-data" class="form-horizontal" style="margin-top: 30px;">
                    <div class="control-group">
                        <label for="arquivo_csv" class="control-label">Selecione o arquivo CSV<span class="required">*</span></label>
                        <div class="controls">
                            <input type="file" id="arquivo_csv" name="arquivo_csv" accept=".csv" required />
                            <span class="help-block">Apenas arquivos CSV são aceitos. Tamanho máximo: 10MB</span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <div class="span12">
                            <div class="span6 offset3" style="display: flex; justify-content: center; gap: 10px;">
                                <button type="submit" class="button btn btn-mini btn-success" style="max-width: 160px">
                                    <span class="button__icon"><i class='bx bx-upload'></i></span>
                                    <span class="button__text2">Importar Produtos</span>
                                </button>
                                <a href="<?= base_url() ?>index.php/produtos" class="button btn btn-mini btn-warning">
                                    <span class="button__icon"><i class="bx bx-undo"></i></span>
                                    <span class="button__text2">Voltar</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="span12" style="margin-left: 0; margin-top: 30px;">
                    <div class="alert alert-warning">
                        <h5><i class="icon-warning-sign"></i> Formato do Arquivo CSV</h5>
                        <p>O arquivo deve conter as seguintes colunas na ordem especificada:</p>
                        <table class="table table-bordered" style="margin-top: 10px;">
                            <thead>
                                <tr>
                                    <th>Coluna</th>
                                    <th>Descrição</th>
                                    <th>Obrigatório</th>
                                    <th>Exemplo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Código de Barras</td>
                                    <td>Não</td>
                                    <td>7891234567890</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Descrição</td>
                                    <td><strong>Sim</strong></td>
                                    <td>Produto Exemplo</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>Unidade</td>
                                    <td>Não (padrão: UN)</td>
                                    <td>UN, KG, LT, etc.</td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>Preço de Compra</td>
                                    <td>Não</td>
                                    <td>10.50</td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>Preço de Venda</td>
                                    <td><strong>Sim</strong></td>
                                    <td>25.90</td>
                                </tr>
                                <tr>
                                    <td>6</td>
                                    <td>Estoque</td>
                                    <td>Não (padrão: 0)</td>
                                    <td>100</td>
                                </tr>
                                <tr>
                                    <td>7</td>
                                    <td>Estoque Mínimo</td>
                                    <td>Não</td>
                                    <td>10</td>
                                </tr>
                                <tr>
                                    <td>8</td>
                                    <td>Entrada (Sim/Não)</td>
                                    <td>Não (padrão: Sim)</td>
                                    <td>Sim</td>
                                </tr>
                                <tr>
                                    <td>9</td>
                                    <td>Saída (Sim/Não)</td>
                                    <td>Não (padrão: Sim)</td>
                                    <td>Sim</td>
                                </tr>
                                <tr>
                                    <td>10</td>
                                    <td>NCM</td>
                                    <td>Não</td>
                                    <td>12345678</td>
                                </tr>
                                <tr>
                                    <td>11</td>
                                    <td>CEST</td>
                                    <td>Não</td>
                                    <td>1234567</td>
                                </tr>
                                <tr>
                                    <td>12</td>
                                    <td>CFOP</td>
                                    <td>Não</td>
                                    <td>5102</td>
                                </tr>
                                <tr>
                                    <td>13</td>
                                    <td>Origem (0-Nacional, 1-Estrangeira)</td>
                                    <td>Não (padrão: 0)</td>
                                    <td>0</td>
                                </tr>
                                <tr>
                                    <td>14</td>
                                    <td>Tributação ICMS</td>
                                    <td>Não</td>
                                    <td>00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
