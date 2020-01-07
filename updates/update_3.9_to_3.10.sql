CREATE TABLE `anotacoes_os` ( 
    `idAnotacoes` INT(11) NOT NULL AUTO_INCREMENT, 
    `anotacao` VARCHAR(255) NOT NULL , 
    `data_hora` DATETIME NOT NULL , 
    `os_id` INT(11) NOT NULL , 
    PRIMARY KEY (`idAnotacoes`)
);