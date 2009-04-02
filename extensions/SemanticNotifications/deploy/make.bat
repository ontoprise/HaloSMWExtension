@ECHO OFF

REM Windows batch file for creating Semantic notifications extension 

SET VERSION="1.0"

set OUTPUT_DIR=c:\temp\halosemnot
IF NOT EXIST %OUTPUT_DIR% goto CREATEDIRS

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%

REM Create directories

:CREATEDIRS
mkdir %OUTPUT_DIR%\extensions\SemanticNotifications

REM copy files

xcopy ..\* %OUTPUT_DIR%\extensions\SemanticNotifications /S /EXCLUDE:exclude.dat /Y

REM Pack SemanticNotifications Extension

cd bin
IF EXIST smwhalo-semnot-%VERSION%.zip del smwhalo-semnot-%VERSION%.zip
7z.exe a -tzip smwhalo-semnot-%VERSION%.zip %OUTPUT_DIR%\*
cd..

REM Remove temp files

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%
