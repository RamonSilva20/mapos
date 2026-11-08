#!/bin/bash

# Verifica execucao SUDO
if [ "$EUID" -ne 0 ]; then
    echo "Este script deve ser executado como root. Use sudo:"
    sudo "$0" "$@"
    exit $?
fi

##############################################################
## Script desenvolvido por Bruno Barreto e Leonardo Bernardi
## Adaptado para Map-OS 5 (PHP 8.5 - stack nativa, sem XAMPP)
## Versao Instalador: v3.0.0
##############################################################

# A partir do Map-OS 5 o projeto exige PHP >= 8.5 (composer.json: "php": "^8.5").
# A Apache Friends nao publica XAMPP para Linux com PHP 8.5 (o mais novo e o
# 8.2.12, de 2023), por isso este script NAO usa mais XAMPP: instala a stack
# nativa com Apache + PHP 8.5 (repositorio ondrej/php no Ubuntu, Sury no Debian)
# + MariaDB. Suporta Debian e Ubuntu (e derivados) em 64 bits.

set -o pipefail

# <=== Controle de STEPs ===>
# etapa0  - Display
# etapa1  - Boas Vindas
# etapa2  - Selecao de Versao
# etapa3  - Instalacao da stack (repositorio PHP 8.5, pacotes, servicos)
# etapa4  - Download e instalacao do MAP-OS
# etapa5  - Composer
# etapa6  - Banco de dados
# etapa7  - Configuracao pelo Browser
# etapa8  - Configuracao de dados de E-mail
# etapa9  - Auto Disparador de E-mail (cron)
# etapa10 - Alterar Numero da OS
# <=== Controle de STEPs ===>

# <=== Inicio SET Variaveis ===>
    dirDefault=/opt/InstaladorMAPOS
    dirHtdocs=/var/www/html
    dirMapos=$dirHtdocs/mapos
    dirMaposConfig=$dirMapos/application/.env
    phpBin=/usr/bin/php8.5
    apacheUser=www-data
    # Banco local (uso em TESTE/DESENVOLVIMENTO, conforme aviso abaixo)
    dbName=mapos
    dbUser=mapos
    dbPass=$(openssl rand -hex 12 2>/dev/null || echo "mapos$RANDOM$RANDOM")
    repoMapos="RamonSilva20/mapos"
    videoTutorial="https://youtu.be/aZE-LW_YOE4"
# <=== Fim SET Variaveis ===>

# <=== Inicio Display ===>
    clear
    echo "**************************************************"
    echo "**************************************************"
    echo "**                                              **"
    echo "**                                              **"
    echo "**                                              **"
    echo "**           SCRIPT AUTO INSTALADOR             **"
    echo "**    MAP-OS - SISTEMA DE ORDEM DE SERVICO      **"
    echo "**     LINUX (Debian / Ubuntu) x64 - PHP 8.5    **"
    echo "**                                              **"
    echo "**                                              **"
    echo "**                                              **"
    echo "**************************************************"
    echo "**************************************************"
    echo
# <=== Fim Display ===>

# <=== Inicio Boas Vindas ===>
    echo "Ola, seja bem vindo."
    echo "Esse script foi desenvolvido com o intuito de auxiliar na instalacao automatizada do Sistema MAP-OS e os componentes necessarios."
    echo "Reforcamos que nao recomendamos a instalacao em localhost para uso em PRODUCAO, apenas para TESTE ou DESENVOLVIMENTO devido a riscos de perdas de dados e seguranca."
    echo
    echo "Video Tutorial: $videoTutorial"
    echo
    read -p "Continuar a instalacao? (S/N): " resposta
    if [ "$resposta" = "N" ] || [ "$resposta" = "n" ]; then
        exit 0
    fi
# <=== Fim Boas Vindas ===>

# <=== Inicio Selecao de Versao ===>
    clear
    echo "# DESEJA INSTALAR O MAP-OS RELEASE OU MASTER?"
    echo "1- Release (Versao Estavel)"
    echo "2- Master (Versao Desenvolvimento)"
    echo "9- Sair"
    echo
    read -p "Digite uma opcao: (1,2,9): " resposta
    if [ "$resposta" = "1" ]; then
        downMapos=release
    elif [ "$resposta" = "2" ]; then
        downMapos=master
    elif [ "$resposta" = "9" ]; then
        exit 0
    else
        downMapos=release
    fi
