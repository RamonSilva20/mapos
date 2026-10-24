<?php

require __DIR__ . '/instalador.php';

// Instalado: só uma página de aviso, sem versão do PHP, requisitos nem caminhos.
if (instaladorInstalado()) {
    instaladorBloquear();
}

define('INSTALADOR', true);

$settings_file = __DIR__ . DIRECTORY_SEPARATOR . 'settings.json';

if (! file_exists($settings_file)) {
    exit('Arquivo de configuração não encontrado!');
} else {
    $contents = file_get_contents($settings_file);
    $settings = json_decode($contents, true);
}

$php_version_success = false;
$allow_url_fopen_success = false;

$php_version_required = '8.5';
$current_php_version = PHP_VERSION;

//check required php version
if (version_compare($current_php_version, $php_version_required) >= 0) {
    $php_version_success = true;
}

//check allow_url_fopen
if (ini_get('allow_url_fopen')) {
    $allow_url_fopen_success = true;
}

//check the Composer dependencies (application/vendor): without them neither the system nor the migrations start
$vendor_success = instaladorTemDependencias();

//check if all requirement is success
if ($php_version_success && $allow_url_fopen_success && $vendor_success) {
    $all_requirement_success = true;
} else {
    $all_requirement_success = false;
}

foreach ($settings['extensions'] as $value) {
    if (! extension_loaded($value)) {
        $all_requirement_success = false;
    }
}

foreach ($settings['writeable_directories'] as $value) {
    if (! is_writable('..' . $value)) {
        $all_requirement_success = false;
    }
}

$dashboard_url = $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
$dashboard_url = preg_replace('/install.*/', '', $dashboard_url); //remove everything after index.php
if (! empty($_SERVER['HTTPS'])) {
    $dashboard_url = 'https://' . $dashboard_url;
} else {
    $dashboard_url = 'http://' . $dashboard_url;
}

$storage_sugerido = instaladorSugerirArmazenamento();

$installed = false;

include 'view/index.php';
