<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

// compress output
$hook['display_override'][] = [
    'class' => '',
    'function' => 'compress',
    'filename' => 'compress.php',
    'filepath' => 'hooks',
];

$hook['pre_system'][] = [
    'class' => 'WhoopsHook',
    'function' => 'bootWhoops',
    'filename' => 'whoops.php',
    'filepath' => 'hooks',
    'params' => [],
];

// Tenta criar wrappers antes do Router (funciona se application/controllers/ for gravável)
$hook['pre_system'][] = [
    'function' => 'ensure_module_wrappers',
    'filename'  => 'module_wrappers.php',
    'filepath'  => 'hooks',
];

// Fallback: carrega controller do módulo diretamente sem precisar de arquivo wrapper
$hook['pre_controller'][] = [
    'function' => 'load_module_controller',
    'filename'  => 'module_wrappers.php',
    'filepath'  => 'hooks',
];

/* End of file hooks.php */
/* Location: ./application/config/hooks.php */
