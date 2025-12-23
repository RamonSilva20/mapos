-- ===========================================
-- Adicionar campo estoque_consumido em produtos_proposta
-- Data: 22/12/2025
-- ===========================================

-- Verificar e adicionar campo estoque_consumido se não existir
SET @dbname = DATABASE();
SET @tablename = 'produtos_proposta';
SET @columnname = 'estoque_consumido';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TINYINT(1) DEFAULT 0 COMMENT ''Indica se o estoque já foi consumido (0=não, 1=sim)'' AFTER `subtotal`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Atualizar status padrão na tabela propostas
ALTER TABLE `propostas` 
MODIFY COLUMN `status` VARCHAR(20) DEFAULT 'Em aberto' COMMENT 'Em aberto, Rascunho, Pendente, Aguardando, Aprovada, Não aprovada, Concluído, Modelo';

