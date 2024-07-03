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
            $search = trim($this->get('search', true));
            $where = $search ? "nomeCliente LIKE '%{$search}%' OR documento LIKE '%{$search}%' OR telefone LIKE '%{$search}%' OR celular LIKE '%{$search}%' OR email LIKE '%{$search}%' OR contato LIKE '%{$search}%'" : '';

            $perPage = $this->get('perPage', true) ?: 20;
            $page = $this->get('page', true) ?: 0;
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

        $senhaCliente = $this->post('senha', true) ?: preg_replace('/[^\p{L}\p{N}\s]/', '', $this->post('documento', true));
        $cpf_cnpj = preg_replace('/[^\p{L}\p{N}\s]/', '', $this->post('documento', true));
        $pessoaFisica = strlen($cpf_cnpj) == 11 ? true : false;

        $data = [
            'nomeCliente' => $this->post('nomeCliente', true),
            'contato' => $this->post('contato', true),
            'pessoa_fisica' => $pessoaFisica,
            'documento' => $this->post('documento', true),
            'telefone' => $this->post('telefone', true),
            'celular' => $this->post('celular', true),
            'email' => $this->post('email', true),
            'senha' => password_hash($senhaCliente, PASSWORD_DEFAULT),
            'rua' => $this->post('rua', true),
            'numero' => $this->post('numero', true),
            'complemento' => $this->post('complemento', true),
            'bairro' => $this->post('bairro', true),
            'cidade' => $this->post('cidade', true),
            'estado' => $this->post('estado', true),
            'cep' => $this->post('cep', true),
            'dataCadastro' => date('Y-m-d'),
            'fornecedor' => $this->post('fornecedor', true) == true ? 1 : 0,
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

        if ($this->put('documento', true) && ! verific_cpf_cnpj($this->put('documento', true))) {
            $this->response([
                'status' => false,
                'message' => 'CPF/CNPJ inválido. Verifique o número do documento e tente novamente.',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $data = [
            'nomeCliente' => $this->put('nomeCliente', true),
            'contato' => $this->put('contato', true),
            'documento' => $this->put('documento', true),
            'telefone' => $this->put('telefone', true),
            'celular' => $this->put('celular', true),
            'email' => $this->put('email', true),
            'rua' => $this->put('rua', true),
            'numero' => $this->put('numero', true),
            'complemento' => $this->put('complemento', true),
            'bairro' => $this->put('bairro', true),
            'cidade' => $this->put('cidade', true),
            'estado' => $this->put('estado', true),
            'cep' => $this->put('cep', true),
            'fornecedor' => $this->put('fornecedor', true) == true ? 1 : 0,
        ];

        if ($this->put('senha')) {
            $data['senha'] = password_hash($this->put('senha', true), PASSWORD_DEFAULT);
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
