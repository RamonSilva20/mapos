<?php

namespace Libraries\Api\Modulos;

use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Modulo;
use Libraries\Api\Recursos\Cliente;
use Libraries\Api\Recursos\Endereco;
use Libraries\Api\Recursos\OrdemDeServico;
use Libraries\Api\Recursos\Venda;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Schema\Comuns;
use Libraries\Api\Schema\Schema;
use Libraries\Api\Upload;
use Limite_login;

final class AreaDoCliente extends Modulo
{
    private const CAPTCHA = 'captcha';

    private const VALIDADE_TOKEN_SENHA = 3600;

    private const MENSAGEM_RECUPERACAO = 'Se o e-mail tiver cadastro, as instruções para criar uma senha nova vão chegar nele em instantes.';

    public static function rotas(): array
    {
        $auth = 'Cliente: autenticação';
        $conta = 'Cliente: conta';
        $os = 'Cliente: ordens de serviço';
        $compras = 'Cliente: compras';

        return [
            Rota::post('/cliente/auth/login', [self::class, 'login'])->publica()->limite('login')->tag($auth)
                ->resumo('Entra na área do cliente', 'Os tokens só valem nas rotas `/cliente/...`. E-mail inexistente e senha errada têm a mesma resposta, e várias falhas bloqueiam o acesso por um tempo (`429`).')
                ->corpo(Schema::objeto([
                    'email' => Schema::email()->obrigatorio()->max(100),
                    'senha' => Schema::senha()->obrigatorio()->max(200),
                    'dispositivo' => Schema::texto()->max(100),
                ]))
                ->dados(self::schemaTokens())
                ->erros(401, 429),

            Rota::post('/cliente/auth/renovar', [self::class, 'renovar'])->publica()->limite('renovar')->tag($auth)
                ->resumo('Troca o refresh token por um par novo', 'Mesmas regras de `POST /auth/renovar`.')
                ->corpo(Schema::objeto(['refresh_token' => Schema::texto()->obrigatorio()->max(200)]))
                ->dados(self::schemaTokens())
                ->erros(401, 409, 429),

            Rota::post('/cliente/auth/logout', [self::class, 'logout'])->doCliente()->tag($auth)
                ->resumo('Encerra a sessão atual')
                ->semConteudo(),

            Rota::get('/cliente/auth/sessoes', [self::class, 'sessoes'])->doCliente()->tag($auth)
                ->resumo('Sessões abertas do cliente')
                ->dados(Schema::lista(Autenticacao::schemaSessao())),

            Rota::delete('/cliente/auth/sessoes/{sessao:s}', [self::class, 'encerrarSessao'])->doCliente()->tag($auth)
                ->resumo('Encerra uma sessão do cliente')
                ->semConteudo()
                ->erros(404),

            Rota::get('/cliente/auth/captcha', [self::class, 'captcha'])->publica()->limite('captcha')->tag($auth)
                ->resumo('Captcha do cadastro', 'Mostre a imagem e envie o `desafio` com o texto digitado em `POST /cliente/auth/cadastro`. Vale 10 minutos.')
                ->dados(Schema::objeto([
                    'imagem' => Schema::texto()->obrigatorio()->descricao('PNG como data URI.'),
                    'desafio' => Schema::texto()->obrigatorio(),
                    'expires_in' => Schema::inteiro()->obrigatorio(),
                ])),

            Rota::post('/cliente/auth/cadastro', [self::class, 'cadastrar'])->publica()->limite('cadastro')->tag($auth)
                ->resumo('Cria a conta do cliente', 'Exige o captcha. Depois do cadastro, entre com `POST /cliente/auth/login`.')
                ->corpo(self::entradaConta(true)->com([
                    'captcha_desafio' => Schema::texto()->obrigatorio()->max(2000),
                    'captcha_resposta' => Schema::texto()->obrigatorio()->max(20),
                ]))
                ->criado(Cliente::schema())
                ->erros(429),

            Rota::post('/cliente/auth/senha/recuperar', [self::class, 'recuperarSenha'])->publica()->limite('recuperar')->tag($auth)
                ->resumo('Pede a recuperação de senha', 'Sempre responde 202, tenha o e-mail cadastro ou não. O código chega por e-mail e vale 1 hora.')
                ->corpo(Schema::objeto(['email' => Schema::email()->obrigatorio()->max(100)]))
                ->resposta(202, Comuns::mensagem(), 'Pedido recebido.'),

            Rota::post('/cliente/auth/senha/redefinir', [self::class, 'redefinirSenha'])->publica()->limite('recuperar')->tag($auth)
                ->resumo('Cria a senha nova com o código do e-mail', 'O código vale uma vez. As sessões abertas do cliente são encerradas.')
                ->corpo(Schema::objeto([
                    'codigo' => Schema::texto()->obrigatorio()->max(200),
                    'nova_senha' => Schema::senha()->obrigatorio()->min(8)->max(72),
                ]))
                ->semConteudo('Senha alterada.'),

            Rota::get('/cliente/painel', [self::class, 'painel'])->doCliente()->tag($conta)
                ->resumo('Resumo da área do cliente')
                ->dados(Schema::objeto([
                    'os_em_andamento' => Schema::inteiro()->obrigatorio(),
                    'total_os' => Schema::inteiro()->obrigatorio(),
                    'total_compras' => Schema::inteiro()->obrigatorio(),
                    'total_cobrancas' => Schema::inteiro()->obrigatorio(),
                    'ultimas_os' => Schema::lista(OrdemDeServico::schemaResumo())->obrigatorio(),
                    'ultimas_compras' => Schema::lista(Venda::schemaResumo())->obrigatorio(),
                ])),

            Rota::get('/cliente/conta', [self::class, 'conta'])->doCliente()->tag($conta)
                ->resumo('Dados do cliente logado')
                ->dados(Cliente::schema()),

            Rota::patch('/cliente/conta', [self::class, 'atualizarConta'])->doCliente()->tag($conta)
                ->resumo('Altera os próprios dados', 'Trocar o e-mail ou a senha exige `senha_atual`. Trocar a senha encerra as outras sessões.')
                ->corpo(self::entradaConta(false)->semObrigatorios()->com(['senha_atual' => Schema::senha()->max(200)]))
                ->dados(Cliente::schema()),

            Rota::get('/cliente/os', [self::class, 'ordens'])->doCliente()->tag($os)
                ->resumo('OS do cliente')
                ->consulta(Comuns::paginacaoConsulta())
                ->lista(OrdemDeServico::schemaResumo()),

            Rota::post('/cliente/os', [self::class, 'abrirOs'])->doCliente()->tag($os)
                ->resumo('Abre uma OS', 'A assistência recebe o aviso por e-mail e entra em contato.')
                ->corpo(Schema::objeto([
                    'descricao_produto' => Schema::texto()->obrigatorio()->max(2000)->descricao('Equipamento ou serviço.'),
                    'defeito' => Schema::texto()->max(2000),
                    'observacoes' => Schema::texto()->max(2000),
                ]))
                ->criado(OrdemDeServico::schema())
                ->erros(503),

            Rota::get('/cliente/os/{id}', [self::class, 'verOs'])->doCliente()->tag($os)
                ->resumo('Mostra uma OS do cliente', 'Sem as anotações internas.')
                ->dados(OrdemDeServico::schema())
                ->erros(404),

            Rota::get('/cliente/os/{id}/anexos/{anexo}', [self::class, 'anexo'])->doCliente()->tag($os)
                ->resumo('Baixa um anexo da OS')
                ->consulta(Schema::objeto(['miniatura' => Schema::booleano()->padrao(false), 'download' => Schema::booleano()->padrao(false)]))
                ->arquivo()
                ->erros(404),

            Rota::get('/cliente/os/{id}/pix', [self::class, 'pixOs'])->doCliente()->tag($os)
                ->resumo('PIX para pagar a OS')
                ->dados(Ordens::schemaPix())
                ->erros(404),

            Rota::get('/cliente/compras', [self::class, 'compras'])->doCliente()->tag($compras)
                ->resumo('Compras do cliente')
                ->consulta(Comuns::paginacaoConsulta())
                ->lista(Venda::schemaResumo()),

            Rota::get('/cliente/compras/{id}', [self::class, 'verCompra'])->doCliente()->tag($compras)
                ->resumo('Mostra uma compra do cliente')
                ->dados(Venda::schema())
                ->erros(404),

            Rota::get('/cliente/compras/{id}/pix', [self::class, 'pixCompra'])->doCliente()->tag($compras)
                ->resumo('PIX para pagar a compra')
                ->dados(Ordens::schemaPix())
                ->erros(404),

            Rota::get('/cliente/cobrancas', [self::class, 'cobrancas'])->doCliente()->tag('Cliente: cobranças')
                ->resumo('Cobranças do cliente', 'Boletos, links e PIX gerados para as OS e compras.')
                ->consulta(Comuns::paginacaoConsulta())
                ->lista(Cobrancas::schema()),
        ];
    }

