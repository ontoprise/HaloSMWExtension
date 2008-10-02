@echo off
REM run bots

cd..
cd..
cd maintenance
php SMW_startBot.php -b smw_consistencybot
php SMW_startBot.php -b smw_anomaliesbot -p "CATEGORY_NUMBER_ANOMALY=Check%20number%20of%20sub%20categories,CATEGORY_LEAF_ANOMALY=Check%20for%20category%20leafs,CATEGORY_RESTRICTION="
php SMW_startBot.php -b smw_undefinedentitiesbot
php SMW_startBot.php -b smw_missingannotationsbot

REM compare results with saved logs
cd..
cd tests\gardening
IF EXIST %1 del %1
php SMW_compareLog.php -l logs -b smw_consistencybot >> %1
php SMW_compareLog.php -l logs -b smw_anomaliesbot >> %1
php SMW_compareLog.php -l logs -b smw_undefinedentitiesbot >> %1
php SMW_compareLog.php -l logs -b smw_missingannotationsbot >> %1