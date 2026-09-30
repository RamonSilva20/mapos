<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

A PHP Error was encountered

Severity:    <?= htmlspecialchars((string) $severity, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), "\n" ?>
Message:     <?= htmlspecialchars((string) $message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), "\n" ?>
Filename:    <?= htmlspecialchars((string) $filepath, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), "\n" ?>
Line Number: <?= htmlspecialchars((string) $line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>

<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === true) : ?>

Backtrace:
<?php	foreach (debug_backtrace() as $error) : ?>
<?php	  if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0) : ?>
	File: <?= htmlspecialchars((string) $error['file'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), "\n" ?>
	Line: <?= htmlspecialchars((string) $error['line'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), "\n" ?>
	Function: <?= htmlspecialchars((string) $error['function'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), "\n\n" ?>
<?php	  endif ?>
<?php	endforeach ?>

<?php endif ?>
