<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class UsuariosController extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('Authorization_Token');
        $this->load->model('Mapos_model');
        $this->load->model('usuarios_model');
    }

    public function index_get($id = '')
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'cUsuario')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Visualizar Usuários',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id) {
            $search = trim($this->get('search', true));

            if ($search) {
                $this->load->model('api_model');
                $usuarios = $this->api_model->searchUsuario($search);
            } else {
                $perPage = $this->get('perPage', true) ?: 20;
                $page = $this->get('page', true) ?: 0;
                $start = $page ? ($perPage * $page) : 0;

                $usuarios = $this->usuarios_model->get($perPage, $start);
            }

            if ($usuarios) {
                $this->response([
                    'status' => true,
                    'message' => 'Lista de Usuários',
                    'result' => $usuarios,
                ], REST_Controller::HTTP_OK);
            }
        }

        if ($id && is_numeric($id)) {
            $this->response([
                'status' => true,
                'message' => 'Detalhes do usuário',
                'result' => $this->usuarios_model->getById($id),
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Nenhum usuário localizado.',
        ], REST_Controller::HTTP_OK);
    }

    public function index_post()
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'cUsuario')) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Adicionar Usuários!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $_POST = (array) json_decode(file_get_contents('php://input'), true);

        $this->load->library('form_validation');

        if ($this->form_validation->run('usuarios') == false) {
            $this->response([
                'status' => false,
                'message' => validation_errors(),
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $data = [
            'nome' => $this->post('nome', true),
            'rg' => $this->post('rg', true),
            'cpf' => $this->post('cpf', true),
            'cep' => $this->post('cep', true),
            'rua' => $this->post('rua', true),
            'numero' => $this->post('numero', true),
            'bairro' => $this->post('bairro', true),
            'cidade' => $this->post('cidade', true),
            'estado' => $this->post('estado', true),
            'email' => $this->post('email', true),
            'senha' => password_hash($this->post('senha', true), PASSWORD_DEFAULT),
            'telefone' => $this->post('telefone', true),
            'celular' => $this->post('celular', true),
            'dataExpiracao' => $this->post('dataExpiracao', true),
            'situacao' => $this->post('situacao', true),
            'permissoes_id' => $this->post('permissoes_id', true),
            'dataCadastro' => date('Y-m-d'),
        ];

        if ($this->usuarios_model->add('usuarios', $data)) {

            $this->load->model('api_model');
            $data = $this->api_model->lastRow('usuarios', 'idUsuarios');

            $this->response([
                'status' => true,
                'message' => 'Usuário adicionado com sucesso!',
                'result' => $data,
            ], REST_Controller::HTTP_OK);
        }
    }

    public function index_put($id)
    {
        $this->logged_user();

        if (! $id) {
            $this->response([
                'status' => false,
                'message' => 'Informe o ID do Usuário!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if ($this->logged_user()->usuario->idUsuarios != $id) {
            if (! $this->permission->checkPermission($this->logged_user()->level, 'cUsuario')) {
                $this->response([
                    'status' => false,
                    'message' => 'Você não está autorizado a Editar Usuários!',
                ], REST_Controller::HTTP_UNAUTHORIZED);
            }
        }

        if (! $this->put(['nome'], true) ||
            ! $this->put(['rg'], true) ||
            ! $this->put(['cpf'], true) ||
            ! $this->put(['cep'], true) ||
            ! $this->put(['rua'], true) ||
            ! $this->put(['numero'], true) ||
            ! $this->put(['bairro'], true) ||
            ! $this->put(['cidade'], true) ||
            ! $this->put(['estado'], true) ||
            ! $this->put(['email'], true) ||
            ! $this->put(['telefone'], true) ||
            ! $this->put(['situacao'], true) ||
            ! $this->put(['permissoes_id'], true)
        ) {
            $this->response([
                'status' => false,
                'message' => 'Preencha todos campos',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if ($id == 1 && $this->put('situacao', true) == 0) {
            $this->response([
                'status' => false,
                'message' => 'error', 'O usuário super admin não pode ser desativado!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $data = [
            'nome' => $this->put('nome', true),
            'rg' => $this->put('rg', true),
            'cpf' => $this->put('cpf', true),
            'cep' => $this->put('cep', true),
            'rua' => $this->put('rua', true),
            'numero' => $this->put('numero', true),
            'bairro' => $this->put('bairro', true),
            'cidade' => $this->put('cidade', true),
            'estado' => $this->put('estado', true),
            'email' => $this->put('email', true),
            'telefone' => $this->put('telefone', true),
            'celular' => $this->put('celular', true),
            'dataExpiracao' => $this->put('dataExpiracao', true),
            'situacao' => $this->put('situacao', true),
            'permissoes_id' => $this->put('permissoes_id', true),
        ];

        if ($this->put('senha', true)) {
            $data['senha'] = $this->put('senha', true);
        }

        if ($this->usuarios_model->edit('usuarios', $data, 'idUsuarios', $id)) {
            $this->log_app('Alterou um usuário. ID: ' . $id);
            $this->response([
                'status' => true,
                'message' => 'Cliente editado com sucesso!',
                'result' => $this->usuarios_model->getById($id),
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status' => false,
            'message' => 'Não foi possível editar o Usuário.',
        ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function index_delete($id)
    {
        $this->logged_user();
        if (! $this->permission->checkPermission($this->logged_user()->level, 'cUsuario') || $this->logged_user()->usuario->idUsuarios == $id) {
            $this->response([
                'status' => false,
                'message' => 'Você não está autorizado a Excluir Usuários!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (! $id) {
            $this->response([
                'status' => false,
                'message' => 'Informe o ID do Usuário!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if ($id == 1) {
            $this->response([
                'status' => false,
                'message' => 'error', 'O usuário super admin não pode ser deletado!',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $this->usuarios_model->delete('usuarios', 'idUsuarios', $id);

        $this->log_app('Removeu um usuário. ID: ' . $id);

        $this->response([
            'status' => true,
            'message' => 'Usuário excluído com sucesso!',
        ], REST_Controller::HTTP_OK);
    }

    /**
     * login function.
     *
     * @return void
     */
    public function login_post()
    {
        $_POST = (array) json_decode(file_get_contents('php://input'), true);

        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'E-mail', 'valid_email|required|trim');
        $this->form_validation->set_rules('password', 'Senha', 'required|trim');

        if ($this->form_validation->run() == false) {
            $this->response([
                'status' => false,
                'message' => strip_tags(validation_errors()),
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $this->load->model('Mapos_model');
        $email = $this->post('email', true);
        $password = $this->post('password', true);
        $user = $this->Mapos_model->check_credentials($email);

        if ($user) {
            // Verificar se acesso está expirado
            if ($this->chk_date($user->dataExpiracao)) {
                $this->response([
                    'status' => false,
                    'message' => 'Os dados de acesso estão incorretos!',
                ], REST_Controller::HTTP_UNAUTHORIZED);
            }

            // Verificar credenciais do usuário
            if (password_verify($password, $user->senha)) {
                $this->log_app('Efetuou login no sistema', $user->nome);
                $permissoes = $this->getInstanceDatabase('permissoes', '*', 'idPermissao = ' . $user->permissoes_id, 1, true);
                $permissoes = unserialize($permissoes['permissoes']);

                $token_data = [
                    'uid' => $user->idUsuarios,
                    'email' => $user->email,
                    'permissao' => $user->permissoes_id,
                ];

                $result = [
                    'access_token' => $this->authorization_token->generateToken($token_data),
                    'permissions' => [$permissoes],
                ];

                $this->response([
                    'status' => true,
                    'message' => 'Login realizado com sucesso!',
                    'result' => $result,
                ], REST_Controller::HTTP_OK);
            }

            $this->response([
                'status' => false,
                'message' => 'Os dados de acesso estão incorretos!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $this->response([
            'status' => false,
            'message' => 'Os dados de acesso estão incorretos!',
        ], REST_Controller::HTTP_UNAUTHORIZED);
    }

    /**
     * reGenToken function.
     *
     * @return void
     */
    public function reGenToken_get()
    {
        $user = $this->logged_user(true)->usuario;

        if (! empty($user->email)) {
            if (! empty($user)) {
                // token regeneration process
                $token_data = [
                    'uid' => $user->idUsuarios,
                    'email' => $user->email,
                    'permissao' => $user->permissoes_id,
                ];

                $permissoes = $this->getInstanceDatabase('permissoes', '*', 'idPermissao = ' . $user->permissoes_id, 1, true);
                $permissoes = unserialize($permissoes['permissoes']);

                $result = [
                    'access_token' => $this->authorization_token->generateToken($token_data),
                    'permissions' => [$permissoes],
                ];

                $this->response([
                    'status' => true,
                    'message' => 'Login realizado com sucesso!',
                    'result' => $result,
                ], REST_Controller::HTTP_OK);
            }

            $this->response([
                'status' => false,
                'message' => 'Usuário não encontrado, verifique se suas credenciais estão corretas!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $this->response([
            'status' => false,
            'message' => 'O x-api-key expirado é necessário para gerar um novo x-api-key!',
        ], REST_Controller::HTTP_OK);
    }

    public function conta_get()
    {
        $usuarioLogado = $this->logged_user();
        $usuarioLogado->usuario->url_image_user = ! is_null($usuarioLogado->usuario->url_image_user) ? base_url() . 'assets/userImage/' . $usuarioLogado->usuario->url_image_user : null;
        unset($usuarioLogado->usuario->senha);

        $this->response([
            'status' => true,
            'message' => 'Dados do Usuário!',
            'result' => $usuarioLogado,
        ], REST_Controller::HTTP_OK);
    }

    private function chk_date($data_banco)
    {
        $data_banco = new DateTime($data_banco);
        $data_hoje = new DateTime('now');

        return $data_banco < $data_hoje;
    }
}
