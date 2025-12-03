ALTER TABLE `produtos_os` ADD `descricao` VARCHAR(80) NULL AFTER `quantidade`, ADD `preco` VARCHAR(15) NULL AFTER `descricao`;

ALTER TABLE `servicos_os` ADD `servico` VARCHAR(80) NULL AFTER `idServicos_os`, ADD `quantidade` DOUBLE NULL AFTER `servico`, ADD `preco` VARCHAR(15) NULL AFTER `quantidade`;

ALTER TABLE `itens_de_vendas` ADD `preco` VARCHAR(15) NULL AFTER `quantidade`;