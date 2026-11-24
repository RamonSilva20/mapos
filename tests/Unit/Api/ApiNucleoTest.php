<?php

require_once dirname(__DIR__, 2) . '/Support/ApiTabelas.php';
require_once APPPATH . 'libraries/Limite_login.php';

use Libraries\Api\Api;
use Libraries\Api\Autenticacao\Sessoes;
use Libraries\Api\Autenticacao\Tokens;
use Libraries\Api\Configuracao;
use Libraries\Api\Http\Requisicao;
use Libraries\Api\Http\Resposta;
use Libraries\Api\Modulo;
use Libraries\Api\Modulos\Autenticacao;
use Libraries\Api\Roteamento\Rota;
use Libraries\Api\Roteamento\Roteador;
use Libraries\Api\Schema\Schema;

final class ModuloDoNucleoDeTeste extends Modulo
{
    public static function rotas(): array
    {
        return [
            Rota::get('/aberta', [self::class, 'ok'])->publica(),
            Rota::get('/clientes', [self::class, 'ok'])->permissao('vCliente'),
            Rota::get('/relatorio', [self::class, 'ok'])->todasAsPermissoes('vCliente', 'rCliente'),
            Rota::get('/so-cliente', [self::class, 'ok'])->doCliente(),
            Rota::get('/conta', [self::class, 'ok'])->liberadaCom2faPendente(),
            Rota::get('/manutencao', [self::class, 'ok'])->publica()->liberadaComBancoDesatualizado(),
            Rota::post('/eco', [self::class, 'eco'])->publica()->corpo(Schema::objeto(['nome' => Schema::texto()->obrigatorio()->max(10)])),
            Rota::get('/quebra', [self::class, 'quebra'])->publica(),
            Rota::post('/login-de-teste', [self::class, 'ok'])->publica()->limite('login'),
        ];
    }

    public function ok(): Resposta
    {
        return Resposta::dados(['titular' => $this->ctx->temTitular() ? $this->ctx->titular()->id : null]);
    }

    public function eco(): Resposta
    {
        return Resposta::dados($this->ctx->corpo());
    }

    public function quebra(): Resposta
    {
        echo 'saída perdida';

        throw new RuntimeException('detalhe interno do banco');
    }
}

final class ApiNucleoTest extends MaposTestCase
{
    private const CHAVE = 'chave-de-teste-com-mais-de-32-caracteres!!';

    private object $ci;

    private bool $bancoPendente = false;

    protected function setUp(): void
    {
        parent::setUp();
        ApiTabelas::criar($this->db);
        $this->db->query('CREATE TABLE login_attempts (id INTEGER PRIMARY KEY AUTOINCREMENT, escopo VARCHAR(20) NOT NULL, tipo VARCHAR(10) NOT NULL, chave CHAR(64) NOT NULL,
            falhas INTEGER NOT NULL DEFAULT 0, bloqueado_ate DATETIME NULL, ultima_falha DATETIME NOT NULL)');
        $this->db->query('CREATE UNIQUE INDEX login_attempts_chave ON login_attempts (escopo, tipo, chave)');
        $this->db->query('CREATE TABLE usuarios_codigos_recuperacao (id INTEGER PRIMARY KEY AUTOINCREMENT, usuario_id INTEGER, codigo_hash TEXT, criado_em TEXT, usado_em TEXT)');

        $this->ci = (object) ['db' => $this->db];
        $this->ci->limite_login = new Limite_login(['db' => $this->db, 'segredo' => 'segredo', 'auditar' => static function (): void {
        }]);
    }

    private function configuracao(array $origens = [], int $limite = 300, string $chave = self::CHAVE): Configuracao
    {
        return new Configuracao($chave, 'http://mapos.test', 900, 3600, 86400, $origens, $limite);
    }

    private function api(?Configuracao $configuracao = null, ?Roteador $roteador = null): Api
    {
        return new Api(
            $this->ci,
            $roteador ?? new Roteador([...ModuloDoNucleoDeTeste::rotas(), ...Autenticacao::rotas()]),
            $configuracao ?? $this->configuracao(),
            fn (): bool => $this->bancoPendente,
        );
    }

