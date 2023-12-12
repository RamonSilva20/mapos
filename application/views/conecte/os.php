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
                            <th>#</th>
                            <th>Responsável</th>
                            <th>Data Inicial</th>
                            <th>Data Final</th>
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
                            <th>#</th>
                            <th>Responsável</th>
                            <th>Data Inicial</th>
                            <th>Data Final</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $r) {
                            $dataInicial = date(('d/m/Y'), strtotime($r->dataInicial));
                            $dataFinal = date(('d/m/Y'), strtotime($r->dataFinal));
                            if ($r->status == "Aberto") {
                                $status = '<span class="label label-success">Aberto</span>';
                            } elseif ($r->status == "Orçamento") {
                                $status = '<span class="label label-info">Orçamento</span>';
                            } elseif ($r->status == "Finalizado") {
                                $status = '<span class="label label-important">Finalizado</span>';
                            } elseif ($r->status == "Cancelado") {
                                $status = '<span class="label label-inverse">Cancelado</span>';
                            } elseif ($r->status == "Faturado") {
                                $status = '<span class="label">Faturado</span>';
                            } elseif ($r->status == "Negociação") {
                                $status = '<span class="label">Negociação / Inadimplente</span>';
                            } else {
                                $status = '<span class="label">Em Andamento</span>';
                            }
                            echo '<tr>';
                            echo '<td>' . $r->idOs . '</td>';
                            echo '<td>' . $r->nome . '</td>';
                            echo '<td>' . $dataInicial . '</td>';
                            echo '<td>' . $dataFinal . '</td>';
                            echo '<td>' . $status . '</td>';


                            echo '<td><a href="' . base_url() . 'index.php/mine/visualizarOs/' . $r->idOs . '" class="btn-nwe" title="Visualizar e Imprimir"><i class="bx bx-show-alt"></i></a>
                                  <a href="' . base_url() . 'index.php/mine/imprimirOs/' . $r->idOs . '" class="btn-nwe3" title="Imprimir"><i class="bx bx-printer"></i></a>
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
