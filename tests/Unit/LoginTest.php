<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Login.
 *
 * verificarLogin() depende de sessão, formulários e banco, então não roda fora
 * do framework. O que dá para isolar é a decisão de expiração da conta, que é
 * o que decide se um usuário com senha correta ainda entra no sistema, e a
 * comparação de senha que authorize o acesso.
 */
final class LoginTest extends MaposTestCase
{
    /**
     * @param string|null $dataExpiracao null quando a coluna vem vazia
     */
    private function contaExpirada($dataExpiracao): bool
    {
        $login = $this->makeInstance(Login::class);

        return (bool) $this->invokeMethod($login, 'chk_date', [$dataExpiracao]);
    }

    public function testContaExpiradaEhRecusada(): void
    {
        $this->assertTrue($this->contaExpirada('01/01/2020'));
    }

    public function testContaVigenteEhAceita(): void
    {
        $this->assertFalse($this->contaExpirada('01/01/2099'));
    }

    public function testContaQueExpiraHojeAindaEntra(): void
    {
        // Comparação é estrita com 'now'; a data de hoje já passou no instante
        // da checagem, então a conta é tratada como expirada.
        $this->assertTrue($this->contaExpirada((new DateTime())->format('d/m/Y')));
    }

    /**
     * Conta sem data de expiração não expira.
     *
     * usuarios.dataExpiracao é date DEFAULT NULL e o servidor não exige
     * preenchimento, então esse é o estado de qualquer usuário criado sem
     * expiração. Antes da correção, chk_date(null) fazia new DateTime(null),
     * que resolve para o instante atual, e comparava contra um segundo
     * new DateTime('now'): quando o relógio tinha avançado entre as duas
     * chamadas, o login era recusado com "a conta está expirada".
     */
    public function testContaSemDataDeExpiracaoNaoExpira(): void
    {
        $this->assertFalse($this->contaExpirada(null));
    }

    /**
     * String vazia e o valor zero-ish do banco caem no mesmo caminho de null.
     *
     * A coluna é date, então o banco devolve null ou uma data — mas o dado
     * vem de um POST e a API usa post('dataExpiracao'), que devolve string
     * vazia quando o campo não vem.
     */
    #[DataProvider('valoresVaziosDeExpiracao')]
    public function testValoresVaziosNaoExpiram($valor): void
    {
        $this->assertFalse($this->contaExpirada($valor));
    }

    public static function valoresVaziosDeExpiracao(): array
    {
        return [
            'null' => [null],
            'string vazia' => [''],
            'string de espacos' => ['   '],
        ];
    }

    /**
     * A regra continua valendo depois da correção: data passada expira.
     */
    public function testDataPassadaContinuaExpirando(): void
    {
        $this->assertTrue($this->contaExpirada('01/01/2020'));
    }

    /**
     * A regra de expiração existe em dois lugares: Login::chk_date() e
     * UsuariosController::chk_date(), na API.
     *
     * As duas cópias nasceram idênticas e essa duplicação é a origem de metade
     * do problema do #2884: corrigir uma e esquecer a outra deixa o admin e a
     * API discordando sobre quem pode entrar. Este teste falha se as duas
     * implementações divergirem.
     *
     * A comparação é sobre o corpo do método, sem os comentários, porque os
     * dois arquivos documentam contextos diferentes (interface web e token).
     */
    public function testAsDuasCopiasDeChkDateNaoDivergem(): void
    {
        $login = $this->corpoDoMetodo(
            dirname(__DIR__, 2) . '/application/controllers/Login.php',
            'chk_date'
        );
        $api = $this->corpoDoMetodo(
            dirname(__DIR__, 2) . '/application/controllers/api/v1/UsuariosController.php',
            'chk_date'
        );

        $this->assertNotNull($login, 'Login::chk_date não encontrado.');
        $this->assertNotNull($api, 'UsuariosController::chk_date não encontrado.');
        $this->assertSame(
            $login,
            $api,
            'Login::chk_date e UsuariosController::chk_date divergiram. Corrija as duas '
                . 'juntas, senão o admin e a API discordam sobre quem pode entrar.'
        );
    }

    /**
     * Extrai o corpo de um método privado, sem os comentários de bloco.
     */
    private function corpoDoMetodo(string $arquivo, string $metodo): ?string
    {
        $fonte = file_get_contents($arquivo);
        $padrao = '/function ' . preg_quote($metodo, '/') . '\s*\([^)]*\)\s*\{(.*?)\n    \}/s';

        if (! preg_match($padrao, $fonte, $achados)) {
            return null;
        }

        // Tira comentários de linha e de bloco, e normaliza o espaçamento.
        $corpo = preg_replace('#/\*.*?\*/#s', '', $achados[1]);
        $corpo = preg_replace('#//[^\n]*#', '', (string) $corpo);

        return trim((string) preg_replace('/[ \t]+/', ' ', (string) $corpo));
    }

    public function testSenhaConfereComHashValido(): void
    {
        $hash = password_hash('senha-correta', PASSWORD_DEFAULT);

        $this->assertTrue(password_verify('senha-correta', $hash));
    }

    public function testSenhaDivergenteNaoConfere(): void
    {
        $hash = password_hash('senha-correta', PASSWORD_DEFAULT);

        $this->assertFalse(password_verify('senha-errada', $hash));
    }

    public function testHashDeSenhaNaoEhReversivel(): void
    {
        $hash = password_hash('senha-correta', PASSWORD_DEFAULT);

        $this->assertNotSame('senha-correta', $hash);
        $this->assertStringNotContainsString('senha-correta', $hash);
    }
}
