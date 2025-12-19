-- ===========================================
-- Permitir servicos_id NULL para serviços customizados
-- Data: 2025-01-XX
-- ===========================================

-- Remover foreign key constraint
ALTER TABLE `servicos_os` DROP FOREIGN KEY `fk_servicos_os_servicos1`;

-- Alterar coluna para permitir NULL
ALTER TABLE `servicos_os` MODIFY `servicos_id` INT(11) NULL;

-- Recriar foreign key constraint (agora permitindo NULL)
ALTER TABLE `servicos_os` 
ADD CONSTRAINT `fk_servicos_os_servicos1`
FOREIGN KEY (`servicos_id`)
REFERENCES `servicos` (`idServicos`)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

