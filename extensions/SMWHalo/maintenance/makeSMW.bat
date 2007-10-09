@ECHO OFF

REM Windows batch file for creating SMW deploy version 

set OUTPUT_DIR=c:\temp\smw
IF NOT EXIST %OUTPUT_DIR% goto CREATEDIRS

rm -r -f %OUTPUT_DIR%

REM Create directories

:CREATEDIRS
mkdir %OUTPUT_DIR%\extensions\SemanticMediaWiki

REM copy files

xcopy ..\..\SemanticMediaWiki\* %OUTPUT_DIR%\extensions\SemanticMediaWiki /S /EXCLUDE:excludeForSMW.dat /Y

REM Pack SMW

cd bin
IF EXIST smw.zip rm smw.zip
7z.exe a -tzip smw.zip %OUTPUT_DIR%\*
cd..

REM Remove temp files

rm -r -f %OUTPUT_DIR%
