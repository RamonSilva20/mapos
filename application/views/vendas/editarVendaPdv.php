<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>


<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <!-- <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-cash-register"></i>
                </span>
                <h5>Editar Venda PDV</h5>
            </div>
-->
            <div class="widget-content nopadding tab-content">
                <div class="span12" id="divProdutosServicos" style=" margin-left: 0">
                    <!--
                         <ul class="nav nav-tabs">
                        <li class="active" id="tabDetalhes"><a href="#tab1" data-toggle="tab">Detalhes da Venda</a></li>
                    </ul>
-->
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab1">
                            <div class="span12" id="divEditarVenda">
                                <form action="<?php echo current_url(); ?>" method="post" id="formVendas">
                                    <?php echo form_hidden('idVendas', $result->idVendas) ?>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <h3>#Venda:
                                            <?php echo $result->idVendas ?>
                                        </h3>
                                        <div class="span2" style="margin-left: 0">
                                            <label for="dataFinal">Data Final</label>
                                            <input id="dataVenda" class="span12 datepicker" type="text" name="dataVenda" value="<?php echo date('d/m/Y', strtotime($result->dataVenda)); ?>" />
                                        </div>
                                        <div class="span5">
                                            <label for="cliente">Cliente<span class="required">*</span></label>
                                            <input id="cliente" class="span12" type="text" name="cliente" value="<?php echo $result->nomeCliente ?>" />
                                            <input id="clientes_id" class="span12" type="hidden" name="clientes_id" value="<?php echo $result->clientes_id ?>" />
                                            <input id="valorTotal" type="hidden" name="valorTotal" value="" />
                                        </div>
                                        <div class="span5">
                                            <label for="tecnico">Vendedor<span class="required">*</span></label>
                                            <input id="tecnico" class="span12" type="text" name="tecnico" value="<?php echo $result->nome ?>" />
                                            <input id="usuarios_id" class="span12" type="hidden" name="usuarios_id" value="<?php echo $result->usuarios_id ?>" />
                                        </div>
                                    </div>
                                    <div class="span12" style="padding: 1%; margin-left: 0">
                                        <div class="span8 offset2" style="text-align: center">
                                            <?php if ($result->faturado == 0) { ?>
                                                <a href="#modal-faturar" id="btn-faturar" role="button" data-toggle="modal" class="btn btn-success"><i class="fas fa-cash-register"></i> Faturar (F11)</a>
                                            <?php
                                            } else {
                                                //redireciona caso a venda seja faturada com sucesso
                                                redirect(base_url() . "index.php/pdv");
                                            } ?>
                                            <button class="btn btn-primary" id="btnContinuar"><i class="fas fa-sync-alt"></i> Atualizar</button>
                                            <a href="<?php echo base_url() ?>index.php/vendas/visualizar/<?php echo $result->idVendas; ?>" class="btn btn-inverse"><i class="fas fa-eye"></i> Visualizar Venda</a>
                                            <a href="<?php echo base_url() ?>index.php/pdv" class="btn"><i class="fas fa-backward"></i> Voltar</a>
                                        </div>
                                    </div>
                                </form>
                                <div class="span12 well" style="padding: 1%; margin-left: 0">
                                    <form id="formProdutos" action="<?php echo base_url(); ?>index.php/vendas/adicionarProduto" method="post">
                                        <div class="span6">
                                            <input type="hidden" name="idProduto" id="idProduto" />
                                            <input type="hidden" name="idVendasProduto" id="idVendasProduto" value="<?php echo $result->idVendas ?>" />
                                            <input type="hidden" name="estoque" id="estoque" value="" />
                                            <label for="">Produto</label>
                                            <input type="text" class="span12" name="produto" id="produto" placeholder="Digite o nome ou cod do produto" autofocus />
                                        </div>
                                        <div class="span2">
                                            <label for="">Preço</label>
                                            <input type="text" placeholder="Preço" id="preco" name="preco" class="span12 money" />
                                        </div>
                                        <div class="span2">
                                            <label for="">Quantidade</label>
                                            <input type="text" placeholder="Quantidade" id="quantidade" name="quantidade" class="span12" value="1" />
                                        </div>
                                        <div class="span2">
                                            <label for="">&nbsp</label>
                                            <button class="btn btn-success span12" id="btnAdicionarProduto"><i class="fas fa-plus"></i> Adicionar</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="span12" id="divProdutos" style="margin-left: 0">
                                    <table class="table table-bordered" id="tblProdutos">
                                        <thead>
                                            <tr>
                                                <th>id</th>
                                                <th>Produto</th>
                                                <th>Quantidade</th>
                                                <th>Preço</th>
                                                <th>Ações</th>
                                                <th>Sub-total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $total = 0;
                                            foreach ($produtos as $p) {
                                                $preco = $p->preco ?: $p->precoVenda;
                                                $total = $total + $p->subTotal;
                                                echo '<tr>'."\n\r";
                                                echo '<td>' . $p->idProdutos . '</td>'."\n\r";
                                                echo '<td>' . $p->descricao . '</td>'."\n\r";
                                                echo '<td>' . $p->quantidade . '</td>'."\n\r";
                                                echo '<td>' . $preco . '</td>'."\n\r";
                                                echo '<td><a href="" idAcao="' . $p->idItens . '" prodAcao="' . $p->idProdutos . '" quantAcao="' . $p->quantidade . '" title="Excluir Produto" class="btn btn-danger"><i class="fas fa-trash-alt"></i></a></td>'."\n\r";
                                                echo '<td>R$ ' . number_format($p->subTotal, 2, ',', '.') . '</td>'."\n\r";
                                                echo '</tr>'."\n\r";
                                            } ?>
                                            <tr>
                                                <td colspan="4" style="text-align: right"><strong>Total:</strong></td>
                                                <td><strong>R$
                                                        <?php echo number_format($total, 2, ',', '.'); ?></strong> <input type="hidden" id="total-venda" value="<?php echo number_format($total, 2); ?>"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                &nbsp
            </div>
        </div>
    </div>
