<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-title">
                <h4><?= $button ?> <?= ucfirst($this->lang->line('service')); ?> </h4>
                <hr>  
            </div>
            <div class="card-body">
                <div class="basic-form">
                    <form action="<?= $action; ?>" method="post">
                        <div class="form-group">
                            <label><?= ucfirst($this->lang->line('service_name')) ?></label>
                            <input type="text" class="form-control" name="nome" id="nome" placeholder="<?= ucfirst($this->lang->line('service_name')) ?>" value="<?= $nome; ?>" />
                            <?= form_error('nome') ?>
                        </div>
                        <div class="form-group">
                            <label><?= ucfirst($this->lang->line('service_description')) ?></label>
                            <input type="text" class="form-control" name="descricao" id="descricao" placeholder="<?= ucfirst($this->lang->line('service_description')) ?>" value="<?= $descricao; ?>" />
                            <?= form_error('descricao') ?>
                        </div>
                        <div class="form-group">
                            <label><?= ucfirst($this->lang->line('service_price')) ?></label>
                            <input type="text" class="form-control" name="preco" id="preco" placeholder="<?= ucfirst($this->lang->line('service_price')) ?>" value="<?= $preco; ?>" />
                            <?= form_error('preco') ?>
                        </div>

                        <input type="hidden" name="idServicos" value="<?= $idServicos; ?>" />
                        <button type="submit" class="btn btn-info"><?= $button ?></button>
                        <a href="<?= site_url('servicos') ?>" class="btn btn-dark">
                           <i class="fa fa-reply"></i> <?= $this->lang->line('app_cancel'); ?>
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
