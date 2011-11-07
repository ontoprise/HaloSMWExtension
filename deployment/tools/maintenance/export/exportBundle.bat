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

REM get bundle directory
FOR /f "tokens=*" %%a in (
'php exportOntologyBundleDeployDescriptor.php --stripname %1'
) do (
SET BUNDLEDIR=%%a
)
echo/%%BUNDLEDIR%%=%BUNDLEDIR%


REM Set output dir
SET OUTPUTDIR=C:\TEMP\%BUNDLEDIR%\extensions\%BUNDLEDIR%
IF EXIST %OUTPUTDIR% GOTO dump
mkdir %OUTPUTDIR%
:dump

REM Export bundle
ECHO Export bundle %1
php export.php --current --output=file:%OUTPUTDIR%/dump.xml -b %1 %2 %3 %4 %5
php exportOntologyBundleDeployDescriptor.php -o %OUTPUTDIR%/deploy.xml -b %1 -d dump.xml %2 %3 %4 %5
IF %ERRORLEVEL% NEQ 0 GOTO end

REM Zip bundle
ECHO Zip bundle
%ZIP% a -r C:\TEMP\%BUNDLEDIR%\%BUNDLEDIR%.zip C:\TEMP\%BUNDLEDIR%\*

REM Remove temp dir
ECHO Remove temporary directory
RMDIR /S /Q C:\TEMP\%BUNDLEDIR%\extensions

ECHO The output file is at: C:\TEMP\%BUNDLEDIR%
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