@echo off
REM Params for this script:
REM %1 Install dir of wiki
REM %2 dbuser
REM %3 dbpass
REM %4 dbname
REM %5 dbdump file

if [%1]==[] GOTO End
if [%2]==[] GOTO End
if [%3]==[] GOTO End
if [%4]==[] GOTO End
if [%5]==[] GOTO End

@echo on

%1\mysql\bin\mysql.exe -u %2 --password=%3 --execute="create database if not exists %4"
%1\mysql\bin\mysql.exe -u %2 --password=%3 %4 <%5

:End