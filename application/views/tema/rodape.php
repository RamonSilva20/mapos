<div class="row-fluid">
    <div id="footer" class="span12">
        <a class="pecolor" href="https://github.com/RamonSilva20/mapos" target="_blank">
            <?= date('Y') ?> &copy; Ramon Silva - Map-OS - Versão: <?= $this->config->item('app_version') ?>
        </a>
    </div>
</div>
<!--end-Footer-part-->
<script src="<?= base_url() ?>assets/js/bootstrap.min.js"></script>
<script src="<?= base_url() ?>assets/js/matrix.js"></script>
</body>

<script type="text/javascript">
    $(document).ready(function() {
        var dataTableEnabled = '<?= $configuration['control_datatable'] ?>';
        var tabelaVazia = '<?= empty($results) ? '1' : '0' ?>'; // Verifica se a tabela está vazia

        if (dataTableEnabled == '1' && tabelaVazia == '0') { // Só inicializa o DataTables se houver resultados
            $('#tabela').dataTable({
                "ordering": false,
                "info": false,
                "language": {
                    "url": "<?= base_url() ?>assets/js/dataTable_pt-br.json",
                    "zeroRecords": "Termo de busca não encontrado!"

                }
            });
        }
    });
</script>

</html>
