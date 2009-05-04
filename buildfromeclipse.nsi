/*
* Halowiki.nsi (c) Ontoprise 2008-2009
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
!define VERSION "1.4.3"
!define BUILD_ID "431"
!define REQUIRED_JAVA_VERSION 16

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
!define MUI_FINISHPAGE_RUN_TEXT "Start Lucene as Windows service"
!define MUI_FINISHPAGE_RUN_FUNCTION "startLucene"


!define MUI_FINISHPAGE_TEXT "Installation of ${PRODUCT} ${VERSION} is completed. You got some new shortcuts in the startmenu. \
If you made an update you already have some of the shortcuts on the desktop and you may move them to the startmenu. The main page can be opened by clicking on '${PRODUCT} ${VERSION} Main Page'."
!define MUI_FINISHPAGE_LINK "Visit the ontoprise website for the latest news"
!define MUI_FINISHPAGE_LINK_LOCATION "http://wiki.ontoprise.com/"

;Start Menu Folder Page Configuration
!define MUI_STARTMENUPAGE_REGISTRY_ROOT "HKCU" 
!define MUI_STARTMENUPAGE_REGISTRY_KEY "Software\Ontoprise\${PRODUCT} ${VERSION}" 
!define MUI_STARTMENUPAGE_REGISTRY_VALUENAME "Start Menu Folder"


!ifdef NOCOMPRESS
SetCompress off
!endif
;--------------------------------

Name "${PRODUCT} v${VERSION}"
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
BrandingText "ontoprise GmbH 2009 - wiki.ontoprise.de - Build: ${BUILD_ID}"
LicenseText "GPL-License"
LicenseData "..\SMWPlusInstaller\gpl.txt"
ComponentText "Choose type of installation"
RequestExecutionLevel admin

Var FILE_LIST
Var STARTMENU_FOLDER
Var MUI_TEMP
; Pages --------------------------------

!define MUI_ICON "..\SMWPlusInstaller\images\smwplus_32.ico"
  
!insertmacro MUI_PAGE_WELCOME
!insertmacro MUI_PAGE_LICENSE "..\SMWPlusInstaller\gpl.txt"
!define MUI_PAGE_CUSTOMFUNCTION_SHOW initComponentsPage
!define MUI_PAGE_CUSTOMFUNCTION_LEAVE checkForNeededProcess
!insertmacro MUI_PAGE_COMPONENTS

!define MUI_PAGE_CUSTOMFUNCTION_PRE preDirectory
!insertmacro MUI_PAGE_DIRECTORY
!insertmacro MUI_PAGE_STARTMENU Application $STARTMENU_FOLDER
 
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
Var LUCENE_AS_SERVICE

Function ".onInit"
  InitPluginsDir
  File /oname=$PLUGINSDIR\wikicustomize.ini "..\SMWPlusInstaller\gui\wikicustomize.ini"

  
FunctionEnd


Function .onSelChange
 GetCurInstType $R0
 ${Switch} $R0
    ${Case} 0
        SendMessage $mui.ComponentsPage.DescriptionText ${WM_SETTEXT} 0 "STR:Select 'new' if you are setting up a new installation of SMW+"
        ${Break}
    ${Case} 1
        SendMessage $mui.ComponentsPage.DescriptionText ${WM_SETTEXT} 0 "STR:Select 'update' if you want to upgrade your existing installation of SMW+ to a new version."
        ${Break}
    ${Default} 
        SendMessage $mui.ComponentsPage.DescriptionText ${WM_SETTEXT} 0 "STR:Select 'custom' if you want to choose the compontents to install"
        ${Break}
  ${EndSwitch}

FunctionEnd

Function initComponentsPage
    SendMessage $mui.ComponentsPage.DescriptionText ${WM_SETTEXT} 0 "STR:Select 'new' if you are setting up a new installation of SMW+"
FunctionEnd


; ---- Install sections ---------------


Section "XAMPP" xampp
  SectionIn 1
  SetOutPath "$INSTDIR"
  CreateDirectory "$INSTDIR"
  !ifndef NOFILES
    File /r /x .svn /x CVS ..\xampp\*
  !endif
  ;Store installation folder
    WriteRegStr HKCU "Software\Ontoprise\${PRODUCT} ${VERSION}" "" $INSTDIR
    WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${PRODUCT} ${VERSION}" "DisplayName" "${PRODUCT} ${VERSION}"
    WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${PRODUCT} ${VERSION}" "UninstallString" "$INSTDIR\Uninstall.exe"
    WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${PRODUCT} ${VERSION}" "DisplayIcon" $INSTDIR\htdocs\mediawiki\installer\images\smwplus_32.ico
   
    
    
    CreateDirectory "$SMPROGRAMS\$STARTMENU_FOLDER"
   
    CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\Start ${PRODUCT}.lnk" "$INSTDIR\xampp_start.bat"
    CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\Stop ${PRODUCT}.lnk" "$INSTDIR\xampp_stop.exe"
    
SectionEnd

Section "-CopyInstaller"
  SectionIn 1 2 3
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  SetOutPath "$INSTDIR\htdocs\mediawiki\installer"
  CreateDirectory "$INSTDIR\htdocs\mediawiki\installer"
  !ifndef NOFILES
    File /r /x CVS /x .svn /x *.exe /x *.nsi ..\SMWPlusInstaller\*
    ${If} $0 == 1
    
    #CreateShortCut "$DESKTOP\${PRODUCT} ${VERSION} Main Page.lnk" "http://localhost/mediawiki/index.php" \
    #"" "$INSTDIR\htdocs\mediawiki\installer\images\smwplus_32.ico" 0
    CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\${PRODUCT} ${VERSION} Main Page.lnk" "http://localhost/mediawiki/index.php" \
    "" "$INSTDIR\htdocs\mediawiki\installer\images\smwplus_32.ico" 0
    ${EndIf}
  !endif
SectionEnd

/*Section "Online Help" ohelp
    SectionIn 1 2 3
    SectionGetFlags ${xampp} $0
    IntOp $0 $0 & ${SF_SELECTED}
    
    SetOutPath "$INSTDIR\help"
    CreateDirectory "$INSTDIR\help"
    !ifndef NOFILES
        File /r /x CVS ..\com.ontoprise.smwplus.help\compiled\*
        ${If} $0 == 1
            CreateDirectory "$SMPROGRAMS\$STARTMENU_FOLDER"
            CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\${PRODUCT} ${VERSION} Help.lnk" "$INSTDIR\help\Help.exe"
        ${EndIf}
    !endif
SectionEnd
*/


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
            
            File /r /x .svn /x CVS /x *.zip /x *.exe /x *.cache /x *.settings /x LocalSettings.php /x ACLs.php /x *.nsi /x SKOSExpander.php * 
            File /oname=extensions\SMWHalo\bin\xpdf\pdftotext.exe extensions\SMWHalo\bin\xpdf\pdftotext.exe
            File /oname=extensions\SMWHalo\bin\antiword\antiword.exe extensions\SMWHalo\bin\antiword\antiword.exe
                        
      !endif  
  ;Create uninstaller
  WriteUninstaller "$INSTDIR\Uninstall.exe"
  CreateDirectory "$SMPROGRAMS\$STARTMENU_FOLDER"
  CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\Uninstall.lnk" "$INSTDIR\Uninstall.exe"
    
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
    SectionIn 1 2
    SectionGetFlags ${xampp} $0
    IntOp $0 $0 & ${SF_SELECTED}
    
    CreateDirectory "$INSTDIR\lucene"
    CreateDirectory "$INSTDIR\lucene\lib"
    CreateDirectory "$INSTDIR\lucene\service"
    #!ifndef NOFILES
        SetOutPath "$INSTDIR\lucene\lib"
        File /r ..\LuceneWikiServer\lib\*.jar
        
        SetOutPath "$INSTDIR\lucene\service"
        File /r /x CVS /x .svn ..\LuceneWikiServer\service\*
        
        SetOutPath "$INSTDIR\lucene"
        File ..\LuceneWikiServer\LuceneSearch.jar
        File ..\LuceneWikiServer\*.bat
        File ..\LuceneWikiServer\linkd.exe
        File ..\LuceneWikiServer\*.txt
        File ..\LuceneWikiServer\*.properties
        File ..\LuceneWikiServer\*.template
        File ..\LuceneWikiServer\smwplus_db.xml
    #!endif
        
        SetOutPath "$INSTDIR\lucene"
        StrCpy $PHP "$INSTDIR\php\php.exe"
        StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"
        
        ; dump db 
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in=dump.bat.template out=dump.bat noslash=true php-path="$PHP" wiki-path="$MEDIAWIKIDIR" lucene-path="$INSTDIR\lucene"'
        nsExec::ExecToLog 'dump.bat'
               
        ; adapt global.conf.template file
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in=global.conf.template out=global.conf wiki-db=semwiki_en ip=true'
        ; adapt lsearch.conf.template file
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in=lsearch.conf.template out=lsearch.conf project-path="$INSTDIR\lucene" wiki-path="$MEDIAWIKIDIR" project-path-url="$INSTDIR\lucene" wiki-path-url="$MEDIAWIKIDIR"'
         ; adapt start.bat.template file
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in=start.bat.template out=start.bat lucene-path-url="$INSTDIR\lucene" lucene-path="$INSTDIR\lucene" ip=true'
        ; Build Lucene index
        nsExec::ExecToLog 'buildall.bat smwplus_db.xml semwiki_en'
        
        ;change LocalSettings
        SetOutPath "$MEDIAWIKIDIR"
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" importUS=1 ls=LocalSettings.php'
        
        CALL installLuceneAsService
        ${If} $0 == 1
            SetOutPath "$INSTDIR\lucene"
            CreateDirectory "$SMPROGRAMS\$STARTMENU_FOLDER"
            CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\${PRODUCT} ${VERSION} Start Lucene.lnk" "$INSTDIR\lucene\start.bat"
            
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
LangString START_SERVERS ${LANG_ENGLISH} "To update your installation to ${PRODUCT} ${VERSION} please start Apache web server $\nand MySQL database management system. $\nIf you have a previous installation of SMW+ on this server, then you $\nwill find a short cut on the desktop named 'SMW+ 1.x Start'. $\nDouble clicking this short cut launches Apache and MySQL."
LangString COULD_NOT_START_SERVERS ${LANG_ENGLISH} "Apache and MySQL could not be started for some reason. Installation may not be complete!"
LangString FIREWALL_COMPLAIN_INFO ${LANG_ENGLISH} "Windows firewall may block the apache and mySQL processes. $\n If this is the case with your installation, then unblock both processes in the pop-up windows $\n and click on 'OK' to finish the installation process."

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
    MessageBox MB_ICONEXCLAMATION|MB_OKCANCEL $(START_SERVERS) IDOK 0 IDCANCEL skipCheck
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
    ${Switch} $WIKISKIN
        ${Case} 'ontoskin (grayish, based on monobook)'
            StrCpy $WIKISKIN "ontoskin"
            ${Break}
        ${Case} 'ontoskin2 (blue)'
            StrCpy $WIKISKIN "ontoskin2"
            ${Break}
         ${Default}
            StrCpy $WIKISKIN "ontoskin2"
        ${Break}
    ${EndSwitch}
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
        MessageBox MB_OK|MB_ICONINFORMATION $(FIREWALL_COMPLAIN_INFO)
        SetOutPath "$INSTDIR\htdocs\mediawiki"
        nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\extensions\SMWHaloHelp\maintenance\setup.php" --install'
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
        #Exec "$INSTDIR\xampp_start.bat"
        #CALL waitForApacheAndMySQL
        MessageBox MB_OK $(FIREWALL_COMPLAIN_INFO)
        SetOutPath "$INSTDIR\htdocs\mediawiki"
        nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\extensions\SMWHaloHelp\maintenance\setup.php" --deinstall'
        nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\extensions\SMWHaloHelp\maintenance\setup.php" --install'
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
   SetOutPath "$INSTDIR\lucene"
   StrCmp "yes" $LUCENE_AS_SERVICE LuceneAsService NotAsService
   LuceneAsService:
       Exec "net start LuceneWiki"
   Goto Done
   NotAsService:
   ${If} $0 == 1
       Exec "$INSTDIR\lucene\start.bat"
   ${EndIf}
   Done:
