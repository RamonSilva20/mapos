<div class="accordion" id="collapse-group">
    <div class="accordion-group widget-box">
        <div class="accordion-heading">
            <div class="widget-title">
                <a data-parent="#collapse-group" href="#collapseGOne" data-toggle="collapse">
                    <span class="icon"><i class="far fa-car"></i></span>
                    <h5>Dados do Veículo</h5>
                </a>
            </div>
        </div>
        <div class="collapse in accordion-body">
            <div class="widget-content">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td style="text-align: right; width: 50%;"><strong>Cliente</strong></td>
                            <td>
                                <?php echo $result->nomeCliente; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; width: 50%;"><strong>Placa</strong></td>
                            <td>
                                <?php echo $result->placa; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right;"><strong>Ano do Modelo</strong></td>
                            <td>
                                <?php echo $result->anoModelo; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right;"><strong>Ano de Fabricacao</strong></td>
                            <td>
                                <?php echo $result->anoFabricacao; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right;"><strong>Marca / Fabricante</strong></td>
                            <td>
                                <?php echo $result->marcaFabricante; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right;"><strong>Modelo</strong></td>
                            <td>
                                <?php echo $result->modelo; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right;"><strong>Chassi</strong></td>
                            <td>
                                <?php echo $result->chassi; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
