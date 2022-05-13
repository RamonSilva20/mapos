ALTER TABLE `servicos` ADD `minutosEstimados` INT NOT NULL DEFAULT 0;
ALTER TABLE `servicos_os` ADD `minutosGastos` INT NOT NULL DEFAULT 0;
ALTER TABLE `servicos_os` ADD `iniciadoEm` VARCHAR(64);
