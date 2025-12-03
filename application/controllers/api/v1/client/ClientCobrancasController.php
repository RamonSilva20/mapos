<?php

/*
    Ivan Sarkozin
    https://github.com/sarkozin
*/

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class ClientCobrancasController extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Conecte_model');
        $this->load->library('Authorization_Token');
    }

    public function index_get()
    {
        $clientLogged = $this->logged_client();
        
        $cobrancas = $this->Conecte_model->getCobrancas('cobrancas', '*', '', 20, $this->uri->segment(3), '', '', $this->logged_client()->usuario->idClientes);
        
        if(empty($cobrancas)) {
            $this->response([
                'status' => false,
                'message' => 'Nenhum resultado encontrado'
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        foreach ($cobrancas as $cobranca) {
            unset($cobranca->senha);
        }
        
        $this->response([
            'status' => true,
            'message' => 'Listando resultados',
            'result' => $cobrancas
        ], REST_Controller::HTTP_OK);
    }
}