    private function chamar(string $metodo, string $caminho, array $cabecalhos = [], string $corpo = '', ?Api $api = null): Resposta
    {
        $cabecalhos = array_change_key_case($cabecalhos);
        if ($corpo !== '' && ! isset($cabecalhos['content-type'])) {
            $cabecalhos['content-type'] = 'application/json';
        }

        return ($api ?? $this->api())->atender(new Requisicao($metodo, $caminho, [], $cabecalhos, $corpo, [], [], '10.0.0.1'));
    }

    private function tokenDe(string $tipo, int $id): string
    {
        $sessao = (new Sessoes($this->db, 3600, 86400))->criar($tipo, $id, null, null);

        return (new Tokens(self::CHAVE, 'http://mapos.test', 900))->emitirAcesso($tipo, $id, $sessao['sessao'])['token'];
    }

    private function comToken(string $token): array
    {
        return ['Authorization' => 'Bearer ' . $token];
    }

    public function testSemTokenResponde401ComWwwAuthenticateEFormatoDeProblema(): void
    {
        $resposta = $this->chamar('GET', '/clientes');

        $this->assertSame(401, $resposta->status);
        $this->assertSame('nao_autenticado', $resposta->corpo['codigo']);
        $this->assertStringStartsWith('Bearer', $resposta->cabecalhos['WWW-Authenticate']);
        $this->assertStringStartsWith('application/problem+json', $resposta->cabecalhos['Content-Type']);
        $this->assertSame('no-store', $resposta->cabecalhos['Cache-Control']);
    }

    public function testComPermissaoPassaESemPermissaoRecebe403(): void
    {
        $vendedor = ApiTabelas::usuario($this->db, 'v@x.com', ApiTabelas::grupo($this->db, 'Vendas', ['vVenda']));
        $atendente = ApiTabelas::usuario($this->db, 'a@x.com', ApiTabelas::grupo($this->db, 'Atendimento', ['vCliente']));

        $negado = $this->chamar('GET', '/clientes', $this->comToken($this->tokenDe('usuario', $vendedor)));
        $this->assertSame(403, $negado->status);
        $this->assertSame('sem_permissao', $negado->corpo['codigo']);

        $permitido = $this->chamar('GET', '/clientes', $this->comToken($this->tokenDe('usuario', $atendente)));
        $this->assertSame(200, $permitido->status);
        $this->assertSame($atendente, $permitido->corpo['data']['titular']);

        $this->assertSame(403, $this->chamar('GET', '/relatorio', $this->comToken($this->tokenDe('usuario', $atendente)))->status, 'A rota exige as duas permissões.');
    }

    public function testPermissaoTrocadaNoBancoValeNaChamadaSeguinte(): void
    {
        $grupo = ApiTabelas::grupo($this->db, 'Atendimento', ['vCliente']);
        $token = $this->tokenDe('usuario', ApiTabelas::usuario($this->db, 'a@x.com', $grupo));
        $this->assertSame(200, $this->chamar('GET', '/clientes', $this->comToken($token))->status);

        $this->db->where('idPermissao', $grupo)->update('permissoes', ['permissoes' => json_encode(['vVenda' => '1'])]);

        $this->assertSame(403, $this->chamar('GET', '/clientes', $this->comToken($token))->status);
    }

    public function testContaDesativadaExpiradaOuGrupoDesativadoPerdemOAcessoNaHora(): void
    {
        $grupo = ApiTabelas::grupo($this->db, 'Atendimento', ['vCliente']);
        $id = ApiTabelas::usuario($this->db, 'a@x.com', $grupo);
        $token = $this->tokenDe('usuario', $id);

        $this->db->where('idUsuarios', $id)->update('usuarios', ['dataExpiracao' => '2000-01-01']);
        $this->assertSame('conta_expirada', $this->chamar('GET', '/clientes', $this->comToken($token))->corpo['codigo']);

        $this->db->where('idUsuarios', $id)->update('usuarios', ['dataExpiracao' => null]);
        $this->db->where('idPermissao', $grupo)->update('permissoes', ['situacao' => 0]);
        $this->assertSame('grupo_desativado', $this->chamar('GET', '/clientes', $this->comToken($token))->corpo['codigo']);

        $this->db->where('idPermissao', $grupo)->update('permissoes', ['situacao' => 1]);
        $this->db->where('idUsuarios', $id)->update('usuarios', ['situacao' => 0]);
        $resposta = $this->chamar('GET', '/clientes', $this->comToken($token));
        $this->assertSame(401, $resposta->status);
        $this->assertSame('conta_desativada', $resposta->corpo['codigo']);
    }

