@echo off
IF "%~1"=="" goto help
IF NOT EXIST %1 GOTO notexist
:import
php import.php -f %1 -m %2
goto end

:notexist
echo File %1 does not exist
goto end

:help
echo #
echo "Usage: import <dump file> [ <mode> == 0 (dryrun), 1 (warn, default), 2 (force) ]"
echo #

:end
