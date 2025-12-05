<style>
    /* Hiding the checkbox, but allowing it to be focused */
    .badgebox {
        opacity: 0;
    }

    .badgebox+.badge {
        /* Move the check mark away when unchecked */
        text-indent: -999999px;
        /* Makes the badge's width stay the same checked and unchecked */
        width: 27px;
    }

    .badgebox:focus+.badge {
        /* Set something to make the badge looks focused */
        /* This really depends on the application, in my case it was: */

        /* Adding a light border */
        box-shadow: inset 0px 0px 5px;
        /* Taking the difference out of the padding */
    }

    .badgebox:checked+.badge {
        /* Move the check mark back when checked */
        text-indent: 0;
    }
</style>
<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-shopping-bag"></i>
                </span>
                <h5>Cadastro de Produto</h5>
            </div>
            <div class="widget-content nopadding tab-content">
                <?php echo $custom_error; ?>
                <form action="<?php echo current_url(); ?>" id="formProduto" method="post" class="form-horizontal">
                    <div class="control-group">
                        <label for="codDeBarra" class="control-label">Código de Barra<span class=""></span></label>
                        <div class="controls">
                            <input id="codDeBarra" type="text" name="codDeBarra" value="<?php echo set_value('codDeBarra'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="descricao" class="control-label">Descrição<span class="required">*</span></label>
                        <div class="controls">
                            <input id="descricao" type="text" name="descricao" value="<?php echo set_value('descricao'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">Tipo de Movimento</label>
                        <div class="controls">
                            <label for="entrada" class="btn btn-default" style="margin-top: 5px;">Entrada
                                <input type="checkbox" id="entrada" name="entrada" class="badgebox" value="1" checked>
                                <span class="badge">&check;</span>
                            </label>
                            <label for="saida" class="btn btn-default" style="margin-top: 5px;">Saída
                                <input type="checkbox" id="saida" name="saida" class="badgebox" value="1" checked>
                                <span class="badge">&check;</span>
                            </label>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="precoCompra" class="control-label">Preço de Compra<span class="required">*</span></label>
                        <div class="controls">
                            <input id="precoCompra" class="money" data-affixes-stay="true" data-thousands="" data-decimal="." type="text" name="precoCompra" value="<?php echo set_value('precoCompra'); ?>" />
                            <strong><span style="color: red" id="errorAlert"></span><strong>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="Lucro" class="control-label">Lucro</label>
                        <div class="controls">
                            <select id="selectLucro" name="selectLucro" style="width: 10.5em;">
                              <option value="markup">Markup</option>
                              <option value="margemLucro">Margem de Lucro</option>
                            </select>
                            <input style="width: 4em;" id="Lucro" name="Lucro" type="text" placeholder="%" maxlength="3" size="2" />
                            <i class="icon-info-sign tip-left" title="Markup: Porcentagem aplicada ao valor de compra | Margem de Lucro: Porcentagem aplicada ao valor de venda"></i>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="precoVenda" class="control-label">Preço de Venda<span class="required">*</span></label>
                        <div class="controls">
                            <input id="precoVenda" class="money" data-affixes-stay="true" data-thousands="" data-decimal="." type="text" name="precoVenda" value="<?php echo set_value('precoVenda'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="unidade" class="control-label">Unidade<span class="required">*</span></label>
                        <div class="controls">
                            <select id="unidade" name="unidade"></select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="estoque" class="control-label">Estoque<span class="required">*</span></label>
                        <div class="controls">
                            <input id="estoque" type="text" name="estoque" value="<?php echo set_value('estoque'); ?>" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="estoqueMinimo" class="control-label">Estoque Mínimo</label>
                        <div class="controls">
                            <input id="estoqueMinimo" type="text" name="estoqueMinimo" value="<?php echo set_value('estoqueMinimo'); ?>" />
                        </div>
                    </div>
                    
                    <!-- Campos Fiscais -->
                    <div class="control-group">
                        <label class="control-label" style="font-weight: bold; color: #0066cc;">Informações Fiscais</label>
                        <div class="controls"></div>
                    </div>
                    
                    <div class="control-group">
                        <label for="ncm" class="control-label">NCM</label>
                        <div class="controls">
                            <input id="ncm" type="text" name="ncm" maxlength="8" value="<?php echo set_value('ncm'); ?>" placeholder="Ex: 12345678" />
                            <small style="color: #666;">Código NCM (8 dígitos)</small>
                        </div>
                    </div>
                    
                    <div class="control-group">
                        <label for="cest" class="control-label">CEST</label>
                        <div class="controls">
                            <input id="cest" type="text" name="cest" maxlength="7" value="<?php echo set_value('cest'); ?>" placeholder="Ex: 1234567" />
                            <small style="color: #666;">Código CEST (7 dígitos)</small>
                        </div>
                    </div>
                    
                    <div class="control-group">
                        <label for="cfop" class="control-label">CFOP</label>
                        <div class="controls">
                            <input id="cfop" type="text" name="cfop" maxlength="4" value="<?php echo set_value('cfop'); ?>" placeholder="Ex: 5102" />
                            <small style="color: #666;">Código CFOP (4 dígitos)</small>
                        </div>
                    </div>
                    
                    <div class="control-group">
                        <label for="origem" class="control-label">Origem</label>
                        <div class="controls">
                            <select id="origem" name="origem">
                                <option value="0" <?php echo set_select('origem', '0', true); ?>>0 - Nacional</option>
                                <option value="1" <?php echo set_select('origem', '1'); ?>>1 - Estrangeira - Importação direta</option>
                                <option value="2" <?php echo set_select('origem', '2'); ?>>2 - Estrangeira - Adquirida no mercado interno</option>
                                <option value="3" <?php echo set_select('origem', '3'); ?>>3 - Nacional - Mercadoria com mais de 40% de conteúdo estrangeiro</option>
                                <option value="4" <?php echo set_select('origem', '4'); ?>>4 - Nacional - Produção em conformidade com processos produtivos básicos</option>
                                <option value="5" <?php echo set_select('origem', '5'); ?>>5 - Nacional - Mercadoria com menos de 40% de conteúdo estrangeiro</option>
                                <option value="6" <?php echo set_select('origem', '6'); ?>>6 - Estrangeira - Importação direta sem similar nacional</option>
                                <option value="7" <?php echo set_select('origem', '7'); ?>>7 - Estrangeira - Adquirida no mercado interno sem similar nacional</option>
                                <option value="8" <?php echo set_select('origem', '8'); ?>>8 - Nacional - Mercadoria com mais de 70% de conteúdo estrangeiro</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="control-group">
                        <label for="tributacao" class="control-label">Tributação ICMS</label>
                        <div class="controls">
                            <select id="tributacao" name="tributacao">
                                <option value="">Selecione...</option>
                                <option value="00" <?php echo set_select('tributacao', '00'); ?>>00 - Tributada integralmente</option>
                                <option value="10" <?php echo set_select('tributacao', '10'); ?>>10 - Tributada com cobrança de ICMS por substituição tributária</option>
                                <option value="20" <?php echo set_select('tributacao', '20'); ?>>20 - Com redução de base de cálculo</option>
                                <option value="30" <?php echo set_select('tributacao', '30'); ?>>30 - Isenta ou não tributada com cobrança de ICMS por substituição tributária</option>
                                <option value="40" <?php echo set_select('tributacao', '40'); ?>>40 - Isenta</option>
                                <option value="41" <?php echo set_select('tributacao', '41'); ?>>41 - Não tributada</option>
                                <option value="50" <?php echo set_select('tributacao', '50'); ?>>50 - Suspensa</option>
                                <option value="51" <?php echo set_select('tributacao', '51'); ?>>51 - Diferimento</option>
                                <option value="60" <?php echo set_select('tributacao', '60'); ?>>60 - ICMS cobrado anteriormente por substituição tributária</option>
                                <option value="70" <?php echo set_select('tributacao', '70'); ?>>70 - Com redução de base de cálculo e cobrança de ICMS por substituição tributária</option>
                                <option value="90" <?php echo set_select('tributacao', '90'); ?>>90 - Outras</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <div class="span12">
                            <div class="span6 offset3" style="display: flex;justify-content: center">
                                <button type="submit" class="button btn btn-mini btn-success" style="max-width: 160px"><span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">Adicionar</span></button>
                                <a href="<?php echo base_url() ?>index.php/produtos" id="" class="button btn btn-mini btn-warning"><span class="button__icon"><i class="bx bx-undo"></i></span><span class="button__text2">Voltar</span></a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url() ?>assets/js/jquery.validate.js"></script>
