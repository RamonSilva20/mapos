<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2><?php echo $button ?> <?= $this->lang->line('person'); ?> </h2>

        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <br>
        <form class="form-horizontal" action="<?php echo $action; ?>" method="post">
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tinyint"><?= $this->lang->line('company'); ?>?</label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="company" id="company" placeholder="<?= $this->lang->line('company'); ?>" value="<?php echo $company; ?>" />
                <?php echo form_error('company') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="varchar"><?= $this->lang->line('name'); ?></label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="name" id="name" placeholder="<?= $this->lang->line('name'); ?>" value="<?php echo $name; ?>" />
                <?php echo form_error('name') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="varchar"><?= $this->lang->line('company_name'); ?></label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="company_name" id="company_name" placeholder="<?= $this->lang->line('company_name'); ?>" value="<?php echo $company_name; ?>" />
                <?php echo form_error('company_name') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="varchar">CPF/CNPJ</label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="cpf_cnpj" id="cpf_cnpj" placeholder="CPF/CNPJ" value="<?php echo $cpf_cnpj; ?>" />
                <?php echo form_error('cpf_cnpj') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="varchar">RG/IE</label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="rg_ie" id="rg_ie" placeholder="RG/IE" value="<?php echo $rg_ie; ?>" />
                <?php echo form_error('rg_ie') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="varchar"><?= $this->lang->line('phone'); ?></label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="phone" id="phone" placeholder="<?= $this->lang->line('phone'); ?>" value="<?php echo $phone; ?>" />
                <?php echo form_error('phone') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="varchar"><?= $this->lang->line('celphone'); ?></label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="celphone" id="celphone" placeholder="<?= $this->lang->line('celphone'); ?>" value="<?php echo $celphone; ?>" />
                <?php echo form_error('celphone') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="varchar"><?= $this->lang->line('email'); ?></label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="email" id="email" placeholder="<?= $this->lang->line('email'); ?>" value="<?php echo $email; ?>" />
                <?php echo form_error('email') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="varchar"><?= $this->lang->line('image'); ?></label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="image" id="image" placeholder="<?= $this->lang->line('image'); ?>" value="<?php echo $image; ?>" />
                <?php echo form_error('image') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="obs"><?= $this->lang->line('obs'); ?> </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <textarea class="form-control" rows="3" name="obs" id="obs" placeholder="<?= $this->lang->line('obs'); ?>"><?php echo $obs; ?></textarea>
                <?php echo form_error('obs') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tinyint"><?= $this->lang->line('app_active'); ?></label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="active" id="active" placeholder="<?= $this->lang->line('app_active'); ?>" value="<?php echo $active; ?>" />
                <?php echo form_error('active') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tinyint"><?= $this->lang->line('client'); ?>?</label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="client" id="client" placeholder="<?= $this->lang->line('client'); ?>?" value="<?php echo $client; ?>" />
                <?php echo form_error('client') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tinyint"><?= $this->lang->line('supplier'); ?>?</label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="supplier" id="supplier" placeholder="<?= $this->lang->line('supplier'); ?>?" value="<?php echo $supplier; ?>" />
                <?php echo form_error('supplier') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tinyint"><?= $this->lang->line('employee'); ?>?</label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="employee" id="employee" placeholder="<?= $this->lang->line('employee'); ?>?" value="<?php echo $employee; ?>" />
                <?php echo form_error('employee') ?>
            </div>
        </div>
	    <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tinyint"><?= $this->lang->line('shipping_company'); ?>?</label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="shipping_company" id="shipping_company" placeholder="<?= $this->lang->line('shipping_company'); ?>?" value="<?php echo $shipping_company; ?>" />
                <?php echo form_error('shipping_company') ?>
            </div>
        </div>

	    <div class="ln_solid"></div>
	    <div class="text-center"> <input type="hidden" name="id" value="<?php echo $id; ?>" /> 
	    <button type="submit" class="btn btn-primary"><?php echo $button ?></button> 
	    <a href="<?php echo site_url('persons') ?>" class="btn btn-default"><?php echo $this->lang->line('app_cancel'); ?></a> </div>
	</form>
    
    </div>
    </div>
  </div>
</div>
