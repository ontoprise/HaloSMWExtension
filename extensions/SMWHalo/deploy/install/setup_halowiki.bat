@ECHO OFF

IF (%1) == (-help) GOTO HELP
IF (%1) == (-update) GOTO UPDATE 

:INSTALL
.\php\php.exe SMW_install.php
REM Create a file which indicates that Halowiki has been installed once.
echo Halowiki configured.
GOTO ENDE

:HELP
echo -----------------------------------------------------------------
echo Usage: 
echo For installation: setup_halowiki.bat 
echo For update:       setup_halowiki.bat -update 
echo -----------------------------------------------------------------
GOTO ENDE

:UPDATE
.\php\php.exe htdocs/mediawiki/extensions/SMWHalo/maintenance/SMW_setup.php -t
echo Halowiki updated.

:ENDE
