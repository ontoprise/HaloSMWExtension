REM Builds all extensions

SET CURRENT=%CD%
SET OUT=c:\temp\alldeploys

IF NOT EXIST %OUT% goto CREATEDIRS

del /S /Q %OUT%\*
rmdir /S /Q %OUT%

REM Create directories

:CREATEDIRS
mkdir %OUT%

cd SemanticGardening\deploy
CALL make
xcopy bin\*.zip %OUT%
cd %CURRENT%

cd DataImport\deploy
CALL make
xcopy bin\*.zip %OUT%
cd %CURRENT%

cd RichMedia\deploy
CALL make
xcopy bin\*.zip %OUT%
cd %CURRENT%

cd SemanticNotifications\deploy
CALL make
xcopy bin\*.zip %OUT%
cd %CURRENT%

cd SMWHalo\deploy
CALL makeSMWHalo
CALL makeExt
xcopy bin\*.zip %OUT%
cd %CURRENT%

cd UnifiedSearch\deploy
CALL make gpl
xcopy bin\*.zip %OUT%
cd %CURRENT%

echo All deployable zips are located in %OUT%

