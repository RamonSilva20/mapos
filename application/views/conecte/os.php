<?php
// alterar para permissão de o cliente adicionar ou não a ordem de serviço
if (!$this->session->userdata('cadastra_os')) { ?>
    <div class="span12" style="margin-left: 0">
        <div class="span3">
            <a href="<?php echo base_url(); ?>index.php/mine/adicionarOs" class="button btn btn-success" style="max-width: 150px">
              <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">Adicionar</span></a>
        </div>
    </div>
<?php
}

if (!$results) {
    ?>
    <div class="span12" style="margin-left: 0">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-diagnoses"></i>
                </span>
                <h5>Ordens de Serviço</h5>

            </div>

            <div class="widget-content nopadding tab-content">


                <table id="tabela" class="table table-bordered ">
                    <thead>
                        <tr>
                            <th>Nº</th>
                            <th>Responsável</th>
                            <th>Data Inicial</th>
                            <th>Data Final</th>
                            <th>Venc. Garantia</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr>
                            <td colspan="6">Nenhuma OS Cadastrada</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

<?php
} else { ?>

    <div class="span12" style="margin-left: 0">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-diagnoses"></i>
                </span>
                <h5>Ordens de Serviço</h5>

            </div>

            <div class="widget-content nopadding tab-content">


                <table class="table table-bordered ">
                    <thead>
                        <tr>
                            <th>Nº</th>
                            <th>Responsável</th>
                            <th>Data Inicial</th>
                            <th>Data Final</th>
                            <th>Venc. Garantia</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $this->load->model('os_model'); foreach ($results as $r) {
                            $dataInicial = date(('d/m/Y'), strtotime($r->dataInicial));
                            $dataFinal = date(('d/m/Y'), strtotime($r->dataFinal));
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
                            
                            $vencGarantia = '';
                            if ($r->garantia && is_numeric($r->garantia)) {
                                $vencGarantia = dateInterval($r->dataFinal, $r->garantia);
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

                            echo '<tr>';
                            echo '<td>' . $r->idOs . '</td>';
                            echo '<td>' . $r->nome . '</td>';
                            echo '<td>' . $dataInicial . '</td>';
                            echo '<td>' . $dataFinal . '</td>';
                            echo '<td><span class="badge" style="background-color: ' . $corGarantia . '; border-color: ' . $corGarantia . '">' . $vencGarantia . '</span> </td>';
                            echo '<td><span class="badge" style="background-color: ' . $cor . '; border-color: ' . $cor . '">' . $r->status . '</span> </td>';

                            echo '<td><a href="' . base_url() . 'index.php/mine/visualizarOs/' . $r->idOs . '" class="btn-nwe" title="Visualizar e Imprimir"><i class="bx bx-show-alt"></i></a>
                                  <a href="' . base_url() . 'index.php/mine/imprimirOs/' . $r->idOs . '" class="btn-nwe3" title="Imprimir" target="_blank"><i class="bx bx-printer"></i></a>
                                  <a href="' . base_url() . 'index.php/mine/detalhesOs/' . $r->idOs . '" class="btn-nwe4" title="Ver mais detalhes"><i class="bx bx-detail"></i></a>
                                  </td>';
                            echo '</tr>';
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php echo $this->pagination->create_links();
} ?>
