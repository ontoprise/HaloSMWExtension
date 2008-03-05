; bigtest.nsi
;
; This script attempts to test most of the functionality of the NSIS exehead.

;--------------------------------
!include "LogicLib.nsh"
!include "WordFunc.nsh"
!include "TextFunc.nsh"
!include "FileFunc.nsh"



!ifdef NOCOMPRESS
SetCompress off
!endif

;--------------------------------

Name "Halowiki"
Caption "SMW+ 1.0"
Icon "images\nsis1-install.ico"
OutFile "halowiki.exe"

SetOverwrite try
SetDateSave on
SetDatablockOptimize on
CRCCheck on
SilentInstall normal
BGGradient 000000 800000 FFFFFF
InstallColors FF8080 000030
XPStyle on
ComponentText "" "" " "
InstallDir "$PROGRAMFILES\ontoprise\smwplus\"


DirText $CHOOSEDIRTEXT "" "" ""	
CheckBitmap "images\classic-cross.bmp"

LicenseText "GPL-License"
LicenseData "gpl.txt"

RequestExecutionLevel admin

;--------------------------------

Page license
Page components
Page custom showFullInstWithoutXAMPP checkForFilesWithoutXAMPP
Page custom showMWAndSMWUpdate checkMWAndSMWUpdate
Page directory
Page instfiles

UninstPage uninstConfirm
UninstPage instfiles

;--------------------------------

!ifndef NOINSTTYPES ; only if not defined
  InstType "New (with XAMPP)"
  InstType "New (without XAMPP)"
  InstType "Update from MediaWiki"
  InstType "Update from SMW"
  InstType "Update from SMW+"
  InstType /NOCUSTOM
!endif

AutoCloseWindow false
ShowInstDetails show

;--------------------------------

Var PHP
Var MYSQLBIN
Var DBSERVER
Var DBUSER
Var DBPASS
VAR HTTPD
VAR WIKIPATH
Var CURINSTTYPE
Var CHOOSEDIRTEXT

Function ".onInit"
  InitPluginsDir
  File /oname=$PLUGINSDIR\wikiinst.ini "gui\wikiinst.ini"
  File /oname=$PLUGINSDIR\smwinst.ini "gui\smwinst.ini"
FunctionEnd

Function .onSelChange
	GetCurInstType $CURINSTTYPE
FunctionEnd

Function showFullInstWithoutXAMPP
  GetCurInstType $CURINSTTYPE
  StrCpy $CHOOSEDIRTEXT "Select a directory where to install XAMPP and the wiki" 
  ${If} $CURINSTTYPE == 1
  	  StrCpy $CHOOSEDIRTEXT "Select a directory which is published by your Apache webserver"
  	  Push $R0
	  InstallOptions::dialog $PLUGINSDIR\wikiinst.ini
	  Pop $R0
	  ReadINIStr $PHP "$PLUGINSDIR\wikiinst.ini" "Field 2" "state"
	  ReadINIStr $MYSQLBIN "$PLUGINSDIR\wikiinst.ini" "Field 4" "state"
	  ReadINIStr $DBSERVER "$PLUGINSDIR\wikiinst.ini" "Field 6" "state"
	  ReadINIStr $DBUSER "$PLUGINSDIR\wikiinst.ini" "Field 8" "state"
	  ReadINIStr $DBPASS "$PLUGINSDIR\wikiinst.ini" "Field 10" "state"
	  ReadINIStr $WIKIPATH "$PLUGINSDIR\wikiinst.ini" "Field 12" "state"
	  ReadINIStr $HTTPD "$PLUGINSDIR\wikiinst.ini" "Field 14" "state"
	  Pop $R0
  ${Else}
  	  Abort
  ${EndIf}
  
FunctionEnd

Function showMWAndSMWUpdate
  GetCurInstType $CURINSTTYPE
  
  ; if SMW+ or wiki with/without XAMPP then abort
  ${If} $CURINSTTYPE == 4 
  ${OrIf} $CURINSTTYPE == 1
  ${OrIf} $CURINSTTYPE == 0
   	Abort
  ${Else} 
  	  StrCpy $CHOOSEDIRTEXT "Select your installation directory of MediaWiki"
  	  Push $R0
	  InstallOptions::dialog $PLUGINSDIR\smwinst.ini
	  Pop $R0
	  ReadINIStr $PHP "$PLUGINSDIR\smwinst.ini" "Field 2" "state"
	  Pop $R0	
  ${EndIf}
 
FunctionEnd

Function changeConfigForFullXAMPP
	
	ExecWait '"$INSTDIR\setup_xampp.bat"'
	ExecWait '"$INSTDIR\setup_halowiki.bat"'	 	 
FunctionEnd

