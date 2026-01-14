-- ===========================================
-- Adicionar campo lancamento em propostas
-- Data: 22/12/2025
-- ===========================================

-- Adicionar campo lancamento se não existir
SET @dbname = DATABASE();
SET @tablename = 'propostas';
SET @columnname = 'lancamento';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT(11) NULL COMMENT ''ID do primeiro lançamento financeiro vinculado'' AFTER `valor_total`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Adicionar índice para melhor performance
SET @preparedStatement2 = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (index_name = 'idx_propostas_lancamento')
  ) > 0,
  'SELECT 1',
  CONCAT('CREATE INDEX idx_propostas_lancamento ON ', @tablename, ' (', @columnname, ')')
));
PREPARE createIndexIfNotExists FROM @preparedStatement2;
EXECUTE createIndexIfNotExists;
DEALLOCATE PREPARE createIndexIfNotExists;
