<?php

class Login extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('mapos_model');
    }

    public function index()
    {
        // A tela de entrada é sempre escura (DESIGN.md), então não lê o modo
        // de cor das configurações.
        $this->load->view('mapos/login', [
            'erro' => $this->session->flashdata('error'),
        ]);
    }

    public function sair()
    {
        $this->session->sess_destroy();

        // O Referer é controlado pelo cliente; redirecionar para ele permitiria
        // que um terceiro enviasse o usuário para um domínio externo.
        return redirect(site_url('login'));
    }

    public function verificarLogin()
    {
        header('Access-Control-Allow-Origin: ' . base_url());
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Headers: Content-Type');

        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'E-mail', 'valid_email|required|trim');
        $this->form_validation->set_rules('senha', 'Senha', 'required|trim');
        if ($this->form_validation->run() == false) {
            echo json_encode(['result' => false, 'message' => validation_errors()]);
            exit();
        }

        $email = $this->input->post('email');
        $password = $this->input->post('senha');
        $ip = $this->input->ip_address();

        // Limite de tentativas (#2870): bloqueado, nem consulta a senha.
        $this->load->library('Limite_login');
        if ($this->limite_login->bloqueado('usuario', $email, $ip)) {
            echo json_encode($this->falha(Limite_login::MENSAGEM));
            exit();
        }

        $this->load->model('Mapos_model');
        $user = $this->Mapos_model->check_credentials($email);

        // Mesma mensagem para e-mail inexistente e senha errada: mensagens
        // distintas revelam quais e-mails possuem conta. A expiração só é
        // informada depois da senha certa, pelo mesmo motivo.
        if (! $user || ! password_verify($password, $user->senha)) {
            $this->limite_login->registrarFalha('usuario', $email, $ip);
            echo json_encode($this->falha('Os dados de acesso estão incorretos.'));
            exit();
        }

        $this->limite_login->registrarSucesso('usuario', $email);

        if ($this->chk_date($user->dataExpiracao)) {
            echo json_encode($this->falha('A conta do usuário está expirada, por favor entre em contato com o administrador do sistema.'));
            exit();
        }

        // Novo ID de sessão a cada autenticação, para que um ID
        // fixado antes do login não continue válido depois dele.
        $this->session->sess_regenerate(true);

        $session_admin_data = ['nome_admin' => $user->nome, 'email_admin' => $user->email, 'url_image_user_admin' => $user->url_image_user, 'id_admin' => $user->idUsuarios, 'permissao' => $user->permissoes_id, 'logado' => true];
        $this->session->set_userdata($session_admin_data);
        log_info('Efetuou login no sistema');
        echo json_encode(['result' => true]);
        exit();
    }

    /**
     * Resposta de login recusado, com o token CSRF novo para a próxima tentativa.
     */
    private function falha(string $mensagem): array
    {
        return ['result' => false, 'message' => $mensagem, 'MAPOS_TOKEN' => $this->security->get_csrf_hash()];
    }

    /**
     * Diz se a conta está expirada.
     *
     * Sem data cadastrada a conta não expira: usuarios.dataExpiracao é
     * date DEFAULT NULL e o servidor não exige preenchimento, então tratar
     * o vazio como expirado trancava o login de quem nunca teve expiração
     * configurada — com a mensagem enganosa de "conta expirada".
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
