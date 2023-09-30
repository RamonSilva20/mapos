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
:: Versao Instalador: v2.4.20230920
:: Publicado na versao 4.41.0 do MapOS
::::::::::::::::::::::::::::

SET stepnext=stepTermos
:: <=== Inicio STEP00 ===>
:step00
CLS
ECHO  **************************************************
ECHO  **************************************************
ECHO  **                                              **
ECHO  **                                              **
ECHO  **                                              **
ECHO  **           SCRIPT AUTO INSTALADOR             **
ECHO  **    MAP-OS - SISTEMA DE ORDEM DE SERVICO      **
ECHO  **             Windows 10/11                    **
ECHO  **                                              **
ECHO  **                                              **
ECHO  **                                              **
ECHO  **************************************************
ECHO  **************************************************
ECHO.
:: <=== Inicio SET Diretorios ===>
SET dirDefault=C:\InstaladorMAPOS
SET urlWget=https://eternallybored.org/misc/wget/1.21.4/32/wget.exe
SET urlXampp="https://sourceforge.net/projects/xampp/files/XAMPP Windows/8.2.4/xampp-windows-x64-8.2.4-0-VS16-installer.exe"
SET urlComposer=https://getcomposer.org/Composer-Setup.exe
SET dirXampp=C:\xampp
SET dirHtdocs=C:\xampp\htdocs
SET dirMySQL=C:\xampp\mysql\bin
SET dirPHP=C:\xampp\php
:: <=== Fim SET Diretorios ===>
GOTO %stepnext%
:: <=== Fim STEP00 ===>

:: <=== Inicio Termos de Aceite ===>
:stepTermos
ECHO AVISO IMPORTANTE!
ECHO.
ECHO Ao seguir com a execucao desse script, voce esta ciente de que caso exista uma instalacao previa do XAMPP ou MySQL no seu sistema, ela podera ser completamente removida. Isso pode resultar na perda permanente de todos os dados contidos nessas instalacoes.
ECHO.
ECHO Portanto, recomendamos que voce faca backup de todos os dados importantes antes de prosseguir com a instalacao. Ao continuar com a instalacao, voce concorda que nao responsabilizara os desenvolvedores do script por quaisquer perdas de dados que possam ocorrer como resultado da remocao dessas instalacoes.
ECHO.
CHOICE /C SN /M "Aceita os termos acima?"
IF ERRORLEVEL 2 SET stepnext=stepNaoAceite && GOTO step00
IF ERRORLEVEL 1 SET stepnext=step01 && GOTO step00
PAUSE
:: <=== Fim Termos de Aceite ===>

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
IF not EXIST "%dirDefault%\MapOS.zip" FOR /F "eol= tokens=2 delims=, " %%A IN (' cURL -s https://api.github.com/repos/RamonSilva20/mapos/releases/latest ^| findstr /I /C:"zipball_url" ') DO PowerShell -command "& { iwr %%A -OutFile %dirDefault%\MapOS.zip }"
SET stepnext=step02
GOTO step00
:: <=== Fim STEP01 ===>

:: <=== Inicio STEP02 ===>
:step02
ECHO 02 SERVIDOR WEB XAMPP...
ECHO.
ECHO 02.1 Executando instalador XAMPP
IF EXIST "%dirXampp%\xampp-control.exe" SET stepnext=step03 && GOTO step00
ECHO * Por favor aguarde, a instalacao pode levar ate 5 min.
START /wait %dirDefault%\xampp.exe --mode unattended
IF %ErrorLevel% GTR 0 ( DEL %dirDefault%\xampp.exe && ECHO Falha na instalacao do XAMPP, efetuando novo download. && SET stepnext=step01 && GOTO step00 )
ECHO 02.2 Configurando XAMPP
ECHO.>> %dirXampp%\xampp-control.ini
ECHO [Autostart]>> %dirXampp%\xampp-control.ini
ECHO Apache=1 >> %dirXampp%\xampp-control.ini
ECHO MySQL=1 >> %dirXampp%\xampp-control.ini
ECHO 02.3 Ativando Extensoes do PHP
PowerShell -command "&{(Get-Content -Path '%dirPHP%\php.ini') -replace ';extension=gd', 'extension=gd'} | Set-Content -Path '%dirPHP%\php.ini'"
PowerShell -command "&{(Get-Content -Path '%dirPHP%\php.ini') -replace ';extension=zip', 'extension=zip'} | Set-Content -Path '%dirPHP%\php.ini'"
ECHO 02.4 Configurando PHP TimeZone
PowerShell -command "&{(Get-Content -Path '%dirPHP%\php.ini') -replace 'date.timezone=Europe/Berlin', 'date.timezone=America/Fortaleza'} | Set-Content -Path '%dirPHP%\php.ini'"
ECHO 02.5 Iniciar Apache e MySQL
TASKKILL /F /IM httpd.exe /T >NUL 2>&1
TASKLIST | find "mysqld.exe" >NUL 2>&1
TASKKILL /F /IM xampp-control.exe /T >NUL 2>&1
TIMEOUT /T 5 >NUL
start %dirXampp%\xampp-control.exe >NUL 2>&1
SET stepnext=step03
GOTO step00
:: <=== Fim STEP02 ===>

