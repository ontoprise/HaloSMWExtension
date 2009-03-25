@ECHO OFF

REM Windows batch file for creating SMW Rich Media extension 

SET VERSION="1.0"

set OUTPUT_DIR=c:\temp\halodataimport
IF NOT EXIST %OUTPUT_DIR% goto CREATEDIRS

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%

REM Create directories

:CREATEDIRS
mkdir %OUTPUT_DIR%\extensions\RichMedia
mkdir %OUTPUT_DIR%\extensions\SemanticForms

REM copy files

xcopy ..\* %OUTPUT_DIR%\extensions\RichMedia /S /EXCLUDE:exclude.dat /Y
xcopy ..\patches\SemanticForms %OUTPUT_DIR%\extensions\SemanticForms /S /Y
REM Pack Rich Media Extension

cd bin
IF EXIST smwhalo-richmedia-%VERSION%.zip del smwhalo-richmedia-%VERSION%.zip
7z.exe a -tzip smwhalo-richmedia-%VERSION%.zip %OUTPUT_DIR%\*
cd..

REM Remove temp files

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%