</div>

<!-- Modal Faturar-->
<div id="modal-faturar" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form id="formFaturar" action="<?php echo current_url() ?>" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Faturar Venda</h3>
        </div>
        <div class="modal-body">
            <div class="span12 alert alert-info" style="margin-left: 0"> Obrigatório o preenchimento dos campos com asterisco.</div>
            <div class="span12" style="margin-left: 0">
                <label for="descricao">Descrição</label>
                <input class="span12" id="descricao" type="text" name="descricao" value="Fatura de Venda PDV- #<?php echo $result->idVendas; ?> " />
            </div>
            <div class="span12" style="margin-left: 0">
                <div class="span12" style="margin-left: 0">
                    <label for="cliente">Cliente*</label>
                    <input class="span12" id="cliente" type="text" name="cliente" value="<?php echo $result->nomeCliente ?>" />
                    <input type="hidden" name="clientes_id" id="clientes_id" value="<?php echo $result->clientes_id ?>">
                    <input type="hidden" name="vendas_id" id="vendas_id" value="<?php echo $result->idVendas; ?>">
                </div>
            </div>
            <div class="span12" style="margin-left: 0">
                <div class="span4" style="margin-left: 0">
                    <label for="valor">Valor*</label>
                    <input type="hidden" id="tipo" name="tipo" value="receita" />
                    <input class="span12 money" id="valor" type="text" name="valor" value="<?php echo number_format($total, 2); ?> " />
                </div>
                <div class="span4">
                    <label for="vencimento">Data Vencimento*</label>
                    <input class="span12 datepicker" autocomplete="on" id="vencimento" type="text" name="vencimento" value="<?php echo date('d/m/Y'); ?>" />
                </div>
            </div>
            <div class="span12" style="margin-left: 0">
                <div class="span4" style="margin-left: 0">
                    <label for="recebido">Recebido?</label>
                    &nbsp &nbsp &nbsp &nbsp<input id="recebido" type="checkbox" name="recebido" value="1" checked />
                </div>
                <div id="divRecebimento" class="span8" style=" display: none">
                    <div class="span6">
                        <label for="recebimento">Data Recebimento</label>
                        <input class="span12 datepicker" autocomplete="on" id="recebimento" type="text" name="recebimento" value="<?php echo date('d/m/Y'); ?>" />
                    </div>
                    <div class="span6">
                        <label for="formaPgto">Forma Pgto</label>
                        <select name="formaPgto" id="formaPgto" class="span12">
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Cartão de Crédito">Cartão de Crédito</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Boleto">Boleto</option>
                            <option value="Depósito">Depósito</option>
                            <option value="Débito">Débito</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true" id="btn-cancelar-faturar">Cancelar</button>
            <button class="btn btn-primary" id="btn-faturar-confirmar">Faturar (F12)</button>
        </div>
    </form>
