<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aProduto')) { ?>
    <a href="<?php echo base_url(); ?>index.php/produtos/adicionar" class="btn btn-success"><i class="fas fa-plus"></i> Adicionar Produto</a>
    
    <a href="#modal-etiquetas" role="button" data-toggle="modal" class="btn btn-success span2" style="float: right;">
        <i class="fas fa-barcode"></i> Gerar Etiquetas Cod. Barra</a>
    
    <a href="#modal-etiquetas_sku" role="button" data-toggle="modal" class="btn btn-success span2" style="float: right;">
        <i class="fas fa-barcode"></i> Gerar Etiquetas Cod. Produto</a>
        
        <a href="#modal-etiquetas-qr" role="button" data-toggle="modal" class="btn btn-success span2" style="float: right;">
        <i class="fas fa-barcode"></i> Gerar Etiquetas Cod. QR</a>

<?php } ?>

<div class="widget-box">
    <div class="widget-title">
    <span class="icon"><i class="fas fa-shopping-bag"></i></span>
    <h5>Produtos</h5>
    </div>
    <div class="widget-content nopadding">
    <!--
    <div class="widget_box_Painel2">
    -->
        <table id="tabela" width="100%" class="table_p">
            <thead>
            <tr>
                <th>Cod. Produto</th>
                <th>Cod. de Barra</th>
                <th>Nome</th>
                <th>Estoque</th>
                <th>Preço</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php

            if (!$results) {
                echo '<tr>
                                <td colspan="5">Nenhum Produto Cadastrado</td>
                                </tr>';
            }
            foreach ($results as $r) {
                echo '<tr>';
                echo '<td><div align="center">' . $r->idProdutos . '</td>';
                echo '<td><div align="center">' . $r->codDeBarra . '</td>';
                echo '<td>' . $r->descricao . '</td>';
                echo '<td><div align="center">' . $r->estoque . '</td>';
                echo '<td><div align="center">' . number_format($r->precoVenda, 2, ',', '.') . '</td>';
                echo '<td><div align="center">';
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vProduto')) {
                    echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/produtos/visualizar/' . $r->idProdutos . '" class="btn tip-top" title="Visualizar Produto"><i class="fas fa-eye"></i></a>  ';
                }
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eProduto')) {
                    echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/produtos/editar/' . $r->idProdutos . '" class="btn btn-info tip-top" title="Editar Produto"><i class="fas fa-edit"></i></a>';
                }
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dProduto')) {
                    echo '<a style="margin-right: 1%" href="#modal-excluir" role="button" data-toggle="modal" produto="' . $r->idProdutos . '" class="btn btn-danger tip-top" title="Excluir Produto"><i class="fas fa-trash-alt"></i></a>';
                }
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eProduto')) {
                    echo '<a href="#atualizar-estoque" role="button" data-toggle="modal" produto="' . $r->idProdutos . '" estoque="' . $r->estoque . '" class="btn btn-primary tip-top" title="Atualizar Estoque"><i class="fas fa-plus-square"></i></a>';
                }
                echo '</td>';
                echo '</tr>';
            } ?>
            </tbody>
        </table>
    </div>
</div>
<?php echo $this->pagination->create_links(); ?>

<!-- Modal -->
<div id="modal-excluir" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/produtos/excluir" method="post">
        <div class="modal_header_anexos">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel"><i class="fas fa-trash-alt"></i> Excluir Produto</h3>
        </div>
        <div class="modal-body">
            <input type="hidden" id="idProduto" class="idProduto" name="id" value=""/>
            <h5 style="text-align: center">Deseja realmente excluir este produto?</h5>
        </div>
        <div class="modal-footer">
            <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button class="btn btn-danger">Excluir</button>
        </div>
    </form>
</div>

<!-- Modal Estoque -->
<div id="atualizar-estoque" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/produtos/atualizar_estoque" method="post" id="formEstoque">
        <div class="modal_header_anexos">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel"><i class="fas fa-plus-square"></i> Atualizar Estoque</h3>
        </div>
        <div class="modal-body">
            <div class="control-group">
                <label for="estoqueAtual" class="control-label">Estoque Atual</label>
                <div class="controls">
                    <input id="estoqueAtual" type="text" name="estoqueAtual" value="" readonly />
                </div>
            </div>

            <div class="control-group">
                <label for="estoque" class="control-label">Adicionar Produtos<span class="required">*</span></label>
                <div class="controls">
                    <input type="hidden" id="idProduto" class="idProduto" name="id" value=""/>
                    <input id="estoque" type="text" name="estoque" value=""/>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button class="btn btn-primary">Atualizar</button>
        </div>
    </form>
</div>

