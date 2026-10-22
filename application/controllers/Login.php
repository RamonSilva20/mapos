<?php

class Login extends CI_Controller
{
    /** Quanto tempo a senha conferida espera o código, e quantos códigos errados são aceitos (#2876). */
    private const DOIS_FATORES_VALIDADE = 300;

    private const DOIS_FATORES_TENTATIVAS = 5;

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
        // senhaConfere() gasta o mesmo bcrypt sem conta, para o tempo de resposta
        // também não revelar quais e-mails existem (#2879).
        $senhaConfere = senhaConfere((string) $password, $user->senha ?? null);
        if (! $user || ! $senhaConfere) {
            $this->limite_login->registrarFalha('usuario', $email, $ip);
            echo json_encode($this->falha('Os dados de acesso estão incorretos.'));
            exit();
        }

        $bloqueio = $this->motivoDeBloqueio($user);
        if ($bloqueio !== null) {
            echo json_encode($this->falha($bloqueio));
            exit();
        }

        // 2FA ativo (#2876): a senha sozinha não entra. A sessão guarda só que a
        // senha conferiu, por 5 minutos e 5 tentativas, e o código decide. O
        // contador de falhas só zera com o código certo: senão repetir a senha
        // devolveria tentativas de código de graça.
        if ($user->totp_ativado_em ?? null) {
            $this->session->sess_regenerate(true);
            $this->session->set_userdata('dois_fatores', ['email' => $user->email, 'expira' => time() + self::DOIS_FATORES_VALIDADE, 'tentativas' => 0]);
            echo json_encode(['result' => true, 'destino' => site_url('login/doisFatores')]);
            exit();
        }

        $this->limite_login->registrarSucesso('usuario', $email);
        $this->iniciarSessao($user);
        echo json_encode(['result' => true]);
        exit();
    }

    /**
     * Segundo passo do login (#2876): o código do aplicativo autenticador ou
     * um código de recuperação. Só abre com a senha já conferida na sessão.
     */
    public function doisFatores()
    {
        if ($this->doisFatoresPendente() === null) {
            $this->session->set_flashdata('error', 'Entre com e-mail e senha primeiro.');

            return redirect(site_url('login'));
        }

        return $this->load->view('mapos/login_dois_fatores', ['erro' => $this->session->flashdata('error')]);
    }

    public function verificarDoisFatores()
    {
        $pendente = $this->doisFatoresPendente();
        if ($pendente === null || $this->input->method() !== 'post') {
            $this->session->set_flashdata('error', 'Entre com e-mail e senha primeiro.');

            return redirect(site_url('login'));
        }

        $this->load->library('Limite_login');
        $this->load->library('Totp');
        $ip = $this->input->ip_address();
        $email = (string) $pendente['email'];

        if ($this->limite_login->bloqueado('usuario', $email, $ip)) {
            $this->session->unset_userdata('dois_fatores');
            $this->session->set_flashdata('error', Limite_login::MENSAGEM);

            return redirect(site_url('login'));
        }

        $user = $this->mapos_model->check_credentials($email);
        $codigo = trim((string) $this->input->post('codigo'));
        $ok = $user && $this->motivoDeBloqueio($user) === null && $this->totp->aceitar($this->mapos_model, $user, $codigo);

        if (! $ok) {
            $this->limite_login->registrarFalha('usuario', $email, $ip);
            $pendente['tentativas']++;

            if ($pendente['tentativas'] >= self::DOIS_FATORES_TENTATIVAS) {
                $this->session->unset_userdata('dois_fatores');
                $this->session->set_flashdata('error', 'Código incorreto várias vezes. Entre de novo com e-mail e senha.');

                return redirect(site_url('login'));
            }

            $this->session->set_userdata('dois_fatores', $pendente);
            $this->session->set_flashdata('error', 'Código incorreto. Confira o aplicativo ou use um código de recuperação.');

            return redirect(site_url('login/doisFatores'));
        }

        $this->limite_login->registrarSucesso('usuario', $email);
        $this->session->unset_userdata('dois_fatores');
        $this->iniciarSessao($user);

        return redirect(site_url('mapos'));
    }

    /** @return array{email: string, expira: int, tentativas: int}|null */
    private function doisFatoresPendente(): ?array
    {
        $pendente = $this->session->userdata('dois_fatores');
        if (! is_array($pendente) || (int) ($pendente['expira'] ?? 0) < time()) {
            $this->session->unset_userdata('dois_fatores');

            return null;
        }

        return $pendente;
    }

    /** Motivo para a conta não entrar (expirada, grupo desativado), ou null. */
    private function motivoDeBloqueio(object $user): ?string
    {
        if ($this->chk_date($user->dataExpiracao)) {
            return 'A conta do usuário está expirada, por favor entre em contato com o administrador do sistema.';
        }

        // Grupo de permissão desativado (#2846): a conta não entra, como a
        // expirada (até a v4 a situação do grupo era só informativa).
        if (! $this->mapos_model->grupoAtivo((int) $user->permissoes_id)) {
            return 'O grupo de permissão desta conta está desativado. Fale com o administrador do sistema.';
        }

        return null;
    }

    private function iniciarSessao(object $user): void
    {
        // Novo ID de sessão a cada autenticação, para que um ID
        // fixado antes do login não continue válido depois dele.
        $this->session->sess_regenerate(true);

        $this->session->set_userdata(['nome_admin' => $user->nome, 'email_admin' => $user->email, 'url_image_user_admin' => $user->url_image_user, 'id_admin' => $user->idUsuarios, 'permissao' => $user->permissoes_id, 'tema_modo_admin' => $user->tema_modo ?? null, 'sessao_versao' => (int) ($user->sessao_versao ?? 0), 'logado' => true]);

        // Grupo que exige 2FA e usuário sem ele: entra, mas só alcança a tela de ativação (MY_Controller).
        if (! ($user->totp_ativado_em ?? null) && $this->mapos_model->grupoExige2fa((int) $user->permissoes_id)) {
            $this->session->set_userdata('exigir_2fa_cadastro', true);
        }

        log_info('Efetuou login no sistema');
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
        return contaExpirada($data_banco);
    }
}
