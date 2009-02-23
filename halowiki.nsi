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

!define PRODUCTPATH "SMWPLUS"
!define PRODUCT "SMW+"
!define PRODUCT_CAPTION "SMW+"
!define VERSION "1.4.2"
!define BUILD_ID "421"


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
!define MUI_FINISHPAGE_RUN_TEXT "Start Lucene"
!define MUI_FINISHPAGE_RUN_FUNCTION "startLucene"

!define MUI_FINISHPAGE_SHOWREADME 
!define MUI_FINISHPAGE_SHOWREADME_FUNCTION createXAMPPShortcuts
!define MUI_FINISHPAGE_SHOWREADME_TEXT "Create XAMPP shortcuts"
!define MUI_FINISHPAGE_SHOWREADME_CHECKED

!define MUI_FINISHPAGE_TEXT "The installation or update was successful! If you made a new installation you may add some shortcuts on the desktop to start/stop you wiki easily. \
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
InstallDir "$PROGRAMFILES\Ontoprise\${PRODUCTPATH}\"
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
 
Page custom showWikiCustomize checkWikiCustomize 

!insertmacro MUI_PAGE_INSTFILES
;!define MUI_PAGE_CUSTOMFUNCTION_LEAVE checkForSkype
;!insertmacro MUI_PAGE_FINISH

;!undef MUI_PAGE_CUSTOMFUNCTION_SHOW
!define MUI_PAGE_CUSTOMFUNCTION_SHOW FinishPageShow
!insertmacro MUI_PAGE_FINISH




; Language ------------------------------

!insertmacro MUI_LANGUAGE "English"

; Installation types ---------------------------

!ifndef NOINSTTYPES ; only if not defined
  InstType "New"
  InstType "Update"
  InstType /COMPONENTSONLYONCUSTOM 
!endif

AutoCloseWindow false
ShowInstDetails hide

;--------------------------------

; Basic variables for environment
Var PHP
Var MEDIAWIKIDIR


;Wiki customizations
Var WIKINAME 
Var WIKILOGO 
Var WIKILANG 
Var WIKISKIN 


; Helper
Var CHOOSEDIRTEXT
Var INSTALLTYPE

Function ".onInit"
  InitPluginsDir
  File /oname=$PLUGINSDIR\wikicustomize.ini "..\SMWPlusInstaller\gui\wikicustomize.ini"

  
FunctionEnd


Function .onSelChange
 GetCurInstType $R0
 ${Switch} $R0
    ${Case} 0
        SendMessage $mui.ComponentsPage.DescriptionText ${WM_SETTEXT} 0 "STR:Normal installation. No further software needed."
        ${Break}
    ${Case} 1
        SendMessage $mui.ComponentsPage.DescriptionText ${WM_SETTEXT} 0 "STR:Choose this if you want to do an update."
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
    File /r /x .svn ..\xampp\*
  !endif
  
SectionEnd

Section "-CopyInstaller"
  SectionIn 1 2 3
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  SetOutPath "$INSTDIR\htdocs\mediawiki\installer"
  CreateDirectory "$INSTDIR\htdocs\mediawiki\installer"
  !ifndef NOFILES
    File /r /x .svn /x *.exe /x *.nsi ..\SMWPlusInstaller\*
    ${If} $0 == 1
    CreateShortCut "$DESKTOP\${PRODUCT} ${VERSION} Main Page.lnk" "http://localhost/mediawiki/index.php" \
    "" "$INSTDIR\htdocs\mediawiki\installer\images\smwplus_32.ico" 0
    ${EndIf}
  !endif
SectionEnd

Section "Online Help" ohelp
    SectionIn 1 2 3
    SectionGetFlags ${xampp} $0
    IntOp $0 $0 & ${SF_SELECTED}
    
    SetOutPath "$INSTDIR\help"
    CreateDirectory "$INSTDIR\help"
    !ifndef NOFILES
        File /r ..\com.ontoprise.smwplus.help\compiled\*
        ${If} $0 == 1
            CreateShortCut "$DESKTOP\${PRODUCT} ${VERSION} Help.lnk" "$INSTDIR\help\Help.exe"
        ${EndIf}
    !endif
SectionEnd



