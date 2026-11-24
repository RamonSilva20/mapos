<?php

namespace Libraries\Api\Modulos;

use Libraries\Api\Http\HttpErro;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Modulo;
use Libraries\Api\Recursos\Usuario;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Schema\Schema;
use Limite_login;

final class Autenticacao extends Modulo
{
    public static function rotas(): array
    {
        $tag = 'Autenticação';

        return [
            Rota::post('/auth/login', [self::class, 'login'])->publica()->limite('login')->tag($tag)
                ->resumo('Entra com e-mail e senha', "Abre uma sessão e devolve o par de tokens. Quem tem a autenticação em dois fatores ativa recebe um `desafio` em vez dos tokens e conclui em `POST /auth/login/dois-fatores`.\n\nE-mail inexistente e senha errada têm a mesma resposta. Depois de várias falhas o acesso fica bloqueado por um tempo (`429` com `Retry-After`), o mesmo limite do painel.")
                ->corpo(Schema::objeto([
                    'email' => Schema::email()->obrigatorio()->max(80)->exemplo('admin@empresa.com.br'),
                    'senha' => Schema::senha()->obrigatorio()->max(200),
                    'dispositivo' => Schema::texto()->max(100)->descricao('Nome do aparelho ou aplicativo, mostrado na lista de sessões.')->exemplo('App Android - Moto G'),
                ]))
                ->dados(self::schemaLogin(), 'Tokens emitidos, ou o desafio do segundo fator.')
                ->erros(401, 403, 422, 429),

            Rota::post('/auth/login/dois-fatores', [self::class, 'loginDoisFatores'])->publica()->limite('login')->tag($tag)
                ->resumo('Conclui o login com o código do segundo fator', 'Aceita o código de 6 dígitos do aplicativo autenticador ou um código de recuperação (uso único). O desafio vale 5 minutos.')
                ->corpo(Schema::objeto([
                    'desafio' => Schema::texto()->obrigatorio()->max(2000),
                    'codigo' => Schema::texto()->obrigatorio()->max(20)->exemplo('123456'),
                    'dispositivo' => Schema::texto()->max(100),
                ]))
                ->dados(self::schemaTokens(), 'Tokens emitidos.')
                ->erros(401, 422, 429),

            Rota::post('/auth/renovar', [self::class, 'renovar'])->publica()->limite('renovar')->tag($tag)
                ->resumo('Troca o refresh token por um par novo', "O refresh token é de uso único: guarde o novo e descarte o anterior.\n\n- Reapresentar um token já usado, depois de alguns segundos, encerra a sessão inteira (sinal de cópia vazada) e responde `401` com `codigo: refresh_reutilizado`.\n- Duas renovações simultâneas com o mesmo token: uma recebe o par novo e a outra `409` (`renovacao_concorrente`), sem encerrar a sessão.")
                ->corpo(Schema::objeto([
                    'refresh_token' => Schema::texto()->obrigatorio()->max(200),
                ]))
                ->dados(self::schemaTokens(), 'Par novo de tokens.')
                ->erros(401, 409, 422, 429),

            Rota::post('/auth/logout', [self::class, 'logout'])->liberadaCom2faPendente()->tag($tag)
                ->resumo('Encerra a sessão atual', 'O access token e o refresh token desta sessão deixam de valer na hora.')
                ->semConteudo('Sessão encerrada.')
                ->erros(401),

            Rota::get('/auth/sessoes', [self::class, 'sessoes'])->liberadaCom2faPendente()->tag($tag)
                ->resumo('Lista as sessões abertas da conta')
                ->dados(Schema::lista(self::schemaSessao()))
                ->erros(401),

            Rota::delete('/auth/sessoes', [self::class, 'encerrarOutras'])->liberadaCom2faPendente()->tag($tag)
                ->resumo('Encerra todas as outras sessões', 'Mantém só a sessão do token usado na chamada.')
                ->dados(Schema::objeto(['encerradas' => Schema::inteiro()->obrigatorio()]))
                ->erros(401),

            Rota::delete('/auth/sessoes/{sessao:s}', [self::class, 'encerrarSessao'])->liberadaCom2faPendente()->tag($tag)
                ->resumo('Encerra uma sessão da conta')
                ->semConteudo('Sessão encerrada.')
                ->erros(401, 404),
        ];
    }

    public static function schemaTokens(): Schema
    {
        static $schema = null;

        return $schema ??= Schema::objeto([
            'token_type' => Schema::enum(['Bearer'])->obrigatorio(),
            'access_token' => Schema::texto()->obrigatorio()->descricao('JWT para o cabeçalho `Authorization: Bearer`.'),
            'expires_in' => Schema::inteiro()->obrigatorio()->descricao('Segundos até o access token expirar.')->exemplo(900),
            'refresh_token' => Schema::texto()->obrigatorio()->descricao('Token opaco de uso único para `POST /auth/renovar`.'),
            'refresh_expires_in' => Schema::inteiro()->obrigatorio()->exemplo(2592000),
            'sessao' => Schema::texto()->obrigatorio()->descricao('Identificador da sessão (ver `GET /auth/sessoes`).'),
            'dois_fatores_pendente' => Schema::booleano()->obrigatorio()->descricao('O grupo exige 2FA e a conta ainda não ativou: só as rotas de conta e sessão respondem até ativar.'),
            'usuario' => Usuario::schema()->obrigatorio(),
        ])->nome('Tokens');
    }

