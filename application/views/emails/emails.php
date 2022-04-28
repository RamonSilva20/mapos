<div class="widget-box">
    <div class="widget-title" style="margin: -20px 0 0">
        <span class="icon">
            <i class="fas fa-envelope"></i>
        </span>
        <h5>Lista de envio de e-mails</h5>
    </div>
    <div class="widget-content nopadding tab-content">
        <table id="tabela" class="table table-bordered ">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Para</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$results) { ?>
                    <tr>
                        <td colspan="5">Nenhum e-mail na fila</td>
                    </tr>
                <?php } ?>

                <?php foreach ($results as $r) {
    $status = [
                        'pending' => '<span class="badge badge-default">Pendente</span>',
                        'sending' => '<span class="badge badge-info">Enviando</span>',
                        'sent' => '<span class="badge badge-success">Enviado</span>',
                        'failed' => '<span class="badge badge-warning">Falhou</span>',
                    ];
    echo '<tr>';
    echo '<td>' . $r->id . '</td>';
    echo '<td>' . $r->to . '</td>';
    echo '<td>' . $status[$r->status] . '</td>';
    echo '<td>' . date('d/m/Y H:i:s', strtotime($r->date)) . '</td>';
    echo '<td>';
    echo '<a href="#modal-excluir" role="button" data-toggle="modal" email="' . $r->id . '" class="btn-nwe4" title="Excluir item"><i class="bx bx-trash-alt"></i></a>  ';
    echo '</td>';
    echo '</tr>';
} ?>

            </tbody>
        </table>
    </div>
</div>
<?php echo $this->pagination->create_links(); ?>
<!-- Modal -->
<div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <form action="<?= site_url('mapos/excluirEmail') ?>" method="post">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h5 id="myModalLabel">Excluir Email da Lista</h5>
        </div>
        <div class="modal-body">
            <input type="hidden" id="idEmail" name="id" value="" />
            <h5 style="text-align: center">Deseja realmente excluir este email da lista de envio?</h5>
        </div>
        <div class="modal-footer" style="display:flex;justify-content: center">
          <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true"><span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
          <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-trash'></i></span> <span class="button__text2">Excluir</span></button>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', 'a', function(event) {
            var email = $(this).attr('email');
            $('#idEmail').val(email);
        });
    });
</script>