FunctionEnd


Function FinishPageShow
  SectionGetFlags ${lucene} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  ${If} $0 == 0
 
    GetDlgItem $R0 $mui.FinishPage 1203
    ShowWindow $R0 ${SW_HIDE}
  ${EndIf}
  
  /*SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  ${If} $0 == 0
    GetDlgItem $R0 $mui.FinishPage 1204
    ShowWindow $R0 ${SW_HIDE}
  ${Endif}*/
FunctionEnd

Var JAVA_HOME
Var JAVA_HOME_SHORT
Var JAVA_VER
Var JAVA_INSTALLATION_MSG


Function installLuceneAsService
   Call LocateJVM
   StrCpy $LUCENE_AS_SERVICE "no"
   StrCmp "" $JAVA_INSTALLATION_MSG Success InstallJava
    Success:
        SetOutPath "$INSTDIR\lucene"
                          
        StrCpy $PHP "$INSTDIR\php\php.exe"
        StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"
        
        ; create install script for windows service registration
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in=installAsService.bat.template out=installAsService.bat noslash=true ip=true java_home="$JAVA_HOME"'
        
        ; stop lucene
        DetailPrint "Stopping lucene (if necessary)"
        nsExec::ExecToLog 'net stop LuceneWiki'
        
        ; uninstall service
        DetailPrint "Uninstall lucene as service (if necessary)"
        nsExec::ExecToLog '"$INSTDIR\lucene\uninstallAsService.bat"'
        
        ; run script and register service 
        DetailPrint "Register lucene as windows service"
        nsExec::ExecToLog '"$INSTDIR\lucene\installAsService.bat"'
        
             
        DetailPrint "Lucene service installed."
        StrCpy $LUCENE_AS_SERVICE "yes"
        Goto Done
        
    InstallJava:
       MessageBox MB_OK "Lucene is installed but will not work without Java 1.6 Runtime. Furthermore, it is not installed as a service."
        
    Done:
