@echo off
REM start script
SET PHP=php
%PHP% smwadmin/smwadmin.php %*
IF ERRORLEVEL 1 goto end
IF ERRORLEVEL 2 goto end
IF [%1]==[] goto end
%PHP% smwadmin/smwadmin.php --install
:end