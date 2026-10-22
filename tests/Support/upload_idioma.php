<?php

/**
 * Roda a CI_Lang e a CI_Upload reais, num processo à parte, e imprime a
 * mensagem de "tipo de arquivo não permitido" (ArmazenamentoTest, #2872).
 *
 * Uso: php upload_idioma.php <raiz-do-projeto> <antigo|armazenamentoReceber>
 *
 * - antigo: lang->load('upload', 'pt-br') antes do envio, como a primeira
 *   versão da correção; mostra que set_error() desfaz a tradução.
 * - armazenamentoReceber: o helper que o painel e a API usam.
 */
[, $raiz, $modo] = $argv;

define('BASEPATH', $raiz . '/application/vendor/codeigniter/framework/system/');
define('APPPATH', $raiz . '/application/');
define('FCPATH', $raiz . '/');
define('ENVIRONMENT', 'testing');

function &get_config(array $replace = [])
{
    static $config = ['language' => 'english'];

    return $config;
}

function log_message($level, $message)
{
}

function show_error($message)
{
    throw new RuntimeException($message);
}

function &get_mimes()
{
    static $mimes = [];

    return $mimes;
}

function &get_instance()
{
    global $ci;

    return $ci;
}

require BASEPATH . 'core/Lang.php';
require BASEPATH . 'libraries/Upload.php';
require APPPATH . 'helpers/armazenamento_helper.php';

// Como o CI_Config real: $config é uma referência ao get_config().
$ci = new stdClass();
$ci->lang = new CI_Lang();
$ci->load = new class() {
    public function get_package_paths($include_base = false)
    {
        return [APPPATH];
    }
};

// do_upload() real exige is_uploaded_file(); aqui só interessa o erro, que
// passa pelo set_error() real, o mesmo que rejeita um PNG com conteúdo de PHP.
$upload = new class() extends CI_Upload {
    public function do_upload($field = 'userfile')
    {
        $this->set_error('upload_invalid_filetype');

        return false;
    }
};

if ($modo === 'antigo') {
    $ci->lang->load('upload', 'pt-br');
    $upload->do_upload();
} else {
    armazenamentoReceber($upload, 'userfile');
}

echo trim(strip_tags($upload->display_errors('', ''))), "\n";
echo 'idioma depois: ', get_config()['language'], "\n";