FunctionEnd

Function LocateJVM
    Push $0
    Push $1
    
    ReadRegStr $JAVA_VER HKLM "SOFTWARE\JavaSoft\Java Runtime Environment" "CurrentVersion"
#    MessageBox MB_OK "Detected Java version: $JAVA_VER"
    DetailPrint "Detected Java version: [$JAVA_VER]."
    StrCmp "" $JAVA_VER JavaNotPresent CheckJavaVer

    JavaNotPresent:
        DetailPrint "No Java detected."
        StrCpy $JAVA_INSTALLATION_MSG "Java Runtime Environment is not \
             installed on your computer. You need version 1.6 or newer to \
             run this program."
#        MessageBox MB_OK "$JAVA_INSTALLATION_MSG java_ver:$JAVA_VER"
        Goto Done

    CheckJavaVer:
#        MessageBox MB_OK "Java is present, check java version"
        DetailPrint "Checking Java version ..."
        ReadRegStr $0 HKLM "SOFTWARE\JavaSoft\Java Runtime Environment\$JAVA_VER" JavaHome
        GetFullPathName $JAVA_HOME "$0"
        GetFullPathName /SHORT $JAVA_HOME_SHORT "$0"
        StrCpy $0 $JAVA_VER 1 0
        StrCpy $1 $JAVA_VER 1 2
        StrCpy $JAVA_VER "$0$1"
        IntCmp ${REQUIRED_JAVA_VERSION} $JAVA_VER FoundCorrectJavaVer FoundCorrectJavaVer JavaVerNotCorrect
        
    FoundCorrectJavaVer:
#        MessageBox MB_OK "Found valid Java version"
        DetailPrint "Found valid Java version."
        IfFileExists "$JAVA_HOME_SHORT\bin\javaw.exe" 0 JavaNotPresent
        Goto Done
        
    JavaVerNotCorrect:
#        MessageBox MB_OK "Java version not correct"
        DetailPrint "Found invalid Java version."
        StrCpy $JAVA_INSTALLATION_MSG "The version of Java Runtime Environment \
            installed on your computer is $JAVA_VER. Version ${REQUIRED_JAVA_VERSION} or newer is required to \
            run this program."
        
    Done:
        Pop $1
        Pop $0
FunctionEnd
; Uninstaller
Function un.uninstallAsWindowsService
    SetOutPath "$INSTDIR\lucene"
    nsExec::ExecToLog '"$INSTDIR\lucene\UninstallAsService.bat"' 
    DetailPrint "Lucene service uninstalled."
    SetOutPath "c:\temp\halo" #dummy to make installation dir removable
FunctionEnd

Section "Uninstall"

    !insertmacro MUI_STARTMENU_GETFOLDER Application $MUI_TEMP

    MessageBox MB_OKCANCEL|MB_ICONEXCLAMATION \
        "Please note that all running ${PRODUCT} instances must be closed before uninstall. $\n$\n \
        Are you sure you want to deinstall the ${PRODUCT}? All files (including \
        changed configuration files and log files) will be removed." \
    IDOK Deinstall
    # MessageBox MB_OK "User aborted!"
    goto FinalExit

    Deinstall:
