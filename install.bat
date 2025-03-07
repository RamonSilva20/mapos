TITLE Instalador Map-OS Windows 10/11
@ECHO OFF
CLS

ECHO =============================
ECHO Running Admin shell
ECHO =============================

:init
setlocal DisableDelayedExpansion
set cmdInvoke=1
set winSysFolder=System32
set "batchPath=%~0"
for %%k in (%0) do set batchName=%%~nk
set "vbsGetPrivileges=%temp%\OEgetPriv_%batchName%.vbs"
setlocal EnableDelayedExpansion

:checkPrivileges
NET FILE 1>NUL 2>NUL
IF '%errorlevel%' == '0' ( GOTO gotPrivileges ) else ( GOTO getPrivileges )

:getPrivileges
IF '%1'=='ELEV' (ECHO ELEV & shIFt /1 & GOTO gotPrivileges)
ECHO.
ECHO **************************************
ECHO Invoking UAC for Privilege Escalation
ECHO **************************************

ECHO Set UAC = CreateObject^("Shell.Application"^) > "%vbsGetPrivileges%"
ECHO args = "ELEV " >> "%vbsGetPrivileges%"
ECHO For Each strArg in WScript.Arguments >> "%vbsGetPrivileges%"
ECHO args = args ^& strArg ^& " "  >> "%vbsGetPrivileges%"
ECHO Next >> "%vbsGetPrivileges%"

IF '%cmdInvoke%'=='1' GOTO InvokeCmd

ECHO UAC.ShellExecute "!batchPath!", args, "", "runas", 1 >> "%vbsGetPrivileges%"
GOTO ExecElevation.

