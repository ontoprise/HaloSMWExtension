/*
* Halowiki.nsi (c) Ontoprise 2008
*
*    This script builds an installer for SMW+
*
* Author: Kai Kühn
*
* Needs NSIS 2.35 or higher
* additional extensions: (see extension folder) 
*    - FindProcDLL.dll
*/

;Without files (compiles much faster, for debugging)
;!define NOFILES
 
;--------------------------------
!include "MUI2.nsh"
!include "LogicLib.nsh"
!include "FileFunc.nsh"

!insertmacro GetFileName

; --- The following definitions are meant to be changed ---

!define PRODUCT "SMW+"
!define PRODUCT_CAPTION "SMW+"
!define VERSION "1.3"
!define BUILD_ID "303"


; ----------------------------------------------------------

!define MUI_ABORTWARNING

!define MUI_HEADERIMAGE
!define MUI_HEADERIMAGE_BITMAP "..\SMWPlusInstaller\images\header-install.bmp"

!define MUI_WELCOMEFINISHPAGE 
!define MUI_WELCOMEFINISHPAGE_BITMAP "..\SMWPlusInstaller\images\wizard-install.bmp"
!define MUI_WELCOMEFINISHPAGE_BITMAP_NOSTRETCH
!define MUI_COMPONENTSPAGE_SMALLDESC

!define MUI_WELCOMEPAGE_TITLE "Welcome to the ${PRODUCT} ${VERSION} Setup Wizard"
!define MUI_WELCOMEPAGE_TEXT "This wizard will guide you through the installation of ${PRODUCT} ${VERSION}."

!define MUI_FINISHPAGE_RUN
!define MUI_FINISHPAGE_RUN_CHECKED
!define MUI_FINISHPAGE_RUN_TEXT "Start XAMPP (if installed)"
!define MUI_FINISHPAGE_RUN_FUNCTION "startXAMPP"

!define MUI_FINISHPAGE_SHOWREADME 
!define MUI_FINISHPAGE_SHOWREADME_FUNCTION createXAMPPShortcuts
!define MUI_FINISHPAGE_SHOWREADME_TEXT "Create XAMPP shortcuts (if installed)"
!define MUI_FINISHPAGE_SHOWREADME_CHECKED

!define MUI_FINISHPAGE_TEXT "The installation was successful! If you installed XAMPP, you'll find some new icons on your desktop. \
Please open the main page by clicking on '${PRODUCT} ${VERSION} Main Page'."
!define MUI_FINISHPAGE_LINK "Visit the ontoprise website for the latest news"
!define MUI_FINISHPAGE_LINK_LOCATION "http://www.ontoprise.com/"




!ifdef NOCOMPRESS
SetCompress off
!endif

;--------------------------------

Name "${PRODUCT} Version ${VERSION}"
Caption "${PRODUCT_CAPTION} ${VERSION}"
Icon "..\SMWPlusInstaller\images\nsis1-install.ico"
OutFile "${PRODUCT}-${VERSION}.exe"

AllowSkipFiles off
SetOverwrite on
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
CheckBitmap "..\SMWPlusInstaller\images\classic-cross.bmp"
BrandingText "ontoprise GmbH 2008 - wiki.ontoprise.de - Build: ${BUILD_ID}"
LicenseText "GPL-License"
LicenseData "..\SMWPlusInstaller\gpl.txt"

RequestExecutionLevel admin


; Pages --------------------------------

!define MUI_ICON "..\SMWPlusInstaller\images\smwplus_32.ico"
  
!insertmacro MUI_PAGE_WELCOME
!insertmacro MUI_PAGE_LICENSE "..\SMWPlusInstaller\gpl.txt"
!define MUI_PAGE_CUSTOMFUNCTION_SHOW initComponentsPage
!define MUI_PAGE_CUSTOMFUNCTION_LEAVE checkForNeededProcess
!insertmacro MUI_PAGE_COMPONENTS
!define MUI_PAGE_CUSTOMFUNCTION_PRE preDirectory
!insertmacro MUI_PAGE_DIRECTORY 
Page custom showDialogs checkDialogs
Page custom showLDAPConfig
Page custom showLDAPConfig2
Page custom showWikiCustomize checkWikiCustomize 
!insertmacro MUI_PAGE_INSTFILES
;!define MUI_PAGE_CUSTOMFUNCTION_LEAVE checkForSkype
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

; Basic variables for environment
Var PHP
Var MEDIAWIKIDIR
Var MYSQLBIN
Var DBSERVER
Var DBUSER
Var DBPASS
VAR HTTPD
VAR WIKIPATH

;Wiki customizations
Var WIKINAME 
Var WIKILOGO 
Var WIKILANG 
Var WIKISKIN 
Var CSH 
Var INSTHELP

; Helper
Var CHOOSEDIRTEXT
Var INSTALLTYPE

Function ".onInit"
  InitPluginsDir
  File /oname=$PLUGINSDIR\wikiinst.ini "..\SMWPlusInstaller\gui\wikiinst.ini"
  File /oname=$PLUGINSDIR\wikiupdate.ini "..\SMWPlusInstaller\gui\wikiupdate.ini"
  File /oname=$PLUGINSDIR\wikicustomize.ini "..\SMWPlusInstaller\gui\wikicustomize.ini"
  File /oname=$PLUGINSDIR\ldap.ini "..\SMWPlusInstaller\gui\ldap.ini"
  File /oname=$PLUGINSDIR\ldap2.ini "..\SMWPlusInstaller\gui\ldap2.ini"
  
FunctionEnd


Function .onSelChange
 GetCurInstType $R0
 ${Switch} $R0
    ${Case} 0
        SendMessage $mui.ComponentsPage.DescriptionText ${WM_SETTEXT} 0 "STR:Normal installation. No further software needed."
        ${Break}
    ${Case} 1
        SendMessage $mui.ComponentsPage.DescriptionText ${WM_SETTEXT} 0 "STR:Choose this if you already have a server environment or if you do an update."
        ${Break}
    ${Default} 
        SendMessage $mui.ComponentsPage.DescriptionText ${WM_SETTEXT} 0 "STR:Custom installation"
        ${Break}
  ${EndSwitch}

