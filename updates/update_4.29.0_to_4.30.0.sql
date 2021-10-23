INSERT INTO `configuracoes` (`idConfig`, `config`, `valor`) VALUES (9, 'control_editos', 1);

INSERT INTO `configuracoes` (`idConfig`, `config`, `valor`) VALUES (10, 'control_datatable', 1);

ALTER TABLE `clientes` ADD `fornecedor` BOOLEAN NOT NULL DEFAULT FALSE;
