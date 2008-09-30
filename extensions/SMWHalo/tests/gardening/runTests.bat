REM run bots

cd..
cd..
cd maintenance
php SMW_startBot.php -b smw_consistencybot


REM compare results with saved logs
cd..
cd tests\gardening
IF EXIST %1 del %1
php SMW_compareLog.php -l logs -b smw_consistencybot >> %1