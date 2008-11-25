@ECHO OFF

REM Windows batch file for creating SMW Halo deploy version with SMW/MW patches

SET VERSION="1.4"

set OUTPUT_DIR=c:\temp\halosmw
IF NOT EXIST %OUTPUT_DIR% goto CREATEDIRS

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%

REM Create directories

:CREATEDIRS
mkdir %OUTPUT_DIR%
mkdir %OUTPUT_DIR%\skins\ontoskin
mkdir %OUTPUT_DIR%\skins\ontoskin2

mkdir %OUTPUT_DIR%\extensions\SMWHalo


REM copy files

xcopy ..\* %OUTPUT_DIR%\extensions\SMWHalo /S /EXCLUDE:excludeForHalo.dat /Y

REM Patches for SMW 

REM Patches for MW
REM Namespace.php patch for category renaming must be applied manually.

REM Additional skins

REM ontoskin
xcopy ..\..\..\skins\ontoskin %OUTPUT_DIR%\skins\ontoskin /S /EXCLUDE:excludeForHalo.dat /Y
xcopy ..\..\..\skins\OntoSkin.deps.php %OUTPUT_DIR%\skins /Y
xcopy ..\..\..\skins\OntoSkin.php %OUTPUT_DIR%\skins /Y

REM ontoskin2
xcopy ..\..\..\skins\ontoskin2 %OUTPUT_DIR%\skins\ontoskin2 /S /EXCLUDE:excludeForHalo.dat /Y
xcopy ..\..\..\skins\OntoSkin2.deps.php %OUTPUT_DIR%\skins /Y
xcopy ..\..\..\skins\OntoSkin2.php %OUTPUT_DIR%\skins /Y


REM Pack SMWHalo Extension

cd bin
IF EXIST smwplus-%VERSION%.zip del smwplus-%VERSION%.zip
7z.exe a -tzip smwplus-%VERSION%.zip %OUTPUT_DIR%\*
cd..

REM Remove temp files

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%
