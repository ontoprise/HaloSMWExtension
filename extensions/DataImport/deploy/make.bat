@ECHO OFF

REM Windows batch file for creating SMW Data Import extension 

SET VERSION="1.0"

set OUTPUT_DIR=c:\temp\halodataimport
IF NOT EXIST %OUTPUT_DIR% goto CREATEDIRS

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%

REM Create directories

:CREATEDIRS
mkdir %OUTPUT_DIR%\extensions\DataImport

REM copy files

xcopy ..\* %OUTPUT_DIR%\extensions\DataImport /S /EXCLUDE:exclude.dat /Y

REM Pack DataImport Extension

cd bin
IF EXIST smwhalo-dataimport-%VERSION%.zip del smwhalo-dataimport-%VERSION%.zip
7z.exe a -tzip smwhalo-dataimport-%VERSION%.zip %OUTPUT_DIR%\*
cd..

REM Remove temp files

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%
