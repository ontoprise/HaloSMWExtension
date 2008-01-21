@ECHO OFF

REM Windows batch file for creating SMW deploy version 

set OUTPUT_DIR=c:\temp\smw
IF NOT EXIST %OUTPUT_DIR% goto CREATEDIRS

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%

REM Create directories

:CREATEDIRS
mkdir %OUTPUT_DIR%\extensions\SemanticMediaWiki

REM copy files

xcopy ..\..\SemanticMediaWiki\* %OUTPUT_DIR%\extensions\SemanticMediaWiki /S /EXCLUDE:excludeForSMW.dat /Y

REM Pack SMW

cd bin
IF EXIST smw-1.0.zip del smw-1.0.zip
7z.exe a -tzip smw-1.0.zip %OUTPUT_DIR%\*
cd..

REM Remove temp files

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%
