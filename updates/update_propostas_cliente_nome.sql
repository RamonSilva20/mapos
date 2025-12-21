-- ===========================================
-- Permitir propostas com apenas nome do cliente
-- Data: 22/12/2025
-- ===========================================

-- Adicionar campo cliente_nome
ALTER TABLE `propostas` 
ADD COLUMN `cliente_nome` VARCHAR(255) NULL COMMENT 'Nome do cliente quando não cadastrado' AFTER `clientes_id`;

-- Remover constraint de foreign key
ALTER TABLE `propostas` 
DROP FOREIGN KEY `fk_propostas_clientes`;

-- Tornar clientes_id nullable
ALTER TABLE `propostas` 
MODIFY COLUMN `clientes_id` INT(11) NULL;

-- Recriar constraint de foreign key (agora permitindo NULL)
ALTER TABLE `propostas` 
ADD CONSTRAINT `fk_propostas_clientes`
FOREIGN KEY (`clientes_id`)
REFERENCES `clientes` (`idClientes`)
ON DELETE SET NULL
ON UPDATE NO ACTION;

