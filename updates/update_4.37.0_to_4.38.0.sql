ALTER TABLE `os` ADD `desconto` DECIMAL(10, 2) NULL DEFAULT 0;
ALTER TABLE `os` ADD `valor_desconto` DECIMAL(10, 2) NULL DEFAULT 0;
ALTER TABLE `vendas` CHANGE `desconto` `desconto` DECIMAL(10,2) NULL DEFAULT '0.00';
ALTER TABLE `vendas` ADD `valor_desconto` DECIMAL(10, 2) NULL DEFAULT 0;
ALTER TABLE `lancamentos` ADD `desconto` DECIMAL(10, 2) NULL DEFAULT 0;
ALTER TABLE `lancamentos` ADD `valor_desconto` DECIMAL(10, 2) NULL DEFAULT 0;
INSERT INTO `configuracoes` (`idConfig`, `config`, `valor`) VALUES (14, 'email_automatico', 1);
