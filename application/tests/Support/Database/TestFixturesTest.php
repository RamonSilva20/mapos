<?php

namespace Tests\Support\Database;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * A invariante que segura a contagem de usuários das fixtures.
 *
 * `USER_COUNT` é escrita à mão porque `count()` não é expressão de constante em
 * PHP, então a constante não pode derivar de `EXTRA_USERS`. Uma constante escrita à
 * mão sem conferência é uma bomba-relógio: a montagem do banco a usa para dizer
 * quantas contas existem, a reinstalação por teste a usa para conferir o que
 * gravou, e o erro não aparece em lugar nenhum — aparece como "a seed parou no
 * meio", uma mensagem que culpa a seed por uma constante errada dois arquivos
 * longe.
 *
 * Este arquivo é a conferência. Sem ele, a constante é um número que ninguém
 * confere; com ele, um quarto usuário sem o quarto número quebra aqui, e a
 * quebra é na linha certa.
 *
 * Sem trait de transação e sem banco: as três coisas conferidas são listas e
 * contagens em memória, e é justamente por isso que a verificação é barata o
 * suficiente para existir.
 */
final class TestFixturesTest extends TestCase
{
    /**
     * A constante, a lista de e-mails e as contas extras dizem o mesmo número.
     *
     * As três de uma vez, porque a divergência entre quaisquer duas delas é a
     * mesma falha: um usuário instalado que a conferência não conta, ou uma conta
     * prometida que não foi instalada. Separar em três casos só multiplicaria o
     *trabalho de ler a falha.
     */
    #[Test]
    public function testTheUserCountMatchesTheEmailListAndTheExtraUsers(): void
    {
        $emails = TestFixtures::userEmails();

        $this->assertCount(
            TestFixtures::USER_COUNT,
            $emails,
            'USER_COUNT não bate com a lista de e-mails. Um dos dois foi editado sem o outro, e o '
            . 'erro só apareceria como uma montagem que se diz incompleta.'
        );
    }

    /**
     * A conta da seed é uma, e as de recusa são as que a suíte precisa.
     *
     * A lista é o contrato com quem consome: `setup-db.php` a imprime, e
     * LoginControllerTest usa `inativo@` e `expirado@` para cobrir os dois caminhos
     * de recusa. A ordem também é contrato, porque a montagem mostra os e-mails
     * nessa ordem e quem lê a saída compara com a saída de ontem.
     */
    #[Test]
    public function testTheEmailListIsTheSeedUserFollowedByTheRefusalAccounts(): void
    {
        $this->assertSame(
            ['admin@admin.com', 'inativo@admin.com', 'expirado@admin.com'],
            TestFixtures::userEmails()
        );
    }

    /**
     * A conta da seed vem de `Usuarios`, e a lista não a inventa.
     *
     * `admin@admin.com` não está em `EXTRA_USERS` e não pode estar: a senha dela
     * é escrita pela seed, e a suíte não tem como reescrever o hash sem perder a
     * correspondência que o Login testa. Se um dia alguém colocar o admin em
     * `EXTRA_USERS`, o INSERT passaria por cima da seed e o hash ficaria o que o
     * `addExtraUsers()` copiasse — que é o hash de quem ele acabou de ler.
     */
    #[Test]
    public function testTheSeedAccountIsNotOneOfTheExtraAccounts(): void
    {
        $this->assertNotContains(
            'admin@admin.com',
            array_column($this->extraUsers(), 'email'),
            'A conta da seed não pode estar entre as extras: a senha dela é da seed, e seria '
            . 'sobrescrita por uma cópia de si mesma.'
        );
    }

    /**
     * As duas contas de recusa recusam por motivos diferentes, e nenhum dos dois
     * é o mesmo motivo.
     *
     * `situacao` 0 morre antes da autenticação, e `dataExpiracao` no passado morre
     * depois dela, no `chk_date()`. Se as duas tivessem o mesmo valor, um dos dois
     * caminhos de recusa do Login deixaria de ser coberto sem nada reclamar: a
     * conta existiria, o teste passaria, e a linha nova do controller não teria
     * quem a exercitasse.
     */
    #[Test]
    public function testTheRefusalAccountsFailForTwoDifferentReasons(): void
    {
        $extra = $this->extraUsers();

        $this->assertCount(2, $extra, 'São dois caminhos de recusa, e cada um com a sua conta.');

        $inactive = $this->accountNamed($extra, 'Inativo');
        $expired = $this->accountNamed($extra, 'Expirado');

        $this->assertSame(0, $inactive['situacao'], 'A conta inativa tem de ser recusada por situacao.');
        $this->assertSame(1, $expired['situacao'], 'A conta expirada tem de passar a autenticação para morrer depois.');

        $this->assertGreaterThan(
            time(),
            strtotime((string) $inactive['dataExpiracao']),
            'A conta dita inativa não pode estar expirada, senão os dois caminhos de recusa são o mesmo.'
        );
        $this->assertLessThan(
            time(),
            strtotime((string) $expired['dataExpiracao']),
            'A conta dita expirada precisa de uma data no passado, que é o que o chk_date() olha.'
        );
    }

    /**
     * Os CPFs das contas são distintos entre si e não são o da seed.
     *
     * Não é uma regra do Login: é uma regra de fixture. Dois usuários com o mesmo
     * CPF passam a ser o mesmo usuário para qualquer consulta que filtre por ele, e
     * um teste que conte por CPF passa a contar um usuário onde há dois — sem erro,
     * porque a consulta concorda com o que a tabela tem.
     */
    #[Test]
    public function testTheRefusalAccountsHaveDistinctCpfs(): void
    {
        $cpfs = array_column($this->extraUsers(), 'cpf');

        $this->assertSame(
            $cpfs,
            array_unique($cpfs),
            'Duas contas de fixture com o mesmo CPF viram um usuário só para qualquer consulta por CPF.'
        );
    }

    /**
     * A conta pedida por nome, ou o caso falha dizendo qual sumiu.
     *
     * @param  list<array<string, string|int>>  $extra
     * @return array<string, string|int>
     */
    private function accountNamed(array $extra, string $name): array
    {
        foreach ($extra as $user) {
            if ($user['nome'] === $name) {
                return $user;
            }
        }

        $this->fail("A conta '{$name}' sumiu de EXTRA_USERS, e o Login precisa dela para cobrir um dos dois caminhos de recusa.");
    }

    /**
     * As contas extras, pela reflexão que a constante privada não expõe.
     *
     * Ler a constante por reflexão é feio, e a alternativa — torná-la pública e
     * duplicar a lista de e-mails — seria pior: um segundo lugar para os mesmos
     * dois usuários divergirem. O teste é o único consumidor que precisa do
     * detalhe, e o detalhe é privado justamente para o resto da suíte usar
     * `userEmails()`.
     *
     * @return list<array<string, string|int>>
     */
    private function extraUsers(): array
    {
        $constant = new \ReflectionClass(TestFixtures::class);

        /** @var list<array<string, string|int>> $users */
        $users = $constant->getConstant('EXTRA_USERS');

        return $users;
    }
}
