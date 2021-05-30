ALTER TABLE `os` ADD `desconto` DECIMAL(10, 2) NULL DEFAULT 0;
ALTER TABLE `os` ADD `valor_desconto` DECIMAL(10, 2) NULL;
ALTER TABLE `vendas` ADD `desconto` DECIMAL(10, 2) NULL DEFAULT 0;
ALTER TABLE `vendas` ADD `valor_desconto` DECIMAL(10, 2) NULL;
ALTER TABLE `lancamentos` ADD `desconto` DECIMAL(10, 2) NULL DEFAULT 0;
ALTER TABLE `lancamentos` ADD `valor_desconto` DECIMAL(10, 2) NULL;