# <=== Fim Selecao de Versao ===>

# <=== Inicio Instalacao da Stack ===>
    clear
    echo "# PREPARANDO O SERVIDOR (Apache + PHP 8.5 + MariaDB)..."
    echo

    if [ ! -d "$dirDefault" ]; then
        mkdir -p "$dirDefault"
    fi

    export DEBIAN_FRONTEND=noninteractive

    # Identifica a distribuicao
    if [ ! -f /etc/os-release ]; then
        echo "* Nao foi possivel identificar a distribuicao (/etc/os-release ausente)."
        exit 1
    fi
    . /etc/os-release
    codename="$VERSION_CODENAME"

    echo "* Instalando pre-requisitos"
    apt-get update -y >/dev/null 2>&1
    apt-get install -y ca-certificates apt-transport-https software-properties-common curl wget unzip gnupg lsb-release >/dev/null 2>&1

    echo "* Configurando repositorio do PHP 8.5"
    if echo "$ID $ID_LIKE" | grep -qi ubuntu; then
        add-apt-repository -y ppa:ondrej/php >/dev/null 2>&1
    elif echo "$ID $ID_LIKE" | grep -qi debian; then
        curl -sSLo /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg
        echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $codename main" \
            > /etc/apt/sources.list.d/sury-php.list
    else
        echo "* Distribuicao '$ID' nao suportada automaticamente."
        echo "  Instale manualmente PHP 8.5, Apache e MariaDB e rode novamente."
        exit 1
    fi

    apt-get update -y >/dev/null 2>&1

    echo "* Instalando Apache, PHP 8.5 e extensoes (pode levar alguns minutos)"
    apt-get install -y \
        apache2 libapache2-mod-php8.5 \
        php8.5 php8.5-cli php8.5-common php8.5-mysql php8.5-curl php8.5-gd \
        php8.5-zip php8.5-xml php8.5-mbstring php8.5-intl php8.5-bcmath >/dev/null 2>&1

    if [ ! -x "$phpBin" ]; then
        echo "* Falha ao instalar o PHP 8.5. Verifique o repositorio e tente novamente."
        exit 1
    fi

    echo "* Instalando MariaDB"
    apt-get install -y mariadb-server >/dev/null 2>&1

    echo "* Ajustando timezone do PHP para America/Sao_Paulo"
    for ini in /etc/php/8.5/apache2/php.ini /etc/php/8.5/cli/php.ini; do
        if [ -f "$ini" ]; then
            sed -i "s~^;*date.timezone.*~date.timezone = America/Sao_Paulo~" "$ini"
        fi
    done

    echo "* Habilitando PHP 8.5 e mod_rewrite no Apache"
    a2dismod php8.3 php8.2 php8.1 php8.0 php7.4 >/dev/null 2>&1
    a2enmod php8.5 rewrite >/dev/null 2>&1

    echo "* Liberando AllowOverride (necessario para o .htaccess do Map-OS)"
    cat > /etc/apache2/conf-available/mapos.conf <<EOF
<Directory $dirMapos>
    AllowOverride All
    Require all granted
</Directory>
EOF
    a2enconf mapos >/dev/null 2>&1

    echo "* Habilitando servicos na inicializacao"
    systemctl enable --now apache2 >/dev/null 2>&1
    systemctl enable --now mariadb >/dev/null 2>&1

    echo "* Instalando Composer"
    if ! command -v composer >/dev/null 2>&1; then
        $phpBin -r "copy('https://getcomposer.org/installer', '$dirDefault/composer-setup.php');"
        $phpBin "$dirDefault/composer-setup.php" --install-dir=/usr/local/bin --filename=composer >/dev/null 2>&1
        rm -f "$dirDefault/composer-setup.php"
    fi
# <=== Fim Instalacao da Stack ===>

