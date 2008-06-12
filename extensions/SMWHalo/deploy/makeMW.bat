@ECHO OFF

REM Windows batch file for creating MW

set OUTPUT_DIR=c:\temp\mw
IF NOT EXIST %OUTPUT_DIR% goto CREATEDIRS

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%

REM Create directories

:CREATEDIRS
mkdir %OUTPUT_DIR%

REM copy files

xcopy ..\..\..\* %OUTPUT_DIR% /S /EXCLUDE:excludeForMW.dat /Y

REM remove LocalSettings.php and AdminSettings.php
del %OUTPUT_DIR%\LocalSettings.php
del %OUTPUT_DIR%\AdminSettings.php

REM Remove image directory content
del /S /Q %OUTPUT_DIR%\images\*

REM create extensions dir
mkdir %OUTPUT_DIR%\extensions

REM Pack MW

cd bin
IF EXIST mw-1.12beta.zip del mw-1.12beta.zip
7z.exe a -tzip mw-1.12beta.zip %OUTPUT_DIR%\*
cd..

REM Remove temp files

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%