FunctionEnd

Function initComponentsPage
    SendMessage $mui.ComponentsPage.DescriptionText ${WM_SETTEXT} 0 "STR:Normal installation. No further software needed."
FunctionEnd


; ---- Install sections ---------------


Section "XAMPP" xampp
  SectionIn 1
  SetOutPath "$INSTDIR"
  CreateDirectory "$INSTDIR"
  !ifndef NOFILES
  	File /r /x CVS ..\xampp\*
  !endif
  
SectionEnd

Section "-CopyInstaller"
  SectionIn 1 2 3
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  ${If} $0 == 1
     SetOutPath "$INSTDIR\htdocs\mediawiki\installer"
     CreateDirectory "$INSTDIR\htdocs\mediawiki\installer"
  ${Else}
     SetOutPath "$INSTDIR\installer"
     CreateDirectory "$INSTDIR\installer"
  ${EndIf}
  !ifndef NOFILES
    File /r /x CVS /x *.exe /x *.nsi ..\SMWPlusInstaller\*
    ${If} $0 == 1
    CreateShortCut "$DESKTOP\${PRODUCT} ${VERSION} Main Page.lnk" "http://localhost/mediawiki/index.php" \
    "" "$INSTDIR\htdocs\mediawiki\installer\images\smwplus_32.ico" 0
    ${EndIf}
  !endif
SectionEnd

SectionGroup "${PRODUCT} ${VERSION}" 
Section "${PRODUCT} ${VERSION} core" smwplus
  SectionIn 1 2 RO
  
  ; check for AdminSettings.php
  IfFileExists $INSTDIR\LocalSettings.php 0 setpath
  	tryagain:
  	IfFileExists $INSTDIR\AdminSettings.php 0 as_noexists
  		goto setpath
  		as_noexists:
  			MessageBox MB_OK|MB_ICONSTOP  "Could not find AdminSettings.php. \
			Please create one using AdminSettingsTemplate.php and continue afterwards."
			goto tryagain
			
  setpath:
  
  ; Set output path 
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  ${If} $0 == 1
  	; XAMPP section did already install SMWPlus
  	SetOutPath "$INSTDIR\htdocs\mediawiki"
  	CreateDirectory "$INSTDIR\htdocs\mediawiki"
  ${Else}
 	SetOutPath "$INSTDIR"
  	CreateDirectory "$INSTDIR"
  ${EndIf}
  
  ; Copy files and config 
  ${If} $0 == 1 
	 IntOp $INSTALLTYPE 0 + 4
  ${Else}
  	IfFileExists $INSTDIR\extensions\SMWHalo\*.* 0 notexistsSMWPlus
    	
   	IntOp $INSTALLTYPE 0 + 0
   	goto copyfiles
   	
  	notexistsSMWPlus:
	   IfFileExists $INSTDIR\extensions\SemanticMediaWiki\*.* 0 notexistsSMW
	   		   		
			IntOp $INSTALLTYPE 0 + 1
	   		goto copyfiles
	  	  
	  	  notexistsSMW:
	  		  IfFileExists $INSTDIR\LocalSettings.php 0 notexistsMW
									
					IntOp $INSTALLTYPE 0 + 2
					goto copyfiles
			notexistsMW:
				
				IntOp $INSTALLTYPE 0 + 3
				goto copyfiles
			
				
  ${EndIf}
  
  copyfiles:
	  !ifndef NOFILES
	    	File /r /x CVS /x *.zip /x *.exe /x *.cache /x *.settings /x LocalSettings.php /x ACLs.php /x *.nsi *
      !endif  
   
  ;configure:
	  ${If} $INSTALLTYPE == 0 
	  	CALL changeConfigForSMWPlusUpdate
	  ${EndIf}
	  ${If} $INSTALLTYPE == 1
	  	CALL changeConfigForSMWUpdate
	  ${EndIf}
	  ${If} $INSTALLTYPE == 2
	  	CALL changeConfigForMWUpdate
	  ${EndIf}
	  ${If} $INSTALLTYPE == 3 
	  	CALL changeConfigForNoXAMPP
	  ${EndIf}
	  ${If} $INSTALLTYPE == 4
	  	CALL changeConfigForFullXAMPP
	  ${EndIf}
SectionEnd

