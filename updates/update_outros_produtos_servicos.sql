-- ===========================================
-- Criar tabela para Outros Produtos/Serviços da OS
-- Data: 2025-01-XX
-- ===========================================

CREATE TABLE IF NOT EXISTS `outros_produtos_servicos_os` (
  `idOutros` INT(11) NOT NULL AUTO_INCREMENT,
  `os_id` INT(11) NOT NULL,
  `descricao` TEXT NOT NULL,
  `preco` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `ordem` INT(11) NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idOutros`),
  INDEX `fk_outros_os_idx` (`os_id` ASC),
  CONSTRAINT `fk_outros_os`
    FOREIGN KEY (`os_id`)
    REFERENCES `os` (`idOs`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

