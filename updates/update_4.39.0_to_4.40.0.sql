ALTER TABLE `os` ADD `tipo_desconto` VARCHAR(8) NULL DEFAULT NULL;
ALTER TABLE `vendas` ADD `tipo_desconto` VARCHAR(8) NULL DEFAULT NULL;
ALTER TABLE `lancamentos` ADD `tipo_desconto` VARCHAR(8) NULL DEFAULT NULL;
ALTER TABLE `clientes` ADD `asaas_id` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `usuarios` DROP `asaas_id`;
INSERT INTO `configuracoes` (`idConfig`, `config`, `valor`) VALUES (15, 'control_2vias', 0);
ALTER TABLE `configuracoes` CHANGE `config` `config` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
UPDATE `configuracoes` SET `valor` = 'Prezado(a), {CLIENTE_NOME} a OS de nº {NUMERO_OS} teve o status alterado para: {STATUS_OS} segue a descrição {DESCRI_PRODUTOS} com valor total de {VALOR_OS}! Para mais informações entre em contato conosco. Atenciosamente, {EMITENTE} {TELEFONE_EMITENTE}.' WHERE `configuracoes`.`idConfig` = 7;