</div>
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<script src="<?php echo base_url(); ?>assets/js/maskmoney.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        //faturar pelo teclado
        shortcut.add("F11", function() {
            $("#btn-faturar").trigger('click');

        });
        shortcut.add("F12", function() {
            $("#btn-faturar-confirmar").trigger('click');

        });
        shortcut.add("Ctrl+Shift+Z", function() {

            //window.alert("!")

        });


        $('#divRecebimento').show();
        $(".money").maskMoney();
        $('#recebido').click(function(event) {

            var flag = $(this).is(':checked');
            if (flag == false) {
                $('#divRecebimento').hide();

            } else {
                $('#divRecebimento').show();
            }
        });
        $(document).on('click', '#btn-faturar', function(event) {
            event.preventDefault();
            valor = $('#total-venda').val();
            valor = valor.replace(',', '');
            $('#valor').val(valor);
        });
        $("#formFaturar").validate({
            rules: {
                descricao: {
                    required: true
                },
                cliente: {
                    required: true
                },
                valor: {
                    required: true
                },
                vencimento: {
                    required: true
                }
            },
            messages: {
                descricao: {
                    required: 'Campo Requerido.'
                },
                cliente: {
                    required: 'Campo Requerido.'
                },
                valor: {
                    required: 'Campo Requerido.'
                },
                vencimento: {
                    required: 'Campo Requerido.'
                }
            },
            submitHandler: function(form) {
                var dados = $(form).serialize();
                $('#btn-cancelar-faturar').trigger('click');
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/vendas/faturar",
                    data: dados,
                    dataType: 'json',
                    success: function(data) {
                        if (data.result == true) {
                            //chamar a folha de impressão aqui
                            window.open("<?php echo site_url() ?>/pdv/imprimirTermica/<?php echo $result->idVendas; ?>", "Venda <?php echo $result->idVendas; ?>", "_self", "directories=0,titlebar=0,toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=1,width=300,heigth=600")

                            window.location.reload(true);
                        } else {
                            Swal.fire({
                                type: "error",
                                title: "Atenção",
                                text: "Ocorreu um erro ao tentar faturar venda."
                            });
                            $('#progress-fatura').hide();
                        }
                    }
                });
                return false;
            }
        });


        $("#produto").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteProdutoSaida",
            minLength: 3,
            response: function(event, ui) {
                //window.alert(ui.content.length)
                //window.alert(ui.content[0].estoque )
                if (ui.content.length == 1) {
                    // $(this).val(ui.content[0].value);
                    $("#idProduto").val(ui.content[0].id);
                    $("#estoque").val(ui.content[0].estoque);
                    $("#preco").val(ui.content[0].preco);
                    $("#quantidade").val(1);
                    //vai automaticamente para o botao
                    //$("#btnAdicionarProduto").focus();
                    //checa se o produto ja esta na lista e atualiza a quantidade
                    if (document.getElementById('tblProdutos').rows.length > 2) {
                        buscaProdutoInserido(ui.content[0].id)
                    } else {
                        
                        $("#btnAdicionarProduto").focus();
                        $("#btnAdicionarProduto").trigger('click')
                        //limpa o cache da pesquisa
                        // $(this).data().autocomplete.term = null;
                    }
                    $(this).data().autocomplete.term = null;
                    //simula uma entrada automatica, verificar compatibilidade com leitor de cod barras
                    //$("#btnAdicionarProduto").trigger('click');
                }if(ui.content.length >= 1){
                    
                }

            },
            select: function(event, ui) {

                $("#idProduto").val(ui.item.id);
                $("#estoque").val(ui.item.estoque);
                $("#preco").val(ui.item.preco);
                $("#btnAdicionarProduto").focus();
                $("#btnAdicionarProduto").trigger('click');

            }


        });
        $("#cliente").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteCliente",
            minLength: 2,
            select: function(event, ui) {
                $("#clientes_id").val(ui.item.id);
            }
        });
        $("#tecnico").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteUsuario",
            minLength: 2,
            select: function(event, ui) {
                $("#usuarios_id").val(ui.item.id);
            }
        });
        $("#formVendas").validate({
            rules: {
                cliente: {
                    required: true
                },
                tecnico: {
                    required: true
                },
                dataVenda: {
                    required: true
                }
            },
            messages: {
                cliente: {
                    required: 'Campo Requerido.'
                },
                tecnico: {
                    required: 'Campo Requerido.'
                },
                dataVenda: {
                    required: 'Campo Requerido.'
                }
            },
            errorClass: "help-inline",
            errorElement: "span",
            highlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').addClass('error');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error');
                $(element).parents('.control-group').addClass('success');
            }
        });
        $("#formProdutos").validate({
            rules: {
                quantidade: {
                    required: true
                }
            },
            messages: {
                quantidade: {
                    required: 'Insira a quantidade'
                }
            },
            submitHandler: function(form) {
                var quantidade = parseInt($("#quantidade").val());
                var estoque = parseInt($("#estoque").val());

                <?php if (!$configuration['control_estoque']) {
                    echo 'estoque = 1000000';
                }; ?>

                if (estoque < quantidade) {
                    Swal.fire({
                        type: "warning",
                        title: "Atenção",
                        text: "Você não possui estoque suficiente."
                    });
                } else {
                    var dados = $(form).serialize();
                    $("#divProdutos").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                    $.ajax({
                        type: "POST",
                        url: "<?php echo base_url(); ?>index.php/vendas/adicionarProduto",
                        data: dados,
                        dataType: 'json',
                        success: function(data) {
                            if (data.result == true) {
                                $("#divProdutos").load("<?php echo current_url(); ?> #divProdutos");
                                $("#quantidade").val('');
                                $("#preco").val('');

                                $("#produto").val('').focus();




                            } else {
                                Swal.fire({
                                    type: "error",
                                    title: "Atenção",
                                    text: "Ocorreu um erro ao tentar adicionar produto."
                                });
                            }

                        },

                    });
                    return false;
                }
            }
        });
        $(document).on('click', 'a', function(event) {
            var idProduto = $(this).attr('idAcao');
            var quantidade = $(this).attr('quantAcao');
            var produto = $(this).attr('prodAcao');
            if ((idProduto % 1) == 0) {
                $("#divProdutos").html("<div class='progress progress-info progress-striped active'><div class='bar' style='width: 100%'></div></div>");
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>index.php/vendas/excluirProduto",
                    data: "idProduto=" + idProduto + "&quantidade=" + quantidade + "&produto=" + produto,
                    dataType: 'json',
                    success: function(data) {
                        if (data.result == true) {
                            $("#divProdutos").load("<?php echo current_url(); ?> #divProdutos");
                        } else {
                            Swal.fire({
                                type: "error",
                                title: "Atenção",
                                text: "Ocorreu um erro ao tentar excluir produto."
                            });
                        }
                    }
                });
                return false;
            }
        });
        $(".datepicker").datepicker({
            dateFormat: 'dd/mm/yy'
        });
    });

    function searchExistentProduct(idProduto) {
        var searchValue = idProduto
        var searchTable = document.getElementById('tblProdutos'); //Search Value In Table search Table by Id 
        var searchColCount; //Column Counetr
        //Loop through table rows
        for (var rowIndex = 0; rowIndex < searchTable.rows.length; rowIndex++) {

            var rowData = '';
           
            //Get column count from header row
            if (rowIndex == 0) {
                searchColCount = searchTable.rows.item(rowIndex).cells.length;
                continue; //do not execute further code for header row.
            }
            //Process data rows. (rowIndex >= 1)
            for (var colIndex = 0; colIndex < searchColCount; colIndex++) {
                rowData += searchTable.rows.item(rowIndex).cells.item(colIndex).textContent;

            }

            //If search term is not found in row data
            //then hide the row, else show

            if (rowData.indexOf(searchValue) > -1) {
               // window.alert("achei")
                console.log
                //$("#btnAdicionarProduto").trigger('click')
                console.log(rowData);
                $(this).data().autocomplete.term = null;

            } else {
                //window.alert("nao tinha")
                $("#btnAdicionarProduto").trigger('click')
                console.log(rowData);
                $(this).data().autocomplete.term = null;
            }
            //limpa o cache do autocomplete para permitir consulta identica a anterior
            $(this).data().autocomplete.term = null;
        }




    }

    function buscaProdutoInserido(chaveBusca) {
        // Declare variables
        var input, filter, table, tr, td, i, txtValue;
        console.log(chaveBusca)
        input = chaveBusca
        filter = input.toUpperCase();
        table = document.getElementById("tblProdutos");

        tr = table.getElementsByTagName("tr");
        // Loop through all table rows, and hide those who don't match the search query
        for (i = 0; i < tr.length; i++) {
            let finaliza = false
            td = tr[i].getElementsByTagName("td")[0];
            console.log("Linha:" + i)
            if (td) {
                console.log("Linha:" + i)
                txtValue = td.textContent || td.innerText;
                console.log(txtValue)
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    //acao se tiver o item
                    $("#btnAdicionarProduto").trigger('click')
                  // window.alert("item ja presente")
                    //  $(this).data().autocomplete.term = null;
                    finaliza = true
                }
                if ((txtValue.toUpperCase().indexOf(filter) != -1) && finaliza !=true) {
                    $("#btnAdicionarProduto").trigger('click')
                    finaliza = true
                   // window.alert("item não estava presente")
                    //acao se nao tiver
                    // $("#btnAdicionarProduto").trigger('click')
                    // $(this).data().autocomplete.term = null;
                }
                if(finaliza == true){
                break
            }
            }
            
        }
    }
</script>