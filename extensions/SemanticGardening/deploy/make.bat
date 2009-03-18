@ECHO OFF

REM Windows batch file for creating SMW Data Import extension 

SET VERSION="1.0"

set OUTPUT_DIR=c:\temp\halosemgard
IF NOT EXIST %OUTPUT_DIR% goto CREATEDIRS

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%

REM Create directories

:CREATEDIRS
mkdir %OUTPUT_DIR%\extensions\SemanticGardening

REM copy files

xcopy ..\* %OUTPUT_DIR%\extensions\SemanticGardening /S /EXCLUDE:exclude.dat /Y

REM Pack SemanticGardening Extension

cd bin
IF EXIST smwhalo-gardening-%VERSION%.zip del smwhalo-gardening-%VERSION%.zip
7z.exe a -tzip smwhalo-gardening-%VERSION%.zip %OUTPUT_DIR%\*
cd..

REM Remove temp files

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%