Section "LDAP Authentication" ldap
    
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  ReadINIStr $R0 "$PLUGINSDIR\ldap.ini" "Field 2" "state"
  ReadINIStr $R1 "$PLUGINSDIR\ldap.ini" "Field 4" "state"
  ReadINIStr $R2 "$PLUGINSDIR\ldap.ini" "Field 6" "state"
  ReadINIStr $R3 "$PLUGINSDIR\ldap.ini" "Field 8" "state"
  ReadINIStr $R4 "$PLUGINSDIR\ldap.ini" "Field 10" "state"
  ReadINIStr $R5 "$PLUGINSDIR\ldap.ini" "Field 12" "state"
    
  ${If} $0 == 1
  	; LDAP with XAMPP
  	SetOutPath "$INSTDIR\htdocs\mediawiki"
    StrCpy $PHP "$INSTDIR\php\php.exe"
    StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"
    DetailPrint "Activate PHP LDAP extension"
  	nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\activateExtension.php" ini="$INSTDIR\apache\bin\php.ini" on=php_ldap on=php_gd2'
    
  ${Else}
 	SetOutPath "$INSTDIR"
 	${If} $INSTALLTYPE == 3 
	  	ReadINIStr $PHP "$PLUGINSDIR\wikiinst.ini" "Field 2" "state"
	${Else}
		ReadINIStr $PHP "$PLUGINSDIR\wikiupdate.ini" "Field 2" "state"
	${EndIf}
    StrCpy $MEDIAWIKIDIR "$INSTDIR"
  ${EndIf}
  
    ; Configure basic LDAP options
    DetailPrint "Configure basic LDAP features"
  	nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" \
    importLDAP=1 wgLDAPDomainNames="[$R0]" wgLDAPServerNames="$R0~$R1" wgLDAPSearchStrings="$R0~$R3" \
    wgLDAPUseLocal=false wgLDAPEncryptionType="$R0~$R4" wgLDAPOptions[$\'no_url$\']=true \
    wgLDAPOptions[$\'port$\']="$R2" wgMinimalPasswordLength=1 ls=LocalSettings.php'
  	
    ${If} $R5 == 1
        ; Configure LDAP group options
        DetailPrint "Configure LDAP group features"
        ReadINIStr $R1 "$PLUGINSDIR\ldap2.ini" "Field 2" "state"
        ReadINIStr $R2 "$PLUGINSDIR\ldap2.ini" "Field 4" "state"
        ReadINIStr $R3 "$PLUGINSDIR\ldap2.ini" "Field 6" "state"
        ReadINIStr $R4 "$PLUGINSDIR\ldap2.ini" "Field 8" "state"
        ReadINIStr $R5 "$PLUGINSDIR\ldap2.ini" "Field 10" "state"
        ReadINIStr $R6 "$PLUGINSDIR\ldap2.ini" "Field 12" "state"
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" "wgLDAPRequiredGroups[$\'$R0$\']=[$R1]" \
        "wgLDAPGroupBaseDNs[$\'$R0$\']=$R2" "wgLDAPGroupObjectclass[$\'$R0$\']=$R3" "wgLDAPGroupAttribute[$\'$R0$\']=$R4" \
        "wgLDAPGroupAttributeValue[$\'$R0$\']=$R5" "wgLDAPGroupNameAttribute[$\'$R0$\']=$R6" "wgLDAPUseLDAPGroups[$\'$R0$\']=true" \
        ls=LocalSettings.php'
    ${EndIf}
  	
 
SectionEnd

Section "ACL - Access Control Lists" acl
  SectionIn 1 RO
  DetailPrint "Configure ACL extension"
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  ${If} $0 == 1
  	
  	SetOutPath "$INSTDIR\htdocs\mediawiki"
    StrCpy $PHP "$INSTDIR\php\php.exe"
    StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"
  	
  ${Else}
 	SetOutPath "$INSTDIR"
 	${If} $INSTALLTYPE == 3 
	  	ReadINIStr $PHP "$PLUGINSDIR\wikiinst.ini" "Field 2" "state"
	${Else}
		ReadINIStr $PHP "$PLUGINSDIR\wikiupdate.ini" "Field 2" "state"
	${EndIf}
    StrCpy $MEDIAWIKIDIR "$INSTDIR"
  ${EndIf}
  
  ; change config file
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" importACL=1 ls=LocalSettings.php'
  
SectionEnd

Section "Semantic Forms" semforms
  SectionIn 1 RO
  DetailPrint "Configure Semantic Forms extension"
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  ${If} $0 == 1
    
    SetOutPath "$INSTDIR\htdocs\mediawiki"
    StrCpy $PHP "$INSTDIR\php\php.exe"
    StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"
    
  ${Else}
    SetOutPath "$INSTDIR"
    ${If} $INSTALLTYPE == 3 
        ReadINIStr $PHP "$PLUGINSDIR\wikiinst.ini" "Field 2" "state"
    ${Else}
        ReadINIStr $PHP "$PLUGINSDIR\wikiupdate.ini" "Field 2" "state"
    ${EndIf}
    StrCpy $MEDIAWIKIDIR "$INSTDIR"
  ${EndIf}
  
  ; change config file
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" importSemForms=1 ls=LocalSettings.php'
SectionEnd

Section "Semantic Calendar" semcalendar
  SectionIn 1 RO
  DetailPrint "Configure Semantic Calendar extension"
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  ${If} $0 == 1
    
    SetOutPath "$INSTDIR\htdocs\mediawiki"
    StrCpy $PHP "$INSTDIR\php\php.exe"
    StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"
    
  ${Else}
    SetOutPath "$INSTDIR"
    ${If} $INSTALLTYPE == 3 
        ReadINIStr $PHP "$PLUGINSDIR\wikiinst.ini" "Field 2" "state"
    ${Else}
        ReadINIStr $PHP "$PLUGINSDIR\wikiupdate.ini" "Field 2" "state"
    ${EndIf}
    StrCpy $MEDIAWIKIDIR "$INSTDIR"
  ${EndIf}
  
  ; change config file
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" importSemCalendar=1 ls=LocalSettings.php'
SectionEnd

Section "Treeview" treeview
  SectionIn 1 RO
  DetailPrint "Configure Semantic Calendar extension"
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  ${If} $0 == 1
    
    SetOutPath "$INSTDIR\htdocs\mediawiki"
    StrCpy $PHP "$INSTDIR\php\php.exe"
    StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"
    
  ${Else}
    SetOutPath "$INSTDIR"
    ${If} $INSTALLTYPE == 3 
        ReadINIStr $PHP "$PLUGINSDIR\wikiinst.ini" "Field 2" "state"
    ${Else}
        ReadINIStr $PHP "$PLUGINSDIR\wikiupdate.ini" "Field 2" "state"
    ${EndIf}
    StrCpy $MEDIAWIKIDIR "$INSTDIR"
  ${EndIf}
  
  ; change config file
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" importTreeview=1 ls=LocalSettings.php'
SectionEnd

Section "WYSIWYG" wysiwyg
  SectionIn 1
  DetailPrint "Configure WYSIWYG extension"
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  ${If} $0 == 1
    
    SetOutPath "$INSTDIR\htdocs\mediawiki"
    StrCpy $PHP "$INSTDIR\php\php.exe"
    StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"
    
  ${Else}
    SetOutPath "$INSTDIR"
    ${If} $INSTALLTYPE == 3 
        ReadINIStr $PHP "$PLUGINSDIR\wikiinst.ini" "Field 2" "state"
    ${Else}
        ReadINIStr $PHP "$PLUGINSDIR\wikiupdate.ini" "Field 2" "state"
    ${EndIf}
    StrCpy $MEDIAWIKIDIR "$INSTDIR"
  ${EndIf}
  
  ; change config file
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" importWYSIWYG=1 ls=LocalSettings.php'
SectionEnd

SectionGroupEnd


;Languages (english)
LangString DESC_xampp ${LANG_ENGLISH} "Select XAMPP if you don't have Apache and stuff. No other software is required."
LangString DESC_smwplus ${LANG_ENGLISH} "${PRODUCT} ${VERSION}"
LangString DESC_ldap ${LANG_ENGLISH} "Authenticate users with an existing LDAP server."
LangString DESC_acl ${LANG_ENGLISH} "Access Control Lists allow restricting wiki access for groups by excluding namespaces and wiki operations."
LangString DESC_semcalendar ${LANG_ENGLISH} "The sementic calendar renders date annotations graphically as a calendar entry."
LangString DESC_semforms ${LANG_ENGLISH} "Semantic Forms ease the annotation process by providing a simple interface."
LangString DESC_treeview ${LANG_ENGLISH} "The Treeview extension allows a hierarchical displaying of content or links."
LangString DESC_wysiwyg ${LANG_ENGLISH} "The WYSIWYG extension allows editing with a Word-like comfortable editor."

LangString CUSTOMIZE_PAGE_TITLE ${LANG_ENGLISH} "Customize your wiki"
LangString CUSTOMIZE_PAGE_SUBTITLE ${LANG_ENGLISH} "Set wiki name or logo"
LangString CONFIG_PAGE_TITLE ${LANG_ENGLISH} "Specify wiki environment"
LangString CONFIG_PAGE_SUBTITLE ${LANG_ENGLISH} "Give some details about your server environment."
LangString PHP_PAGE_TITLE ${LANG_ENGLISH} "Set your PHP-Interpreter"
LangString PHP_PAGE_SUBTITLE ${LANG_ENGLISH} "It's needed for the Gardening tools to work."
LangString LDAP_CONFIG1_PAGE_TITLE ${LANG_ENGLISH} "Configure your LDAP server"
LangString LDAP_CONFIG1_PAGE_SUBTITLE ${LANG_ENGLISH} "Server, Port, Connection type,..."
LangString SELECT_XAMPP_DIR ${LANG_ENGLISH} "Select an empty directory where to install XAMPP and the wiki."
LangString SELECT_NEWUPDATE_DIR ${LANG_ENGLISH} "Select an existing installation to update or an empty directory for a new."
LangString START_SERVERS ${LANG_ENGLISH} "Please start Apache and MySQL"
LangString COULD_NOT_START_SERVERS ${LANG_ENGLISH} "Apache and MySQL could not be started for some reason. Installation may not be complete!"
LangString FIREWALL_COMPLAIN_INFO ${LANG_ENGLISH} "If Windows Firewall complains, unblock the two processes. Then continue."
LangString INSTALLER_CREATES_DB ${LANG_ENGLISH} "The installer will create a database (semwiki_en/de) and REMOVE any existing databases with this name."
LangString PHP_NOT_EXISTS ${LANG_ENGLISH} "php.exe does not exist"
LangString MYSQL_NOT_EXISTS ${LANG_ENGLISH} "mysql.exe does not exist!"
LangString HTTPD_NOT_EXISTS ${LANG_ENGLISH} "httpd.conf does not exist!"
LangString DATABASE_MISSING ${LANG_ENGLISH} "Database server must be specified!"
LangString DATABASE_USER_MISSING ${LANG_ENGLISH} "Database user must be specified!"
LangString DATABASE_PASSWORD_MISSING ${LANG_ENGLISH} "Database password must be specified!"
LangString WIKIPATH_MISSING ${LANG_ENGLISH} "Wiki path must be specified!"

;Assign language strings to sections
!insertmacro MUI_FUNCTION_DESCRIPTION_BEGIN
	!insertmacro MUI_DESCRIPTION_TEXT ${xampp} $(DESC_xampp)
	!insertmacro MUI_DESCRIPTION_TEXT ${smwplus} $(DESC_smwplus)
    !insertmacro MUI_DESCRIPTION_TEXT ${ldap} $(DESC_ldap)
    !insertmacro MUI_DESCRIPTION_TEXT ${acl} $(DESC_acl)
	!insertmacro MUI_DESCRIPTION_TEXT ${semcalendar} $(DESC_semcalendar)
    !insertmacro MUI_DESCRIPTION_TEXT ${semforms} $(DESC_semforms)
    !insertmacro MUI_DESCRIPTION_TEXT ${treeview} $(DESC_treeview)
!insertmacro MUI_FUNCTION_DESCRIPTION_END
;--------------------------------


Function preDirectory
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  ${If} $0 == 1
  	StrCpy $CHOOSEDIRTEXT $(SELECT_XAMPP_DIR)
  ${Else}
    StrCpy $CHOOSEDIRTEXT $(SELECT_NEWUPDATE_DIR)
  ${EndIf}
FunctionEnd

Function checkForNeededProcess
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  ${If} $0 == 0
    CALL checkForApacheAndMySQL
  ${EndIf}
FunctionEnd

Function checkForApacheAndMySQL
 checkagain:
   FindProcDLL::FindProc "apache.exe"
   IntOp $0 0 + $R0
   FindProcDLL::FindProc "mysqld.exe"
   IntOp $1 0 + $R0
   FindProcDLL::FindProc "mysqld-nt.exe"
   IntOp $1 $1 + $R0
   ${If} $0 == 0
   ${OrIf} $1 == 0
    MessageBox MB_OKCANCEL $(START_SERVERS) IDOK 0 IDCANCEL skipCheck
    goto checkagain
   ${EndIf}
   goto out
 skipcheck:
    Abort
 out:
FunctionEnd

Function waitForApacheAndMySQL
   IntOp $2 0 + 10
 checkagain:
   Sleep 1000
   FindProcDLL::FindProc "apache.exe"
   IntOp $0 0 + $R0
   FindProcDLL::FindProc "mysqld.exe"
   IntOp $1 0 + $R0
   FindProcDLL::FindProc "mysqld-nt.exe"
   IntOp $1 $1 + $R0
   ${If} $0 == 0
   ${OrIf} $1 == 0
    IntOp $2 $2 - 1
    IntCmp $2 0 notfound
    goto checkagain
   ${EndIf}
   goto out
 notfound:
    MessageBox MB_OK|MB_ICONEXCLAMATION $(COULD_NOT_START_SERVERS)
 out:
FunctionEnd

Function showDialogs
  
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}

  ${If} $0 == 0
  	  ; XAMPP is NOT selected
  	  IfFileExists $INSTDIR\extensions\SMWHalo\*.* 0 notexistsSMWPlus
  	  CALL showPHP
  	  goto out
  	  notexistsSMWPlus:
  	  	IfFileExists $INSTDIR\extensions\SemanticMediaWiki\*.* 0 notexistsSMW
  	  	; show PHP dialog
  	  	CALL showPHP
  	  	goto out
  	  	notexistsSMW:
  	  	IfFileExists $INSTDIR\LocalSettings.php 0 notexistsMW
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
	
