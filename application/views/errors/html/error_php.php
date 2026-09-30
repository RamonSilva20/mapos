<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div style="border:1px solid #990000;padding-left:20px;margin:0 0 10px 0;">

<h4>A PHP Error was encountered</h4>

<p>Severity: <?= htmlspecialchars((string) $severity, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
<p>Message:  <?= htmlspecialchars((string) $message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
<p>Filename: <?= htmlspecialchars((string) $filepath, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
<p>Line Number: <?= htmlspecialchars((string) $line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>

<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === true) : ?>

	<p>Backtrace:</p>
	<?php foreach (debug_backtrace() as $error) : ?>

		<?php if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0) : ?>

			<p style="margin-left:10px">
			File: <?= htmlspecialchars((string) $error['file'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><br />
			Line: <?= htmlspecialchars((string) $error['line'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><br />
			Function: <?= htmlspecialchars((string) $error['function'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
			</p>

		<?php endif ?>

	<?php endforeach ?>

<?php endif ?>

</div>