    private static function schemaTokens(): Schema
    {
        static $schema = null;

        return $schema ??= Schema::objeto([
            'token_type' => Schema::enum(['Bearer'])->obrigatorio(),
            'access_token' => Schema::texto()->obrigatorio(),
            'expires_in' => Schema::inteiro()->obrigatorio(),
            'refresh_token' => Schema::texto()->obrigatorio(),
            'refresh_expires_in' => Schema::inteiro()->obrigatorio(),
            'sessao' => Schema::texto()->obrigatorio(),
            'cliente' => Cliente::schema()->obrigatorio(),
        ])->nome('TokensDoCliente');
    }

    private static function entradaConta(bool $novo): Schema
    {
        return Schema::objeto([
            'nome' => Schema::texto()->obrigatorio()->max(255),
            'documento' => Schema::texto()->max(20)->regra(static fn (string $v): ?string => $v === '' || verific_cpf_cnpj($v) ? null : 'Informe um CPF ou CNPJ válido.'),
            'email' => Schema::email()->obrigatorio()->max(100),
            'telefone' => Schema::texto()->max(20),
            'celular' => Schema::texto()->max(20),
            'contato' => Schema::texto()->max(45),
            'endereco' => Endereco::entrada(),
            'senha' => Schema::senha()->min(8)->max(72)->obrigatorio($novo),
        ])->nome($novo ? 'CadastroDeCliente' : 'AlteracaoDeCliente');
    }

