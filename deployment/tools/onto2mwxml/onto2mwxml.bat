@echo off
cd tsc
onto2mwxml.exe %*
IF %ERRORLEVEL% NEQ 0 EXIT %ERRORLEVEL%
cd ..
