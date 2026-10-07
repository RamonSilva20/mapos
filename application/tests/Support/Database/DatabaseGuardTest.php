<?php

namespace Tests\Support\Database;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * A barreira entre a suíte e o banco de desenvolvimento.
 *
 * Sem trait de transação e sem banco: DatabaseGuard não abre conexão, ela diz não
 * ou calcula um nome. É por isso que este arquivo existe e antes não existia: as
 * duas recusas usavam `exit()`, e um `exit()` não pode ser capturado por um teste.
 * Uma guarda que não pode ser testada é um costume, não uma guarda — e esta é a
 * única barreira entre um teste e `DROP DATABASE mapos`.
 *
 * A conversão para exceção não tirou a proteção: quem chama, nos scripts de `bin/`,
 * traduz a exceção de volta em `fwrite(STDERR)` e `exit(1)`. O que mudou é que
 * agora o caminho da recusa é observável, e é isso que os casos abaixo fixam.
 *
 * A razão de `assertDatabaseNameIsSafe()` recusar em vez de sanitizar está no
 * docblock de `workerDatabaseName()`, e o caso correspondente está nos dois arquivos:
 * sanitizar o nome de um banco é a forma de trocar uma barreira por uma coincidência.
 */
final class DatabaseGuardTest extends TestCase
{
    /**
     * Um nome que termina em '_test' passa, e a lista inclui o que a suíte usa.
     *
     * O sufixo, e não o prefixo nem o caminho, é o que a guarda exige. `mapos_test` e
     * `mapos_1_test` passam; `mapos` não passa, mesmo sendo o nome mais óbvio que
     * alguém escreveria.
     */
    #[Test]
    public function testANameEndingInTestIsAccepted(): void
    {
        foreach (['mapos_test', 'mapos_1_test', 'a_test', '_test'] as $name) {
            DatabaseGuard::assertDatabaseNameIsSafe($name);
        }

        $this->addToAssertionCount(4);
    }

    /**
     * Qualquer nome sem o sufixo é recusado, e o nome recusado aparece na mensagem.
     *
     * `mapos` e `test` são os dois jeitos de errar o sufixo: um esqueceu de
     * escrever, o outro escreveu do lado errado. E `mapos_teste` é o caso que só
     * aparece quando alguém usa o nome em português — a regex é ancorada no fim
     * justamente para ele não passar.
     *
     * A mensagem importa porque é o que o operador lê quando o CI falha por
     * configuração, e um "abortado" sem o nome recebido não diz o que corrigir.
     */
    #[DataProvider('provideUnsafeDatabaseNames')]
    #[Test]
    public function testANameWithoutTheTestSuffixIsRefused(string $name): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("'" . $name . "'");

        DatabaseGuard::assertDatabaseNameIsSafe($name);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideUnsafeDatabaseNames(): iterable
    {
        // Um nome só, dois rótulos, é o caso repetido: `mapos` era a entrada duas
        // vezes, uma como "sem sufixo" e outra como "o banco de desenvolvimento".
        // O rótulo que ficou é o concreto, porque é ele que diz por que a regra
        // existe: sem ele, o conjunto parece cobrir a forma do nome e o valor
        // concreto da regra, e não cobre.
        yield 'o banco de desenvolvimento, que é o que a regra existe para proteger' => ['mapos'];
        yield 'sufixo trocado' => ['mapos_teste'];
        yield 'sufixo no meio' => ['test_mapos'];
        yield 'só o sufixo, sem nome' => ['test'];
        yield 'vazio' => [''];
        yield 'produção' => ['mapos_producao'];
    }

