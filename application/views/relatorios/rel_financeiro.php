<div class="row-fluid" style="margin-top: 0">
    <div class="span4">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </span>
                <h5>Relatórios Rápidos</h5>
            </div>
            <div class="widget-content">
                <ul style="flex-direction: row;" class="site-stats">
                    <li><a target="_blank" href="<?php echo base_url() ?>index.php/relatorios/financeiroRapid"><i class="fas fa-hand-holding-usd"></i> <small>Relatório do mês - pdf</small></a></li>
                    <li><a target="_blank" href="<?php echo base_url() ?>index.php/relatorios/financeiroRapid?format=xls"><i class="fas fa-hand-holding-usd"></i> <small>Relatório do mês - xls</small></a></li>
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
                <form target="_blank" action="<?php echo base_url() ?>index.php/relatorios/financeiroCustom" method="get">
                    <div class="span12 well">

                        <div class="span6">
                            <label for="">Vencimento de:</label>
                            <input type="date" name="dataInicial" class="span12" />
                        </div>
                        <div class="span6">
                            <label for="">até:</label>
                            <input type="date" name="dataFinal" class="span12" />
                        </div>

                    </div>

                    <div class="span12 well" style="margin-left: 0">
                        <div class="span6">
                            <label for="">Tipo:</label>
                            <select name="tipo" class="span12">
                                <option value="todos">Todos</option>
                                <option value="receita">Receita</option>
                                <option value="despesa">Despesa</option>
                            </select>
                        </div>
                        <div class="span6">
                            <label for="">Situação:</label>
                            <select name="situacao" class="span12">
                                <option value="todos">Todos</option>
                                <option value="pago">Pago</option>
                                <option value="pendente">Pendente</option>
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
