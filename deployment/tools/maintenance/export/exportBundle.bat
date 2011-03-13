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

IF "%~1"=="" GOTO help
IF "%~2"=="" GOTO help

REM IMPORTANT: Make sure 7z.exe is in PATH!
SET ZIP=7z

@%ZIP% > null
IF %ERRORLEVEL% NEQ 0 GOTO install7z

REM Set output dir
SET OUTPUTDIR=C:\TEMP\%2\extensions\%1
IF EXIST %OUTPUTDIR% GOTO dump
mkdir %OUTPUTDIR%
:dump

REM Export bundle
ECHO Export bundle %1
php export.php --current --output=file:%OUTPUTDIR%/dump.xml -b %1 %3 %4 %5
php exportOntologyBundleDeployDescriptor.php -o %OUTPUTDIR%/deploy.xml -b %1 -d dump.xml %3 %4 %5

REM Zip bundle
ECHO Zip bundle
%ZIP% a -r C:\TEMP\%2\%1.zip C:\TEMP\%2\*

REM Remove temp dir
ECHO Remove temporary directory
RMDIR /S /Q C:\TEMP\%2\extensions

ECHO The output file is at: C:\TEMP\%2
GOTO end

:help
echo.
echo Usage: exportBundle.bat bundle-id temporary-name (arbitrary)
GOTO end

:install7z
echo.
echo 7-zip is required to run this utility [http://www.7-zip.org/]. Make sure it is in the PATH variable.
GOTO end

:end