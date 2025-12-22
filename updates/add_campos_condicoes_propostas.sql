-- ===========================================
-- Adicionar campos de Condições Comerciais e Condições Gerais
-- Data: 22/12/2025
-- ===========================================

-- Adicionar campos para condições comerciais
ALTER TABLE `propostas` 
ADD COLUMN `tipo_cond_comerc` VARCHAR(1) DEFAULT 'N' COMMENT 'N=Nenhuma, P=Parcelas, T=Texto livre' AFTER `valor_total`,
ADD COLUMN `cond_comerc_texto` TEXT NULL COMMENT 'Texto livre das condições comerciais' AFTER `tipo_cond_comerc`;

-- Adicionar campos para condições gerais
ALTER TABLE `propostas`
ADD COLUMN `validade_dias` INT(11) NULL COMMENT 'Validade da proposta em dias' AFTER `cond_comerc_texto`,
ADD COLUMN `prazo_entrega` VARCHAR(255) NULL COMMENT 'Prazo de entrega (texto livre)' AFTER `validade_dias`;

