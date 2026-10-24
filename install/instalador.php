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

    $armazenamento = $post['storage_path'] ?? '';
    if (! is_string($armazenamento) || preg_match('/[\x00-\x1F\x7F"\'$\\\\]/', $armazenamento)) {
        return [null, 'A pasta dos anexos não pode ter aspas, barra invertida, cifrão nem caracteres de controle.'];
    }

    return [$campos + ['enter_token_expire_time' => $expiracao, 'enter_api_enabled' => $api, 'storage_path' => trim($armazenamento)], null];
}

/**
 * Pasta sugerida para os anexos: ao lado da pasta do sistema (uma acima), fora
 * do que o servidor web publica na instalação comum. Vazio quando a pasta
 * acima não aceita escrita (hospedagem compartilhada); aí vale application/storage.
 */
function instaladorSugerirArmazenamento(?string $raiz = null): string
{
    $raiz ??= instaladorRaiz();
    $acima = dirname($raiz);
    $sugerida = $acima . DIRECTORY_SEPARATOR . 'mapos-storage';

    if (is_dir($sugerida) ? is_writable($sugerida) : is_writable($acima)) {
        return $sugerida;
    }

    return '';
}

/**
 * Confere e cria a pasta dos anexos (STORAGE_PATH). Aceita caminho absoluto ou
 * relativo à raiz do sistema. Recusa pastas que o servidor web publica
 * (assets/, install/ e a própria raiz); application/ fica de fora porque o
 * sistema nega o acesso a ela (.htaccess e nginx).
 *
 * @return array{0: string|null, 1: string|null} [caminho final, ou null quando vazio; erro]
 */
function instaladorPrepararArmazenamento(string $valor, ?string $raiz = null): array
{
    $valor = trim($valor);
    if ($valor === '') {
        return [null, null];
    }

    $raiz = rtrim($raiz ?? instaladorRaiz(), '/\\');
    $absoluto = (bool) preg_match('#^(/|[A-Za-z]:[\\\\/]|\\\\\\\\)#', $valor);
    $caminho = rtrim($absoluto ? $valor : $raiz . DIRECTORY_SEPARATOR . $valor, '/\\');

    if (! is_dir($caminho) && ! @mkdir($caminho, 0750, true) && ! is_dir($caminho)) {
        return [null, 'Não foi possível criar a pasta dos anexos. Crie-a no servidor, dê permissão de escrita ao usuário do PHP e informe o caminho de novo.'];
    }
    if (! is_writable($caminho)) {
        return [null, 'A pasta dos anexos existe, mas o PHP não tem permissão para escrever nela.'];
    }

    $real = realpath($caminho) ?: $caminho;
    $raizReal = realpath($raiz) ?: $raiz;
    $dentroDe = static fn (string $pasta, string $base): bool => str_starts_with($pasta . DIRECTORY_SEPARATOR, $base . DIRECTORY_SEPARATOR);

    // Dentro da raiz só application/ é aceita: o resto (a raiz, assets/, install/...) o servidor web publica.
    if ($dentroDe($real, $raizReal) && ! $dentroDe($real, $raizReal . DIRECTORY_SEPARATOR . 'application')) {
        return [null, 'A pasta dos anexos fica dentro da pasta pública do sistema. Escolha uma pasta fora dela (por exemplo '
            . dirname($raizReal) . DIRECTORY_SEPARATOR . 'mapos-storage) ou deixe o campo vazio.'];
    }

    return [$real, null];
}

/**
 * Dependências do Composer (application/vendor). Sem elas nem o sistema nem o
 * comando de migrations sobem.
 */
function instaladorTemDependencias(?string $raiz = null): bool
{
    $raiz ??= instaladorRaiz();

    return is_file($raiz . '/application/vendor/autoload.php');
}

/**
 * Localiza o PHP de linha de comando (o do servidor web costuma ser FPM ou
 * módulo do Apache, que não roda o index.php do terminal).
 *
 * @param  string[]|null  $candidatos  Para teste; padrão: o binário atual (se for CLI) e os lugares comuns
 */
