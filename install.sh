#!/bin/bash

# Verifica execucao SUDO
if [ "$EUID" -ne 0 ]; then
    echo "Este script deve ser executado como root. Use sudo:"
    sudo "$0" "$@"
    exit $?
fi

##############################################################
## Script desenvolvido por Bruno Barreto e Leonardo Bernardi
## Versao Instalador: v1.0.20230916
## Publicado na versao 4.41.0 do MapOS
##############################################################

# <=== Inicio STEP00 ===>
    clear
    echo "**************************************************"
    echo "**************************************************"
    echo "**                                              **"
    echo "**                                              **"
    echo "**                                              **"
    echo "**           SCRIPT AUTO INSTALADOR             **"
    echo "**    MAP-OS - SISTEMA DE ORDEM DE SERVICO      **"
    echo "**          LINUX (Debian / Ubuntu)             **"
    echo "**                                              **"
    echo "**                                              **"
    echo "**                                              **"
    echo "**************************************************"
    echo "**************************************************"
    echo

    # <=== Inicio SET Diretorios ===>
        comando_instalar="sudo apt-get"
        dirDefault=/opt/InstaladorMAPOS
        urlXampp="https://sourceforge.net/projects/xampp/files/XAMPP%20Linux/8.2.4/xampp-linux-x64-8.2.4-0-installer.run/download"
        dirXampp=/opt/lampp
        dirHtdocs=/opt/lampp/htdocs
        dirMySQL=/opt/lampp/bin/mysql
    # <=== Fim SET Diretorios ===>
# <=== Fim STEP00 ===>

# <=== Inicio Termos de Aceite ===>
    echo "AVISO IMPORTANTE!"
    echo "Recomendamos que, se houver instalações anteriores do Map-OS, faça backup de todos os dados importantes antes de prosseguir com a instalacao. Ao continuar com a instalacao, você concorda que nao responsabilizara os desenvolvedores do script por quaisquer perdas de dados que possam ocorrer."
    echo
    read -p "Aceita os termos acima? (S/N): " resposta
    if [ "$resposta" = "N" ] || [ "$resposta" = "n" ]; then
        exit 0
    elif [ "$resposta" = "S" ] || [ "$resposta" = "s" ]; then
        echo
    fi
# <=== Fim Termos de Aceite ===>

# <=== Inicio STEP01 ===>
    clear
    echo "01 BAIXANDO DEPENDENCIAS..."
    echo
    echo "01.1 Verificando pasta de instalacao"
    if [ ! -d "$dirDefault" ]; then
        mkdir $dirDefault
    fi
    echo
    echo "01.2 Verificando Ferramentas"
    $comando_instalar install -y wget unzip curl &> /dev/null
# <=== Fim STEP01 ===>

# <=== Inicio STEP02 ===>
    clear
    echo "02 SERVIDOR WEB XAMPP..."
    echo
    echo "02.1 Verificando Instalacao XAMPP"
    if [ -d "$dirXampp" ]
    then
        echo "* XAMPP ja esta instalado."
    else
        echo "* Por favor aguarde, baixando instalador."
        wget --quiet --show-progress "$urlXampp" -O $dirDefault/xampp-installer.run
        echo
        echo "* Por favor aguarde, a instalacao pode levar ate 5 min."
        chmod +x $dirDefault/xampp-installer.run
        sudo $dirDefault/xampp-installer.run --mode unattended
        echo
        echo "* Por favor aguarde, instalando Extensões PHP"
        $comando_instalar install -y php-curl php-gd php-zip php-xml &> /dev/null
        $dirXampp/lampp restart
    fi
    echo
    echo "02.2 Verificando Inicializado com o sistema"
    if [ -d "/etc/init.d/start_xampp" ]
    then
        echo "* XAMPP ja inicia com o sistema"
    else
        echo "[Unit]"> /etc/systemd/system/xampp.service
        echo "Description=XAMPP Control Panel">> /etc/systemd/system/xampp.service
        echo "[Service]">> /etc/systemd/system/xampp.service
        echo "ExecStart=/opt/lampp/lampp start">> /etc/systemd/system/xampp.service
        echo "ExecStop=/opt/lampp/lampp stop">> /etc/systemd/system/xampp.service
        echo "Type=forking">> /etc/systemd/system/xampp.service
        echo "[Install]">> /etc/systemd/system/xampp.service
        echo "WantedBy=multi-user.target">> /etc/systemd/system/xampp.service
        sudo systemctl daemon-reload
        sudo systemctl enable xampp.service
        echo "* Configurado inicializacao automatica"
    fi
# <=== Fim STEP02 ===>

