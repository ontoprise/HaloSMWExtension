@ECHO OFF

REM Windows batch file for creating SMW Data Import extension 

SET VERSION="1.0"

set OUTPUT_DIR=c:\temp\haloautomaticsemanticforms
IF NOT EXIST %OUTPUT_DIR% goto CREATEDIRS

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%

REM Create directories

:CREATEDIRS
mkdir %OUTPUT_DIR%\extensions\AutomaticSemanticForms

REM copy files

xcopy ..\* %OUTPUT_DIR%\extensions\AutomaticSemanticForms /S /EXCLUDE:exclude.dat /Y

REM Pack AutomaticSemanticForms Extension

cd bin
IF EXIST smwhalo-automaticsemanticforms-%VERSION%.zip del smwhalo-automaticsemanticforms-%VERSION%.zip
7z.exe a -tzip smwhalo-automaticsemanticforms-%VERSION%.zip %OUTPUT_DIR%\*
cd..

REM Remove temp files

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%
