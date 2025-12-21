-- ===========================================
-- Adicionar Permissões de Propostas Comerciais
-- Data: 22/12/2025
-- ===========================================

-- Adicionar permissões de Propostas
INSERT INTO `permissoes` (`nome`, `permissao`, `descricao`) VALUES
('Visualizar Propostas', 'vPropostas', 'Permite visualizar propostas comerciais'),
('Adicionar Propostas', 'aPropostas', 'Permite adicionar propostas comerciais'),
('Editar Propostas', 'ePropostas', 'Permite editar propostas comerciais'),
('Excluir Propostas', 'dPropostas', 'Permite excluir propostas comerciais')
ON DUPLICATE KEY UPDATE descricao = VALUES(descricao);

