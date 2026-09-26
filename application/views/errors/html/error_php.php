<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<div style="border:1px solid #990000;padding-left:20px;margin:0 0 10px 0;">

<h4>A PHP Error was encountered</h4>

<p>Severity: <?php echo esc($severity); ?></p>
<p>Message:  <?php echo esc($message); ?></p>
<p>Filename: <?php echo esc($filepath); ?></p>
<p>Line Number: <?php echo esc($line); ?></p>

<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === true) : ?>

	<p>Backtrace:</p>
	<?php foreach (debug_backtrace() as $error) : ?>

		<?php if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0) : ?>

			<p style="margin-left:10px">
			File: <?php echo esc($error['file']); ?><br />
			Line: <?php echo esc($error['line']); ?><br />
			Function: <?php echo $error['function'] ?>
			</p>

		<?php endif ?>

	<?php endforeach ?>

<?php endif ?>

</div>