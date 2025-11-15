@echo off

:: ==================================================================
:: INÍCIO - Bloco de Verificação de Administrador
:: ==================================================================
REM Verifica se o script já tem privilégios de admin
>nul 2>&1 "%SYSTEMROOT%\system32\cacls.exe" "%SYSTEMROOT%\system32\config\system"

REM Se o comando acima FALHAR (errorlevel 1), não é admin.
if '%errorlevel%' NEQ '0' (
    echo.
    echo Solicitando privilegios de administrador...
    echo.
    
    REM Re-inicia este mesmo script usando PowerShell para pedir elevacao (UAC)
    powershell -Command "Start-Process '%~f0' -Verb RunAs"
    
    rem Fecha a janela atual (não-admin)
    exit
)
:: Se chegou aqui, JÁ É ADMIN. O script continua abaixo.
:: ==================================================================
:: FIM - Bloco de Verificação de Administrador
:: ==================================================================


rem Define o título da janela do servidor
title Servidor DVA (REDE)

echo ===================================================
echo   INICIANDO O SERVIDOR DO SISTEMA DE CONTROLE DVA
echo   (Executando como Servidor de Rede)
echo ===================================================
echo.
echo ATENCAO: NAO FECHE ESTA JANELA!
echo Ela mantem o sistema no ar para toda a escola.
echo.
echo Outros computadores podem acessar o sistema em:
echo http://192.168.0.110:8000/login.php
echo.

rem %~dp0 é um comando que pega o caminho da pasta atual (onde o Iniciar.bat está)
set PHP_EXE=%~dp0\php\php.exe
set WWW_ROOT=%~dp0\sistema_dva
set ROUTER_SCRIPT=%~dp0\sistema_dva\router.php

rem Espera 3 segundos para o usuário ler a mensagem
timeout /t 3 /nobreak > NUL

rem Inicia o servidor PHP para aceitar conexões da rede (0.0.0.0)
"%PHP_EXE%" -S 0.0.0.0:8000 -t "%WWW_ROOT%" "%ROUTER_SCRIPT%"