<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2><?php echo $this->lang->line('app_view'); ?> <?= $this->lang->line('person'); ?></h2>

        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <br>
        <div class="col-md-8 col-sm-8 col-xs-12">
            <h2><?php echo $this->lang->line('app_details'); ?></h2>


            <table class="table table-bordered table-striped">
        	    <tr><td><?= $this->lang->line('company'); ?></td><td><?php echo $company; ?></td></tr>
        	    <tr><td><?= $this->lang->line('name'); ?></td><td><?php echo $name; ?></td></tr>
        	    <tr><td><?= $this->lang->line('company_name'); ?></td><td><?php echo $company_name; ?></td></tr>
        	    <tr><td>CPF/CNPJ</td><td><?php echo $cpf_cnpj; ?></td></tr>
        	    <tr><td>RG/IE</td><td><?php echo $rg_ie; ?></td></tr>
        	    <tr><td><?= $this->lang->line('phone'); ?></td><td><?php echo $phone; ?></td></tr>
        	    <tr><td><?= $this->lang->line('celphone'); ?></td><td><?php echo $celphone; ?></td></tr>
        	    <tr><td><?= $this->lang->line('email'); ?></td><td><?php echo $email; ?></td></tr>
        	    <tr><td><?= $this->lang->line('image'); ?></td><td><?php echo $image; ?></td></tr>
        	    <tr><td><?= $this->lang->line('obs'); ?></td><td><?php echo $obs; ?></td></tr>
        	    <tr><td><?= $this->lang->line('app_active'); ?></td><td><?php echo $active; ?></td></tr>
        	    <tr><td><?= $this->lang->line('client'); ?>?</td><td><?php echo $client; ?></td></tr>
        	    <tr><td><?= $this->lang->line('supplier'); ?>?</td><td><?php echo $supplier; ?></td></tr>
        	    <tr><td><?= $this->lang->line('employee'); ?>?</td><td><?php echo $employee; ?></td></tr>
        	    <tr><td><?= $this->lang->line('shipping_company'); ?></td><td><?php echo $shipping_company; ?></td></tr>
        	    <tr><td><?= $this->lang->line('app_created'); ?></td><td><?= datetime_from_sql($created_at); ?></td></tr>
        	    <tr><td><?= $this->lang->line('app_updated'); ?></td><td><?= datetime_from_sql($updated_at); ?></td></tr>
        	</table>

        </div>
        
        <div class="col-md-2 col-sm-4 col-xs-12">
            <h2><?php echo $this->lang->line('app_actions'); ?></h2>
            <a href="<?php echo site_url('persons/create') ?>" class="btn btn-success col-md-12"><?php echo $this->lang->line('app_create'); ?></a>
            <a href="<?php echo site_url('persons/update/'.$id) ?>" class="btn btn-primary col-md-12"><?php echo $this->lang->line('app_edit'); ?></a>
            <a href="<?php echo site_url('persons') ?>" class="btn btn-default col-md-12"><?php echo $this->lang->line('app_back'); ?></a>
        </div>
    
       </div>
      </div>
   </div>
</div>