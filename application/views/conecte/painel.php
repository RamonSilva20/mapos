<link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>

<div class="quick-actions_homepage">
    <ul class="cardBox">
        <li class="card">
            <a href="<?php echo base_url() ?>index.php/mine/os">
                <div class="lord-icon04">
                    <i class='bx bx-file iconBx04'></i>
                </div>
            </a>
            <a href="<?php echo base_url() ?>index.php/mine/os">
                <div style="font-size: 1.2em" class="numbers">Ordens de Serviço</div>
            </a>
        </li>

        <li class="card">
            <a href="<?php echo base_url() ?>index.php/mine/compras">
                <div class="lord-icon05">
                    <i class='bx bx-cart-alt iconBx05'></i>
                </div>
            </a>
            <a href="<?php echo base_url() ?>index.php/mine/compras">
                <div style="font-size: 1.2em" class="numbers">Compras&nbsp;&nbsp;&nbsp;&nbsp;</div>
            </a>
        <li class="card">
            <a href="<?php echo base_url() ?>index.php/mine/cobrancas">
                <div class="lord-icon05">
                    <i class='bx bx-credit-card-front iconBx05'></i>
                </div>
            </a>
            <a href="<?php echo base_url() ?>index.php/mine/cobrancas">
                <div style="font-size: 1.2em" class="numbers">Cobranças&nbsp;&nbsp;&nbsp;&nbsp;</div>
            </a>
        </li>
        <li class="card">
            <a href="<?php echo base_url() ?>index.php/mine/conta">
                <div class="lord-icon07">
                    <i class='bx bx-user-circle iconBx07'></i></span>
                </div>
            </a>
            <a href="<?php echo base_url() ?>index.php/mine/conta">
                <div style="font-size: 1.2em" class="numbers">Minha Conta</div>
            </a>
        </li>
    </ul>
</div>

