<?php
/**
 * Script para criar tabelas de Propostas Comerciais
 * Acesse via browser: http://seusite.com/updates/install_propostas.php
 */

// Permite acesso direto ao script
define('BASEPATH', true);

// Caminho para o arquivo de configuração do CodeIgniter
$config_path = dirname(__FILE__) . '/../application/config/database.php';

if (!file_exists($config_path)) {
    die('Arquivo de configuração não encontrado: ' . $config_path);
}

// Incluir apenas o necessário para conexão
if (file_exists($config_path)) {
    include($config_path);
}

// Buscar configuração do banco
$db_config = $db['default'];

$host = $db_config['hostname'];
$user = $db_config['username'];
$pass = $db_config['password'];
$database = $db_config['database'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Instalando Módulo de Propostas Comerciais</h2>";
    echo "<pre>";
    
    // Ler arquivo SQL
    $sql_file = dirname(__FILE__) . '/create_propostas.sql';
    if (!file_exists($sql_file)) {
        die("Arquivo SQL não encontrado: $sql_file");
    }
    
    $sql = file_get_contents($sql_file);
    
    // Dividir em statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt) && !preg_match('/^\/\*/', $stmt);
        }
    );
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (empty(trim($statement))) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $success_count++;
            echo "✓ Executado com sucesso\n";
        } catch (PDOException $e) {
            // Ignorar se a tabela já existe
            if (strpos($e->getMessage(), 'already exists') !== false || strpos($e->getMessage(), 'Duplicate') !== false) {
                echo "⚠ Tabela/índice já existe, ignorando...\n";
                $success_count++;
            } else {
                echo "✗ Erro: " . $e->getMessage() . "\n";
                $error_count++;
            }
        }
    }
    
    echo "\n========================================\n";
    echo "Resultado:\n";
    echo "✓ Sucessos: $success_count\n";
    echo "✗ Erros: $error_count\n";
    echo "========================================\n";
    
    echo "</pre>";
    
    // Adicionar permissões
    echo "<h3>Adicionando Permissões...</h3>";
    echo "<pre>";
    $permissoes_file = dirname(__FILE__) . '/add_permissões_propostas.sql';
    if (file_exists($permissoes_file)) {
        $permissoes_sql = file_get_contents($permissoes_file);
        $permissoes_statements = array_filter(
            array_map('trim', explode(';', $permissoes_sql)),
            function($stmt) {
                return !empty($stmt) && !preg_match('/^--/', $stmt) && !preg_match('/^\/\*/', $stmt);
            }
        );
        
        foreach ($permissoes_statements as $statement) {
            if (empty(trim($statement))) continue;
            try {
                $pdo->exec($statement);
                echo "✓ Permissões adicionadas\n";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    echo "⚠ Permissões já existem\n";
                } else {
                    echo "✗ Erro: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    echo "</pre>";
    
    echo "<p><strong>Instalação concluída!</strong></p>";
    echo "<p><strong>Importante:</strong> Configure as permissões no sistema para que os usuários possam acessar o módulo de Propostas.</p>";
    echo "<p><a href='../index.php'>Voltar para o sistema</a></p>";
    
} catch (PDOException $e) {
    echo "<h3>Erro de conexão:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}

