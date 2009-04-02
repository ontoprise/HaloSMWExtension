REM Builds all extensions

SET CURRENT=%CD%

cd DataImport\deploy
CALL make
cd %CURRENT%

cd RichMedia\deploy
CALL make
cd %CURRENT%

cd SemanticGardening\deploy
CALL make
cd %CURRENT%

cd SemanticNotifications\deploy
CALL make
cd %CURRENT%

cd SMWHalo\deploy
CALL makeSMWHalo
CALL makeExt
cd %CURRENT%

cd UnifiedSearch\deploy
CALL make gpl
cd %CURRENT%
