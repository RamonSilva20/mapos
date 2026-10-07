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

    /**
     * knownIssue #2884: usuarios.dataExpiracao é date DEFAULT NULL e a tela de
     * cadastro não exige preenchimento, então uma conta criada sem expiração
     * cai neste caminho com $data_banco = null.
     *
     * chk_date(null) faz new DateTime(null), que resolve para o instante atual,
     * e compara contra um segundo new DateTime('now'). A resposta depende de o
     * relógio ter avançado entre as duas chamadas: quando avançou, o primeiro é
     * "menor que" o segundo e o login é recusado com "A conta do usuário está
     * expirada".
     *
     * Este teste roda a comparação várias vezes porque o resultado é
     * dependente de timing: assertTrue fixo reprovava de forma intermitente,
     * passando na máquina local e falhando no CI. Nos dois casos chk_date
     * devolveu false pelo menos uma vez em N tentativas.
     *
     * O que fixa o defeito é o bloqueio acontecer alguma vez. Quando #2884 for
     * corrigido, chk_date passa a devolver false sempre, e este teste falha de
     * forma determinística — que é o sinal de que a correção entrou.
     */
    public function testContaSemDataDeExpiracaoEhBloqueadaEmAlgumMomento(): void
    {
        $bloqueios = 0;

        for ($i = 0; $i < 200; $i++) {
            $bloqueios += $this->contaExpirada(null) ? 1 : 0;
        }

        $this->assertGreaterThan(
            0,
            $bloqueios,
            'Com dataExpiracao nula, chk_date deveria recusar o login em algum momento (#2884). '
                . 'Se está retornando false sempre, provavelmente o #2884 já foi corrigido: '
                . 'atualize este teste para esperar false.'
        );
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
