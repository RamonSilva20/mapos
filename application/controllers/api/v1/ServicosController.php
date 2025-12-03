<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require APPPATH . 'libraries/REST_Controller.php';

class ServicosController extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('servicos_model');
    }

    public function index_get($id = '')
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'vServico')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Visualizar Serviços',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id) {
            $search = trim($this->get('search', true));
            $where = $search ? "nome LIKE '%{$search}%' OR descricao LIKE '%{$search}%'" : '';

            $perPage = $this->get('perPage', true) ?: 20;
            $page = $this->get('page', true) ?: 0;
            $start = $page ? ($perPage * $page) : 0;

            $servicos = $this->servicos_model->get('servicos', '*', $where, $perPage, $start);

            $this->response([
                'status' => true,
                'message' => 'Listando Serviços',
                'result' => $servicos,
            ], REST_Controller::HTTP_OK);
        }

        if ($id && is_numeric($id)) {
            $servico = $this->servicos_model->getById($id);

            $this->response([
                'status' => true,
                'message' => 'Detalhes do Serviço',
                'result' => $servico,
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Nenhum Produto localizado.',
            'result' => null,
        ], REST_Controller::HTTP_OK);
    }

    public function index_post()
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'aServico')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Adicionar Serviços!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $_POST = (array) json_decode(file_get_contents('php://input'), true);

        $this->load->library('form_validation');

        if ($this->form_validation->run('servicos') == false) {
            $this->response([
                'status' => false,
                'message' => validation_errors(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $preco = $this->post('preco', true);
        $preco = str_replace(',', '', $preco);

        $data = [
            'nome' => $this->post('nome', true),
            'descricao' => $this->post('descricao', true) ? $this->post('descricao', true) : '',
            'preco' => $preco,
        ];

        if ($this->servicos_model->add('servicos', $data)) {
            $this->response([
                'status' => true,
                'message' => 'Serviço adicionado com sucesso!',
                'result' => $this->servicos_model->get('servicos', '*', "descricao = '{$data['descricao']}'", 1, 0, true),
            ], REST_Controller::HTTP_CREATED);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível adicionar o Serviço. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function index_put($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'eServico')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Editar Serviços!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id) {
            $this->response([
                'status' => false,
                'message' => 'Informe o ID do Serviço!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $inputData = json_decode(trim(file_get_contents('php://input')));

        if (! $this->put('nome', true) || ! $this->put('preco', true)) {
            $this->response([
                'status' => false,
                'message' => 'Preencha todos os campos obrigatórios!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $preco = $this->put('preco', true);
        $preco = str_replace(',', '', $preco);

        $data = [
            'nome' => $this->put('nome', true),
            'descricao' => $this->put('descricao', true) ? $this->put('descricao', true) : '',
            'preco' => $preco,
        ];

        if ($this->servicos_model->edit('servicos', $data, 'idServicos', $id)) {
            $this->response([
                'status' => true,
                'message' => 'Serviço editado com sucesso!',
                'result' => $this->servicos_model->getById($id),
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível editar o Serviço. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function index_delete($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'dServico')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Apagar Serviços!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id) {
            $this->response([
                'status' => false,
                'message' => 'Informe o ID do Serviço!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $this->servicos_model->delete('servicos_os', 'servicos_id', $id);

        if ($this->servicos_model->delete('servicos', 'idServicos', $id)) {
            $this->log_app('Removeu um Serviço. ID' . $id);
            $this->response([
                'status' => true,
                'message' => 'Serviço excluído com sucesso!',
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível excluir o Serviço. Avise ao Administrador.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }
}
