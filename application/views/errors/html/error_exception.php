<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<div style="border:1px solid #990000;padding-left:20px;margin:0 0 10px 0;">

<h4>An uncaught Exception was encountered</h4>

<p>Type: <?= get_class($exception) ?></p>
<p>Message: <?= esc($message) ?></p>
<p>Filename: <?= esc($exception->getFile()) ?></p>
<p>Line Number: <?= esc($exception->getLine()) ?></p>

<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === true) : ?>

	<p>Backtrace:</p>
	<?php foreach ($exception->getTrace() as $error) : ?>

		<?php if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0) : ?>

			<p style="margin-left:10px">
			File: <?= esc($error['file']) ?><br />
			Line: <?= esc($error['line']) ?><br />
			Function: <?= esc($error['function']) ?>
			</p>
		<?php endif ?>

	<?php endforeach ?>

<?php endif ?>

</div>