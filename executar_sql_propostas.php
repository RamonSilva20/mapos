<?php
// Script temporário para executar SQL de atualização
// Execute: php executar_sql_propostas.php

// Ler configurações do banco de dados
$db_config = [];
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $db_config[trim($key)] = trim($value);
        }
    }
}

$host = $db_config['DB_HOSTNAME'] ?? 'localhost';
$user = $db_config['DB_USERNAME'] ?? 'root';
$pass = $db_config['DB_PASSWORD'] ?? '';
$database = $db_config['DB_DATABASE'] ?? 'mapos';

$mysqli = new mysqli($host, $user, $pass, $database);

if ($mysqli->connect_error) {
    die("Erro de conexão: " . $mysqli->connect_error);
}

echo "Conectado ao banco de dados: {$database}\n\n";

// Ler o arquivo SQL
$sql = file_get_contents('updates/add_estoque_consumido_propostas.sql');

// Executar cada comando separadamente
$commands = explode(';', $sql);

foreach ($commands as $command) {
    $command = trim($command);
    if (empty($command) || strpos($command, '--') === 0) {
        continue;
    }
    
    // Executar comandos preparados
    if (strpos($command, 'PREPARE') !== false || strpos($command, 'EXECUTE') !== false || strpos($command, 'DEALLOCATE') !== false) {
        if ($mysqli->multi_query($command)) {
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->next_result());
        }
    } else {
        if ($mysqli->query($command)) {
            echo "✓ Comando executado com sucesso\n";
        } else {
            // Ignorar erro se coluna já existe
            if (strpos($mysqli->error, 'Duplicate column name') === false) {
                echo "✗ Erro: " . $mysqli->error . "\n";
                echo "Comando: " . substr($command, 0, 100) . "...\n";
            } else {
                echo "ℹ Coluna já existe, ignorando...\n";
            }
        }
    }
}

echo "\nScript executado com sucesso!\n";
$mysqli->close();