SectionGroup "${PRODUCT} ${VERSION}" 
Section "${PRODUCT} ${VERSION} core" smwplus
  SectionIn 1 2 RO
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  ${If} $0 == 0 
  ; check for AdminSettings.php
    tryagain:
    IfFileExists $INSTDIR\htdocs\mediawiki\AdminSettings.php 0 as_noexists
        goto setpath
        as_noexists:
            MessageBox MB_OK|MB_ICONSTOP  "Could not find AdminSettings.php. \
            Please create one using AdminSettingsTemplate.php and continue afterwards."
            goto tryagain
            
  setpath:
  ${EndIf}
  ; Set output path 
  
  
  SetOutPath "$INSTDIR\htdocs\mediawiki"
  CreateDirectory "$INSTDIR\htdocs\mediawiki"
  
  ; Copy files and config 
  ${If} $0 == 1 
     IntOp $INSTALLTYPE 0 + 1
  ${Else}
    IfFileExists $INSTDIR\htdocs\mediawiki\extensions\SMWHalo\*.* 0 notexistsSMWPlus
        
    IntOp $INSTALLTYPE 0 + 0
    goto copyfiles
    
    notexistsSMWPlus:
       MessageBox MB_OK|MB_ICONSTOP  "Could not find wiki installation. Abort here." 
       Abort
            
                
  ${EndIf}
  
  copyfiles:
      !ifndef NOFILES
            
            File /r /x .svn /x *.zip /x *.exe /x *.cache /x *.settings /x LocalSettings.php /x ACLs.php /x *.nsi *
            File /oname=extensions\SMWHalo\bin\xpdf\pdftotext.exe extensions\SMWHalo\bin\xpdf\pdftotext.exe
            File /oname=extensions\SMWHalo\bin\antiword\antiword.exe extensions\SMWHalo\bin\antiword\antiword.exe
            
      !endif  
   
  ;configure:
      ${If} $INSTALLTYPE == 0 
        CALL changeConfigForSMWPlusUpdate
      ${EndIf}
      ${If} $INSTALLTYPE == 1
        CALL changeConfigForFullXAMPP
      ${EndIf}
     
SectionEnd



Section "Semantic Forms" semforms
  SectionIn 1 RO
  DetailPrint "Configure Semantic Forms extension"
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  SetOutPath "$INSTDIR\htdocs\mediawiki"
  StrCpy $PHP "$INSTDIR\php\php.exe"
  StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"
  
  ; change config file
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" importSemForms=1 ls=LocalSettings.php'
SectionEnd

Section "Treeview" treeview
  SectionIn 1 RO
  DetailPrint "Configure Semantic Calendar extension"
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  SetOutPath "$INSTDIR\htdocs\mediawiki"
  StrCpy $PHP "$INSTDIR\php\php.exe"
  StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"
  
  ; change config file
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" importTreeview=1 ls=LocalSettings.php'
SectionEnd

Section "WYSIWYG" wysiwyg
  SectionIn 1
  DetailPrint "Configure WYSIWYG extension"
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  SetOutPath "$INSTDIR\htdocs\mediawiki"
  StrCpy $PHP "$INSTDIR\php\php.exe"
  StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"
  
  ; change config file
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" importWYSIWYG=1 ls=LocalSettings.php'
SectionEnd

Section "Lucene search" lucene
    #SectionIn 1 2 3
    SectionGetFlags ${xampp} $0
    IntOp $0 $0 & ${SF_SELECTED}
    
    CreateDirectory "$INSTDIR\lucene"
    CreateDirectory "$INSTDIR\lucene\lib"
    !ifndef NOFILES
        SetOutPath "$INSTDIR\lucene\lib"
        File /r ..\LuceneWikiServer\lib\*.jar
        
        SetOutPath "$INSTDIR\lucene"
        File ..\LuceneWikiServer\LuceneSearch.jar
        File ..\LuceneWikiServer\*.bat
        File ..\LuceneWikiServer\linkd.exe
        File ..\LuceneWikiServer\*.txt
        File ..\LuceneWikiServer\*.properties
        File ..\LuceneWikiServer\*.template
        File ..\LuceneWikiServer\smwplus_db.xml
    !endif
        
        SetOutPath "$INSTDIR\lucene"
        StrCpy $PHP "$INSTDIR\php\php.exe"
        StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"
        
        ; dump db 
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in=dump.bat.template out=dump.bat noslash=true php-path="$PHP" wiki-path="$MEDIAWIKIDIR" lucene-path="$INSTDIR\lucene"'
        nsExec::ExecToLog 'dump.bat'
               
        ; adapt global.conf.template file
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in=global.conf.template out=global.conf wiki-db=semwiki_en ip=true'
        ; adapt lsearch.conf.template file
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in=lsearch.conf.template out=lsearch.conf project-path="$INSTDIR\lucene" wiki-path="$MEDIAWIKIDIR"'
         ; adapt start.bat.template file
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in=start.bat.template out=start.bat lucene-path="$INSTDIR\lucene" ip=true'
        ; Build Lucene index
        nsExec::ExecToLog 'buildall.bat smwplus_db.xml semwiki_en'
        
        ;change LocalSettings
        SetOutPath "$MEDIAWIKIDIR"
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" importUS=1 ls=LocalSettings.php'
        
        ${If} $0 == 1
            SetOutPath "$INSTDIR\lucene"
            CreateShortCut "$DESKTOP\${PRODUCT} ${VERSION} Start Lucene.lnk" "$INSTDIR\lucene\start.bat"
        ${EndIf}
       
