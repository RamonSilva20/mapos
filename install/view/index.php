<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?php echo($settings['title']); ?>">
  <meta name="author" content="Rodrigo Ribeiro - me@rodrigo3d.com">
  <link rel="shortcut icon" href="<?php echo $dashboard_url; ?>install/assets/images/favicon.ico" type="image/x-icon" />

  <title><?php echo($settings['title']); ?></title>
  <link rel='stylesheet' type='text/css' href='<?php echo $dashboard_url; ?>install/assets/css/bootstrap.min.css' />
  <link rel='stylesheet' type='text/css' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css' />

  <link rel='stylesheet' type='text/css' href='<?php echo $dashboard_url; ?>install/assets/css/install.css' />
  <link rel='stylesheet' type='text/css' href='<?php echo $dashboard_url; ?>install/assets/css/custom.css' />

  <script type='text/javascript' src='<?php echo $dashboard_url; ?>install/assets/js/jquery.min.js'></script>
  <script type='text/javascript' src='<?php echo $dashboard_url; ?>install/assets/js/jquery.validate.min.js'></script>
  <script type='text/javascript' src='<?php echo $dashboard_url; ?>install/assets/js/jquery.form.js'></script>

</head>

<body>
  <div class="install-box">
    <div class="panel panel-install">

      <div class="panel-heading text-center">
        <img id="logo" class="card-img-top" src="<?php echo $dashboard_url; ?>assets/img/logo.png" title="Logo" />
        <h3><?php echo($settings['title']); ?></h3>
      </div>

      <div class="panel-body no-padding">

        <?php if (!$installed) : ?>
          <div class="tab-container clearfix">
            <div id="pre-installation" class="tab-title col-sm-4 active">
              <i class="fa fa-circle-o"></i>
              <strong> Pré Instalação</strong>
            </div>
            <div id="configuration" class="tab-title col-sm-4">
              <i class="fa fa-circle-o"></i>
              <strong> Configuração</strong>
            </div>
            <div id="finished" class="tab-title col-sm-4">
              <i class="fa fa-circle-o"></i>
              <strong> Finalização</strong>
            </div>
          </div>
        <?php endif; ?>

        <div id="alert-container"></div>

        <div class="tab-content">
          <div role="tabpanel" class="tab-pane active" id="pre-installation-tab">
            <?php include_once "pre-installation.php"; ?>
          </div>
          <div role="tabpanel" class="tab-pane " id="configuration-tab">
            <?php include_once "configuration.php"; ?>
          </div>
          <div role="tabpanel" class="tab-pane " id="finished-tab">
            <?php include_once "finished.php"; ?>
          </div>
        </div>

      </div>

    </div>
  </div>

  <footer class="footer">
    <div class="container">
      <p class="text-muted">
        <a href="<?php echo $dashboard_url; ?>">&bull;</a>
      </p>
    </div>
  </footer>

  <script type="text/javascript">

    <?php if ($installed) : ?>
      $("#pre-installation-tab").removeClass('active');
      $("#finished-tab").addClass('active');
    <?php endif; ?>

</script>
<script src="assets/js/main.js"></script>

</body>

</html>