FunctionEnd

Function showPHP
    !insertmacro MUI_HEADER_TEXT $(PHP_PAGE_TITLE) $(PHP_PAGE_SUBTITLE)	
   	Push $R0
	InstallOptions::dialog $PLUGINSDIR\wikiupdate.ini
	Pop $R0
 	 
FunctionEnd

Function showWikiCustomize
	 !insertmacro MUI_HEADER_TEXT $(CUSTOMIZE_PAGE_TITLE) $(CUSTOMIZE_PAGE_SUBTITLE)
	  Push $R0
	  InstallOptions::dialog $PLUGINSDIR\wikicustomize.ini
	  Pop $R0
	 
FunctionEnd

Function checkWikiCustomize
   
FunctionEnd

Function showLDAPConfig
	SectionGetFlags ${ldap} $0
  	IntOp $0 $0 & ${SF_SELECTED}
  	
  	${If} $0 == 1 
		!insertmacro MUI_HEADER_TEXT $(LDAP_CONFIG1_PAGE_TITLE) $(LDAP_CONFIG1_PAGE_SUBTITLE)
	  	Push $R0
	  	InstallOptions::dialog $PLUGINSDIR\ldap.ini
	  	Pop $R0
	${Else}
	 	Abort
	${EndIf}
FunctionEnd

