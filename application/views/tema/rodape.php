<div class="row-fluid">
    <div id="footer" class="span12"> <a class="pecolor" href="https://github.com/RamonSilva20/mapos" target="_blank">
            <?php echo date('Y'); ?> &copy; Ramon Silva - Map-OS - Versão:
            <?php echo $this->config->item('app_version'); ?></a></div>
</div>
<!--end-Footer-part-->
<script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/matrix.js"></script>
</body>
<script type="text/javascript">
    $(document).ready(function() {
        var dataTableEnabled = '<?php echo $configuration['control_datatable']; ?>';
        if (dataTableEnabled == '1') {
            $('#tabela').dataTable({
                "ordering": false,
                "language": {
                    "url": "<?php echo base_url(); ?>assets/js/dataTable_pt-br.json"
                }
            });
        }
    });
</script>

</html>
