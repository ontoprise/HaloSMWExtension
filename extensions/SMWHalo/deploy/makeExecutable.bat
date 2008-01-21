@ECHO OFF
REM Builds a self-extractable executable of complete XAMPP/MW/SMW/SMWHalo
REM 7z.exe needs to be in PATH!
REM xampp.zip, mw-1.12beta.zip, smw-1.0.zip, 
REM smwhalo-1.0.zip must be in the same directory as this script

REM extract XAMP (only if -noxampp is not specified
IF NOT (%1) == (-noxampp) 7z x bin/xampp.zip -oc:\temp\haloexe * -r

REM extract wiki
7z x bin/mw-1.12beta.zip -oc:\temp\haloexe\xampp\htdocs\mediawiki * -r
7z x bin/smw-1.0.zip -oc:\temp\haloexe\xampp\htdocs\mediawiki * -r
7z x bin/smwhalo-1.0.zip -aoa -oc:\temp\haloexe\xampp\htdocs\mediawiki * -r

REM copy additional files
xcopy install/* c:\temp\haloexe\xampp /Y

REM Build executable
IF EXIST halowiki.exe del halowiki.exe
7z a -sfx7z.sfx halowiki.exe c:\temp\haloexe\xampp\*

REM Remove extracted packages
del /S /Q c:\temp\haloexe\*
rmdir /S /Q c:\temp\haloexe
