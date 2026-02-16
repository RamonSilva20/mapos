<?php
// Script installation module propostas commercial docker friendly
// Usage: php install_modulos_docker.php

echo "===================================================\n";
echo "   INSTALADOR DE MÓDULOS EXTRAS (DOCKER COMPATIBLE)   \n";
echo "===================================================\n\n";

// 1. Load .env config
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    // try application env
    $envFile = __DIR__ . '/application/.env';
}

if (!file_exists($envFile)) {
    die("Erro: Arquivo .env não encontrado em " . __DIR__ . "\n");
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $env[trim($key)] = trim($value, " \"'");
    }
}

// 2. Connect to Database
$host = $env['DB_HOSTNAME'] ?? 'mysql';
$user = $env['DB_USERNAME'] ?? 'mapos';
$pass = $env['DB_PASSWORD'] ?? 'mapos';
$dbname = $env['DB_DATABASE'] ?? 'mapos';
$port = 3306;

echo "Conectando ao banco de dados...\n";
echo "Host: $host\nUser: $user\nDatabase: $dbname\n\n";

$mysqli = new mysqli($host, $user, $pass, $dbname, $port);

if ($mysqli->connect_error) {
    die("Erro de conexão (" . $mysqli->connect_errno . "): " . $mysqli->connect_error . "\n");
}

echo "Conexão estabelecida com sucesso!\n\n";

// 3. Helper to run SQL files
function runSqlFile($mysqli, $filepath) {
    echo "Executando: " . basename($filepath) . "... ";
    if (!file_exists($filepath)) {
        echo "[ARQUIVO NÃO ENCONTRADO]\n";
        return false;
    }

    $sql = file_get_contents($filepath);
    $queries = explode(';', $sql);
    $success = true;

    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;

        try {
            if (!$mysqli->query($query)) {
                // Ignore specific errors like "Duplicate column" or "Table exists"
                if (strpos($mysqli->error, 'Duplicate column') !== false || 
                    strpos($mysqli->error, 'already exists') !== false) {
                    continue; // Skip
                }
                echo "\nErro na query: " . substr($query, 0, 100) . "...\n";
                echo "MySQL Error: " . $mysqli->error . "\n";
                $success = false;
            }
        } catch (Exception $e) {
             if (strpos($e->getMessage(), 'Duplicate column') !== false || 
                    strpos($e->getMessage(), 'already exists') !== false) {
                    continue; // Skip
             }
             echo "\nException: " . $e->getMessage() . "\n";
             $success = false;
        }
    }

    if ($success) echo "[OK]\n";
    else echo "[COM ERROS]\n";
    return $success;
}

// 4. Run SQL updates
$updates = [
    'updates/create_propostas.sql',
    'updates/add_estoque_consumido_propostas.sql',
    'updates/add_lancamento_propostas.sql',
    'updates/add_campos_condicoes_propostas.sql',
    'updates/update_propostas_cliente_nome.sql'
];

foreach ($updates as $file) {
    runSqlFile($mysqli, __DIR__ . '/' . $file);
}

// 5. Update Permissions logic
echo "Atualizando permissões de usuários... ";

$sql = "SELECT idPermissao, permissoes FROM permissoes";
$result = $mysqli->query($sql);
$count = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $perms = unserialize($row['permissoes']);
        $changed = false;

        // Add Propostas permissions
        $keys = ['vProposta', 'aProposta', 'eProposta', 'dProposta'];
        foreach ($keys as $k) {
            if (!isset($perms[$k])) {
                $perms[$k] = '1';
                $changed = true;
            }
        }
        
        // Also add plural checks just in case
        $keys_plural = ['vPropostas', 'aPropostas', 'ePropostas', 'dPropostas'];
        foreach ($keys_plural as $k) {
            if (!isset($perms[$k])) {
                $perms[$k] = '1';
                $changed = true;
            }
        }

        if ($changed) {
            $newPerms = serialize($perms);
            $update = $mysqli->prepare("UPDATE permissoes SET permissoes = ? WHERE idPermissao = ?");
            $update->bind_param('si', $newPerms, $row['idPermissao']);
            $update->execute();
            $count++;
        }
    }
    echo "[OK] - $count registros atualizados.\n";
} else {
    echo "[ERRO AO LER PERMISSÕES] - " . $mysqli->error . "\n";
}

$mysqli->close();

echo "\nInstalação do módulo Propostas concluída!\n";
?>
