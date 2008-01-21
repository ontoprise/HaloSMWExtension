@ECHO OFF
REM Builds a self-extractable executable of SMWHalo
REM 7z.exe needs to be in PATH!
REM smwhalo-1.0.zip must be in the same directory as this script

REM extract wiki
CD bin
7z x smwhalo-1.0.zip -aoa -oc:\temp\haloexe\xampp\htdocs\mediawiki * -r
CD ..

REM Build executable
IF EXIST halowiki.exe del halowiki.exe
CD bin
7z a -sfx7z.sfx halowiki.exe c:\temp\haloexe\xampp\*
CD ..

REM Remove extracted packages
del /S /Q c:\temp\haloexe\*
rmdir /S /Q c:\temp\haloexe