    public function login(): Resposta
    {
        $dados = $this->ctx->corpo();
        $ip = $this->ctx->requisicao->ip;
        $limite = $this->limite();
        $restantes = $limite->segundosRestantes('cliente', $dados['email'], $ip);
        if ($restantes > 0) {
            throw HttpErro::muitasRequisicoes($restantes, Limite_login::MENSAGEM);
        }

        $cliente = $this->db->where('email', $dados['email'])->limit(1)->get('clientes')->row();
        if (! senhaConfere($dados['senha'], $cliente->senha ?? null) || ! $cliente) {
            $limite->registrarFalha('cliente', $dados['email'], $ip);

            throw HttpErro::naoAutenticado('Os dados de acesso estão incorretos.', 'credenciais_invalidas');
        }
        $limite->registrarSucesso('cliente', $dados['email']);

        $sessao = $this->ctx->sessoes->criar(Rota::CLIENTE, (int) $cliente->idClientes, $ip, $this->ctx->requisicao->userAgent(), $dados['dispositivo'] ?? null);
        $this->db->insert('logs', [
            'usuario' => mb_substr((string) $cliente->nomeCliente, 0, 80),
            'ip' => $ip,
            'tarefa' => mb_substr('Cliente ' . $cliente->nomeCliente . ' efetuou login pela API', 0, 100),
            'data' => date('Y-m-d'),
            'hora' => date('H:i:s'),
        ]);

        return Resposta::dados($this->tokens($cliente, $sessao['sessao'], $sessao['refresh_token'], $sessao['refresh_expira_em']));
    }

