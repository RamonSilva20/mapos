<?php

namespace Tests\Support\Database;

use PHPUnit\Framework\TestCase;

/**
 * Afirma que a conexão do harness e a do CodeIgniter não podem divergir.
 *
 * A suíte abre duas conexões para o mesmo banco: o PDO do TestDatabase, que cria e
 * apaga o banco, e a do autoloader 'database' do index.php, que o CI3 abre durante o
 * boot. A segunda é configurada por application/config/database.php, que não conhece
 * MAPOS_TEST_DB_* — ele lê $_ENV['DB_*'] e cai no placeholder 'enter_db_username'
 * quando a chave não existe.
 *
 * Quando fromEnvironment() resolvia as credenciais só nas suas propriedades privadas,
 * as duas conexões discordavam sempre que não havia application/.env, porque nada
 * publicava $_ENV['DB_USERNAME']. No desenvolvimento o .env preenche a chave e o
 * placeholder nunca aparecia, então a suíte passava; no CI não existe .env, e o boot
 * morria com "Access denied for user 'enter_db_username'".
 *
 * Um teste que roda dentro do processo já bootado não veria a raiz disso — quem
 * preenche a chave é o bootstrap, antes de qualquer teste existir. Por isso o teste
 * compara o config efetivo com o que foi publicado, e não apenas afirma que existe
 * arquivo de config: é a comparação que pega a regressão, e ela só tem o que
 * comparar onde o .env falta, que é o CI.
 *
 * A decisão de reaproveitar o schema, que é a outra coisa que TestDatabase decide,
 * está em SchemaFingerprintTest.
 */
final class TestDatabaseTest extends TestCase
{
    /**
     * As chaves que fromEnvironment() tem que publicar, e que o config/database.php
     * tem que ler. Inclui o usuário e a senha, que é justamente o par que faltava:
     * host, porta e banco já eram publicados desde o começo.
     *
     * @var array<string, string>
     */
    private const PUBLISHED_KEYS = [
        'DB_HOSTNAME' => 'hostname',
        'DB_PORT' => 'port',
        'DB_DATABASE' => 'database',
        'DB_USERNAME' => 'username',
        'DB_PASSWORD' => 'password',
    ];

    public function testTheCi3ConfigConnectsWithTheSameDatabaseAsTheHarness(): void
    {
        $config = $this->ci3DatabaseConfig();

        foreach (self::PUBLISHED_KEYS as $envKey => $configKey) {
            $this->assertArrayHasKey(
                $envKey,
                $_ENV,
                "TestDatabase::fromEnvironment() precisa publicar {$envKey} em \$_ENV para o config/database.php"
            );

            $this->assertSame(
                $_ENV[$envKey],
                $config[$configKey] ?? null,
                "O config/database.php e o harness precisam concordar sobre {$envKey}"
            );

            // A redundância que importa: se um dia o valor publicado voltar a ser
            // omitido, a comparação acima acusaria uma diferença, e esta diz o quê.
            $this->assertStringNotContainsString(
                'enter_',
                (string) ($config[$configKey] ?? ''),
                "O config/database.php caiu no placeholder de {$configKey}: fromEnvironment() não publicou a chave"
            );
        }
    }

    /**
     * O config que o CI3 realmente usa, resolvido agora.
     *
     * O arquivo é incluído dentro de uma closure em vez do corpo do teste: ele exige
     * BASEPATH e ENVIRONMENT definidos, e o `defined('BASEPATH') or exit()` da
     * primeira linha derrubaria o processo do PHPUnit inteiro se o boot um dia não os
     * tivesse deixado no lugar. Dentro da closure o escopo também é isolado, então o
     * $db não vaza para o teste.
     *
     * @return array<string, mixed>
     */
    private function ci3DatabaseConfig(): array
    {
        $this->assertTrue(
            defined('BASEPATH') && defined('ENVIRONMENT'),
            'O app precisa estar bootado antes de ler o config/database.php'
        );

        $path = TestDatabase::envPath() . '/config/database.php';

        $read = static function (string $path): array {
            include $path;

            return $db['default'];
        };

        $config = $read($path);

        $this->assertIsArray($config, 'O config/database.php precisa montar $db[\'default\'] como array');

        return $config;
    }
}
