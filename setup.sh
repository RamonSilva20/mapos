#!/bin/bash

# Atualizar os pacotes
sudo apt update && sudo apt upgrade -y

# Instalar o PHP e o curl (supondo que você está usando uma distribuição baseada em Debian/Ubuntu)
sudo apt install php-cli php-zip wget curl unzip -y

# Baixar o instalador do Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

# Verificar se o instalador do Composer é seguro
php -r "if (hash_file('sha384', 'composer-setup.php') === 'HASH_VALOR') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"

# Instalar o Composer globalmente
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Remover o instalador
php -r "unlink('composer-setup.php');"

# Executar o Composer install
composer install --no-dev

# Executar com Servidor Embutido do PHP
php -S localhost:8000

