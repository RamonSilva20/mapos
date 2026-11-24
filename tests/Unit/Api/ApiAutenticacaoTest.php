<?php

require_once dirname(__DIR__, 2) . '/Support/ApiTabelas.php';

use Firebase\JWT\JWT;
use Libraries\Api\Autenticacao\Sessoes;
use Libraries\Api\Autenticacao\Tokens;
use Libraries\Api\Http\HttpErro;

final class ApiAutenticacaoTest extends MaposTestCase
{
    private const CHAVE = 'chave-de-teste-com-mais-de-32-caracteres!!';

    private int $agora = 1_800_000_000;

    protected function setUp(): void
    {
        parent::setUp();
        ApiTabelas::criar($this->db);
    }

    private function tokens(int $duracao = 900): Tokens
    {
        return new Tokens(self::CHAVE, 'http://mapos.test', $duracao, fn (): int => $this->agora);
    }

    private function sessoes(int $renovacao = 3600, int $sessao = 86400): Sessoes
    {
        return new Sessoes($this->db, $renovacao, $sessao, fn (): int => $this->agora);
    }

    private function codigoDoErro(callable $acao): string
    {
        try {
            $acao();
        } catch (HttpErro $e) {
            return $e->codigo;
        }

        $this->fail('Era esperado um HttpErro.');
    }

    public function testAccessTokenValeSoParaOTipoEAudienciaCertos(): void
    {
        $token = $this->tokens()->emitirAcesso('usuario', 7, 'sessao1')['token'];

        $claims = $this->tokens()->validarAcesso($token, 'usuario');
        $this->assertSame('7', $claims->sub);
        $this->assertSame('sessao1', $claims->sid);
        $this->assertObjectNotHasProperty('permissoes', $claims, 'Permissões não viajam no token.');

        $this->assertSame('token_invalido', $this->codigoDoErro(fn () => $this->tokens()->validarAcesso($token, 'cliente')));
    }

    public function testTokenExpiradoAssinadoComOutraChaveOuDeOutroEmissorEhRecusado(): void
    {
        $token = $this->tokens(60)->emitirAcesso('usuario', 7, 's')['token'];
        $this->agora += 61 + 5;
        $this->assertSame('token_expirado', $this->codigoDoErro(fn () => $this->tokens()->validarAcesso($token, 'usuario')));

        $forjado = JWT::encode(['iss' => 'http://mapos.test', 'aud' => 'mapos-api', 'sub' => '1', 'typ' => 'usuario', 'sid' => 's', 'exp' => $this->agora + 60], str_repeat('x', 40), 'HS256');
        $this->assertSame('token_invalido', $this->codigoDoErro(fn () => $this->tokens()->validarAcesso($forjado, 'usuario')));

        $outroEmissor = (new Tokens(self::CHAVE, 'http://outro.test', 900, fn (): int => $this->agora))->emitirAcesso('usuario', 1, 's')['token'];
        $this->assertSame('token_invalido', $this->codigoDoErro(fn () => $this->tokens()->validarAcesso($outroEmissor, 'usuario')));

        $semAssinatura = JWT::urlsafeB64Encode('{"alg":"none","typ":"JWT"}') . '.' . JWT::urlsafeB64Encode(json_encode(['iss' => 'http://mapos.test', 'aud' => 'mapos-api', 'sub' => '1', 'typ' => 'usuario', 'sid' => 's', 'exp' => $this->agora + 60])) . '.';
        $this->assertSame('token_invalido', $this->codigoDoErro(fn () => $this->tokens()->validarAcesso($semAssinatura, 'usuario')));
    }

    public function testDesafioDoSegundoFatorNaoServeComoAccessToken(): void
    {
        $desafio = $this->tokens()->emitirDesafio2fa(7)['token'];

        $this->assertSame(7, $this->tokens()->validarDesafio2fa($desafio));
        $this->assertSame('token_invalido', $this->codigoDoErro(fn () => $this->tokens()->validarAcesso($desafio, 'usuario')));

        $acesso = $this->tokens()->emitirAcesso('usuario', 7, 's')['token'];
        $this->assertSame('desafio_invalido', $this->codigoDoErro(fn () => $this->tokens()->validarDesafio2fa($acesso)));
    }

    public function testRefreshTokenGiraEFicaSoComOHashNoBanco(): void
    {
        $sessoes = $this->sessoes();
        $criada = $sessoes->criar('usuario', 7, '10.0.0.1', 'app', 'Celular');

        $linha = $this->db->get(Sessoes::TABELA_TOKENS)->row();
        $this->assertSame(hash('sha256', $criada['refresh_token']), $linha->token_hash);
        $this->assertStringNotContainsString($criada['refresh_token'], json_encode($this->db->get(Sessoes::TABELA_TOKENS)->result()));

        $this->agora += 30;
        $renovado = $sessoes->renovar('usuario', $criada['refresh_token'], '10.0.0.2', 'app');
        $this->assertSame($criada['sessao'], $renovado['sessao']);
        $this->assertNotSame($criada['refresh_token'], $renovado['refresh_token']);
        $this->assertTrue($sessoes->ativa($criada['sessao'], 'usuario', 7));

        $this->agora += 30;
        $this->assertSame($criada['sessao'], $sessoes->renovar('usuario', $renovado['refresh_token'], null, null)['sessao'], 'O token novo funciona.');
    }

