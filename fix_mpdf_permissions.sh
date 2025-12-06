#!/bin/bash
# Script para corrigir permissões do diretório temporário do mPDF

DIR="/opt/lampp/htdocs/mapos/assets/uploads/temp"

# Criar diretórios se não existirem
mkdir -p "$DIR/mpdf"

# Ajustar permissões
chmod -R 777 "$DIR"

# Tentar ajustar proprietário (pode precisar de sudo)
if [ -w /etc/passwd ]; then
    # Se tiver permissão de root
    chown -R www-data:www-data "$DIR" 2>/dev/null || chown -R daemon:daemon "$DIR" 2>/dev/null || chown -R apache:apache "$DIR" 2>/dev/null
else
    echo "Para ajustar o proprietário, execute com sudo:"
    echo "sudo chown -R www-data:www-data $DIR"
    echo "ou"
    echo "sudo chown -R daemon:daemon $DIR"
fi

echo "Permissões ajustadas para: $DIR"
ls -la "$DIR"

