@ECHO OFF
IF EXIST halo.info GOTO UPDATE 

:INSTALL
.\php\php.exe SMW_install.php
REM Create a file which indicates that Halowiki has been installed once.
test > halo.info
echo Halowiki configured.
GOTO ENDE

:UPDATE
.\php\php.exe htdocs/mediawiki/extensions/SMWHalo/maintenance/SMW_setup.php -t
echo Halowiki updated.

:ENDE