:: <=== Inicio STEP03 ===>
:step03
ECHO 03 INSTALACAO SISTEMA MAP-OS...
ECHO.
ECHO 03.1 Extracao dos arquivos MAP-OS
IF EXIST %dirHtdocs%\mapos\index.php SET stepnext=step04 && GOTO step00
PowerShell -ExecutionPolicy Bypass -Command "Expand-Archive %dirDefault%\MapOS.zip %dirHtdocs%" -Force
IF %ErrorLevel% GTR 0 ( DEL %dirDefault%\MapOS.zip && ECHO Falha na extracao do Map-OS, efetuando novo download. && SET stepnext=step01 && GOTO step00 )
ECHO 03.2 Correcao da Pasta MAP-OS
FOR /F "tokens=4" %%B IN ( ' dir "%dirHtdocs%\" ^| findstr /I /C:"RamonSilva20" ' ) DO IF NOT EXIST %dirHtdocs%\mapos rename %dirHtdocs%\%%B mapos
SET stepnext=step04
GOTO step00
:: <=== Fim STEP03 ===>

:: <=== Inicio STEP04 ===>
:step04
ECHO 04 COMPLEMENTO COMPOSER...
ECHO.
ECHO 04.1 Executando instalador COMPOSER
PowerShell composer --version >NUL 2>&1
IF %ErrorLevel% EQU 0 ( GOTO step04-2 )
START /wait %dirDefault%\composer.exe /SILENT /ALLUSERS
IF %ErrorLevel% GTR 0 ( DEL %dirDefault%\composer.exe && ECHO Falha na execucao do COMPOSER, efetuando novo download. && SET stepnext=step01 && GOTO step00 )
TIMEOUT /T 5 >NUL
:step04-2
ECHO 04.2 Instalacao do complemento COMPOSER
IF NOT EXIST %dirHtdocs%\mapos\application\vendor START /I /D %dirHtdocs%\mapos /WAIT PowerShell C:\ProgramData\ComposerSetup\bin\composer install --no-dev
SET stepnext=step05
GOTO step00
:: <=== Fim STEP04 ===>

:: <=== Inicio STEP05 ===>
:step05
ECHO 05 CONFIGURANDO MAPOS...
ECHO.
ECHO 05.1 Criar Banco de Dados (mapos)
%dirMySQL%\mysql.exe -u "root" -e "create database `mapos`;" >NUL 2>&1
ECHO 05.2 Configurar MapOS (Browser)
ECHO 05.2.1 Insira as configuracoes abaixo:
ECHO.
ECHO Host: localhost
ECHO Usuario: root
ECHO Senha: "Em Branco"
ECHO Banco de Dados: mapos
ECHO URL: http://localhost/mapos
start /B http://localhost/mapos
ECHO.
CHOICE /C SN /M "Configuracao do MapOS Finalizada?"
IF ERRORLEVEL 2 SET stepnext=step05 && GOTO step00
IF ERRORLEVEL 1 SET stepnext=step06 && GOTO step00
:: <=== Fim STEP05 ===>

:: <=== Inicio STEP06 ===>
:step06
CHOICE /C SN /M "Gostaria de configurar os dados de e-mail?"
IF ERRORLEVEL 2 ECHO "* Dados de Email nao alterado." && SET stepnext=step07 && GOTO step00
IF ERRORLEVEL 1 ECHO.
SET /p protocolo=Informe o Protocolo (Padrao: SMTP): 
SET /p hostsmtp=Informe o endereco do Host SMTP (Ex: smtp.seudominio.com): 
SET /p criptografia=Informe a Criptografia (SSL/TLS): 
SET /p porta=Informe a Porta (Ex: 587): 
SET /p email=Informe o Email (Ex: nome@seudominio.com): 
SET /p senha=Informe a Senha (****): 
ECHO.
CHOICE /C SN /M "Confirma a informacoes acima?"
IF ERRORLEVEL 2 ECHO "* Nao configurado disparo automatico." && SET stepnext=step06 && GOTO step00
IF ERRORLEVEL 1 SET dirEmail=%dirHtdocs%\mapos\application\config\email.php
PowerShell -command "&Set-Content -Path '%dirEmail%' -Value '<?php'"
ECHO $config['protocol']         = '%protocolo%';>>%dirEmail%
ECHO $config['smtp_host']        = '%hostsmtp%';>>%dirEmail%
ECHO $config['smtp_crypto']      = '%criptografia%';>>%dirEmail%
ECHO $config['smtp_port']        = %porta%;>>%dirEmail%
ECHO $config['smtp_user']        = '%email%';>>%dirEmail%
ECHO $config['smtp_pass']        = '%senha%';>>%dirEmail%
ECHO $config['validate']         = true;>>%dirEmail%
ECHO $config['mailtype']         = 'html';>>%dirEmail%
ECHO $config['charset']          = 'utf-8';>>%dirEmail%
ECHO $config['newline']          = "\r\n";>>%dirEmail%
ECHO $config['bcc_batch_mode']   = false;>>%dirEmail%
ECHO $config['wordwrap']         = false;>>%dirEmail%
ECHO $config['priority']         = 3;>>%dirEmail%
SET stepnext=step07
GOTO step00
:: <=== Fim STEP06 ===>

