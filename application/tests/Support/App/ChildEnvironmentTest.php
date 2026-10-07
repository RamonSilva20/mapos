<?php

namespace Tests\Support\App;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * A promessa de ChildEnvironment::base(): o mínimo que um filho precisa para o
 * boot não escolher sozinho, e nada além disso.
 *
 * O teste não conecta em nada e não sobe filho nenhum: o contrato é o conteúdo
 * do array. A metade que vaza do pai (os caminhos de ambiente) é conferida só
 * quando o ambiente os expõe — num worker do ParaTest o `$_ENV` chega vazio, e
 * um caso que exigisse HOME falharia no CI sem cobrar nada.
 */
final class ChildEnvironmentTest extends TestCase
{
    #[Test]
    public function testTheSharedBaseCarriesWhatAChildBootNeeds(): void
    {
        $base = ChildEnvironment::base();

        foreach (['PATH', 'APP_ENCRYPTION_KEY', 'GLOBAL_XSS_FILTERING', 'API_JWT_KEY', 'API_TOKEN_EXPIRE_TIME', 'APP_LOG_PATH'] as $key) {
            $this->assertArrayHasKey($key, $base, "Faltou '{$key}' no ambiente compartilhado.");
            $this->assertNotSame('', $base[$key], "A chave '{$key}' chegou vazia.");
        }

        $this->assertStringEndsWith('/', $base['APP_LOG_PATH']);
    }

    #[Test]
    public function testThePassthroughCarriesTheParentsEnvironmentWhenPresent(): void
    {
        if (! isset($_ENV['HOME'])) {
            $this->markTestSkipped('O ambiente do processo não expõe HOME para $_ENV.');

            return;
        }

        $this->assertSame($_ENV['HOME'], ChildEnvironment::base()['HOME'] ?? null);
    }
}