    private static function schemaLogin(): Schema
    {
        return Schema::objeto([
            'dois_fatores' => Schema::booleano()->obrigatorio()->descricao('true: envie o código em `POST /auth/login/dois-fatores`.'),
            'desafio' => Schema::texto()->descricao('Só quando dois_fatores é true.'),
            'desafio_expires_in' => Schema::inteiro()->exemplo(300),
            'token_type' => Schema::enum(['Bearer']),
            'access_token' => Schema::texto(),
            'expires_in' => Schema::inteiro(),
            'refresh_token' => Schema::texto(),
            'refresh_expires_in' => Schema::inteiro(),
            'sessao' => Schema::texto(),
            'dois_fatores_pendente' => Schema::booleano(),
            'usuario' => Usuario::schema(),
        ])->nome('RespostaLogin');
    }

    public static function schemaSessao(): Schema
    {
        static $schema = null;

        return $schema ??= Schema::objeto([
            'id' => Schema::texto()->obrigatorio(),
            'atual' => Schema::booleano()->obrigatorio(),
            'dispositivo' => Schema::texto()->anulavel()->obrigatorio(),
            'ip' => Schema::texto()->anulavel()->obrigatorio(),
            'user_agent' => Schema::texto()->anulavel()->obrigatorio(),
            'criada_em' => Schema::dataHora()->obrigatorio(),
            'ultimo_uso_em' => Schema::dataHora()->obrigatorio(),
            'expira_em' => Schema::dataHora()->obrigatorio(),
        ])->nome('Sessao');
    }

    public function login(): Resposta
    {
        $dados = $this->ctx->corpo();
        $ip = $this->ctx->requisicao->ip;
        $limite = $this->limiteLogin();

        $restantes = $limite->segundosRestantes('usuario', $dados['email'], $ip);
        if ($restantes > 0) {
            throw HttpErro::muitasRequisicoes($restantes, Limite_login::MENSAGEM);
        }

        $usuario = $this->db->where('email', $dados['email'])->where('situacao', 1)->limit(1)->get('usuarios')->row();
        if (! senhaConfere($dados['senha'], $usuario->senha ?? null) || ! $usuario) {
            $limite->registrarFalha('usuario', $dados['email'], $ip);

            throw HttpErro::naoAutenticado('Os dados de acesso estão incorretos.', 'credenciais_invalidas');
        }

        $this->conferirConta($usuario);

        if (! empty($usuario->totp_ativado_em)) {
            $desafio = $this->ctx->tokens->emitirDesafio2fa((int) $usuario->idUsuarios);

            return Resposta::dados([
                'dois_fatores' => true,
                'desafio' => $desafio['token'],
                'desafio_expires_in' => $desafio['expira_em'] - time(),
            ]);
        }

        $limite->registrarSucesso('usuario', $dados['email']);

        return Resposta::dados(['dois_fatores' => false] + $this->abrirSessao($usuario, $dados['dispositivo'] ?? null));
    }

    public function loginDoisFatores(): Resposta
    {
        $dados = $this->ctx->corpo();
        $ip = $this->ctx->requisicao->ip;
        $id = $this->ctx->tokens->validarDesafio2fa($dados['desafio']);
        $usuario = $this->db->where('idUsuarios', $id)->where('situacao', 1)->limit(1)->get('usuarios')->row();
        if (! $usuario || empty($usuario->totp_ativado_em)) {
            throw HttpErro::naoAutenticado('O desafio expirou ou é inválido. Entre de novo com e-mail e senha.', 'desafio_invalido');
        }

        $limite = $this->limiteLogin();
        $this->conferirBloqueio($limite, (string) $usuario->email, $ip);
        $this->conferirConta($usuario);

        $totp = $this->ctx->biblioteca('Totp');
        if (! $totp->aceitar($this->ctx->modelo('Mapos_model'), $usuario, trim($dados['codigo']))) {
            $limite->registrarFalha('usuario', (string) $usuario->email, $ip);

            throw HttpErro::naoAutenticado('Código incorreto. Confira o aplicativo ou use um código de recuperação.', 'codigo_invalido');
        }

        $limite->registrarSucesso('usuario', (string) $usuario->email);

        return Resposta::dados($this->abrirSessao($usuario, $dados['dispositivo'] ?? null));
    }

    public function renovar(): Resposta
    {
        $dados = $this->ctx->corpo();
        $renovado = $this->ctx->sessoes->renovar(Rota::USUARIO, $dados['refresh_token'], $this->ctx->requisicao->ip, $this->ctx->requisicao->userAgent());

        $usuario = $this->db->where('idUsuarios', $renovado['titular_id'])->where('situacao', 1)->limit(1)->get('usuarios')->row();
        if (! $usuario) {
            $this->ctx->sessoes->encerrar($renovado['sessao'], Rota::USUARIO, $renovado['titular_id'], 'conta_desativada');

            throw HttpErro::naoAutenticado('Sua conta está desativada.', 'conta_desativada');
        }
        $this->conferirConta($usuario);

        return Resposta::dados($this->tokens($usuario, $renovado['sessao'], $renovado['refresh_token'], $renovado['refresh_expira_em']));
    }

