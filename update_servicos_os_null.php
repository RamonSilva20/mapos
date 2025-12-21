<?php
/**
 * Script para permitir servicos_id NULL na tabela servicos_os
 * Execute este arquivo uma vez através do navegador
 * 
 * URL: https://mapos.tecnicolitoral.com/update_servicos_os_null.php
 */

// Carregar variáveis de ambiente do arquivo .env
$env_file = __DIR__ . '/application/.env';
$db_config = [];

if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $db_config[trim($key)] = trim($value);
        }
    }
}

// Configurações do banco
$host = $db_config['DB_HOST'] ?? 'localhost';
$user = $db_config['DB_USER'] ?? 'root';
$pass = $db_config['DB_PASS'] ?? '';
$dbname = $db_config['DB_NAME'] ?? 'mapos';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Atualizar servicos_os - Permitir NULL</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>Atualizar Tabela servicos_os</h1>
    <p>Permitindo servicos_id NULL para serviços customizados</p>

<?php
echo "<h2>Alterando estrutura da tabela servicos_os</h2>";

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Erro de conexão: " . $conn->connect_error);
    }
    
    // Verificar se já permite NULL
    $check_query = "SHOW COLUMNS FROM `servicos_os` WHERE Field = 'servicos_id'";
    $result = $conn->query($check_query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $is_null = $row['Null'] === 'YES';
        
        if ($is_null) {
            echo "<div class='info'>ℹ A coluna 'servicos_id' já permite NULL. Nenhuma alteração necessária.</div>";
        } else {
            // Remover foreign key constraint
            echo "<p>Removendo foreign key constraint...</p>";
            $conn->query("SET FOREIGN_KEY_CHECKS=0");
            
            // Tentar remover constraint (pode não existir com esse nome exato)
            $constraints = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = 'servicos_os' AND COLUMN_NAME = 'servicos_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
            
            if ($constraints && $constraints->num_rows > 0) {
                $constraint = $constraints->fetch_assoc();
                $constraint_name = $constraint['CONSTRAINT_NAME'];
                $drop_fk = "ALTER TABLE `servicos_os` DROP FOREIGN KEY `$constraint_name`";
                
                if ($conn->query($drop_fk)) {
                    echo "<div class='success'>✓ Foreign key constraint removida: $constraint_name</div>";
                } else {
                    echo "<div class='error'>✗ Erro ao remover constraint: " . htmlspecialchars($conn->error) . "</div>";
                }
            }
            
            // Alterar coluna para permitir NULL
            $sql = "ALTER TABLE `servicos_os` MODIFY `servicos_id` INT(11) NULL";
            
            if ($conn->query($sql)) {
                echo "<div class='success'>✓ Coluna 'servicos_id' agora permite NULL!</div>";
                
                // Recriar foreign key constraint (agora permitindo NULL)
                $add_fk = "ALTER TABLE `servicos_os` 
                    ADD CONSTRAINT `fk_servicos_os_servicos1`
                    FOREIGN KEY (`servicos_id`)
                    REFERENCES `servicos` (`idServicos`)
                    ON DELETE NO ACTION
                    ON UPDATE NO ACTION";
                
                if ($conn->query($add_fk)) {
                    echo "<div class='success'>✓ Foreign key constraint recriada com sucesso!</div>";
                } else {
                    echo "<div class='info'>ℹ Aviso: Não foi possível recriar a foreign key (pode já existir): " . htmlspecialchars($conn->error) . "</div>";
                }
                
                $conn->query("SET FOREIGN_KEY_CHECKS=1");
            } else {
                echo "<div class='error'>✗ Erro ao alterar coluna: " . htmlspecialchars($conn->error) . "</div>";
            }
        }
    } else {
        echo "<div class='error'>✗ Coluna 'servicos_id' não encontrada na tabela servicos_os.</div>";
    }
    
    $conn->close();
    echo "<p><strong>Script executado com sucesso!</strong></p>";
    echo "<p><a href='javascript:history.back()'>← Voltar</a> <a href='index.php' class='btn'>Ir para o sistema</a></p>";
    
} catch (Exception $e) {
    echo "<div class='error'>✗ Erro: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

</body>
</html>