Function showLDAPConfig2
	SectionGetFlags ${ldap} $0
  	IntOp $0 $0 & ${SF_SELECTED}
  	ReadINIStr $R0 "$PLUGINSDIR\ldap.ini" "Field 12" "state"
  	${If} $0 == 1 
  	${AndIf} $R0 == 1
		!insertmacro MUI_HEADER_TEXT $(LDAP_CONFIG1_PAGE_TITLE) $(LDAP_CONFIG1_PAGE_SUBTITLE)
	  	Push $R0
	  	InstallOptions::dialog $PLUGINSDIR\ldap2.ini
	  	Pop $R0
	${Else}
	 	Abort
	${EndIf}
FunctionEnd
	
Function checkDialogs

	SectionGetFlags ${xampp} $0
  	IntOp $0 $0 & ${SF_SELECTED}
	
	${If} $0 == 0
			IfFileExists $INSTDIR\LocalSettings.php 0 notexistsMW
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
		MessageBox MB_OK $(MYSQL_NOT_EXISTS)
		goto aborthere
	notexistsPHP:
		MessageBox MB_OK $(PHP_NOT_EXISTS)
		goto aborthere
	notexistsHTTPD:
		MessageBox MB_OK $(HTTPD_NOT_EXISTS)
		goto aborthere
	specifyDatabase:
		MessageBox MB_OK $(DATABASE_MISSING)
		goto aborthere 
	specifyUser:
		MessageBox MB_OK $(DATABASE_USER_MISSING)
		goto aborthere 
	specifyPass:
		MessageBox MB_OK $(DATABASE_PASSWORD_MISSING)
		goto aborthere 
	specifyWiki:
		MessageBox MB_OK $(WIKIPATH_MISSING)
		goto aborthere 
	aborthere:
		Abort
	out:
FunctionEnd

Function checkPHP
	ReadINIStr $PHP "$PLUGINSDIR\wikiupdate.ini" "Field 2" "state"
	IfFileExists $PHP 0 notexistsPHP
	goto out
	notexistsPHP:
		MessageBox MB_OK $(PHP_NOT_EXISTS)
		Abort
	out:
FunctionEnd

