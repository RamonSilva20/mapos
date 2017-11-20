<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2><?php echo $this->lang->line('app_view'); ?> Services</h2>

        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <br>
        <div class="col-md-8 col-sm-8 col-xs-12">
            <h2><?php echo $this->lang->line('app_details'); ?></h2>


            <table class="table table-bordered table-striped">
	    <tr><td>Service Name</td><td><?php echo $service_name; ?></td></tr>
	    <tr><td>Price</td><td><?php echo $price; ?></td></tr>
	    <tr><td>Active</td><td><?php echo $active; ?></td></tr>
	    <tr><td>Created At</td><td><?php echo $created_at; ?></td></tr>
	    <tr><td>Updated At</td><td><?php echo $updated_at; ?></td></tr>
	</table>

        </div>
        
        <div class="col-md-2 col-sm-4 col-xs-12">
            <h2><?php echo $this->lang->line('app_actions'); ?></h2>
            <a href="<?php echo site_url('services/create') ?>" class="btn btn-success col-md-12"><?php echo $this->lang->line('app_create'); ?></a>
            <a href="<?php echo site_url('services/update/'.$id) ?>" class="btn btn-primary col-md-12"><?php echo $this->lang->line('app_edit'); ?></a>
            <a href="<?php echo site_url('services') ?>" class="btn btn-default col-md-12"><?php echo $this->lang->line('app_back'); ?></a>
        </div>
    
       </div>
      </div>
   </div>
</div>