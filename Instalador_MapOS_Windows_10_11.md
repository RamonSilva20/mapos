title Instalador Map-OS Windows v1.6.20230503
@ECHO OFF
cls
ECHO ###########################################################
ECHO # Detectando privilegios...                               #
ECHO ###########################################################
net session >NUL 2>&1
IF not %ERRORLEVEL% == 0 (
   rem //Comandos sem privilegios de Administrador
   ECHO.
   ECHO ###########################################################
   ECHO #Atencao! Sem privilegios de Administrador.               #
   ECHO #Feche o Instalador MAP-OS e execute como Administrador   #
   ECHO ###########################################################
   PAUSE >NUL
   exit
)
SET stepnext=step01
:: <=== Inicio STEP00 ===>
:step00
cls
ECHO  ************************************************
ECHO  *                                              *
ECHO  *           SCRIPT AUTO INSTALADOR             *
ECHO  *    MAP-OS - SISTEMA DE ORDEM DE SERVICO      *
ECHO  *                                              *
ECHO  *                                              *
ECHO  *              Desenvolvido por                *
ECHO  *      Bruno Barreto - Leonardo Bernardi       *
ECHO  *                                              *
ECHO  ************************************************
ECHO  ****       AGRADECEMOS A PREFERENCIA        ****
ECHO  ************************************************
ECHO.
:: <=== Inicio SET Diretorios ===>
SET dirDefault=C:\InstaladorMAPOS
SET urlWget=https://eternallybored.org/misc/wget/1.21.3/32/wget.exe
SET urlXampp="https://sourceforge.net/projects/xampp/files/XAMPP Windows/8.2.4/xampp-windows-x64-8.2.4-0-VS16-installer.exe"
SET urlComposer=https://getcomposer.org/Composer-Setup.exe
SET urlMapOS=https://github.com/RamonSilva20/mapos/archive/refs/heads/master.zip
SET dirXampp=C:\xampp
SET dirHtdocs=C:\xampp\htdocs
SET dirMySQL=C:\xampp\mysql\bin
SET dirPHP=C:\xampp\php
:: <=== Fim SET Diretorios ===>
goto %stepnext%
:: <=== Fim STEP00 ===>

:: <=== Inicio STEP01 ===>
:step01
ECHO 01 BAIXANDO DEPENDENCIAS...
ECHO.
ECHO 01.1 Verificando pasta de instalacao
IF not EXIST %dirDefault% mkdir %dirDefault% >NUL 2>&1
ECHO 01.2 Verificando Wget
IF not EXIST "%dirDefault%\wget.exe" PowerShell -command "& { iwr %urlWget% -OutFile %dirDefault%\wget.exe }" >NUL 2>&1
ECHO 01.3 Verificando Xampp
IF not EXIST "%dirDefault%\xampp.exe" %dirDefault%\wget --quiet --show-progress %urlXampp% -O %dirDefault%\xampp.exe
ECHO 01.4 Verificando Composer
IF not EXIST "%dirDefault%\composer.exe" PowerShell -command "& { iwr %urlComposer% -OutFile %dirDefault%\composer.exe }" >NUL 2>&1
ECHO 01.5 Verificando MapOS GitHUB
IF not EXIST "%dirDefault%\MapOS.zip" PowerShell -command "& { iwr %urlMapOS% -OutFile %dirDefault%\MapOS.zip }" >NUL 2>&1
SET stepnext=step02
goto step00
:: <=== Fim STEP01 ===>

:: <=== Inicio STEP02 ===>
:step02
ECHO 02 SERVIDOR WEB XAMPP...
ECHO.
ECHO 02.1 Executando instalador XAMPP
start /wait %dirDefault%\xampp.exe --mode unattended
ECHO 02.2 Configurando XAMPP
ECHO.>> %dirXampp%\xampp-control.ini
ECHO [Autostart]>> %dirXampp%\xampp-control.ini
ECHO Apache=1 >> %dirXampp%\xampp-control.ini
ECHO MySQL=1 >> %dirXampp%\xampp-control.ini
ECHO 02.3 Ativando Extensoes do PHP
PowerShell -command "&{(Get-Content -Path '%dirPHP%\php.ini') -replace ';extension=gd', 'extension=gd'} | Set-Content -Path '%dirPHP%\php.ini'"
PowerShell -command "&{(Get-Content -Path '%dirPHP%\php.ini') -replace ';extension=zip', 'extension=zip'} | Set-Content -Path '%dirPHP%\php.ini'"
ECHO 02.4 Configurando PHP TimeZone Sao_Paulo
PowerShell -command "&{(Get-Content -Path '%dirPHP%\php.ini') -replace 'date.timezone=Europe/Berlin', 'date.timezone=America/Sao_Paulo'} | Set-Content -Path '%dirPHP%\php.ini'"
ECHO 02.5 Iniciar Apache e MySQL
start %dirXampp%\xampp-control.exe >NUL 2>&1
SET stepnext=step03
goto step00
:: <=== Fim STEP02 ===>

:: <=== Inicio STEP03 ===>
:step03
ECHO 03 INSTALACAO SISTEMA MAP-OS...
ECHO.
ECHO 03.1 Extracao dos arquivos MAP-OS
PowerShell -ExecutionPolicy Bypass -Command "Expand-Archive %dirDefault%\MapOS.zip %dirHtdocs%" -Force
ECHO 03.2 Correcao da Pasta MAP-OS
IF EXIST %dirHtdocs%\mapos-master rename %dirHtdocs%\mapos-master mapos
SET stepnext=step04
goto step00
:: <=== Fim STEP03 ===>

:: <=== Inicio STEP04 ===>
:step04
ECHO 04 COMPLEMENTO COMPOSER...
ECHO.
ECHO 04.1 Executando instalador COMPOSER
START /wait %dirDefault%\composer.exe /SILENT /ALLUSERS
TIMEOUT /T 5 >NUL
ECHO 04.2 Instalacao do complemento COMPOSER
START /I /D %dirHtdocs%\mapos /WAIT PowerShell C:\ProgramData\ComposerSetup\bin\composer install --no-dev
SET stepnext=step05
goto step00
:: <=== Fim STEP04 ===>

:: <=== Inicio STEP05 ===>
:step05
ECHO 05 CONFIGURANDO MAPOS...
ECHO.
ECHO 05.1 Criar Banco de Dados (mapos)
%dirMySQL%\mysql.exe -u"root" -e "create database `mapos`;" >NUL 2>&1
ECHO 05.2 Configurar MapOS (Browser)
ECHO 05.2.1 Insira as configuracoes abaixo:
ECHO.
ECHO Host: localhost
ECHO Usuario: root
ECHO Senha: "Em Branco"
ECHO Banco de Dados: mapos
ECHO URL: http://localhost/mapos
start /B http://localhost/mapos
PAUSE
SET stepnext=stepfim
goto step00
:: <=== Fim STEP05 ===>

:: <=== Inicio STEP FIM ===>
:stepfim
ECHO  ************************************************
ECHO  ****    MAPOS CONFIGURADO COM SUCESSO       ****
ECHO  ************************************************
ECHO.
erase /F /S /Q "%dirDefault%\*.*" >NUL
rmdir /Q /S "%dirDefault%\" >NUL
TIMEOUT /T 10 >NUL
EXIT
:: <=== Inicio STEP FIM ===>