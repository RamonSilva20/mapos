#!/bin/bash
# Script para adicionar permissões de Propostas

echo "Atualizando permissões..."

# Conectar ao MySQL e executar atualização
mysql -u root mapos << 'EOF'

-- Atualizar permissão Administrador (ID 1)
UPDATE permissoes 
SET permissoes = REPLACE(
    permissoes,
    's:9:"vCobranca";s:1:"1";}',
    's:9:"vCobranca";s:1:"1";s:10:"vPropostas";s:1:"1";s:10:"aPropostas";s:1:"1";s:10:"ePropostas";s:1:"1";s:10:"dPropostas";s:1:"1";}'
)
WHERE idPermissao = 1 AND permissoes NOT LIKE '%vPropostas%';

-- Para outras permissões, adicionar no final do array
UPDATE permissoes 
SET permissoes = REPLACE(
    permissoes,
    ';}',
    ';s:10:"vPropostas";s:1:"1";s:10:"aPropostas";s:1:"1";s:10:"ePropostas";s:1:"1";s:10:"dPropostas";s:1:"1";}'
)
WHERE idPermissao > 1 
AND permissoes IS NOT NULL 
AND permissoes != '' 
AND permissoes NOT LIKE '%vPropostas%';

SELECT 'Permissões atualizadas com sucesso!' as status;

EOF

echo "Concluído!"

