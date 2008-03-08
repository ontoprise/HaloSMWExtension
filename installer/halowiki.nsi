; Halowiki.nsi
;
; This script builds an installer for MediaWiki, SMW, SMW+ and XAMPP

;Without files (much faster for debugging)
!define NOFILES

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
!define MUI_PAGE_CUSTOMFUNCTION_PRE preDirectory
!insertmacro MUI_PAGE_DIRECTORY 
Page custom showDialogs checkDialogs
Page custom showWikiCustomize checkWikiCustomize 
!insertmacro MUI_PAGE_INSTFILES
!insertmacro MUI_PAGE_FINISH

; Language ------------------------------

!insertmacro MUI_LANGUAGE "English"

; Installation types ---------------------------

!ifndef NOINSTTYPES ; only if not defined
  InstType "New (with XAMPP)"
  InstType "New/Update (without XAMPP)"
  InstType /COMPONENTSONLYONCUSTOM 
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

Var CHOOSEDIRTEXT

Function ".onInit"
  InitPluginsDir
  File /oname=$PLUGINSDIR\wikiinst.ini "gui\wikiinst.ini"
  File /oname=$PLUGINSDIR\smwinst.ini "gui\smwinst.ini"
  File /oname=$PLUGINSDIR\wikicustomize.ini "gui\wikicustomize.ini"
FunctionEnd





; ---- Install sections ---------------

Section "XAMPP" xampp
  SectionIn 1
  SetOutPath $INSTDIR
  CreateDirectory "$INSTDIR"
  !ifndef NOFILES
  	File /r d:\xampp\*
  !endif
  
  
  ; Create shortcuts
  CreateShortCut "$DESKTOP\${PRODUCT} ${VERSION} Start.lnk" "$INSTDIR\xampp_start.exe"
  CreateShortCut "$DESKTOP\${PRODUCT} ${VERSION} Stop.lnk" "$INSTDIR\xampp_stop.exe"
  CreateShortCut "$DESKTOP\${PRODUCT} ${VERSION} Main page.lnk" "http://localhost/mediawiki/index.php"
SectionEnd

SectionGroup "SMW+ 1.0" 
Section "SMW+ 1.0 core" smwplus
  SectionIn 1 2 RO
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  ${If} $0 == 1
  	; XAMPP section did already install SMWPlus
  	SetOutPath $INSTDIR\htdocs\mediawiki
  	CreateDirectory "$INSTDIR\htdocs\mediawiki"
  
  ${Else}
  
	SetOutPath $INSTDIR
  	CreateDirectory "$INSTDIR"
  	
  ${EndIf}
  !ifndef NOFILES
    	File /r /x CVS /x *.zip /x *.exe /x *.cache /x *.settings ..\*
  !endif
  ${If} $0 == 1 
	  SetOutPath $INSTDIR
	  CALL changeConfigForFullXAMPP
	 
  ${Else}
  	IfFileExists $INSTDIR\extensions\SMWHalo\*.* 0 notexistsSMWPlus
  	
  	CALL changeConfigForSMWPlusUpdate
   	goto out
  	notexistsSMWPlus:
	   IfFileExists $INSTDIR\extensions\SemanticMediaWiki\*.* 0 notexistsSMW
	  		
			CALL changeConfigForSMWUpdate
	   		goto out
	  	  
	  	  notexistsSMW:
	  		  	
		  IfFileExists $INSTDIR\LocalSettings.php 0 notexists
				copy:
				IfFileExists $INSTDIR\AdminSettings.php 0 as_noexists
				
				CALL changeConfigForMWUpdate
				goto out
			notexists:
				
			  	CALL changeConfigForNoXAMPP
				goto out
			as_noexists:
				MessageBox MB_OK|MB_ICONSTOP  "Could not find AdminSettings.php. \
				Please create one using AdminSettingsTemplate.php and continue afterwards."
				goto copy
			out:	
  ${EndIf}
    
SectionEnd

Section "LDAP Authentication" ldap
SectionEnd

Section "ACL - Access Control Lists" acl
SectionEnd

SectionGroupEnd
;--------------------------------
LangString DESC_xampp ${LANG_ENGLISH} "Select XAMPP if you don't have Apache and stuff. No other software is required."
LangString DESC_smwplus ${LANG_ENGLISH} "SMWPlus 1.0"
LangString CUSTOMIZE_PAGE_TITLE ${LANG_ENGLISH} "Customize your wiki"
LangString CUSTOMIZE_PAGE_SUBTITLE ${LANG_ENGLISH} "Set wiki name or logo"
LangString CONFIG_PAGE_TITLE ${LANG_ENGLISH} "Specify wiki environment"
LangString CONFIG_PAGE_SUBTITLE ${LANG_ENGLISH} "Give some details about your server environment."
LangString PHP_PAGE_TITLE ${LANG_ENGLISH} "Set your PHP-Interpreter"
LangString PHP_PAGE_SUBTITLE ${LANG_ENGLISH} "It's needed for the Gardening tools to work."

