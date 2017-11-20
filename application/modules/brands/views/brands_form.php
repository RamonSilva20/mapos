<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2><?php echo $button ?> <?= $this->lang->line('brand');  ?> </h2>

        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <br>
        <form class="form-horizontal" action="<?php echo $action; ?>" method="post">
    	    <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="varchar"><?= $this->lang->line('brand_name');  ?></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" class="form-control" name="brand_name" id="brand_name" placeholder="<?= $this->lang->line('brand_name');  ?>" value="<?php echo $brand_name; ?>" />
                    <?php echo form_error('brand_name') ?>
                </div>
            </div>
    	    <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tinyint"><?= $this->lang->line('app_active');  ?></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" class="form-control" name="active" id="active" placeholder="<?= $this->lang->line('app_active');  ?>" value="<?php echo $active; ?>" />
                    <?php echo form_error('active') ?>
                </div>
            </div>
    	 
    	    <div class="ln_solid"></div>
    	    <div class="text-center"> <input type="hidden" name="id" value="<?php echo $id; ?>" /> 
    	    <button type="submit" class="btn btn-primary"><?php echo $button ?></button> 
    	    <a href="<?php echo site_url('brands') ?>" class="btn btn-default"><?php echo $this->lang->line('app_cancel'); ?></a> </div>
	     </form>
    
    </div>
    </div>
  </div>
</div>