# <=== Inicio Instalacao MAP-OS ===>
    clear
    echo "# INSTALACAO SISTEMA MAP-OS..."
    echo
    if [ -f "$dirMapos/index.php" ]; then
        echo "* Map-OS ja presente em $dirMapos."
    else
        echo "* Baixando a versao $downMapos do projeto."
        if [ "$downMapos" = "master" ]; then
            wget --quiet --show-progress -O "$dirDefault/MapOS.zip" \
                "https://github.com/$repoMapos/archive/refs/heads/master.zip"
        else
            wget --quiet --show-progress -O "$dirDefault/MapOS.zip" \
                "$(curl -s https://api.github.com/repos/$repoMapos/releases/latest | grep 'zipball_url' | awk -F\" '{print $4}')"
        fi
        echo
        echo "* Extraindo projeto."
        rm -rf "$dirDefault/extract"
        mkdir -p "$dirDefault/extract"
        unzip -q "$dirDefault/MapOS.zip" -d "$dirDefault/extract"
        # A pasta extraida varia (mapos-master, RamonSilva20-mapos-<hash>, ...)
        extracted="$(find "$dirDefault/extract" -maxdepth 1 -mindepth 1 -type d | head -n 1)"
        mv "$extracted" "$dirMapos"
        rm -rf "$dirDefault/extract" "$dirDefault/MapOS.zip"
    fi

    echo "* Ajustando permissoes."
    # IMPORTANTE: o .htaccess que acompanha o Map-OS e o que protege o .env no
    # Apache (docs/servidor-web.md). NUNCA apagar.
    chown -R $apacheUser:$apacheUser "$dirMapos"
    find "$dirMapos" -type d -exec chmod 755 {} \;
    find "$dirMapos" -type f -exec chmod 644 {} \;
    # Pastas que o sistema precisa escrever
    for w in application application/logs application/storage updates; do
        if [ -d "$dirMapos/$w" ]; then
            chmod -R 775 "$dirMapos/$w"
        fi
    done
# <=== Fim Instalacao MAP-OS ===>

# <=== Inicio Composer ===>
    clear
    echo "# COMPLEMENTO COMPOSER..."
    echo
    if [ -d "$dirMapos/application/vendor" ]; then
        echo "* Dependencias (vendor) ja instaladas."
    else
        echo "* Instalando dependencias do projeto."
        cd "$dirMapos" || exit 1
        sudo -u $apacheUser COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader -n
        chown -R $apacheUser:$apacheUser "$dirMapos/application/vendor"
    fi
# <=== Fim Composer ===>

# <=== Inicio Banco de Dados ===>
    clear
    echo "# BANCO DE DADOS (MariaDB)..."
    echo
    echo "* Criando banco '$dbName' e usuario '$dbUser'."
    mysql -u root <<SQL