<div class="span12" style="margin-left: 0">
    <div class="widget-box">
        <div class="widget-title" style="margin: -20px 0 0">
            <span class="icon"><i class="fas fa-signal"></i></span>
            <h5>Últimas Ordens de Serviço</h5>
        </div>
        <div class="widget-content">
            <table id="tabela" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Responsável</th>
                        <th>Data Inicial</th>
                        <th>Data Final</th>
                        <th>Venc. da Garantia</th>
                        <th>Status</th>
                        <th style="text-align:right">Visualizar / Imprimir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($os != null) {
                        foreach ($os as $o) {
                            $vencGarantia = '';

                            if ($o->garantia && is_numeric($o->garantia)) {
                                $vencGarantia = dateInterval($o->dataFinal, $o->garantia);
                            }
                            $corGarantia = '';
                            if (!empty($vencGarantia)) {
                                $dataGarantia = explode('/', $vencGarantia);
                                $dataGarantiaFormatada = $dataGarantia[2] . '-' . $dataGarantia[1] . '-' . $dataGarantia[0];
                            if (strtotime($dataGarantiaFormatada) >= strtotime(date('d-m-Y'))) {
                                    $corGarantia = '#4d9c79';
                            } else {
                                    $corGarantia = '#f24c6f';
                            }
                            } elseif ($o->garantia == "0") {
                                $vencGarantia = 'Sem Garantia';
                                $corGarantia = '';
                            } else {
                                $vencGarantia = '';
                                $corGarantia = '';
                            }

                            switch ($o->status) {
                                case 'Aberto':
                                    $cor = '#00cd00';
                                    break;
                                case 'Em Andamento':
                                    $cor = '#436eee';
                                    break;
                                case 'Orçamento':
                                    $cor = '#CDB380';
                                    break;
                                case 'Negociação':
                                    $cor = '#AEB404';
                                    break;
                                case 'Cancelado':
                                    $cor = '#CD0000';
                                    break;
                                case 'Finalizado':
                                    $cor = '#256';
                                    break;
                                case 'Faturado':
                                    $cor = '#B266FF';
                                    break;
                                case 'Aguardando Peças':
                                    $cor = '#FF7F00';
                                    break;
                                case 'Aprovado':
                                    $cor = '#808080';
                                    break;
                                default:
                                    $cor = '#E0E4CC';
                                    break;
                            }

                            echo '<tr>';
                            echo '<td>' . $o->idOs . '</td>';
                            echo '<td>' . $o->nome . '</td>';
                            echo '<td>' . date('d/m/Y', strtotime($o->dataInicial)) . '</td>';
                            echo '<td>' . date('d/m/Y', strtotime($o->dataFinal)) . '</td>';
                            echo '<td><span class="badge" style="background-color: ' . $corGarantia . '; border-color: ' . $corGarantia . '">' . $vencGarantia . '</span> </td>';
                            echo '<td><span class="badge" style="background-color: ' . $cor . '; border-color: ' . $cor . '">' . $o->status . '</span> </td>';
                            echo '<td style="text-align:right">';
                            echo '<a href="' . base_url() . 'index.php/mine/visualizarOs/' . $o->idOs . '" class="btn"> <i class="fas fa-eye" ></i></a> ';
                            echo '<a href="' . base_url() . 'index.php/mine/imprimirOs/' . $o->idOs . '" class="btn"> <i class="fas fa-print"></i></a>';
                            echo '</td>';
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
        <div class="widget-title" style="margin: -20px 0 0">
            <span class="icon"><i class="fas fa-signal"></i></span>
            <h5>Últimas Compras</h5>
        </div>
        <div class="widget-content">
            <table id="tabela" class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Responsável</th>
                        <th>Data da Venda</th>
                        <th>Faturado</th>
                        <th>Venc. da Garantia</th>
                        <th>Status</th>
                        <th style="text-align:right">Visualizar / Imprimir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
            if ($compras != null) {
                foreach ($compras as $c) {
                    $vencGarantia = '';

                            if ($c->garantia && is_numeric($c->garantia)) {
                                $vencGarantia = dateInterval($c->dataVenda, $c->garantia);
                            }
                            $corGarantia = '';
                            if (!empty($vencGarantia)) {
                                    $dataGarantia = explode('/', $vencGarantia);
                                    $dataGarantiaFormatada = $dataGarantia[2] . '-' . $dataGarantia[1] . '-' . $dataGarantia[0];
                                if (strtotime($dataGarantiaFormatada) >= strtotime(date('d-m-Y'))) {
                                    $corGarantia = '#4d9c79';
                                } else {
                                    $corGarantia = '#f24c6f';
                                }
                                } elseif ($c->garantia == "0") {
                                    $vencGarantia = 'Sem Garantia';
                                    $corGarantia = '';
                                } else {
                                    $vencGarantia = '';
                                    $corGarantia = '';
                                }
                            if ($c->faturado == 1) {
                                    $faturado = 'Sim';
                                } else {
                                    $faturado = 'Não';
                                }
                    
                    switch ($c->status) {
                        case 'Aberto':
                            $cor = '#00cd00';
                            break;
                        case 'Em Andamento':
                            $cor = '#436eee';
                            break;
                        case 'Orçamento':
                            $cor = '#CDB380';
                            break;
                        case 'Negociação':
                            $cor = '#AEB404';
                            break;
                        case 'Cancelado':
                            $cor = '#CD0000';
                            break;
                        case 'Finalizado':
                            $cor = '#256';
                            break;
                        case 'Faturado':
                            $cor = '#B266FF';
                            break;
                        case 'Aguardando Peças':
                            $cor = '#FF7F00';
                            break;
                        case 'Aprovado':
                            $cor = '#808080';
                            break;
                        default:
                            $cor = '#E0E4CC';
                            break;
                    }
                    echo '<tr>';
                    echo '<td>' . $c->idVendas . '</td>';
                    echo '<td>' . $c->nome . '</td>';
                    echo '<td>' . date('d/m/Y', strtotime($c->dataVenda)) . '</td>';
                    echo '<td>' . $faturado . '</td>';
                    echo '<td><span class="badge" style="background-color: ' . $corGarantia . '; border-color: ' . $corGarantia . '">' . $vencGarantia . '</span> </td>';
                    echo '<td><span class="badge" style="background-color: ' . $cor . '; border-color: ' . $cor . '">' . $c->status . '</span> </td>';
                    echo '<td style="text-align:right">';
                    echo '<a href="' . base_url() . 'index.php/mine/visualizarCompra/' . $c->idVendas . '" class="btn"> <i class="fas fa-eye" ></i> </a> ';
                    echo '<a href="' . base_url() . 'index.php/mine/imprimirCompra/' . $c->idVendas . '" class="btn"> <i class="fas fa-print" ></i> </a>';
                    echo '</td>';
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
