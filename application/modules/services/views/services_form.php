<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2><?php echo $button ?> <?= $this->lang->line('service') ?> </h2>

        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <br>
        <form class="form-horizontal" action="<?php echo $action; ?>" method="post">
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="varchar"><?= $this->lang->line('service_name') ?></label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="service_name" id="service_name" placeholder="<?= $this->lang->line('service_name') ?>" value="<?php echo $service_name; ?>" />
                <?php echo form_error('service_name') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="decimal"><?= $this->lang->line('service_price') ?></label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control money" name="price" id="price" placeholder="<?= $this->lang->line('service_price') ?>" value="<?php echo $price; ?>" />
                <?php echo form_error('price') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tinyint"><?= $this->lang->line('app_active') ?></label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <select class="form-control" name="active" id="active">
                    <option value="1" <?php echo $active ? 'selected' : ''; ?> ><?= $this->lang->line('app_yes') ?></option>
                    <option value="0" <?php echo !$active ? 'selected' : '' ?>><?= $this->lang->line('app_no') ?></option>
                </select>
                <?php echo form_error('active') ?>
            </div>
        </div>
	    <div class="ln_solid"></div>
	    <div class="text-center"> <input type="hidden" name="id" value="<?php echo $id; ?>" /> 
	    <button type="submit" class="btn btn-primary"><?php echo $button ?></button> 
	    <a href="<?php echo site_url('services') ?>" class="btn btn-default"><?php echo $this->lang->line('app_cancel'); ?></a> </div>
	</form>
    
    </div>
    </div>
  </div>
</div>


<!-- Maskmoney -->
<script src="<?php echo base_url(); ?>assets/js/maskmoney.min.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        
        $(".money").maskMoney({allowNegative: false, thousands:'', decimal:'.', affixesStay: false});

    });
</script>