<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Libraries\Api\Api;
use Libraries\Api\Configuracao;
use Libraries\Api\Http\Requisicao;
use Libraries\Api\Rotas;
use Libraries\Api\SessaoEmMemoria;

class Api_v2 extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (! apiHabilitada()) {
            show_404();
        }
    }

    public function index()
    {
        $this->db->db_debug = false;
        $this->session = new SessaoEmMemoria();
        $this->load->helper(Rotas::HELPERS);

        $api = new Api(
            $this,
            Rotas::roteador(),
            Configuracao::doAmbiente($_ENV, base_url(), ENVIRONMENT === 'production'),
            fn (): bool => migracoesPendentes($this->db) !== [],
        );

        $caminho = (string) preg_replace('#^api/v2#', '', $this->uri->uri_string());
        $api->atender(Requisicao::dosGlobais($caminho, $this->input->ip_address()))->enviar();

        exit;
    }
}
