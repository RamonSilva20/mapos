<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2><?php echo $button ?> Services </h2>

        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <br>
        <form class="form-horizontal" action="<?php echo $action; ?>" method="post">
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="varchar">Service Name</label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="service_name" id="service_name" placeholder="Service Name" value="<?php echo $service_name; ?>" />
                <?php echo form_error('service_name') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="decimal">Price</label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="price" id="price" placeholder="Price" value="<?php echo $price; ?>" />
                <?php echo form_error('price') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tinyint">Active</label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="active" id="active" placeholder="Active" value="<?php echo $active; ?>" />
                <?php echo form_error('active') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="datetime">Created At</label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="created_at" id="created_at" placeholder="Created At" value="<?php echo $created_at; ?>" />
                <?php echo form_error('created_at') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="datetime">Updated At</label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="updated_at" id="updated_at" placeholder="Updated At" value="<?php echo $updated_at; ?>" />
                <?php echo form_error('updated_at') ?>
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
