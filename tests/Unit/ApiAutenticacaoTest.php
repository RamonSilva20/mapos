<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once APPPATH . 'libraries/Refresh_token.php';
require_once APPPATH . 'libraries/Authorization_Token.php';

/**
 * Autenticação da API (#2869): access token com `exp`, compatibilidade com os
 * tokens anteriores (só `API_TIME`) e refresh token revogável.
 */
final class ApiAutenticacaoTest extends MaposTestCase
{
    private const CHAVE = 'chave-de-teste-com-mais-de-32-caracteres!!';

    private int $agora;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agora = strtotime('2026-10-14 10:00:00');
        $this->db->query('CREATE TABLE api_refresh_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            familia CHAR(32) NOT NULL,
            token_hash CHAR(64) NOT NULL,
            criado_em DATETIME NOT NULL,
            expira_em DATETIME NOT NULL,
            usado_em DATETIME NULL,
            revogado_em DATETIME NULL,
            ip VARCHAR(45) NULL,
            user_agent VARCHAR(255) NULL
        )');
        $this->db->query('CREATE UNIQUE INDEX api_refresh_tokens_hash ON api_refresh_tokens (token_hash)');
    }

    protected function tearDown(): void
    {
        JWT::$timestamp = null;
        JWT::$leeway = 0;

        parent::tearDown();
    }

    private function autorizacao(int $expiracao = 3600): Authorization_Token
    {
        $autorizacao = $this->makeInstance(Authorization_Token::class);
        $this->setPrivateProperty($autorizacao, 'token_key', self::CHAVE);
        $this->setPrivateProperty($autorizacao, 'token_algorithm', 'HS256');
        $this->setPrivateProperty($autorizacao, 'token_header', 'Authorization');
        $this->setPrivateProperty($autorizacao, 'token_expire_time', $expiracao);
        $this->setPrivateProperty($autorizacao, 'CI', (object) [
            'input' => new class() {
                public function request_headers(): array
                {
                    return ['Authorization' => 'Bearer x'];
                }
            },
        ]);

        return $autorizacao;
    }

    private function refresh(int $duracao = 3600): Refresh_token
    {
        return new Refresh_token(['db' => $this->db, 'relogio' => fn () => $this->agora, 'duracao' => $duracao]);
    }

    public function testTokenNovoTemExpIatEOApiTimeDoLegado(): void
    {
        $token = $this->autorizacao()->generateToken(['uid' => 7, 'type' => 'user']);
        $claims = JWT::decode($token, new Key(self::CHAVE, 'HS256'));

        $this->assertSame(3600, $claims->exp - $claims->iat);
        $this->assertEqualsWithDelta(time(), $claims->iat, 5);
        $this->assertSame($claims->iat, $claims->API_TIME);
        $this->assertSame(7, $claims->uid);
    }

    public function testTokenNovoValidaEExpiraPeloExp(): void
    {
        $autorizacao = $this->autorizacao();
        $token = $autorizacao->generateToken(['uid' => 7]);

        $this->assertTrue($autorizacao->validateToken($token)['status']);

        JWT::$timestamp = time() + 3601;
        $vencido = $autorizacao->validateToken($token);
        $this->assertFalse($vencido['status']);
        $this->assertSame('Token Time Expire.', $vencido['message']);
    }

    public function testRenovacaoAceitaTokenVencidoSoDentroDaJanela(): void
    {
        $autorizacao = $this->autorizacao();
        $token = $autorizacao->generateToken(['uid' => 7]);

        JWT::$timestamp = time() + 3600 + 1800;
        $this->assertTrue($autorizacao->validateToken($token, true)['status'], 'Vencido há 30 min, dentro de 1 hora extra.');
        $this->assertSame(0, JWT::$leeway, 'A tolerância não vaza para as outras chamadas.');

        JWT::$timestamp = time() + 3600 + 3601;
        $this->assertFalse($autorizacao->validateToken($token, true)['status'], 'Passou de 2x o tempo de expiração.');
    }

    public function testTokenAnteriorAoExpSemClaimExpContinuaValendoPeloApiTime(): void
    {
        $autorizacao = $this->autorizacao();
        $legado = JWT::encode(['uid' => 7, 'API_TIME' => time() - 100], self::CHAVE, 'HS256');
        $velho = JWT::encode(['uid' => 7, 'API_TIME' => time() - 7000], self::CHAVE, 'HS256');
        $semTempo = JWT::encode(['uid' => 7], self::CHAVE, 'HS256');

        $this->assertTrue($autorizacao->validateToken($legado)['status']);
        $this->assertFalse($autorizacao->validateToken($velho)['status']);
        $this->assertTrue($autorizacao->validateToken($velho, true)['status'], 'Renovação: até 2x.');
        $this->assertSame('Token Time Not Define!', $autorizacao->validateToken($semTempo)['message']);
    }

    public function testAssinaturaErradaOuAlgoritmoNoneSaoRecusados(): void
    {
        $autorizacao = $this->autorizacao();
        $outra = JWT::encode(['uid' => 7, 'exp' => time() + 100], 'outra-chave-com-mais-de-32-caracteres!!!', 'HS256');
        $this->assertFalse($autorizacao->validateToken($outra)['status']);

        $semAssinatura = rtrim(strtr(base64_encode('{"alg":"none","typ":"JWT"}'), '+/', '-_'), '=') . '.' . rtrim(strtr(base64_encode('{"uid":1,"exp":' . (time() + 100) . '}'), '+/', '-_'), '=') . '.';
        $this->assertFalse($autorizacao->validateToken($semAssinatura)['status']);
        $this->assertFalse($autorizacao->validateToken('lixo')['status']);
    }

    public function testRefreshTokenGuardaSoOHashEGiraACadaUso(): void
    {
        $refresh = $this->refresh();
        $token = $refresh->emitir(7, '10.0.0.1', 'app/2.1');

        $linha = $this->db->get('api_refresh_tokens')->row();
        $this->assertSame(hash('sha256', $token), $linha->token_hash);
        $this->assertNotSame($token, $linha->token_hash);

        $girado = $refresh->girar($token);
        $this->assertTrue($girado['ok']);
        $this->assertSame(7, $girado['usuario_id']);
        $this->assertNotSame($token, $girado['refresh_token']);
        $this->assertSame($linha->familia, $this->db->where('token_hash', hash('sha256', $girado['refresh_token']))->get('api_refresh_tokens')->row()->familia);

        $this->assertTrue($refresh->girar($girado['refresh_token'])['ok'], 'O novo token funciona.');
    }

    public function testTokenJaUsadoDentroDaToleranciaSoRecusa(): void
    {
        $refresh = $this->refresh();
        $token = $refresh->emitir(7);
        $novo = $refresh->girar($token)['refresh_token'];

        $this->agora += Refresh_token::TOLERANCIA_SEGUNDOS;
        $this->assertSame('reutilizado', $refresh->girar($token)['motivo']);
        $this->assertTrue($refresh->girar($novo)['ok'], 'Resposta perdida na rede: a sessão segue.');
    }

    public function testReusoDepoisDaToleranciaRevogaASessaoInteira(): void
    {
        $refresh = $this->refresh();
        $token = $refresh->emitir(7);
        $novo = $refresh->girar($token)['refresh_token'];

        $this->agora += Refresh_token::TOLERANCIA_SEGUNDOS + 1;
        $this->assertSame('reutilizado', $refresh->girar($token)['motivo']);
        $this->assertSame('revogado', $refresh->girar($novo)['motivo'], 'Quem tinha o token novo também sai.');
    }

    public function testRefreshTokenExpiradoInvalidoERevogado(): void
    {
        $refresh = $this->refresh(100);
        $token = $refresh->emitir(7);

        $this->assertSame('invalido', $refresh->girar('qualquer-coisa')['motivo']);
        $this->assertSame('invalido', $refresh->girar('')['motivo']);

        $this->agora += 101;
        $this->assertSame('expirado', $refresh->girar($token)['motivo']);

        $this->agora -= 101;
        $this->assertTrue($refresh->revogar($token));
        $this->assertSame('revogado', $refresh->girar($token)['motivo']);
        $this->assertFalse($refresh->revogar('desconhecido'));
    }

    public function testRevogarDoUsuarioNaoMexeNosOutros(): void
    {
        $refresh = $this->refresh();
        $deAna = $refresh->emitir(7);
        $deBia = $refresh->emitir(8);
        $segundaSessaoDaAna = $refresh->emitir(7);

        $refresh->revogarDoUsuario(7);

        $this->assertSame('revogado', $refresh->girar($deAna)['motivo']);
        $this->assertSame('revogado', $refresh->girar($segundaSessaoDaAna)['motivo']);
        $this->assertTrue($refresh->girar($deBia)['ok']);
    }

    public function testSemATabelaNaoEmiteERecusaTudo(): void
    {
        $this->db->query('DROP TABLE api_refresh_tokens');
        $refresh = $this->refresh();

        $this->assertNull($refresh->emitir(7));
        $this->assertSame('invalido', $refresh->girar('x')['motivo']);
        $this->assertFalse($refresh->revogar('x'));
        $refresh->revogarDoUsuario(7);
    }

    public function testControllerMantemOContratoDoLoginEDoReGenToken(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/api/v1/UsuariosController.php');

        $this->assertStringContainsString("['access_token' => \$this->authorization_token->generateToken(\$token_data)] + \$this->sessaoDoToken", $codigo);
        $this->assertStringContainsString("'permissions' => [\$permissoes]", $codigo);
        preg_match('/function reGenToken_get\(\).*?\n    }\n/s', $codigo, $trecho);
        $this->assertStringContainsString('Deprecation: true', $trecho[0]);
        $this->assertStringContainsString('logged_user(true)', $trecho[0]);
        $this->assertStringContainsString('HTTP_FORBIDDEN', $trecho[0], 'O app só renova em 403.');

        $rotas = (string) file_get_contents(APPPATH . 'config/routes_api.php');
        $this->assertStringContainsString("'api/v1/UsuariosController/refresh'", $rotas);
        $this->assertStringContainsString("'api/v1/UsuariosController/logout'", $rotas);
        $this->assertStringContainsString("'api/v1/UsuariosController/reGenToken'", $rotas);
    }

    public function testPermissaoVemDoBancoEUsuarioInativoPerdeOAcesso(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'libraries/REST_Controller.php');
        preg_match('/function logged_user\(.*?\n    }\n/s', $codigo, $trecho);

        $this->assertStringContainsString('$this->user->usuario->permissoes_id', $trecho[0]);
        $this->assertStringNotContainsString('$this->user->level = $decodedToken->data->permissao;', $trecho[0]);
        $this->assertStringContainsString('(int) $this->user->usuario->situacao !== 1', $trecho[0]);
    }

    public function testSenhaTrocadaOuUsuarioDesativadoRevogaAsSessoesDaApi(): void
    {
        $this->assertStringContainsString('revogarDoUsuario', (string) file_get_contents(APPPATH . 'models/Usuarios_model.php'));
        $this->assertStringContainsString('revogarDoUsuario', (string) file_get_contents(APPPATH . 'models/Mapos_model.php'));
    }

    public function testConfiguracaoDeCorsELimiteDaApi(): void
    {
        $config = [];
        $_ENV['API_CORS_ORIGINS'] = 'https://app.exemplo.com, https://outro.com';
        $_ENV['API_RATE_LIMIT'] = '120';
        include APPPATH . 'config/rest.php';

        $this->assertTrue($config['check_cors']);
        $this->assertFalse($config['allow_any_cors_domain']);
        $this->assertSame(['https://app.exemplo.com', 'https://outro.com'], $config['allowed_cors_origins']);
        $this->assertContains('App-Version', $config['allowed_cors_headers'], 'O app web envia esse header.');
        $this->assertContains('Authorization', $config['allowed_cors_headers']);
        $this->assertSame(120, $config['api_rate_limit']);

        $_ENV['API_CORS_ORIGINS'] = '*';
        include APPPATH . 'config/rest.php';
        $this->assertTrue($config['allow_any_cors_domain']);

        unset($_ENV['API_CORS_ORIGINS'], $_ENV['API_RATE_LIMIT']);
        include APPPATH . 'config/rest.php';
        $this->assertFalse($config['check_cors'], 'Padrão: sem CORS, como na v4.');
        $this->assertSame(300, $config['api_rate_limit']);
    }

    public function testBibliotecaAntigaSaiuEFirebaseVemDoComposer(): void
    {
        $this->assertDirectoryDoesNotExist(APPPATH . 'third_party/php-jwt');
        $this->assertStringContainsString('"firebase/php-jwt"', (string) file_get_contents(MAPOS_ROOT . '/composer.json'));
        $this->assertStringNotContainsString('third_party/php-jwt', (string) file_get_contents(APPPATH . 'libraries/Authorization_Token.php'));
    }
}
