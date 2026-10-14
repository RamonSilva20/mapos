<?php

/**
 * Regras do instalador que não dependem de tela: se o sistema já está
 * instalado, validação do que chega pelo formulário e montagem do .env e do
 * SQL de carga. Fica separado de index.php e do_install.php para ser testado.
 *
 * O instalador só pode rodar uma vez. A trava é application/.env (criado no
 * fim da instalação) ou install/instalado.lock; qualquer um dos dois bloqueia
 * index.php e do_install.php antes de ler o formulário ou abrir conexão com o
 * banco, para o arquivo não servir de testador de conexão num sistema em uso.
 */
const INSTALADOR_API_EXPIRACOES = ['60', '3600', '86400', '604800', '2592000'];

function instaladorRaiz(): string
{
    return dirname(__DIR__);
}

/**
 * Lê o disco: outra instalação pode terminar entre duas chamadas.
 *
 * @phpstan-impure
 */
function instaladorInstalado(?string $raiz = null): bool
{
    $raiz ??= instaladorRaiz();

    return file_exists($raiz . '/application/.env') || file_exists($raiz . '/install/instalado.lock');
}

/** Resposta curta e sem detalhes (versão do PHP, requisitos, caminhos) de um sistema já instalado. */
function instaladorBloquear(bool $json = false): never
{
    http_response_code(403);
    header('Cache-Control: no-store');

    if ($json) {
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode(['success' => false, 'message' => 'O Map-OS já está instalado.']));
    }

    header('Content-Type: text/html; charset=utf-8');
    exit('<!DOCTYPE html><html lang="pt-br"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Map-OS já instalado</title></head>'
        . '<body style="font-family:sans-serif;max-width:32rem;margin:4rem auto;padding:0 1rem;line-height:1.5">'
        . '<h1 style="font-size:1.25rem">Map-OS já instalado</h1>'
        . '<p>O instalador não pode ser executado de novo. Apague a pasta <code>install</code> do servidor.</p>'
        . '<p><a href="../">Ir para a página de login</a></p></body></html>');
}

/**
 * Valida o POST do formulário.
 *
 * @return array{0: array<string, string>|null, 1: string|null} [dados, erro]
 */
function instaladorValidar(array $post): array
{
    $campos = [];

    foreach (['host', 'dbuser', 'dbpassword', 'dbname', 'full_name', 'email', 'password', 'base_url'] as $nome) {
        $valor = $post[$nome] ?? '';
        if (! is_string($valor)) {
            return [null, 'Por favor insira todos os campos.'];
        }
        if (preg_match('/[\x00-\x1F\x7F]/', $valor)) {
            return [null, 'Os campos não podem ter quebra de linha nem caracteres de controle.'];
        }
        $campos[$nome] = $nome === 'password' || $nome === 'dbpassword' ? $valor : trim($valor);
    }

    foreach (['host', 'dbuser', 'dbname', 'full_name', 'email', 'password', 'base_url'] as $obrigatorio) {
        if ($campos[$obrigatorio] === '') {
            return [null, 'Por favor insira todos os campos.'];
        }
    }

    if (filter_var($campos['email'], FILTER_VALIDATE_EMAIL) === false) {
        return [null, 'Por favor insira um email válido.'];
    }

    if (strlen($campos['password']) < 8) {
        return [null, 'A senha do administrador precisa ter pelo menos 8 caracteres.'];
    }

    if (! preg_match('#^https?://[^\s"\'\\\\]+$#i', $campos['base_url'])) {
        return [null, 'Informe a URL completa do sistema, começando com http:// ou https://.'];
    }

    $expiracao = (string) ($post['enter_token_expire_time'] ?? '3600');
    $api = (string) ($post['enter_api_enabled'] ?? 'false');
    if (! in_array($expiracao, INSTALADOR_API_EXPIRACOES, true) || ! in_array($api, ['true', 'false'], true)) {
        return [null, 'Configuração da API inválida.'];
    }

    return [$campos + ['enter_token_expire_time' => $expiracao, 'enter_api_enabled' => $api], null];
}

/** Valor entre aspas duplas para o .env (o Dotenv interpreta \, " e $ ali dentro). */
function instaladorValorEnv(string $valor): string
{
    return addcslashes($valor, '\\"$');
}

/**
 * Preenche o modelo .env.example. A troca é de uma passada só (strtr): um
 * valor que contenha outro marcador, como uma senha "enter_db_name", não é
 * substituído de novo.
 *
 * @param array<string, string> $dados Saída de instaladorValidar()
 */
function instaladorMontarEnv(string $modelo, array $dados, string $chaveDeCriptografia, string $chaveJwt): string
{
    return strtr($modelo, [
        'enter_db_hostname' => instaladorValorEnv($dados['host']),
        'enter_db_username' => instaladorValorEnv($dados['dbuser']),
        'enter_db_password' => instaladorValorEnv($dados['dbpassword']),
        'enter_db_name' => instaladorValorEnv($dados['dbname']),
        'enter_encryption_key' => $chaveDeCriptografia,
        'enter_baseurl' => instaladorValorEnv($dados['base_url']),
        'enter_jwt_key' => $chaveJwt,
        'enter_token_expire_time' => $dados['enter_token_expire_time'],
        'enter_api_enabled' => $dados['enter_api_enabled'],
        'pre_installation' => 'production',
    ]);
}

/**
 * Preenche o SQL de carga com a conta de administrador. O escape é do
 * mysqli; o hash da senha vem de password_hash().
 */
function instaladorMontarSql(string $sql, array $dados, string $agora, callable $escapar): string
{
    return strtr($sql, [
        'admin_name' => $escapar($dados['full_name']),
        'admin_email' => $escapar($dados['email']),
        'admin_password' => password_hash($dados['password'], PASSWORD_DEFAULT),
        'admin_created_at' => $agora,
    ]);
}

/** Regras de acesso do Apache de quem ainda não instalou: só a tela e o envio do formulário ficam públicos. */
function instaladorNegarApache(string $padrao): string
{
    return "<FilesMatch \"{$padrao}\">\n"
        . "    <IfModule mod_authz_core.c>\n        Require all denied\n    </IfModule>\n"
        . "    <IfModule !mod_authz_core.c>\n        Order allow,deny\n        Deny from all\n    </IfModule>\n"
        . "</FilesMatch>\n";
}

/** install/.htaccess gravado ao fim da instalação: só index.php (a página de aviso) continua acessível. */
function instaladorHtaccessInstalado(): string
{
    return "# Gerado ao concluir a instalação (#2874). Apague a pasta install do servidor.\n"
        . instaladorNegarApache('^(?!index\\.php$)');
}