#        MessageBox MB_OK "User said OK!"
    
    Call un.uninstallAsWindowsService
    
    # Delete all start menu entries
    Delete "$SMPROGRAMS\$MUI_TEMP\Uninstall.lnk"
    Delete "$SMPROGRAMS\$MUI_TEMP\Start ${PRODUCT}.lnk"
    Delete "$SMPROGRAMS\$MUI_TEMP\Stop ${PRODUCT}.lnk"
    Delete "$SMPROGRAMS\$MUI_TEMP\${PRODUCT} ${VERSION} Start Lucene.lnk" 
    Delete "$SMPROGRAMS\$MUI_TEMP\${PRODUCT} ${VERSION} Main Page.lnk"
    #Delete "$SMPROGRAMS\$MUI_TEMP\${PRODUCT} ${VERSION} Help.lnk"
    
    ;Delete start menu IF THE MENU IS EMPTY.
    StrCpy $MUI_TEMP "$SMPROGRAMS\$MUI_TEMP"

    startMenuDeleteLoop:
        RMDir $MUI_TEMP
        GetFullPathName $MUI_TEMP "$MUI_TEMP\.."
        
        IfErrors startMenuDeleteLoopDone

        StrCmp $MUI_TEMP $SMPROGRAMS startMenuDeleteLoopDone startMenuDeleteLoop
    startMenuDeleteLoopDone:

    DeleteRegKey HKCU "Software\Ontoprise\${PRODUCT} ${VERSION}"
    DeleteRegKey HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${PRODUCT} ${VERSION}"

    Delete "$INSTDIR\*"
   
    RMDir /r "$INSTDIR\webalizer"
    RMDir /r "$INSTDIR\anonymous"
    RMDir /r "$INSTDIR\apache"
    RMDir /r "$INSTDIR\tmp"
    RMDir /r "$INSTDIR\sendmail"
    RMDir /r "$INSTDIR\cgi-bin"
    RMDir /r "$INSTDIR\security"
    RMDir /r "$INSTDIR\FileZillaFTP"
    RMDir /r "$INSTDIR\phpMyAdmin"
    RMDir /r "$INSTDIR\php"
    RMDir /r "$INSTDIR\perl"
    RMDir /r "$INSTDIR\help"
    RMDir /r "$INSTDIR\htdocs"
    RMDir /r "$INSTDIR\install"
    RMDir /r "$INSTDIR\licenses"
    RMDir /r "$INSTDIR\lucene"
    RMDir /r "$INSTDIR\MercuryMail"
    RMDir /r "$INSTDIR\mysql"
    RMDir /r "$INSTDIR\webdav"
    
    ; only remove if empty
    RMDir "$INSTDIR"

    ; If OntoStudio is installed in:
    ; c:\Program Files\Ontoprise\OntoStudio
    ; we want the uninstaller to remove
    ; c:\Program Files\Ontoprise\OntoStudio
    ; AND
    ; c:\Program Files\Ontoprise
    ; but the latter only if there's no other file or directory
    ; The following code accomplishes this.
    Push 5 #maximum amount of directories to remove
    Push "$INSTDIR" #input string

    Exch $R0 ;input string
    Exch
    Exch $R1 ;maximum number of dirs to check for
    Push $R2
    Push $R3
    Push $R4
    Push $R5
       IfFileExists "$R0\*.*" 0 +2
       RMDir "$R0"
     StrCpy $R5 0
    top:
     StrCpy $R2 0
     StrLen $R4 $R0
    loop:
     IntOp $R2 $R2 + 1
      StrCpy $R3 $R0 1 -$R2
     StrCmp $R2 $R4 exit
     StrCmp $R3 "\" 0 loop
      StrCpy $R0 $R0 -$R2
       IfFileExists "$R0\*.*" 0 +2
       RMDir "$R0"
     IntOp $R5 $R5 + 1
     StrCmp $R5 $R1 exit
    Goto top
    exit:
    Pop $R5
    Pop $R4
    Pop $R3
    Pop $R2
    Pop $R1
    Pop $R0

    
    
    Push "$INSTDIR\output.txt" # output file (dummy)
    Push "*" # filter (dummy)
    Push "C:\tmp2\bbb\test" # folder to search in (dummy)
    Call un.MakeFileList
    StrCmp "" "$FILE_LIST" DeleteOk DeleteNotOk
    DeleteOk:
        Goto FinalExit
        
    DeleteNotOk:
        MessageBox MB_OK "Some files could not be deleted."

    FinalExit:

