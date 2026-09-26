<div id="content">
<!--start-top-serch-->
  <div id="content-header">
   <div></div>
      <div id="breadcrumb">
        <a href="<?= base_url() ?>" title="Dashboard" class="tip-bottom"> Início</a>
        <?php if ($this->uri->segment(1) != null) { ?>
            <a href="<?= base_url() . 'index.php/' . $this->uri->segment(1) ?>" class="tip-bottom" title="<?= ucfirst($this->uri->segment(1)); ?>">
              <?= ucfirst($this->uri->segment(1)); ?>
            </a>
          <?php if ($this->uri->segment(2) != null) { ?>
            <a href="<?= base_url() . 'index.php/' . $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/' . $this->uri->segment(3) ?>" class="current tip-bottom" title="<?= ucfirst($this->uri->segment(2)); ?>">
              <?= ucfirst($this->uri->segment(2));
          } ?>
            </a>
          <?php } ?>
      </div>
    </div>
    <div class="container-flu">
      <div class="row-fluid">
        <div class="span12">
          <?php if ($var = $this->session->flashdata('success')): ?>
              <script>Swal.fire({ icon: 'success', title: 'Sucesso!', text: <?= esc_msg($var) ?> });</script>
          <?php endif; ?>
          <?php if ($var = $this->session->flashdata('error')): ?>
              <script>Swal.fire({ icon: 'error', title: 'Falha!', text: <?= esc_msg($var) ?> });</script>
          <?php endif; ?>
          <?php if (isset($view)) {
              echo $this->load->view($view, null, true);
          } ?>
        </div>
      </div>
    </div>
  </div>
