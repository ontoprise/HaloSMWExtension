@echo off

SET ZIP=%CD%\..\..\zip.exe

REM Set output dir
SET OUTPUTDIR=C:\TEMP\%2\extensions\%1
IF EXIST OUTPUTDIR GOTO dump
mkdir OUTPUTDIR
:dump

REM Export bundle
php export.php --current --output=file:%2/dump.xml -b %1 %3 %4 %5
php exportOntologyBundleDeployDescriptor.php -o %2/deploy.xml -b %1 -d dump.xml %3 %4 %5

REM Zip bundle
set OLDDIR=%CD%
cd C:\TEMP\%2
%ZIP% -r C:\TEMP\%2\$1.zip *
cd %OLDDIR%

REM Remove temp dir
DEL /S C:\TEMP\%2\extensions
RMDIR C:\TEMP\%2\extensions

