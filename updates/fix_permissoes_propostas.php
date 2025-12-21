<?php
// Script PHP para atualizar permissões
// Execute: php fix_permissoes_propostas.php

// Permitir acesso direto
define('BASEPATH', true);
define('ENVIRONMENT', 'production');

$config_file = '../application/config/database.php';
include($config_file);

$db_config = $db['default'];
$host = $db_config['hostname'];
$user = $db_config['username'];
$pass = $db_config['password'];
$database = $db_config['database'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Atualizando permissões de Propostas...\n\n";
    
    $stmt = $pdo->query("SELECT idPermissao, nome, permissoes FROM permissoes WHERE permissoes IS NOT NULL AND permissoes != ''");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $atualizados = 0;
    
    foreach ($registros as $registro) {
        echo "Processando: {$registro['nome']} (ID: {$registro['idPermissao']})... ";
        
        $perms = @unserialize($registro['permissoes']);
        
        if (!is_array($perms)) {
            echo "ERRO: Não é um array válido\n";
            continue;
        }
        
        // Verificar se já tem
        if (isset($perms['vPropostas'])) {
            echo "JÁ TEM\n";
            continue;
        }
        
        // Adicionar permissões
        $perms['vPropostas'] = '1';
        $perms['aPropostas'] = '1';
        $perms['ePropostas'] = '1';
        $perms['dPropostas'] = '1';
        
        // Serializar novamente
        $perms_serialized = serialize($perms);
        
        // Atualizar
        $update = $pdo->prepare("UPDATE permissoes SET permissoes = ? WHERE idPermissao = ?");
        $update->execute([$perms_serialized, $registro['idPermissao']]);
        
        echo "ATUALIZADO ✓\n";
        $atualizados++;
    }
    
    echo "\n========================================\n";
    echo "Total de perfis atualizados: $atualizados\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    exit(1);
}

