-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema mapos
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema mapos
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `mapos` DEFAULT CHARACTER SET utf8 ;
USE `mapos` ;



CREATE TABLE IF NOT EXISTS `ci_sessions` (
        `id` varchar(128) NOT NULL,
        `ip_address` varchar(45) NOT NULL,
        `timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
        `data` blob NOT NULL,
        KEY `ci_sessions_timestamp` (`timestamp`)
);


-- -----------------------------------------------------
-- Table `mapos`.`groups`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mapos`.`groups` ;

CREATE TABLE IF NOT EXISTS `mapos`.`groups` (
  `id` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(20) NOT NULL,
  `description` VARCHAR(100) NOT NULL,
  `permissions` TEXT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `mapos`.`login_attempts`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mapos`.`login_attempts` ;

CREATE TABLE IF NOT EXISTS `mapos`.`login_attempts` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) NOT NULL,
  `login` VARCHAR(100) NOT NULL,
  `time` INT(11) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `mapos`.`persons`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mapos`.`persons` ;

CREATE TABLE IF NOT EXISTS `mapos`.`persons` (
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
  `provider` TINYINT(1) NULL DEFAULT 0,
  `shipping_company` TINYINT(1) NULL DEFAULT 0,
  `created_at` DATETIME NULL DEFAULT now(),
  `updated_at` DATETIME NULL DEFAULT now(),
  PRIMARY KEY (`id`),
  UNIQUE INDEX `cpf_cnpj_UNIQUE` (`cpf_cnpj` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mapos`.`users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mapos`.`users` ;

CREATE TABLE IF NOT EXISTS `mapos`.`users` (
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
    REFERENCES `mapos`.`persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `mapos`.`users_groups`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mapos`.`users_groups` ;

CREATE TABLE IF NOT EXISTS `mapos`.`users_groups` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `group_id` MEDIUMINT(8) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `uc_users_groups` (`user_id` ASC, `group_id` ASC),
  INDEX `fk_users_groups_users1_idx` (`user_id` ASC),
  INDEX `fk_users_groups_groups1_idx` (`group_id` ASC),
  CONSTRAINT `fk_users_groups_groups1`
    FOREIGN KEY (`group_id`)
    REFERENCES `mapos`.`groups` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_groups_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `mapos`.`users` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `mapos`.`adresses`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mapos`.`adresses` ;

CREATE TABLE IF NOT EXISTS `mapos`.`adresses` (
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
    REFERENCES `mapos`.`persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mapos`.`departments`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mapos`.`departments` ;

CREATE TABLE IF NOT EXISTS `mapos`.`departments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `department_name` VARCHAR(80) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NULL DEFAULT now(),
  `updated_at` DATETIME NULL DEFAULT now(),
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mapos`.`employees`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mapos`.`employees` ;

CREATE TABLE IF NOT EXISTS `mapos`.`employees` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `salary` DECIMAL(10,2) NULL,
  `hiring_date` DATE NULL,
  `active` TINYINT(1) NULL DEFAULT 1,
  `created_at` DATETIME NULL,
  `update_at` DATETIME NULL,
  `person_id` INT NOT NULL,
  `department_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_employees_persons1_idx` (`person_id` ASC),
  INDEX `fk_employees_departments1_idx` (`department_id` ASC),
  CONSTRAINT `fk_employees_persons1`
    FOREIGN KEY (`person_id`)
    REFERENCES `mapos`.`persons` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_employees_departments1`
    FOREIGN KEY (`department_id`)
    REFERENCES `mapos`.`departments` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mapos`.`brands`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mapos`.`brands` ;

CREATE TABLE IF NOT EXISTS `mapos`.`brands` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `brand_name` VARCHAR(100) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mapos`.`services`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mapos`.`services` ;

CREATE TABLE IF NOT EXISTS `mapos`.`services` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `service_name` VARCHAR(300) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;



INSERT INTO `persons` (`id`, `company`, `name`, `company_name`, `cpf_cnpj`, `rg_ie`, `phone`, `celphone`, `email`, `image`, `obs`, `active`, `client`, `provider`, `shipping_company`, `created_at`, `updated_at`) VALUES (NULL, '0', 'Administrador', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '0', '0', '0', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT INTO `groups` (`id`, `name`, `description`,`permissions`) VALUES (1,'admin','Administradores','{}'),(2,'members','Técnicos','{}');

INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `salt`, `email`, `activation_code`, `forgotten_password_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`,`group_id`,`person_id`) VALUES
     ('1','127.0.0.1','admin','$2a$07$SeBknntpZror9uyftVopmu61qg0ms8Qv1yV6FG.kQOSM.9QhmTo36','','admin@admin.com','',NULL,'1268889823','1268889823','1', 'Admin','istrator','ADMIN','0',1,1);


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