Function changeConfigForNoXAMPP
	
	; Set config variables
	ExecWait '"$PHP" $INSTDIR\installer\changeLS.php phpInterpreter=$PHP wgDBserver=$DBSERVER wgDBuser=$DBUSER wgDBpassword=$DBPASS \
		smwgIQEnabled=true smwgAllowNewHelpQuestions=true smwgAllowNewHelpQuestions=true \
		keepGardeningConsole=false smwhgEnableLogging=false smwgDeployVersion=true \
		semanticAC=false wgGardeningBotDelay=100 script-path=$WIKIPATH ls=LocalSettings.php.template'
		
	; Set httpd
	ExecWait '"$PHP" $INSTDIR\installer\changeHttpd.php httpd=$HTTPD wiki-path=$WIKIPATH fs-path=$INSTDIR'
	
	; Create and initialize DB
	ExecWait '"cmd" /C $MYSQLBIN --host=$DBSERVER --user=$DBUSER --password=$DBPASS < "$INSTDIR\installer\createDB.inf"' $0
	ExecWait '"cmd" /C $MYSQLBIN --host=$DBSERVER --user=$DBUSER --password=$DBPASS < "$INSTDIR\installer\halodb.sql"' $1
	
FunctionEnd
	
Function checkForFilesWithoutXAMPP
	ReadINIStr $PHP "$PLUGINSDIR\wikiinst.ini" "Field 2" "state"
	ReadINIStr $MYSQLBIN "$PLUGINSDIR\wikiinst.ini" "Field 4" "state"
	ReadINIStr $DBSERVER "$PLUGINSDIR\wikiinst.ini" "Field 6" "state"
	ReadINIStr $DBUSER "$PLUGINSDIR\wikiinst.ini" "Field 8" "state"
	ReadINIStr $DBPASS "$PLUGINSDIR\wikiinst.ini" "Field 10" "state"
	ReadINIStr $WIKIPATH "$PLUGINSDIR\wikiinst.ini" "Field 12" "state"
	ReadINIStr $HTTPD "$PLUGINSDIR\wikiinst.ini" "Field 14" "state"
	
	IfFileExists $MYSQLBIN 0 notexistsMySQL
	IfFileExists $PHP 0 notexistsPHP
	IfFileExists $HTTPD 0 notexistsHTTPD
	StrLen $0 $DBSERVER
	${If} $0 == 0
		goto specifyDatabase
	${EndIf}
	StrLen $0 $DBUSER
	${If} $0 == 0
		goto specifyUser
	${EndIf}
	StrLen $0 $DBPASS
	${If} $0 == 0
		goto specifyPass
	${EndIf}
	StrLen $0 $WIKIPATH
	${If} $0 == 0
		goto specifyWiki
	${EndIf}
	goto out
	notexistsMySQL:
		MessageBox MB_OK "mysql.exe does not exist!"
		goto aborthere
	notexistsPHP:
		MessageBox MB_OK "php.exe does not exist!"
		goto aborthere
	notexistsHTTPD:
		MessageBox MB_OK "httpd.conf does not exist!"
		goto aborthere
	specifyDatabase:
		MessageBox MB_OK "Database must be specified!"
		goto aborthere 
	specifyUser:
		MessageBox MB_OK "Database user must be specified!"
		goto aborthere 
	specifyPass:
		MessageBox MB_OK "Database password must be specified!"
		goto aborthere 
	specifyWiki:
		MessageBox MB_OK "Wiki path must be specified!"
		goto aborthere 
	aborthere:
		Abort
	out:
	
FunctionEnd

Function changeConfigForMWUpdate
	
	
	ExecWait '"$PHP" $INSTDIR\installer\changeLS.php phpInterpreter=$PHP \ 
		smwgIQEnabled=true smwgAllowNewHelpQuestions=true smwgAllowNewHelpQuestions=true \
		keepGardeningConsole=false smwhgEnableLogging=false smwgDeployVersion=true \
		semanticAC=false wgGardeningBotDelay=100 importSMW=1 importSMWPlus=1 ls=LocalSettings.php'
		
		
	ExecWait '"$PHP" $INSTDIR\maintenance\update.php'
	ExecWait '"$PHP" $INSTDIR\maintenance\SMW_setup.php'
	ExecWait '"$PHP" $INSTDIR\extensions\SMWHalo\maintenance\SMW_setup.php'
	
	
FunctionEnd

Function checkMWAndSMWUpdate
	ReadINIStr $PHP "$PLUGINSDIR\smwinst.ini" "Field 2" "state"
	IfFileExists $PHP 0 notexistsPHP
	goto out
	notexistsPHP:
		MessageBox MB_OK "php.exe does not exist!"
		Abort
	out:
FunctionEnd

