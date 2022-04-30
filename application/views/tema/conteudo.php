<div id="content">
    <!--start-top-serch-->
    <div id="content-header">
        <div></div>
        <div id="breadcrumb">
            <a href="<?php echo base_url(); ?>" title="Dashboard" class="tip-bottom"> Início</a>
            <?php if ($this->uri->segment(1) != null) { ?>
                <a href="<?php echo base_url() . 'index.php/' . $this->uri->segment(1); ?>" class="tip-bottom" title="<?php echo ucfirst($this->uri->segment(1)); ?>">
                    <?php echo ucfirst($this->uri->segment(1)); ?>
                </a>
                <?php if ($this->uri->segment(2) != null) { ?>
                    <a href="<?php echo base_url() . 'index.php/' . $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/' . $this->uri->segment(3); ?>" class="current tip-bottom" title="<?php echo ucfirst($this->uri->segment(2)); ?>">
                    <?php echo ucfirst($this->uri->segment(2));
                } ?>
                    </a>
                <?php } ?>
        </div>
    </div>
    <div class="container-flu">
        <div class="row-fluid">
            <div class="span12">
                <?php if ($var = $this->session->flashdata('success')) : ?><script>
                        swal("Sucesso!", "<?php echo str_replace('"', '', $var); ?>", "success");
                    </script><?php endif; ?>
                <?php if ($var = $this->session->flashdata('error')) : ?><script>
                        swal("Falha!", "<?php echo str_replace('"', '', $var); ?>", "error");
                    </script><?php endif; ?>
                <?php if (isset($view)) {
                    echo $this->load->view($view, null, true);
                } ?>
            </div>
        </div>
    </div>
</div>
