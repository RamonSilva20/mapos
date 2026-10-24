<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Área do cliente (#2848).
 *
 * Telas de entrada (login, cadastro, recuperação de senha) usam a superfície
 * escura de views/entrada/; as telas de quem está logado usam a moldura
 * própria de views/conecte/template.php (Top Nav escura no desktop e
 * tabbar-mobile no celular, DESIGN.md), com os componentes da v5.
 */
class Mine extends CI_Controller
{
    /** Validade do token de recuperação de senha (#2875). */
    public const TOKEN_VALIDADE_SEGUNDOS = 3600;

    /** Itens por página das listagens e itens recentes do início. */
    private const POR_PAGINA = 10;

    private const RECENTES = 5;

    /** @var array<string, string>|null Cache de configuracoes(). */
    private ?array $configuracoesLidas = null;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Conecte_model');
        $this->load->helper(['Security_helper', 'os', 'vendas', 'financeiro', 'clientes', 'conecte']);
    }

    public function index()
    {
        $this->load->view('conecte/login', [
            'erro' => $this->session->flashdata('error'),
            'sucesso' => $this->session->flashdata('success'),
            // O atalho da listagem de clientes abre mine?e=<e-mail> com o campo preenchido.
            'email' => trim((string) $this->input->get('e')),
        ]);
    }

    public function sair()
    {
        $this->session->sess_destroy();
        redirect('mine');
    }

    // --- Recuperação de senha ---------------------------------------------

    public function resetarSenha()
    {
        $this->load->view('conecte/resetar_senha', ['erro' => $this->session->flashdata('error')]);
    }

    /**
     * Grava a senha nova a partir do token (formulário de nova_senha). Com
     * erro de campo, a tela volta com o erro; com token inválido, volta para o
     * login com a mensagem genérica.
     */
    public function senhaSalvar()
    {
        $tokenEmClaro = (string) $this->input->post('token');
        if ($tokenEmClaro === '') {
            return redirect('mine');
        }

        $token = $this->check_token($tokenEmClaro);
        $cliente = $token ? $this->check_credentials($token->email) : null;

        // O token precisa existir, estar dentro da validade, ainda não ter
        // sido utilizado e corresponder a um cliente existente.
        if ($token == null || $cliente == null || ! $this->tokenVigente($token) || $token->email != $cliente->email) {
            log_info('Alteração de senha. Porém, o token é inválido, expirado ou já utilizado.');
            $this->session->set_flashdata('error', 'Token inválido ou expirado. Solicite uma nova recuperação de senha.');

            return redirect('mine');
        }

        $erros = $this->validarRegras([['field' => 'senha', 'label' => 'Senha', 'rules' => 'required|min_length[8]|max_length[72]']]);
        $confirmacao = conecteErroDaConfirmacao($this->input->post());
        if ($confirmacao !== null && ! isset($erros['senha'])) {
            $erros['senha_confirmacao'] = $confirmacao;
        }

        if ($erros !== []) {
            return $this->load->view('conecte/nova_senha', ['token' => $tokenEmClaro, 'erros' => $erros]);
        }

        $this->load->model('resetSenhas_model', '', true);
        $senha = password_hash((string) $this->input->post('senha'), PASSWORD_DEFAULT);
        if (! $this->Conecte_model->edit('clientes', ['senha' => $senha], 'idClientes', $cliente->idClientes)
            || ! $this->resetSenhas_model->edit('resets_de_senha', ['token_utilizado' => true], 'id', $token->id)) {
            return $this->load->view('conecte/nova_senha', ['token' => $tokenEmClaro, 'erros' => ['_geral' => 'Não foi possível salvar a senha. Tente de novo.']]);
        }

        log_info('Alteração da senha realizada com sucesso.');
        $this->session->set_flashdata('success', 'Senha alterada. Entre com a senha nova.');

        return redirect('mine?' . http_build_query(['e' => $cliente->email]));
    }

    public function tokenManual()
    {
        if ($this->input->method() !== 'post') {
            return $this->load->view('conecte/token_digita', ['erro' => null]);
        }

        $digitado = trim((string) $this->input->post('token'));
        if ($digitado === '') {
            return $this->load->view('conecte/token_digita', ['erro' => 'Cole o código que chegou no seu e-mail.']);
        }

        $token = $this->check_token($digitado);

        // Um token inexistente, expirado ou já utilizado recebe sempre a
        // mesma resposta genérica, para não revelar quais tokens existem.
        if ($token == null || ! $this->tokenVigente($token)) {
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

        return $this->load->view('conecte/nova_senha', ['token' => $digitado, 'erros' => []]);
    }

    public function verifyTokenSenha()
    {
        $segment = $this->uri->uri_to_assoc(3);
        $token = $this->check_token($segment['token'] ?? null);

        // Um token inexistente, expirado ou já utilizado recebe sempre a mesma
        // resposta genérica, para não revelar quais tokens existem.
        if ($token == null || ! $this->tokenVigente($token)) {
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

        return $this->load->view('conecte/nova_senha', ['token' => $segment['token'], 'erros' => []]);
    }

    public function gerarTokenResetarSenha()
    {
        $emailSolicitado = (string) $this->input->post('email');

        // Limite de pedidos por e-mail e IP (#2879): cada pedido conta, exista ou
        // não a conta, e quem passa do limite recebe a mesma resposta de sucesso,
        // sem e-mail, para o limite não revelar quais e-mails têm cadastro nem
        // permitir encher a caixa de um cliente.
        $this->load->library('Limite_login');
        $ip = $this->input->ip_address();

        if ($this->limite_login->bloqueado('reset', $emailSolicitado, $ip)) {
            log_info('Pedido de alteração de senha ignorado pelo limite de tentativas.');
            $this->session->set_flashdata('success', 'Solicitação realizada com sucesso! Um e-mail com as instruções será enviado para ' . $emailSolicitado . '.');
            redirect(base_url() . 'index.php/mine');
        }
        $this->limite_login->registrarFalha('reset', $emailSolicitado, $ip);

        if (! $cliente = $this->check_credentials($emailSolicitado)) {
            // Mesma resposta de sucesso quando o e-mail não existe, para não
            // permitir enumeração de contas.
            log_info('Cliente solicitou alteração de senha para um e-mail inexistente.');
            // Texto puro: o alert da tela de login já escapa a mensagem.
            $this->session->set_flashdata('success', 'Solicitação realizada com sucesso! Um e-mail com as instruções será enviado para ' . $emailSolicitado . '.');
            redirect(base_url() . 'index.php/mine');
        } else {
            $this->load->model('resetSenhas_model', '', true);

            // Só o hash do token vai para o banco; o token em claro vai só no
            // e-mail. Um pedido novo invalida os anteriores do mesmo e-mail (#2875).
            [$tokenEmClaro, $tokenHash] = tokenRecuperacaoGerar();
            $this->db->where('email', $cliente->email)->where('token_utilizado', 0)->update('resets_de_senha', ['token_utilizado' => 1]);

            $data = [
                'email' => $cliente->email,
                'token' => $tokenHash,
                'data_expiracao' => date('Y-m-d H:i:s', time() + self::TOKEN_VALIDADE_SEGUNDOS),
                'token_utilizado' => 0,
            ];
            if ($this->resetSenhas_model->add('resets_de_senha', $data) == true) {
                $this->enviarRecuperarSenha($cliente->idClientes, $cliente->email, 'Recuperar Senha', json_encode(['token' => $tokenEmClaro] + $data));
                log_info('Cliente solicitou alteração de senha.');
                $this->session->set_flashdata('success', 'Solicitação realizada com sucesso! Um e-mail com as instruções será enviado para ' . $cliente->email . '.');
                redirect(base_url() . 'index.php/mine');
            } else {
                $this->session->set_flashdata('error', 'Falha ao realizar solicitação!');
                log_info('Cliente solicitou alteração de senha. Porém falhou ao realizar solicitação!');
                redirect('mine/resetarSenha');
            }
        }
    }

    // --- Login ----------------------------------------------------------

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

            return;
        }

        $email = $this->input->post('email');
        $password = $this->input->post('senha');
        $ip = $this->input->ip_address();

        // Limite de tentativas (#2870): bloqueado, nem consulta a senha.
        $this->load->library('Limite_login');
        if ($this->limite_login->bloqueado('cliente', $email, $ip)) {
            echo json_encode(['result' => false, 'message' => Limite_login::MENSAGEM, 'MAPOS_TOKEN' => $this->security->get_csrf_hash()]);

            return;
        }

        $cliente = $this->check_credentials($email);

        // Mesma mensagem para e-mail inexistente e senha errada: mensagens
        // distintas revelam quais e-mails possuem cadastro.
        $senhaConfere = senhaConfere((string) $password, $cliente->senha ?? null);
        if (! $cliente || ! $senhaConfere) {
            $this->limite_login->registrarFalha('cliente', $email, $ip);
            echo json_encode(['result' => false, 'message' => 'Os dados de acesso estão incorretos.', 'MAPOS_TOKEN' => $this->security->get_csrf_hash()]);

            return;
        }

        $this->limite_login->registrarSucesso('cliente', $email);

        // Novo ID de sessão a cada autenticação, para que um ID
        // fixado antes do login não continue válido depois dele.
        $this->session->sess_regenerate(true);

        $this->session->set_userdata([
            'nome' => $cliente->nomeCliente,
            'cliente_id' => $cliente->idClientes,
            'email' => $cliente->email,
            'conectado' => true,
            'isCliente' => true,
        ]);

        $this->load->model('Audit_model');
        $this->Audit_model->add([
            'usuario' => $cliente->nomeCliente,
            'tarefa' => 'Cliente ' . $cliente->nomeCliente . ' efetuou login',
            'data' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'ip' => $ip,
        ]);

        echo json_encode(['result' => true]);
    }

    // --- Telas do cliente logado ----------------------------------------------

    public function painel()
    {
        $idCliente = $this->clienteLogado();

        return $this->tela('conecte/painel', 'painel', [
            'os' => $this->Conecte_model->getLastOs($idCliente, self::RECENTES),
            'compras' => $this->Conecte_model->getLastCompras($idCliente, self::RECENTES),
            'resumo' => [
                'os_em_andamento' => $this->Conecte_model->contarOsEmAndamento($idCliente),
                'os' => $this->Conecte_model->count('os', $idCliente),
                'compras' => $this->Conecte_model->count('vendas', $idCliente),
                'cobrancas' => $this->Conecte_model->count('cobrancas', $idCliente),
            ],
        ], $this->acaoAbrirOs());
    }

    public function conta()
    {
        $this->clienteLogado();

        return $this->tela('conecte/conta', 'conta', ['cliente' => $this->Conecte_model->getDados()]);
    }

    /**
     * "Meus dados" (#2851): POST comum; com erro, a tela volta com os valores
     * e o erro em cada campo; com sucesso, volta para a conta com o toast. O
     * cadastro alterado é sempre o da sessão.
     */
    public function editarDados()
    {
        $idCliente = $this->clienteLogado();
        $cliente = $this->Conecte_model->getDados();
        $erros = [];

        if ($this->input->method() === 'post') {
            $erros = $this->validarRegras(conecteRegrasDoCliente($idCliente));
            $confirmacao = conecteErroDaConfirmacao($this->input->post());
            if ($confirmacao !== null && ! isset($erros['senha'])) {
                $erros['senha_confirmacao'] = $confirmacao;
            }

            // Trocar e-mail (o login) ou senha pede a senha atual (#2879): quem só
            // sequestrou a sessão não assume a conta de forma permanente.
            $dados = $erros === [] ? conecteDadosDoCliente($this->input->post(), false) : [];
            if ($erros === [] && (strcasecmp((string) $dados['email'], (string) $cliente->email) !== 0 || ! empty($dados['senha'])) && ! senhaConfere((string) $this->input->post('senha_atual'), (string) $cliente->senha)) {
                $erros['senha_atual'] = 'Para trocar o e-mail ou a senha, informe a sua senha atual.';
            }

            if ($erros === []) {
                if ($this->Conecte_model->edit('clientes', $dados, 'idClientes', $idCliente)) {
                    $this->session->set_userdata(['nome' => $dados['nomeCliente'], 'email' => $dados['email']]);
                    $this->session->set_flashdata('success', 'Seus dados foram salvos.');

                    return redirect('mine/conta');
                }

                $erros['_geral'] = 'Não foi possível salvar. Tente de novo.';
            }
        }

        return $this->tela('conecte/editar_dados', 'conta', [
            'valores' => clienteValoresDoFormulario($this->input->method() === 'post' ? $this->input->post() : null, $cliente),
            'erros' => $erros,
        ]);
    }

    public function os()
    {
        $idCliente = $this->clienteLogado();
        $offset = max(0, (int) $this->uri->segment(3));
        $total = $this->Conecte_model->count('os', $idCliente);

        return $this->tela('conecte/os', 'os', [
            'results' => $this->Conecte_model->getOs('os', '*', '', self::POR_PAGINA, $offset, '', '', $idCliente),
            'total' => $total,
            'paginacao' => $this->paginacao(site_url('mine/os'), $total, $offset),
        ], $this->acaoAbrirOs());
    }

    /**
     * Tela da OS para o cliente: status, valores, textos, produtos, serviços,
     * anexos e o PIX. Só a OS do cliente logado; as demais voltam para o
     * início sem dizer se existem.
     */
    public function visualizarOs($id = null)
    {
        $idCliente = $this->clienteLogado();
        $this->load->model('os_model');
        $this->load->model('mapos_model');

        $os = $this->osDoCliente($id, $idCliente);
        $idOs = (int) $os->idOs;
        $emitente = $this->mapos_model->getEmitente();
        $pixKey = $this->configuracao('pix_key');
        $totais = $this->os_model->totais($os);
        $pixPayload = $this->os_model->getPixPayload($idOs, $pixKey, $emitente);

        return $this->tela('conecte/visualizar_os', 'os', [
            'os' => $os,
            'totais' => $totais,
            'produtos' => $this->os_model->getProdutos($idOs),
            'servicos' => $this->os_model->getServicos($idOs),
            'anexos' => $this->os_model->getAnexos($idOs),
            // Os trechos do painel (os/partes/*) sem as ações de edição.
            'pode' => ['editar' => false],
            'garantia_ate' => osVencimentoGarantia($os->dataFinal, $os->garantia, $os->status),
            'pix' => $pixPayload === null ? null : [
                'payload' => $pixPayload,
                'qr' => $this->os_model->getQrCode($idOs, $pixKey, $emitente),
                'chave' => $this->formatarChave($pixKey),
            ],
        ]);
    }

    /** Endereço antigo da tela de detalhes: agora é a mesma tela da OS. */
    public function detalhesOs($id = null)
    {
        $this->clienteLogado();

        return redirect('mine/visualizarOs/' . (ctype_digit((string) $id) ? (int) $id : 0));
    }

    public function imprimirOs($id = null)
    {
        $idCliente = $this->clienteLogado();
        $this->load->model('mapos_model');
        $this->load->model('os_model');

        $os = $this->osDoCliente($id, $idCliente);
        $idOs = (int) $os->idOs;

        $data['result'] = $os;
        $data['produtos'] = $this->os_model->getProdutos($idOs);
        $data['servicos'] = $this->os_model->getServicos($idOs);
        $data['emitente'] = $this->mapos_model->getEmitente();
        $data['pix_key'] = $this->configuracao('pix_key');
        $data['qrCode'] = $this->os_model->getQrCode($idOs, $data['pix_key'], $data['emitente']);
        $data['chaveFormatada'] = $this->formatarChave($data['pix_key']);

        $this->load->view('conecte/imprimirOs', $data);
    }

    /**
     * Link da OS nos e-mails (mine/minha_ordem_de_servico/<y>): abre a tela da
     * OS, depois do login. O número do link só identifica a OS (ver
     * conecteIdDoLinkDoEmail()); a checagem de dono é a da tela.
     */
    public function minha_ordem_de_servico($y = null)
    {
        $this->clienteLogado();

        $id = conecteIdDoLinkDoEmail($y);
        if ($id === null) {
            $this->session->set_flashdata('error', 'Ordem de serviço não encontrada.');

            return redirect('mine/painel');
        }

        return redirect('mine/visualizarOs/' . $id);
    }

    /**
     * Abrir uma OS pelo cliente: só a descrição é obrigatória. A OS vai para o
     * técnico com menos OS e os textos são gravados no formato dos textos da
     * OS (osTextoParaGravar()), sem HTML do cliente.
     */
    public function adicionarOs()
    {
        $idCliente = $this->clienteLogado();
        $erros = [];

        if ($this->input->method() === 'post') {
            $erros = $this->validarRegras([
                ['field' => 'descricaoProduto', 'label' => 'Equipamento ou serviço', 'rules' => 'required|trim|max_length[2000]'],
                ['field' => 'defeito', 'label' => 'Defeito', 'rules' => 'trim|max_length[2000]'],
                ['field' => 'observacoes', 'label' => 'Observações', 'rules' => 'trim|max_length[2000]'],
            ]);

            $tecnico = $erros === [] ? $this->tecnicoParaNovaOs() : null;
            if ($erros === [] && $tecnico === null) {
                $erros['_geral'] = 'Não foi possível abrir a OS agora. Fale com a assistência.';
            }

            if ($erros === []) {
                $texto = fn (string $campo) => osTextoParaGravar(is_string($this->input->post($campo)) ? $this->input->post($campo) : '');
                $dados = [
                    'dataInicial' => date('Y-m-d'),
                    'clientes_id' => $idCliente,
                    'usuarios_id' => $tecnico,
                    'dataFinal' => date('Y-m-d'),
                    'descricaoProduto' => $texto('descricaoProduto'),
                    'defeito' => $texto('defeito'),
                    'status' => 'Aberto',
                    'observacoes' => $texto('observacoes'),
                    'faturado' => 0,
                ];

                $idOs = $this->Conecte_model->add('os', $dados, true);
                if (is_numeric($idOs)) {
                    $this->load->model('mapos_model');
                    $this->load->model('usuarios_model');
                    $this->load->model('os_model');

                    // Histórico de status da OS (#2842); sem usuário = aberta pelo cliente.
                    $this->os_model->registrarStatus((int) $idOs, null, 'Aberto', null);

                    $remetentes = array_map(static fn ($usuario) => $usuario->email, $this->usuarios_model->getAll());
                    $remetentes[] = (string) $this->session->userdata('email');

                    $this->enviarOsPorEmail($idOs, $remetentes, 'Nova Ordem de Serviço #' . $idOs . ' - Criada pelo Cliente');
                    $this->session->set_flashdata('success', 'OS #' . $idOs . ' aberta. A assistência vai entrar em contato.');

                    return redirect('mine/visualizarOs/' . $idOs);
                }

                $erros['_geral'] = 'Não foi possível abrir a OS. Tente de novo.';
            }
        }

        return $this->tela('conecte/adicionarOs', 'os', [
            'valores' => array_map(fn ($campo) => is_string($this->input->post($campo)) ? $this->input->post($campo) : '', ['descricaoProduto' => 'descricaoProduto', 'defeito' => 'defeito', 'observacoes' => 'observacoes']),
            'erros' => $erros,
        ]);
    }

    public function compras()
    {
        $idCliente = $this->clienteLogado();
        $offset = max(0, (int) $this->uri->segment(3));
        $total = $this->Conecte_model->count('vendas', $idCliente);

        return $this->tela('conecte/compras', 'compras', [
            'results' => $this->Conecte_model->getCompras('vendas', '*', '', self::POR_PAGINA, $offset, '', '', $idCliente),
            'total' => $total,
            'paginacao' => $this->paginacao(site_url('mine/compras'), $total, $offset),
        ]);
    }

    public function visualizarCompra($id = null)
    {
        $idCliente = $this->clienteLogado();
        $this->load->model('mapos_model');
        $this->load->model('vendas_model');

        $venda = $this->compraDoCliente($id, $idCliente);
        $idVenda = (int) $venda->idVendas;
        $emitente = $this->mapos_model->getEmitente();
        $pixKey = $this->configuracao('pix_key');
        $pixPayload = $this->vendas_model->getPixPayload($idVenda, $pixKey, $emitente);

        return $this->tela('conecte/visualizar_compra', 'compras', [
            'venda' => $venda,
            'totais' => $this->vendas_model->totais($venda),
            'produtos' => $this->vendas_model->getProdutos($idVenda),
            // Os trechos do painel (vendas/partes/*) sem as ações de edição.
            'pode' => ['itens' => false],
            'garantia_ate' => vendaGarantiaAte($venda->dataVenda, $venda->garantia),
            'pix' => $pixPayload === null ? null : [
                'payload' => $pixPayload,
                'qr' => $this->vendas_model->getQrCode($idVenda, $pixKey, $emitente),
                'chave' => $this->formatarChave($pixKey),
            ],
        ]);
    }

    public function imprimirCompra($id = null)
    {
        $idCliente = $this->clienteLogado();
        $this->load->model('mapos_model');
        $this->load->model('vendas_model');

        $venda = $this->compraDoCliente($id, $idCliente);
        $idVenda = (int) $venda->idVendas;

        $data['result'] = $venda;
        $data['produtos'] = $this->vendas_model->getProdutos($idVenda);
        $data['emitente'] = $this->mapos_model->getEmitente();
        $data['pix_key'] = $this->configuracao('pix_key');
        $data['qrCode'] = $this->vendas_model->getQrCode($idVenda, $data['pix_key'], $data['emitente']);
        $data['chaveFormatada'] = $this->formatarChave($data['pix_key']);

        $this->load->view('conecte/imprimirVenda', $data);
    }

    public function cobrancas()
    {
        $idCliente = $this->clienteLogado();
        $offset = max(0, (int) $this->uri->segment(3));
        $total = $this->Conecte_model->count('cobrancas', $idCliente);

        return $this->tela('conecte/cobrancas', 'cobrancas', [
            'results' => $this->Conecte_model->getCobrancas('cobrancas', '*', '', self::POR_PAGINA, $offset, '', '', $idCliente),
            'total' => $total,
            'paginacao' => $this->paginacao(site_url('mine/cobrancas'), $total, $offset),
        ]);
    }

    /** Exibe o anexo de uma OS do cliente (imagens e PDF na página). */
    public function anexo($id = null, $parte = null)
    {
        $this->enviarAnexo($id, $parte === 'thumb', false);
    }

    public function downloadanexo($id = null)
    {
        $this->enviarAnexo($id, false, true);
    }

    /**
     * O anexo precisa pertencer a uma OS do cliente autenticado; o arquivo vem
     * de STORAGE_PATH (#2872), nunca de uma URL pública.
     */
    private function enviarAnexo($id, bool $miniatura, bool $baixar): void
    {
        $idCliente = $this->clienteLogado();

        $file = ($id != null && is_numeric($id)) ? $this->db->where('idAnexos', (int) $id)->get('anexos', 1)->row() : null;

        $os = null;
        if ($file) {
            $this->load->model('os_model');
            $os = $this->os_model->getById($file->os_id);
        }

        $arquivo = ($os && $os->idClientes == $idCliente) ? armazenamentoArquivoDoAnexo($file, $miniatura) : null;

        if ($arquivo === null) {
            if ($miniatura) {
                show_404();
            }
            $this->session->set_flashdata('error', 'Anexo não encontrado.');
            redirect('mine/painel');
        }

        armazenamentoEnviar($arquivo, 'anexo-' . (int) $file->idAnexos . '.' . strtolower(pathinfo($arquivo, PATHINFO_EXTENSION)), $baixar);
    }

    // --- Cadastro -------------------------------------------------------------

    /**
     * Cadastro do cliente pela área do cliente, com captcha. Com erro, a tela
     * volta com os valores e o erro em cada campo; com sucesso, vai para o
     * login com o e-mail preenchido.
     */
    public function cadastrar()
    {
        $this->load->model('clientes_model', '', true);
        $erros = [];

        if ($this->input->method() === 'post') {
            $captchaEsperado = (string) $this->session->userdata('captchaWord');
            $captchaInformado = trim((string) $this->input->post('captcha'));
            // Um captcha só vale para uma tentativa, certa ou errada.
            $this->session->unset_userdata('captchaWord');

            $erros = $this->validarRegras(conecteRegrasDoCliente(null));
            $confirmacao = conecteErroDaConfirmacao($this->input->post());
            if ($confirmacao !== null && ! isset($erros['senha'])) {
                $erros['senha_confirmacao'] = $confirmacao;
            }

            // Sem a checagem de "vazio", uma sessão sem captcha gerado compara
            // '' com '' e libera o cadastro sem resolver a imagem.
            if ($captchaEsperado === '' || $captchaInformado === '' || strtolower($captchaInformado) !== strtolower($captchaEsperado)) {
                $erros['captcha'] = 'Os caracteres não conferem com a imagem. Digite os da imagem nova.';
            }

            if ($erros === []) {
                $dados = conecteDadosDoCliente($this->input->post(), true) + ['dataCadastro' => date('Y-m-d')];
                $id = $this->clientes_model->add('clientes', $dados);

                if ($id > 0) {
                    $this->enviarEmailBoasVindas($id);
                    $this->enviarEmailTecnicoNotificaClienteNovo($id);
                    $this->session->set_flashdata('success', 'Cadastro feito! Entre com o seu e-mail e a senha que você criou.');

                    return redirect('mine?' . http_build_query(['e' => $dados['email']]));
                }

                $erros['_geral'] = 'Não foi possível concluir o cadastro. Tente de novo.';
            }
        }

        $this->load->view('conecte/cadastrar', [
            'valores' => clienteValoresDoFormulario($this->input->method() === 'post' ? $this->input->post() : null, null),
            'erros' => $erros,
        ]);
    }

    public function captcha()
    {
        header('Content-type: image/png');
        header('Cache-Control: no-store');

        $arrFont = [
            'font-ZXX_Noise.otf',
            'font-karabine.ttf',
            'font-capture.ttf',
            'font-captcha.ttf',
        ];

        shuffle($arrFont);

        $codigoCaptcha = substr(bin2hex(random_bytes(4)), 0, 7);

        $img = imagecreatefromjpeg(FCPATH . 'assets/img/captcha_bg.jpg');
        $corCaptcha = imagecolorallocate($img, 255, 0, 0);

        $font = FCPATH . 'assets/fonts/captcha/' . $arrFont[0];

        imagettftext(
            $img,
            23,
            0,
            5,
            rand(30, 35),
            $corCaptcha,
            $font,
            $codigoCaptcha
        );

        imagepng($img);
        imagedestroy($img);

        $this->session->set_userdata('captchaWord', $codigoCaptcha);
    }

    // --- Apoio ----------------------------------------------------------------

    /**
     * Id do cliente da sessão; sem login, volta para a tela de entrada.
     */
    private function clienteLogado(): int
    {
        if (! session_id() || ! $this->session->userdata('conectado') || ! $this->session->userdata('cliente_id')) {
            redirect('mine');
        }

        return (int) $this->session->userdata('cliente_id');
    }

    /**
     * OS do cliente logado; id inválido, inexistente ou de outro cliente
     * recebe a mesma mensagem, para não revelar quais OS existem.
     */
    private function osDoCliente($id, int $idCliente): object
    {
        $os = ctype_digit((string) $id) ? $this->os_model->getById((int) $id) : null;

        if (! $os || (int) $os->idClientes !== $idCliente) {
            $this->session->set_flashdata('error', 'Ordem de serviço não encontrada.');
            redirect('mine/os');
        }

        return $os;
    }

    /** Venda do cliente logado; as demais recebem a mesma mensagem. */
    private function compraDoCliente($id, int $idCliente): object
    {
        $venda = ctype_digit((string) $id) ? $this->vendas_model->getById((int) $id) : null;

        if (! $venda || (int) $venda->clientes_id !== $idCliente) {
            $this->session->set_flashdata('error', 'Compra não encontrada.');
            redirect('mine/compras');
        }

        return $venda;
    }

    /** Ação principal das telas de OS: abrir uma nova (topo da moldura). */
    private function acaoAbrirOs(): array
    {
        return ['label' => 'Abrir OS', 'icon' => 'plus', 'href' => site_url('mine/adicionarOs')];
    }

    /**
     * Renderiza uma tela dentro da moldura da área do cliente
     * (views/conecte/template.php).
     *
     * @param  array|null  $acao  Props do button-primary da tela, no topo
     */
    private function tela(string $view, string $menu, array $dados = [], ?array $acao = null)
    {
        $flash = [];
        foreach (['success' => ['success', 'Sucesso!', 5000], 'error' => ['danger', 'Falha!', 8000]] as $chave => [$variante, $titulo, $duracao]) {
            $mensagem = $this->session->flashdata($chave);
            if (is_string($mensagem) && trim($mensagem) !== '') {
                $flash[] = ['variant' => $variante, 'title' => $titulo, 'duration' => $duracao, 'message' => $mensagem];
            }
        }

        $this->load->view('conecte/template', $dados + [
            'view' => $view,
            'menu' => conecteMenu($menu),
            'acao' => $acao,
            'flash' => $flash,
            'configuration' => $this->configuracoes(),
            'cliente_nome' => (string) $this->session->userdata('nome'),
        ]);
    }

    /** Props do componente pagination das listagens. */
    private function paginacao(string $urlBase, int $total, int $offset): array
    {
        return paginacaoProps([
            'base_url' => $urlBase,
            'total_rows' => $total,
            'per_page' => self::POR_PAGINA,
            'offset' => $offset,
        ]);
    }

    /**
     * Roda o form_validation com as regras e devolve os erros por campo
     * (vazio quando passou), com as mensagens em português, como o
     * MY_Controller::validarFormulario() do painel.
     *
     * @return array<string, string>
     */
    private function validarRegras(array $regras): array
    {
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();
        $this->form_validation->set_data($this->input->post() ?: []);
        $this->form_validation->set_rules($regras);

        $idioma = $this->config->item('language');
        $this->config->set_item('language', 'pt-br');
        try {
            $valido = $this->form_validation->run();
        } finally {
            $this->config->set_item('language', $idioma);
        }

        return $valido ? [] : $this->form_validation->error_array();
    }

    /**
     * Configurações do sistema (tabela configuracoes), lidas uma vez: modo de
     * cor da moldura e chave PIX.
     *
     * @return array<string, string>
     */
    private function configuracoes(): array
    {
        if ($this->configuracoesLidas === null) {
            $this->configuracoesLidas = ['app_name' => (string) $this->config->item('app_name')];
            foreach ($this->db->get('configuracoes')->result() as $linha) {
                $this->configuracoesLidas[$linha->config] = (string) $linha->valor;
            }
        }

        return $this->configuracoesLidas;
    }

    private function configuracao(string $nome): string
    {
        return $this->configuracoes()[$nome] ?? '';
    }

    /**
     * Técnico que recebe a OS aberta pelo cliente: o com menos OS; sem OS no
     * sistema, o primeiro usuário ativo.
     */
    private function tecnicoParaNovaOs(): ?int
    {
        $usuario = $this->db->query('SELECT usuarios_id, count(*) as down FROM os GROUP BY usuarios_id ORDER BY down LIMIT 1')->row();
        if ($usuario !== null && $usuario->usuarios_id) {
            return (int) $usuario->usuarios_id;
        }

        $usuario = $this->db->where('situacao', 1)->limit(1)->get('usuarios')->row();

        return $usuario ? (int) $usuario->idUsuarios : null;
    }

    private function validarCPF($cpf)
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

    private function validarCNPJ($cnpj)
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

    private function formatarChave($chave)
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

    private function check_credentials($email)
    {
        $this->db->where('email', $email);
        $this->db->limit(1);

        return $this->db->get('clientes')->row();
    }

    /**
     * Linha de resets_de_senha do token em claro, buscada pelo hash (#2875).
     * Token fora do formato (64 hex) nem consulta o banco.
     */
    private function check_token($token)
    {
        $hash = tokenRecuperacaoHash($token);
        if ($hash === null) {
            return null;
        }

        $linha = $this->db->where('token', $hash)->limit(1)->get('resets_de_senha')->row();

        return ($linha && hash_equals((string) $linha->token, $hash)) ? $linha : null;
    }

    /**
     * Token ainda não usado e dentro da validade. data_expiracao guarda o
     * instante em que o token expira.
     */
    private function tokenVigente(object $token): bool
    {
        return ! $token->token_utilizado && strtotime((string) $token->data_expiracao) > time();
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

        $remetentes = array_unique(array_filter($remetentes));
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

        if ($emitente == null || $remetente == null) {
            return false;
        }

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
        $this->load->model('email_model');

        $dados['emitente'] = $this->mapos_model->getEmitente();
        $dados['cliente'] = $this->clientes_model->getById($id);

        $emitente = $dados['emitente'];
        $assunto = 'Novo Cliente Cadastrado no Sistema';

        if ($emitente == null) {
            return;
        }

        foreach ($this->usuarios_model->getAll() as $usuario) {
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
}
