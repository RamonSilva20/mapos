<div class="quick-actions_homepage">
    <ul class="quick-actions">
        <li class="bg_lo span3"> <a href="<?php echo base_url() ?>index.php/mine/os"> <i class="fas fa-diagnoses" style="font-size:36px"></i>
                <div>Ordens de Serviço</div>
            </a></li>
        <li class="bg_ls span3"> <a href="<?php echo base_url() ?>index.php/mine/compras"><i class="fas fa-shopping-cart" style="font-size:36px"></i>
                <div>Compras</div>
            </a></li>
        <li class="bg_lg span3"> <a href="<?php echo base_url() ?>index.php/mine/conta"><i class="fas fa-user"  style="font-size:36px"></i>
                <div>Minha Conta</div>
            </a></li>
    </ul>
</div>


<div class="span12" style="margin-left: 0">

    <div class="widget-box">
        <div class="widget-title"><span class="icon"><i class="fas fa-signal"></i></span>
            <h5>Últimas Ordens de Serviço</h5>
        </div>
        <div class="widget-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Data Inicial</th>
                        <th>Data Final</th>
                        <th>Garantia</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($os != null) {
                        foreach ($os as $o) {
                            echo '<tr>';
                            echo '<td>' . $o->idOs . '</td>';
                            echo '<td>' . date('d/m/Y', strtotime($o->dataInicial)) . '</td>';
                            echo '<td>' . date('d/m/Y', strtotime($o->dataFinal)) . '</td>';
                            echo '<td>' . $o->garantia . '</td>';
                            echo '<td>' . $o->status . '</td>';
                            echo '<td> <a href="' . base_url() . 'index.php/mine/visualizarOs/' . $o->idOs . '" class="btn"> <i class="fas fa-eye" ></i> </a></td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="3">Nenhum ordem de serviço encontrada.</td></tr>';
                    }

                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="widget-box">
        <div class="widget-title"><span class="icon"><i class="fas fa-signal"></i></span>
            <h5>Últimas Compras</h5>
        </div>
        <div class="widget-content">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Data de Venda</th>
                        <th>Responsável</th>
                        <th>Faturado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($compras != null) {
                        foreach ($compras as $p) {
                            if ($p->faturado == 1) {
                                $faturado = 'Sim';
                            } else {
                                $faturado = 'Não';
                            }
                            echo '<tr>';
                            echo '<td>' . $p->idVendas . '</td>';
                            echo '<td>' . date('d/m/Y', strtotime($p->dataVenda)) . '</td>';
                            echo '<td>' . $p->nome . '</td>';
                            echo '<td>' . $faturado . '</td>';
                            echo '<td> <a href="' . base_url() . 'index.php/mine/visualizarCompra/' . $p->idVendas . '" class="btn"> <i class="fas fa-eye" ></i> </a></td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5">Nenhum venda encontrada.</td></tr>';
                    }

                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
