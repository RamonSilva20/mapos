ALTER TABLE `lancamentos` ADD `usuarios_id` INT(11) NOT NULL AFTER `vendas_id`;
ALTER TABLE `lancamentos` ADD CONSTRAINT `fk_usuarios_lancamentos1` FOREIGN KEY (`usuarios_id`) REFERENCES `usuarios` (`idUsuarios`) ON DELETE NO ACTION ON UPDATE NO ACTION;
