@echo off

REM Run bots with no log page in order to not modify database
cd..
cd..
cd maintenance
php SMW_startBot.php -b smw_consistencybot -nolog
php SMW_startBot.php -b smw_anomaliesbot -nolog -p "CATEGORY_NUMBER_ANOMALY=Check%20number%20of%20sub%20categories,CATEGORY_LEAF_ANOMALY=Check%20for%20category%20leafs,CATEGORY_RESTRICTION="
php SMW_startBot.php -b smw_undefinedentitiesbot -nolog 
php SMW_startBot.php -b smw_missingannotationsbot -nolog -p "MA_PART_OF_NAME=,MA_CATEGORY_RESTRICTION="

REM compare results with saved logs
cd..
cd tests\gardening
IF EXIST test.log del test.log
php SMW_compareLog.php -l logs -b smw_consistencybot >> test.log
php SMW_compareLog.php -l logs -b smw_anomaliesbot >> test.log
php SMW_compareLog.php -l logs -b smw_missingannotationsbot >> test.log
php SMW_compareLog.php -l logs -b smw_undefinedentitiesbot >> test.log

:end
