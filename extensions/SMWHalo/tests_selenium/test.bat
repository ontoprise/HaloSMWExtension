@echo off

setlocal 
set wiki_install_dir=C:\DEV\workspace\HaloSMWExtension
set xampp_install_dir=c:\xampp

cd  %wiki_install_dir%\tests\tests_halo
rem init.php -t %wiki_install_dir%\extensions\SMWHalo\tests_selenium -x %xampp_install_dir%
php run-test.php --log-junit %wiki_install_dir%\extensions\SMWHalo\tests_selenium\logs\results.xml %wiki_install_dir%\extensions\SMWHalo\tests_selenium\SeleniumTests.php

endlocal

pause