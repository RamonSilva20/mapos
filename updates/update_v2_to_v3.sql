
DROP TABLE `ci_sessions`;

-- Atualizar tabela de sessão do Codeigniter
CREATE TABLE IF NOT EXISTS `ci_sessions` (
        `id` varchar(128) NOT NULL,
        `ip_address` varchar(45) NOT NULL,
        `timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
        `data` blob NOT NULL,
        KEY `ci_sessions_timestamp` (`timestamp`)
);

-- Alterando o tamanho do campo senha
ALTER TABLE usuarios MODIFY senha VARCHAR(200) NOT NULL;

-- Reset de senha do usuário administrador
UPDATE usuarios SET senha = '94556715d7862d57e603e5e7389e0174227388d94090370517e3cfe5b1cccfbf3647bacd8dfc6190492c42d19e76df96308236c87c83ff78c37c01678d675e4fZE8TIK5YP2vt2j7+3ta7mfbOgY8wdMfs/vPCG5YBWh4='

-- Alterando tabela usuario
ALTER TABLE `usuarios` ADD `dataExpiracao` DATE NOT NULL ;