Function changeConfigForFullXAMPP
	; setup XAMPP (setup_xampp.bat and install script slightly modified)
	DetailPrint "Update XAMPP"
	SetOutPath "$INSTDIR"
	nsExec::ExecToLog '"$INSTDIR\setup_xampp.bat"'
	SetOutPath "$INSTDIR\htdocs\mediawiki"
	
	; setup halowiki (change LocalSettings.php)
    ; Use LocalSettings.php.template and change the following variables:
    ;   phpInterpreter
    ;   smwgIQEnabled
    ;   smwgAllowNewHelpQuestions
    ;   wgUseAjax
    ;   smwgKeepGardeningConsole
    ;   smwgEnableLogging
    ;   smwgDeployVersion
    ;   smwgSemanticAC
    ;   smwgGardeningBotDelay
    ;   wgScriptPath
    
	DetailPrint "Update LocalSettings.php"
	nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\installer\changeLS.php" phpInterpreter="$INSTDIR\php\php.exe" \
		smwgIQEnabled=true smwgAllowNewHelpQuestions=true wgUseAjax=true wgJobRunRate=0 wgEnableUploads=true \
		smwgKeepGardeningConsole=false smwgEnableLogging=false smwgDeployVersion=true \
		smwgSemanticAC=false smwgGardeningBotDelay=100 wgScriptPath="/mediawiki" ls=LocalSettings.php.template'
	
    ; Activate php_gd2.dll for thumbnails
    DetailPrint "Update php.ini"
    nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\installer\activateExtension.php" ini="$INSTDIR\apache\bin\php.ini" on=php_gd2'
    
    ; Make halowiki directory accessible by Apache	
	DetailPrint "Update httpd.conf"	 
	nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\installer\changeHttpd.php" httpd="$INSTDIR\apache\conf\httpd.conf" wiki-path=mediawiki fs-path="$INSTDIR\htdocs\mediawiki"'
	
	DetailPrint "Config customizations"
	CALL configCustomizationsForNewWithXAMPP
FunctionEnd

Function changeConfigForNoXAMPP
	ReadINIStr $PHP "$PLUGINSDIR\wikiinst.ini" "Field 2" "state"
    ReadINIStr $MYSQLBIN "$PLUGINSDIR\wikiinst.ini" "Field 4" "state"
    ReadINIStr $DBSERVER "$PLUGINSDIR\wikiinst.ini" "Field 6" "state"
    ReadINIStr $DBUSER "$PLUGINSDIR\wikiinst.ini" "Field 8" "state"
    ReadINIStr $DBPASS "$PLUGINSDIR\wikiinst.ini" "Field 10" "state"
	CALL checkForApacheAndMySQL
	
	; Set config variables
	DetailPrint "Update LocalSettings.php"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\installer\changeLS.php" phpInterpreter="$PHP" wgDBserver="$DBSERVER" wgDBuser="$DBUSER" wgDBpassword="$DBPASS" \
		smwgIQEnabled=true smwgAllowNewHelpQuestions=true smwgAllowNewHelpQuestions=true wgUseAjax=true wgJobRunRate=0 \
		smwgKeepGardeningConsole=false smwgEnableLogging=false smwgDeployVersion=true wgEnableUploads=true \
		smwgSemanticAC=false smwgGardeningBotDelay=100 wgScriptPath="/$WIKIPATH" ls=LocalSettings.php.template'
		
	; Set httpd
	DetailPrint "Update httpd.conf"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\installer\changeHttpd.php" httpd="$HTTPD" wiki-path="$WIKIPATH" fs-path="$INSTDIR"'
	
	; Create and initialize DB
    DetailPrint "Import database: semwiki_en/de"
    MessageBox MB_OKCANCEL $(INSTALLER_CREATES_DB) IDOK 0 IDCANCEL aborthere
    
	nsExec::ExecToLog '"cmd" /C $MYSQLBIN --host=$DBSERVER --user=$DBUSER --password=$DBPASS < "$INSTDIR\installer\createDB.inf"' $0
	nsExec::ExecToLog '"cmd" /C $MYSQLBIN --host=$DBSERVER --user=$DBUSER --password=$DBPASS < "$INSTDIR\installer\en_de_wikidb.sql"' $1 
	
	DetailPrint "Config customizations"
	CALL configCustomizationsForNewWithoutXAMPP
    goto out
   aborthere:
    Abort
   out: 
FunctionEnd

Function changeConfigForMWUpdate
	ReadINIStr $PHP "$PLUGINSDIR\wikiupdate.ini" "Field 2" "state"
	CALL checkForApacheAndMySQL
	
	DetailPrint "Update LocalSettings.php"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\installer\changeLS.php" phpInterpreter="$PHP" \ 
		smwgIQEnabled=true smwgAllowNewHelpQuestions=true smwgAllowNewHelpQuestions=true wgUseAjax=true \
		smwgKeepGardeningConsole=false smwgEnableLogging=false smwgDeployVersion=true wgJobRunRate=0 wgEnableUploads=true \
		smwgSemanticAC=false smwgGardeningBotDelay=100 importSMW=1 importSMWPlus=1 ls=LocalSettings.php'
		
	
	DetailPrint "Update MediaWiki database"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\maintenance\update.php"'
	
	DetailPrint "Update SMW tables"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\maintenance\SMW_setup.php"'
	
	DetailPrint "Update SMW+ tables"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\extensions\SMWHalo\maintenance\SMW_setup.php"'
	
	DetailPrint "Config customizations"
	CALL configCustomizationsForUpdate
FunctionEnd



Function changeConfigForSMWUpdate
	ReadINIStr $PHP "$PLUGINSDIR\wikiupdate.ini" "Field 2" "state"
	CALL checkForApacheAndMySQL
	
	DetailPrint "Update LocalSettings.php"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\installer\changeLS.php" phpInterpreter="$PHP" \ 
		smwgAllowNewHelpQuestions=true smwgAllowNewHelpQuestions=true wgUseAjax=true wgJobRunRate=0 \
		smwgKeepGardeningConsole=false smwgEnableLogging=false smwgDeployVersion=true wgEnableUploads=true \
		smwgSemanticAC=false smwgGardeningBotDelay=100 importSMWPlus=1 ls=LocalSettings.php'
	
	; update MediaWiki
	DetailPrint "Update MediaWiki database"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\maintenance\update.php"'
	
	; update SMW tables
	DetailPrint "Update SMW tables"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\maintenance\SMW_setup.php"'
	
	; setup SMW+
	DetailPrint "Update SMW+ tables"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\extensions\SMWHalo\maintenance\SMW_setup.php"'
	
	; unify Types in SMW
	DetailPrint "Unify types"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\maintenance\SMW_unifyTypes.php"'
	
	; update all semantic data
	DetailPrint "Refresh all semantic data"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\maintenance\SMW_refreshData.php"'
	
	; run job queue
	DetailPrint "Run job queue"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\maintenance\runJobs.php"'
	
	DetailPrint "Config customizations"
	CALL configCustomizationsForUpdate