;Assign language strings to sections
!insertmacro MUI_FUNCTION_DESCRIPTION_BEGIN
	!insertmacro MUI_DESCRIPTION_TEXT ${xampp} $(DESC_xampp)
	!insertmacro MUI_DESCRIPTION_TEXT ${smwplus} $(DESC_smwplus)
	
!insertmacro MUI_FUNCTION_DESCRIPTION_END
;--------------------------------

Function preDirectory
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  ${If} $0 == 1
  	StrCpy $CHOOSEDIRTEXT "Select an empty directory where to install XAMPP and the wiki."
  ${Else}
  	StrCpy $CHOOSEDIRTEXT "Select an existing installation to update or an empty directory for a new."
  ${EndIf}
FunctionEnd

Function showDialogs
  
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}

  ${If} $0 == 0
  	  ; XAMPP is NOT selected
  	  IfFileExists $INSTDIR\extensions\SMWHalo\*.* 0 notexistsSMWPlus
  	  goto out
  	  notexistsSMWPlus:
  	  	IfFileExists $INSTDIR\extensions\SemanticMediaWiki\*.* 0 notexistsSMW
  	  	; show PHP dialog
  	  	CALL showPHP
  	  	goto out
  	  	notexistsSMW:
  	  	IfFileExists $INSTDIR\extensions\LocalSettings.php 0 notexistsMW
  	  		; show PHP dialog
  	  		CALL showPHP
  	  		goto out
  	  		notexistsMW:
	  	  	CALL showFull
	  	  
  ${Else}
  	  ; XAMPP is selected
  	  Abort
  ${EndIf}
  out:
FunctionEnd

Function showFull
	!insertmacro MUI_HEADER_TEXT $(CONFIG_PAGE_TITLE) $(CONFIG_PAGE_SUBTITLE)
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
FunctionEnd

Function showPHP
    !insertmacro MUI_HEADER_TEXT $(PHP_PAGE_TITLE) $(PHP_PAGE_SUBTITLE)	
   	Push $R0
	InstallOptions::dialog $PLUGINSDIR\smwinst.ini
	Pop $R0
 	ReadINIStr $PHP "$PLUGINSDIR\smwinst.ini" "Field 2" "state"
 	Pop $R0	

 
FunctionEnd

Function showWikiCustomize
	 !insertmacro MUI_HEADER_TEXT $(CUSTOMIZE_PAGE_TITLE) $(CUSTOMIZE_PAGE_SUBTITLE)
	  Push $R0
	  InstallOptions::dialog $PLUGINSDIR\wikicustomize.ini
	  Pop $R0
	  ReadINIStr $PHP "$PLUGINSDIR\wikicustomize.ini" "Field 2" "state"
	  Pop $R0
FunctionEnd

Function checkWikiCustomize
FunctionEnd


	
Function checkDialogs

	SectionGetFlags ${xampp} $0
  	IntOp $0 $0 & ${SF_SELECTED}
	
	${If} $0 == 0
		IfFileExists $INSTDIR\extensions\SMWHalo\*.* 0 notexistsSMWPlus
		goto out
		notexistsSMWPlus:
			IfFileExists $INSTDIR\extensions\LocalSettings.php 0 notexistsMW
			CALL checkPHP
			goto out
			notexistsMW:
				CALL checkFull
	${EndIf}
	out:
	
FunctionEnd

Function checkFull
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

Function checkPHP
	ReadINIStr $PHP "$PLUGINSDIR\smwinst.ini" "Field 2" "state"
	IfFileExists $PHP 0 notexistsPHP
	goto out
	notexistsPHP:
		MessageBox MB_OK "php.exe does not exist!"
		Abort
	out:
FunctionEnd

Function changeConfigForFullXAMPP
	; setup XAMPP (setup_xampp.bat and install script slightly modified)
	nsExec::ExecToLog '"$INSTDIR\setup_xampp.bat"'
	; setup halowiki (change LocalSettings.php)
	nsExec::ExecToLog '"$INSTDIR\setup_halowiki.bat"'	 	 
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

Function changeConfigForMWUpdate
	
	
	ExecWait '"$PHP" $INSTDIR\installer\changeLS.php phpInterpreter=$PHP \ 
		smwgIQEnabled=true smwgAllowNewHelpQuestions=true smwgAllowNewHelpQuestions=true \
		keepGardeningConsole=false smwhgEnableLogging=false smwgDeployVersion=true \
		semanticAC=false wgGardeningBotDelay=100 importSMW=1 importSMWPlus=1 ls=LocalSettings.php'
		
		
	ExecWait '"$PHP" $INSTDIR\maintenance\update.php'
	ExecWait '"$PHP" $INSTDIR\maintenance\SMW_setup.php'
	ExecWait '"$PHP" $INSTDIR\extensions\SMWHalo\maintenance\SMW_setup.php'
	
	
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
; Uninstaller


