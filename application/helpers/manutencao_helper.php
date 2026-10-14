<?php

/**
 * Avisos de manutenção do sistema para quem administra (cSistema) no painel:
 * migrations do banco ainda não aplicadas e valores antigos e inseguros de
 * cookie e sessão no .env (#2858).
 *
 * Quem instala sem rodar as migrations, ou atualiza da 4.x só copiando os
 * arquivos, fica com o esquema antigo: o limite de tentativas de login, o 2FA
 * e outras proteções da v5 dependem de tabelas e colunas das migrations. Sem
 * esse aviso a falta só aparecia como uma linha no log.
 */
if (! function_exists('migracoesPendentes')) {
    /**
     * Versões de migrations (arquivos em database/migrations) maiores que a
     * aplicada no banco.
     *
     * @param  object       $db     Conexão do CodeIgniter (Query Builder)
     * @param  string|null  $pasta  Pasta das migrations (padrão: application/database/migrations)
     * @return list<string> Nome dos arquivos pendentes, do mais antigo ao mais novo
     */
    function migracoesPendentes($db, ?string $pasta = null): array
    {
        $pasta ??= rtrim(APPPATH, '/\\') . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';

        $arquivos = [];
        foreach (glob($pasta . DIRECTORY_SEPARATOR . '*_*.php') ?: [] as $caminho) {
            $nome = basename($caminho, '.php');
            if (preg_match('/^\d{14}_/', $nome)) {
                $arquivos[] = $nome;
            }
        }
        sort($arquivos, SORT_STRING);

        // Sem a tabela de controle (banco que nunca rodou migrations) nada foi aplicado.
        $aplicada = '0';
        if ($db->table_exists('migrations')) {
            $linha = $db->select('version')->get('migrations')->row();
            $aplicada = (string) ($linha->version ?? '0');
        }

        $aplicada = str_pad($aplicada, 14, '0', STR_PAD_LEFT);

        return array_values(array_filter(
            $arquivos,
            static fn (string $nome): bool => strcmp(substr($nome, 0, 14), $aplicada) > 0
        ));
    }
}

if (! function_exists('envCookiesInseguros')) {
    /**
     * Valores do .env que a 4.x gravava e que enfraquecem a sessão: cookie
     * legível por JavaScript, sessão que não troca de id no login e cookie
     * sem Secure em acesso por HTTPS. Instalações novas já saem com os
     * padrões seguros; quem atualiza só acrescentando variáveis ao .env
     * mantém os antigos.
     *
     * @param  array<string, mixed>  $env    Conteúdo do $_ENV
     * @param  bool                  $https  O acesso atual é por HTTPS
     * @return list<string> Nomes das variáveis a corrigir
     */
    function envCookiesInseguros(array $env, bool $https): array
    {
        $falso = static function ($valor): bool {
            if ($valor === null || trim((string) $valor) === '') {
                return false; // ausente: vale o padrão seguro do sistema
            }

            return filter_var($valor, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === false;
        };

        $inseguras = [];
        if ($falso($env['APP_COOKIE_HTTPONLY'] ?? null)) {
            $inseguras[] = 'APP_COOKIE_HTTPONLY';
        }
        if ($falso($env['APP_SESS_REGENERATE_DESTROY'] ?? null)) {
            $inseguras[] = 'APP_SESS_REGENERATE_DESTROY';
        }
        if ($https && $falso($env['APP_COOKIE_SECURE'] ?? null)) {
            $inseguras[] = 'APP_COOKIE_SECURE';
        }

        return $inseguras;
    }
}

if (! function_exists('manutencaoAvisos')) {
    /**
     * Mensagens de aviso do painel, na ordem de importância.
     *
     * @param  object                $db     Conexão do CodeIgniter
     * @param  array<string, mixed>  $env    Conteúdo do $_ENV
     * @param  bool                  $https  O acesso atual é por HTTPS
     * @return list<string>
     */
    function manutencaoAvisos($db, array $env, bool $https, ?string $pastaMigrations = null): array
    {
        $avisos = [];

        $pendentes = migracoesPendentes($db, $pastaMigrations);
        if ($pendentes !== []) {
            $quantidade = count($pendentes);
            $avisos[] = ($quantidade === 1 ? 'Há 1 atualização do banco de dados pendente' : "Há {$quantidade} atualizações do banco de dados pendentes")
                . '. Enquanto o banco não for atualizado, proteções da versão 5, como o limite de tentativas de login, o 2FA e o histórico da OS, ficam desligadas. '
                . 'Abra Configurações, aba Sistema, e use "Atualizar banco de dados" (ou rode php index.php tools migrate no servidor).';
        }

        $inseguras = envCookiesInseguros($env, $https);
        if ($inseguras !== []) {
            $avisos[] = 'O arquivo application/.env tem valores antigos que enfraquecem a sessão (' . implode(', ', $inseguras)
                . '). Apague essas linhas ou use true (em APP_COOKIE_SECURE, auto) e recarregue o sistema. Detalhes em docs/cookies-e-sessao.md.';
        }

        return $avisos;
    }
}