FunctionEnd

Function changeConfigForSMWPlusUpdate
	ReadINIStr $PHP "$PLUGINSDIR\wikiupdate.ini" "Field 2" "state"
	CALL checkForApacheAndMySQL
	; update MediaWiki
	DetailPrint "Update MediaWiki database"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\maintenance\update.php"'
	
	; update SMW tables
	DetailPrint "Update SMW tables"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\maintenance\SMW_setup.php"'
	
	; update SMW+ tables
	DetailPrint "Update SMW+ tables"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\extensions\SMWHalo\maintenance\SMW_setup.php"'
	
	; update SMW+ data
	DetailPrint "Update SMW+ data"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\extensions\SMWHalo\maintenance\SMW_update.php"'
	
	; unify Types in SMW
	DetailPrint "Unify types"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\maintenance\SMW_unifyTypes.php"'
	
	; update all semantic data
	DetailPrint "Refresh all semantic data"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\maintenance\SMW_refreshData.php"'
	
	; run job queue
	DetailPrint "Run job queue"
	nsExec::ExecToLog '"$PHP" "$INSTDIR\maintenance\runJobs.php"'
	
	DetailPrint "Config customizations"
	CALL configCustomizationsForUpdate
FunctionEnd

Function configCustomizationsForNewWithXAMPP
	
    ; Set customization
    ;   Wikiname
    ;   Wikilogo
    ;   Wiki content language
    ;   Wiki skin
    ;   Use Context-sensitive help (true/false)
    ;   Install help pages (true/false)
	ReadINIStr $WIKINAME "$PLUGINSDIR\wikicustomize.ini" "Field 2" "state"
	ReadINIStr $WIKILOGO "$PLUGINSDIR\wikicustomize.ini" "Field 4" "state"
	ReadINIStr $WIKILANG "$PLUGINSDIR\wikicustomize.ini" "Field 6" "state"
	ReadINIStr $WIKISKIN "$PLUGINSDIR\wikicustomize.ini" "Field 8" "state"
	ReadINIStr $CSH "$PLUGINSDIR\wikicustomize.ini" "Field 9" "state"
	ReadINIStr $INSTHELP "$PLUGINSDIR\wikicustomize.ini" "Field 10" "state"
	
	${If} $WIKINAME == ""
		StrCpy $WIKINAME "MyWiki"
	${EndIf}
	${If} $WIKISKIN == ""
		StrCpy $WIKISKIN "ontoskin"
	${EndIf}
	${Switch} $WIKILANG
	  ${Case} 'English'
	    StrCpy $WIKILANG "en"
	    ${Break}
	  ${Case} 'German'
	    StrCpy $WIKILANG "de"
	    ${Break}
	  ${Default}
	    StrCpy $WIKILANG "en"
	    ${Break}
	${EndSwitch}
	${Switch} $CSH
		${Case} 1
			StrCpy $CSH "true"
		${Break}
		${Case} 0
			StrCpy $CSH "false"
		${Break}
	${EndSwitch}
	
	IfFileExists $WIKILOGO 0 logo_not_exists
		CopyFiles $WIKILOGO $INSTDIR\htdocs\mediawiki
		${GetFileName} $WIKILOGO $R0
		StrCpy $WIKILOGO "$R0"
		goto updateLocalSettings
	logo_not_exists:
		StrCpy $WIKILOGO "__notset__"
	updateLocalSettings:	
		${GetFileName} $WIKILOGO $R0
		nsExec::ExecToLog ' "$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\installer\changeLS.php" \
		wgSitename="$WIKINAME" wgDBname="semwiki_$WIKILANG" wgLogo=$$wgScriptPath/url:("$WIKILOGO") wgLanguageCode=$WIKILANG wgDefaultSkin="$WIKISKIN" \
		smwgAllowNewHelpQuestions=$CSH ls=LocalSettings.php'
	
	${If} $INSTHELP == 1
		DetailPrint "Installing helppages"
		DetailPrint "Starting XAMPP"
		SetOutPath "$INSTDIR"
		Exec "$INSTDIR\xampp_start.exe"
		CALL waitForApacheAndMySQL
        MessageBox MB_OK $(FIREWALL_COMPLAIN_INFO)
		SetOutPath "$INSTDIR\htdocs\mediawiki"
		nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\extensions\SMWHalo\maintenance\SMW_setup.php" --helppages'
	${EndIf} 
FunctionEnd

