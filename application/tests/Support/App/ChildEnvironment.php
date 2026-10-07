<?php

namespace Tests\Support\App;

/**
 * A metade compartilhada do ambiente que um processo filho da suíte recebe.
 *
 * As refeições de inicialização são pedaços de ambiente, não do processo pai:
 * o FrontendBoundaryTest sobe um `php -S`, o Utf8mb4DownTest sobe a sonda, e os
 * dois precisam do mesmo mínimo para o config do filho não cair no placeholder
 * de credencial. As chaves de configuração apareciam copiadas de teste em teste;
 * este é o lugar único onde a lista mora (ver AGENTS.md, `FrontendBoundaryTest`).
 *
 * Quem usa soma o que o próprio filho precisa: o servidor front-end monta a
 * resposta com `DB_*` e `APP_ENVIRONMENT`, a sonda com `MAPOS_TEST_DB_*` e
 * `TEST_TOKEN`. O que está aqui não tem segredo de transporte — só o que um
 * boot do CI3 exige para não escolher sozinho.
 */
final class ChildEnvironment
{
    /**
     * O mínimo comum: os caminhos do ambiente (quando o pai os tem) e as cinco
     * chaves que o config lê sem valor padrão.
     *
     * `PATH` ganha um fallback que o pai não precisa ter; as cinco chaves ganham
     * marcadores — nunca são afirmados por teste nenhum, servem só para o boot
     * do filho não tocar num `.env` do desenvolvedor.
     *
     * @return array<string, string>
     */
    public static function base(): array
    {
        $environment = [];

        foreach (['PATH', 'HOME', 'LANG', 'LC_ALL', 'TMPDIR', 'COMPOSER_HOME', 'XDG_CONFIG_HOME'] as $name) {
            if (isset($_ENV[$name])) {
                $environment[$name] = $_ENV[$name];
            }
        }

        $environment['PATH'] = $environment['PATH'] ?? getenv('PATH') ?: '/usr/bin:/bin';

        return $environment + [
            // As cinco vêm de $_ENV com fallback, porque config.php lê as
            // cinco sem valor padrão e um filho sem elas loga o aviso que
            // quebraria o assert de árvore limpa do FrontendBoundaryTest.
            'APP_ENCRYPTION_KEY' => $_ENV['APP_ENCRYPTION_KEY'] ?? 'mapos-child-encryption-key',
            'GLOBAL_XSS_FILTERING' => $_ENV['GLOBAL_XSS_FILTERING'] ?? 'false',
            'API_JWT_KEY' => $_ENV['API_JWT_KEY'] ?? 'mapos-child-jwt-key',
            'API_TOKEN_EXPIRE_TIME' => $_ENV['API_TOKEN_EXPIRE_TIME'] ?? '3600',
            // Fora da árvore de código, como config/testing/config.php faz: um
            // log do filho na árvore quebraria o format:check, e o log existe
            // DE PROPÓSITO no caminho 404.
            'APP_LOG_PATH' => rtrim(sys_get_temp_dir(), '/') . '/mapos-test-logs/',
        ];
    }
}
