-- Adiciona campo detalhes na tabela servicos_os para permitir edição livre de detalhes do serviço na OS
ALTER TABLE `servicos_os` ADD COLUMN `detalhes` TEXT NULL AFTER `preco`;

