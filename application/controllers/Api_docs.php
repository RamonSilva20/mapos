<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Libraries\Api\OpenApi;
use Libraries\Api\Rotas;

class Api_docs extends CI_Controller
{
    private const CSP = "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob: https:; font-src 'self' data:; connect-src 'self'; worker-src 'self' blob:; frame-ancestors 'self'; base-uri 'self'; form-action 'self'; object-src 'none'";

    public function __construct()
    {
        parent::__construct();

        if (! apiHabilitada()) {
            show_404();
        }
    }

    public function index()
    {
        header_remove('Content-Security-Policy-Report-Only');
        header('Content-Security-Policy: ' . self::CSP, true);

        $this->load->view('api/docs', [
            'especificacao' => site_url('api/docs/openapi.json'),
            'titulo' => 'API do Map-OS',
        ]);
    }

    public function openapi()
    {
        $this->load->helper(Rotas::HELPERS);

        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_header('Access-Control-Allow-Origin: *')
            ->set_header('Cache-Control: public, max-age=300')
            ->set_output(json_encode(
                OpenApi::gerar(Rotas::roteador(), base_url(), (string) $this->config->item('app_version'), Rotas::TAGS),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
            ));
    }
}
