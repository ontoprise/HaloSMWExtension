@ECHO OFF

REM Windows batch file for creating SMW Halo deploy version with SMW/MW patches

set OUTPUT_DIR=c:\temp\halosmw_ext
IF NOT EXIST %OUTPUT_DIR% goto CREATEDIRS

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%

REM Create directories

:CREATEDIRS
mkdir %OUTPUT_DIR%
mkdir %OUTPUT_DIR%\extensions
mkdir %OUTPUT_DIR%\extensions\DynamicPageList
mkdir %OUTPUT_DIR%\extensions\Glossary
mkdir %OUTPUT_DIR%\extensions\FCKeditor
mkdir %OUTPUT_DIR%\extensions\Lockdown
mkdir %OUTPUT_DIR%\extensions\ParserFunctions
mkdir %OUTPUT_DIR%\extensions\PermissionACL
mkdir %OUTPUT_DIR%\extensions\SemanticCalendar
mkdir %OUTPUT_DIR%\extensions\SemanticForms
mkdir %OUTPUT_DIR%\extensions\StringFunctions
mkdir %OUTPUT_DIR%\extensions\Treeview
mkdir %OUTPUT_DIR%\extensions\Variables
mkdir %OUTPUT_DIR%\includes
mkdir %OUTPUT_DIR%\skins
mkdir %OUTPUT_DIR%\extensions\SMWHalo\includes
mkdir %OUTPUT_DIR%\extensions\SMWHalo\bin
mkdir %OUTPUT_DIR%\extensions\SMWHalo\specials\SMWUploadConverter

REM patch files
xcopy ..\..\..\patches\includes %OUTPUT_DIR%\includes /Y /EXCLUDE:excludeForExt.dat
xcopy ..\..\..\patches\skins %OUTPUT_DIR%\skins /S /Y /EXCLUDE:excludeForExt.dat

REM copy extensions
xcopy ..\..\DynamicPageList %OUTPUT_DIR%\extensions\DynamicPageList /S /EXCLUDE:excludeForExt.dat /Y
xcopy ..\..\Glossary %OUTPUT_DIR%\extensions\Glossary /S /EXCLUDE:excludeForExt.dat /Y
xcopy ..\..\FCKeditor %OUTPUT_DIR%\extensions\FCKeditor /S /EXCLUDE:excludeForExt.dat /Y
xcopy ..\..\Lockdown %OUTPUT_DIR%\extensions\Lockdown /S /EXCLUDE:excludeForExt.dat /Y
xcopy ..\..\ParserFunctions %OUTPUT_DIR%\extensions\ParserFunctions /S /EXCLUDE:excludeForExt.dat /Y
xcopy ..\..\PermissionACL %OUTPUT_DIR%\extensions\PermissionACL /S /EXCLUDE:excludeForExt.dat /Y
xcopy ..\..\SemanticCalendar %OUTPUT_DIR%\extensions\SemanticCalendar /S /EXCLUDE:excludeForExt.dat /Y
xcopy ..\..\SemanticForms %OUTPUT_DIR%\extensions\SemanticForms /S /EXCLUDE:excludeForExt.dat /Y
xcopy ..\..\StringFunctions %OUTPUT_DIR%\extensions\StringFunctions /S /EXCLUDE:excludeForExt.dat /Y
xcopy ..\..\Treeview %OUTPUT_DIR%\extensions\Treeview /S /EXCLUDE:excludeForExt.dat /Y
xcopy ..\..\Variables %OUTPUT_DIR%\extensions\Variables /S /EXCLUDE:excludeForExt.dat /Y

xcopy ..\..\LdapAuthentication.php %OUTPUT_DIR%\extensions /Y
xcopy ..\..\Cite.php %OUTPUT_DIR%\extensions /Y
xcopy ..\..\Cite.i18n.php %OUTPUT_DIR%\extensions /Y
xcopy ..\..\Quicktime.php %OUTPUT_DIR%\extensions /Y

xcopy ..\..\..\patches\extensions\SMWHalo %OUTPUT_DIR%\extensions\SMWHalo /Y /S /EXCLUDE:excludeForExt.dat

cd bin
IF EXIST smwplus-1.2-ext.zip del smwplus-ext.zip
7z.exe a -tzip smwplus-1.2-ext.zip %OUTPUT_DIR%\*
cd..

REM Remove temp files

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%

