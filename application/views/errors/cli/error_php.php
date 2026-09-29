<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

A PHP Error was encountered

Severity:    <?= esc($severity), "\n" ?>
Message:     <?= esc($message), "\n" ?>
Filename:    <?= esc($filepath), "\n" ?>
Line Number: <?= esc($line) ?>

<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === true) : ?>

Backtrace:
<?php	foreach (debug_backtrace() as $error) : ?>
<?php	  if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0) : ?>
	File: <?= esc($error['file']), "\n" ?>
	Line: <?= esc($error['line']), "\n" ?>
	Function: <?= esc($error['function']), "\n\n" ?>
<?php	  endif ?>
<?php	endforeach ?>

<?php endif ?>
