<?php
/**
 * Script para adicionar permissões de Propostas às permissões existentes
 * Acesse via browser: http://seusite.com/updates/add_permissões_propostas.php
 * OU execute via linha de comando: php add_permissões_propostas.php
 */

// Definir BASEPATH para permitir acesso
define('BASEPATH', true);

// Caminho para o arquivo de configuração
$config_path = dirname(__FILE__) . '/../application/config/database.php';

if (!file_exists($config_path)) {
    die('Arquivo de configuração não encontrado: ' . $config_path);
}

require_once($config_path);

// Buscar configuração do banco
$db_config = $db['default'];

$host = $db_config['hostname'];
$user = $db_config['username'];
$pass = $db_config['password'];
$database = $db_config['database'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Adicionando Permissões de Propostas</h2>";
    echo "<pre>";
    
    // Buscar todas as permissões
    $stmt = $pdo->query("SELECT idPermissao, permissoes FROM permissoes WHERE permissoes IS NOT NULL AND permissoes != ''");
    $permissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $atualizadas = 0;
    
    foreach ($permissoes as $permissao) {
        $permissoes_array = unserialize($permissao['permissoes']);
        
        if (!is_array($permissoes_array)) {
            continue;
        }
        
        // Adicionar permissões de Propostas se não existirem
        $modificado = false;
        if (!isset($permissoes_array['vPropostas'])) {
            $permissoes_array['vPropostas'] = '1';
            $modificado = true;
        }
        if (!isset($permissoes_array['aPropostas'])) {
            $permissoes_array['aPropostas'] = '1';
            $modificado = true;
        }
        if (!isset($permissoes_array['ePropostas'])) {
            $permissoes_array['ePropostas'] = '1';
            $modificado = true;
        }
        if (!isset($permissoes_array['dPropostas'])) {
            $permissoes_array['dPropostas'] = '1';
            $modificado = true;
        }
        
        if ($modificado) {
            $permissoes_serialized = serialize($permissoes_array);
            $update_stmt = $pdo->prepare("UPDATE permissoes SET permissoes = ? WHERE idPermissao = ?");
            $update_stmt->execute([$permissoes_serialized, $permissao['idPermissao']]);
            $atualizadas++;
            echo "✓ Permissões atualizadas para ID: {$permissao['idPermissao']}\n";
        }
    }
    
    echo "\n========================================\n";
    echo "Resultado:\n";
    echo "✓ Permissões atualizadas: $atualizadas\n";
    echo "========================================\n";
    
    echo "</pre>";
    echo "<p><strong>Permissões adicionadas com sucesso!</strong></p>";
    echo "<p><a href='../index.php'>Voltar para o sistema</a></p>";
    
} catch (PDOException $e) {
    echo "<h3>Erro:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}

