@echo off

REM --- set this variable before using ---
 
SET TSCDIR=

REM --------------------------------------

SET CURRENT=%cd%
cd %TSCDIR%
%TSCDIR%\onto2mwxml.exe %*
IF %ERRORLEVEL% NEQ 0 EXIT %ERRORLEVEL%
CD %CURRENT%