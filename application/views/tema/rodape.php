
<!--Footer-part-->
<div class="row-fluid">
    <div id="footer" class="span12"> <a href="https://github.com/RamonSilva20/mapos" target="_blank">
        <?= date('Y'); ?> &copy; Map-OS - Ramon Silva </a></div>
  </div>
  <!--end-Footer-part-->
  <script src="<?= base_url(); ?>assets/js/bootstrap.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/matrix.js"></script>
</body>
<script type="text/javascript">
    $(document).ready(function() {
        $('#tabela').dataTable( {
            "ordering": false,
            "language": {
                "url": "<?= base_url(); ?>assets/js/dataTable_pt-br.json"
            }
        } );
} );
</script>
</html>

