<?php

/**
 * Bootstrap da suíte de testes.
 *
 * Sobe o mínimo do CodeIgniter 3 necessário para exercitar classes de
 * application/ sem servidor web e sem banco externo: as constantes de path,
 * o Query Builder e o SQLite em memória.
 */

define('MAPOS_ROOT', dirname(__DIR__));
define('APPPATH', MAPOS_ROOT . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
define('BASEPATH', MAPOS_ROOT . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'codeigniter' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);

require MAPOS_ROOT . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// O Query Builder chama log_message() e show_error(); sob teste eles não têm
// para onde escrever, então viram stubs antes de o CI carregá-los.
if (! function_exists('log_message')) {
    function log_message($level, $message, $php_error = false)
    {
    }
}

if (! function_exists('show_error')) {
    function show_error($message, $status_code = 500, $heading = 'Erro')
    {
        throw new RuntimeException($heading . ': ' . $message);
    }
}

// html_escape() lê o charset por config_item(), que na versão do CI carrega o
// application/config/config.php inteiro e depende do .env. Sob teste basta o
// que as funções testadas consultam.
if (! function_exists('config_item')) {
    function config_item($item)
    {
        $config = ['charset' => 'UTF-8'];

        return $config[$item] ?? null;
    }
}

require BASEPATH . 'core' . DIRECTORY_SEPARATOR . 'Common.php';
require BASEPATH . 'database' . DIRECTORY_SEPARATOR . 'DB.php';

/**
 * Abre uma conexão nova do Query Builder em um SQLite em memória.
 *
 * O escopo é o mesmo do produção, com uma diferença: a produção roda MySQL e os
 * testes rodam SQLite. Isso muda detalhes de escape, então os testes verificam
 * que o payload sai como literal escapado e não como SQL executável, e não que
 * a string final seja idêntica byte a byte à do MySQL.
 */
function maposTestDatabase()
{
    $db = &DB([
        'dsn' => '',
        'hostname' => '',
        'username' => '',
        'password' => '',
        'database' => ':memory:',
        'dbdriver' => 'sqlite3',
        'dbprefix' => '',
        'pconnect' => false,
        'db_debug' => true,
        'cache_on' => false,
        'cachedir' => '',
        'char_set' => '',
        'dbcollat' => '',
        'swap_pre' => '',
        'encrypt' => false,
        'compress' => false,
        'stricton' => false,
        'failover' => [],
        'port' => '',
    ]);

    return $db;
}

// CI_Controller real exige a instância do framework. Os controllers testados
// não são instanciados, só funções puras chamadas via reflexão, então um stub
// vazio basta para que o arquivo do controller possa ser carregado.
if (! class_exists('CI_Controller', false)) {
    class CI_Controller
    {
    }
}

// Mesma ideia para as migrations: o CI_Migration real busca o banco na
// instância do framework. O stub expõe $db para o teste injetar o SQLite.
if (! class_exists('CI_Migration', false)) {
    class CI_Migration
    {
        /** @var object */
        public $db;
    }
}

require_once APPPATH . 'helpers' . DIRECTORY_SEPARATOR . 'general_helper.php';
require_once APPPATH . 'helpers' . DIRECTORY_SEPARATOR . 'financeiro_helper.php';
require_once APPPATH . 'helpers' . DIRECTORY_SEPARATOR . 'escape_helper.php';
require_once APPPATH . 'helpers' . DIRECTORY_SEPARATOR . 'tema_helper.php';
require_once MAPOS_ROOT . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'check-escape.php';
require_once APPPATH . 'helpers' . DIRECTORY_SEPARATOR . 'js_helper.php';
require_once APPPATH . 'helpers' . DIRECTORY_SEPARATOR . 'componente_helper.php';
require_once MAPOS_ROOT . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'check-inline-script.php';
require_once APPPATH . 'libraries' . DIRECTORY_SEPARATOR . 'Permission.php';
require_once APPPATH . 'controllers' . DIRECTORY_SEPARATOR . 'Login.php';
require_once APPPATH . 'core' . DIRECTORY_SEPARATOR . 'MY_Controller.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'MaposTestCase.php';