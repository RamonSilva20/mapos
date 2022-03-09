ALTER TABLE `clientes` ADD `senha` VARCHAR(200) NOT NULL ;
CREATE TABLE `resets_de_senha` ( `id` INT(11) NOT NULL AUTO_INCREMENT = 1, add PRIMARY KEY (`id`)  , `email` VARCHAR(200) NOT NULL , `token` VARCHAR(255) NOT NULL , `data_expiracao` DATETIME NOT NULL, `token_utilizado` TINYINT NOT NULL ) ENGINE = InnoDB;
