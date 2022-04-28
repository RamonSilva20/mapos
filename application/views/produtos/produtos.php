<div class="new122" style="margin-top: 0; min-height: 100vh">
<div class="flexxn" style="display: flex;">
<?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aProduto')) { ?>
    <a href="<?php echo base_url(); ?>index.php/produtos/adicionar" class="button btn btn-mini btn-success" style="max-width: 160px">
      <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">Add. Produtos</span></a>
    <a href="#modal-etiquetas" role="button" data-toggle="modal" class="button btn btn-mini btn-warning" style="max-width: 160px">
      <span class="button__icon"><i class='bx bx-barcode-reader' ></i></span><span class="button__text2">Gerar Etiquetas</span></a>
</div>

<?php } ?>

<div class="widget-box">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-shopping-bag"></i>
        </span>
        <h5>Produtos</h5>
    </div>
    <div class="widget-content nopadding tab-content">
        <table id="tabela" class="table table-bordered ">
            <thead>
            <tr>
                <th>Cod.</th>
                <th>Cod. Barra</th>
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
                                <td colspan="6">Nenhum Produto Cadastrado</td>
                                </tr>';
            }
            foreach ($results as $r) {
                echo '<tr>';
                echo '<td>' . $r->idProdutos . '</td>';
                echo '<td>' . $r->codDeBarra . '</td>';
                echo '<td>' . $r->descricao . '</td>';
                echo '<td>' . $r->estoque . '</td>';
                echo '<td>' . number_format($r->precoVenda, 2, ',', '.') . '</td>';
                echo '<td>';
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vProduto')) {
                    echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/produtos/visualizar/' . $r->idProdutos . '" class="btn-nwe" title="Visualizar Produto"><i class="bx bx-show bx-xs"></i></a>  ';
                }
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eProduto')) {
                    echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/produtos/editar/' . $r->idProdutos . '" class="btn-nwe3" title="Editar Produto"><i class="bx bx-edit bx-xs"></i></a>';
                }
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dProduto')) {
                    echo '<a style="margin-right: 1%" href="#modal-excluir" role="button" data-toggle="modal" produto="' . $r->idProdutos . '" class="btn-nwe4" title="Excluir Produto"><i class="bx bx-trash-alt bx-xs"></i></a>';
                }
                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eProduto')) {
                    echo '<a href="#atualizar-estoque" role="button" data-toggle="modal" produto="' . $r->idProdutos . '" estoque="' . $r->estoque . '" class="btn-nwe5" title="Atualizar Estoque"><i class="bx bx-plus-circle bx-xs"></i></a>';
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
<div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/produtos/excluir" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel"><i class="fas fa-trash-alt"></i> Excluir Produto</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="idProduto" class="idProduto" name="id" value=""/>
            <h5 style="text-align: center">Deseja realmente excluir este produto?</h5>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
            <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true">
              <span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
            <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-trash'></i></span> <span class="button__text2">Excluir</span></button>
        </div>
    </form>
</div>

<!-- Modal Estoque -->
<div id="atualizar-estoque" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/produtos/atualizar_estoque" method="post" id="formEstoque">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel"><i class="fas fa-plus-square"></i> Atualizar Estoque</h5>
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
        <div class="modal-footer" style="display:flex;justify-content: center">
          <button class="button btn btn-primary"><span class="button__icon"><i class="bx bx-sync"></i></span><span class="button__text2">Atualizar</span></button>
          <button class="button btn btn-warning"  data-dismiss="modal" aria-hidden="true"><span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
        </div>
    </form>
</div>

<!-- Modal Etiquetas -->
<div id="modal-etiquetas" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo base_url() ?>index.php/relatorios/produtosEtiquetas" method="get">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Gerar etiquetas com Código de Barras</h5>
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

                <div class="span6">
                    <label class="span12" for="valor">Formato Etiqueta</label>
                    <select class="span5" name="etiquetaCode">
                        <option value="EAN13">EAN-13</option>
                        <option value="UPCA">UPCA</option>
                        <option value="C93">CODE 93</option>
                        <option value="C128A">CODE 128</option>
                        <option value="CODABAR">CODABAR</option>
                        <option value="QR">QR-CODE</option>
                    </select>
                </div>

            </div>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
          <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true"><span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
          <button class="button btn btn-success"><span class="button__icon"><i class='bx bx-barcode'></i></span><span class="button__text2">Gerar</span></button>
        </div>
    </form>
</div>
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
