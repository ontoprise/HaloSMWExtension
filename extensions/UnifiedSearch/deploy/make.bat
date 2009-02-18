@ECHO OFF

REM Windows batch file for creating SMW Search extension (SemanticRetrieval extension)

SET VERSION="1.1"

set OUTPUT_DIR=c:\temp\halosearch
IF NOT EXIST %OUTPUT_DIR% goto CREATEDIRS

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%

REM Create directories

:CREATEDIRS
mkdir %OUTPUT_DIR%\extensions\UnifiedSearch

REM copy files

xcopy ..\* %OUTPUT_DIR%\extensions\UnifiedSearch /S /EXCLUDE:exclude.dat /Y

REM Pack Search Extension

cd bin
IF EXIST smwhalo-search-%VERSION%.zip del smwhalo-search-%VERSION%.zip
7z.exe a -tzip smwhalo-search-%VERSION%.zip %OUTPUT_DIR%\*
cd..

REM Remove temp files

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%
