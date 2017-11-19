<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2><?php echo $this->lang->line('app_view'); ?> Brands</h2>

        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <br>
        <div class="col-md-8 col-sm-8 col-xs-12">
            <h2><?php echo $this->lang->line('app_details'); ?></h2>


            <table class="table table-bordered table-striped">
	    <tr><td>Brand Name</td><td><?php echo $brand_name; ?></td></tr>
	    <tr><td>Active</td><td><?php echo $active; ?></td></tr>
	    <tr><td>Created At</td><td><?php echo $created_at; ?></td></tr>
	    <tr><td>Update At</td><td><?php echo $update_at; ?></td></tr>
	</table>

        </div>
        
        <div class="col-md-2 col-sm-4 col-xs-12">
            <h2><?php echo $this->lang->line('app_actions'); ?></h2>
            <a href="<?php echo site_url('brands/create') ?>" class="btn btn-success col-md-12"><?php echo $this->lang->line('app_create'); ?></a>
            <a href="<?php echo site_url('brands/update/'.$id) ?>" class="btn btn-primary col-md-12"><?php echo $this->lang->line('app_edit'); ?></a>
            <a href="<?php echo site_url('brands') ?>" class="btn btn-default col-md-12"><?php echo $this->lang->line('app_back'); ?></a>
        </div>
    
       </div>
      </div>
   </div>
</div>