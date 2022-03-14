ALTER TABLE `clientes` ADD `senha` VARCHAR(200) NOT NULL;
CREATE TABLE `resets_de_senha` ( `id` INT NOT NULL AUTO_INCREMENT, `email` VARCHAR(200) NOT NULL , `token` VARCHAR(255) NOT NULL , `data_expiracao` DATETIME NOT NULL, `token_utilizado` TINYINT NOT NULL, PRIMARY KEY (`id`)) ENGINE = InnoDB DEFAULT CHARACTER SET = latin1;
INSERT INTO `configuracoes` (`idConfig`, `config`, `valor`) VALUES (13, 'control_edit_vendas', 1);
