<div id="content">
    <div id="content-header">
      <div id="breadcrumb"> <a href="<?= base_url() ?>" title="Dashboard" class="tip-bottom"><i class="fas fa-home"></i> Dashboard</a>
        <?php if ($this->uri->segment(1) != null) { ?>
            <a href="<?= base_url() . 'index.php/' . $this->uri->segment(1) ?>" class="tip-bottom" title="<?= ucfirst($this->uri->segment(1)); ?>">
              <?= ucfirst($this->uri->segment(1)); ?>
            </a>
          <?php if ($this->uri->segment(2) != null) { ?>
            <a href="<?= base_url() . 'index.php/' . $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/' . $this->uri->segment(3) ?>" class="current tip-bottom" title="<?= ucfirst($this->uri->segment(2)); ?>">
              <?= ucfirst($this->uri->segment(2)); } ?>
            </a>
          <?php } ?>
      </div>
    </div>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span12">
          <?php if ($this->session->flashdata('error') != null) { ?>
            <div class="alert alert-danger">
              <button type="button" class="close" data-dismiss="alert">&times;</button>
              <?= $this->session->flashdata('error'); ?>
            </div>
          <?php
          } ?>
          <?php if ($this->session->flashdata('success') != null) { ?>
            <div class="alert alert-success">
              <button type="button" class="close" data-dismiss="alert">&times;</button>
              <?= $this->session->flashdata('success'); ?>
            </div>
          <?php
          } ?>
          <?php if (isset($view)) {
              echo $this->load->view($view, null, true);
          } ?>
        </div>
      </div>
    </div>
  </div>
