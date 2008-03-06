; Halowiki.nsi
;
; This script builds an installer for MediaWiki, SMW, SMW+ and XAMPP

;--------------------------------
!include "MUI2.nsh"
!include "LogicLib.nsh"

!define PRODUCT "SMWPlus"
!define PRODUCT_CAPTION "SMW+"
!define VERSION "1.0"

!define MUI_ABORTWARNING

!define MUI_HEADERIMAGE
!define MUI_HEADERIMAGE_BITMAP "images\header-install.bmp"

!define MUI_WELCOMEFINISHPAGE 
!define MUI_WELCOMEFINISHPAGE_BITMAP "images\wizard-install.bmp"
!define MUI_WELCOMEFINISHPAGE_BITMAP_NOSTRETCH
!define MUI_COMPONENTSPAGE_SMALLDESC

!define MUI_WELCOMEPAGE_TITLE "Welcome to the ${PRODUCT} ${VERSION} Setup Wizard"
!define MUI_WELCOMEPAGE_TEXT "This wizard will guide you through the installation of ${PRODUCT} ${VERSION}."
!define MUI_FINISHPAGE_LINK "Visit the ontoprise website for the latest news"
!define MUI_FINISHPAGE_LINK_LOCATION "http://www.ontoprise.com/"

!ifdef NOCOMPRESS
SetCompress off
!endif

;--------------------------------

Name "${PRODUCT} Version ${VERSION}"
Caption "${PRODUCT_CAPTION} ${VERSION}"
Icon "images\nsis1-install.ico"
OutFile "${PRODUCT}-${VERSION}.exe"

SetOverwrite try
SetDateSave on
SetDatablockOptimize on
CRCCheck on
SilentInstall normal
BGGradient 000000 95F5E2 FFFFFF
InstallColors FF8080 000030
;XPStyle on
ComponentText "" "" " "
InstallDir "$PROGRAMFILES\Ontoprise\${PRODUCT}\"
DirText $CHOOSEDIRTEXT "" "" ""	
CheckBitmap "images\classic-cross.bmp"
BrandingText "ontoprise GmbH 2008 - www.ontoprise.de"
LicenseText "GPL-License"
LicenseData "gpl.txt"

RequestExecutionLevel admin


; Pages --------------------------------

  
!insertmacro MUI_PAGE_WELCOME
!insertmacro MUI_PAGE_LICENSE "gpl.txt"
!insertmacro MUI_PAGE_COMPONENTS
Page custom showFullInstWithoutXAMPP checkForFilesWithoutXAMPP
Page custom showMWAndSMWUpdate checkMWAndSMWUpdate
!insertmacro MUI_PAGE_DIRECTORY
!insertmacro MUI_PAGE_INSTFILES
!insertmacro MUI_PAGE_FINISH

; Language ------------------------------

!insertmacro MUI_LANGUAGE "English"

; Installation types ---------------------------

!ifndef NOINSTTYPES ; only if not defined
  InstType "New (with XAMPP)"
  InstType "New (without XAMPP)"
  InstType "Update from MediaWiki"
  InstType "Update from SMW"
  InstType "Update from SMW+"
  InstType /NOCUSTOM
!endif

AutoCloseWindow false
ShowInstDetails hide

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
  
  ; if SMW+ 
  ${If} $CURINSTTYPE == 4
  	  StrCpy $CHOOSEDIRTEXT "Select your installation directory of MediaWiki"
  	  Abort
  ${EndIf}
  
  ; if xampp or noxampp
  ${If} $CURINSTTYPE == 1
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
	; setup XAMPP (setup_xampp.bat and install script slightly modified)
	ExecWait '"$INSTDIR\setup_xampp.bat"'
	; setup halowiki (change LocalSettings.php)
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

Section "Wiki with XAMPP" wikixampp
  SectionIn 1 RO
  SetOutPath $INSTDIR
  CreateDirectory "$INSTDIR"
  File /r d:\xampp\*
  SetOutPath $INSTDIR\htdocs\mediawiki
  CreateDirectory "$INSTDIR\htdocs\mediawiki"
  File /r /x CVS /x *.zip /x *.exe /x *.cache /x *.settings ..\*
  SetOutPath $INSTDIR
  CALL changeConfigForFullXAMPP
  
  ; Create shortcuts
  CreateShortCut "$DESKTOP\${PRODUCT} ${VERSION} Start.lnk" "$INSTDIR\xampp_start.exe"
  CreateShortCut "$DESKTOP\${PRODUCT} ${VERSION} Stop.lnk" "$INSTDIR\xampp_stop.exe"
  CreateShortCut "$DESKTOP\${PRODUCT} ${VERSION} Main page.lnk" "http://localhost/mediawiki/index.php"
SectionEnd

Section "Wiki without XAMPP" wikinoxampp
  SectionIn 2 RO
  
  SetOutPath $INSTDIR
  CreateDirectory "$INSTDIR"
  File /r /x CVS /x *.zip /x *.exe /x *.cache /x *.settings ..\*
  CALL changeConfigForNoXAMPP
    
SectionEnd

Section "Wiki update" wikiupdate
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

Section "Update SMW 1.0" smwupdate
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

Section "Update SMW+ 1.0" smwplusupdate
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
LangString DESC_wikixampp ${LANG_ENGLISH} "Installs SMW+ and XAMPP. No other software is required."
LangString DESC_wikinoxampp ${LANG_ENGLISH} "Installs only SMW+. Needs environement: MySQL, Apache, PHP."
LangString DESC_wikiupdate ${LANG_ENGLISH} "Updates existing MediaWiki installation."
LangString DESC_smwupdate ${LANG_ENGLISH} "Updates existing SMW installation."
LangString DESC_smwplusupdate ${LANG_ENGLISH} "Updates existing SMW+ installation."

;Assign language strings to sections
!insertmacro MUI_FUNCTION_DESCRIPTION_BEGIN
	!insertmacro MUI_DESCRIPTION_TEXT ${wikixampp} $(DESC_wikixampp)
	!insertmacro MUI_DESCRIPTION_TEXT ${wikinoxampp} $(DESC_wikinoxampp)
	!insertmacro MUI_DESCRIPTION_TEXT ${wikiupdate} $(DESC_wikiupdate)
	!insertmacro MUI_DESCRIPTION_TEXT ${smwupdate} $(DESC_smwupdate)
	!insertmacro MUI_DESCRIPTION_TEXT ${smwplusupdate} $(DESC_smwplusupdate)
!insertmacro MUI_FUNCTION_DESCRIPTION_END
;--------------------------------

; Uninstaller


