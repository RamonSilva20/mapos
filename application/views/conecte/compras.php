<?php

if (!$results) { ?>
    <div class="widget-box">
        <div class="widget-title" style="margin: -20px 0 0">
            <span class="icon">
                <i class="fas fa-tags"></i>
            </span>
            <h5>Compras</h5>

        </div>

        <div class="widget-content nopadding tab-content">


            <table id="tabela" class="table table-bordered ">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Data da Compra</th>
                        <th>Vendedor</th>
                        <th>Faturado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <td colspan="6">Nenhuma compra cadastrada</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
<?php
} else { ?>


    <div class="widget-box">
        <div class="widget-title">
            <span class="icon">
                <i class="fas fa-shopping-cart"></i>
            </span>
            <h5>Compras</h5>

        </div>

        <div class="widget-content nopadding tab-content">


            <table id="tabela" class="table table-bordered ">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Vendedor</th>
                        <th>Data da Compra</th>
                        <th>Vencimento da Garantia</th>
                        <th>Faturado</th>
                        <th>Status</th>
                        <th>Visualizar / Imprimir</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r) {
                        $vencGarantia = '';

                        if ($r->garantia && is_numeric($r->garantia)) {
                            $vencGarantia = dateInterval($r->dataVenda, $r->garantia);
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
                        } elseif ($r->garantia == "0") {
                            $vencGarantia = 'Sem Garantia';
                            $corGarantia = '';
                        } else {
                            $vencGarantia = '';
                            $corGarantia = '';
                        }
                        $dataVenda = date(('d/m/Y'), strtotime($r->dataVenda));
                        if ($r->faturado == 1) {
                            $faturado = 'Sim';
                        } else {
                            $faturado = 'Não';
                        }
                    
                    switch ($r->status) {
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
                        echo '<td>' . $r->idVendas . '</td>';
                        echo '<td>' . $r->nome . '</td>';
                        echo '<td>' . $dataVenda . '</td>';
                        echo '<td><span class="badge" style="background-color: ' . $corGarantia . '; border-color: ' . $corGarantia . '">' . $vencGarantia . '</span> </td>';
                        echo '<td>' . $faturado . '</td>';
                        echo '<td><span class="badge" style="background-color: ' . $cor . '; border-color: ' . $cor . '">' . $r->status . '</span> </td>';
                        echo '<td><a href="' . base_url() . 'index.php/mine/visualizarCompra/' . $r->idVendas . '" class="btn-nwe" title="Ver mais detalhes"><i class="bx bx-show"></i></a>
                      <a href="' . base_url() . 'index.php/mine/imprimirCompra/' . $r->idVendas . '" class="btn-nwe6" title="Imprimir"><i class="bx bx-printer"></i></a>

                  </td>';
                        echo '</tr>';
                    } ?>
                </tbody>
            </table>
        </div>
    </div>

<?php echo $this->pagination->create_links();
} ?>
