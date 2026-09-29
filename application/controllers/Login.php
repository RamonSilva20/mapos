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
        $this->load->view('mapos/login');
    }

    public function sair()
    {
        $this->session->sess_destroy();

        // O Referer é controlado pelo cliente; redirecionar para ele permitiria
        // que um terceiro enviasse o usuário para um domínio externo.
        respond_redirect(site_url('login'));
    }

    public function verificarLogin()
    {
        // Pelo CI_Output, e não pelo header() do PHP: o Output só emite no fim
        // da requisição, o que mantém o cabeçalho correto sem acordar os avisos
        // de "Cannot modify header information" do CLI, que o Whoops converte
        // em exceção e abortava a suíte. Não existe um set_headers() plural no
        // CI3, então cada cabeçalho sai pelo set_header().
        $this->output->set_header('Access-Control-Allow-Origin: ' . base_url());
        $this->output->set_header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        $this->output->set_header('Access-Control-Max-Age: 1000');
        $this->output->set_header('Access-Control-Allow-Headers: Content-Type');

        $email = trim((string) $this->input->post('email'));
        $password = trim((string) $this->input->post('senha'));

        $erro = $this->validarCredenciais($email, $password);

        if ($erro !== null) {
            return $this->respondFailure($erro);
        }

        $this->load->model('Mapos_model');
        $user = $this->Mapos_model->check_credentials($email);

        if (! $user) {
            // Mesma mensagem do erro de senha: mensagens distintas revelam
            // quais e-mails possuem conta.
            return $this->respondFailure('Os dados de acesso estão incorretos.');
        }

        if ($this->chk_date($user->dataExpiracao)) {
            return $this->respondFailure('A conta do usuário está expirada, por favor entre em contato com o administrador do sistema.');
        }

        if (! password_verify($password, $user->senha)) {
            return $this->respondFailure('Os dados de acesso estão incorretos.');
        }

        // Novo ID de sessão a cada autenticação, para que um ID
        // fixado antes do login não continue válido depois dele.
        $this->session->sess_regenerate(true);

        $this->session->set_userdata([
            'nome_admin' => $user->nome,
            'email_admin' => $user->email,
            'url_image_user_admin' => $user->url_image_user,
            'id_admin' => $user->idUsuarios,
            'permissao' => $user->permissoes_id,
            'logado' => true,
        ]);

        log_info('Efetuou login no sistema');

        return $this->respondJson(['result' => true]);
    }

    /**
     * Devolve a mensagem de erro, ou null quando os campos estão preenchidos.
     *
     * A view já valida no navegador (jQuery Validate), então isto é a segunda
     * linha: o endpoint é público e pode ser chamado direto.
     *
     * As mensagens são texto puro de propósito. A resposta é JSON e a view
     * escreve com $('#message').text(), então o HTML do validation_errors()
     * apareceria como tag visível na tela, em inglês, com uma mensagem por
     * linha. Um único texto também serve para o log e para o Swal de outros
     * pontos que consomem este endpoint.
     */
    private function validarCredenciais(string $email, string $senha): ?string
    {
        if ($email === '' || $senha === '') {
            return 'Preencha o e-mail e a senha.';
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return 'Insira um e-mail válido.';
        }

        return null;
    }

    private function respondFailure(string $message)
    {
        // MAPOS_TOKEN vai em toda falha, e não só na de credenciais: a view usa
        // o campo para renovar o token a cada tentativa, e um caminho sem o
        // campo deixaria o formulário com o token velho.
        return $this->respondJson([
            'result' => false,
            'message' => $message,
            'MAPOS_TOKEN' => $this->security->get_csrf_hash(),
        ]);
    }

    private function respondJson(array $payload)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Diz se a expiração da conta já passou.
     *
     * dataExpiracao é date DEFAULT NULL, então null é um valor legítimo: conta
     * sem expiração. Passar null para new DateTime() é depreciado no PHP 8.1
     * e, com failOnDeprecation someday ligado, vira erro.
     */
    private function chk_date(?string $dataExpiracao): bool
    {
        if ($dataExpiracao === null || $dataExpiracao === '') {
            return false;
        }

        return new DateTime($dataExpiracao) < new DateTime('now');
    }
}
