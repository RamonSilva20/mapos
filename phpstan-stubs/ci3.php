<?php

/**
 * Stubs do CodeIgniter 3 para o PHPStan.
 *
 * O CI3 injeta as bibliotecas em $this por assignação dinâmica, em
 * Controller::__construct(), a partir do autoload. Nada disso é visível
 * para análise estática, então o PHPStan reporta ~3900 "propriedade não
 * definida" e ~770 "função não encontrada" que não são bugs.
 *
 * Este arquivo declara o contrato real do framework: as propriedades
 * injetadas na instância e as funções globais. Não é carregado em runtime.
 */

// Os tipos das propriedades são escritos como objetos genéricos de propósito.
// As classes reais do CI3 (CI_DB_driver, CI_Input, ...) só existem se o
// vendor estiver|autoloadado, e citá-las aqui faz o PHPStan reclamar que o
// tipo é desconhecido -- o que quebra o stub inteiro.
class CI_Controller
{
    // Vazio no CI3 real, mas as subclasses chamam parent::__construct().
    public function __construct()
    {
    }

    /** @var object */
    public $db;

    /** @var object */
    public $input;

    /** @var object */
    public $session;

    /** @var object */
    public $load;

    /** @var object */
    public $uri;

    /** @var object */
    public $config;

    /** @var object */
    public $output;

    /** @var object */
    public $router;

    /** @var object */
    public $security;

    /** @var object */
    public $form_validation;

    /** @var object */
    public $pagination;

    /** @var object */
    public $email;

    /** @var object */
    public $permission;

    /** @var mixed */
    public $data;
}

/**
 * Nunca retorna: no CI3 a função chama exit() no fim.
 *
 * Declarar never importa: sem isso o PHPStan acha que o código depois de um
 * redirect() é alcançável e não aponta os blocos inalcançáveis que existem
 * logo abaixo de um redirect() nos controllers.
 *
 * @return never
 */
function redirect($uri = '', $method = 'auto', $code = null)
{
    exit;
}

function base_url($uri = '', $ssl = false)
{
}

function site_url($uri = '', $ssl = false)
{
}

function current_url()
{
}

function is_https()
{
}

function show_404($page = '', $log_error = true)
{
}

function show_error($message, $status_code = 500, $heading = 'Error')
{
}

function log_message($level, $message, $php_error = false)
{
}

function html_escape($var)
{
}

function is_php($file = '')
{
}

function get_mime_by_extension($filename, $empty = 'application/octet-stream')
{
}

function is_really_writable($file)
{
}

function force_download($filename = '', $data = '', $set_mime = false)
{
}

function write_file($path, $data, $mode = 'wb')
{
}

function delete_files($path, $del_dir = false, $htdocs = false, $_level = 0)
{
}

function load_class($class, $directory = 'libraries')
{
}

function &get_instance()
{
}

function get_config($file, $index = '', $xss_clean = false)
{
}

/** @return CI_Controller */
function &re_get_instance()
{
}

// CI_Model recebe as mesmas bibliotecas injetadas na instância que o
// controller. Sem esta classe, todo model reporta $db e $input inexistentes.
//
// O construtor é declarado porque many subclasses do Map-OS chamam
// parent::__construct(), e sem ele o PHPStan acusa "undefined static method
// __construct()". No CI3 real ele não faz nada.
class CI_Model
{
    public function __construct()
    {
    }
    /** @var object */
    public $db;

    /** @var object */
    public $input;

    /** @var object */
    public $session;

    /** @var object */
    public $load;

    /** @var object */
    public $config;

    /** @var object */
    public $validation;
}

// Constantes definidas pelo index.php do CI3 e pelo core, antes de qualquer
// controller carregar.
const BASEPATH = '/var/www/html/';
const APPPATH = '/var/www/html/application/';
const FCPATH = '/var/www/html/';
const SELF = 'index.php';
const VIEWPATH = APPPATH . 'views/';
const WRITEPATH = FCPATH;
const SYSDIR = BASEPATH . 'system/';

// Escrita de arquivo. O valor é só um default; o chamador pode sobrescrever.
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0755);

// CI_Security é a classe que MY_Security estende. Só as propriedades do CSRF
// que a subclasse usa.
class CI_Security
{
    /** @var string */
    protected $_csrf_hash;

    /** @var int */
    protected $_csrf_expire = 7200;

    /** @var string */
    protected $_csrf_cookie_name = 'ci_csrf_token';
}

/**
 * @return mixed
 */
function config_item($item)
{
}

// CI_Email é a classe que MY_Email estende. Vazia porque o PHPStan só precisa
// que o nome exista paraMY_Email resolver.
class CI_Email
{
    public function __construct()
    {
    }

    /**
     * MY_Email::send() chama parent::send(). O retorno é o que o caller
     * espera: o CI3 devolve a instância para permitir encadeamento.
     *
     * @return static
     */
    public function send($auto_clear = true)
    {
        return $this;
    }

    /** @var string */
    public $from;

    /** @var string */
    public $to;

    /** @var string */
    public $subject;

    /** @var string */
    public $message;

    /** @var array */
    public $_headers = [];

    /** @var array */
    public $_recipients = [];

    /** @var array */
    public $_cc_array = [];
}

// O SDK do Gerencianet é carregado em runtime por arquivo, sem autoload, então
// o PHPStan não encontra a classe. Declarada vazia para silenciar os usos.
class Gerencianet
{
}

// Helpers do Map-OS (application/helpers).
function set_value($field = '', $default = '')
{
}

function validation_errors($prefix = '', $suffix = '')
{
}

function singular($str)
{
}

function humanize($str)
{
}
