<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

An uncaught Exception was encountered

Type:        <?= get_class($exception), "\n" ?>
Message:     <?= htmlspecialchars((string) $message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), "\n" ?>
Filename:    <?= htmlspecialchars((string) $exception->getFile(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), "\n" ?>
Line Number: <?= htmlspecialchars((string) $exception->getLine(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>

<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === true) : ?>

Backtrace:
<?php	foreach ($exception->getTrace() as $error) : ?>
<?php	  if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0) : ?>
	File: <?= htmlspecialchars((string) $error['file'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), "\n" ?>
	Line: <?= htmlspecialchars((string) $error['line'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), "\n" ?>
	Function: <?= htmlspecialchars((string) $error['function'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), "\n\n" ?>
<?php	  endif ?>
<?php	endforeach ?>

<?php endif ?>
