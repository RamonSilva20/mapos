CREATE TABLE `cobrancas` (
  `idCobranca` INT(4) NOT NULL AUTO_INCREMENT,
  `charge_id` INT(4) NULL DEFAULT NULL,
  `conditional_discount_date` DATE NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  `custom_id` INT(4) NULL DEFAULT NULL,
  `expire_at` DATE NOT NULL,
  `message` VARCHAR(255) NOT NULL,
  `payment_method` VARCHAR(11) NULL DEFAULT NULL,
  `payment_url` VARCHAR(255) NULL DEFAULT NULL,
  `request_delivery_address` VARCHAR(64) NULL DEFAULT NULL,
  `status` VARCHAR(36) NOT NULL,
  `total` VARCHAR(15) NULL DEFAULT NULL,
  `barcode` VARCHAR(255) NOT NULL,
  `link` VARCHAR(255) NOT NULL,
  `payment` VARCHAR(64) NOT NULL,
  `pdf` VARCHAR(255) NULL DEFAULT NULL,
  `vendas_id` INT(11) NOT NULL,
  PRIMARY KEY (`idCobranca`),
  INDEX `fk_cobrancas_vendas1_idx` (`vendas_id` ASC) ,
  CONSTRAINT `fk_cobrancas_vendas1`
    FOREIGN KEY (`vendas_id`)
    REFERENCES `mapos`.`vendas` (`idVendas`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)