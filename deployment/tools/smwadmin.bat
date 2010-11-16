@echo off
REM assume PHP is in PATH variable
SET PHP=php

REM Update MW if it was recently updated
IF EXIST ..\..\init$.ext (%PHP% ..\..\maintenance\update.php --quick) ELSE (GOTO runsmwadmin)
DEL ..\..\init$.ext

REM Run SMWAdmin tool
:runsmwadmin
%PHP% smwadmin/smwadmin.php %*
IF ERRORLEVEL 1 goto end
IF ERRORLEVEL 2 goto end
IF [%1]==[] goto end

REM Update MW if it was just updated now
IF EXIST ..\..\init$.ext (%PHP% ..\..\maintenance\update.php --quick) ELSE (GOTO runsmwadmin)
DEL ..\..\init$.ext

%PHP% smwadmin/smwadmin.php --finalize
:end