function instaladorLocalizarPhpCli(?array $candidatos = null): ?string
{
    if (! function_exists('proc_open')) {
        return null;
    }

    $candidatos ??= array_values(array_unique(array_filter([
        PHP_SAPI === 'cli' ? PHP_BINARY : null,
        PHP_BINDIR . DIRECTORY_SEPARATOR . 'php',
        dirname(PHP_BINARY) . DIRECTORY_SEPARATOR . 'php',
        '/usr/bin/php',
        '/usr/local/bin/php',
        'php',
    ])));

    foreach ($candidatos as $candidato) {
        $resultado = instaladorExecutar([$candidato, '-v']);

        if ($resultado !== null && $resultado[0] === 0 && str_contains($resultado[1], '(cli)')) {
            return $candidato;
        }
    }

    return null;
}

/**
 * Executa um programa sem shell (cada argumento vai direto ao processo) e junta
 * stdout e stderr. A saída passa por um arquivo temporário para não travar
 * quando o programa escreve muito.
 *
 * @param  list<string>  $comando
 * @return array{0: int, 1: string}|null [código de saída, saída]; null se o processo não pôde ser criado
 */
function instaladorExecutar(array $comando, ?string $pasta = null): ?array
{
    if (! function_exists('proc_open')) {
        return null;
    }

    $arquivo = tempnam(sys_get_temp_dir(), 'mapos-instalador-');
    if ($arquivo === false) {
        return null;
    }

    try {
        $processo = @proc_open($comando, [1 => ['file', $arquivo, 'w'], 2 => ['file', $arquivo, 'a']], $pipes, $pasta);
        if (! is_resource($processo)) {
            return null;
        }

        $codigo = proc_close($processo);

        return [$codigo, trim((string) file_get_contents($arquivo))];
    } finally {
        @unlink($arquivo);
    }
}

/**
 * Roda as migrations pendentes (php index.php tools migrate) na raiz do sistema.
 * O banco.sql entrega o esquema da 4.x; sem esta etapa a instalação nova ficava
 * sem o limite de tentativas de login, o 2FA e as demais tabelas da v5 (#2858).
 *
 * @return array{0: bool|null, 1: string} [true: aplicou; false: o comando falhou; null: não foi possível executar; saída]
 */
function instaladorMigrar(?string $raiz = null, ?string $php = null): array
{
    $raiz ??= instaladorRaiz();
    $php ??= instaladorLocalizarPhpCli();

    if ($php === null) {
        return [null, 'O PHP de linha de comando não foi encontrado ou o servidor bloqueia a execução de programas (proc_open).'];
    }

    $resultado = instaladorExecutar([$php, 'index.php', 'tools', 'migrate'], $raiz);
    if ($resultado === null) {
        return [null, 'Não foi possível executar o PHP de linha de comando.'];
    }

    [$codigo, $saida] = $resultado;

    // Só o fim da saída: o suficiente para entender o erro sem despejar o log inteiro na tela.
    $linhas = array_slice(preg_split('/\R/', $saida) ?: [], -8);
    $texto = implode("\n", $linhas);

    if ($codigo !== 0 && $texto === '') {
        $texto = "O comando terminou com o código {$codigo} e sem mensagem. Veja o arquivo mais recente em application/logs.";
    }

    return [$codigo === 0, $texto];
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
function instaladorMontarEnv(string $modelo, array $dados, string $chaveDeCriptografia, string $chaveJwt, ?string $armazenamento = null): string
{
    $env = strtr($modelo, [
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

    // STORAGE_PATH não tem marcador no modelo (quem copia o .env.example à mão o deixa vazio): troca a linha,
    // depois do strtr para que um caminho com "enter_..." não seja tratado como marcador.
    if ($armazenamento !== null && $armazenamento !== '') {
        $env = (string) preg_replace_callback(
            '/^STORAGE_PATH=.*$/m',
            static fn (): string => 'STORAGE_PATH="' . instaladorValorEnv($armazenamento) . '"',
            $env
        );
    }

    return $env;
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
