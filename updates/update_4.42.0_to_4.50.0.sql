ALTER TABLE `os`
ADD COLUMN `assClienteImg` LONGTEXT,
ADD COLUMN `assClienteIp` VARCHAR(20),
ADD COLUMN `assClienteData` DATETIME,
ADD COLUMN `assTecnicoImg` LONGTEXT,
ADD COLUMN `assTecnicoIp` VARCHAR(20),
ADD COLUMN `assTecnicoData` DATETIME;

ALTER TABLE `usuarios`
ADD COLUMN `assinaturaImg` LONGTEXT;

INSERT INTO `configuracoes` (`config`, `valor`) VALUES ('usar_assinatura', 1), ('status_assinatura', 'Aprovado');
