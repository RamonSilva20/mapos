<?php

require __DIR__ . '/instalador.php';

// Antes de qualquer outra coisa: com o sistema instalado, nem lê o POST nem abre conexão com o banco.
if (instaladorInstalado()) {
    instaladorBloquear(true);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

ini_set('max_execution_time', 300); //300 seconds
header('Content-Type: application/json; charset=utf-8');

$settings_file = __DIR__ . DIRECTORY_SEPARATOR . 'settings.json';

if (! file_exists($settings_file)) {
    exit(json_encode(['success' => false, 'message' => 'Arquivo de configuração não encontrado!']));
}

$settings = json_decode(file_get_contents($settings_file), true);

[$dados, $erro] = instaladorValidar($_POST);
if ($erro !== null) {
    exit(json_encode(['success' => false, 'message' => $erro]));
}

if (! instaladorTemDependencias()) {
    exit(json_encode(['success' => false, 'message' => 'As dependências do sistema não estão instaladas (pasta application/vendor). Use a versão completa da release ou, a partir do código-fonte, execute "composer install --no-dev" na pasta do projeto.']));
}

// Pasta dos anexos (opcional): criada e conferida antes de mexer no banco.
[$armazenamento, $erro] = instaladorPrepararArmazenamento($dados['storage_path']);
if ($erro !== null) {
    exit(json_encode(['success' => false, 'message' => $erro]));
}

if (! is_file($settings['database_file'])) {
    exit(json_encode(['success' => false, 'message' => 'O arquivo ../banco.sql não foi encontrado na pasta de instalação!']));
}

// Uma instalação por vez: lock exclusivo no arquivo, então a segunda requisição simultânea é recusada.
// O sistema operacional solta o lock se o PHP morrer no meio, então um arquivo esquecido nunca trava novas tentativas.
$arquivoDeTrava = __DIR__ . DIRECTORY_SEPARATOR . 'instalacao.lock';
$trava = @fopen($arquivoDeTrava, 'c');
if ($trava === false || ! flock($trava, LOCK_EX | LOCK_NB)) {
    exit(json_encode(['success' => false, 'message' => 'Já existe uma instalação em andamento. Aguarde alguns instantes.']));
}

$terminar = static function (bool $sucesso, string $mensagem, ?string $aviso = null) use ($trava, $arquivoDeTrava): never {
    @unlink($arquivoDeTrava);
    flock($trava, LOCK_UN);
    fclose($trava);
    exit(json_encode(['success' => $sucesso, 'message' => $mensagem, 'aviso' => $aviso]));
};

try {
    $mysqli = @new mysqli($dados['host'], $dados['dbuser'], $dados['dbpassword'], $dados['dbname']);

    if (mysqli_connect_errno()) {
        $terminar(false, $mysqli->connect_error ?: 'Não foi possível conectar ao banco de dados.');
    }
} catch (Throwable $e) {
    $terminar(false, $e->getMessage());
}

// Confere de novo já com a trava: outra instalação pode ter terminado enquanto esta validava.
if (instaladorInstalado()) {
    $mysqli->close();
    $terminar(false, 'Parece que este aplicativo já está instalado! Você não pode reinstalá-lo novamente.');
}

try {
    $sql = instaladorMontarSql(
        file_get_contents($settings['database_file']),
        $dados,
        date('Y-m-d H:i:s'),
        static fn (string $valor): string => $mysqli->real_escape_string($valor)
    );

    // Erros do mysqli (PHP 8.1+ lança exceção) viram resposta JSON em vez de deixar a instalação pela metade.
    //create tables in datbase
    $mysqli->multi_query($sql);
    do {
    } while (mysqli_more_results($mysqli) && mysqli_next_result($mysqli));
    $mysqli->close();

    $modelo = file_get_contents(dirname(__DIR__) . '/application/.env.example');
    $env = instaladorMontarEnv($modelo, $dados, bin2hex(random_bytes(16)), base64_encode(random_bytes(32)), $armazenamento);

    $arquivoEnv = dirname(__DIR__) . '/application/.env';
    if (! file_put_contents($arquivoEnv, $env)) {
        $terminar(false, 'Erro ao criar arquivo env.');
    }
    // Guarda a senha do banco e as chaves: só o dono do arquivo lê (se o sistema de arquivos permitir).
    @chmod($arquivoEnv, 0600);

    // O banco.sql traz o esquema da 4.x: as migrations da v5 (limite de login, 2FA, histórico da OS, API...) rodam agora (#2858).
    [$migrou, $saidaMigracao] = instaladorMigrar();
    $aviso = null;
    if ($migrou === null) {
        $aviso = 'O instalador não conseguiu rodar as atualizações do banco de dados. ' . $saidaMigracao . ' '
            . 'Depois de entrar, abra Configurações, aba Sistema, e use "Atualizar banco de dados", ou rode php index.php tools migrate na pasta do sistema. '
            . 'Até lá o limite de tentativas de login e o 2FA ficam desligados.';
    } elseif ($migrou === false) {
        $aviso = 'O banco foi criado, mas as atualizações do banco de dados falharam' . ($saidaMigracao !== '' ? ': ' . $saidaMigracao : '') . '. '
            . 'Corrija o erro e rode php index.php tools migrate na pasta do sistema, ou use "Atualizar banco de dados" em Configurações, aba Sistema. '
            . 'Até lá o limite de tentativas de login e o 2FA ficam desligados.';
    }

    // Trava definitiva e regra do servidor web que deixa só a página de aviso acessível.
    @file_put_contents(__DIR__ . '/instalado.lock', date('c'));
    @file_put_contents(__DIR__ . '/.htaccess', instaladorHtaccessInstalado());

    $terminar(true, 'Instalação bem sucedida.', $aviso);
} catch (Throwable $e) {
    $terminar(false, 'A instalação falhou: ' . $e->getMessage());
}
