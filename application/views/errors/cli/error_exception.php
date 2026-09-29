<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

An uncaught Exception was encountered

Type:        <?= get_class($exception), "\n" ?>
Message:     <?= esc($message), "\n" ?>
Filename:    <?= esc($exception->getFile()), "\n" ?>
Line Number: <?= esc($exception->getLine()) ?>

<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === true) : ?>

Backtrace:
<?php	foreach ($exception->getTrace() as $error) : ?>
<?php	  if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0) : ?>
	File: <?= esc($error['file']), "\n" ?>
	Line: <?= esc($error['line']), "\n" ?>
	Function: <?= esc($error['function']), "\n\n" ?>
<?php	  endif ?>
<?php	endforeach ?>

<?php endif ?>
