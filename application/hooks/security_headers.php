<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once APPPATH . 'helpers' . DIRECTORY_SEPARATOR . 'seguranca_cabecalhos_helper.php';

/**
 * Envia os cabeçalhos de segurança no início de toda requisição.
 *
 * Roda em pre_system, antes de qualquer controller, para que redirecionamentos,
 * exit() no meio do método, 404 e downloads também saiam com os cabeçalhos.
 */
function enviarCabecalhosSeguranca()
{
    if (headers_sent() || PHP_SAPI === 'cli') {
        return;
    }

    foreach (segurancaCabecalhos($_ENV, $_SERVER) as $nome => $valor) {
        header($nome . ': ' . $valor);
    }
}
