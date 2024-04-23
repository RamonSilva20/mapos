<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class ClientesController extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('clientes_model');
        $this->load->helper('validation_helper');
    }

    public function index_get($id = '')
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'vCliente')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Visualizar Clientes',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id) {
            $search = trim($this->input->get('search'));
            $where = $search ? "nomeCliente LIKE '%{$search}%' OR documento LIKE '%{$search}%' OR telefone LIKE '%{$search}%' OR celular LIKE '%{$search}%' OR email LIKE '%{$search}%' OR contato LIKE '%{$search}%'" : '';

            $perPage = $this->input->get('perPage') ?: 20;
            $page = $this->input->get('page') ?: 0;
            $start = $page ? ($perPage * $page) : 0;

            $clientes = $this->clientes_model->get('clientes', '*', $where, $perPage, $start);

            if ($clientes) {
                $this->response([
                    'status' => true,
                    'message' => 'Lista de Clientes',
                    'result' => $clientes,
                ], REST_Controller::HTTP_OK);
            }
        }

        if ($id && is_numeric($id)) {
            $cliente = $this->clientes_model->getById($id);

            if ($cliente) {
                $cliente->ordensServicos = $this->clientes_model->getOsByCliente($id);
                $this->response([
                    'status' => true,
                    'message' => 'Detalhes do Cliente',
                    'result' => $cliente,
                ], REST_Controller::HTTP_OK);
            }

            $this->response([
                'status' => false,
                'message' => 'Nenhum cliente localizado com esse ID.',
                'result' => null,
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Nenhum cliente localizado.',
            'result' => null,
        ], REST_Controller::HTTP_OK);
    }

    public function index_post()
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'aCliente')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Adicionar Clientes!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $_POST = (array) json_decode(file_get_contents('php://input'), true);

        $this->load->library('form_validation');

        if ($this->form_validation->run('clientes') == false) {
            $this->response([
                'status' => false,
                'message' => validation_errors(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $senhaCliente = $this->input->post('senha') ?: preg_replace('/[^\p{L}\p{N}\s]/', '', $this->input->post('documento'));
        $cpf_cnpj = preg_replace('/[^\p{L}\p{N}\s]/', '', $this->input->post('documento'));
        $pessoaFisica = strlen($cpf_cnpj) == 11 ? true : false;

        $data = [
            'nomeCliente' => $this->input->post('nomeCliente'),
            'contato' => $this->input->post('contato'),
            'pessoa_fisica' => $pessoaFisica,
            'documento' => $this->input->post('documento'),
            'telefone' => $this->input->post('telefone'),
            'celular' => $this->input->post('celular'),
            'email' => $this->input->post('email'),
            'senha' => password_hash($senhaCliente, PASSWORD_DEFAULT),
            'rua' => $this->input->post('rua'),
            'numero' => $this->input->post('numero'),
            'complemento' => $this->input->post('complemento'),
            'bairro' => $this->input->post('bairro'),
            'cidade' => $this->input->post('cidade'),
            'estado' => $this->input->post('estado'),
            'cep' => $this->input->post('cep'),
            'dataCadastro' => date('Y-m-d'),
            'fornecedor' => $this->input->post('fornecedor') == true ? 1 : 0,
        ];

        if ($this->clientes_model->add('clientes', $data) == true) {
            $this->response([
                'status' => true,
                'message' => 'Cliente adicionado com sucesso!',
                'result' => $this->clientes_model->get('clientes', '*', "telefone = '{$data['telefone']}'", 1, 0, true),
            ], REST_Controller::HTTP_CREATED);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível adicionar o Cliente.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function index_put($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'eCliente')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Editar Clientes!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $inputData = json_decode(trim(file_get_contents('php://input')));

        if (isset($inputData->documento) && ! verific_cpf_cnpj($inputData->documento)) {
            $this->response([
                'status' => false,
                'message' => 'CPF/CNPJ inválido. Verifique o número do documento e tente novamente.',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $data = [
            'nomeCliente' => $inputData->nomeCliente,
            'contato' => $inputData->contato,
            'documento' => $inputData->documento,
            'telefone' => $inputData->telefone,
            'celular' => $inputData->celular,
            'email' => $inputData->email,
            'rua' => $inputData->rua,
            'numero' => $inputData->numero,
            'complemento' => $inputData->complemento,
            'bairro' => $inputData->bairro,
            'cidade' => $inputData->cidade,
            'estado' => $inputData->estado,
            'cep' => $inputData->cep,
            'fornecedor' => $inputData->fornecedor == true ? 1 : 0,
        ];

        if ($this->put('senha')) {
            $data['senha'] = password_hash($this->put('senha'), PASSWORD_DEFAULT);
        }

        if ($this->clientes_model->edit('clientes', $data, 'idClientes', $id) == true) {
            $this->response([
                'status' => true,
                'message' => 'Cliente editado com sucesso!',
                'result' => $this->clientes_model->getById($id),
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível editar o Cliente.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function index_delete($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'dCliente')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Apagar Clientes!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id) {
            $this->response([
                'status' => false,
                'message' => 'Informe o ID do cliente!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $os = $this->clientes_model->getAllOsByClient($id);
        if ($os != null) {
            $this->clientes_model->removeClientOs($os);
        }

        $vendas = $this->clientes_model->getAllVendasByClient($id);
        if ($vendas != null) {
            $this->clientes_model->removeClientVendas($vendas);
        }

        if ($this->clientes_model->delete('clientes', 'idClientes', $id) == true) {
            $this->log_app('Removeu um cliente. ID' . $id);
            $this->response([
                'status' => true,
                'message' => 'Cliente excluído com sucesso!',
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível excluir o Cliente.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }
}