    public function testSessaoEncerradaDerrubaOAccessTokenAntesDeExpirar(): void
    {
        $id = ApiTabelas::usuario($this->db, 'a@x.com', ApiTabelas::grupo($this->db, 'G', ['vCliente']));
        $token = $this->tokenDe('usuario', $id);
        $this->assertSame(204, $this->chamar('POST', '/auth/logout', $this->comToken($token))->status);

        $resposta = $this->chamar('GET', '/clientes', $this->comToken($token));
        $this->assertSame(401, $resposta->status);
        $this->assertSame('sessao_encerrada', $resposta->corpo['codigo']);
    }

    public function testTokenDeClienteNaoAbreRotaDeUsuarioENemOContrario(): void
    {
        $grupo = ApiTabelas::grupo($this->db, 'Admin', ['vCliente']);
        $usuario = ApiTabelas::usuario($this->db, 'a@x.com', $grupo);
        $this->db->insert('clientes', ['idClientes' => $usuario, 'nomeCliente' => 'Cliente com o mesmo id', 'email' => 'c@x.com']);

        $this->assertSame(401, $this->chamar('GET', '/clientes', $this->comToken($this->tokenDe('cliente', $usuario)))->status);
        $this->assertSame(401, $this->chamar('GET', '/so-cliente', $this->comToken($this->tokenDe('usuario', $usuario)))->status);
        $this->assertSame(200, $this->chamar('GET', '/so-cliente', $this->comToken($this->tokenDe('cliente', $usuario)))->status);
    }

    public function testDoisFatoresExigidoPeloGrupoLiberaSoAsRotasDeConta(): void
    {
        $id = ApiTabelas::usuario($this->db, 'a@x.com', ApiTabelas::grupo($this->db, 'Seguro', ['vCliente'], 1, 1));
        $token = $this->tokenDe('usuario', $id);

        $bloqueada = $this->chamar('GET', '/clientes', $this->comToken($token));
        $this->assertSame(403, $bloqueada->status);
        $this->assertSame('dois_fatores_obrigatorio', $bloqueada->corpo['codigo']);
        $this->assertSame(200, $this->chamar('GET', '/conta', $this->comToken($token))->status);

        $this->db->where('idUsuarios', $id)->update('usuarios', ['totp_ativado_em' => '2026-01-01 00:00:00']);
        $this->assertSame(200, $this->chamar('GET', '/clientes', $this->comToken($token))->status);
    }

    public function testCorpoInvalidoJsonQuebradoETipoErrado(): void
    {
        $invalido = $this->chamar('POST', '/eco', [], '{"nome": "nome comprido demais"}');
        $this->assertSame(422, $invalido->status);
        $this->assertArrayHasKey('nome', $invalido->corpo['erros']);

        $this->assertSame('json_invalido', $this->chamar('POST', '/eco', [], '{"nome":')->corpo['codigo']);
        $this->assertSame(415, $this->chamar('POST', '/eco', ['Content-Type' => 'text/plain'], 'nome=x')->status);

        $ok = $this->chamar('POST', '/eco', [], '{"nome": "Ana", "admin": true}');
        $this->assertSame(['nome' => 'Ana'], $ok->corpo['data'], 'Campo fora do schema não chega ao handler.');
    }

    public function testRotaInexistenteMetodoErradoEErroInternoSemVazarDetalhe(): void
    {
        $this->assertSame(404, $this->chamar('GET', '/nao-existe')->status);

        $metodo = $this->chamar('DELETE', '/aberta');
        $this->assertSame(405, $metodo->status);
        $this->assertSame('GET', $metodo->cabecalhos['Allow']);

        $quebra = $this->chamar('GET', '/quebra');
        $this->assertSame(500, $quebra->status);
        $this->assertStringNotContainsString('detalhe interno', json_encode($quebra->corpo));
    }

