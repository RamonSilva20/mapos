<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fas fa-file-invoice"></i>
                </span>
                <h5>Notas de Entrada</h5>
            </div>
            <div class="widget-content nopadding">
                <div class="span12" style="padding: 1%; margin-left: 0">
                    <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aNotaEntrada')) { ?>
                        <div class="span3">
                            <a href="<?= base_url() ?>index.php/notasentrada/adicionar" class="button btn btn-mini btn-success"
                                style="max-width: 180px; white-space: nowrap;">
                                <span class="button__icon"><i class='bx bx-plus-circle'></i></span>
                                <span class="button__text2">Nova Nota</span>
                            </a>
                        </div>
                    <?php } ?>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Série</th>
                            <th>Chave de Acesso</th>
                            <th>Emitente</th>
                            <th>Data Emissão</th>
                            <th>Valor Total</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($results) {
                            foreach ($results as $r) {
                                echo '<tr>';
                                echo '<td>' . $r->numero_nf . '</td>';
                                echo '<td>' . $r->serie_nf . '</td>';
                                echo '<td>' . ($r->chave_acesso ? substr($r->chave_acesso, 0, 20) . '...' : '-') . '</td>';
                                echo '<td>' . $r->nome_emitente . '</td>';
                                echo '<td>' . date('d/m/Y', strtotime($r->data_emissao)) . '</td>';
                                echo '<td>R$ ' . number_format($r->valor_total, 2, ',', '.') . '</td>';
                                echo '<td>' . $r->status . '</td>';
                                echo '<td>';
                                echo '<a href="' . base_url() . 'index.php/notasentrada/visualizar/' . $r->idNotaEntrada . '" class="btn btn-mini btn-info" title="Visualizar"><i class="bx bx-show"></i></a> ';
                                if ($this->permission->checkPermission($this->session->userdata('permissao'), 'eNotaEntrada')) {
                                    echo '<a href="' . base_url() . 'index.php/notasentrada/excluir/' . $r->idNotaEntrada . '" class="btn btn-mini btn-danger" title="Excluir" onclick="return confirm(\'Tem certeza que deseja excluir esta nota?\')"><i class="bx bx-trash-alt"></i></a>';
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="8">Nenhuma nota de entrada cadastrada.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