Function changeConfigForSMWUpdate
	
	ExecWait '"$PHP" $INSTDIR\installer\changeLS.php phpInterpreter=$PHP \ 
		smwgAllowNewHelpQuestions=true smwgAllowNewHelpQuestions=true \
		keepGardeningConsole=false smwhgEnableLogging=false smwgDeployVersion=true \
		semanticAC=false wgGardeningBotDelay=100 importSMWPlus=1 ls=LocalSettings.php'
		
	ExecWait '"$PHP" $INSTDIR\maintenance\update.php'
	ExecWait '"$PHP" $INSTDIR\maintenance\SMW_setup.php'
	ExecWait '"$PHP" $INSTDIR\extensions\SMWHalo\maintenance\SMW_setup.php'
	ExecWait '"$PHP" $INSTDIR\maintenance\SMW_refreshData.php'
	ExecWait '"$PHP" $INSTDIR\maintenance\runJobs.php'
	
	
FunctionEnd

Function changeConfigForSMWPlusUpdate
	
	ExecWait '"$PHP" $INSTDIR\maintenance\update.php'
	ExecWait '"$PHP" $INSTDIR\maintenance\SMW_setup.php'
	ExecWait '"$PHP" $INSTDIR\extensions\SMWHalo\maintenance\SMW_setup.php'
	ExecWait '"$PHP" $INSTDIR\extensions\SMWHalo\maintenance\SMW_update.php'
	ExecWait '"$PHP" $INSTDIR\maintenance\SMW_unifyTypes.php'
	ExecWait '"$PHP" $INSTDIR\maintenance\SMW_refreshData.php'
	ExecWait '"$PHP" $INSTDIR\maintenance\runJobs.php'
FunctionEnd

; ---- Install sections ---------------

Section "Wiki with XAMPP"
  SectionIn 1 RO
  SetOutPath $INSTDIR
  CreateDirectory "$INSTDIR"
  ;File /r d:\xampp\*
  SetOutPath $INSTDIR\htdocs\mediawiki
  CreateDirectory "$INSTDIR\htdocs\mediawiki"
  File /r /x CVS /x *.zip /x *.exe /x *.cache /x *.settings ..\*
  SetOutPath $INSTDIR
  CALL changeConfigForFullXAMPP
  MessageBox MB_OK "Installation complete."
SectionEnd

Section "Wiki without XAMPP"
  SectionIn 2 RO
  
  SetOutPath $INSTDIR
  CreateDirectory "$INSTDIR"
  File /r /x CVS /x *.zip /x *.exe /x *.cache /x *.settings ..\*
  CALL changeConfigForNoXAMPP
  MessageBox MB_OK "Installation complete. Please restart Apache."
  
SectionEnd

Section "Wiki update"
	SectionIn 3 RO
	SetOutPath $INSTDIR
	IfFileExists $INSTDIR\LocalSettings.php 0 notexists
		copy:
		IfFileExists $INSTDIR\AdminSettings.php 0 as_noexists
		File /r /x CVS /x *.zip /x *.exe /x *.cache /x *.settings ..\*
		CALL changeConfigForMWUpdate
		goto out
	notexists:
		MessageBox MB_OK|MB_ICONEXCLAMATION  "Could not find Mediawiki. Abort here!"
		goto out
	as_noexists:
		MessageBox MB_OK|MB_ICONSTOP  "Could not find AdminSettings.php. Please create and continue afterwards."
		goto copy
	out:		
SectionEnd

Section "Update SMW 1.0"
	SectionIn 4 RO
	SetOutPath $INSTDIR
	IfFileExists $INSTDIR\extensions\SemanticMediaWiki\*.* 0 notexists
  		File /r /x CVS /x *.zip /x *.exe /x *.cache /x *.settings ..\*
		CALL changeConfigForSMWUpdate
   		goto out
  	notexists:
  		MessageBox MB_OK|MB_ICONEXCLAMATION  "Could not find SMW. Abort here!"	
 		
  	out:
SectionEnd

Section "Update SMW+ 1.0"
  SectionIn 5 RO
  SetOutPath $INSTDIR
  
  IfFileExists $INSTDIR\extensions\SMWHalo\*.* 0 notexists
  	CALL changeConfigForSMWPlusUpdate
    File /r /x CVS /x *.zip /x *.exe /x *.cache /x *.settings ..\*
   	goto out
  notexists:
  	MessageBox MB_OK|MB_ICONEXCLAMATION "Could not find SMW+! Abort here!"	
  out: 	
  
SectionEnd


;--------------------------------

; Uninstaller

UninstallText "This will uninstall SMW+. Hit next to continue."
UninstallIcon "${NSISDIR}\Contrib\Graphics\Icons\nsis1-uninstall.ico"

Section "Uninstall"


SectionEnd
