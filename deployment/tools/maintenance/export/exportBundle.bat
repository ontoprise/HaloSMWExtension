@echo off

REM
REM Exports a bundle with images
REM
REM Usage: exportBundle.bat <bundle-id> <temporary name>
REM
REM Note: <temporary name> is a unique directory name just used for bundle generation.
REM       It is arbitrary, e.g. 'mybundle'. Then you'll find the created bundle in c:\temp\mybundle
REM 
REM Author: Kai Kühn / ontoprise / 2011
REM

REM IMPORTANT: Make sure zip.exe is in PATH!
SET ZIP=zip

REM Set output dir
SET OUTPUTDIR=C:\TEMP\%2\extensions\%1
IF EXIST %OUTPUTDIR% GOTO dump
mkdir %OUTPUTDIR%
:dump

REM Export bundle
ECHO Export bundle
php export.php --current --output=file:%OUTPUTDIR%/dump.xml -b %1 %3 %4 %5
php exportOntologyBundleDeployDescriptor.php -o %OUTPUTDIR%/deploy.xml -b %1 -d dump.xml %3 %4 %5

REM Zip bundle
ECHO Zip bundle
set OLDDIR=%CD%
cd C:\TEMP\%2
%ZIP% -r %1.zip *
cd %OLDDIR%

REM Remove temp dir
ECHO Remove temporary directory
RMDIR /S /Q C:\TEMP\%2\extensions

:end
ECHO The output file is at: C:\TEMP\%2
