-- ===========================================
-- FASE 5: Pagamento Parcial e Integração
-- Data: 17/12/2025
-- ===========================================

-- Adicionar campo valor_pago na tabela lancamentos
ALTER TABLE `lancamentos` ADD `valor_pago` DECIMAL(10, 2) NULL DEFAULT 0 AFTER `valor_desconto`;

-- Adicionar campo status_pagamento (pendente, parcial, pago)
ALTER TABLE `lancamentos` ADD `status_pagamento` VARCHAR(20) NULL DEFAULT 'pendente' AFTER `valor_pago`;

-- Criar tabela para histórico de pagamentos parciais
CREATE TABLE IF NOT EXISTS `pagamentos_parciais` (
  `idPagamento` INT(11) NOT NULL AUTO_INCREMENT,
  `lancamentos_id` INT(11) NOT NULL,
  `valor` DECIMAL(10, 2) NOT NULL,
  `data_pagamento` DATE NOT NULL,
  `forma_pgto` VARCHAR(50) NULL,
  `observacao` TEXT NULL,
  `usuarios_id` INT(11) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idPagamento`),
  INDEX `fk_pagamentos_lancamentos_idx` (`lancamentos_id` ASC),
  INDEX `fk_pagamentos_usuarios_idx` (`usuarios_id` ASC),
  CONSTRAINT `fk_pagamentos_lancamentos`
    FOREIGN KEY (`lancamentos_id`)
    REFERENCES `lancamentos` (`idLancamentos`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pagamentos_usuarios`
    FOREIGN KEY (`usuarios_id`)
    REFERENCES `usuarios` (`idUsuarios`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Adicionar campos de pagamento na tabela OS
ALTER TABLE `os` ADD `forma_pgto` VARCHAR(50) NULL DEFAULT NULL AFTER `faturado`;
ALTER TABLE `os` ADD `parcelas` INT(11) NULL DEFAULT 1 AFTER `forma_pgto`;
ALTER TABLE `os` ADD `valor_entrada` DECIMAL(10, 2) NULL DEFAULT 0 AFTER `parcelas`;

-- Adicionar campos de pagamento na tabela vendas (se ainda não existir)
ALTER TABLE `vendas` ADD `forma_pgto` VARCHAR(50) NULL DEFAULT NULL AFTER `faturado`;
ALTER TABLE `vendas` ADD `parcelas` INT(11) NULL DEFAULT 1 AFTER `forma_pgto`;
ALTER TABLE `vendas` ADD `valor_entrada` DECIMAL(10, 2) NULL DEFAULT 0 AFTER `parcelas`;

-- Atualizar lancamentos existentes para terem status_pagamento correto
UPDATE `lancamentos` SET `status_pagamento` = 'pago' WHERE `baixado` = 1;
UPDATE `lancamentos` SET `status_pagamento` = 'pendente' WHERE `baixado` = 0;

-- Atualizar valor_pago para lançamentos já pagos
UPDATE `lancamentos` SET `valor_pago` = COALESCE(`valor_desconto`, `valor`) WHERE `baixado` = 1;




