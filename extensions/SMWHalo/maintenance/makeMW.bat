@ECHO OFF

REM Windows batch file for creating MW

set OUTPUT_DIR=c:\temp\mw
IF NOT EXIST %OUTPUT_DIR% goto CREATEDIRS

rm -r -f %OUTPUT_DIR%

REM Create directories

:CREATEDIRS
mkdir %OUTPUT_DIR%

REM copy files

xcopy ..\..\..\* %OUTPUT_DIR% /S /EXCLUDE:excludeForMW.dat /Y

REM Pack MW

cd bin
IF EXIST mw.zip rm mw.zip
7z.exe a mw.zip %OUTPUT_DIR%\*
cd..

REM Remove temp files

rm -r -f %OUTPUT_DIR%
