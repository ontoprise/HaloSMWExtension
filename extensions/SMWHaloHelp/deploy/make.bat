@ECHO OFF

REM Windows batch file for creating SMWHalo Help deploy version

SET VERSION="1.0"

set OUTPUT_DIR=c:\temp\halosmwhelp
IF NOT EXIST %OUTPUT_DIR% goto CREATEDIRS

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%

REM Create directories

:CREATEDIRS
mkdir %OUTPUT_DIR%\extensions\SMWHaloHelp

REM copy files

xcopy ..\* %OUTPUT_DIR%\extensions\SMWHaloHelp /S /EXCLUDE:exclude.dat /Y


REM Pack SMWHalo Help Extension

cd bin
IF EXIST smwhalohelp-%VERSION%.zip del smwhalohelp-%VERSION%.zip
7z.exe a -tzip smwhalohelp-%VERSION%.zip %OUTPUT_DIR%\*
cd..

REM Remove temp files

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%