    public function testCorsSoParaOrigemConfiguradaEPreflight(): void
    {
        $api = $this->api($this->configuracao(['https://app.test']));

        $permitida = $this->chamar('GET', '/aberta', ['Origin' => 'https://app.test'], '', $api);
        $this->assertSame('https://app.test', $permitida->cabecalhos['Access-Control-Allow-Origin']);
        $this->assertStringContainsString('Location', $permitida->cabecalhos['Access-Control-Expose-Headers']);

        $outra = $this->chamar('GET', '/aberta', ['Origin' => 'https://mal.test'], '', $api);
        $this->assertArrayNotHasKey('Access-Control-Allow-Origin', $outra->cabecalhos);

        $preflight = $this->chamar('OPTIONS', '/clientes', ['Origin' => 'https://app.test'], '', $api);
        $this->assertSame(204, $preflight->status);
        $this->assertStringContainsString('GET', $preflight->cabecalhos['Access-Control-Allow-Methods']);
        $this->assertStringContainsString('Authorization', $preflight->cabecalhos['Access-Control-Allow-Headers']);

        $semCors = $this->chamar('GET', '/aberta', ['Origin' => 'https://app.test']);
        $this->assertArrayNotHasKey('Access-Control-Allow-Origin', $semCors->cabecalhos, 'Sem API_CORS_ORIGINS, sem CORS.');
    }

    public function testLimiteDeRequisicoesResponde429ComRetryAfter(): void
    {
        $api = $this->api($this->configuracao([], 2));

        $primeira = $this->chamar('GET', '/aberta', [], '', $api);
        $this->assertSame('2', $primeira->cabecalhos['RateLimit-Limit']);
        $this->assertSame('1', $primeira->cabecalhos['RateLimit-Remaining']);
        $this->chamar('GET', '/aberta', [], '', $api);

        $terceira = $this->chamar('GET', '/aberta', [], '', $api);
        $this->assertSame(429, $terceira->status);
        $this->assertGreaterThan(0, (int) $terceira->cabecalhos['Retry-After']);
    }

    public function testRotasDeLoginTemLimiteProprioMaisBaixo(): void
    {
        $api = $this->api($this->configuracao([], 1000));
        for ($i = 0; $i < Api::LIMITE_AUTENTICACAO; $i++) {
            $this->chamar('POST', '/login-de-teste', [], '', $api);
        }

        $this->assertSame(429, $this->chamar('POST', '/login-de-teste', [], '', $api)->status);
        $this->assertSame(200, $this->chamar('GET', '/aberta', [], '', $api)->status, 'O limite do login não afeta as outras rotas.');
    }

    public function testChaveInseguraOuBancoDesatualizadoDeixamAApiForaDoAr(): void
    {
        foreach (['', 'enter_jwt_key', 'curta'] as $chave) {
            $resposta = $this->chamar('GET', '/aberta', [], '', $this->api($this->configuracao([], 300, $chave)));
            $this->assertSame(503, $resposta->status, "chave: {$chave}");
            $this->assertSame('chave_insegura', $resposta->corpo['codigo']);
        }

        $this->bancoPendente = true;
        $this->assertSame('banco_desatualizado', $this->chamar('GET', '/aberta')->corpo['codigo']);
        $this->assertSame(200, $this->chamar('GET', '/manutencao')->status);
    }

    public function testLoginComSenhaErradaEEmailInexistenteTemAMesmaRespostaEBloqueiaDepoisDoLimite(): void
    {
        ApiTabelas::usuario($this->db, 'ana@x.com', ApiTabelas::grupo($this->db, 'G', ['vCliente']));

        $errada = $this->chamar('POST', '/auth/login', [], '{"email":"ana@x.com","senha":"errada"}');
        $inexistente = $this->chamar('POST', '/auth/login', [], '{"email":"ninguem@x.com","senha":"errada"}');
        $this->assertSame(401, $errada->status);
        $this->assertSame($errada->corpo, $inexistente->corpo);

        for ($i = 0; $i < Limite_login::LIMITE_EMAIL; $i++) {
            $this->chamar('POST', '/auth/login', [], '{"email":"ana@x.com","senha":"errada"}');
        }
        $bloqueado = $this->chamar('POST', '/auth/login', [], '{"email":"ana@x.com","senha":"senha-certa-123"}');
        $this->assertSame(429, $bloqueado->status, 'Bloqueado, nem a senha certa entra.');
        $this->assertArrayHasKey('Retry-After', $bloqueado->cabecalhos);
    }