SectionEnd

SectionGroupEnd


;Languages (english)
LangString DESC_xampp ${LANG_ENGLISH} "Select XAMPP contains the server infrastructure."
LangString DESC_smwplus ${LANG_ENGLISH} "${PRODUCT} ${VERSION}"
LangString DESC_ohelp ${LANG_ENGLISH} "Eclipse-based online help."
LangString DESC_lucene ${LANG_ENGLISH} "Lucene based full-text index."

LangString DESC_semforms ${LANG_ENGLISH} "Semantic Forms ease the annotation process by providing a simple interface."
LangString DESC_treeview ${LANG_ENGLISH} "The Treeview extension allows a hierarchical displaying of content or links."
LangString DESC_wysiwyg ${LANG_ENGLISH} "The WYSIWYG extension allows editing with a Word-like comfortable editor."

LangString CUSTOMIZE_PAGE_TITLE ${LANG_ENGLISH} "Customize your wiki"
LangString CUSTOMIZE_PAGE_SUBTITLE ${LANG_ENGLISH} "Set wiki name or logo"

LangString SELECT_XAMPP_DIR ${LANG_ENGLISH} "Select an empty directory where to install XAMPP and the wiki."
LangString SELECT_NEWUPDATE_DIR ${LANG_ENGLISH} "Select an existing installation to update."
LangString START_SERVERS ${LANG_ENGLISH} "Please start Apache and MySQL"
LangString COULD_NOT_START_SERVERS ${LANG_ENGLISH} "Apache and MySQL could not be started for some reason. Installation may not be complete!"
LangString FIREWALL_COMPLAIN_INFO ${LANG_ENGLISH} "If Windows Firewall complains, unblock Apache and MySQL processes. Then continue."

;Assign language strings to sections
!insertmacro MUI_FUNCTION_DESCRIPTION_BEGIN
    !insertmacro MUI_DESCRIPTION_TEXT ${xampp} $(DESC_xampp)
    !insertmacro MUI_DESCRIPTION_TEXT ${smwplus} $(DESC_smwplus)
    !insertmacro MUI_DESCRIPTION_TEXT ${ohelp} $(DESC_ohelp)
    !insertmacro MUI_DESCRIPTION_TEXT ${lucene} $(DESC_lucene)
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



Function showWikiCustomize

    SectionGetFlags ${xampp} $0
    IntOp $0 $0 & ${SF_SELECTED}
    
    ${If} $0 == 1 
        !insertmacro MUI_HEADER_TEXT $(CUSTOMIZE_PAGE_TITLE) $(CUSTOMIZE_PAGE_SUBTITLE)
          Push $R0
          InstallOptions::dialog $PLUGINSDIR\wikicustomize.ini
          Pop $R0

    ${Else}
        Abort
    ${EndIf}
         
FunctionEnd

Function checkWikiCustomize
  CALL checkForSkype
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
    CALL configCustomizationsForNew
FunctionEnd


Function changeConfigForSMWPlusUpdate
    
    CALL checkForApacheAndMySQL
    ; update MediaWiki
    DetailPrint "Update MediaWiki database"
    nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\maintenance\update.php"'
    
    ; update SMW tables
    DetailPrint "Update SMW tables"
    nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\extensions\SemanticMediaWiki\maintenance\SMW_setup.php"'
    
    ; update SMW+ data
    DetailPrint "Refresh semantic data"
    nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\extensions\SemanticMediaWiki\maintenance\SMW_refreshData.php"'
    
    ; run job queue
    DetailPrint "Run job queue"
    nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\maintenance\runJobs.php"'
    
    DetailPrint "Config customizations"
    CALL configCustomizationsForUpdate
