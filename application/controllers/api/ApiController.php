<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Classe ApiController.
 * 
 * @extends REST_Controller
 */
require(APPPATH.'/libraries/REST_Controller.php');

class ApiController extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('Authorization_Token');
        $this->load->model('mapos_model');
    }

    public function index_get()
    {
        $user = $this->logged_user();

        $result = new stdClass;
        $result->countOs   = $this->mapos_model->count('os');
        $result->clientes  = $this->mapos_model->count('clientes');
        $result->produtos  = $this->mapos_model->count('produtos');
        $result->servicos  = $this->mapos_model->count('servicos');
        $result->garantias = $this->mapos_model->count('garantias');
        $result->vendas    = $this->mapos_model->count('vendas');
        
        $result->osAbertas    = $this->mapos_model->getOsAbertas();
        $result->osAndamento  = $this->mapos_model->getOsAndamento();
        $result->estoqueBaixo = $this->mapos_model->getProdutosMinimo();

        $this->response([
            'status'  => true,
            'message' => 'Dashboard',
            'result'  => $result
        ], REST_Controller::HTTP_OK);
    }

    public function emitente_get()
    {
        $this->logged_user();

        $result = new stdClass;
        $result->appName  = $this->getConfig('app_name');
        $result->emitente = $this->mapos_model->getEmitente() ?: false;

        $this->response([
            'status'  => true,
            'message' => 'Dados do Map-OS',
            'result'  => $result
        ], REST_Controller::HTTP_OK);
    }

    public function audit_get()
    {
        $this->logged_user();
        
        if (!$this->permission->checkPermission($this->logged_user()->level, 'cAuditoria')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Visualizar Auditoria'
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }
        
        $perPage  = $this->input->get('perPage') ?: 20;
        $page     = $this->input->get('page') ?: 0;
        $start    = $page ? ($perPage * $page) : 0;
        
        $this->load->model('Audit_model');
        $logs = $this->Audit_model->get('logs', '*', '', $perPage, $start);
        
        $this->response([
            'status' => true,
            'message' => 'Listando Logs',
            'result' => $logs
        ], REST_Controller::HTTP_OK);
    }
}
