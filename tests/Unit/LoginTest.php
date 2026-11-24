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
        // No formato da coluna usuarios.dataExpiracao (date): "d/m/Y" de um dia
        // acima de 12 nem é uma data válida para o DateTime.
        $this->assertTrue($this->contaExpirada((new DateTime())->format('Y-m-d')));
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
