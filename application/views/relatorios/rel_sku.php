<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />

<div class="row-fluid" style="margin-top: 0">
    <div class="span4">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-shopping-bag"></i>
                </span>
                <h5>Relatórios Rápidos</h5>
            </div>
            <div class="widget-content">
                <ul style="flex-direction: row;" class="site-stats">
                    <li><a target="_blank" href="<?php echo base_url() ?>index.php/relatorios/skuRapid"><i class="fas fa-shopping-bag"></i> <small>SKU rápido - pdf</small></a></li>
                    <li><a target="_blank" href="<?php echo base_url() ?>index.php/relatorios/skuRapid?format=xls"><i class="fas fa-shopping-bag"></i> <small>SKU rápido - xls</small></a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="span8">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </span>
                <h5>Relatórios Customizáveis</h5>
            </div>
            <div class="widget-content">
                <form target="_blank" action="<?php echo base_url() ?>index.php/relatorios/skuCustom" method="get">
                    <div class="span12 well">
                        <div class="span6">
                            <label for="">Data de ocorrência de:</label>
                            <input type="date" name="dataInicial" class="span12" />
                        </div>
                        <div class="span6">
                            <label for="">até:</label>
                            <input type="date" name="dataFinal" class="span12" />
                        </div>
                    </div>

                    <div class="span12 well" style="margin-left: 0">
                        <label for="cliente">Cliente</label>
                        <input class="span12" id="cliente" type="text" name="cliente" />
                        <input type="hidden" name="clientes_id" id="clientes_id">
                    </div>

                    <div class="span12 well" style="margin-left: 0">
                        <div class="span12">
                            <label for="">Origem:</label>
                            <select name="origem" class="span12">
                                <option value="">Todas</option>
                                <option value="os">Ordem de Serviço</option>
                                <option value="venda">Venda</option>
                            </select>
                        </div>
                    </div>

                    <div class="span12 well" style="margin-left: 0">
                        <div class="span12">
                            <label for="">Tipo de impressão:</label>
                            <select name="format" class="span12">
                                <option value="">PDF</option>
                                <option value="xls">XLS</option>
                            </select>
                        </div>
                    </div>

                    <div class="span12" style="display:flex;justify-content: center">
                        <input type="reset" class="button btn btn-warning" value="Limpar" / style="justify-content: center">
                        <button class="button btn btn-inverse"><span class="button__icon"><i class="bx bx-printer"></i></span> <span class="button__text2">Imprimir</span></button>
                    </div>
                </form>
                &nbsp
            </div>
        </div>
    </div>
</div>
</div>
<script src="<?php echo base_url(); ?>assets/js/maskmoney.js"></script>
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $(".money").maskMoney();

        $("#cliente").autocomplete({
            source: "<?php echo base_url(); ?>index.php/os/autoCompleteCliente",
            minLength: 2,
            select: function(event, ui) {
                $("#clientes_id").val(ui.item.id);
            }
        });
    });
</script>
