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

SetDateSave on
SetDatablockOptimize on
CRCCheck on
SilentInstall normal
BGGradient 000000 800000 FFFFFF
InstallColors FF8080 000030
XPStyle on

InstallDir "$PROGRAMFILES"

CheckBitmap "images\classic-cross.bmp"

LicenseText "GPL-License"
LicenseData "gpl.txt"

RequestExecutionLevel admin

;--------------------------------

Page license
Page components
Page custom showFullInstWithoutXAMPP
Page custom showMWAndSMWUpdate
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
Var CURINSTTYPE

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
   
  ${If} $CURINSTTYPE == 1
  	 Push $R0
	  InstallOptions::dialog $PLUGINSDIR\wikiinst.ini
	  Pop $R0
	  ReadINIStr $PHP "$PLUGINSDIR\wikiinst.ini" "Field 2" "state"
	  ReadINIStr $MYSQLBIN "$PLUGINSDIR\wikiinst.ini" "Field 4" "state"
	  ReadINIStr $DBSERVER "$PLUGINSDIR\wikiinst.ini" "Field 6" "state"
	  ReadINIStr $DBUSER "$PLUGINSDIR\wikiinst.ini" "Field 8" "state"
	  ReadINIStr $DBPASS "$PLUGINSDIR\wikiinst.ini" "Field 10" "state"
	  Pop $R0
  ${Else}
  	  Abort
  ${EndIf}
  
FunctionEnd

Function showMWAndSMWUpdate
  GetCurInstType $CURINSTTYPE
  ${If} $CURINSTTYPE == 4 
  ${OrIf} $CURINSTTYPE == 1
  	Abort
  ${Else}
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
	ExecWait '"$PHP" $INSTDIR\changeLS.php phpInterpreter=$PHP wgDBserver=$DBSERVER wgDBuser=$DBUSER wgDBpassword=$DBPASS \
		smwgIQEnabled=true smwgAllowNewHelpQuestions=true smwgAllowNewHelpQuestions=true \
		keepGardeningConsole=false smwhgEnableLogging=false smwgDeployVersion=true \
		semanticAC=false wgGardeningBotDelay=100 importSMW=1 importSMWPlus=1'
		
	; Create and initialize DB
	ExecWait '"cmd" /C $MYSQLBIN --host=$DBSERVER --user=$DBUSER --password=$DBPASS < "$INSTDIR\installer\createDB.inf"' $0
	ExecWait '"cmd" /C $MYSQLBIN --host=$DBSERVER --user=$DBUSER --password=$DBPASS < "$INSTDIR\installer\halodb.sql"' $1
		
	
FunctionEnd

Function changeConfigForMWUpdate
	StrCpy $R0 $R9
	
	ExecWait '"$PHP" $INSTDIR\changeLS.php phpInterpreter=$PHP \ 
		smwgIQEnabled=true smwgAllowNewHelpQuestions=true smwgAllowNewHelpQuestions=true \
		keepGardeningConsole=false smwhgEnableLogging=false smwgDeployVersion=true \
		semanticAC=false wgGardeningBotDelay=100 importSMW=1 importSMWPlus=1'
		
		
	ExecWait '"$PHP" $INSTDIR\maintenance\update.php'
	ExecWait '"$PHP" $INSTDIR\extensions\SMWHalo\maintenance\SMW_setup.php'
	ExecWait '"$PHP" $INSTDIR\maintenance\SMW_setup.php'
	
	
FunctionEnd

Function changeConfigForSMWUpdate
	ExecWait '"$PHP" $INSTDIR\changeLS.php phpInterpreter=$PHP \ 
		smwgIQEnabled=true smwgAllowNewHelpQuestions=true smwgAllowNewHelpQuestions=true \
		keepGardeningConsole=false smwhgEnableLogging=false smwgDeployVersion=true \
		semanticAC=false wgGardeningBotDelay=100 importSMWPlus=1'
		
	ExecWait '"$PHP" $INSTDIR\maintenance\update.php'
	ExecWait '"$PHP" $INSTDIR\extensions\SMWHalo\maintenance\SMW_setup.php'
	ExecWait '"$PHP" $INSTDIR\maintenance\SMW_setup.php'
	ExecWait '"$PHP" $INSTDIR\maintenance\SMW_refreshData.php'
	ExecWait '"$PHP" $INSTDIR\maintenance\runJobs.php'
	
	MessageBox MB_OK "Installation complete."
FunctionEnd

Function changeConfigForSMWPlusUpdate
			
	ExecWait '"$PHP" $INSTDIR\maintenance\update.php'
	ExecWait '"$PHP" $INSTDIR\extensions\SMWHalo\maintenance\SMW_setup.php'
	ExecWait '"$PHP" $INSTDIR\maintenance\SMW_setup.php'
	ExecWait '"$PHP" $INSTDIR\extensions\SMWHalo\maintenance\SMW_update.php'
	ExecWait '"$PHP" $INSTDIR\maintenance\SMW_unifyTypes.php'
	ExecWait '"$PHP" $INSTDIR\maintenance\SMW_refreshData.php'
	ExecWait '"$PHP" $INSTDIR\maintenance\runJobs.php'
FunctionEnd

; ---- Install sections ---------------

Section "-XAMPP"
  SectionIn 1 
  SetOutPath $INSTDIR
  CreateDirectory "$INSTDIR"
  ;File /r c:Programme\xampp\xampp\*
  CALL changeConfigForFullXAMPP
  MessageBox MB_OK "Installation complete."
SectionEnd

Section "-noXAMPP"
  SectionIn 2 
  SetOutPath $INSTDIR
  CreateDirectory "$INSTDIR"
  ;File /r c:Programme\xampp\xampp\*
  CALL changeConfigForNoXAMPP
  MessageBox MB_OK "Installation complete."
  
SectionEnd

Section "-MediaWiki 1.12"
	SectionIn 3
	CALL changeConfigForMWUpdate
		
SectionEnd

Section "-SMW 1.0"
	SectionIn 3
	IfFileExists $INSTDIR\extensions\SemanticMediaWiki\*.* 0 notexists
  		;File /r /x CVS /x *.zip /x *.exe ..\*
		CALL changeConfigForSMWUpdate
   	
  	notexists:
  		MessageBox MB_OK|MB_ICONEXCLAMATION  "Could not find SMW. Abort here!"	
 		
  
SectionEnd

Section "-SMW+ 1.0"
  SectionIn 4
  SetOutPath $INSTDIR
  
  IfFileExists $INSTDIR\extensions\SMWHalo\*.* 0 notexists
  	CALL changeConfigForSMWPlusUpdate
   	;File /r /x CVS /x *.zip /x *.exe ..\*
   
  notexists:
  	MessageBox MB_OK|MB_ICONEXCLAMATION "Could not find SMW+! Abort here!"	
 	 	
  
SectionEnd


;--------------------------------

; Uninstaller

UninstallText "This will uninstall SMW+. Hit next to continue."
UninstallIcon "${NSISDIR}\Contrib\Graphics\Icons\nsis1-uninstall.ico"

Section "Uninstall"


SectionEnd
