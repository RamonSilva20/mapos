-- ===========================================
-- Estrutura de Parcelas para OS
-- Data: 17/12/2025
-- ===========================================

-- Criar tabela para armazenar parcelas da OS
CREATE TABLE IF NOT EXISTS `parcelas_os` (
  `idParcela` INT(11) NOT NULL AUTO_INCREMENT,
  `os_id` INT(11) NOT NULL,
  `numero` INT(11) NOT NULL COMMENT 'Número da parcela (1, 2, 3...)',
  `dias` INT(11) NOT NULL COMMENT 'Dias até o vencimento (30, 60, 90...)',
  `valor` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `observacao` TEXT NULL COMMENT 'Observação da parcela',
  `data_vencimento` DATE NULL COMMENT 'Data de vencimento calculada',
  `forma_pgto` VARCHAR(50) NULL COMMENT 'Forma de pagamento',
  `detalhes` VARCHAR(255) NULL COMMENT 'Banco, gateway, conta bancária',
  `status` VARCHAR(20) DEFAULT 'pendente' COMMENT 'pendente, pago, cancelado',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idParcela`),
  INDEX `fk_parcelas_os_idx` (`os_id` ASC),
  CONSTRAINT `fk_parcelas_os`
    FOREIGN KEY (`os_id`)
    REFERENCES `os` (`idOs`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Adicionar índice para melhor performance
CREATE INDEX `idx_parcelas_os_status` ON `parcelas_os` (`os_id`, `status`);


