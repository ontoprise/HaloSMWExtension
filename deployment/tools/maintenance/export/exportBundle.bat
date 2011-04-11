@echo off

REM
REM Exports a bundle with images
REM
REM Usage: exportBundle.bat <bundle-id> 
REM
REM Note: You'll find the created bundle in c:\temp\mybundle
REM 
REM Author: Kai Kühn / ontoprise / 2011
REM

IF "%~1"=="" GOTO help

REM IMPORTANT: Make sure 7z.exe is in PATH!
SET ZIP=7za

@%ZIP% > null
IF %ERRORLEVEL% NEQ 0 GOTO install7z

REM Set output dir
SET OUTPUTDIR=C:\TEMP\%1\extensions\%1
IF EXIST %OUTPUTDIR% GOTO dump
mkdir %OUTPUTDIR%
:dump

REM Export bundle
ECHO Export bundle %1
php export.php --current --output=file:%OUTPUTDIR%/dump.xml -b %1 %2 %3 %4 %5
php exportOntologyBundleDeployDescriptor.php -o %OUTPUTDIR%/deploy.xml -b %1 -d dump.xml %3 %4 %5

REM Zip bundle
ECHO Zip bundle
%ZIP% a -r C:\TEMP\%1\%1.zip C:\TEMP\%1\*

REM Remove temp dir
ECHO Remove temporary directory
RMDIR /S /Q C:\TEMP\%1\extensions

ECHO The output file is at: C:\TEMP\%1
GOTO end

:help
echo.
echo Usage: exportBundle.bat bundle-id 
GOTO end

:install7z
echo.
echo 7-zip is required to run this utility [http://www.7-zip.org/]. Make sure it is in the PATH variable.
GOTO end

:end