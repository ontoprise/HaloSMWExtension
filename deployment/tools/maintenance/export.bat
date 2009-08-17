@echo off
IF EXIST bundle GOTO dump
mkdir bundle
:dump
php export.php --current --output=file:bundle/dump.xml -b %1

