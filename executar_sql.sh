#!/bin/bash
# Script para executar SQL com senha

cd /opt/lampp/htdocs/mapos

# Tentar executar com senha vazia primeiro
mysql -u root mapos < updates/add_estoque_consumido_propostas.sql 2>&1

# Se falhar, tentar com senha
if [ $? -ne 0 ]; then
    echo "Tentando com autenticação..."
    mysql -u root -p84dy866n mapos < updates/add_estoque_consumido_propostas.sql 2>&1
fi

