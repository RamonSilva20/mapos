SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';



-- -----------------------------------------------------
-- Table `ci_sessions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `session_id` VARCHAR(40) NOT NULL DEFAULT '0',
  `ip_address` VARCHAR(45) NOT NULL DEFAULT '0',
  `user_agent` VARCHAR(120) NOT NULL,
  `last_activity` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_data` TEXT NOT NULL,
  PRIMARY KEY (`session_id`),
  INDEX `last_activity_idx` (`last_activity` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `clientes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `clientes` (
  `idClientes` INT(11) NOT NULL AUTO_INCREMENT,
  `nomeCliente` VARCHAR(255) NOT NULL,
  `documento` VARCHAR(20) NOT NULL,
  `telefone` VARCHAR(20) NOT NULL,
  `celular` VARCHAR(20) NULL DEFAULT NULL,
  `email` VARCHAR(100) NOT NULL,
  `dataCadastro` DATE NULL DEFAULT NULL,
  `rua` VARCHAR(70) NULL DEFAULT NULL,
  `numero` VARCHAR(15) NULL DEFAULT NULL,
  `bairro` VARCHAR(45) NULL DEFAULT NULL,
  `cidade` VARCHAR(45) NULL DEFAULT NULL,
  `estado` VARCHAR(20) NULL DEFAULT NULL,
  `cep` VARCHAR(20) NULL DEFAULT NULL,
  PRIMARY KEY (`idClientes`))
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `lancamentos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `lancamentos` (
  `idLancamentos` INT(11) NOT NULL AUTO_INCREMENT,
  `descricao` VARCHAR(255) NULL DEFAULT NULL,
  `valor` VARCHAR(15) NOT NULL,
  `data_vencimento` DATE NOT NULL,
  `data_pagamento` DATE NULL DEFAULT NULL,
  `baixado` TINYINT(1) NULL DEFAULT NULL,
  `cliente_fornecedor` VARCHAR(255) NULL DEFAULT NULL,
  `forma_pgto` VARCHAR(100) NULL DEFAULT NULL,
  `tipo` VARCHAR(45) NULL DEFAULT NULL,
  `anexo` VARCHAR(250) NULL,
  `clientes_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`idLancamentos`),
  INDEX `fk_lancamentos_clientes1` (`clientes_id` ASC),
  CONSTRAINT `fk_lancamentos_clientes1`
    FOREIGN KEY (`clientes_id`)
    REFERENCES `clientes` (`idClientes`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `permissoes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `permissoes` (
  `idPermissao` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(80) NOT NULL,
  `permissoes` TEXT NULL,
  `situacao` TINYINT(1) NULL,
  `data` DATE NULL,
  PRIMARY KEY (`idPermissao`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `usuarios`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `idUsuarios` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(80) NOT NULL,
  `rg` VARCHAR(20) NULL DEFAULT NULL,
  `cpf` VARCHAR(20) NOT NULL,
  `rua` VARCHAR(70) NULL DEFAULT NULL,
  `numero` VARCHAR(15) NULL DEFAULT NULL,
  `bairro` VARCHAR(45) NULL DEFAULT NULL,
  `cidade` VARCHAR(45) NULL DEFAULT NULL,
  `estado` VARCHAR(20) NULL DEFAULT NULL,
  `email` VARCHAR(80) NOT NULL,
  `senha` VARCHAR(45) NOT NULL,
  `telefone` VARCHAR(20) NOT NULL,
  `celular` VARCHAR(20) NULL DEFAULT NULL,
  `situacao` TINYINT(1) NOT NULL,
  `dataCadastro` DATE NOT NULL,
  `nivel` INT(11) NOT NULL,
  `permissoes_id` INT NOT NULL,
  PRIMARY KEY (`idUsuarios`),
  INDEX `fk_usuarios_permissoes1_idx` (`permissoes_id` ASC),
  CONSTRAINT `fk_usuarios_permissoes1`
    FOREIGN KEY (`permissoes_id`)
    REFERENCES `permissoes` (`idPermissao`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `os`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `os` (
  `idOs` INT(11) NOT NULL AUTO_INCREMENT,
  `dataInicial` DATE NULL DEFAULT NULL,
  `dataFinal` DATE NULL DEFAULT NULL,
  `garantia` VARCHAR(45) NULL DEFAULT NULL,
  `descricaoProduto` VARCHAR(150) NULL DEFAULT NULL,
  `defeito` VARCHAR(150) NULL DEFAULT NULL,
  `status` VARCHAR(45) NULL DEFAULT NULL,
  `observacoes` VARCHAR(150) NULL DEFAULT NULL,
  `laudoTecnico` VARCHAR(150) NULL DEFAULT NULL,
  `valorTotal` VARCHAR(15) NULL DEFAULT NULL,
  `clientes_id` INT(11) NOT NULL,
  `usuarios_id` INT(11) NOT NULL,
  `lancamento` INT(11) NULL DEFAULT NULL,
  `faturado` TINYINT(1) NOT NULL,
  PRIMARY KEY (`idOs`),
  INDEX `fk_os_clientes1` (`clientes_id` ASC),
  INDEX `fk_os_usuarios1` (`usuarios_id` ASC),
  INDEX `fk_os_lancamentos1` (`lancamento` ASC),
  CONSTRAINT `fk_os_clientes1`
    FOREIGN KEY (`clientes_id`)
    REFERENCES `clientes` (`idClientes`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_os_lancamentos1`
    FOREIGN KEY (`lancamento`)
    REFERENCES `lancamentos` (`idLancamentos`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_os_usuarios1`
    FOREIGN KEY (`usuarios_id`)
    REFERENCES `usuarios` (`idUsuarios`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `produtos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `produtos` (
  `idProdutos` INT(11) NOT NULL AUTO_INCREMENT,
  `descricao` VARCHAR(80) NOT NULL,
  `unidade` VARCHAR(10) NULL DEFAULT NULL,
  `precoCompra` DECIMAL(10,2) NULL DEFAULT NULL,
  `precoVenda` DECIMAL(10,2) NOT NULL,
  `estoque` INT(11) NOT NULL,
  `estoqueMinimo` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`idProdutos`))
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `produtos_os`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `produtos_os` (
  `idProdutos_os` INT(11) NOT NULL AUTO_INCREMENT,
  `quantidade` INT(11) NOT NULL,
  `os_id` INT(11) NOT NULL,
  `produtos_id` INT(11) NOT NULL,
  `subTotal` VARCHAR(15) NULL DEFAULT NULL,
  PRIMARY KEY (`idProdutos_os`),
  INDEX `fk_produtos_os_os1` (`os_id` ASC),
  INDEX `fk_produtos_os_produtos1` (`produtos_id` ASC),
  CONSTRAINT `fk_produtos_os_os1`
    FOREIGN KEY (`os_id`)
    REFERENCES `os` (`idOs`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_produtos_os_produtos1`
    FOREIGN KEY (`produtos_id`)
    REFERENCES `produtos` (`idProdutos`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `servicos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `servicos` (
  `idServicos` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(45) NOT NULL,
  `descricao` VARCHAR(45) NULL DEFAULT NULL,
  `preco` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`idServicos`))
ENGINE = InnoDB
AUTO_INCREMENT = 7
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `servicos_os`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `servicos_os` (
  `idServicos_os` INT(11) NOT NULL AUTO_INCREMENT,
  `os_id` INT(11) NOT NULL,
  `servicos_id` INT(11) NOT NULL,
  `subTotal` VARCHAR(15) NULL DEFAULT NULL,
  PRIMARY KEY (`idServicos_os`),
  INDEX `fk_servicos_os_os1` (`os_id` ASC),
  INDEX `fk_servicos_os_servicos1` (`servicos_id` ASC),
  CONSTRAINT `fk_servicos_os_os1`
    FOREIGN KEY (`os_id`)
    REFERENCES `os` (`idOs`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_servicos_os_servicos1`
    FOREIGN KEY (`servicos_id`)
    REFERENCES `servicos` (`idServicos`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `vendas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `vendas` (
  `idVendas` INT NOT NULL AUTO_INCREMENT,
  `dataVenda` DATE NULL,
  `valorTotal` VARCHAR(45) NULL,
  `desconto` VARCHAR(45) NULL,
  `faturado` TINYINT(1) NULL,
  `clientes_id` INT(11) NOT NULL,
  `usuarios_id` INT(11) NULL,
  `lancamentos_id` INT(11) NULL,
  PRIMARY KEY (`idVendas`),
  INDEX `fk_vendas_clientes1` (`clientes_id` ASC),
  INDEX `fk_vendas_usuarios1` (`usuarios_id` ASC),
  INDEX `fk_vendas_lancamentos1` (`lancamentos_id` ASC),
  CONSTRAINT `fk_vendas_clientes1`
    FOREIGN KEY (`clientes_id`)
    REFERENCES `clientes` (`idClientes`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_vendas_usuarios1`
    FOREIGN KEY (`usuarios_id`)
    REFERENCES `usuarios` (`idUsuarios`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_vendas_lancamentos1`
    FOREIGN KEY (`lancamentos_id`)
    REFERENCES `lancamentos` (`idLancamentos`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `itens_de_vendas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `itens_de_vendas` (
  `idItens` INT NOT NULL AUTO_INCREMENT,
  `subTotal` VARCHAR(45) NULL,
  `quantidade` INT(11) NULL,
  `vendas_id` INT NOT NULL,
  `produtos_id` INT(11) NOT NULL,
  PRIMARY KEY (`idItens`),
  INDEX `fk_itens_de_vendas_vendas1` (`vendas_id` ASC),
  INDEX `fk_itens_de_vendas_produtos1` (`produtos_id` ASC),
  CONSTRAINT `fk_itens_de_vendas_vendas1`
    FOREIGN KEY (`vendas_id`)
    REFERENCES `vendas` (`idVendas`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_itens_de_vendas_produtos1`
    FOREIGN KEY (`produtos_id`)
    REFERENCES `produtos` (`idProdutos`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `anexos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `anexos` (
  `idAnexos` INT NOT NULL AUTO_INCREMENT,
  `anexo` VARCHAR(45) NULL,
  `thumb` VARCHAR(45) NULL,
  `url` VARCHAR(300) NULL,
  `path` VARCHAR(300) NULL,
  `os_id` INT(11) NOT NULL,
  PRIMARY KEY (`idAnexos`),
  INDEX `fk_anexos_os1` (`os_id` ASC),
  CONSTRAINT `fk_anexos_os1`
    FOREIGN KEY (`os_id`)
    REFERENCES `os` (`idOs`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `documentos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `documentos` (
  `idDocumentos` INT NOT NULL AUTO_INCREMENT,
  `documento` VARCHAR(70) NULL,
  `descricao` TEXT NULL,
  `file` VARCHAR(100) NULL,
  `path` VARCHAR(300) NULL,
  `url` VARCHAR(300) NULL,
  `cadastro` DATE NULL,
  `categoria` VARCHAR(80) NULL,
  `tipo` VARCHAR(15) NULL,
  `tamanho` VARCHAR(45) NULL,
  PRIMARY KEY (`idDocumentos`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `emitente`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `emitente` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `nome` VARCHAR(255) NULL ,
  `cnpj` VARCHAR(45) NULL ,
  `ie` VARCHAR(50) NULL ,
  `rua` VARCHAR(70) NULL ,
  `numero` VARCHAR(15) NULL ,
  `bairro` VARCHAR(45) NULL ,
  `cidade` VARCHAR(45) NULL ,
  `uf` VARCHAR(20) NULL ,
  `telefone` VARCHAR(20) NULL ,
  `email` VARCHAR(255) NULL ,
  `url_logo` VARCHAR(225) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;




INSERT INTO `permissoes` (`idPermissao`, `nome`, `permissoes`, `situacao`, `data`) VALUES
(1, 'Administrador', 'a:38:{s:8:"aCliente";s:1:"1";s:8:"eCliente";s:1:"1";s:8:"dCliente";s:1:"1";s:8:"vCliente";s:1:"1";s:8:"aProduto";s:1:"1";s:8:"eProduto";s:1:"1";s:8:"dProduto";s:1:"1";s:8:"vProduto";s:1:"1";s:8:"aServico";s:1:"1";s:8:"eServico";s:1:"1";s:8:"dServico";s:1:"1";s:8:"vServico";s:1:"1";s:3:"aOs";s:1:"1";s:3:"eOs";s:1:"1";s:3:"dOs";s:1:"1";s:3:"vOs";s:1:"1";s:6:"aVenda";s:1:"1";s:6:"eVenda";s:1:"1";s:6:"dVenda";s:1:"1";s:6:"vVenda";s:1:"1";s:8:"aArquivo";s:1:"1";s:8:"eArquivo";s:1:"1";s:8:"dArquivo";s:1:"1";s:8:"vArquivo";s:1:"1";s:11:"aLancamento";s:1:"1";s:11:"eLancamento";s:1:"1";s:11:"dLancamento";s:1:"1";s:11:"vLancamento";s:1:"1";s:8:"cUsuario";s:1:"1";s:9:"cEmitente";s:1:"1";s:10:"cPermissao";s:1:"1";s:7:"cBackup";s:1:"1";s:8:"rCliente";s:1:"1";s:8:"rProduto";s:1:"1";s:8:"rServico";s:1:"1";s:3:"rOs";s:1:"1";s:6:"rVenda";s:1:"1";s:11:"rFinanceiro";s:1:"1";}', 1, '2014-09-03');





INSERT INTO `usuarios` (`idUsuarios`, `nome`, `rg`, `cpf`, `rua`, `numero`, `bairro`, `cidade`, `estado`, `email`, `senha`, `telefone`, `celular`, `situacao`, `dataCadastro`, `nivel`, `permissoes_id`) VALUES
(1, 'admin', 'MG-25.502.560', '600.021.520-87', 'Rua Acima', '12', 'Alvorada', 'Brasília', 'DF', 'admin@admin.com', '7c4a8d09ca3762af61e59520943dc26494f8941b', '0000-0000', '', 1, '2013-11-22', 1, 1);


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