<script src="<?php echo base_url(); ?>assets/js/maskmoney.js"></script>
<script type="text/javascript">
    function calcLucro(precoCompra, Lucro) {
        var lucroTipo = $('#selectLucro').val();
        var precoVenda;
        
        if (lucroTipo === 'markup') {
            precoVenda = (precoCompra * (1 + Lucro / 100)).toFixed(2);
        } else if (lucroTipo === 'margemLucro') {
            precoVenda = (precoCompra / (1 - (Lucro / 100))).toFixed(2);
        }
        
        return precoVenda;
    }
    
    function atualizarPrecoVenda() {
        var precoCompra = Number($("#precoCompra").val());
        var lucro = Number($("#Lucro").val());
        
        if (precoCompra > 0 && lucro >= 0) {
            $('#precoVenda').val(calcLucro(precoCompra, lucro));
        }
    }
    
    $("#precoCompra, #Lucro, #selectLucro").on('input change', atualizarPrecoVenda);

    $("#precoCompra, #Lucro").on('input change', function() {
        if ($("#precoCompra").val() == '0.00' && $('#precoVenda').val() != '') {
            $('#errorAlert').text('Você não pode preencher valor de compra e depois apagar.').css("display", "inline").fadeOut(6000);
            $('#precoVenda').val('');
            $("#precoCompra").focus();
        } else if ($("#precoCompra").val() != '' && $("#Lucro").val() != '') {
            atualizarPrecoVenda();
        }
    });

    $("#Lucro").keyup(function() {
        this.value = this.value.replace(/[^0-9.]/g, '');
        if ($("#precoCompra").val() == null || $("#precoCompra").val() == '') {
            $('#errorAlert').text('Preencher valor da compra primeiro.').css("display", "inline").fadeOut(5000);
            $('#Lucro').val('');
            $('#precoVenda').val('');
            $("#precoCompra").focus();

        } else if (Number($("#Lucro").val()) >= 0) {
            $('#precoVenda').val(calcLucro(Number($("#precoCompra").val()), Number($("#Lucro").val())));
        } else {
            $('#errorAlert').text('Não é permitido número negativo.').css("display", "inline").fadeOut(5000);
            $('#Lucro').val('');
            $('#precoVenda').val('');
        }
    });

    $('#precoVenda').focusout(function () {
        if (Number($('#precoVenda').val()) < Number($("#precoCompra").val())) {
            $('#errorAlert').text('Preço de venda não pode ser menor que o preço de compra.').css("display", "inline").fadeOut(6000);
            $('#precoVenda').val('');
        }
    });

    $(document).ready(function() {
        $(".money").maskMoney();
        $.getJSON('<?php echo base_url() ?>assets/json/tabela_medidas.json', function(data) {
            for (i in data.medidas) {
                $('#unidade').append(new Option(data.medidas[i].descricao, data.medidas[i].sigla));
            }
        });
        $('#formProduto').validate({
            rules: {
                descricao: {
                    required: true
                },
                unidade: {
                    required: true
                },
                precoCompra: {
                    required: true
                },
                precoVenda: {
                    required: true
                },
                estoque: {
                    required: true
                }
            },
            messages: {
                descricao: {
                    required: 'Campo Requerido.'
                },
                unidade: {
                    required: 'Campo Requerido.'
                },
                precoCompra: {
                    required: 'Campo Requerido.'
                },
                precoVenda: {
                    required: 'Campo Requerido.'
                },
                estoque: {
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
    });
</script>
