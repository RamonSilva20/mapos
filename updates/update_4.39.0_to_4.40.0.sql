ALTER TABLE `os` ADD `tipo_desconto` VARCHAR(8) NULL DEFAULT NULL;
ALTER TABLE `vendas` ADD `tipo_desconto` VARCHAR(8) NULL DEFAULT NULL;
ALTER TABLE `lancamentos` ADD `tipo_desconto` VARCHAR(8) NULL DEFAULT NULL;
ALTER TABLE `clientes` ADD `asaas_id` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `usuarios` DROP `asaas_id`;
INSERT INTO `configuracoes` (`idConfig`, `config`, `valor`) VALUES (15, 'control_2vias', 0);