    public function testReusoLogoDepoisEhConcorrenciaEDepoisDaToleranciaEncerraASessao(): void
    {
        $sessoes = $this->sessoes();
        $criada = $sessoes->criar('usuario', 7, null, null);
        $sessoes->renovar('usuario', $criada['refresh_token'], null, null);

        $this->agora += Sessoes::TOLERANCIA_REUSO;
        $this->assertSame('renovacao_concorrente', $this->codigoDoErro(fn () => $sessoes->renovar('usuario', $criada['refresh_token'], null, null)));
        $this->assertTrue($sessoes->ativa($criada['sessao'], 'usuario', 7), 'Repetição dentro da tolerância não derruba a sessão.');

        $this->agora += 1;
        $this->assertSame('refresh_reutilizado', $this->codigoDoErro(fn () => $sessoes->renovar('usuario', $criada['refresh_token'], null, null)));
        $this->assertFalse($sessoes->ativa($criada['sessao'], 'usuario', 7), 'Cópia vazada: a sessão inteira cai.');
    }

    public function testRefreshDeClienteNaoRenovaSessaoDeUsuario(): void
    {
        $criada = $this->sessoes()->criar('usuario', 7, null, null);

        $this->assertSame('refresh_invalido', $this->codigoDoErro(fn () => $this->sessoes()->renovar('cliente', $criada['refresh_token'], null, null)));
        $this->assertSame(1, (int) $this->db->where('usado_em IS NULL', null, false)->count_all_results(Sessoes::TABELA_TOKENS), 'O token não foi consumido.');
    }

    public function testRefreshExpiradoOuSessaoAlemDoLimiteAbsoluto(): void
    {
        $criada = $this->sessoes(3600, 7200)->criar('usuario', 7, null, null);
        $this->agora += 3601;
        $this->assertSame('refresh_expirado', $this->codigoDoErro(fn () => $this->sessoes(3600, 7200)->renovar('usuario', $criada['refresh_token'], null, null)));

        $outra = $this->sessoes(3600, 3700)->criar('usuario', 7, null, null);
        $this->agora += 3000;
        $renovada = $this->sessoes(3600, 3700)->renovar('usuario', $outra['refresh_token'], null, null);
        $this->assertSame($this->agora + 700, $renovada['refresh_expira_em'], 'O refresh novo não passa do fim da sessão.');

        $this->agora += 701;
        $this->assertSame('sessao_expirada', $this->codigoDoErro(fn () => $this->sessoes(3600, 3700)->renovar('usuario', $renovada['refresh_token'], null, null)));
    }

    public function testLogoutEncerramentoPorTitularEOutraContaNaoEncerraSessaoAlheia(): void
    {
        $sessoes = $this->sessoes();
        $a = $sessoes->criar('usuario', 7, null, null);
        $b = $sessoes->criar('usuario', 7, null, null);
        $alheia = $sessoes->criar('usuario', 8, null, null);

        $this->assertFalse($sessoes->encerrar($alheia['sessao'], 'usuario', 7), 'Quem não é dono não encerra.');
        $this->assertTrue($sessoes->ativa($alheia['sessao'], 'usuario', 8));

        $this->assertSame(1, $sessoes->encerrarDoTitular('usuario', 7, 'teste', $a['sessao']));
        $this->assertTrue($sessoes->ativa($a['sessao'], 'usuario', 7));
        $this->assertFalse($sessoes->ativa($b['sessao'], 'usuario', 7));
        $this->assertSame('sessao_encerrada', $this->codigoDoErro(fn () => $sessoes->renovar('usuario', $b['refresh_token'], null, null)));

        $this->assertCount(1, $sessoes->listar('usuario', 7));
    }

    public function testBibliotecaDosModelsEncerraAsSessoesDoUsuarioEDoCliente(): void
    {
        $sessoes = $this->sessoes();
        $usuario = $sessoes->criar('usuario', 7, null, null);
        $cliente = $sessoes->criar('cliente', 7, null, null);

        require_once APPPATH . 'libraries/Api_sessoes.php';
        (new Api_sessoes(['db' => $this->db]))->encerrarDoUsuario(7);

        $this->assertFalse($sessoes->ativa($usuario['sessao'], 'usuario', 7));
        $this->assertTrue($sessoes->ativa($cliente['sessao'], 'cliente', 7), 'Usuário e cliente com o mesmo id são titulares diferentes.');

        (new Api_sessoes(['db' => $this->db]))->encerrarDoCliente(7);
        $this->assertFalse($sessoes->ativa($cliente['sessao'], 'cliente', 7));
    }
}