    public function renovar(): Resposta
    {
        $renovado = $this->ctx->sessoes->renovar(Rota::CLIENTE, $this->ctx->corpo()['refresh_token'], $this->ctx->requisicao->ip, $this->ctx->requisicao->userAgent());
        $cliente = $this->db->where('idClientes', $renovado['titular_id'])->limit(1)->get('clientes')->row();
        if (! $cliente) {
            throw HttpErro::naoAutenticado('Faça o login novamente.', 'conta_desativada');
        }

        return Resposta::dados($this->tokens($cliente, $renovado['sessao'], $renovado['refresh_token'], $renovado['refresh_expira_em']));
    }

    public function logout(): Resposta
    {
        $t = $this->ctx->titular();
        $this->ctx->sessoes->encerrar($t->sessao, $t->tipo, $t->id);

        return Resposta::semConteudo();
    }

    public function sessoes(): Resposta
    {
        $t = $this->ctx->titular();

        return Resposta::dados(Autenticacao::listarSessoes($this->ctx->sessoes->listar($t->tipo, $t->id), $t->sessao));
    }

    public function encerrarSessao(): Resposta
    {
        $t = $this->ctx->titular();
        if (! $this->ctx->sessoes->encerrar((string) $this->ctx->parametro('sessao'), $t->tipo, $t->id, 'encerrada_pelo_titular')) {
            throw HttpErro::naoEncontrado('Sessão não encontrada.');
        }

        return Resposta::semConteudo();
    }

    public function captcha(): Resposta
    {
        $resposta = substr(bin2hex(random_bytes(4)), 0, 6);
        $fontes = glob(FCPATH . 'assets/fonts/captcha/*.{ttf,otf}', GLOB_BRACE) ?: [];
        $imagem = @imagecreatefromjpeg(FCPATH . 'assets/img/captcha_bg.jpg') ?: imagecreatetruecolor(160, 50);
        $cor = imagecolorallocate($imagem, 200, 30, 30);
        if ($fontes !== []) {
            imagettftext($imagem, 23, random_int(-6, 6), 8, random_int(30, 38), $cor, $fontes[array_rand($fontes)], $resposta);
        } else {
            imagestring($imagem, 5, 10, 15, $resposta, $cor);
        }
        ob_start();
        imagepng($imagem);
        $png = (string) ob_get_clean();

        $duracao = 600;
        $desafio = $this->ctx->tokens->emitirTemporario(self::CAPTCHA, 0, $duracao, ['h' => $this->hashDoCaptcha($resposta)]);

        return Resposta::dados(['imagem' => 'data:image/png;base64,' . base64_encode($png), 'desafio' => $desafio['token'], 'expires_in' => $duracao]);
    }

    public function cadastrar(): Resposta
    {
        $dados = $this->ctx->corpo();
        $claims = $this->ctx->tokens->validarTemporario($dados['captcha_desafio'], self::CAPTCHA, 'O captcha expirou. Peça outro em GET /cliente/auth/captcha.', 'captcha_expirado');
        if (! hash_equals((string) ($claims->h ?? ''), $this->hashDoCaptcha($dados['captcha_resposta']))) {
            throw HttpErro::validacao(['captcha_resposta' => 'Os caracteres não conferem com a imagem.']);
        }
        $this->conferirUnicos($dados, null);

        $id = $this->inserirLinha('clientes', conecteDadosDoCliente($this->paraFormulario($dados), true) + ['dataCadastro' => date('Y-m-d')]);
        $cliente = $this->db->where('idClientes', $id)->get('clientes', 1)->row();
        $this->enviarBoasVindas($cliente);

        return Resposta::criado(Cliente::de($cliente), $this->ctx->url('cliente/conta'));
    }