    /**
     * O sufixo `_test` sozinho não autoriza o nome: ele também tem que ser um
     * identificador.
     *
     * Os dois casos daqui passam na exigência do sufixo e falham na do identificador,
     * e os dois são o mesmo defeito visto de dois lados: o nome entra concatenado
     * entre crases num `DROP DATABASE` e num `CREATE DATABASE`, e num qualificado
     * `banco`.`tabela`. Uma barra, uma crase, um espaço ou um ponto-e-vírgula no meio
     * do nome fecham a instrução antes do fim.
     *
     * O primeiro é travessia de caminho: `../../x_test` termina em `_test`, então a
     * guarda antiga o aceitava, e `SchemaFingerprint::fingerprintPath()` o usava
     * para montar o arquivo de impressão, escrevendo fora do diretório que ela
     * documentava não ser escapável. O segundo é injeção de DDL, e é o que o
     * comentário da guarda chamava de "uma única resposta para isto é seguro para
     * concatenar numa instrução" — resposta que a guarda não dava.
     *
     * O terceiro é o caso incidental, e é o que uma pessoa depara na prática: um
     * `MAPOS_TEST_DB_DATABASE` escrito com traço ou ponto, que hoje funciona e
     * depois de aqui passa a ser recusado com a mensagem dizendo o que é aceito.
     * Recusar é a direção certa para a única barreira entre um teste e o banco de
     * desenvolvimento.
     *
     * @return iterable<string, array{string}>
     */
    #[DataProvider('provideTestSuffixedNamesThatAreNotIdentifiers')]
    #[Test]
    public function testATestSuffixedNameThatIsNotAnIdentifierIsRefused(string $name): void
    {
        $this->assertSame(
            1,
            preg_match('/_test$/', $name),
            'o caso precisa passar na exigência do sufixo, senão não prova a do identificador'
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("'" . $name . "'");

        DatabaseGuard::assertDatabaseNameIsSafe($name);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideTestSuffixedNamesThatAreNotIdentifiers(): iterable
    {
        yield 'travessia de caminho, que escapa do diretório de impressões digitais' => ['../../x_test'];
        yield 'crase e ponto-e-vírgula, que fecham a instrução antes do fim' => ['x`;DROP DATABASE mapos;-- _test'];
        yield 'espaço, que o MySQL aceitaria entre aspas' => ['meu banco_test'];
        yield 'traço, que alguém usa por hábito' => ['mapos-dev_test'];
        yield 'acento, que um nome em português traz' => ['mapos_josé_test'];
    }

    /**
         * workerName() resolve o token do ambiente, e o nome é o mesmo que a forma
         * com token na mão produziria.
         *
         * É a chamada que os scripts e o clone usam; o `?? 'solo'` copiado em cinco
         * arquivos passou a morar aqui, e a promessa é a delegação sem desvio. O
         * ambiente é restaurado no finally porque a suíte pode estar rodando sob o
         * ParaTest, onde o TEST_TOKEN do worker é a própria razão do nome.
         */
    #[Test]
    public function testWorkerNameFollowsTheEnvironmentToken(): void
    {
        $original = getenv('TEST_TOKEN');

        try {
            putenv('TEST_TOKEN=7');
            $this->assertSame('mapos_7_test', DatabaseGuard::workerName('mapos_test'));
        } finally {
            putenv('TEST_TOKEN=' . ($original === false ? '' : $original));
        }
    }

    #[Test]
    public function testWorkerNameFallsBackToSoloWithoutAnEnvironmentToken(): void
    {
        $original = getenv('TEST_TOKEN');

        try {
            putenv('TEST_TOKEN=');
            $this->assertSame('mapos_solo_test', DatabaseGuard::workerName('mapos_test'));
        } finally {
            putenv('TEST_TOKEN=' . ($original === false ? '' : $original));
        }
    }

    /**
     * Os dois lados do momento do nome: token antes do sufixo, e a guarda por trás.
     *
     * Este é o caso que a ordem do nome obedece: `mapos_test_1` é recusado pela
     * guarda acima, e é recusado DE PROPÓSITO. Se o token fosse depois do sufixo, o
     * nome do worker não passaria pela única barreira que existe contra um `DROP`
     * no banco errado, e a proteção passaria a depender de cada script lembrar de
     * checar o sufixo por conta própria.
     */
    #[Test]
    public function testTheWorkerTokenGoesBeforeTheTestSuffix(): void
    {
        $this->assertSame('mapos_1_test', DatabaseGuard::workerDatabaseName('mapos_test', '1'));
        $this->assertSame('mapos_abc_test', DatabaseGuard::workerDatabaseName('mapos_test', 'abc'));

        $this->expectException(RuntimeException::class);
        DatabaseGuard::assertDatabaseNameIsSafe('mapos_test_1');
    }

    /**
     * A mesma chamada é idempotente: chamar duas vezes dá o mesmo nome.
     *
     * O `workerDatabaseName()` é chamado de dois lugares independentes — o clone e
     * a conferência do drop-worker-databases — e os dois precisam chegar ao mesmo
     * nome. Se a função lembrasse de um token anterior, os dois lados divergiriam e
     * o drop pararia de reconhecer o banco que o clone criou.
     */
    #[Test]
    public function testTheWorkerNameIsIdempotent(): void
    {
        $this->assertSame(
            DatabaseGuard::workerDatabaseName('mapos_test', '7'),
            DatabaseGuard::workerDatabaseName('mapos_test', '7')
        );
    }

    /**
     * Um token com caractere que não pode entrar num nome é recusado, não limpo.
     *
     * Sanitizar '1-a' e '1_a' para o mesmo banco seria uma falha de isolamento
     * criada em silêncio, e ela só apareceria quando um worker esperasse o lock do
     * outro — intermitente por definição. Recusar é a resposta que transforma esse
     * silêncio num erro de primeira linha.
     */
    #[DataProvider('provideUnsafeTokens')]
    #[Test]
    public function testATokenWithAnUnsafeCharacterIsRefused(string $token): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("'{$token}'");

        DatabaseGuard::workerDatabaseName('mapos_test', $token);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideUnsafeTokens(): iterable
    {
        yield 'hífen' => ['1-a'];
        yield 'ponto' => ['1.a'];
        yield 'espaço' => ['1 a'];
        yield 'barra' => ['1/a'];
        yield 'vazio' => [''];
        yield 'cedilha' => ['1ç'];
        yield 'asterisco' => ['1*'];
    }

    /**
     * Um nome de worker que não cabe em 64 caracteres é recusado, e não truncado.
     *
     * O MySQL trunca um identificador longo em silêncio, e truncar
     * 'mapos_x_12_test' pode virar 'mapos_x_1_test' — dois workers no mesmo banco,
     * que é exatamente a linha que o clone existe para não contestar. A recusa
     * acontece antes do CREATE por isso.
     */
    #[Test]
    public function testAWorkerNameThatWouldOverflowTheMysqlIdentifierLimitIsRefused(): void
    {
        $long = str_repeat('x', 60) . '_test';
        $this->assertSame(65, strlen($long));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('64');

        DatabaseGuard::workerDatabaseName($long, '1');
    }

    /**
     * A base do modelo é o nome sem o sufixo, e um modelo sem base é recusado.
     */
    #[Test]
    public function testTheModelBaseIsTheNameWithoutTheSuffix(): void
    {
        $this->assertSame('mapos', DatabaseGuard::modelBase('mapos_test'));
        $this->assertSame('mapos_1', DatabaseGuard::modelBase('mapos_1_test'));

        $this->expectException(RuntimeException::class);
        DatabaseGuard::modelBase('_test');
    }

    /**
     * Só o nome que `workerDatabaseName()` produziria é reconhecido como de worker.
     *
     * Esta é a conferida que separa o drop-worker-databases de um `DROP` preguiçoso,
     * e ela é feita refazendo o nome em vez de comparando com um padrão escrito à
     * mão. Os casos abaixo são os que um padrão escrito à mão aceitaria por engano.
     */
    #[Test]
    public function testOnlyAReDerivableWorkerNameIsRecognised(): void
    {
        $model = 'mapos_test';

        $this->assertTrue(DatabaseGuard::isWorkerDatabaseName('mapos_1_test', $model));
        $this->assertTrue(DatabaseGuard::isWorkerDatabaseName('mapos_abc_test', $model));

        // O token mais simples que é legal é 'test', e `mapos_test_test` é um nome
        // de worker verdadeiro. A conferida refaz o nome, e o que ela recusa é o
        // que o clone não produziria — não o que parece esquisito. Um padrão
        // escrito à mão teria de listar as exceções, e a lista é infinita.
        $this->assertTrue(DatabaseGuard::isWorkerDatabaseName('mapos_test_test', $model));

        // O próprio modelo nunca é um worker, e é o banco que mais caro seria
        // apagar: reconstruí-lo custa a cadeia inteira de migrations.
        $this->assertFalse(DatabaseGuard::isWorkerDatabaseName('mapos_test', $model));

        // Sem token no meio.
        $this->assertFalse(DatabaseGuard::isWorkerDatabaseName('mapos__test', $model));

        // De outro modelo, visto pelo modelo daqui. Com um padrão como
        // `_\d+_test$` este nome passaria, e é o banco de outro branch — é o
        // WorkerParityTest e o clone de outro token que o usariam, e este script
        // não tem por que mandar embora num banco que não é dele.
        $this->assertFalse(DatabaseGuard::isWorkerDatabaseName('financeiro_1_test', $model));

        // E o worker do outro modelo continua sendo dele, o que é o outro lado da
        // mesma regra: quem decide é o modelo que se está limpando.
        $this->assertTrue(DatabaseGuard::isWorkerDatabaseName('financeiro_2_test', 'financeiro_test'));

        // Não é nome de worker de jeito nenhum.
        $this->assertFalse(DatabaseGuard::isWorkerDatabaseName('mapos', $model));
        $this->assertFalse(DatabaseGuard::isWorkerDatabaseName('', $model));
        $this->assertFalse(DatabaseGuard::isWorkerDatabaseName('mapos_1', $model));
    }

    /**
     * Um token que o nome recusa não transforma o banco num worker.
     *
     * `mapos_1-a_test` tem cara de nome de worker e um token que
     * `workerDatabaseName()` jamais produziria. A conferida refaz o nome, o
     * `workerDatabaseName()` recusa o token, e a resposta é não — que é o que
     * impede o script de apagar um banco que ninguém do ParaTest criou.
     */
    #[Test]
    public function testATokenTheNameRefusesDoesNotMakeItAWorkerDatabase(): void
    {
        $this->assertFalse(DatabaseGuard::isWorkerDatabaseName('mapos_1-a_test', 'mapos_test'));
        $this->assertFalse(DatabaseGuard::isWorkerDatabaseName('mapos_1.a_test', 'mapos_test'));
    }

    /**
     * O identificador que pode entrar num SQL é um só, e ele recusa o que não é nome.
     *
     * `SchemaReader::identifier()` e `workerDatabaseName()` faziam a mesma pergunta
     * com a mesma regex em dois arquivos. A resposta única mora aqui, e este caso
     * fixa o que ela aceita para que uma mudança de um lado não mude só o outro.
     */
    #[DataProvider('provideIdentifiers')]
    #[Test]
    public function testASafeIdentifierIsAlettersAndDigitsAndUnderscoreOnly(string $value, bool $expected): void
    {
        $this->assertSame($expected, DatabaseGuard::isSafeIdentifier($value));
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function provideIdentifiers(): iterable
    {
        yield 'simples' => ['os', true];
        yield 'com sublinhado' => ['id_os', true];
        yield 'com dígito' => ['tabela1', true];
        yield 'vazio' => ['', false];
        yield 'com ponto' => ['mapos.test', false];
        yield 'com hífen' => ['mapos-test', false];
        yield 'com crase' => ['`os`', false];
        yield 'com aspas' => ['"os"', false];
        yield 'com espaço' => ['id os', false];
        yield 'com ponto e vírgula' => ['os; DROP TABLE x', false];
        yield 'tentativa de injeção' => ['os`; DROP TABLE `x', false];
    }
}
