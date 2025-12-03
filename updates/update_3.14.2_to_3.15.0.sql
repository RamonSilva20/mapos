CREATE TABLE `configuracoes` ( `idConfig` INT NOT NULL AUTO_INCREMENT , `config` VARCHAR(20) NOT NULL UNIQUE, `valor` VARCHAR(20) NOT NULL , PRIMARY KEY (`idConfig`)) ENGINE = InnoDB;

INSERT INTO `configuracoes` (`idConfig`, `config`, `valor`) VALUES
(2, 'app_name', 'Map-OS'),
(3, 'app_theme', 'white'),
(4, 'per_page', '10'),
(5, 'os_notification', 'cliente'),
(6, 'control_estoque', '1');

