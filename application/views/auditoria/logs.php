<a href="#modal-excluir" role="button" data-toggle="modal" class="btn btn-danger tip-top" title="Excluir Logs"><i class="fas fa-trash-alt"></i> Remover Logs - 30 dias ou mais</a>

<div class="widget-box">
    <div class="widget-title">
        <span class="icon">
            <i class="fas fa-clock"></i>
        </span>
        <h5>Logs</h5>
    </div>
    <div class="widget-content nopadding">
    <!--
    <div class="widget_box_Painel2">
    -->
        <table id="tabela" width="100%" class="table_p">
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>IP</th>
                    <th>Tarefa</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $r) {
    echo '<tr>';
    echo '<td><div align="center">' . $r->usuario . '</td>';
    echo '<td><div align="center">' . date('d/m/Y', strtotime($r->data)) . '</td>';
    echo '<td><div align="center">' . $r->hora . '</td>';
    echo '<td><div align="center">' . $r->ip . '</td>';
    echo '<td><div align="center">' . $r->tarefa . '</td>';
    echo '</tr>';
} ?>
                <?php if (!$results) { ?>
                    <tr>
                        <td colspan="5">Nenhum registro encontrado.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php echo $this->pagination->create_links(); ?>

<!-- Modal -->
<div id="modal-excluir" class="modal hide fade widget_box_vizualizar4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?php echo site_url('auditoria/clean') ?>" method="post">
        <div class="modal_header_anexos">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5>Limpeza de Logs</h5>
        </div>
        <div class="modal-body">
            <h5 style="text-align: center">Deseja realmente remover os logs mais antigos?</h5>
        </div>
        <div class="modal-footer">
            <button class="btn btn-warning" data-dismiss="modal" aria-hidden="true">Cancelar</button>
            <button class="btn btn-danger">Excluir</button>
        </div>
    </form>
</div>