:InvokeCmd
ECHO args = "/c """ + "!batchPath!" + """ " + args >> "%vbsGetPrivileges%"
ECHO UAC.ShellExecute "%SystemRoot%\%winSysFolder%\cmd.exe", args, "", "runas", 1 >> "%vbsGetPrivileges%"

:ExecElevation
"%SystemRoot%\%winSysFolder%\WScript.exe" "%vbsGetPrivileges%" %*
exit /B

:gotPrivileges
setlocal & cd /d %~dp0
IF '%1'=='ELEV' (del "%vbsGetPrivileges%" 1>nul 2>nul  &  shIFt /1)

::::::::::::::::::::::::::::
::START
::::::::::::::::::::::::::::
REM Run shell as admin (example) - put here code as you like
ECHO %batchName% Arguments: P1=%1 P2=%2 P3=%3 P4=%4 P5=%5 P6=%6 P7=%7 P8=%8 P9=%9
CLS

::::::::::::::::::::::::::::
:: Script desenvolvido por Bruno Barreto e Leonardo Bernardi
:: Versao Instalador: v2.7.2025033
:: Publicado na versao 4.52.0 do MapOS
::::::::::::::::::::::::::::

:: <=== Controle de STEPs ===>
:: stepSET - Definicao de Variaveis
:: etapa0 - Display
:: etapa1 - Boas Vindas
:: etapa2 - Video Tutorial
:: etapa3 - Selecao de Versao
:: etapa4 - Download de Dependências
:: etapa5 - Instalação XAMPP
:: etapa6 - Instalação MAP-OS
:: etapa7 - Instalação Composer
:: etapa8 - Configuração pelo Browser
:: etapa9 - Configuração de dados de E-mail
:: etapa10 - Auto Disparador de E-mail
:: etapa11 - Alterar Número da OS
:: <=== Controle de STEPs ===>

:: <=== Definições de SETs ===>
SET step=0
SET dirDefault=%temp%\InstaladorMAPOS
SET urlWget=https://eternallybored.org/misc/wget/1.21.4/64/wget.exe
SET urlXampp="https://sourceforge.net/projects/xampp/files/XAMPP Windows/8.2.12/xampp-windows-x64-8.2.12-0-VS16-installer.exe"
SET urlComposer=https://getcomposer.org/Composer-Setup.exe
SET dirXampp=C:\xampp
SET dirHtdocs=C:\xampp\htdocs
SET dirMySQL=C:\xampp\mysql\bin
SET dirPHP=C:\xampp\php
SET dirMaposConfig=C:\xampp\htdocs\mapos\application\.env
:: <=== Fim SET Diretorios ===>

:: <=== Inicio Display ===>
:etapa0
CLS
ECHO  **************************************************
ECHO  **************************************************
ECHO  **                                              **
ECHO  **                                              **
ECHO  **                                              **
ECHO  **           SCRIPT AUTO INSTALADOR             **
ECHO  **    MAP-OS - SISTEMA DE ORDEM DE SERVICO      **
ECHO  **            Windows 10/11 x64                 **
ECHO  **                                              **
ECHO  **                                              **
ECHO  **                                              **
ECHO  **************************************************
ECHO  **************************************************
ECHO.
SET /a step+=1
GOTO etapa%step%
:: <=== Fim Display ===>

:: <=== Inicio Boas Vindas ===>
:etapa1
ECHO Ola, seja bem vindo.
ECHO Esse script foi desenvolvido com o intuito de auxiliar na instalacao automatizada do Sistema MAP-OS e os componentes necessarios.
ECHO Reforcamos que nao recomendamos a instalacao em localhost para uso em PRODUCAO, apenas para TESTE ou DESENVOLVIMENTO devido a riscos de perdas de dados e seguranca.
ECHO.
CHOICE /C SN /M "Continuar a instalacao"
IF ERRORLEVEL 2 GOTO etapaNaoAceite
IF ERRORLEVEL 1 GOTO etapa0
:: <=== Fim Boas Vindas ===>

:: <=== Inicio Video Tutorial ===>
:etapa2
CHOICE /C SN /M "# GOSTARIA DE ASSISTIR O VIDEO TUTORIAL"
IF ERRORLEVEL 2 GOTO etapa0
IF ERRORLEVEL 1 start /B https://youtu.be/aZE-LW_YOE4 && GOTO etapa0
:: <=== Fim Video Tutorial ===>

:: <=== Inicio Selecao de Versao ===>
:etapa3
IF EXIST "%dirDefault%\MapOS.zip" GOTO etapa0
ECHO # DESEJA INSTALAR O MAP-OS RELEASE OU MASTER?
ECHO 1- Release (Versao Estavel)
ECHO 2- Master (Versao Desenvolvimento)
ECHO 9- Sair
ECHO.
CHOICE /C 129 /M "Digite uma opcao:"
IF ERRORLEVEL 9 GOTO sair
IF ERRORLEVEL 2 SET downMapos=master && GOTO etapa0
IF ERRORLEVEL 1 SET downMapos=release && GOTO etapa0
GOTO etapa0
:: <=== Fim Selecao de Versao ===>

:: <=== Inicio Download de Dependências ===>
:etapa4
ECHO # BAIXANDO DEPENDENCIAS...
ECHO.
ECHO - Criando diretório de instalacao
IF not EXIST %dirDefault% mkdir %dirDefault% >NUL 2>&1
ECHO - Verificando Wget
IF not EXIST "%dirDefault%\wget.exe" PowerShell -command "& { iwr %urlWget% -OutFile %dirDefault%\wget.exe }" >NUL 2>&1
ECHO - Verificando Xampp
IF not EXIST "%dirDefault%\xampp.exe" %dirDefault%\wget --quiet --show-progress %urlXampp% -O %dirDefault%\xampp.exe
ECHO - Verificando Composer
IF not EXIST "%dirDefault%\composer.exe" PowerShell -command "& { iwr %urlComposer% -OutFile %dirDefault%\composer.exe }" >NUL 2>&1
ECHO - Verificando MapOS GitHUB
IF EXIST "%dirDefault%\MapOS.zip" GOTO etapa0
IF %downMapos%==master (
    ECHO - Baixando a versao MASTER
    PowerShell -command "& { iwr https://github.com/RamonSilva20/mapos/archive/refs/heads/master.zip -OutFile %dirDefault%\MapOS.zip }"
) ELSE (
    ECHO - Baixando a versao RELEASE
    FOR /F "eol= tokens=2 delims=, " %%A IN (' cURL -s https://api.github.com/repos/RamonSilva20/mapos/releases/latest ^| findstr /I /C:"zipball_url" ') DO PowerShell -command "& { iwr %%A -OutFile %dirDefault%\MapOS.zip }"
)
GOTO etapa0
:: <=== Fim Download de Dependências ===>

:: <=== Inicio Instalação XAMPP ===>
:etapa5
ECHO # SERVIDOR WEB XAMPP...
ECHO.
ECHO Executando instalador XAMPP
IF EXIST "%dirXampp%\xampp-control.exe" GOTO etapa0
ECHO * Por favor aguarde, a instalacao pode levar ate 5 min.
START /wait %dirDefault%\xampp.exe --mode unattended --unattendedmodeui minimal
IF %ErrorLevel% GTR 0 ( DEL %dirDefault%\xampp.exe && ECHO Falha na instalacao do XAMPP, efetuando novo download. && SET step=1 && GOTO etapa0 )
ECHO.
ECHO Configurando XAMPP
ECHO.>> %dirXampp%\xampp-control.ini
ECHO [Autostart]>> %dirXampp%\xampp-control.ini
ECHO Apache=1 >> %dirXampp%\xampp-control.ini
ECHO MySQL=1 >> %dirXampp%\xampp-control.ini
ECHO.
ECHO Ativando Extensoes do PHP
PowerShell -command "&{(Get-Content -Path '%dirPHP%\php.ini') -replace ';extension=gd', 'extension=gd'} | Set-Content -Path '%dirPHP%\php.ini'"
PowerShell -command "&{(Get-Content -Path '%dirPHP%\php.ini') -replace ';extension=zip', 'extension=zip'} | Set-Content -Path '%dirPHP%\php.ini'"
ECHO.
ECHO Configurando PHP TimeZone
PowerShell -command "&{(Get-Content -Path '%dirPHP%\php.ini') -replace 'date.timezone=Europe/Berlin', 'date.timezone=America/Sao_Paulo'} | Set-Content -Path '%dirPHP%\php.ini'"
ECHO.
ECHO Reinciando Servicos Apache, MySQL e Xampp
TASKKILL /F /IM httpd.exe /T >NUL 2>&1
TASKKILL /F /IM mysqld.exe /T >NUL 2>&1
TASKKILL /F /IM xampp-control.exe /T >NUL 2>&1
TIMEOUT /T 5 >NUL
start %dirXampp%\xampp-control.exe >NUL 2>&1
GOTO etapa0
:: <=== Fim Instalação XAMPP ===>

:: <=== Inicio Instalação MAP-OS ===>
:etapa6
ECHO # INSTALACAO SISTEMA MAP-OS...
ECHO.
ECHO Extracao dos arquivos MAP-OS
IF EXIST %dirHtdocs%\mapos\index.php GOTO etapa0
PowerShell -ExecutionPolicy Bypass -Command "Expand-Archive %dirDefault%\MapOS.zip %dirHtdocs%" -Force
ECHO.
ECHO Correcao da Pasta MAP-OS
IF EXIST %dirHtdocs%\mapos-master (
    RENAME %dirHtdocs%\mapos-master mapos
) ELSE (
    FOR /F "tokens=4" %%B IN ( ' dir "%dirHtdocs%\" ^| findstr /I /C:"RamonSilva20" ' ) DO IF NOT EXIST %dirHtdocs%\mapos rename %dirHtdocs%\%%B mapos
)
IF not EXIST %dirHtdocs%\mapos\index.php ( DEL %dirDefault%\MapOS.zip && ECHO Falha na extracao do Map-OS, efetuando novo download. && SET step=2 && GOTO etapa0 )
IF EXIST %dirHtdocs%\mapos\.htaccess DEL %dirHtdocs%\mapos\.htaccess
GOTO etapa0
:: <=== Fim Instalação MAP-OS ===>

:: <=== Inicio Instalação Composer ===>
:etapa7
ECHO # COMPLEMENTO COMPOSER...
ECHO.
ECHO Instalando COMPOSER
IF EXIST "%dirXampp%\composer\composer.bat" ( GOTO composerinstall )
CALL %dirDefault%\composer.exe /SILENT /ALLUSERS /DEV="%dirXampp%\composer" >NUL 2>&1
IF %ErrorLevel% GTR 0 ( DEL %dirDefault%\composer.exe && ECHO Falha na execucao do COMPOSER, efetuando novo download. && SET step=2 && GOTO etapa0 )
:composerinstall
ECHO Instalando Composer Install no MAP-OS
IF not EXIST %dirHtdocs%\mapos\application\vendor START /I /WAIT /D %dirHtdocs%\mapos PowerShell %dirXampp%\composer\composer.bat install --ignore-platform-reqs --no-dev
GOTO etapa0
:: <=== Fim Instalação Composer ===>

:: <=== Inicio Configuração pelo Browser ===>
:etapa8
%dirMySQL%\mysql.exe -u "root" -e "create database `mapos`;" >NUL 2>&1
ECHO # CONFIGURANDO MAPOS...
ECHO Clique em "Proximo" e insira os dados abaixo:
ECHO.
ECHO Host: localhost
ECHO Usuario: root
ECHO Senha: "Em Branco"
ECHO Banco de Dados: mapos
ECHO.
ECHO Nome: "Digite seu Nome Completo"
ECHO Email: "Informe seu E-mail para Login"
ECHO Senha: "Insira sua senha para acesso"
ECHO.
ECHO URL: http://localhost/mapos/
TIMEOUT /T 5 >NUL
start /B http://localhost/mapos/install
ECHO.
ECHO Obs: Caso a instalacao nao tenha sido bem sucedida, encerre o script e execute novamente.
CHOICE /C SN /M "A instalacao foi bem sucedida?"
IF ERRORLEVEL 2 SET step=2 && GOTO etapa0
IF ERRORLEVEL 1 ECHO.
CHOICE /C SN /M "Gostaria de realizar a configuracao personalizada? "
IF ERRORLEVEL 2 GOTO etapaFim
IF ERRORLEVEL 1 GOTO etapa0
:: <=== Fim Configuração pelo Browser ===>

:: <=== Inicio Configuração de dados de E-mail ===>
:etapa9
CHOICE /C SN /M "Gostaria de configurar os dados de E-mail?"
IF ERRORLEVEL 2 GOTO etapa0
IF ERRORLEVEL 1 ECHO.
SET /p protocolo=Informe o Protocolo (Padrao: SMTP): 
SET /p hostsmtp=Informe o endereco do Host SMTP (Ex: smtp.seudominio.com): 
SET /p criptografia=Informe a Criptografia (SSL/TLS): 
SET /p porta=Informe a Porta (Ex: 587): 
SET /p email=Informe o Email (Ex: nome@seudominio.com): 
SET /p senha=Informe a Senha (****): 
ECHO.
CHOICE /C SN /M "Confirma a informacoes acima?"
IF ERRORLEVEL 2 SET step=7 && GOTO etapa0
IF ERRORLEVEL 1 ECHO Configurando dados de E-mail
FOR /F %%A IN (' findstr /b /c:"EMAIL_PROTOCOL" "%dirMaposConfig%" ') DO PowerShell -command "&{(Get-Content -Path '%dirMaposConfig%') -replace '%%A', 'EMAIL_PROTOCOL=%protocolo%'} | Set-Content -Path '%dirMaposConfig%'"
FOR /F %%A IN (' findstr /b /c:"EMAIL_SMTP_HOST" "%dirMaposConfig%" ') DO PowerShell -command "&{(Get-Content -Path '%dirMaposConfig%') -replace '%%A', 'EMAIL_SMTP_HOST=%hostsmtp%'} | Set-Content -Path '%dirMaposConfig%'"
FOR /F %%A IN (' findstr /b /c:"EMAIL_SMTP_CRYPTO" "%dirMaposConfig%" ') DO PowerShell -command "&{(Get-Content -Path '%dirMaposConfig%') -replace '%%A', 'EMAIL_SMTP_CRYPTO=%criptografia%'} | Set-Content -Path '%dirMaposConfig%'"
FOR /F %%A IN (' findstr /b /c:"EMAIL_SMTP_PORT" "%dirMaposConfig%" ') DO PowerShell -command "&{(Get-Content -Path '%dirMaposConfig%') -replace '%%A', 'EMAIL_SMTP_PORT=%porta%'} | Set-Content -Path '%dirMaposConfig%'"
FOR /F %%A IN (' findstr /b /c:"EMAIL_SMTP_USER" "%dirMaposConfig%" ') DO PowerShell -command "&{(Get-Content -Path '%dirMaposConfig%') -replace '%%A', 'EMAIL_SMTP_USER=%email%'} | Set-Content -Path '%dirMaposConfig%'"
FOR /F %%A IN (' findstr /b /c:"EMAIL_SMTP_PASS" "%dirMaposConfig%" ') DO PowerShell -command "&{(Get-Content -Path '%dirMaposConfig%') -replace '%%A', 'EMAIL_SMTP_PASS=%senha%'} | Set-Content -Path '%dirMaposConfig%'"
GOTO etapa0
:: <=== Fim Configuração de dados de E-mail ===>

:: <=== Inicio Auto Disparador de E-mail ===>
:etapa10
SCHTASKS /query /FO LIST /TN "MaposEnvioEmail" | findstr /I /C:"MaposEnvioEmail" >NUL 2>&1
IF %ERRORLEVEL% EQU 0 ( GOTO desativarDisparo ) ELSE ( GOTO ativarDisparo )

:desativarDisparo
CHOICE /C SN /M "Disparo automatico de E-mails ja ativado, deseja desativar?"
IF ERRORLEVEL 2 GOTO etapa0
IF ERRORLEVEL 1 SCHTASKS /Delete /TN "MaposEnvioEmail" /F && SCHTASKS /Delete /TN "MaposReenvioEmail" /F
GOTO etapa0

:ativarDisparo
CHOICE /C SN /M "Deseja de ativar disparo automatico de Emails (Agendador de Tarefas do Windows)?"
IF ERRORLEVEL 2 GOTO etapa0
IF ERRORLEVEL 1 ECHO Ativando disparador automatico de E-mails
SET ps=%dirDefault%\schedule.ps1
ECHO $action = New-ScheduledTaskAction '%dirPHP%\php.exe' -Argument 'index.php email/process' -WorkingDirectory '%dirHtdocs%\mapos'>%ps%
ECHO $trigger = New-ScheduledTaskTrigger -AtStartup>>%ps%
ECHO $task = Register-ScheduledTask -TaskName "MaposEnvioEmail" -Description "Comando responsável por verificar e disparar os E-mails pendentes no sistema Mapos. Criado por Bruno Barreto e Leonardo Bernardi" -Trigger $trigger -Action $action -RunLevel Highest>>%ps%
ECHO $task.Triggers.Repetition.Interval= 'PT2M'>>%ps%
PowerShell -command "&Add-Content -Path '%ps%' -Value '$task | Set-ScheduledTask'"
ECHO $action = New-ScheduledTaskAction '%dirPHP%\php.exe' -Argument 'index.php email/retry' -WorkingDirectory '%dirHtdocs%\mapos'>>%ps%
ECHO $trigger = New-ScheduledTaskTrigger -AtStartup>>%ps%
ECHO $task = Register-ScheduledTask -TaskName "MaposReenvioEmail" -Description "Comando responsável por verificar e disparar os E-mails pendentes no sistema Mapos. Criado por Bruno Barreto e Leonardo Bernardi" -Trigger $trigger -Action $action -RunLevel Highest>>%ps%
ECHO $task.Triggers.Repetition.Interval= 'PT5M'>>%ps%
PowerShell -command "&Add-Content -Path '%ps%' -Value '$task | Set-ScheduledTask'"
PowerShell -command "&%ps%"
ECHO Agendador de Tarefas do Windows Configurado com Sucesso!
TIMEOUT /T 3 >NUL
GOTO etapa0
:: <=== Fim Auto Disparador de E-mail ===>

:: <=== Inicio Alterar Número da OS ===>
:etapa11
FOR /F  %%A IN (' %dirMySQL%\mysql.exe -u root -e "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA='mapos' AND TABLE_NAME='os'" --batch --raw --silent ') do ECHO A proxima OS criada sera %%A
CHOICE /C SN /M "Gostaria de alterar o numero da proxima OS?"
IF ERRORLEVEL 2 GOTO etapaFim
IF ERRORLEVEL 1 SET /p nOS=Informe o numero para a proxima OS:
%dirMySQL%\mysql.exe -u "root" -e "use mapos; ALTER TABLE os AUTO_INCREMENT=%nOS%;" >NUL 2>&1
GOTO etapaFim
:: <=== Fim Alterar Número da OS ===>

:: <=== Inicio STEP FIM ===>
:etapaFim
CLS
ECHO  ************************************************
ECHO  ****  SCRIPT DE AUTOINSTALACAO FINALIZADA   ****
ECHO  ****      AGRADECEMOS A PREFERENCIA         ****
ECHO  ************************************************
TIMEOUT /T 5 >NUL
GOTO stepSair
:: <=== Inicio STEP FIM ===>

:: <=== Inicio STEP NAO ACEITE ===>
:etapaNaoAceite
ECHO  ************************************************
ECHO  ****    TERMO DE INSTALACAO NAO ACEITO      ****
ECHO  ****      AGRADECEMOS A PREFERENCIA         ****
ECHO  ************************************************
ECHO.
TIMEOUT /T 5 >NUL
GOTO stepSair
:: <=== Inicio STEP NAO ACEITE ===>

:: <=== Inicio SAIR ===>
:stepSair
GOTO:=EOF
:: <=== Fim SAIR ===>