SectionEnd

###########################################################################
# Function for generating a list of files
# http://nsis.sourceforge.net/archive/nsisweb.php?page=922&instances=0
###########################################################################
Function un.MakeFileList
    StrCpy $FILE_LIST ""
    Exch $R0 #path
    Exch
    Exch $R1 #filter
    Exch
    Exch 2
    Exch $R2 #output file
    Exch 2
    Push $R3
    Push $R4
    Push $R5
     ClearErrors
     #MessageBox MB_OK "MakeFileList: FindFirst with [$R3] [$R4] [$R0\$R1]"
     FindFirst $R3 $R4 "$R0\$R1"
      #FileOpen $R5 $R2 w
    
     Loop:
     IfErrors Done
      #FileWrite $R5 "$R0\$R4$\r$\n"
      #MessageBox MB_OK "--- 1: [$FILE_LIST]"
      StrCpy $FILE_LIST "$R0\$R4$\r$\n$FILE_LIST"
      #MessageBox MB_OK "--- 2: [$FILE_LIST]"
      FindNext $R3 $R4
      Goto Loop
    
     Done:
      FileClose $R5
     FindClose $R3
    Pop $R5
    Pop $R4
    Pop $R3
    Pop $R2
    Pop $R1
    Pop $R0
    #MessageBox MB_OK "The following files could not be deleted: $FILE_LIST"
    DetailPrint "The following files could not be deleted: [$FILE_LIST]"
FunctionEnd