Function configCustomizationsForNewWithoutXAMPP
	ReadINIStr $PHP "$PLUGINSDIR\wikiinst.ini" "Field 2" "state"
	ReadINIStr $WIKINAME "$PLUGINSDIR\wikicustomize.ini" "Field 2" "state"
	ReadINIStr $WIKILOGO "$PLUGINSDIR\wikicustomize.ini" "Field 4" "state"
	ReadINIStr $WIKILANG "$PLUGINSDIR\wikicustomize.ini" "Field 6" "state"
	ReadINIStr $WIKISKIN "$PLUGINSDIR\wikicustomize.ini" "Field 8" "state"
	ReadINIStr $CSH "$PLUGINSDIR\wikicustomize.ini" "Field 9" "state"
	ReadINIStr $INSTHELP "$PLUGINSDIR\wikicustomize.ini" "Field 10" "state"
	
	${If} $WIKINAME == ""
		StrCpy $WIKINAME "MyWiki"
	${EndIf}
	${If} $WIKISKIN == ""
		StrCpy $WIKISKIN "ontoskin"
	${EndIf}
	${Switch} $WIKILANG
	  ${Case} 'English'
	    StrCpy $WIKILANG "en"
	    ${Break}
	  ${Case} 'German'
	    StrCpy $WIKILANG "de"
	    ${Break}
	  ${Default}
	    StrCpy $WIKILANG "en"
	    ${Break}
	${EndSwitch}
	${Switch} $CSH
		${Case} 1
			StrCpy $CSH "true"
		${Break}
		${Case} 0
			StrCpy $CSH "false"
		${Break}
	${EndSwitch}
	
	IfFileExists $WIKILOGO 0 logo_not_exists
		CopyFiles $WIKILOGO $INSTDIR
		${GetFileName} $WIKILOGO $R0
		StrCpy $WIKILOGO "$R0"
		goto updateLocalSettings
	logo_not_exists:
		StrCpy $WIKILOGO "__notset__"
	updateLocalSettings:
		${GetFileName} $WIKILOGO $R0
		nsExec::ExecToLog '"$PHP" "$INSTDIR\installer\changeLS.php" \
		wgSitename="$WIKINAME" wgDBname="semwiki_$WIKILANG" wgLogo=$$wgScriptPath/url:("$WIKILOGO") wgLanguageCode=$WIKILANG wgDefaultSkin="$WIKISKIN" \
		smwgAllowNewHelpQuestions=$CSH ls=LocalSettings.php'
	
	${If} $INSTHELP == 1
		DetailPrint "Installing helppages"
		
		nsExec::ExecToLog '"$PHP" "$INSTDIR\extensions\SMWHalo\maintenance\SMW_setup.php" --helppages'
	${EndIf} 
FunctionEnd

Function configCustomizationsForUpdate
	ReadINIStr $PHP "$PLUGINSDIR\wikiupdate.ini" "Field 2" "state"
	ReadINIStr $WIKINAME "$PLUGINSDIR\wikicustomize.ini" "Field 2" "state"
	ReadINIStr $WIKILOGO "$PLUGINSDIR\wikicustomize.ini" "Field 4" "state"
	ReadINIStr $WIKILANG "$PLUGINSDIR\wikicustomize.ini" "Field 6" "state"
	ReadINIStr $WIKISKIN "$PLUGINSDIR\wikicustomize.ini" "Field 8" "state"
	ReadINIStr $CSH "$PLUGINSDIR\wikicustomize.ini" "Field 9" "state"
	ReadINIStr $INSTHELP "$PLUGINSDIR\wikicustomize.ini" "Field 10" "state"
	
	${If} $WIKINAME == ""
		StrCpy $WIKINAME "__notset__"
	${EndIf}
	${If} $WIKISKIN == ""
		StrCpy $WIKISKIN "__notset__"
	${EndIf}
	${Switch} $WIKILANG
	  ${Case} 'English'
	    StrCpy $WIKILANG "en"
	    ${Break}
	  ${Case} 'German'
	    StrCpy $WIKILANG "de"
	    ${Break}
	  ${Default}
	    StrCpy $WIKILANG "__notset__"
	    ${Break}
	${EndSwitch}
	${Switch} $CSH
		${Case} 1
			StrCpy $CSH "true"
		${Break}
		${Case} 0
			StrCpy $CSH "false"
		${Break}
	${EndSwitch}
	
	IfFileExists $WIKILOGO 0 logo_not_exists
		CopyFiles $WIKILOGO $INSTDIR
		${GetFileName} $WIKILOGO $R0
		StrCpy $WIKILOGO "$R0"
		goto updateLocalSettings
	logo_not_exists:
		StrCpy $WIKILOGO "__notset__"
	updateLocalSettings:
		nsExec::ExecToLog '"$PHP" "$INSTDIR\installer\changeLS.php" \
		wgSitename="$WIKINAME" wgDBname="semwiki_$WIKILANG" wgLogo=$$wgScriptPath/url:("$WIKILOGO") wgLanguageCode=$WIKILANG wgDefaultSkin="$WIKISKIN" \
		smwgAllowNewHelpQuestions=$CSH ls=LocalSettings.php'
	
	${If} $INSTHELP == 1
		DetailPrint "Installing helppages"
        		
		nsExec::ExecToLog '"$PHP" "$INSTDIR\extensions\SMWHalo\maintenance\SMW_setup.php" --helppages'
	${EndIf} 
FunctionEnd

Function checkForSkype
    FindProcDLL::FindProc "Skype.exe"
    ${If} $R0 == 1
        MessageBox MB_OK "Seems that Skype is running. Please close it or change its config, so that it does not block TCP port 80."
    ${EndIf}
FunctionEnd

Function startXAMPP
   SectionGetFlags ${xampp} $0
   IntOp $0 $0 & ${SF_SELECTED}
   ${If} $0 == 1
       FindProcDLL::FindProc "apache.exe"
       IntOp $0 0 + $R0
       FindProcDLL::FindProc "mysqld.exe"
       IntOp $1 0 + $R0
       FindProcDLL::FindProc "mysqld-nt.exe"
       IntOp $1 $1 + $R0
       ${If} $0 == 0
       ${AndIf} $1 == 0
        CALL checkForSkype
        SetOutPath "$INSTDIR"
        Exec "$INSTDIR\xampp_start.exe"
       ${EndIf}
    ${EndIf}
FunctionEnd

Function createXAMPPShortcuts
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  ; Only with XAMPP installation
  ${If} $0 == 1
    CreateShortCut "$DESKTOP\${PRODUCT} ${VERSION} Start.lnk" "$INSTDIR\xampp_start.exe"
    CreateShortCut "$DESKTOP\${PRODUCT} ${VERSION} Stop.lnk" "$INSTDIR\xampp_stop.exe"
    ExecShell open "$INSTDIR\MYWIKI_NOTES"
  ${EndIf}
  
FunctionEnd
; Uninstaller


