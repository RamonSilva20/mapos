<link href="<?= base_url('assets/css/lib/datepicker/bootstrap-datepicker3.min.css'); ?>" rel="stylesheet">
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-title">
                <h4>
                    <?= $button ?>
                        <?= ucfirst($this->lang->line('file')); ?>
                </h4>
                <hr>
            </div>
            <div class="card-body">
                <div class="basic-form">
                    <form action="<?= $action; ?>" method="post" enctype="multipart/form-data">
                        
                        <?php if($this->uri->segment(2) == 'create' || $this->uri->segment(2) == 'create_action' ) { ?>
                        
                            <?= $this->upload->display_errors('<div class="alert alert-danger">', '</div>'); ?>
                        
                            <div class="form-group">
                                <label for="file">
                                    <?= ucfirst($this->lang->line('file')) ?>
                                </label>
                                <input type="file" class="form-control" name="file" id="file" value="<?= $file; ?>" />
                                <?= form_error('file') ?>
                            </div>

                        <?php } ?>

                        <div class="form-group">
                            <label for="documento">
                                <?= ucfirst($this->lang->line('file_name')) ?>
                            </label>
                            <input type="text" class="form-control" name="documento" id="documento" value="<?= $documento; ?>" />
                            <?= form_error('documento') ?>
                        </div>
                        <div class="form-group">
                            <label for="descricao">
                                <?= ucfirst($this->lang->line('file_description')) ?>
                            </label>
                            <textarea class="form-control" rows="3" name="descricao" id="descricao"><?= $descricao; ?></textarea>
                            <?= form_error('descricao') ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="cadastro">
                                <?= ucfirst($this->lang->line('file_date')) ?>
                            </label>
                            <input type="text" class="form-control datepicker" name="cadastro" id="cadastro" value="<?= date($this->config->item('date_format'), strtotime($cadastro)); ?>" />
                            <?= form_error('cadastro') ?>
                        </div>
                      
                        <input type="hidden" name="idDocumentos" value="<?= $idDocumentos; ?>" />
                        <button type="submit" class="btn btn-info">
                            <?= $button ?>
                        </button>
                        <a href="<?= site_url('arquivos') ?>" class="btn btn-dark">
                            <i class="fa fa-reply"></i>
                            <?= $this->lang->line('app_cancel'); ?>
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/lib/datepicker/bootstrap-datepicker.min.js') ?>" charset="UTF-8"></script>

<script>

    $(document).ready(function(){

        $('.datepicker').datepicker({
            format: '<?= date_format_datepicker($this->config->item('date_format')) ?>'
        });

    });

</script>