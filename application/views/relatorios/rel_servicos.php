<div class="row-fluid" style="margin-top: 0">
    <div class="span4">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-wrench"></i>
                </span>
                <h5>Relatórios Rápidos</h5>
            </div>
            <div class="widget_box_vizualizar2" style="background-color:#f5f5f5">
                <ul class="site-stats">
                    <li><a target="_blank" href="<?php echo base_url() ?>index.php/relatorios/servicosRapid"><i class="fas fa-wrench"></i> <small>Todos os Serviços</small></a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="span8">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-wrench"></i>
                </span>
                <h5>Relatórios Customizáveis</h5>
            </div>
            
            <div class="span12 widget_box_vizualizar5" style="margin-left: 0">



<div class="span12 well_i" style="margin-left: 0">
						
                        <div class="span6">
                                <label for="">Preço de:</label>
                                <input type="text" name="precoInicial" class="span12 money" />
                            </div>
                            <div class="span6">
                                <label for="">até:</label>
                                <input type="text" name="precoFinal" class="span12 money" />
                            </div>
                        
</div>


<div class="span12" style="margin-left: 0; text-align: center">

<input type="reset" class="btn" value="Limpar" />
                        <button class="btn btn-inverse"><i class="fas fa-print"></i> Imprimir</button>

</div>
</form>
</div>
            
        </div>
    </div>
</div>
<script src="<?php echo base_url(); ?>assets/js/maskmoney.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $(".money").maskMoney();
    });
</script>
