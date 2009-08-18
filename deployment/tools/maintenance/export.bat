@echo off
IF EXIST %2 GOTO dump
mkdir %2
:dump
php export.php --current --output=file:%2/dump.xml -b %1
php exportDesc.php -o %2/deploy.xml -b %1 -d dump.xml
