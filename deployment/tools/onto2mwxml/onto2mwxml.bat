@echo off

REM ------ set this variable to the TSC --------
REM --- installation directory before using ----
 
SET TSCDIR=

REM --------------------------------------------

IF ["%TSCDIR%"]==[""] goto installationHint
IF NOT EXIST "%TSCDIR%" goto installationHint2
SET CURRENT=%cd%
cd "%TSCDIR%"
%TSCDIR:~0,2%
"%TSCDIR%\onto2mwxml.exe" %*
IF %ERRORLEVEL% NEQ 0 EXIT %ERRORLEVEL%
CD "%CURRENT%"
GOTO end

:installationHint
echo Please set TSCDIR before using onto2mwxml.
EXIT 1

:installationHint2
echo Path of TSCDIR does not exist. Please set correctly.
EXIT 1

:end