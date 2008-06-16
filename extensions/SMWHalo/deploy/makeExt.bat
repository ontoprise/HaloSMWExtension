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
mkdir %OUTPUT_DIR%\extensions\FCKeditor
mkdir %OUTPUT_DIR%\extensions\Lockdown
mkdir %OUTPUT_DIR%\extensions\ParserFunctions
mkdir %OUTPUT_DIR%\extensions\PermissionACL
mkdir %OUTPUT_DIR%\extensions\SemanticCalendar
mkdir %OUTPUT_DIR%\extensions\SemanticForms
mkdir %OUTPUT_DIR%\extensions\StringFunctions
mkdir %OUTPUT_DIR%\extensions\Treeview
mkdir %OUTPUT_DIR%\extensions\Variables


REM patch files
xcopy ..\..\..\patches %OUTPUT_DIR% /S /EXCLUDE:excludeForHalo.dat /Y

REM copy extensions
xcopy ..\..\DynamicPageList %OUTPUT_DIR%\extensions\DynamicPageList /S /EXCLUDE:excludeForHalo.dat /Y
xcopy ..\..\FCKeditor %OUTPUT_DIR%\extensions\FCKeditor /S /EXCLUDE:excludeForHalo.dat /Y
xcopy ..\..\Lockdown %OUTPUT_DIR%\extensions\Lockdown /S /EXCLUDE:excludeForHalo.dat /Y
xcopy ..\..\ParserFunctions %OUTPUT_DIR%\extensions\ParserFunctions /S /EXCLUDE:excludeForHalo.dat /Y
xcopy ..\..\PermissionACL %OUTPUT_DIR%\extensions\PermissionACL /S /EXCLUDE:excludeForHalo.dat /Y
xcopy ..\..\SemanticCalendar %OUTPUT_DIR%\extensions\SemanticCalendar /S /EXCLUDE:excludeForHalo.dat /Y
xcopy ..\..\SemanticForms %OUTPUT_DIR%\extensions\SemanticForms /S /EXCLUDE:excludeForHalo.dat /Y
xcopy ..\..\StringFunctions %OUTPUT_DIR%\extensions\StringFunctions /S /EXCLUDE:excludeForHalo.dat /Y
xcopy ..\..\Treeview %OUTPUT_DIR%\extensions\Treeview /S /EXCLUDE:excludeForHalo.dat /Y
xcopy ..\..\Variables %OUTPUT_DIR%\extensions\Variables /S /EXCLUDE:excludeForHalo.dat /Y

xcopy ..\..\LdapAuthentication.php %OUTPUT_DIR%\extensions /Y
xcopy ..\..\Cite.php %OUTPUT_DIR%\extensions /Y
xcopy ..\..\Cite.i18n.php %OUTPUT_DIR%\extensions /Y
xcopy ..\..\Quicktime.php %OUTPUT_DIR%\extensions /Y

cd bin
IF EXIST smwplus-ext.zip del smwplus-ext.zip
7z.exe a -tzip smwplus-ext.zip %OUTPUT_DIR%\*
cd..

REM Remove temp files

del /S /Q %OUTPUT_DIR%\*
rmdir /S /Q %OUTPUT_DIR%

