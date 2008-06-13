@ECHO OFF
REM Builds a self-extractable executable of SMWHalo


REM extract wiki
CD bin
7z x smwhalo-1.0.zip -aoa -oc:\temp\haloexe\htdocs\mediawiki * -r
CD ..

REM Build executable
CD bin
IF EXIST halowiki.exe del halowiki.exe
7z a -sfx7z.sfx halowiki.exe c:\temp\haloexe\*
CD ..

REM Remove extracted packages
del /S /Q c:\temp\haloexe\*
rmdir /S /Q c:\temp\haloexe