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
            'db' => $this->db,
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

    public function testTokenVencidoNaoTemJanelaDeRenovacao(): void
    {
        $autorizacao = $this->autorizacao();
        $token = $autorizacao->generateToken(['uid' => 7]);

        JWT::$timestamp = time() + 3600 + 1;
        $this->assertFalse($autorizacao->validateToken($token)['status'], 'Vencido não vale mais, nem para renovar.');
        $this->assertSame(0, JWT::$leeway);
    }

    public function testTokenComRefreshDuraNoMaximoUmaHora(): void
    {
        $autorizacao = $this->autorizacao(2592000);

        $this->assertSame(3600, $autorizacao->expiresIn(Authorization_Token::TETO_COM_REFRESH));
        $this->assertSame(2592000, $autorizacao->expiresIn(), 'Sem refresh (área do cliente) vale a configurada.');
        $this->assertSame(60, $this->autorizacao(60)->expiresIn(Authorization_Token::TETO_COM_REFRESH), 'O teto não alonga.');

        $claims = JWT::decode($autorizacao->generateToken(['uid' => 7], Authorization_Token::TETO_COM_REFRESH), new Key(self::CHAVE, 'HS256'));
        $this->assertSame(3600, $claims->exp - $claims->iat);
    }

    public function testVersaoDoTokenDerrubaOsAccessTokensAoTrocarSenhaOuDesativar(): void
    {
        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY, token_versao INTEGER NOT NULL DEFAULT 0)');
        $this->db->query('CREATE TABLE clientes (idClientes INTEGER PRIMARY KEY, token_versao INTEGER NOT NULL DEFAULT 0)');
        $this->db->insert('usuarios', ['idUsuarios' => 7]);
        $this->db->insert('usuarios', ['idUsuarios' => 8]);
        $this->db->insert('clientes', ['idClientes' => 7]);

        $autorizacao = $this->autorizacao();
        $versaoNoLogin = $autorizacao->versaoAtual('usuarios', 'idUsuarios', 7);
        $token = JWT::decode($autorizacao->generateToken(['uid' => 7, 'tv' => $versaoNoLogin]), new Key(self::CHAVE, 'HS256'));
        $this->assertTrue(Authorization_Token::versaoVigente($token, $autorizacao->versaoAtual('usuarios', 'idUsuarios', 7)));

        $autorizacao->incrementarVersao('usuarios', 'idUsuarios', 7);

        $this->assertFalse(Authorization_Token::versaoVigente($token, $autorizacao->versaoAtual('usuarios', 'idUsuarios', 7)), 'Token emitido antes da troca de senha.');
        $this->assertSame(0, $autorizacao->versaoAtual('usuarios', 'idUsuarios', 8), 'Os outros usuários não são afetados.');
        $this->assertSame(0, $autorizacao->versaoAtual('clientes', 'idClientes', 7), 'Nem a mesma id na tabela de clientes.');

        $novo = JWT::decode($autorizacao->generateToken(['uid' => 7, 'tv' => $autorizacao->versaoAtual('usuarios', 'idUsuarios', 7)]), new Key(self::CHAVE, 'HS256'));
        $this->assertTrue(Authorization_Token::versaoVigente($novo, $autorizacao->versaoAtual('usuarios', 'idUsuarios', 7)));

        $autorizacao->incrementarVersao('clientes', 'idClientes', 7);
        $this->assertSame(1, $autorizacao->versaoAtual('clientes', 'idClientes', 7));
    }

    public function testTokenSemVersaoValeComoZeroESemAColunaNadaQuebra(): void
    {
        $autorizacao = $this->autorizacao();
        $legado = (object) ['uid' => 7];

        $this->assertTrue(Authorization_Token::versaoVigente($legado, 0), 'Token anterior à coluna vale até a primeira troca de senha.');
        $this->assertFalse(Authorization_Token::versaoVigente($legado, 1));

        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY)');
        $this->assertSame(0, $autorizacao->versaoAtual('usuarios', 'idUsuarios', 7));
        $autorizacao->incrementarVersao('usuarios', 'idUsuarios', 7);
        $this->assertSame(0, $autorizacao->versaoAtual('tabela_qualquer', 'id', 7));
    }

    public function testSessaoRevogadaInvalidaOAccessTokenDela(): void
    {
        $refresh = $this->refresh();
        $token = $refresh->emitir(7);
        $outra = $refresh->emitir(7);
        $familia = $refresh->familiaDoToken($token);

        $this->assertNotNull($familia);
        $this->assertTrue($refresh->familiaAtiva($familia));

        $refresh->revogar($token);

        $this->assertFalse($refresh->familiaAtiva($familia), 'Logout derruba o access token da sessão.');
        $this->assertTrue($refresh->familiaAtiva($refresh->familiaDoToken($outra)), 'Outras sessões seguem.');
        $this->assertNull($refresh->familiaDoToken('desconhecido'));
        $this->assertSame($refresh->familiaDoToken($outra), $refresh->girar($outra)['familia']);
    }

    public function testTokenAnteriorAoExpSemClaimExpContinuaValendoPeloApiTime(): void
    {
        $autorizacao = $this->autorizacao();
        $legado = JWT::encode(['uid' => 7, 'API_TIME' => time() - 100], self::CHAVE, 'HS256');
        $velho = JWT::encode(['uid' => 7, 'API_TIME' => time() - 7000], self::CHAVE, 'HS256');
        $semTempo = JWT::encode(['uid' => 7], self::CHAVE, 'HS256');

        $this->assertTrue($autorizacao->validateToken($legado)['status']);
        $this->assertFalse($autorizacao->validateToken($velho)['status']);
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

    public function testControllerMantemOContratoDoLoginEReGenTokenSaiu(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/api/v1/UsuariosController.php');

        $this->assertStringContainsString("['access_token' => \$this->accessTokenDoUsuario(", $codigo);
        $this->assertStringContainsString("'permissions' => [\$permissoes]", $codigo);
        $this->assertStringNotContainsString('reGenToken', $codigo, 'Renovar token vencido sem senha nem refresh token acabou.');
        $this->assertStringContainsString("'tv' =>", $codigo);
        $this->assertStringContainsString("\$dados['sid']", $codigo);

        $rotas = (string) file_get_contents(APPPATH . 'config/routes_api.php');
        $this->assertStringContainsString("'api/v1/UsuariosController/refresh'", $rotas);
        $this->assertStringContainsString("'api/v1/UsuariosController/logout'", $rotas);
        $this->assertStringNotContainsString('reGenToken', $rotas);
        $this->assertStringNotContainsString('reGenToken', (string) file_get_contents(APPPATH . 'libraries/REST_Controller.php'));
        $this->assertStringContainsString("'tv' =>", (string) file_get_contents(APPPATH . 'controllers/api/v1/client/ClientLoginController.php'));
    }

    public function testPermissaoVemDoBancoEUsuarioInativoPerdeOAcesso(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'libraries/REST_Controller.php');
        preg_match('/function logged_user\(.*?\n    }\n/s', $codigo, $trecho);

        $this->assertStringContainsString('$this->user->usuario->permissoes_id', $trecho[0]);
        $this->assertStringNotContainsString('$decodedToken->data->permissao', $trecho[0]);
        $this->assertStringContainsString('(int) $this->user->usuario->situacao !== 1', $trecho[0]);
        $this->assertStringContainsString('versaoVigente', $trecho[0]);
        $this->assertStringContainsString('sessaoDoTokenAtiva', $trecho[0]);

        preg_match('/function logged_client\(.*?\n    }\n/s', $codigo, $cliente);
        $this->assertStringContainsString('versaoVigente', $cliente[0]);
    }

    public function testSenhaTrocadaOuUsuarioDesativadoRevogaAsSessoesDaApi(): void
    {
        $this->assertStringContainsString('revogarDoUsuario', (string) file_get_contents(APPPATH . 'models/Usuarios_model.php'));
        $this->assertStringContainsString('revogarDoUsuario', (string) file_get_contents(APPPATH . 'models/Mapos_model.php'));
        foreach (['Usuarios_model', 'Mapos_model', 'Clientes_model', 'Conecte_model'] as $modelo) {
            $this->assertStringContainsString('incrementarVersao', (string) file_get_contents(APPPATH . "models/{$modelo}.php"), $modelo);
        }
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
