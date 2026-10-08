<?php

use PHPUnit\Framework\Attributes\DataProvider;

require_once APPPATH . 'controllers/Mine.php';

/**
 * Token de recuperação de senha da área do cliente (#2875): só o hash no
 * banco, validade de 1 hora e pedidos anteriores invalidados.
 */
final class TokenRecuperacaoTest extends MaposTestCase
{
    public function testGeraTokenDe64HexEOHashDele(): void
    {
        [$token, $hash] = tokenRecuperacaoGerar();

        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token);
        $this->assertSame(hash('sha256', $token), $hash);
        $this->assertNotSame($token, $hash);
        $this->assertNotSame($token, tokenRecuperacaoGerar()[0]);
    }

    public static function formatosInvalidos(): array
    {
        return [
            'nulo' => [null],
            'vazio' => [''],
            'token antigo de 32 hex' => [str_repeat('a', 32)],
            'maiúsculas' => [str_repeat('A', 64)],
            'com espaço' => [str_repeat('a', 63) . ' '],
            'SQL' => ["' OR 1=1 -- " . str_repeat('a', 52)],
            'o próprio hash com sufixo' => [str_repeat('a', 64) . 'x'],
            'array' => [[str_repeat('a', 64)]],
        ];
    }

    #[DataProvider('formatosInvalidos')]
    public function testFormatoInvalidoNaoGeraHash($valor): void
    {
        $this->assertNull(tokenRecuperacaoHash($valor));
    }

    /**
     * Fluxo do Mine contra o SQLite: o banco tem só o hash, a busca é pelo
     * hash; o token em claro não aparece no banco.
     */
    public function testBancoGuardaSoOHashEABuscaEhPeloHash(): void
    {
        $this->db->query('CREATE TABLE resets_de_senha (id INTEGER PRIMARY KEY AUTOINCREMENT, email VARCHAR(200), token VARCHAR(255), data_expiracao DATETIME, token_utilizado TINYINT)');

        // Subclasse sem o construtor do framework, com o SQLite em $db.
        $mine = new class($this->db) extends Mine {
            public $db;

            public function __construct($db)
            {
                $this->db = $db;
            }
        };

        [$primeiro, $hashPrimeiro] = tokenRecuperacaoGerar();
        $this->db->insert('resets_de_senha', ['email' => 'c@x.com', 'token' => $hashPrimeiro, 'data_expiracao' => date('Y-m-d H:i:s', time() + 3600), 'token_utilizado' => 0]);

        $linha = $this->invokeMethod($mine, 'check_token', [$primeiro]);
        $this->assertNotNull($linha);
        $this->assertTrue($this->invokeMethod($mine, 'tokenVigente', [$linha]));
        // O valor guardado não serve como token: buscar pelo hash não acha nada.
        $this->assertNull($this->invokeMethod($mine, 'check_token', [$hashPrimeiro]));
        $this->assertStringNotContainsString($primeiro, json_encode($this->db->get('resets_de_senha')->result_array()));
    }

    public function testTokenExpiradoOuUsadoNaoVale(): void
    {
        $mine = $this->makeInstance(Mine::class);
        $vigente = (object) ['token_utilizado' => 0, 'data_expiracao' => date('Y-m-d H:i:s', time() + 60)];
        $expirado = (object) ['token_utilizado' => 0, 'data_expiracao' => date('Y-m-d H:i:s', time() - 1)];
        $usado = (object) ['token_utilizado' => 1, 'data_expiracao' => date('Y-m-d H:i:s', time() + 60)];

        $this->assertTrue($this->invokeMethod($mine, 'tokenVigente', [$vigente]));
        $this->assertFalse($this->invokeMethod($mine, 'tokenVigente', [$expirado]));
        $this->assertFalse($this->invokeMethod($mine, 'tokenVigente', [$usado]));
    }

    public function testGeracaoNoControllerUsaHashValidadeEInvalidaAnteriores(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Mine.php');
        preg_match('/function gerarTokenResetarSenha\(\).*?\n    }\n/s', $codigo, $trecho);

        $this->assertNotEmpty($trecho);
        $this->assertStringContainsString('tokenRecuperacaoGerar()', $trecho[0]);
        $this->assertStringContainsString("'token' => \$tokenHash", $trecho[0]);
        $this->assertStringContainsString('time() + self::TOKEN_VALIDADE_SEGUNDOS', $trecho[0]);
        $this->assertMatchesRegularExpression("/where\\('email', \\\$cliente->email\\)->where\\('token_utilizado', 0\\)->update\\('resets_de_senha', \\['token_utilizado' => 1\\]\\)/", $trecho[0]);
        $this->assertStringNotContainsString('random_bytes(16)', $codigo);
        $this->assertSame(3600, Mine::TOKEN_VALIDADE_SEGUNDOS);
    }
}
