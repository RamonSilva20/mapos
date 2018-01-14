-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema mapos
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema mapos
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `mapos` DEFAULT CHARACTER SET utf8 ;
USE `mapos` ;

-- -----------------------------------------------------
-- Table `ci_sessions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ci_sessions` (
        `id` varchar(128) NOT NULL,
        `ip_address` varchar(45) NOT NULL,
        `timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
        `data` blob NOT NULL,
        KEY `ci_sessions_timestamp` (`timestamp`)
);

-- -----------------------------------------------------
-- Table `groups`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `groups` ;

CREATE TABLE IF NOT EXISTS `groups` (
  `id` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(20) NOT NULL,
  `description` VARCHAR(100) NOT NULL,
  `permissions` TEXT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `login_attempts`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `login_attempts` ;

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) NOT NULL,
  `login` VARCHAR(100) NOT NULL,
  `time` INT(11) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `persons`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `persons` ;

CREATE TABLE IF NOT EXISTS `persons` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `company` TINYINT(1) NOT NULL DEFAULT 0,
  `name` VARCHAR(300) NOT NULL,
  `company_name` VARCHAR(350) NULL,
  `cpf_cnpj` VARCHAR(20) NULL,
  `rg_ie` VARCHAR(30) NULL,
  `phone` VARCHAR(25) NULL,
  `celphone` VARCHAR(25) NULL,
  `email` VARCHAR(500) NULL,
  `image` VARCHAR(80) NULL,
  `obs` TEXT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `client` TINYINT(1) NULL DEFAULT 0,
  `supplier` TINYINT(1) NULL DEFAULT 0,
  `employee` TINYINT(1) NULL DEFAULT 0,
  `shipping_company` TINYINT(1) NULL DEFAULT 0,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL DEFAULT now(),
  PRIMARY KEY (`id`),
  UNIQUE INDEX `cpf_cnpj_UNIQUE` (`cpf_cnpj` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `users` ;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) NOT NULL,
  `username` VARCHAR(100) NULL DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `salt` VARCHAR(255) NULL DEFAULT NULL,
  `email` VARCHAR(254) NOT NULL,
  `activation_code` VARCHAR(40) NULL DEFAULT NULL,
  `forgotten_password_code` VARCHAR(40) NULL DEFAULT NULL,
  `forgotten_password_time` INT(11) UNSIGNED NULL DEFAULT NULL,
  `remember_code` VARCHAR(40) NULL DEFAULT NULL,
  `created_on` INT(11) UNSIGNED NOT NULL,
  `last_login` INT(11) UNSIGNED NULL DEFAULT NULL,
  `active` TINYINT(1) UNSIGNED NULL DEFAULT NULL,
  `first_name` VARCHAR(50) NULL DEFAULT NULL,
  `last_name` VARCHAR(50) NULL DEFAULT NULL,
  `company` VARCHAR(100) NULL DEFAULT NULL,
  `phone` VARCHAR(20) NULL DEFAULT NULL,
  `group_id` MEDIUMINT(8) NOT NULL,
  `person_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_users_persons1_idx` (`person_id` ASC),
  CONSTRAINT `fk_users_persons1`
    FOREIGN KEY (`person_id`)
    REFERENCES `persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `users_groups`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `users_groups` ;

CREATE TABLE IF NOT EXISTS `users_groups` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `group_id` MEDIUMINT(8) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uc_users_groups` (`user_id` ASC, `group_id` ASC),
  INDEX `fk_users_groups_users1_idx` (`user_id` ASC),
  INDEX `fk_users_groups_groups1_idx` (`group_id` ASC),
  CONSTRAINT `fk_users_groups_groups1`
    FOREIGN KEY (`group_id`)
    REFERENCES `groups` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_groups_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `adresses`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `adresses` ;

CREATE TABLE IF NOT EXISTS `adresses` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `street` VARCHAR(350) NOT NULL,
  `number` VARCHAR(10) NOT NULL,
  `complement` VARCHAR(80) NULL,
  `city` VARCHAR(80) NOT NULL,
  `state` VARCHAR(80) NOT NULL,
  `country` VARCHAR(80) NULL,
  `zip` VARCHAR(20) NOT NULL,
  `principal` TINYINT(1) NOT NULL DEFAULT 1,
  `person_id` INT NOT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_adresses_persons1_idx` (`person_id` ASC),
  CONSTRAINT `fk_adresses_persons1`
    FOREIGN KEY (`person_id`)
    REFERENCES `persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `departments`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `departments` ;

CREATE TABLE IF NOT EXISTS `departments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `department_name` VARCHAR(80) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NULL DEFAULT now(),
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `employees`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `employees` ;

CREATE TABLE IF NOT EXISTS `employees` (
  `person_id` INT NOT NULL,
  `salary` DECIMAL(10,2) NULL,
  `hiring_date` DATE NULL,
  `active` TINYINT(1) NULL DEFAULT 1,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  `department_id` INT NOT NULL,
  INDEX `fk_employees_persons1_idx` (`person_id` ASC),
  INDEX `fk_employees_departments1_idx` (`department_id` ASC),
  PRIMARY KEY (`person_id`),
  UNIQUE INDEX `person_id_UNIQUE` (`person_id` ASC),
  CONSTRAINT `fk_employees_persons1`
    FOREIGN KEY (`person_id`)
    REFERENCES `persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_employees_departments1`
    FOREIGN KEY (`department_id`)
    REFERENCES `departments` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `brands`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `brands` ;

CREATE TABLE IF NOT EXISTS `brands` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `brand_name` VARCHAR(100) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `services`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `services` ;

CREATE TABLE IF NOT EXISTS `services` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `service_name` VARCHAR(300) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `categories`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `categories` ;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(80) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `products`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `products` ;

CREATE TABLE IF NOT EXISTS `products` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `product_name` VARCHAR(150) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `description` TEXT NULL,
  `weight` VARCHAR(45) NULL,
  `unity` VARCHAR(45) NULL,
  `min_amount` DECIMAL(10,3) NULL,
  `category_id` INT NOT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  `supplier_id` INT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_products_categories1_idx` (`category_id` ASC),
  INDEX `fk_products_persons1_idx` (`supplier_id` ASC),
  CONSTRAINT `fk_products_categories1`
    FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_products_persons1`
    FOREIGN KEY (`supplier_id`)
    REFERENCES `persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `warehouses`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `warehouses` ;

CREATE TABLE IF NOT EXISTS `warehouses` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `warehouse_name` VARCHAR(45) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `warehouses_products`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `warehouses_products` ;

CREATE TABLE IF NOT EXISTS `warehouses_products` (
  `warehouse_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `stock` DECIMAL(10,3) NOT NULL,
  PRIMARY KEY (`warehouse_id`, `product_id`),
  INDEX `fk_warehouses_has_products_products1_idx` (`product_id` ASC),
  INDEX `fk_warehouses_has_products_warehouses1_idx` (`warehouse_id` ASC),
  CONSTRAINT `fk_warehouses_has_products_warehouses1`
    FOREIGN KEY (`warehouse_id`)
    REFERENCES `warehouses` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_warehouses_has_products_products1`
    FOREIGN KEY (`product_id`)
    REFERENCES `products` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


INSERT INTO `persons` (`id`, `company`, `name`, `company_name`, `cpf_cnpj`, `rg_ie`, `phone`, `celphone`, `email`, `image`, `obs`, `active`, `client`, `supplier`, `employee`, `shipping_company`, `created_at`, `updated_at`) VALUES ('1', '0', 'Administrador', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '0', '0', '1', '0', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT INTO `groups` (`id`, `name`, `description`,`permissions`) VALUES
     (1,'admin','Administradores','{}'),
     (2,'members','Técnicos','{}');

INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `salt`, `email`, `activation_code`, `forgotten_password_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`,`group_id`,`person_id`) VALUES
     ('1','127.0.0.1','admin','$2a$07$SeBknntpZror9uyftVopmu61qg0ms8Qv1yV6FG.kQOSM.9QhmTo36','','admin@admin.com','',NULL,'1268889823','1268889823','1', 'Admin','istrator','ADMIN','0',1,1);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
