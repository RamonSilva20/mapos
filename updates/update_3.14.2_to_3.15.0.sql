CREATE TABLE `banco`.`configuracoes` ( `idConfig` INT NOT NULL AUTO_INCREMENT , `config` VARCHAR(20) NOT NULL UNIQUE, `valor` VARCHAR(20) NOT NULL , PRIMARY KEY (`idConfig`)) ENGINE = InnoDB;

INSERT INTO `configuracoes` (`idConfig`, `config`, `valor`) VALUES
(2, 'app_name', 'Map-OS'),
(3, 'app_theme', 'white'),
(4, 'per_page', '10'),
(5, 'os_notification', 'cliente'),
(6, 'control_estoque', '1');

CREATE TABLE `pagamento` (
  `idPag` INT NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `client_id` varchar(200) NOT NULL,
  `client_secret` varchar(200) NOT NULL,
  `public_key` varchar(200) NOT NULL,
  `access_token` varchar(200) NOT NULL,
  `default_pag` int(1) NOT NULL,
   PRIMARY KEY (`idPag`)
);