    public function recuperarSenha(): Resposta
    {
        $email = $this->ctx->corpo()['email'];
        $ip = $this->ctx->requisicao->ip;
        $limite = $this->limite();
        $resposta = Resposta::json(['data' => ['mensagem' => self::MENSAGEM_RECUPERACAO]], 202);

        if ($limite->bloqueado('reset', $email, $ip)) {
            return $resposta;
        }
        $limite->registrarFalha('reset', $email, $ip);

        $cliente = $this->db->where('email', $email)->limit(1)->get('clientes')->row();
        $emitente = $this->ctx->modelo('Mapos_model')->getEmitente();
        if (! $cliente || ! $emitente) {
            return $resposta;
        }

        [$claro, $hash] = tokenRecuperacaoGerar();
        $this->db->where('email', $cliente->email)->where('token_utilizado', 0)->update('resets_de_senha', ['token_utilizado' => 1]);
        $registro = ['email' => $cliente->email, 'token' => $hash, 'data_expiracao' => date('Y-m-d H:i:s', time() + self::VALIDADE_TOKEN_SENHA), 'token_utilizado' => 0];
        $this->inserirLinha('resets_de_senha', $registro);

        $html = $this->ctx->ci->load->view('conecte/emails/clientenovasenha', [
            'emitente' => $emitente,
            'cliente' => $cliente,
            'resets_de_senha' => (object) (['token' => $claro] + $registro),
        ], true);
        $this->enfileirar((string) $cliente->email, 'Recuperar Senha', $html, $emitente);
        $this->ctx->auditar('Cliente solicitou alteração de senha pela API.');

        return $resposta;
    }

    public function redefinirSenha(): Resposta
    {
        $dados = $this->ctx->corpo();
        $hash = tokenRecuperacaoHash($dados['codigo']);
        $token = $hash === null ? null : $this->db->where('token', $hash)->limit(1)->get('resets_de_senha')->row();
        $cliente = $token ? $this->db->where('email', $token->email)->limit(1)->get('clientes')->row() : null;

        if (! $token || ! $cliente || $token->token_utilizado || strtotime((string) $token->data_expiracao) <= time()) {
            throw HttpErro::regraDeNegocio('Código inválido ou expirado. Peça uma nova recuperação de senha.', 'codigo_invalido');
        }

        $this->transacao(function () use ($token, $cliente, $dados): void {
            $this->db->where('id', (int) $token->id)->where('token_utilizado', 0)->update('resets_de_senha', ['token_utilizado' => 1]);
            if ($this->db->affected_rows() !== 1) {
                throw HttpErro::regraDeNegocio('Código inválido ou expirado. Peça uma nova recuperação de senha.', 'codigo_invalido');
            }
            $this->atualizarLinha('clientes', 'idClientes', (int) $cliente->idClientes, ['senha' => password_hash($dados['nova_senha'], PASSWORD_DEFAULT)]);
            $this->ctx->sessoes->encerrarDoTitular(Rota::CLIENTE, (int) $cliente->idClientes, 'senha_alterada');
        });

        return Resposta::semConteudo();
    }

    public function painel(): Resposta
    {
        $id = $this->ctx->titular()->id;
        $modelo = $this->ctx->modelo('Conecte_model');

        return Resposta::dados([
            'os_em_andamento' => $modelo->contarOsEmAndamento($id),
            'total_os' => (int) $modelo->count('os', $id),
            'total_compras' => (int) $modelo->count('vendas', $id),
            'total_cobrancas' => (int) $modelo->count('cobrancas', $id),
            'ultimas_os' => array_map([OrdemDeServico::class, 'resumoDe'], OrdemDeServico::consultaResumo($this->db)->where('os.clientes_id', $id)->order_by('os.idOs', 'DESC')->limit(5)->get()->result()),
            'ultimas_compras' => array_map([Venda::class, 'resumoDe'], Venda::consultaResumo($this->db)->where('vendas.clientes_id', $id)->order_by('vendas.idVendas', 'DESC')->limit(5)->get()->result()),
        ]);
    }

    public function conta(): Resposta
    {
        return Resposta::dados(Cliente::de($this->cliente()));
    }

