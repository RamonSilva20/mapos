<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Recebe os relatórios da Content-Security-Policy em report-only (#2867).
 *
 * Não estende MY_Controller: o navegador envia o relatório sem a sessão de
 * login, inclusive da tela de login e da área do cliente. Por isso a rota
 * aceita só POST com o content-type de relatório, limita o tamanho e nunca
 * devolve o que recebeu. A rota está em csrf_exclude_uris.
 *
 * As violações ficam agregadas em application/logs/csp-violacoes.json, e cada
 * violação nova também vai para o log do CodeIgniter.
 */
class Csp extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('seguranca_cabecalhos');
    }

    public function report()
    {
        // Lê um byte a mais que o limite só para saber se passou dele.
        $corpo = (string) file_get_contents('php://input', false, null, 0, SEGURANCA_CSP_RELATORIO_MAX + 1);

        $recusa = segurancaCspRecusa(
            (string) $this->input->method(),
            (string) $this->input->server('CONTENT_TYPE'),
            $corpo
        );

        if ($recusa !== 0) {
            $this->output->set_status_header($recusa)->set_output('');

            return;
        }

        $novas = segurancaCspRegistrar(
            segurancaCspLerRelatorio($corpo),
            APPPATH . 'logs' . DIRECTORY_SEPARATOR . 'csp-violacoes.json',
            time()
        );

        foreach ($novas as $chave) {
            log_message('error', 'CSP report-only, violação nova: ' . $chave);
        }

        $this->output->set_status_header(204)->set_output('');
    }
}
