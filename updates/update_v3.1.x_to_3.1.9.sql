-- Alterando tabela de produtos

ALTER TABLE `produtos` ADD `saida` TINYINT(1) NULL DEFAULT NULL AFTER `estoqueMinimo`, ADD `entrada` TINYINT(1) NULL DEFAULT NULL AFTER `saida`;

ALTER TABLE `lancamentos` CHANGE `baixado` `baixado` TINYINT(1) NULL DEFAULT '0';

ALTER TABLE `lancamentos` ADD `vendas_id` INT(11) NULL DEFAULT NULL;