    public function atualizarConta(): Resposta
    {
        $cliente = $this->cliente();
        $id = (int) $cliente->idClientes;
        [$dados] = $this->ctx->corpoParcial(array_diff_key(Clientes::paraEntrada($cliente), ['fornecedor' => true]));
        $this->conferirUnicos($dados, $id);

        $trocaEmail = strcasecmp((string) $dados['email'], (string) $cliente->email) !== 0;
        $trocaSenha = ($dados['senha'] ?? '') !== '';
        if (($trocaEmail || $trocaSenha) && ! senhaConfere((string) ($dados['senha_atual'] ?? ''), (string) $cliente->senha)) {
            throw HttpErro::validacao(['senha_atual' => 'Para trocar o e-mail ou a senha, informe a sua senha atual.']);
        }

        $this->atualizarLinha('clientes', 'idClientes', $id, conecteDadosDoCliente($this->paraFormulario($dados), false));
        if ($trocaSenha) {
            $this->ctx->sessoes->encerrarDoTitular(Rota::CLIENTE, $id, 'senha_alterada', $this->ctx->titular()->sessao);
        }

        return Resposta::dados(Cliente::de($this->cliente()));
    }

    public function ordens(): Resposta
    {
        $id = $this->ctx->titular()->id;

        return $this->listarPelaConsulta(fn () => OrdemDeServico::consultaResumo($this->db)->where('os.clientes_id', $id), 'os.idOs', [OrdemDeServico::class, 'resumoDe']);
    }

    public function abrirOs(): Resposta
    {
        $dados = $this->ctx->corpo();
        $cliente = $this->cliente();
        $tecnico = $this->db->query('SELECT usuarios_id, COUNT(*) AS total FROM os GROUP BY usuarios_id ORDER BY total LIMIT 1')->row();
        $tecnicoId = $tecnico && $tecnico->usuarios_id ? (int) $tecnico->usuarios_id : (int) ($this->db->where('situacao', 1)->limit(1)->get('usuarios')->row()->idUsuarios ?? 0);
        if ($tecnicoId <= 0) {
            throw HttpErro::indisponivel('Não foi possível abrir a OS agora. Fale com a assistência.', 'sem_tecnico');
        }

        $id = $this->transacao(function () use ($dados, $cliente, $tecnicoId): int {
            $id = $this->inserirLinha('os', [
                'dataInicial' => date('Y-m-d'),
                'dataFinal' => date('Y-m-d'),
                'clientes_id' => (int) $cliente->idClientes,
                'usuarios_id' => $tecnicoId,
                'descricaoProduto' => osTextoParaGravar($dados['descricao_produto']),
                'defeito' => osTextoParaGravar($dados['defeito'] ?? ''),
                'observacoes' => osTextoParaGravar($dados['observacoes'] ?? ''),
                'status' => 'Aberto',
                'faturado' => 0,
            ]);
            $this->ctx->modelo('Os_model')->registrarStatus($id, null, 'Aberto', null);

            return $id;
        });

        $emitente = $this->ctx->modelo('Mapos_model')->getEmitente();
        if ($emitente) {
            $os = $this->ctx->modelo('Os_model');
            $html = $this->ctx->ci->load->view('os/emails/os', ['result' => $os->getById($id), 'produtos' => [], 'servicos' => [], 'emitente' => $emitente], true);
            $destinos = array_map(static fn ($u) => (string) $u->email, $this->db->select('email')->where('situacao', 1)->get('usuarios')->result());
            $destinos[] = (string) $cliente->email;
            foreach (array_unique(array_filter($destinos)) as $destino) {
                $this->enfileirar($destino, 'Nova Ordem de Serviço #' . $id . ' - Criada pelo Cliente', $html, $emitente, false);
            }
        }

        return Resposta::criado($this->detalheOs($this->osDoCliente($id)), $this->ctx->url('cliente/os/' . $id));
    }

    public function verOs(): Resposta
    {
        return Resposta::dados($this->detalheOs($this->osDoCliente()));
    }

