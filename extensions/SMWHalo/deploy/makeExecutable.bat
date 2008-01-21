@ECHO OFF
REM Builds a self-extractable executable of complete XAMPP/MW/SMW/SMWHalo
REM 7z.exe needs to be in PATH!
REM xampp.zip, mw-1.12beta.zip, smw-1.0beta.zip, 
REM smwhalo-1.0beta.zip must be in the same directory as this script

REM extract XAMP (only if -noxampp is not specified
IF NOT (%1) == (-noxampp) 7z x xampp.zip -oc:\temp\haloexe * -r

REM extract wiki
7z x mw-1.12beta.zip -oc:\temp\haloexe\xampp\htdocs\mediawiki * -r
7z x smw-1.0beta.zip -oc:\temp\haloexe\xampp\htdocs\mediawiki * -r
7z x smwhalo-1.0beta.zip -aoa -oc:\temp\haloexe\xampp\htdocs\mediawiki * -r

REM Build executable
IF EXIST halowiki.exe del halowiki.exe
7z a -sfx7z.sfx halowiki.exe c:\temp\haloexe\xampp\*

REM Remove extracted packages
del /S /Q c:\temp\haloexe\*
rmdir /S /Q c:\temp\haloexe
