-- Adiciona campo para controlar exibição da descrição na impressão da OS
ALTER TABLE `os` ADD COLUMN `imprimir_descricao` TINYINT(1) DEFAULT 0 AFTER `descricaoProduto`;

