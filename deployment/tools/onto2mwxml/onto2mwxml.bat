@echo off

REM --- set this variable before using ---
 
SET TSCDIR=

REM --------------------------------------

IF [%TSCDIR%]==[] goto installationHint
SET CURRENT=%cd%
cd %TSCDIR%
%TSCDIR%\onto2mwxml.exe %*
IF %ERRORLEVEL% NEQ 0 EXIT %ERRORLEVEL%
CD %CURRENT%
GOTO end

:installationHint
echo "Please set TSCDIR before using onto2mwxml."
EXIT 1

:end