CREATE DATABASE IF NOT EXISTS \`$dbName\` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER IF NOT EXISTS '$dbUser'@'localhost' IDENTIFIED BY '$dbPass';
ALTER USER '$dbUser'@'localhost' IDENTIFIED BY '$dbPass';
GRANT ALL PRIVILEGES ON \`$dbName\`.* TO '$dbUser'@'localhost';
FLUSH PRIVILEGES;
SQL
# <=== Fim Banco de Dados ===>

# <=== Inicio Configuracao pelo Browser ===>
    clear
    echo "# CONFIGURANDO MAPOS..."
    echo "Acesse o Map-OS pelo navegador: http://localhost/mapos/install"
    echo "Clique em PROXIMO e insira os dados abaixo:"
    echo
    echo "  Host: localhost"
    echo "  Usuario: $dbUser"
    echo "  Senha: $dbPass"
    echo "  Banco de Dados: $dbName"
    echo
    echo "  Nome:  Digite seu Nome Completo"
    echo "  Email: Informe seu E-mail para Login"
    echo "  Senha: Insira sua senha para acesso"
    echo
    echo "  URL: http://localhost/mapos/"
    echo
    echo ">> ANOTE a senha do banco acima: $dbPass"
    echo
    echo "Obs: Caso a instalacao nao tenha sido bem sucedida, encerre o script e execute novamente."
    read -p "Gostaria de realizar a configuracao personalizada (e-mail, cron, numero da OS)? (S/N): " resposta
    if [ "$resposta" = "N" ] || [ "$resposta" = "n" ]; then
        clear
        echo  "************************************************"
        echo  "****    SCRIPT DE AUTOMACAO FINALIZADO      ****"
        echo  "****       AGRADECEMOS A PREFERENCIA        ****"
        echo  "************************************************"
        echo
        echo "Senha do banco de dados: $dbPass"
        exit 0
    fi
# <=== Fim Configuracao pelo Browser ===>

# <=== Inicio Configuracoes Personalizadas ===>
    clear
    echo  "************************************************"
    echo  "****     CONFIGURACOES PERSONALIZADAS       ****"
    echo  "************************************************"

    # <=== Inicio Configuracao de dados de E-mail ===>
        echo
        read -p "Gostaria de configurar os dados de e-mail? (S/N): " resposta
        if [ "$resposta" = "S" ] || [ "$resposta" = "s" ]; then
            if [ ! -f "$dirMaposConfig" ]; then
                echo "* O arquivo .env ainda nao existe. Conclua a instalacao pelo navegador antes de configurar o e-mail."
            else
                read -p "Informe o Protocolo (Padrao: SMTP): " protocolo
                read -p "Informe o endereco do Host SMTP (Ex: smtp.seudominio.com): " hostsmtp
                read -p "Informe a Criptografia (SSL/TLS): " criptografia
                read -p "Informe a Porta (Ex: 587): " porta
                read -p "Informe o Email (Ex: nome@seudominio.com): " email
                read -p "Informe a Senha (****): " senha

                sed -i "s~^EMAIL_PROTOCOL.*~EMAIL_PROTOCOL=\"$protocolo\"~"     "$dirMaposConfig"
                sed -i "s~^EMAIL_SMTP_HOST.*~EMAIL_SMTP_HOST=\"$hostsmtp\"~"    "$dirMaposConfig"
                sed -i "s~^EMAIL_SMTP_CRYPTO.*~EMAIL_SMTP_CRYPTO=\"$criptografia\"~" "$dirMaposConfig"
                sed -i "s~^EMAIL_SMTP_PORT.*~EMAIL_SMTP_PORT=\"$porta\"~"       "$dirMaposConfig"
                sed -i "s~^EMAIL_SMTP_USER.*~EMAIL_SMTP_USER=\"$email\"~"       "$dirMaposConfig"
                sed -i "s~^EMAIL_SMTP_PASS.*~EMAIL_SMTP_PASS=\"$senha\"~"       "$dirMaposConfig"
                echo
                echo "* Dados de e-mail alterados com sucesso."
            fi
        else
            echo "* Dados de e-mail nao alterados."
        fi
    # <=== Fim Configuracao de dados de E-mail ===>

    # <=== Inicio Configuracao da Cron ===>
        echo
        read -p "Gostaria de ativar disparo automatico de e-mails? (S/N): " resposta
        if [ "$resposta" = "S" ] || [ "$resposta" = "s" ]; then
            ( crontab -l 2>/dev/null | grep -v "mapos/index.php email/" ; \
              echo "*/2 * * * * $phpBin $dirMapos/index.php email/process" ; \
              echo "*/5 * * * * $phpBin $dirMapos/index.php email/retry" ) | crontab -
            echo "* Disparo automatico configurado com sucesso."
        else
            echo "* Nao configurado disparo automatico."
        fi
    # <=== Fim Configuracao da Cron ===>

    # <=== Inicio Numero da OS ===>
        echo
        read -p "Gostaria de alterar o numero da primeira OS? (S/N): " resposta
        if [ "$resposta" = "S" ] || [ "$resposta" = "s" ]; then
            read -p "Informe o numero (Padrao: 1): " nOS
            mysql -u root -e "USE \`$dbName\`; ALTER TABLE os AUTO_INCREMENT=$nOS;"
            echo "* Numero da proxima OS alterado para $nOS."
        else
            echo "* Numero da primeira OS nao alterado."
        fi
    # <=== Fim Numero da OS ===>
# <=== Fim Configuracoes Personalizadas ===>

# Mensagem de status
clear
echo  "************************************************"
echo  "****    SCRIPT DE AUTOMACAO FINALIZADO      ****"
echo  "****       AGRADECEMOS A PREFERENCIA        ****"
echo  "************************************************"
echo
echo "Senha do banco de dados: $dbPass"
sleep 2
rm -rf "$dirDefault"
exit 0