    public function testLoginRenovacaoELogoutDePontaAPonta(): void
    {
        ApiTabelas::usuario($this->db, 'ana@x.com', ApiTabelas::grupo($this->db, 'G', ['vCliente']));

        $login = $this->chamar('POST', '/auth/login', [], '{"email":"ana@x.com","senha":"senha-certa-123","dispositivo":"Teste"}');
        $this->assertSame(200, $login->status);
        $dados = $login->corpo['data'];
        $this->assertFalse($dados['dois_fatores']);
        $this->assertSame('Bearer', $dados['token_type']);
        $this->assertArrayNotHasKey('senha', $dados['usuario']);
        $this->assertSame(200, $this->chamar('GET', '/clientes', $this->comToken($dados['access_token']))->status);

        $renovado = $this->chamar('POST', '/auth/renovar', [], json_encode(['refresh_token' => $dados['refresh_token']]));
        $this->assertSame(200, $renovado->status);
        $this->assertNotSame($dados['refresh_token'], $renovado->corpo['data']['refresh_token']);

        $this->assertSame(204, $this->chamar('POST', '/auth/logout', $this->comToken($renovado->corpo['data']['access_token']))->status);
        $this->assertSame(401, $this->chamar('POST', '/auth/renovar', [], json_encode(['refresh_token' => $renovado->corpo['data']['refresh_token']]))->status);
    }

    public function testLoginDeQuemTemDoisFatoresDevolveDesafioENaoTokens(): void
    {
        ApiTabelas::usuario($this->db, 'ana@x.com', ApiTabelas::grupo($this->db, 'G', ['vCliente']), ['totp_ativado_em' => '2026-01-01 00:00:00']);

        $login = $this->chamar('POST', '/auth/login', [], '{"email":"ana@x.com","senha":"senha-certa-123"}');

        $this->assertSame(200, $login->status);
        $this->assertTrue($login->corpo['data']['dois_fatores']);
        $this->assertArrayHasKey('desafio', $login->corpo['data']);
        $this->assertArrayNotHasKey('access_token', $login->corpo['data']);
        $this->assertSame(0, (int) $this->db->count_all_results('api_sessoes'), 'Nenhuma sessão nasce antes do segundo fator.');
    }

    public function testLoginDeContaExpiradaOuDeGrupoDesativadoEhRecusadoDepoisDaSenha(): void
    {
        $grupo = ApiTabelas::grupo($this->db, 'G', ['vCliente']);
        ApiTabelas::usuario($this->db, 'ana@x.com', $grupo, ['dataExpiracao' => '2000-01-01']);

        $this->assertSame('conta_expirada', $this->chamar('POST', '/auth/login', [], '{"email":"ana@x.com","senha":"senha-certa-123"}')->corpo['codigo']);
        $this->assertSame('credenciais_invalidas', $this->chamar('POST', '/auth/login', [], '{"email":"ana@x.com","senha":"errada"}')->corpo['codigo'], 'Com a senha errada, não revela a expiração.');
    }

    public function testSessoesListaSoAsDoTitularEMarcaAAtual(): void
    {
        $grupo = ApiTabelas::grupo($this->db, 'G', ['vCliente']);
        $ana = ApiTabelas::usuario($this->db, 'ana@x.com', $grupo);
        $bia = ApiTabelas::usuario($this->db, 'bia@x.com', $grupo);
        $token = $this->tokenDe('usuario', $ana);
        $this->tokenDe('usuario', $ana);
        $daBia = (new Sessoes($this->db, 3600, 86400))->criar('usuario', $bia, null, null);

        $lista = $this->chamar('GET', '/auth/sessoes', $this->comToken($token))->corpo['data'];
        $this->assertCount(2, $lista);
        $this->assertCount(1, array_filter($lista, static fn ($s) => $s['atual']));

        $this->assertSame(404, $this->chamar('DELETE', '/auth/sessoes/' . $daBia['sessao'], $this->comToken($token))->status, 'Sessão de outra conta não é encerrada.');
        $this->assertSame(1, $this->chamar('DELETE', '/auth/sessoes', $this->comToken($token))->corpo['data']['encerradas']);
    }
}