<!-- Modal Etiquetas Cod. Barras -->
<div id="modal-etiquetas" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/relatorios/produtosEtiquetas" method="get">
        <div class="modal_header_anexos">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Gerar etiquetas com Código de Barras</h3>
        </div>
        <div class="modal-body">
            <div class="span12 alert alert-info" style="margin-left: 0"> Escolha o intervalo de produtos para gerar as etiquetas.</div>

            <div class="span12" style="margin-left: 0;">
                <div class="span6" style="margin-left: 0;">
                    <label for="valor">De</label>
                    <input class="span9" style="margin-left: 0" type="text" id="de_id" name="de_id" placeholder="ID do primeiro produto" value=""/>
                </div>


                <div class="span6">
                    <label for="valor">Até</label>
                    <input class="span9" type="text" id="ate_id" name="ate_id" placeholder="ID do último produto" value=""/>
                </div>

                <div class="span4">
                    <label for="valor">Qtd. do Estoque</label>
                    <input class="span12" type="checkbox" name="qtdEtiqueta" value="true"/>
                </div>

                <div class="hide">
                    <label class="span12" for="valor">Formato Etiqueta</label>
                    <select name="etiquetaCode">
                    	<option value="UPCA">UPCA</option>
                        <option value="EAN13">EAN-13</option>
                        <option value="C93">CODE 93</option>
                        <option value="C128A">CODE 128 A</option>
                        <option value="C128B">CODE 128 B</option>
                        <option value="C128C">CODE 128 C</option>
                        <option value="CODABAR">CODABAR</option>
                        <option value="QR">QR-CODE</option>
                    </select>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button class="btn btn-success">Gerar</button>
        </div>
    </form>
</div>

<script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>

<!-- Modal Etiquetas Cod. SKU-->
<div id="modal-etiquetas_sku" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/relatorios/produtosEtiquetasSKU" method="get">
        <div class="modal_header_anexos">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Gerar etiquetas com Cód. Produto</h3>
        </div>
        <div class="modal-body">
            <div class="span12 alert alert-info" style="margin-left: 0"> Escolha o intervalo de produtos para gerar as etiquetas.</div>

            <div class="span12" style="margin-left: 0;">
                <div class="span6" style="margin-left: 0;">
                    <label for="valor">De</label>
                    <input class="span9" style="margin-left: 0" type="text" id="de_id" name="de_id" placeholder="ID do primeiro produto" value=""/>
                </div>

                <div class="span6">
                    <label for="valor">Até</label>
                    <input class="span9" type="text" id="ate_id" name="ate_id" placeholder="ID do último produto" value=""/>
                </div>
                
                <div class="span4">
                    <label for="valor">Qtd. do Estoque</label>
                    <input class="span12" type="checkbox" name="qtdEtiqueta" value="true"/>
                </div>

                <div class="hide">
                    <label class="span12" for="valor">Formato Etiqueta</label>
                    <select name="etiquetaCode">
                    	<option value="C128B">CODE 128 B</option>
                    </select>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button class="btn btn-success">Gerar</button>
        </div>
    </form>
</div>

<script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>

<!-- Modal Etiquetas Cod. QR-->
<div id="modal-etiquetas-qr" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/relatorios/produtosEtiquetasQR" method="get">
        <div class="modal_header_anexos">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Gerar etiquetas com Código QR</h3>
        </div>
        <div class="modal-body">
            <div class="span12 alert alert-info" style="margin-left: 0"> Escolha o intervalo de produtos para gerar as etiquetas.</div>

            <div class="span12" style="margin-left: 0;">
                <div class="span6" style="margin-left: 0;">
                    <label for="valor">De</label>
                    <input class="span9" style="margin-left: 0" type="text" id="de_id" name="de_id" placeholder="ID do primeiro produto" value=""/>
                </div>

                <div class="span6">
                    <label for="valor">Até</label>
                    <input class="span9" type="text" id="ate_id" name="ate_id" placeholder="ID do último produto" value=""/>
                </div>
                
                <div class="span4">
                    <label for="valor">Qtd. do Estoque</label>
                    <input class="span12" type="checkbox" name="qtdEtiqueta" value="true"/>
                </div>

                <div class="hide">
                    <label class="span12" for="valor">Formato Etiqueta</label>
                    <select name="etiquetaCode">
                    <option value="QR">QR-CODE</option>
                    </select>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button class="btn btn-success">Gerar</button>
        </div>
    </form>
</div>

<script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>

<!-- Modal Etiquetas e Estoque-->
<script type="text/javascript">
    $(document).ready(function () {
        $(document).on('click', 'a', function (event) {
            var produto = $(this).attr('produto');
            var estoque = $(this).attr('estoque');
            $('.idProduto').val(produto);
            $('#estoqueAtual').val(estoque);
        });

        $('#formEstoque').validate({
            rules: {
                estoque: {
                    required: true,
                    number: true
                }
            },
            messages: {
                estoque: {
                    required: 'Campo Requerido.',
                    number: 'Informe um número válido.'
                }
            },
            errorClass: "help-inline",
            errorElement: "span",
            highlight: function (element, errorClass, validClass) {
                $(element).parents('.control-group').addClass('error');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error');
                $(element).parents('.control-group').addClass('success');
            }
        });
    });
</script>