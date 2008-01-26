@ECHO OFF
REM Builds a self-extractable executable of complete XAMPP/MW/SMW/SMWHalo
REM xampp.zip, mw-1.12beta.zip, smw-1.0.zip, smwhalo-1.0.zip are packed in one executable

REM extract XAMP (only if -noxampp is not specified
IF (%1) == (-noxampp) GOTO SKIPXAMPP 

CD bin
7z x xampp.zip -oc:\temp\haloexe * -r
CD ..

:SKIPXAMPP

REM extract wiki
CD bin
7z x mw-1.12beta.zip -oc:\temp\haloexe\htdocs\mediawiki * -r
7z x smw-1.0.zip -oc:\temp\haloexe\htdocs\mediawiki * -r
7z x smwhalo-1.0.zip -aoa -oc:\temp\haloexe\htdocs\mediawiki * -r
CD ..

REM copy additional files
xcopy install\* c:\temp\haloexe /Y

REM Build executable
CD bin
IF EXIST halowiki.exe del halowiki.exe
7z a -sfx7z.sfx halowiki.exe c:\temp\haloexe\*
CD ..

REM Remove extracted packages
del /S /Q c:\temp\haloexe\*
rmdir /S /Q c:\temp\haloexe