    public function anexo(): Resposta
    {
        $os = $this->osDoCliente();
        $anexo = $this->ctx->modelo('Os_model')->getAnexoDaOs((int) $os->idOs, $this->ctx->id('anexo'));
        $consulta = $this->ctx->consulta();
        $arquivo = $anexo ? armazenamentoArquivoDoAnexo($anexo, (bool) $consulta['miniatura']) : null;
        if ($arquivo === null) {
            throw HttpErro::naoEncontrado('Anexo não encontrado.');
        }

        return Upload::resposta($arquivo, 'anexo-' . (int) $anexo->idAnexos . '.' . strtolower(pathinfo($arquivo, PATHINFO_EXTENSION)), (bool) $consulta['download']);
    }

    public function pixOs(): Resposta
    {
        $os = $this->osDoCliente();

        return $this->pix($this->ctx->modelo('Os_model'), (int) $os->idOs, (float) $this->ctx->modelo('Os_model')->totais($os)['total']);
    }

    public function compras(): Resposta
    {
        $id = $this->ctx->titular()->id;

        return $this->listarPelaConsulta(fn () => Venda::consultaResumo($this->db)->where('vendas.clientes_id', $id), 'vendas.idVendas', [Venda::class, 'resumoDe']);
    }

    public function verCompra(): Resposta
    {
        $venda = $this->compraDoCliente();
        $modelo = $this->ctx->modelo('Vendas_model');

        return Resposta::dados(Venda::de($venda, [
            'totais' => $modelo->totais($venda),
            'editavel' => false,
            'produtos' => $modelo->getProdutos((int) $venda->idVendas),
            'cobranca_id' => null,
        ]));
    }

    public function pixCompra(): Resposta
    {
        $venda = $this->compraDoCliente();

        return $this->pix($this->ctx->modelo('Vendas_model'), (int) $venda->idVendas, (float) $this->ctx->modelo('Vendas_model')->totais($venda)['total']);
    }

    public function cobrancas(): Resposta
    {
        $id = $this->ctx->titular()->id;
        $serializador = new Cobrancas($this->ctx);

        return $this->listarPelaConsulta(
            fn () => $this->db->select('cobrancas.*, clientes.nomeCliente')->from('cobrancas')->join('clientes', 'clientes.idClientes = cobrancas.clientes_id', 'left')->where('cobrancas.clientes_id', $id),
            'cobrancas.idCobranca',
            [$serializador, 'de'],
        );
    }

    private function tokens(object $cliente, string $sessao, string $refresh, int $refreshExpira): array
    {
        $acesso = $this->ctx->tokens->emitirAcesso(Rota::CLIENTE, (int) $cliente->idClientes, $sessao);

        return [
            'token_type' => 'Bearer',
            'access_token' => $acesso['token'],
            'expires_in' => $this->ctx->tokens->duracao(),
            'refresh_token' => $refresh,
            'refresh_expires_in' => max(0, $refreshExpira - time()),
            'sessao' => $sessao,
            'cliente' => Cliente::de($cliente),
        ];
    }

    private function limite(): Limite_login
    {
        return $this->ctx->biblioteca('Limite_login');
    }

    private function hashDoCaptcha(string $resposta): string
    {
        return hash_hmac('sha256', strtolower(trim($resposta)), $this->ctx->configuracao->chave . '|captcha');
    }

    private function cliente(): object
    {
        return $this->encontrar('clientes', 'idClientes', $this->ctx->titular()->id, 'Cliente não encontrado.');
    }

    private function osDoCliente(?int $id = null): object
    {
        $os = $this->ctx->modelo('Os_model')->getById($id ?? $this->ctx->id());
        if (! $os || (int) $os->clientes_id !== $this->ctx->titular()->id) {
            throw HttpErro::naoEncontrado('OS não encontrada.');
        }

        return $os;
    }

    private function compraDoCliente(): object
    {
        $venda = $this->ctx->modelo('Vendas_model')->getById($this->ctx->id());
        if (! $venda || (int) $venda->clientes_id !== $this->ctx->titular()->id) {
            throw HttpErro::naoEncontrado('Compra não encontrada.');
        }

        return $venda;
    }

