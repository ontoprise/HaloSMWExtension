@ECHO OFF

REM Windows batch file for creating SMW Halo deploy version with SMW/MW patches

set OUTPUT_DIR=c:\temp\halosmw
IF NOT EXIST %OUTPUT_DIR% goto CREATEDIRS

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%

REM Create directories

:CREATEDIRS
mkdir %OUTPUT_DIR%
mkdir %OUTPUT_DIR%\skins\common
mkdir %OUTPUT_DIR%\skins\ontoskin
mkdir %OUTPUT_DIR%\includes

mkdir %OUTPUT_DIR%\extensions\SemanticMediaWiki\includes
mkdir %OUTPUT_DIR%\extensions\SemanticMediaWiki\includes\articlepages
mkdir %OUTPUT_DIR%\extensions\SemanticMediaWiki\includes\storage
mkdir %OUTPUT_DIR%\extensions\SMWHalo
mkdir %OUTPUT_DIR%\extensions\ParserFunctions

REM copy files

xcopy ..\* %OUTPUT_DIR%\extensions\SMWHalo /S /EXCLUDE:excludeForHalo.dat /Y

REM Patches for SMW
xcopy ..\..\SemanticMediaWiki\includes\SMW_QP_Table.php %OUTPUT_DIR%\extensions\SemanticMediaWiki\includes /Y
xcopy ..\..\SemanticMediaWiki\includes\articlepages\SMW_PropertyPage.php %OUTPUT_DIR%\extensions\SemanticMediaWiki\includes\articlepages /Y
xcopy ..\..\SemanticMediaWiki\includes\storage\SMW_SQLStore.php %OUTPUT_DIR%\extensions\SemanticMediaWiki\includes\storage /Y
xcopy ..\..\SemanticMediaWiki\skins\SMW_sorttable.js %OUTPUT_DIR%\extensions\SemanticMediaWiki\skins /Y

REM Patches for MW
xcopy ..\..\..\includes\User.php %OUTPUT_DIR%\includes /Y
xcopy ..\..\..\includes\Namespace.php %OUTPUT_DIR%\includes /Y
xcopy ..\..\..\skins\common\ajax.js %OUTPUT_DIR%\skins\common /Y
xcopy ..\..\..\skins\ontoskin %OUTPUT_DIR%\skins\ontoskin /S /EXCLUDE:excludeForHalo.dat /Y
xcopy ..\..\..\skins\OntoSkin.deps.php %OUTPUT_DIR%\skins /Y
xcopy ..\..\..\skins\OntoSkin.php %OUTPUT_DIR%\skins /Y

REM Additional extensions
xcopy ..\..\ParserFunctions\* %OUTPUT_DIR%\extensions\ParserFunctions /S /Y
xcopy ..\..\Cite.i18n.php %OUTPUT_DIR%\extensions /Y
xcopy ..\..\Cite.php %OUTPUT_DIR%\extensions /Y

REM Pack SMWHalo Extension

cd bin
IF EXIST smwhalo-1.0.zip del smwhalo-1.0.zip
7z.exe a -tzip smwhalo-1.0.zip %OUTPUT_DIR%\*
cd..

REM Remove temp files

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%