:: <=== Inicio STEP07 ===>
:step07
CHOICE /C SN /M "Gostaria de ativar disparo automatico de Emails?"
IF ERRORLEVEL 2 ECHO "* Nao configurado disparo automatico." && SET stepnext=step08 && GOTO step00
IF ERRORLEVEL 1 ECHO.
SET ps=%dirDefault%\schedule.ps1
ECHO $action = New-ScheduledTaskAction 'C:\xampp\php\php.exe' -Argument 'index.php email/process' -WorkingDirectory 'C:\xampp\htdocs\mapos'>%ps%
ECHO $trigger = New-ScheduledTaskTrigger -AtStartup>>%ps%
ECHO $task = Register-ScheduledTask -TaskName "MaposEnvioEmail" -Description "Comando responsável por verificar e disparar os e-mails pendentes no sistema Mapos. Criado por Bruno Barreto e Leonardo Bernardi" -Trigger $trigger -Action $action -RunLevel Highest>>%ps%
ECHO $task.Triggers.Repetition.Interval= 'PT2M'>>%ps%
PowerShell -command "&Add-Content -Path '%ps%' -Value '$task | Set-ScheduledTask'"
ECHO $action = New-ScheduledTaskAction 'C:\xampp\php\php.exe' -Argument 'index.php email/retry' -WorkingDirectory 'C:\xampp\htdocs\mapos'>>%ps%
ECHO $trigger = New-ScheduledTaskTrigger -AtStartup>>%ps%
ECHO $task = Register-ScheduledTask -TaskName "MaposReenvioEmail" -Description "Comando responsável por verificar e disparar os e-mails pendentes no sistema Mapos. Criado por Bruno Barreto e Leonardo Bernardi" -Trigger $trigger -Action $action -RunLevel Highest>>%ps%
ECHO $task.Triggers.Repetition.Interval= 'PT5M'>>%ps%
PowerShell -command "&Add-Content -Path '%ps%' -Value '$task | Set-ScheduledTask'"
PowerShell -command "&%ps%"
ECHO Agendador de Tarefas do Windows Configurada com Sucesso!
TIMEOUT /T 3 >NUL
SET stepnext=step08
GOTO step00
:: <=== Fim STEP07 ===>

:: <=== Inicio STEP08 ===>
:step08
CHOICE /C SN /M "Gostaria de alterar o numero da proxima OS?"
IF ERRORLEVEL 2 echo "* Nao alterado valor da proxima OS." && SET stepnext=stepfim && GOTO step00
IF ERRORLEVEL 1 SET /p nOS=Informe o numero (Padrao: 1): 
%dirMySQL%\mysql.exe -u "root" -e "use mapos; ALTER TABLE os AUTO_INCREMENT=%nOS%;" >NUL 2>&1
SET stepnext=stepfim
GOTO step00
:: <=== Fim STEP08 ===>

:: <=== Inicio STEP FIM ===>
:stepfim
ECHO  ************************************************
ECHO  ****    MAPOS CONFIGURADO COM SUCESSO       ****
ECHO  ************************************************
GOTO StepSair
:: <=== Inicio STEP FIM ===>

:: <=== Inicio STEP NAO ACEITE ===>
:stepNaoAceite
ECHO  ************************************************
ECHO  ****  TERMOS DE INSTALACAO NAO CONFIRMADO   ****
ECHO  ************************************************
ECHO.
TIMEOUT /T 5 >NUL
GOTO StepSair
:: <=== Inicio STEP NAO ACEITE ===>

:: <=== Inicio SAIR ===>
:StepSair
GOTO:=EOF
:: <=== Fim SAIR ===>
