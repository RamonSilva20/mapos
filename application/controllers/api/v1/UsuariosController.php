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
        $ip = $this->input->ip_address();

        // Limite de tentativas (#2870), no mesmo escopo do login do painel.
        $this->load->library('Limite_login');
        $restantes = $this->limite_login->segundosRestantes('usuario', $email, $ip);
        if ($restantes > 0) {
            header('Retry-After: ' . $restantes);
            $this->response([
                'status' => false,
                'message' => Limite_login::MENSAGEM,
            ], REST_Controller::HTTP_TOO_MANY_REQUESTS);
        }

        $user = $this->Mapos_model->check_credentials($email);

        if ($user && password_verify((string) $password, $user->senha)) {
            // Conta expirada: mesma resposta genérica de antes.
            if ($this->chk_date($user->dataExpiracao)) {
                $this->limite_login->registrarSucesso('usuario', $email);
                $this->response([
                    'status' => false,
                    'message' => 'Os dados de acesso estão incorretos!',
                ], REST_Controller::HTTP_UNAUTHORIZED);
            }

            // 2FA (#2876): com ele ativo a senha sozinha não gera token; o contador de falhas só zera com o código certo.
            $this->conferirDoisFatores($user, $email, $ip);
            $this->limite_login->registrarSucesso('usuario', $email);

            $this->log_app('Efetuou login no sistema', $user->nome);
            $permissoes = json_decode_legacy($this->getInstanceDatabase('permissoes', '*', 'idPermissao = ' . (int) $user->permissoes_id, 1, true)['permissoes']);

            $token_data = [
                'uid' => $user->idUsuarios,
                'email' => $user->email,
                'permissao' => $user->permissoes_id,
                'type' => REST_Controller::TOKEN_TYPE_USER,
            ];

            // Campos novos e opcionais (#2869): quem não os conhece continua só com access_token e permissions.
            $result = ['access_token' => $this->authorization_token->generateToken($token_data)] + $this->sessaoDoToken((int) $user->idUsuarios) + [
                'permissions' => [$permissoes],
            ];

            $this->response([
                'status' => true,
                'message' => 'Login realizado com sucesso!',
                'result' => $result,
            ], REST_Controller::HTTP_OK);
        }

        // E-mail inexistente ou senha errada: mesma resposta, e conta para o limite.
        $this->limite_login->registrarFalha('usuario', $email, $ip);
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
        // Mantido para os apps já instalados (#2869); o fluxo novo é POST /refresh.
        header('Deprecation: true');
        header('Link: <' . site_url('api/v1/refresh') . '>; rel="successor-version"');

        $user = $this->logged_user(true)->usuario;

        // Usuário removido ou desativado não pode renovar o token
        if (empty($user) || (int) $user->situacao !== 1) {
            $this->response([
                'status' => false,
                'message' => 'Faça o login novamente!',
            ], REST_Controller::HTTP_FORBIDDEN);
        }

        if (! empty($user->email)) {
            if (! empty($user)) {
                // token regeneration process
                $token_data = [
                    'uid' => $user->idUsuarios,
                    'email' => $user->email,
                    'permissao' => $user->permissoes_id,
                    'type' => REST_Controller::TOKEN_TYPE_USER,
                ];

                $permissoes = json_decode_legacy($this->getInstanceDatabase('permissoes', '*', 'idPermissao = ' . (int) $user->permissoes_id, 1, true)['permissoes']);

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

    /**
     * Segundo fator do login pela API (#2876). Usuário com 2FA ativo manda o
     * campo `totp` (código do aplicativo ou de recuperação); grupo que exige o
     * 2FA recusa quem ainda não o ativou. Responde e encerra se não passar.
     */
    private function conferirDoisFatores(object $user, string $email, string $ip): void
    {
        $this->load->model('Mapos_model');

        if (! ($user->totp_ativado_em ?? null)) {
            if ($this->Mapos_model->grupoExige2fa((int) $user->permissoes_id)) {
                $this->response([
                    'status' => false,
                    'message' => 'O seu grupo de permissão exige a autenticação em dois fatores. Ative em Meu perfil, no painel, para usar a API.',
                    'two_factor_setup_required' => true,
                ], REST_Controller::HTTP_FORBIDDEN);
            }

            return;
        }

        $codigo = trim((string) $this->post('totp', true));
        if ($codigo === '') {
            $this->response([
                'status' => false,
                'message' => 'Informe o código de verificação em dois fatores (campo "totp").',
                'two_factor_required' => true,
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $this->load->library('Totp');
        if (! $this->totp->aceitar($this->Mapos_model, $user, $codigo)) {
            $this->limite_login->registrarFalha('usuario', $email, $ip);
            $this->response([
                'status' => false,
                'message' => 'Código de verificação incorreto.',
                'two_factor_required' => true,
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Renova o access token com o refresh token (#2869).
     *
     * POST /api/v1/refresh {"refresh_token": "..."}. O refresh token é de uso
     * único: a resposta traz um novo, que substitui o anterior.
     */
    public function refresh_post()
    {
        $_POST = (array) json_decode(file_get_contents('php://input'), true);
        $refreshToken = (string) $this->post('refresh_token', true);

        $this->load->library('Refresh_token');
        $girado = $this->refresh_token->girar($refreshToken, $this->input->ip_address(), $this->input->user_agent());

        $usuario = $girado['ok'] ? $this->usuarios_model->getById($girado['usuario_id']) : null;
        // Usuário removido, desativado ou com a conta expirada: a sessão acaba aqui.
        if ($girado['ok'] && (empty($usuario) || (int) $usuario->situacao !== 1 || $this->chk_date($usuario->dataExpiracao))) {
            $this->refresh_token->revogarDoUsuario((int) $girado['usuario_id']);
            $girado = ['ok' => false];
        }

        if (! $girado['ok']) {
            $this->response([
                'status' => false,
                'message' => 'Faça o login novamente!',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $token_data = [
            'uid' => $usuario->idUsuarios,
            'email' => $usuario->email,
            'permissao' => $usuario->permissoes_id,
            'type' => REST_Controller::TOKEN_TYPE_USER,
        ];
        $permissoes = json_decode_legacy($this->getInstanceDatabase('permissoes', '*', 'idPermissao = ' . (int) $usuario->permissoes_id, 1, true)['permissoes']);

        $this->response([
            'status' => true,
            'message' => 'Token renovado!',
            'result' => [
                'access_token' => $this->authorization_token->generateToken($token_data),
                'refresh_token' => $girado['refresh_token'],
                'token_type' => 'Bearer',
                'expires_in' => $this->authorization_token->expiresIn(),
                'permissions' => [$permissoes],
            ],
        ], REST_Controller::HTTP_OK);
    }

    /**
     * Encerra a sessão da API: revoga o refresh token (e a sessão a que ele pertence).
     * POST /api/v1/logout {"refresh_token": "..."}. Sempre responde 200: o app
     * apaga os tokens locais de qualquer forma.
     */
    public function logout_post()
    {
        $_POST = (array) json_decode(file_get_contents('php://input'), true);

        $this->load->library('Refresh_token');
        $this->refresh_token->revogar((string) $this->post('refresh_token', true));

        $this->response([
            'status' => true,
            'message' => 'Sessão encerrada.',
        ], REST_Controller::HTTP_OK);
    }

    /**
     * Campos da sessão que o login devolve além do access_token: refresh_token
     * (só se a migration rodou), token_type e expires_in.
     *
     * @return array<string, mixed>
     */
    private function sessaoDoToken(int $usuarioId): array
    {
        $this->load->library('Refresh_token');
        $refresh = $this->refresh_token->emitir($usuarioId, $this->input->ip_address(), $this->input->user_agent());

        return array_filter([
            'refresh_token' => $refresh,
            'token_type' => 'Bearer',
            'expires_in' => $this->authorization_token->expiresIn(),
        ], static fn ($valor) => $valor !== null);
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

    /**
     * Diz se a conta está expirada.
     *
     * Sem data cadastrada a conta não expira: usuarios.dataExpiracao é
     * date DEFAULT NULL e a API não exige o campo, então tratar o vazio como
     * expirado recusava o token de quem nunca teve expiração configurada.
     *
     * Mantido em par com Login::chk_date(), que tem a mesma regra. Alterar um
     * dos dois sem o outro deixa o admin e a API discordando sobre quem pode
     * entrar.
     *
     * @param  string|null  $data_banco  Data no formato aceito por DateTime, ou null
     */
    private function chk_date($data_banco)
    {
        // O trim importa: string só com espaços é truthy em PHP, então com
        // empty() um campo enviado em branco cairia no new DateTime() e
        // voltaria a ser lido como expirado.
        if (trim((string) $data_banco) === '') {
            return false;
        }

        return new DateTime($data_banco) < new DateTime('now');
    }
}
