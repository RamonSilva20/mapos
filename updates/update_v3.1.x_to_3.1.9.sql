-- Alterando tabela de produtos

ALTER TABLE `produtos` ADD `saida` TINYINT(1) NULL DEFAULT NULL AFTER `estoqueMinimo`, ADD `entrada` TINYINT(1) NULL DEFAULT NULL AFTER `saida`;