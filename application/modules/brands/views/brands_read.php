<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2><?= $this->lang->line('app_view'); ?> <?= $this->lang->line('brand'); ?> </h2>

        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <br>
        <div class="col-md-8 col-sm-8 col-xs-12">
            <h2><?= $this->lang->line('app_details'); ?></h2>


            <table class="table table-bordered table-striped">
        	    <tr><td><?= $this->lang->line('brand_name');  ?></td><td><?= $brand_name; ?></td></tr>
        	    <tr><td><?= $this->lang->line('app_active');  ?>?</td><td><?= $active ? print_label($this->lang->line('app_yes'), 'success') : print_label($this->lang->line('app_no'),'danger'); ?></td></tr>
        	    <tr><td><?= $this->lang->line('app_created');  ?></td><td><?= datetime_from_sql($created_at); ?></td></tr>
        	    <tr><td><?= $this->lang->line('app_updated');  ?></td><td><?= datetime_from_sql($updated_at); ?></td></tr>
        	</table>

        </div>
        
        <div class="col-md-2 col-sm-4 col-xs-12">
            <h2><?= $this->lang->line('app_actions'); ?></h2>
            <a href="<?= site_url('brands/create') ?>" class="btn btn-success col-md-12"><?= $this->lang->line('app_create'); ?></a>
            <a href="<?= site_url('brands/update/'.$id) ?>" class="btn btn-primary col-md-12"><?= $this->lang->line('app_edit'); ?></a>
            <a href="<?= site_url('brands') ?>" class="btn btn-default col-md-12"><?= $this->lang->line('app_back'); ?></a>
        </div>
    
       </div>
      </div>
   </div>
</div>