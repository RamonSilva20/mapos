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

if (! is_file($settings['database_file'])) {
    exit(json_encode(['success' => false, 'message' => 'O arquivo ../banco.sql não foi encontrado na pasta de instalação!']));
}

// Uma instalação por vez: o arquivo é criado só se não existir (modo x), então a segunda requisição simultânea é recusada.
$arquivoDeTrava = __DIR__ . DIRECTORY_SEPARATOR . 'instalacao.lock';
$trava = @fopen($arquivoDeTrava, 'x');
if ($trava === false) {
    exit(json_encode(['success' => false, 'message' => 'Já existe uma instalação em andamento. Aguarde alguns instantes.']));
}

$terminar = static function (bool $sucesso, string $mensagem) use ($trava, $arquivoDeTrava): never {
    fclose($trava);
    @unlink($arquivoDeTrava);
    exit(json_encode(['success' => $sucesso, 'message' => $mensagem]));
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

$sql = instaladorMontarSql(
    file_get_contents($settings['database_file']),
    $dados,
    date('Y-m-d H:i:s'),
    static fn (string $valor): string => $mysqli->real_escape_string($valor)
);

//create tables in datbase
$mysqli->multi_query($sql);
do {
} while (mysqli_more_results($mysqli) && mysqli_next_result($mysqli));
$mysqli->close();

$modelo = file_get_contents(dirname(__DIR__) . '/application/.env.example');
$env = instaladorMontarEnv($modelo, $dados, bin2hex(random_bytes(16)), base64_encode(random_bytes(32)));

if (! file_put_contents(dirname(__DIR__) . '/application/.env', $env)) {
    $terminar(false, 'Erro ao criar arquivo env.');
}

// Trava definitiva e regra do servidor web que deixa só a página de aviso acessível.
@file_put_contents(__DIR__ . '/instalado.lock', date('c'));
@file_put_contents(__DIR__ . '/.htaccess', instaladorHtaccessInstalado());

$terminar(true, 'Instalação bem sucedida.');
