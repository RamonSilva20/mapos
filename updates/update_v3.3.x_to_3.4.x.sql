
-- -----------------------------------------------------
-- Table `Garantia`
-- -----------------------------------------------------

CREATE TABLE IF NOT EXISTS `garantias` (
  `idGarantias` INT NOT NULL AUTO_INCREMENT,
  `dataGarantia` DATE NULL,
  `refGarantia` VARCHAR(15) NULL,
  `textoGarantia` VARCHAR(2000) NULL,
  `usuarios_id` INT(11) NULL,
  PRIMARY KEY (`idGarantias`),
  INDEX `fk_garantias_usuarios1` (`usuarios_id` ASC),
  CONSTRAINT `fk_garantias_usuarios1`
    FOREIGN KEY (`usuarios_id`)
    REFERENCES `usuarios` (`idUsuarios`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


ALTER TABLE `os` ADD `garantias_id` INT NULL;