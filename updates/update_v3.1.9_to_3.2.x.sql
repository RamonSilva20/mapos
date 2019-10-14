-- Alterando tabela usuario
ALTER TABLE `usuarios` ADD `dataExpiracao` DATE NOT NULL ;

UPDATE usuarios SET dataExpiracao = '3000-01-01';