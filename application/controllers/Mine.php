<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Mine extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Conecte_model');
        $this->load->helper('Security_helper');
    }

    public function index()
    {
        $this->load->view('conecte/login');
    }

    public function sair()
    {
        $this->session->sess_destroy();
        redirect('mine');
    }

    public function resetarSenha()
    {
        $this->load->view('conecte/resetar_senha');
    }

    public function senhaSalvar()
    {
        $this->load->library('form_validation');
        $data['custom_error'] = '';
        $this->form_validation->set_rules('senha', 'Senha', 'required');

        if ($this->input->post('token') == null || $this->input->post('token') == '') {
            return redirect('mine');
        }
        if ($this->form_validation->run() == false) {
            echo json_encode(['result' => false, 'message' => 'Por favor digite uma senha']);
        } else {
            $token = $this->check_token($this->input->post('token'));
            $cliente = $token ? $this->check_credentials($token->email) : null;

            // O token precisa existir, estar dentro da validade, ainda não ter
            // sido utilizado e corresponder a um cliente existente.
            if ($token == null || $cliente == null || $token->token_utilizado || $this->validateDate($token->data_expiracao)) {
                log_info('Alteração de senha. Porém, o token é inválido, expirado ou já utilizado.');
                echo json_encode(['result' => false, 'message' => 'Token inválido ou expirado. Solicite uma nova recuperação de senha.']);
            } else {
                if ($token->email == $cliente->email) {
                    $data = [
                        'senha' => password_hash($this->input->post('senha'), PASSWORD_DEFAULT),
                    ];

                    $dataToken = [
                        'token_utilizado' => true,
                    ];
                    $this->load->model('resetSenhas_model', '', true);
                    if ($this->Conecte_model->edit('clientes', $data, 'idClientes', $cliente->idClientes) == true) {
                        if ($this->resetSenhas_model->edit('resets_de_senha', $dataToken, 'id', $token->id) == true) {
                            $session_mine_data = $cliente->nomeCliente ? ['nome' => $cliente->nomeCliente] : ['nome' => 'Inexistente'];
                            $this->session->set_userdata($session_mine_data);
                            log_info('Alteração da senha realizada com sucesso.');
                            echo json_encode(['result' => true]);
                        }
                    }
                } else {
                    $session_mine_data = $cliente->nomeCliente ? ['nome' => $cliente->nomeCliente] : ['nome' => 'Inexistente'];
                    $this->session->set_userdata($session_mine_data);
                    log_info('Alteração de senha. Porém, dados divergentes.');
                    echo json_encode(['result' => false, 'message' => 'Dados divergentes.']);
                }
            }
        }
    }

    public function tokenManual()
    {
        $this->load->library('form_validation');
        $data['custom_error'] = '';
        $this->form_validation->set_rules('token', 'Token', 'required');

        if ($this->form_validation->run('token') == false) {
            $this->session->set_flashdata(['error' => (validation_errors() ? 'Por favor digite o token' : false)]);

            return $this->load->view('conecte/token_digita');
        } else {
            $token = $this->check_token($this->input->post('token'));

            // Um token inexistente, expirado ou já utilizado recebe sempre a
            // mesma resposta genérica, para não revelar quais tokens existem.
            if ($token == null || $token->token_utilizado || $this->validateDate($token->data_expiracao)) {
                $this->session->set_flashdata(['error' => 'Token inválido ou expirado']);
                log_info('Digitou Token. Porém, o token é inválido, expirado ou já utilizado.');

                return redirect(base_url() . 'index.php/mine');
            }

            $cliente = $this->check_credentials($token->email);

            if ($cliente == null || $token->email != $cliente->email) {
                $this->session->set_flashdata(['error' => 'Token inválido ou expirado']);
                log_info('Digitou Token. Porém, os dados de acesso estão incorretos.');

                return redirect(base_url() . 'index.php/mine');
            }

            return $this->load->view('conecte/nova_senha', $token);
        }
        $this->load->view('conecte/token_digita');
    }

    public function verifyTokenSenha()
    {
        $segment = $this->uri->uri_to_assoc(3);
        $token = $this->check_token($segment['token'] ?? null);

        // Um token inexistente, expirado ou já utilizado recebe sempre a mesma
        // resposta genérica, para não revelar quais tokens existem.
        if ($token == null || $token->token_utilizado || $this->validateDate($token->data_expiracao)) {
            $this->session->set_flashdata(['error' => 'Token inválido ou expirado']);
            log_info('Acesso via link do email (Token). Porém, o token é inválido, expirado ou já utilizado.');

            return redirect(base_url() . 'index.php/mine');
        }

        $cliente = $this->check_credentials($token->email);

        if ($cliente == null || $token->email != $cliente->email) {
            $this->session->set_flashdata(['error' => 'Token inválido ou expirado']);
            log_info('Acesso via link do email (Token). Porém, os dados de acesso estão incorretos.');

            return redirect(base_url() . 'index.php/mine');
        }

        return $this->load->view('conecte/nova_senha', $token);
    }

    public function gerarTokenResetarSenha()
    {
        $emailSolicitado = (string) $this->input->post('email');

        if (! $cliente = $this->check_credentials($emailSolicitado)) {
            // Mesma resposta de sucesso quando o e-mail não existe, para não
            // permitir enumeração de contas.
            log_info('Cliente solicitou alteração de senha para um e-mail inexistente.');
            $this->session->set_flashdata('success', 'Solicitação realizada com sucesso! <br> Um e-mail com as instruções será enviado para ' . html_escape($emailSolicitado));
            redirect(base_url() . 'index.php/mine');
        } else {
            $this->load->helper('string');
            $this->load->model('resetSenhas_model', '', true);
            $data = [
                'email' => $cliente->email,
                'token' => random_string('alnum', 32),
                'data_expiracao' => date('Y-m-d H:i:s'),
            ];
            if ($this->resetSenhas_model->add('resets_de_senha', $data) == true) {
                $this->enviarRecuperarSenha($cliente->idClientes, $cliente->email, 'Recuperar Senha', json_encode($data));
                $session_mine_data = ['nome' => $cliente->nomeCliente];
                $this->session->set_userdata($session_mine_data);
                log_info('Cliente solicitou alteração de senha.');
                $this->session->set_flashdata('success', 'Solicitação realizada com sucesso! <br> Um e-mail com as instruções será enviado para ' . $cliente->email);
                redirect(base_url() . 'index.php/mine');
            } else {
                $this->session->set_flashdata('error', 'Falha ao realizar solicitação!');
                $session_mine_data = $cliente->nomeCliente ? ['nome' => $cliente->nomeCliente] : ['nome' => 'Inexistente'];
                $this->session->set_userdata($session_mine_data);
                log_info('Cliente solicitou alteração de senha. Porém falhou ao realizar solicitação!');
                redirect(current_url());
            }
        }
    }

    public function login()
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
        } else {
            $email = $this->input->post('email');
            $password = $this->input->post('senha');
            $cliente = $this->check_credentials($email);

            if ($cliente) {
                // Verificar credenciais do usuário
                if (password_verify($password, $cliente->senha)) {
                    // Novo ID de sessão a cada autenticação, para que um ID
                    // fixado antes do login não continue válido depois dele.
                    $this->session->sess_regenerate(true);

                    $session_mine_data = [
                        'nome' => $cliente->nomeCliente,
                        'cliente_id' => $cliente->idClientes,
                        'email' => $cliente->email,
                        'conectado' => true,
                        'isCliente' => true
                    ];
                    $this->session->set_userdata($session_mine_data);
                    log_info($_SERVER['REMOTE_ADDR'] . ' Efetuou login no sistema');

                    // Registrar login na auditoria
                    $this->load->model('Audit_model');
                    $log_data = [
                        'usuario' => $cliente->nomeCliente,
                        'tarefa' => 'Cliente ' . $cliente->nomeCliente . ' efetuou login',
                        'data' => date('Y-m-d'),
                        'hora' => date('H:i:s'),
                        'ip' => $_SERVER['REMOTE_ADDR']
                    ];

                    $this->Audit_model->add($log_data);

                    echo json_encode(['result' => true]);
                } else {
                    echo json_encode(['result' => false, 'message' => 'Os dados de acesso estão incorretos.', 'MAPOS_TOKEN' => $this->security->get_csrf_hash()]);
                }
            } else {
                // Mesma mensagem do erro de senha: mensagens distintas revelam
                // quais e-mails possuem cadastro.
                echo json_encode(['result' => false, 'message' => 'Os dados de acesso estão incorretos.', 'MAPOS_TOKEN' => $this->security->get_csrf_hash()]);
            }
        }
    }

    public function painel()
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        $data['menuPainel'] = 'painel';
        $data['compras'] = $this->Conecte_model->getLastCompras($this->session->userdata('cliente_id'));
        $data['os'] = $this->Conecte_model->getLastOs($this->session->userdata('cliente_id'));
        $data['output'] = 'conecte/painel';
        $this->load->view('conecte/template', $data);
    }

    public function conta()
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        $data['menuConta'] = 'conta';
        $data['result'] = $this->Conecte_model->getDados();

        $data['output'] = 'conecte/conta';
        $this->load->view('conecte/template', $data);
    }

    public function editarDados()
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        $data['menuConta'] = 'conta';

        $this->load->library('form_validation');
        $data['custom_error'] = '';

        if ($this->form_validation->run('clientes') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } else {
            $senha = $this->input->post('senha');
            if ($senha != null) {
                $senha = password_hash($senha, PASSWORD_DEFAULT);
                $data = [
                    'nomeCliente' => $this->input->post('nomeCliente'),
                    'documento' => $this->input->post('documento'),
                    'telefone' => $this->input->post('telefone'),
                    'celular' => $this->input->post('celular'),
                    'email' => $this->input->post('email'),
                    'senha' => $senha,
                    'rua' => $this->input->post('rua'),
                    'numero' => $this->input->post('numero'),
                    'complemento' => $this->input->post('complemento'),
                    'bairro' => $this->input->post('bairro'),
                    'cidade' => $this->input->post('cidade'),
                    'estado' => $this->input->post('estado'),
                    'cep' => $this->input->post('cep'),
                    'contato' => $this->input->post('contato'),
                ];
            } else {
                $data = [
                    'nomeCliente' => $this->input->post('nomeCliente'),
                    'documento' => $this->input->post('documento'),
                    'telefone' => $this->input->post('telefone'),
                    'celular' => $this->input->post('celular'),
                    'email' => $this->input->post('email'),
                    'rua' => $this->input->post('rua'),
                    'numero' => $this->input->post('numero'),
                    'complemento' => $this->input->post('complemento'),
                    'bairro' => $this->input->post('bairro'),
                    'cidade' => $this->input->post('cidade'),
                    'estado' => $this->input->post('estado'),
                    'cep' => $this->input->post('cep'),
                    'contato' => $this->input->post('contato'),
                ];
            }

            if ($this->Conecte_model->edit('clientes', $data, 'idClientes', $this->session->userdata('cliente_id')) == true) {
                $this->session->set_flashdata('success', 'Dados editados com sucesso!');
                redirect(base_url() . 'index.php/mine/conta');
            } else {
            }
        }

        $data['result'] = $this->Conecte_model->getDados();

        $data['output'] = 'conecte/editar_dados';
        $this->load->view('conecte/template', $data);
    }

    public function compras()
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        $data['menuVendas'] = 'vendas';
        $this->load->library('pagination');

        $config['base_url'] = base_url() . 'index.php/mine/compras/';
        $config['total_rows'] = $this->Conecte_model->count('vendas', $this->session->userdata('cliente_id'));
        $config['per_page'] = 10;
        $config['next_link'] = 'Próxima';
        $config['prev_link'] = 'Anterior';
        $config['full_tag_open'] = '<div class="pagination alternate"><ul>';
        $config['full_tag_close'] = '</ul></div>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li><a style="color: #2D335B"><b>';
        $config['cur_tag_close'] = '</b></a></li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['first_link'] = 'Primeira';
        $config['last_link'] = 'Última';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';

        $this->pagination->initialize($config);

        $data['results'] = $this->Conecte_model->getCompras('vendas', '*', '', $config['per_page'], $this->uri->segment(3), '', '', $this->session->userdata('cliente_id'));

        $data['output'] = 'conecte/compras';
        $this->load->view('conecte/template', $data);
    }

    public function cobrancas()
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        $this->load->library('pagination');
        $this->load->config('payment_gateways');

        $data['menuCobrancas'] = 'cobrancas';

        $config['base_url'] = base_url() . 'index.php/mine/cobrancas/';
        $config['total_rows'] = $this->Conecte_model->count('cobrancas', $this->session->userdata('cliente_id'));
        $config['per_page'] = 10;
        $config['next_link'] = 'Próxima';
        $config['prev_link'] = 'Anterior';
        $config['full_tag_open'] = '<div class="pagination alternate"><ul>';
        $config['full_tag_close'] = '</ul></div>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li><a style="color: #2D335B"><b>';
        $config['cur_tag_close'] = '</b></a></li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['first_link'] = 'Primeira';
        $config['last_link'] = 'Última';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';

        $this->pagination->initialize($config);

        $data['results'] = $this->Conecte_model->getCobrancas('cobrancas', '*', '', $config['per_page'], $this->uri->segment(3), '', '', $this->session->userdata('cliente_id'));
        $data['output'] = 'conecte/cobrancas';

        $this->load->view('conecte/template', $data);
    }

    public function atualizarcobranca($id = null)
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eCobranca')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para atualizar cobrança.');
            redirect(base_url());
        }

        $this->load->model('cobrancas_model');
        $this->cobrancas_model->atualizarStatus($this->uri->segment(3));

        redirect(site_url('mine/cobrancas/'));
    }

    public function enviarcobranca()
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        if (! $this->uri->segment(3) || ! is_numeric($this->uri->segment(3))) {
            $this->session->set_flashdata('error', 'Item não pode ser encontrado, parâmetro não foi passado corretamente.');
            redirect('mapos');
        }

        if (! $this->permission->checkPermission($this->session->userdata('permissao'), 'eCobranca')) {
            $this->session->set_flashdata('error', 'Você não tem permissão para atualizar cobrança.');
            redirect(base_url());
        }

        $this->load->model('cobrancas_model');
        $this->cobrancas_model->enviarEmail($this->uri->segment(3));
        $this->session->set_flashdata('success', 'Email adicionado na fila.');

        redirect(site_url('mine/cobrancas/'));
    }

    public function os()
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        $data['menuOs'] = 'os';
        $this->load->library('pagination');

        $config['base_url'] = base_url() . 'index.php/mine/os/';
        $config['total_rows'] = $this->Conecte_model->count('os', $this->session->userdata('cliente_id'));
        $config['per_page'] = 10;
        $config['next_link'] = 'Próxima';
        $config['prev_link'] = 'Anterior';
        $config['full_tag_open'] = '<div class="pagination alternate"><ul>';
        $config['full_tag_close'] = '</ul></div>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li><a style="color: #2D335B"><b>';
        $config['cur_tag_close'] = '</b></a></li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['first_link'] = 'Primeira';
        $config['last_link'] = 'Última';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';

        $this->pagination->initialize($config);

        $data['results'] = $this->Conecte_model->getOs('os', '*', '', $config['per_page'], $this->uri->segment(3), '', '', $this->session->userdata('cliente_id'));

        $data['output'] = 'conecte/os';
        $this->load->view('conecte/template', $data);
    }

    public function visualizarOs($id = null)
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        $data['menuOs'] = 'os';
        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->load->model('os_model');
        $this->CI = &get_instance();
        $this->CI->load->database();

        $data['pix_key'] = $this->CI->db->get_where('configuracoes', ['config' => 'pix_key'])->row_object()->valor;
        $data['result'] = $this->os_model->getById($this->uri->segment(3));
        $data['produtos'] = $this->os_model->getProdutos($this->uri->segment(3));
        $data['servicos'] = $this->os_model->getServicos($this->uri->segment(3));
        $data['anexos'] = $this->os_model->getAnexos($this->uri->segment(3));
        $data['emitente'] = $this->mapos_model->getEmitente();
        $data['qrCode'] = $this->os_model->getQrCode(
            $id,
            $data['pix_key'],
            $data['emitente']
        );
        $data['chaveFormatada'] = $this->formatarChave($data['pix_key']);

        if ($data['result']->idClientes != $this->session->userdata('cliente_id')) {
            $this->session->set_flashdata('error', 'Esta OS não pertence ao cliente logado.');
            redirect('mine/painel');
        }

        $data['output'] = 'conecte/visualizar_os';
        $this->load->view('conecte/template', $data);
    }

    public function validarCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1+$/', $cpf)) {
            return false;
        }
        $soma1 = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma1 += $cpf[$i] * (10 - $i);
        }
        $resto1 = $soma1 % 11;
        $dv1 = ($resto1 < 2) ? 0 : 11 - $resto1;
        if ($dv1 != $cpf[9]) {
            return false;
        }
        $soma2 = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma2 += $cpf[$i] * (11 - $i);
        }
        $resto2 = $soma2 % 11;
        $dv2 = ($resto2 < 2) ? 0 : 11 - $resto2;

        return $dv2 == $cpf[10];
    }

    public function validarCNPJ($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1+$/', $cnpj)) {
            return false;
        }
        $soma1 = 0;
        for ($i = 0, $pos = 5; $i < 12; $i++, $pos--) {
            $pos = ($pos < 2) ? 9 : $pos;
            $soma1 += $cnpj[$i] * $pos;
        }
        $dv1 = ($soma1 % 11 < 2) ? 0 : 11 - ($soma1 % 11);
        if ($dv1 != $cnpj[12]) {
            return false;
        }
        $soma2 = 0;
        for ($i = 0, $pos = 6; $i < 13; $i++, $pos--) {
            $pos = ($pos < 2) ? 9 : $pos;
            $soma2 += $cnpj[$i] * $pos;
        }
        $dv2 = ($soma2 % 11 < 2) ? 0 : 11 - ($soma2 % 11);

        return $dv2 == $cnpj[13];
    }

    public function formatarChave($chave)
    {
        if ($this->validarCPF($chave)) {
            return substr($chave, 0, 3) . '.' . substr($chave, 3, 3) . '.' . substr($chave, 6, 3) . '-' . substr($chave, 9);
        } elseif ($this->validarCNPJ($chave)) {
            return substr($chave, 0, 2) . '.' . substr($chave, 2, 3) . '.' . substr($chave, 5, 3) . '/' . substr($chave, 8, 4) . '-' . substr($chave, 12);
        } elseif (strlen($chave) === 11) {
            return '(' . substr($chave, 0, 2) . ') ' . substr($chave, 2, 5) . '-' . substr($chave, 7);
        }

        return $chave;
    }

    public function gerarPagamentoGerencianetBoleto()
    {
        print_r(json_encode(['code' => 4001, 'error' => 'Erro interno', 'errorDescription' => 'Cobrança não pode ser gerada pelo lado do cliente']));

    }

    public function gerarPagamentoGerencianetLink()
    {
        print_r(json_encode(['code' => 4001, 'error' => 'Erro interno', 'errorDescription' => 'Cobrança não pode ser gerada pelo lado do cliente']));

    }

    public function imprimirOs($id = null)
    {
        if (!session_id() || !$this->session->userdata('conectado')) {
            redirect('mine');
        }

        $data['menuOs'] = 'os';
        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->load->model('os_model');
        $data['result'] = $this->os_model->getById($this->uri->segment(3));
        $data['produtos'] = $this->os_model->getProdutos($this->uri->segment(3));
        $data['servicos'] = $this->os_model->getServicos($this->uri->segment(3));
        $data['emitente'] = $this->mapos_model->getEmitente();
        $data['pix_key'] = $this->db->get_where('configuracoes', ['config' => 'pix_key'])->row_object()->valor;
        $data['qrCode'] = $this->os_model->getQrCode(
            $id,
            $data['pix_key'],
            $data['emitente']
        );
        $data['chaveFormatada'] = $this->formatarChave($data['pix_key']);

        if ($data['result']->idClientes != $this->session->userdata('cliente_id')) {
            $this->session->set_flashdata('error', 'Esta OS não pertence ao cliente logado.');
            redirect('mine/painel');
        }

        $this->load->view('conecte/imprimirOs', $data);
    }

    public function visualizarCompra($id = null)
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        $data['menuVendas'] = 'vendas';
        $data['custom_error'] = '';
        $this->CI = &get_instance();
        $this->CI->load->database();
        $this->load->model('mapos_model');
        $this->load->model('os_model');
        $this->load->model('vendas_model');

        $data['result'] = $this->vendas_model->getById($this->uri->segment(3));
        $data['produtos'] = $this->vendas_model->getProdutos($this->uri->segment(3));
        $data['emitente'] = $this->mapos_model->getEmitente();
        $data['pix_key'] = $this->CI->db->get_where('configuracoes', ['config' => 'pix_key'])->row_object()->valor;
        $data['qrCode'] = $this->vendas_model->getQrCode(
            $id,
            $data['pix_key'],
            $data['emitente']
        );
        $data['chaveFormatada'] = $this->formatarChave($data['pix_key']);
        
        if ($data['result']->clientes_id != $this->session->userdata('cliente_id')) {
            $this->session->set_flashdata('error', 'Esta OS não pertence ao cliente logado.');
            redirect('mine/painel');
        }

        $data['output'] = 'conecte/visualizar_compra';

        $this->load->view('conecte/template', $data);
    }

    public function imprimirCompra($id = null)
    {
        if (!session_id() || !$this->session->userdata('conectado')) {
            redirect('mine');
        }

        $data['menuVendas'] = 'vendas';
        $data['custom_error'] = '';

        $this->load->model('mapos_model');
        $this->load->model('vendas_model');
        $this->load->model('os_model');

        $data['result'] = $this->vendas_model->getById($id);
        $data['produtos'] = $this->vendas_model->getProdutos($id);
        $data['emitente'] = $this->mapos_model->getEmitente();

        $this->CI = &get_instance();
        $this->CI->load->database();
        $data['pix_key'] = $this->CI->db->get_where('configuracoes', ['config' => 'pix_key'])->row_object()->valor;
        $data['qrCode'] = $this->vendas_model->getQrCode($id, $data['pix_key'], $data['emitente']);
        $data['chaveFormatada'] = $this->formatarChave($data['pix_key']);

        if ($data['result']->clientes_id != $this->session->userdata('cliente_id')) {
            $this->session->set_flashdata('error', 'Esta venda não pertence ao cliente logado.');
            redirect('mine/painel');
        }

        $this->load->view('conecte/imprimirVenda', $data);
    }

    public function minha_ordem_de_servico($y = null, $when = null)
    {
        // O identificador enviado por e-mail é apenas uma ofuscação reversível
        // (y = 7653 * ID + 44023), portanto não pode ser tratado como segredo.
        // O cliente precisa estar autenticado e a OS precisa ser dele.
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        if (($y == null) || (! is_numeric($y))) {
            $this->session->set_flashdata('error', 'Ordem de serviço não encontrada.');
            redirect('mine/painel');
        }

        $y = intval($y);
        $id = ($y - 44023) / 7653;

        if ($id <= 0 || floor($id) != $id) {
            $this->session->set_flashdata('error', 'Ordem de serviço não encontrada.');
            redirect('mine/painel');
        }

        $data['menuOs'] = 'os';
        $this->data['custom_error'] = '';
        $this->load->model('mapos_model');
        $this->load->model('os_model');
        $data['result'] = $this->os_model->getById((int) $id);

        if ($data['result'] == null) {
            $this->session->set_flashdata('error', 'Ordem de serviço não encontrada.');
            redirect('mine/painel');
        }

        if ($data['result']->idClientes != $this->session->userdata('cliente_id')) {
            $this->session->set_flashdata('error', 'Esta OS não pertence ao cliente logado.');
            redirect('mine/painel');
        }

        $data['produtos'] = $this->os_model->getProdutos((int) $id);
        $data['servicos'] = $this->os_model->getServicos((int) $id);
        $data['emitente'] = $this->mapos_model->getEmitente();

        $this->load->view('conecte/minha_os', $data);
    }

    public function adicionarOs()
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }
        $this->load->library('form_validation');

        $this->form_validation->set_rules('descricaoProduto', 'Descrição', 'required');
        $this->form_validation->set_rules('defeito', 'Defeito');
        $this->form_validation->set_rules('observacoes', 'Observações');

        if ($this->form_validation->run() == false) {
            $this->data['custom_error'] = (validation_errors() ? true : false);
        } else {
            $id = null;
            $usuario = $this->db->query('SELECT usuarios_id, count(*) as down FROM os GROUP BY usuarios_id ORDER BY down LIMIT 1')->row();
            if ($usuario == null) {
                $this->db->where('situacao', 1);
                $this->db->limit(1);
                $usuario = $this->db->get('usuarios')->row();

                if ($usuario->idUsuarios == null) {
                    $this->session->set_flashdata('error', 'Ocorreu um erro ao cadastrar a ordem de serviço, por favor contate o administrador do sistema.');
                    redirect('mine/os');
                } else {
                    $id = $usuario->idUsuarios;
                }
            } else {
                $id = $usuario->usuarios_id;
            }

            $data = [
                'dataInicial' => date('Y-m-d'),
                'clientes_id' => $this->session->userdata('cliente_id'),
                'usuarios_id' => $id,
                'dataFinal' => date('Y-m-d'),
                'descricaoProduto' => $this->security->xss_clean($this->input->post('descricaoProduto')),
                'defeito' => $this->security->xss_clean($this->input->post('defeito')),
                'status' => 'Aberto',
                'observacoes' => $this->security->xss_clean(set_value('observacoes')),
                'faturado' => 0,
            ];

            if (is_numeric($id = $this->Conecte_model->add('os', $data, true))) {
                $this->load->model('mapos_model');
                $this->load->model('usuarios_model');

                $idOs = $id;
                $os = $this->Conecte_model->getById($id);

                $remetentes = [];
                $usuarios = $this->usuarios_model->getAll();

                foreach ($usuarios as $usuario) {
                    array_push($remetentes, $usuario->email);
                }
                array_push($remetentes, $os->email);

                $this->enviarOsPorEmail($idOs, $remetentes, 'Nova Ordem de Serviço #' . $idOs . ' - Criada pelo Cliente');
                $this->session->set_flashdata('success', 'OS adicionada com sucesso!');
                redirect('mine/detalhesOs/' . $id);
            } else {
                $this->data['custom_error'] = '<div class="form_error"><p>Ocorreu um erro.</p></div>';
            }
        }

        $data['output'] = 'conecte/adicionarOs';
        $this->load->view('conecte/template', $data);
    }

    public function detalhesOs($id = null)
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        if (! is_numeric($id) || $id == null) {
            $this->session->set_flashdata('error', 'Ordem de serviço não encontrada.');
            redirect('mine/painel');
        }

        $this->load->model('mapos_model');
        $this->load->model('os_model');

        $this->data['result'] = $this->os_model->getById((int) $id);

        // Sem OS, ou OS de outro cliente, o acesso é negado.
        if (! $this->data['result'] || $this->data['result']->idClientes != $this->session->userdata('cliente_id')) {
            $this->session->set_flashdata('error', 'Esta OS não pertence ao cliente logado.');
            redirect('mine/painel');
        }

        $this->data['produtos'] = $this->os_model->getProdutos((int) $id);
        $this->data['servicos'] = $this->os_model->getServicos((int) $id);
        $this->data['anexos'] = $this->os_model->getAnexos((int) $id);

        $this->data['output'] = 'conecte/detalhes_os';
        $this->load->view('conecte/template', $this->data);
    }

    public function cadastrar()
    {
        $this->load->model('clientes_model', '', true);
        $this->load->library('form_validation');
        $this->data['custom_error'] = '';
        $id = 0;

        $captchaEsperado = (string) $this->session->userdata('captchaWord');
        $captchaInformado = (string) $this->input->post('captcha');

        // Sem a checagem de "vazio", uma sessão sem captcha gerado compara
        // '' com '' e libera o cadastro sem resolver a imagem.
        if ($this->form_validation->run('clientes') == false) {
            $this->data['custom_error'] = (validation_errors() ? '<div class="form_error">' . validation_errors() . '</div>' : false);
        } elseif ($captchaEsperado === '' || $captchaInformado === '' || strtolower($captchaInformado) !== strtolower($captchaEsperado)) {
            // Um captcha só vale para uma tentativa.
            $this->session->unset_userdata('captchaWord');
            $this->session->set_flashdata('error', 'Os caracteres da imagem não foram preenchidos corretamente!');
        } else {
            // Consome o captcha para impedir reuso do mesmo código.
            $this->session->unset_userdata('captchaWord');

            $data = [
                'nomeCliente' => set_value('nomeCliente'),
                'documento' => set_value('documento'),
                'telefone' => set_value('telefone'),
                'celular' => $this->input->post('celular'),
                'email' => set_value('email'),
                'senha' => password_hash($this->input->post('senha'), PASSWORD_DEFAULT),
                'rua' => set_value('rua'),
                'complemento' => set_value('complemento'),
                'numero' => set_value('numero'),
                'bairro' => set_value('bairro'),
                'cidade' => set_value('cidade'),
                'estado' => set_value('estado'),
                'cep' => set_value('cep'),
                'dataCadastro' => date('Y-m-d'),
                'contato' => $this->input->post('contato'),
            ];

            $id = $this->clientes_model->add('clientes', $data);

            if ($id > 0) {
                $this->enviarEmailBoasVindas($id);
                $this->enviarEmailTecnicoNotificaClienteNovo($id);
                $this->session->set_flashdata('success', 'Cadastro realizado com sucesso! <br> Um e-mail de boas vindas será enviado para ' . $data['email']);
                redirect(base_url() . 'index.php/mine');
            } else {
                $this->session->set_flashdata('error', 'Falha ao realizar cadastro!');
            }
        }

        $this->load->view('conecte/cadastrar', $this->data);
    }

    public function downloadanexo($id = null)
    {
        if (! session_id() || ! $this->session->userdata('conectado')) {
            redirect('mine');
        }

        if ($id == null || ! is_numeric($id)) {
            $this->session->set_flashdata('error', 'Anexo não encontrado.');
            redirect('mine/painel');
        }

        $this->db->where('idAnexos', (int) $id);
        $file = $this->db->get('anexos', 1)->row();

        if (! $file) {
            $this->session->set_flashdata('error', 'Anexo não encontrado.');
            redirect('mine/painel');
        }

        // O anexo precisa pertencer a uma OS do cliente autenticado.
        $this->load->model('os_model');
        $os = $this->os_model->getById($file->os_id);

        if (! $os || $os->idClientes != $this->session->userdata('cliente_id')) {
            $this->session->set_flashdata('error', 'Anexo não encontrado.');
            redirect('mine/painel');
        }

        $this->load->library('zip');
        $this->zip->read_file($file->path . '/' . $file->anexo);
        $this->zip->download('file' . date('d-m-Y-H.i.s') . '.zip');
    }

    private function check_credentials($email)
    {
        $this->db->where('email', $email);
        $this->db->limit(1);

        return $this->db->get('clientes')->row();
    }

    private function check_token($token)
    {
        // Sem essa guarda, um token nulo vira "WHERE token IS NULL" e pode
        // casar com uma linha da tabela.
        if ($token === null || $token === '') {
            return null;
        }

        $this->db->where('token', $token);
        $this->db->limit(1);

        return $this->db->get('resets_de_senha')->row();
    }

    private function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $dateStart = new \DateTime($date);
        $dateNow = new \DateTime(date($format));

        $dateDiff = $dateStart->diff($dateNow);

        if ($dateDiff->days >= 1) {
            return true;
        } else {
            return false;
        }
    }

    private function enviarRecuperarSenha($idClientes, $clienteEmail, $assunto, $token)
    {
        $dados = [];
        $this->load->model('mapos_model');
        $this->load->model('clientes_model', '', true);

        $dados['emitente'] = $this->mapos_model->getEmitente();
        $dados['cliente'] = $this->clientes_model->getById($idClientes);
        $dados['resets_de_senha'] = json_decode($token);

        $emitente = $dados['emitente'];
        $remetente = $clienteEmail;

        $html = $this->load->view('conecte/emails/clientenovasenha', $dados, true);

        $this->load->model('email_model');

        if ($emitente == null) {
            $this->session->set_flashdata(['error' => 'Cadastrar Emitente.\n\n Por favor contate o administrador do sistema.']);

            return redirect(base_url() . 'index.php/mine/resetarSenha');
        }

        $headers = [
            'From' => "\"$emitente->nome\" <$emitente->email>",
            'Subject' => $assunto,
            'Return-Path' => '',
        ];
        $email = [
            'to' => $remetente,
            'message' => $html,
            'status' => 'pending',
            'date' => date('Y-m-d H:i:s'),
            'headers' => json_encode($headers),
        ];

        return $this->email_model->add('email_queue', $email);
    }

    private function enviarOsPorEmail($idOs, $remetentes, $assunto)
    {
        $dados = [];

        $this->load->model('mapos_model');
        $this->load->model('os_model');
        $dados['result'] = $this->os_model->getById($idOs);
        if (! isset($dados['result']->email)) {
            return false;
        }

        $dados['produtos'] = $this->os_model->getProdutos($idOs);
        $dados['servicos'] = $this->os_model->getServicos($idOs);
        $dados['emitente'] = $this->mapos_model->getEmitente();

        $emitente = $dados['emitente'];
        if (! isset($emitente)) {
            return false;
        }

        $html = $this->load->view('os/emails/os', $dados, true);

        $this->load->model('email_model');

        $remetentes = array_unique($remetentes);
        foreach ($remetentes as $remetente) {
            $headers = [
                'From' => $emitente->email,
                'Subject' => $assunto,
                'Return-Path' => '',
            ];
            $email = [
                'to' => $remetente,
                'message' => $html,
                'status' => 'pending',
                'date' => date('Y-m-d H:i:s'),
                'headers' => json_encode($headers),
            ];
            $this->email_model->add('email_queue', $email);
        }

        return true;
    }

    private function enviarEmailBoasVindas($id)
    {
        $dados = [];
        $this->load->model('mapos_model');
        $this->load->model('clientes_model', '', true);

        $dados['emitente'] = $this->mapos_model->getEmitente();
        $dados['cliente'] = $this->clientes_model->getById($id);

        $emitente = $dados['emitente'];
        $remetente = $dados['cliente'];
        $assunto = 'Bem-vindo!';

        $html = $this->load->view('os/emails/clientenovo', $dados, true);

        $this->load->model('email_model');

        $headers = [
            'From' => "\"$emitente->nome\" <$emitente->email>",
            'Subject' => $assunto,
            'Return-Path' => '',
        ];
        $email = [
            'to' => $remetente->email,
            'message' => $html,
            'status' => 'pending',
            'date' => date('Y-m-d H:i:s'),
            'headers' => json_encode($headers),
        ];

        return $this->email_model->add('email_queue', $email);
    }

    private function enviarEmailTecnicoNotificaClienteNovo($id)
    {
        $dados = [];
        $this->load->model('mapos_model');
        $this->load->model('clientes_model', '', true);
        $this->load->model('usuarios_model');

        $dados['emitente'] = $this->mapos_model->getEmitente();
        $dados['cliente'] = $this->clientes_model->getById($id);

        $emitente = $dados['emitente'];
        $assunto = 'Novo Cliente Cadastrado no Sistema';

        $usuarios = [];
        $usuarios = $this->usuarios_model->getAll();

        foreach ($usuarios as $usuario) {
            $dados['usuario'] = $usuario;
            $html = $this->load->view('os/emails/clientenovonotifica', $dados, true);
            $headers = [
                'From' => "\"$emitente->nome\" <$emitente->email>",
                'Subject' => $assunto,
                'Return-Path' => '',
            ];
            $email = [
                'to' => $usuario->email,
                'message' => $html,
                'status' => 'pending',
                'date' => date('Y-m-d H:i:s'),
                'headers' => json_encode($headers),
            ];
            $this->email_model->add('email_queue', $email);
        }
    }

    public function captcha()
    {
        header('Content-type: image/jpeg');

        $arrFont = ['font-ZXX_Noise.otf', 'font-karabine.ttf', 'font-capture.ttf', 'font-captcha.ttf'];
        shuffle($arrFont);

        $codigoCaptcha = substr(bin2hex(random_bytes(4)), 0, 7);
        $img = imagecreatefromjpeg('./assets/img/captcha_bg.jpg');
        $corCaptcha = imagecolorallocate($img, 255, 0, 0);
        $font = './assets/font-awesome/' . $arrFont[0];

        imagettftext($img, 23, 0, 5, rand(30, 35), $corCaptcha, $font, $codigoCaptcha);
        imagepng($img);
        imagedestroy($img);

        $this->session->set_userdata('captchaWord', $codigoCaptcha);
    }

}

/* End of file conecte.php */
/* Location: ./application/controllers/conecte.php */
