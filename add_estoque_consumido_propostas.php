<?php
/**
 * Script para adicionar campo estoque_consumido na tabela produtos_proposta
 * Execute este arquivo uma vez através do navegador
 * 
 * URL: http://localhost/mapos/add_estoque_consumido_propostas.php
 */

// Carregar variáveis de ambiente do arquivo .env
$env_file = __DIR__ . '/application/.env';
$db_config = [];

if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Remover aspas se existirem
            $value = trim($value, '"\'');
            $db_config[$key] = $value;
        }
    }
}

// Valores padrão ou do .env
$host = $db_config['DB_HOSTNAME'] ?? 'localhost';
$user = $db_config['DB_USERNAME'] ?? 'root';
$pass = $db_config['DB_PASSWORD'] ?? '';
$dbname = $db_config['DB_DATABASE'] ?? 'mapos';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Adicionar Campo Estoque Consumido</title>";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;max-width:800px;margin:0 auto;background:#f5f5f5;} .container{background:white;padding:30px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);} .success{color:#28a745;font-weight:bold;padding:10px;background:#d4edda;border:1px solid #c3e6cb;border-radius:4px;margin:10px 0;} .error{color:#dc3545;font-weight:bold;padding:10px;background:#f8d7da;border:1px solid #f5c6cb;border-radius:4px;margin:10px 0;} .info{color:#004085;font-weight:bold;padding:10px;background:#d1ecf1;border:1px solid #bee5eb;border-radius:4px;margin:10px 0;} a{color:#0066cc;text-decoration:none;margin-right:15px;} a:hover{text-decoration:underline;} .btn{display:inline-block;padding:8px 16px;background:#007bff;color:white;border-radius:4px;margin-top:15px;} .btn:hover{background:#0056b3;}</style></head><body>";
echo "<div class='container'>";
echo "<h2>Adicionando campo estoque_consumido e atualizando status</h2>";

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Erro de conexão: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
    // 1. Verificar e adicionar campo estoque_consumido
    $check_query = "SHOW COLUMNS FROM `produtos_proposta` LIKE 'estoque_consumido'";
    $result = $conn->query($check_query);
    
    if ($result && $result->num_rows == 0) {
        $sql = "ALTER TABLE `produtos_proposta` ADD COLUMN `estoque_consumido` TINYINT(1) DEFAULT 0 COMMENT 'Indica se o estoque já foi consumido (0=não, 1=sim)' AFTER `subtotal`";
        
        if ($conn->query($sql)) {
            echo "<div class='success'>✓ Campo 'estoque_consumido' adicionado com sucesso na tabela produtos_proposta!</div>";
        } else {
            echo "<div class='error'>✗ Erro ao adicionar campo: " . htmlspecialchars($conn->error) . "</div>";
        }
    } else {
        echo "<div class='info'>ℹ Campo 'estoque_consumido' já existe na tabela produtos_proposta.</div>";
    }
    
    // 2. Atualizar comentário da coluna status na tabela propostas
    $sql2 = "ALTER TABLE `propostas` MODIFY COLUMN `status` VARCHAR(20) DEFAULT 'Em aberto' COMMENT 'Em aberto, Rascunho, Pendente, Aguardando, Aprovada, Não aprovada, Concluído, Modelo'";
    
    if ($conn->query($sql2)) {
        echo "<div class='success'>✓ Comentário da coluna 'status' atualizado com sucesso!</div>";
    } else {
        // Ignorar erro se já estiver atualizado
        if (strpos($conn->error, 'Duplicate') === false) {
            echo "<div class='info'>ℹ " . htmlspecialchars($conn->error) . "</div>";
        }
    }
    
    $conn->close();
    echo "<p><strong>Script executado com sucesso!</strong></p>";
    echo "<p><a href='javascript:history.back()'>← Voltar</a> <a href='index.php' class='btn'>Ir para o sistema</a></p>";
    
} catch (Exception $e) {
    echo "<div class='error'>✗ Erro: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<p>Verifique as configurações do banco de dados no arquivo <code>application/.env</code></p>";
    echo "<p>Configurações tentadas:</p>";
    echo "<ul>";
    echo "<li>Host: " . htmlspecialchars($host) . "</li>";
    echo "<li>Usuário: " . htmlspecialchars($user) . "</li>";
    echo "<li>Banco: " . htmlspecialchars($dbname) . "</li>";
    echo "</ul>";
}

echo "</div></body></html>";