#  <=== Inicio STEP03 ===>
    clear
    echo "03 INSTALACAO SISTEMA MAP-OS..."
    echo "03.1 Verificando MapOS GitHUB"
    if [ -d "$dirHtdocs/mapos" ]
    then
        echo "* Map-OS presente no sistema."
    else
        echo "* Baixando a ultima versao do projeto."
        wget --quiet --show-progress -O $dirDefault/MapOS.zip $(curl -s https://api.github.com/repos/RamonSilva20/mapos/releases/latest | grep "zipball_url" | awk -F\" '{print $4}')
        echo
        echo "* Extraindo projeto."
        unzip -q $dirDefault/MapOS.zip -d $dirHtdocs/
        mv -i $dirHtdocs/RamonSilva20-mapos-* $dirHtdocs/mapos
        echo
        echo "* Atribuindo permissões."
        sudo chmod 777 $dirHtdocs/mapos/updates/
        sudo chmod 777 $dirHtdocs/mapos/index.php
        sudo chmod 777 $dirHtdocs/mapos/application/config/config.php
        sudo chmod 777 $dirHtdocs/mapos/application/config/database.php
        echo
        echo "* Criando banco de dados."
        $dirMySQL -u root -e "CREATE DATABASE mapos;"
    fi
# <=== Fim STEP03 ===>

# <=== Inicio STEP04 ===>
    clear
    echo "04 COMPLEMENTO COMPOSER..."
    echo
    echo "04.1 Executando instalador COMPOSER"
    if command -v composer &> /dev/null
    then
        echo "* Composer ja esta instalado."
    else
        echo "* Instalando Composer"
        sudo apt install composer -y &> /dev/null
    fi
    echo
    echo "04.2 Verificando complemento"
    if [ -f "$dirHtdocs/mapos/application/vendor" ]
    then
        echo "* Complementos ja instalados."
    else
        echo "* Instalando complementos."
        cd $dirHtdocs/mapos
        composer install --no-dev -n &> /dev/null
    fi
# <=== Fim STEP04 ===>

# <=== Inicio STEP05 ===>
    clear
    echo "05 CONFIGURANDO MAPOS..."
    echo
    echo "05.1 Configurar MapOS (Browser)"
    echo "Acesse o MapOS via Browser"
    echo "Insira as configurações abaixo:"
    echo
    echo "Host: localhost (ou IP/Dominio)"
    echo "Usuario: root"
    echo "Senha: \"Em Branco\""
    echo "Banco de Dados: mapos"
    echo "URL: http://localhost/mapos (ou IP/Dominio)"
    read -n 1 -s -r -p "Pressione qualquer tecla para continuar..."
# <=== Inicio STEP05 ===>

# <=== Inicio STEP06 ===>
    clear
    echo  "************************************************"
    echo  "****     CONFIGURAÇÕES PERSONALIZADAS       ****"
    echo  "************************************************"

    # <=== Inicio Configuracao de Email ===>
        echo
        read -p "Gostaria de configurar os dados de e-mail? (S/N): " resposta
        if [ "$resposta" = "N" ] || [ "$resposta" = "n" ]; then
            echo "* Dados de Email nao alterado."
        elif [ "$resposta" = "S" ] || [ "$resposta" = "s" ]; then
            echo
            read -p "Informe o Protocolo (Padrao: SMTP): " protocolo
            read -p "Informe o endereco do Host SMTP (Ex: smtp.seudominio.com): " hostsmtp
            read -p "Informe a Criptografia (SSL/TLS): " criptografia
            read -p "Informe a Porta (Ex: 587): " porta
            read -p "Informe o Email (Ex: nome@seudominio.com): " email
            read -p "Informe a Senha (****): " senha

            sed -i "s/\$config\['protocol'\].*/\$config\['protocol'\] = '$protocolo';/" $dirHtdocs/mapos/application/config/email.php
            sed -i "s/\$config\['smtp_host'\].*/\$config\['smtp_host'\] = '$hostsmtp';/" $dirHtdocs/mapos/application/config/email.php
            sed -i "s/\$config\['smtp_crypto'\].*/\$config\['smtp_crypto'\] = '$criptografia';/" $dirHtdocs/mapos/application/config/email.php
            sed -i "s/\$config\['smtp_port'\].*/\$config\['smtp_port'\] = '$porta';/" $dirHtdocs/mapos/application/config/email.php
            sed -i "s/\$config\['smtp_user'\].*/\$config\['smtp_user'\] = '$email';/" $dirHtdocs/mapos/application/config/email.php
            sed -i "s/\$config\['smtp_pass'\].*/\$config\['smtp_pass'\] = '$senha';/" $dirHtdocs/mapos/application/config/email.php
            echo
            echo "* Dados de Email alterados com sucesso."
        fi
    # <=== Fim Configuracao de Email ===>

    # <=== Inicio Configuracao da Cron ===>
        echo
        read -p "Gostaria de ativar disparo automatico de Emails? (S/N): " resposta
        if [ "$resposta" = "N" ] || [ "$resposta" = "n" ]; then
            echo "* Nao configurado disparo automatico."
        elif [ "$resposta" = "S" ] || [ "$resposta" = "s" ]; then
            echo "* Disparo automatico configurado com sucesso."
            (crontab -l ; echo "*/2 * * * * php $dirHtdocs/mapos/index.php email/process") | crontab -
            (crontab -l ; echo "*/5 * * * * php $dirHtdocs/mapos/index.php email/retry") | crontab -
        fi
    # <=== Fim Configuracao da Cron ===>

    # <=== Inicio Configuracao da Cron ===>
    echo
    read -p "Gostaria de alterar o numero da primeira OS? (S/N): " resposta
    if [ "$resposta" = "N" ] || [ "$resposta" = "n" ]; then
            echo "* Nao alterado valor da primeira OS."
    elif [ "$resposta" = "S" ] || [ "$resposta" = "s" ]; then
        read -p "Informe o numero (Padrao: 1):" nOS
        $dirMySQL -u root -e "USE mapos; ALTER TABLE os AUTO_INCREMENT=$nOS;"
        echo "* Número da próxima OS alterado para $nOS"
    fi
    # <=== Fim Configuracao da Cron ===>
# <=== Fim STEP06 ===>

# Mensagem de status
clear
echo  "************************************************"
echo  "****    MAPOS CONFIGURADO COM SUCESSO       ****"
echo  "************************************************"
sleep 2
rm -rf $dirDefault
exit 0
