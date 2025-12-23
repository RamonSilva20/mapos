-- ===========================================
-- Adicionar campo estoque_consumido em produtos_proposta
-- Data: 22/12/2025
-- ===========================================

-- Adicionar campo estoque_consumido se não existir
ALTER TABLE `produtos_proposta` 
ADD COLUMN IF NOT EXISTS `estoque_consumido` TINYINT(1) DEFAULT 0 COMMENT 'Indica se o estoque já foi consumido (0=não, 1=sim)' AFTER `subtotal`;

-- Atualizar status padrão na tabela propostas
ALTER TABLE `propostas` 
MODIFY COLUMN `status` VARCHAR(20) DEFAULT 'Rascunho' COMMENT 'Em aberto, Rascunho, Pendente, Aguardando, Aprovada, Não aprovada, Concluído, Modelo';