    private function detalheOs(object $os): array
    {
        $id = (int) $os->idOs;
        $modelo = $this->ctx->modelo('Os_model');

        return OrdemDeServico::de($os, [
            'totais' => $modelo->totais($os),
            'editavel' => false,
            'produtos' => $modelo->getProdutos($id),
            'servicos' => $modelo->getServicos($id),
            'anexos' => $modelo->getAnexos($id),
            'anotacoes' => [],
            'historico' => $modelo->getHistorico($id),
            'cobranca_id' => null,
        ], $this->ctx->url('cliente/os/' . $id));
    }

    private function pix(object $modelo, int $id, float $total): Resposta
    {
        $chave = (string) $this->ctx->configuracao('pix_key', '');
        $emitente = $this->ctx->modelo('Mapos_model')->getEmitente();
        $payload = $chave === '' || ! $emitente ? null : $modelo->getPixPayload($id, $chave, $emitente);
        if ($payload === null) {
            throw HttpErro::naoEncontrado('PIX indisponível para este documento.');
        }

        return Resposta::dados([
            'valor' => number_format($total, 2, '.', ''),
            'payload' => $payload,
            'qr_code' => (string) $modelo->getQrCode($id, $chave, $emitente),
            'chave' => Ordens::formatarChavePix($chave),
        ]);
    }

    private function conferirUnicos(array $dados, ?int $id): void
    {
        $erros = [];
        if (($dados['email'] ?? '') !== '' && $this->existe('clientes', 'email', $dados['email'], $id, 'idClientes')) {
            $erros['email'] = $id === null ? 'Este e-mail já tem cadastro. Entre com ele ou recupere a senha.' : 'Este e-mail não pode ser usado. Informe outro.';
        }
        if (($dados['documento'] ?? '') !== '' && $this->existe('clientes', 'documento', $dados['documento'], $id, 'idClientes')) {
            $erros['documento'] = $id === null ? 'Este CPF ou CNPJ já tem cadastro. Entre com o e-mail dele ou recupere a senha.' : 'Este CPF ou CNPJ não pode ser usado.';
        }
        if ($erros !== []) {
            throw HttpErro::validacao($erros);
        }
    }

    /** @return array<string, string> */
    private function paraFormulario(array $dados): array
    {
        return array_map(static fn ($v) => (string) $v, [
            'nomeCliente' => $dados['nome'],
            'documento' => $dados['documento'] ?? '',
            'email' => $dados['email'],
            'telefone' => $dados['telefone'] ?? '',
            'celular' => $dados['celular'] ?? '',
            'contato' => $dados['contato'] ?? '',
            'senha' => $dados['senha'] ?? '',
        ] + Endereco::paraBanco(is_array($dados['endereco'] ?? null) ? $dados['endereco'] : [], true));
    }

    private function enviarBoasVindas(object $cliente): void
    {
        $emitente = $this->ctx->modelo('Mapos_model')->getEmitente();
        if (! $emitente) {
            return;
        }
        $this->enfileirar((string) $cliente->email, 'Bem-vindo!', $this->ctx->ci->load->view('os/emails/clientenovo', ['emitente' => $emitente, 'cliente' => $cliente], true), $emitente);
        foreach ($this->db->where('situacao', 1)->get('usuarios')->result() as $usuario) {
            $html = $this->ctx->ci->load->view('os/emails/clientenovonotifica', ['emitente' => $emitente, 'cliente' => $cliente, 'usuario' => $usuario], true);
            $this->enfileirar((string) $usuario->email, 'Novo Cliente Cadastrado no Sistema', $html, $emitente);
        }
    }

    private function enfileirar(string $para, string $assunto, string $html, object $emitente, bool $comNome = true): void
    {
        if ($para === '') {
            return;
        }
        $this->inserirLinha('email_queue', [
            'to' => $para,
            'message' => $html,
            'status' => 'pending',
            'date' => date('Y-m-d H:i:s'),
            'headers' => json_encode(['From' => $comNome ? '"' . $emitente->nome . '" <' . $emitente->email . '>' : (string) $emitente->email, 'Subject' => $assunto, 'Return-Path' => '']),
        ]);
    }
}