    public function logout(): Resposta
    {
        $titular = $this->ctx->titular();
        $this->ctx->sessoes->encerrar($titular->sessao, $titular->tipo, $titular->id);

        return Resposta::semConteudo();
    }

    public function sessoes(): Resposta
    {
        $titular = $this->ctx->titular();

        return Resposta::dados(self::listarSessoes($this->ctx->sessoes->listar($titular->tipo, $titular->id), $titular->sessao));
    }

    public function encerrarOutras(): Resposta
    {
        $titular = $this->ctx->titular();
        $total = $this->ctx->sessoes->encerrarDoTitular($titular->tipo, $titular->id, 'encerrada_pelo_titular', $titular->sessao);

        return Resposta::dados(['encerradas' => $total]);
    }

    public function encerrarSessao(): Resposta
    {
        $titular = $this->ctx->titular();
        if (! $this->ctx->sessoes->encerrar((string) $this->ctx->parametro('sessao'), $titular->tipo, $titular->id, 'encerrada_pelo_titular')) {
            throw HttpErro::naoEncontrado('Sessão não encontrada.');
        }

        return Resposta::semConteudo();
    }

    /** @param  list<object>  $linhas */
    public static function listarSessoes(array $linhas, string $atual): array
    {
        return array_map(static fn (object $s): array => [
            'id' => (string) $s->identificador,
            'atual' => hash_equals((string) $s->identificador, $atual),
            'dispositivo' => $s->dispositivo !== null && $s->dispositivo !== '' ? (string) $s->dispositivo : null,
            'ip' => $s->ip !== null ? (string) $s->ip : null,
            'user_agent' => $s->user_agent !== null ? (string) $s->user_agent : null,
            'criada_em' => date(DATE_ATOM, strtotime((string) $s->criada_em)),
            'ultimo_uso_em' => date(DATE_ATOM, strtotime((string) $s->ultimo_uso_em)),
            'expira_em' => date(DATE_ATOM, strtotime((string) $s->expira_em)),
        ], $linhas);
    }

    private function abrirSessao(object $usuario, ?string $dispositivo): array
    {
        $sessao = $this->ctx->sessoes->criar(Rota::USUARIO, (int) $usuario->idUsuarios, $this->ctx->requisicao->ip, $this->ctx->requisicao->userAgent(), $dispositivo);

        $this->db->insert('logs', [
            'usuario' => mb_substr((string) $usuario->nome, 0, 80),
            'ip' => $this->ctx->requisicao->ip,
            'tarefa' => 'Efetuou login pela API',
            'data' => date('Y-m-d'),
            'hora' => date('H:i:s'),
        ]);

        return $this->tokens($usuario, $sessao['sessao'], $sessao['refresh_token'], $sessao['refresh_expira_em']);
    }

    private function tokens(object $usuario, string $sessao, string $refresh, int $refreshExpira): array
    {
        $acesso = $this->ctx->tokens->emitirAcesso(Rota::USUARIO, (int) $usuario->idUsuarios, $sessao);
        $grupo = $this->db->select('nome, exigir_2fa')->where('idPermissao', (int) $usuario->permissoes_id)->limit(1)->get('permissoes')->row();

        return [
            'token_type' => 'Bearer',
            'access_token' => $acesso['token'],
            'expires_in' => $this->ctx->tokens->duracao(),
            'refresh_token' => $refresh,
            'refresh_expires_in' => max(0, $refreshExpira - time()),
            'sessao' => $sessao,
            'dois_fatores_pendente' => empty($usuario->totp_ativado_em) && (int) ($grupo->exigir_2fa ?? 0) === 1,
            'usuario' => Usuario::de($usuario, $grupo->nome ?? null),
        ];
    }

    private function conferirConta(object $usuario): void
    {
        if (contaExpirada($usuario->dataExpiracao ?? null)) {
            throw HttpErro::proibido('A conta do usuário está expirada. Fale com o administrador do sistema.', 'conta_expirada');
        }
        if ($this->db->where('idPermissao', (int) $usuario->permissoes_id)->where('situacao', 1)->count_all_results('permissoes') === 0) {
            throw HttpErro::proibido('O grupo de permissão desta conta está desativado. Fale com o administrador do sistema.', 'grupo_desativado');
        }
    }

    private function limiteLogin(): Limite_login
    {
        return $this->ctx->biblioteca('Limite_login');
    }

    private function conferirBloqueio(Limite_login $limite, string $email, string $ip): void
    {
        $restantes = $limite->segundosRestantes('usuario', $email, $ip);
        if ($restantes > 0) {
            throw HttpErro::muitasRequisicoes($restantes, Limite_login::MENSAGEM);
        }
    }
}