FunctionEnd

Function configCustomizationsForNew
    
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
    
    
    ${If} $WIKINAME == ""
        StrCpy $WIKINAME "MyWiki"
    ${EndIf}
    ${If} $WIKISKIN == ""
        StrCpy $WIKISKIN "ontoskin2"
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
        smwgAllowNewHelpQuestions="true" ls=LocalSettings.php'
    
    DetailPrint "Installing helppages"
        DetailPrint "Starting XAMPP"
        SetOutPath "$INSTDIR"
        Exec "$INSTDIR\xampp_start.bat"
        CALL waitForApacheAndMySQL
        MessageBox MB_OK $(FIREWALL_COMPLAIN_INFO)
        SetOutPath "$INSTDIR\htdocs\mediawiki"
        nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\extensions\SMWHalo\maintenance\SMW_setup.php" --helppages'
FunctionEnd



Function configCustomizationsForUpdate
    ReadINIStr $PHP "$PLUGINSDIR\wikiupdate.ini" "Field 2" "state"
    ReadINIStr $WIKINAME "$PLUGINSDIR\wikicustomize.ini" "Field 2" "state"
    ReadINIStr $WIKILOGO "$PLUGINSDIR\wikicustomize.ini" "Field 4" "state"
    ReadINIStr $WIKILANG "$PLUGINSDIR\wikicustomize.ini" "Field 6" "state"
    ReadINIStr $WIKISKIN "$PLUGINSDIR\wikicustomize.ini" "Field 8" "state"
    
    
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
        smwgAllowNewHelpQuestions="true" ls=LocalSettings.php'
    
    DetailPrint "Updating helppages"
        DetailPrint "Starting XAMPP"
        SetOutPath "$INSTDIR"
        Exec "$INSTDIR\xampp_start.bat"
        CALL waitForApacheAndMySQL
        MessageBox MB_OK $(FIREWALL_COMPLAIN_INFO)
        SetOutPath "$INSTDIR\htdocs\mediawiki"
        nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\extensions\SMWHalo\maintenance\SMW_setup.php" --removehelppages'
        nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\extensions\SMWHalo\maintenance\SMW_setup.php" --helppages'
FunctionEnd

Function checkForSkype
    FindProcDLL::FindProc "Skype.exe"
    ${If} $R0 == 1
        MessageBox MB_OKCANCEL  "Seems that Skype is running. Please close it or change its config, so that it does not block TCP port 80." IDOK ok IDABORT abortinstaller 
        abortInstaller:
            Abort
        ok:
    ${EndIf}
FunctionEnd

/*Function startXAMPP
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
        Exec "$INSTDIR\xampp_start.bat"
       ${EndIf}
    ${EndIf}
FunctionEnd*/

Function startLucene
   SectionGetFlags ${lucene} $0
   IntOp $0 $0 & ${SF_SELECTED}
   ${If} $0 == 1
       SetOutPath "$INSTDIR\lucene"
       Exec "$INSTDIR\lucene\start.bat"
   ${EndIf}
FunctionEnd

Function createXAMPPShortcuts
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  SetOutPath "$INSTDIR"
  ; Only with XAMPP installation
  ${If} $0 == 1
    CreateShortCut "$DESKTOP\${PRODUCT} ${VERSION} Start.lnk" "$INSTDIR\xampp_start.bat"
    CreateShortCut "$DESKTOP\${PRODUCT} ${VERSION} Stop.lnk" "$INSTDIR\xampp_stop.exe"
    ExecShell open "$INSTDIR\MYWIKI_NOTES"
  ${EndIf}
  
FunctionEnd

Function FinishPageShow
  SectionGetFlags ${lucene} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  ${If} $0 == 0
 
    GetDlgItem $R0 $mui.FinishPage 1203
    ShowWindow $R0 ${SW_HIDE}
  ${EndIf}
  
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  ${If} $0 == 0
    GetDlgItem $R0 $mui.FinishPage 1204
    ShowWindow $R0 ${SW_HIDE}
  ${Endif}
FunctionEnd
; Uninstaller


