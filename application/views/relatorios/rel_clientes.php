<div class="row-fluid" style="margin-top: 0">
    <div class="span4">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="icon-list-alt"></i>
                </span>
                <h5>Relatórios Rápidos</h5>
            </div>
            <div class="widget-content">
                <ul class="site-stats">
                    <li><a href="<?php echo base_url()?>index.php/relatorios/clientesRapid"><i class="icon-user"></i> <small>Todos os Clientes</small></a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="span8">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="icon-list-alt"></i>
                </span>
                <h5>Relatórios Customizáveis</h5>
            </div>
            <div class="widget-content">
                <div class="span12 well">
                    <form action="<?php echo base_url()?>index.php/relatorios/clientesCustom" method="get">
                    <div class="span4">
                        <label for="">Cadastrado de:</label>
                        <input type="date" name="dataInicial" class="span12" />
                    </div>
                    <div class="span4">
                        <label for="">até:</label>
                        <input type="date" name="dataFinal" class="span12" />
                    </div>
                    <div class="span4">
                        <label for="">.</label>
                        <button class="btn btn-inverse span12"><i class="icon-print icon-white"></i> Imprimir</button>
                    </div>
                    </form>
                </div>
                .
            </div>
        </div>
    </div>
</div>