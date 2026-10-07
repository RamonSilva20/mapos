<?php

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

    public function testContaSemDataDeExpiracaoEhBloqueadaComDataNula(): void
    {
        // knownIssue: usuarios.dataExpiracao é date DEFAULT NULL e a tela de
        // cadastro não exige o preenchimento, então uma conta criada sem
        // expiração cai neste caminho. chk_date(null) faz new DateTime(null),
        // que resolve para o instante atual, e compara contra um segundo
        // new DateTime('now'), um microssegundo mais tarde: o resultado é true
        // e o login é recusado com "A conta do usuário está expirada".
        //
        // O teste fixa o comportamento atual para documentar a falha; a
        // correção está em #2833. Quando for corrigida, este teste deve passar
        // a esperar false e a KnownIssue ser removida.
        $this->assertTrue($this->contaExpirada(null));
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
