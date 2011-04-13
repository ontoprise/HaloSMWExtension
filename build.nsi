/*
* Halowiki.nsi (c) Ontoprise 2008-2009
*
*    This script builds an installer for SMW+
*
* Author: Kai K�hn
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
!include "TextFunc.nsh"
!include "EnvVarUpdate.nsh"
!insertmacro ConfigWrite
!insertmacro GetFileName

; --- The following definitions are meant to be changed ---

!define PRODUCTPATH "SMWPLUS"
#!define PRODUCT "SMW+"
!define PRODUCT_YEAR "2011"
#!define VERSION "1.4.3" set by hudson
#!define BUILD_ID "431" set by hudson
!define REQUIRED_JAVA_VERSION 16

!ifdef EXTENSIONS_EDITION
  !define PRODUCT "SMW+"
  !define LOCALSETTINGS "LocalSettings.php.template"
  !define WIKIDB "smwplus_database.sql"
!endif
!ifdef COMMUNITY_EDITION
  !define PRODUCT "SMW+ Community Edition"
  !define LOCALSETTINGS "LocalSettings.php.community.tmpl"
  !define WIKIDB "htdocs\mediawiki\tests\tests_halo\mw16_1_db.sql"
!endif

; ----------------------------------------------------------

!define MUI_ABORTWARNING

!define MUI_HEADERIMAGE
!define MUI_HEADERIMAGE_BITMAP "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\images\header-install.bmp"

!define MUI_WELCOMEFINISHPAGE 
!define MUI_WELCOMEFINISHPAGE_BITMAP "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\images\wizard-install.bmp"
!define MUI_WELCOMEFINISHPAGE_BITMAP_NOSTRETCH
!define MUI_COMPONENTSPAGE_SMALLDESC

!define MUI_WELCOMEPAGE_TITLE "Welcome to the ${PRODUCT} ${VERSION} Setup Wizard"
!define MUI_WELCOMEPAGE_TEXT "This wizard will guide you through the installation of ${PRODUCT} ${VERSION}."

!define MUI_FINISHPAGE_RUN
!define MUI_FINISHPAGE_RUN_CHECKED
!define MUI_FINISHPAGE_RUN_TEXT "Run wiki on startup (installs Windows services)"
!define MUI_FINISHPAGE_RUN_FUNCTION "installAsWindowsService"

!define MUI_FINISHPAGE_SHOWREADME 
!define MUI_FINISHPAGE_SHOWREADME_FUNCTION showReadme
!define MUI_FINISHPAGE_SHOWREADME_TEXT "Show README"
!define MUI_FINISHPAGE_SHOWREADME_CHECKED
!define MUI_FINISHPAGE_TEXT "Installation of ${PRODUCT} ${VERSION} is completed. You are advised to study the README-SMWPLUS.txt file (readme file) \ 
located in the zip-file containing this installer. The readme file contains important information about: $\n \
* how to access the Wiki after the installation $\n \
* default access credentials for administrators $\n \
* where to find documentation for administrators and end users $\n \
* trouble shooting hints and $\n \
* know issues."
!define MUI_FINISHPAGE_LINK "Visit the ontoprise website for the latest news"
!define MUI_FINISHPAGE_LINK_LOCATION "http://wiki.ontoprise.com/"

;Start Menu Folder Page Configuration
!define MUI_STARTMENUPAGE_REGISTRY_ROOT "HKCU" 
!define MUI_STARTMENUPAGE_REGISTRY_KEY "Software\Ontoprise\${PRODUCT} ${VERSION}" 
!define MUI_STARTMENUPAGE_REGISTRY_VALUENAME "Start Menu Folder"


!ifdef NOCOMPRESS
SetCompress off
!endif

!macro GetWindowsVersion OUTPUT_VALUE
    Call GetWindowsVersion
    Pop `${OUTPUT_VALUE}`
!macroend
 
!define GetWindowsVersion '!insertmacro "GetWindowsVersion"'

;--------------------------------

Name "${PRODUCT} v${VERSION}"
Caption "${PRODUCT} ${VERSION}"
Icon "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\images\nsis1-install.ico"
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
CheckBitmap "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\images\classic-cross.bmp"
BrandingText "ontoprise GmbH ${PRODUCT_YEAR} - wiki.ontoprise.de - Build: ${BUILD_ID}"
LicenseText "GPL-License"
LicenseData "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\gpl.txt"
ComponentText "Choose type of installation"
RequestExecutionLevel admin

Var FILE_LIST
Var STARTMENU_FOLDER
Var MUI_TEMP
; Pages --------------------------------

!define MUI_ICON "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\images\smwplus_32.ico"
  
!insertmacro MUI_PAGE_WELCOME
!define MUI_LICENSEPAGE_CHECKBOX
!insertmacro MUI_DEFAULT MUI_LICENSEPAGE_TEXT_TOP "License agreement of SMW+ Community Edition and SMW+ Professional"
!insertmacro MUI_PAGE_LICENSE "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\op_license.txt"
!insertmacro MUI_DEFAULT MUI_LICENSEPAGE_TEXT_TOP "License agreement of third party components"
!insertmacro MUI_PAGE_LICENSE "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\thirdparty.txt"
!define MUI_PAGE_CUSTOMFUNCTION_SHOW initComponentsPage
!define MUI_PAGE_CUSTOMFUNCTION_LEAVE checkForAlreadyRunningProcess
!insertmacro MUI_PAGE_COMPONENTS

!define MUI_PAGE_CUSTOMFUNCTION_PRE preDirectory
!insertmacro MUI_PAGE_DIRECTORY
!insertmacro MUI_PAGE_STARTMENU Application $STARTMENU_FOLDER

Page custom showLuceneParamters checkLuceneParameters
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
  ;InstType "Update"
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
Var DEFAULTLOGO


; Helper
Var IP
Var CHOOSEDIRTEXT
Var INSTALLTYPE
Var LUCENE_AS_SERVICE

Function ".onInit"
  InitPluginsDir
  File /oname=$PLUGINSDIR\wikicustomize.ini "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\gui\wikicustomize.ini"
  File /oname=$PLUGINSDIR\lucene.ini "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\gui\lucene.ini"
  
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
    File /r /x .svn /x CVS "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\XAMPP\*"
  !endif
  ;Store installation folder
    WriteRegStr HKCU "Software\Ontoprise\${PRODUCT} ${VERSION}" "" $INSTDIR
    WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${PRODUCT} ${VERSION}" "DisplayName" "${PRODUCT} ${VERSION}"
    WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${PRODUCT} ${VERSION}" "UninstallString" "$INSTDIR\Uninstall.exe"
    WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${PRODUCT} ${VERSION}" "DisplayIcon" $INSTDIR\htdocs\mediawiki\installer\images\smwplus_32.ico
   
    
    
    CreateDirectory "$SMPROGRAMS\$STARTMENU_FOLDER"
   
    CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\Start ${PRODUCT}.lnk" "$INSTDIR\xampp_start.bat"
    CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\Stop ${PRODUCT}.lnk" "$INSTDIR\xampp_stop.bat"
    
SectionEnd

Section "-CopyInstaller"
  SectionIn 1 2 3
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  SetOutPath "$INSTDIR\htdocs\mediawiki\installer"
  CreateDirectory "$INSTDIR\htdocs\mediawiki\installer"
  !ifndef NOFILES
    File /r /x CVS /x .svn /x *.exe /x *.nsi "..\..\..\Internal__SMWPlusInstaller_and_XAMPP\workspace\SMWPlusInstaller\*"
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
            
            File /r /x .svn /x CVS /x *.zip /x *.exe /x *.cache /x .buildpath /x .project /x *.settings /x LocalSettings.php /x ACLs.php /x *.nsi /x SKOSExpander.php *
            File /oname=AdminSettings.php AdminSettingsTemplate.php
            File /oname=deployment\tools\Smwplus.zip Smwplus.zip
            File /oname=deployment\tools\Smwplussandbox.zip Smwplussandbox.zip
            File /oname=deployment\tools\patch.exe deployment\tools\patch.exe
            File /oname=deployment\tools\unzip.exe deployment\tools\unzip.exe
            CopyFiles $INSTDIR\htdocs\mediawiki\patches\patch.php $INSTDIR\htdocs\mediawiki\deployment\tools
            !ifndef COMMUNITY_EDITION
                File /oname=extensions\RichMedia\bin\xpdf\pdftotext.exe extensions\RichMedia\bin\xpdf\pdftotext.exe
                File /oname=extensions\RichMedia\bin\AbiWord\bin\AbiWord.exe extensions\RichMedia\bin\AbiWord\bin\AbiWord.exe
            !endif
      !endif  
  ;Create uninstaller (only when newly installed)
  ${If} $INSTALLTYPE == 1 
      WriteUninstaller "$INSTDIR\Uninstall.exe"
      CreateDirectory "$SMPROGRAMS\$STARTMENU_FOLDER"
      CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\Uninstall.lnk" "$INSTDIR\Uninstall.exe"
  ${EndIf}
    
  ;configure:
      ${If} $INSTALLTYPE == 0 
        CALL changeConfigForSMWPlusUpdate
      ${EndIf}
      ${If} $INSTALLTYPE == 1
        CALL changeConfigForFullXAMPP
      ${EndIf}
  
  
SectionEnd

Section "SMW+ Setup" smwplussetup
  SectionIn 1 RO
  DetailPrint "Configure SMW+"
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}

  SetOutPath "$INSTDIR\htdocs\mediawiki"
  StrCpy $PHP "$INSTDIR\php\php.exe"
  StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"

  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\maintenance\update.php"'
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\extensions\SemanticMediaWiki\maintenance\SMW_setup.php"'
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\extensions\SMWHalo\maintenance\SMW_setup.php"'
  nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\extensions\EnhancedRetrieval\maintenance\setup.php"'

  SetOutPath "$MEDIAWIKIDIR\deployment\tools"

  ; Set PHP path for deployment framework
  DetailPrint "Set PHP path for deployment framework"
  ${ConfigWrite} "$MEDIAWIKIDIR\deployment\tools\smwadmin.bat" "SET PHP=" '"$INSTDIR\php\php.exe"' $R0

  DetailPrint "Install bundles into wiki"
  nsExec::ExecToLog '"$MEDIAWIKIDIR\deployment\tools\smwadmin.bat" -i Smwplus.zip'
  nsExec::ExecToLog '"$MEDIAWIKIDIR\deployment\tools\smwadmin.bat" -i Smwplussandbox.zip'

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
  ;nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" importSemForms=1 ls=LocalSettings.php'
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
  ;nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" importTreeview=1 ls=LocalSettings.php'
SectionEnd

Section "WYSIWYG" wysiwyg
  SectionIn 1 RO
  DetailPrint "Configure WYSIWYG extension"
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  SetOutPath "$INSTDIR\htdocs\mediawiki"
  StrCpy $PHP "$INSTDIR\php\php.exe"
  StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"
  
  ; change config file
  ;nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" importWYSIWYG=1 ls=LocalSettings.php'
SectionEnd

Section "Lucene search" lucene
    SectionIn 1 RO
    SectionGetFlags ${xampp} $0
    IntOp $0 $0 & ${SF_SELECTED}
    
    CreateDirectory "$INSTDIR\lucene"
    CreateDirectory "$INSTDIR\lucene\lib"
    CreateDirectory "$INSTDIR\lucene\service"
    #!ifndef NOFILES
        SetOutPath "$INSTDIR\lucene\lib"
        File /r "..\..\..\Product__Lucene_server\workspace\lib\*.jar"
        
        SetOutPath "$INSTDIR\lucene\service"
        File /r /x CVS /x .svn "..\..\..\Product__Lucene_server\workspace\service\*"
        
        SetOutPath "$INSTDIR\lucene\scripts"
        File /r /x CVS /x .svn "..\..\..\Product__Lucene_server\workspace\scripts\*"
        
        SetOutPath "$INSTDIR\lucene\template"
        File /r /x CVS /x .svn "..\..\..\Product__Lucene_server\workspace\template\*"
        
        SetOutPath "$INSTDIR\lucene"
        File "..\..\..\Product__Lucene_server\workspace\LuceneSearch.jar"
        File "..\..\..\Product__Lucene_server\workspace\*.bat"
        File "..\..\..\Product__Lucene_server\workspace\linkd.exe"
        File "..\..\..\Product__Lucene_server\workspace\*.txt"
        File "..\..\..\Product__Lucene_server\workspace\*.properties"
        File "..\..\..\Product__Lucene_server\workspace\smwplus_db.xml"
        File "..\..\..\Product__Lucene_server\workspace\lucene-wiki.exe"
        File "..\..\..\Product__Lucene_server\workspace\lucene-wiki.l4j.ini"
    #!endif
        
        SetOutPath "$INSTDIR\lucene"
        StrCpy $PHP "$INSTDIR\php\php.exe"
        StrCpy $MEDIAWIKIDIR "$INSTDIR\htdocs\mediawiki"
        
        DetailPrint "Configure Lucene"
        ; dump db 
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in="template\dump.bat.template" out=dump.bat noslash=true php-path="$PHP" wiki-path="$MEDIAWIKIDIR" lucene-path="$INSTDIR\lucene"'
        nsExec::ExecToLog 'dump.bat'
               
        ; adapt global.conf.template file
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in="template/global.conf.template" out=global.conf wiki-db=semwiki_en ip=$IP lang=$WIKILANG'
        ; adapt lsearch.conf.template file
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in="template/lsearch.conf.template" out=lsearch.conf project-path="$INSTDIR\lucene" wiki-path="$MEDIAWIKIDIR" project-path-url="$INSTDIR\lucene" wiki-path-url="$MEDIAWIKIDIR"'
         ; adapt start.bat.template file
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in="template/start.bat.template" out=start.bat lucene-path-url="$INSTDIR\lucene" lucene-path="$INSTDIR\lucene" ip=$IP'
         ; adapt lucene-wiki.l4j.ini.template
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in="template/lucene-wiki.l4j.ini.template" out=lucene-wiki.l4j.ini lucene-path-url="$INSTDIR\lucene" lucene-path="$INSTDIR\lucene" ip=$IP'
         ; adapt schtask_desc.xml.template
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in="template/schtask_desc.xml.template" out=schtask_desc.xml noslash=true lucene-path-url="$INSTDIR\lucene" lucene-path="$INSTDIR\lucene" ip=$IP'
        ;adapt startUpdater template file
        nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in="template/startUpdater.bat.template" out=startUpdater.bat currentdate="__DATE__"'
        
        ; Build OAI repository
        nsExec::ExecToLog 'initUpdates.bat "$INSTDIR\mysql" semwiki_en root m8nix'
        
        ; Build Lucene index
        DetailPrint "Build Lucene index"
        nsExec::ExecToLog 'buildall.bat smwplus_db.xml semwiki_en'
        
         ; Register scheduled task for lucene update
         ${GetWindowsVersion} $R0
         ${If} $R0 == "7"
         ${OrIf} $R0 == "Vista"
         ${OrIf} $R0 == "2008"
            DetailPrint "Add LuceneIndexUpdate as planned task for Windows 7/Vista/2008 Server"
            nsExec::ExecToLog 'schtasks /create /tn "LuceneIndexUpdate" /XML "$INSTDIR\lucene\schtask_desc.xml"'
         ${EndIf}
         
         ${If} $R0 == "XP"
         ${OrIf} $R0 == "2003"
            DetailPrint "Add LuceneIndexUpdate as planned task for Windows XP/2003 Server"
            nsExec::ExecToLog 'schtasks /create /tn "LuceneIndexUpdate" /ru "SYSTEM" /tr "\"$INSTDIR\lucene\rebuildIndex.bat\"" /sc daily'
         ${EndIf}
        
        ;change LocalSettings
        ;SetOutPath "$MEDIAWIKIDIR"
        ;nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeLS.php" importUS=1 ls=LocalSettings.php'
        
       
        ${If} $0 == 1
            SetOutPath "$INSTDIR\lucene"
            CreateDirectory "$SMPROGRAMS\$STARTMENU_FOLDER"
            CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\${PRODUCT} ${VERSION} Start Lucene.lnk" "$INSTDIR\lucene\lucene-wiki.exe"
            #CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\${PRODUCT} ${VERSION} Start Lucene Updater.lnk" "$INSTDIR\lucene\startUpdater.bat"
        ${EndIf}
        DetailPrint "Starting Lucene"
        SetOutPath "$INSTDIR\lucene"
        Exec "$INSTDIR\lucene\lucene-wiki.exe"       
SectionEnd

Section "Solr" solr
    SectionIn 1 RO
    SectionGetFlags ${xampp} $0
    IntOp $0 $0 & ${SF_SELECTED}

    SetOutPath "$INSTDIR\solr\wiki"
    StrCpy $PHP "$INSTDIR\php\php.exe"

    DetailPrint "Run Solr"
    ; run the solr server
    Exec '"$INSTDIR\solr\wiki\startSolr.bat"'

    ${If} $0 == 1
        SetOutPath "$INSTDIR\solr\wiki"
        CreateDirectory "$SMPROGRAMS\$STARTMENU_FOLDER"
        CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\${PRODUCT} ${VERSION} Start Solr.lnk" "$INSTDIR\solr\wiki\startSolr.bat"
        CreateShortCut "$SMPROGRAMS\$STARTMENU_FOLDER\${PRODUCT} ${VERSION} Start Solr Create Index.lnk" '"$INSTDIR\solr\wiki\createIndex.bat"'
    ${EndIf}

    nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in=createIndex.bat out=createIndex.bat php-exe="$PHP"'
    nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in="\"$INSTDIR\htdocs\mediawiki\extensions\EnhancedRetrieval\SOLR\smwdb-data-config.xml\"" out="\"$INSTDIR\solr\wiki\solr\conf\smwdb-data-config.xml\"" wgDBname=semwiki_en wgDBserver=localhost wgDBport=3306 wgDBuser=root wgDBpassword=m8nix'
    nsExec::ExecToLog '"$PHP" "$MEDIAWIKIDIR\installer\changeVariable.php" in="\"$INSTDIR\htdocs\mediawiki\LocalSettings.php\"" out="\"$INSTDIR\htdocs\mediawiki\LocalSettings.php.solr\"" solr_ip="$IP"'
    CopyFiles "$INSTDIR\htdocs\mediawiki\LocalSettings.php.solr" "$INSTDIR\htdocs\mediawiki\LocalSettings.php"
    CopyFiles "$INSTDIR\htdocs\mediawiki\extensions\EnhancedRetrieval\SOLR\schema.xml" "$INSTDIR\solr\wiki\solr\conf\schema.xml"
    CopyFiles "$INSTDIR\htdocs\mediawiki\extensions\EnhancedRetrieval\SOLR\solrconfig.xml" "$INSTDIR\solr\wiki\solr\conf\solrconfig.xml"

    CreateDirectory "$INSTDIR\solr\wiki\logs"

    ; create index
    nsExec::ExecToLog '"$PHP" "$INSTDIR\solr\wiki\createIndex.php"'

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
LangString LUCENE_PAGE_TITLE ${LANG_ENGLISH} "Lucene settings"
LangString LUCENE_PAGE_SUBTITLE ${LANG_ENGLISH} "Set your IP if possible"

LangString SELECT_XAMPP_DIR ${LANG_ENGLISH} "Select an empty directory where to install XAMPP and the wiki."
LangString SELECT_NEWUPDATE_DIR ${LANG_ENGLISH} "Select an existing installation to update."
LangString STARTED_SERVERS ${LANG_ENGLISH} "There are already running instances of Apache, MySQL and/or memcached. You MUST stop them before continuing."
LangString COULD_NOT_START_SERVERS ${LANG_ENGLISH} "Apache and MySQL could not be started for some reason. Installation may not be complete!"
LangString FIREWALL_COMPLAIN_INFO ${LANG_ENGLISH} "Windows firewall may block the apache and mySQL processes. $\n If this is the case with your installation, then unblock both processes in the pop-up windows $\n and click on 'OK' to finish the installation process."
LangString DIRECTORY_HINT ${LANG_ENGLISH} "Please note that SMW+ must not be installed in directories containing parantheses, like 'Programs (x86)'"

;Assign language strings to sections
!insertmacro MUI_FUNCTION_DESCRIPTION_BEGIN
    !insertmacro MUI_DESCRIPTION_TEXT ${xampp} $(DESC_xampp)
    !insertmacro MUI_DESCRIPTION_TEXT ${smwplus} $(DESC_smwplus)
    #!insertmacro MUI_DESCRIPTION_TEXT ${ohelp} $(DESC_ohelp)
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
  
  ; Hint for directory. Make sure not to install in 32-bit compatibility dir from Windows 7
  ; because it contains parantheses.
  ${GetWindowsVersion} $R0
  ${If} $R0 == "7"
  ${OrIf} $R0 == "Vista"
  ${OrIf} $R0 == "2008"
    MessageBox MB_OK $(DIRECTORY_HINT) IDOK 0 IDCANCEL 0
  ${EndIf}
FunctionEnd

Function checkForAlreadyRunningProcess
  SectionGetFlags ${xampp} $0
  IntOp $0 $0 & ${SF_SELECTED}
  ${If} $0 == 1
    CALL checkForApacheAndMySQLAndMemcached
  ${EndIf}
FunctionEnd

Function checkForApacheAndMySQLAndMemcached
 checkagain:
   FindProcDLL::FindProc "httpd.exe"
   IntOp $0 0 + $R0
   FindProcDLL::FindProc "mysqld.exe"
   IntOp $1 0 + $R0
   FindProcDLL::FindProc "mysqld-nt.exe"
   IntOp $1 $1 + $R0
   FindProcDLL::FindProc "memcached.exe"
   IntOp $2 0 + $R0
   FindProcDLL::FindProc "lucene-wiki.exe"
   IntOp $3 0 + $R0
   FindProcDLL::FindProc "startSolr.bat"
   IntOp $4 0 + $R0
   ${If} $0 == 1
   ${OrIf} $1 == 1
   ${OrIf} $2 == 1
   ${OrIf} $3 == 1
   ${OrIf} $4 == 1
    MessageBox MB_ICONEXCLAMATION|MB_OKCANCEL $(STARTED_SERVERS) IDOK 0 IDCANCEL skipCheck
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
   FindProcDLL::FindProc "httpd.exe"
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

Function showLuceneParamters
    SectionGetFlags ${lucene} $0
    IntOp $0 $0 & ${SF_SELECTED}
    
    ${If} $0 == 1 
        !insertmacro MUI_HEADER_TEXT $(LUCENE_PAGE_TITLE) $(LUCENE_PAGE_SUBTITLE)
          
          ; get IPs
          ip::get_ip
          Pop $0
         
          Loop:
          Push $0
          Call GetNextIp
          Call CheckIp
          Pop $2 ; Type of current IP-address
          Pop $1 ; Current IP-address
          Pop $0 ; Remaining addresses
          StrCmp $2 '1' '' NoLoopBackIp
            Goto Continue
          NoLoopBackIp:
          StrCmp $2 '2' '' NoAPA
            Goto Continue
          NoAPA:
          StrCmp $2 '3' '' NoLanIp
            Goto ExitLoop
          NoLanIp:
            Goto ExitLoop
          Continue:
          StrCmp $0 '' ExitLoop Loop
          ExitLoop:  
          WriteINIStr "$PLUGINSDIR\lucene.ini" "Field 2" "state" $1
        
          Push $R0
          InstallOptions::dialog $PLUGINSDIR\lucene.ini
          Pop $R0

    ${Else}
        Abort
    ${EndIf}
FunctionEnd

Function checkLuceneParameters
    ReadINIStr $IP "$PLUGINSDIR\lucene.ini" "Field 2" "state"
    ${If} $IP == "localhost"
    ${OrIf} $IP == "127.0.0.1"
        MessageBox MB_OK|MB_ICONEXCLAMATION "Not allowed. Please enter a real IP or leave it blank."
        Abort    
    ${EndIf}
    ${If} $IP == ""
        StrCpy $IP "true"
    ${EndIf}
FunctionEnd




Function changeConfigForFullXAMPP
    ; setup XAMPP (setup_xampp.bat and install script slightly modified)
    DetailPrint "Update XAMPP"
    SetOutPath "$INSTDIR"
    nsExec::ExecToLog '"$INSTDIR\setup_xampp.bat"'
    SetOutPath "$INSTDIR\htdocs\mediawiki"
    ; Apache logs directory doesn't exist, so create it
    CreateDirectory "$INSTDIR\apache\logs"
    
    ; setup halowiki (change LocalSettings.php)
    ; Use LocalSettings.php from input and change the following variables:
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
        smwgSemanticAC=false smwgGardeningBotDelay=100 wgScriptPath="/mediawiki" ls=${LOCALSETTINGS}'
    
    ; Activate php_gd2.dll for thumbnails and php_openssl.dll for SSL 
    DetailPrint "Update php.ini"
    nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\installer\activateExtension.php" ini="$INSTDIR\php\php.ini" on=php_gd2,php_openssl,php_curl'
    
    
    ; Make halowiki directory accessible by Apache  
    DetailPrint "Update httpd.conf"  
    nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\installer\changeHttpd.php" httpd="$INSTDIR\apache\conf\httpd.conf" wiki-path=mediawiki fs-path="$INSTDIR\htdocs\mediawiki" memcache=true'
        
    DetailPrint "Config customizations"
    CALL configCustomizationsForNew
FunctionEnd

; deprecated
Function changeConfigForSMWPlusUpdate
    
    CALL checkForApacheAndMySQLAndMemcached
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
            StrCpy $DEFAULTLOGO "skins/ontoskin/images/wiki.jpg"
            ${Break}
        ${Case} 'ontoskin2 (blue)'
            StrCpy $WIKISKIN "ontoskin2"
            StrCpy $DEFAULTLOGO "skins/ontoskin2/images/wiki.jpg"
            ${Break}
        ${Case} 'ontoskin3'
            StrCpy $WIKISKIN "ontoskin3"
            StrCpy $DEFAULTLOGO "skins/ontoskin3/img/wiki.jpg"
            ${Break}   
        ${Default}
            StrCpy $WIKISKIN "ontoskin3"
            StrCpy $DEFAULTLOGO "skins/ontoskin3/img/wiki.jpg"
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
        StrCpy $WIKILOGO $DEFAULTLOGO
    updateLocalSettings:    
        ${GetFileName} $WIKILOGO $R0
        DetailPrint "Configuring LocalSettings.php"
        DetailPrint "Instdir: $INSTDIR\php\php.exe"
        DetailPrint "Wikiname: $WIKINAME"
        DetailPrint "Wikilang: $WIKILANG"
        DetailPrint "Wikilogo: $WIKILOGO"
        DetailPrint "Wikiskin: $WIKISKIN"
   
        SetOutPath "$INSTDIR\htdocs\mediawiki"
        nsExec::ExecToLog ' "$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\installer\changeLS.php" \
        wgSitename="$WIKINAME" wgDBname="semwiki_$WIKILANG" wgLogo=$$wgScriptPath/url:("$WIKILOGO") wgLanguageCode=$WIKILANG wgDefaultSkin="$WIKISKIN" \
        ls=LocalSettings.php'
    
        DetailPrint "Starting XAMPP"
        CALL installMemcached
        SetOutPath "$INSTDIR"
        Exec "$INSTDIR\xampp_start.bat"
        CALL waitForApacheAndMySQL
        MessageBox MB_OK|MB_ICONINFORMATION $(FIREWALL_COMPLAIN_INFO)
    
        DetailPrint "Import wiki database"
        nsExec::ExecToLog ' "$INSTDIR\import_smwplus_db.bat" "$INSTDIR" root m8nix semwiki_en "$INSTDIR\${WIKIDB}" '
        
        DetailPrint "Set php.exe in PATH Variable"
        ${EnvVarUpdate} $0 "PATH" "A" "HKLM" "$INSTDIR\php"
        ${EnvVarUpdate} $0 "PATH" "A" "HKLM" "$INSTDIR\mysql\bin" 
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
    
    /*DetailPrint "Updating helppages"
        DetailPrint "Starting XAMPP"
        SetOutPath "$INSTDIR"
        #Exec "$INSTDIR\xampp_start.bat"
        #CALL waitForApacheAndMySQL
        MessageBox MB_OK $(FIREWALL_COMPLAIN_INFO)
        SetOutPath "$INSTDIR\htdocs\mediawiki"
        nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\extensions\SMWHaloHelp\maintenance\setup.php" --deinstall'
        nsExec::ExecToLog '"$INSTDIR\php\php.exe" "$INSTDIR\htdocs\mediawiki\extensions\SMWHaloHelp\maintenance\setup.php" --install'
     */
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


Function FinishPageShow
  SectionGetFlags ${lucene} $0
  IntOp $0 $0 & ${SF_SELECTED}
  
  ${If} $0 == 0
 
    GetDlgItem $R0 $mui.FinishPage 1203
    ShowWindow $R0 ${SW_HIDE}
  ${EndIf}
  
  ;write log
   StrCpy $0 "$INSTDIR\install.log"
   Push $0
   Call DumpLog
   
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

Function installMemcached
    DetailPrint "Install and start memcached"
    SetOutPath "$INSTDIR"
    Exec "$INSTDIR\installMemcachedAsService.bat"
FunctionEnd

Function un.uninstallMemcached
    DetailPrint "Stop and uninstall memcached"
    SetOutPath "$INSTDIR"
    Exec "$INSTDIR\uninstallMemcachedAsService.bat"
    SetOutPath "c:\temp\halo" #dummy to make installation dir removable
FunctionEnd

Function installAsWindowsService
	SetOutPath "$INSTDIR"
    DetailPrint "Install Apache and MySQL as service."
    Exec "$INSTDIR\installApacheMySQLAsService.bat"
       
    # Do not install Lucene as service (does not work) but register it in Autostart folder
    DetailPrint "Start Lucene automatically via AutoStart folder."
    CreateShortCut "$SMSTARTUP\LuceneForSMWPlus.lnk" "$INSTDIR\lucene\lucene-wiki.exe"
    # Do not install Solr as service (does not work) but register it in Autostart folder
    DetailPrint "Start Solr automatically via AutoStart folder."
    CreateShortCut "$SMSTARTUP\SolrForSMWPlus.lnk" "$INSTDIR\solr\wiki\startSolr.bat"

FunctionEnd


; Uninstaller
Function un.uninstallAsWindowsService
	SetOutPath "$INSTDIR"
    DetailPrint "Stop and uninstall Apache and MySQL as service."
    Exec "$INSTDIR\uninstallApacheMySQLAsService.bat"
    
    DetailPrint "Delete autostart entry for Lucene"
    Delete "$SMSTARTUP\LuceneForSMWPlus.lnk"

    DetailPrint "Delete autostart entry for solr"
    Delete "$SMSTARTUP\SolrForSMWPlus.lnk"
    
    SetOutPath "c:\temp\halo" #dummy to make installation dir removable
FunctionEnd

Function un.checkForApacheAndMySQLAndMemcached
 checkagain:
   FindProcDLL::FindProc "httpd.exe"
   IntOp $0 0 + $R0
   FindProcDLL::FindProc "mysqld.exe"
   IntOp $1 0 + $R0
   FindProcDLL::FindProc "mysqld-nt.exe"
   IntOp $1 $1 + $R0
   FindProcDLL::FindProc "memcached.exe"
   IntOp $2 0 + $R0
   FindProcDLL::FindProc "lucene-wiki.exe"
   IntOp $3 0 + $R0
   FindProcDLL::FindProc "startSolr.bat"
   IntOp $4 0 + $R0
   ${If} $0 == 1
   ${OrIf} $1 == 1
   ${OrIf} $2 == 1
   ${OrIf} $3 == 1
   ${OrIf} $4 == 1
    MessageBox MB_ICONEXCLAMATION|MB_OKCANCEL $(STARTED_SERVERS) IDOK 0 IDCANCEL skipCheck
    goto checkagain
   ${EndIf}
   goto out
 skipcheck:
    
 out:
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
	# MessageBox MB_OK "User said OK!"
    
    ; Un-install services (if installed at all)
    Call un.uninstallAsWindowsService
    Call un.uninstallMemcached
    
    ; Unregister scheduled task for lucene update
    nsExec::ExecToLog 'schtasks /delete /TN "LuceneIndexUpdate" /F'
    
    Call un.checkForApacheAndMySQLAndMemcached
    
    # Delete from PATH variable
    ${un.EnvVarUpdate} $0 "PATH" "R" "HKLM" "$INSTDIR\php"
    ${un.EnvVarUpdate} $0 "PATH" "R" "HKLM" "$INSTDIR\mysql\bin"      
    
    # Delete all start menu entries
    Delete "$SMPROGRAMS\$MUI_TEMP\Uninstall.lnk"
    Delete "$SMPROGRAMS\$MUI_TEMP\Start ${PRODUCT}.lnk"
    Delete "$SMPROGRAMS\$MUI_TEMP\Stop ${PRODUCT}.lnk"
    Delete "$SMPROGRAMS\$MUI_TEMP\${PRODUCT} ${VERSION} Start Lucene.lnk" 
    #Delete "$SMPROGRAMS\$MUI_TEMP\${PRODUCT} ${VERSION} Start Lucene Updater.lnk"
    Delete "$SMPROGRAMS\$MUI_TEMP\${PRODUCT} ${VERSION} Start Solr.lnk"
    Delete "$SMPROGRAMS\$MUI_TEMP\${PRODUCT} ${VERSION} Start Solr Create Index.lnk"
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

    nsExec::ExecToLog '"$INSTDIR\xampp_stop.bat"'

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
    RMDir /r "$INSTDIR\contrib"
    RMDir /r "$INSTDIR\tomcat"
    RMDir /r "$INSTDIR\memcached"
    RMDir /r "$INSTDIR\solr"
    
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

; dumps the install log
!define LVM_GETITEMCOUNT 0x1004
!define LVM_GETITEMTEXT 0x102D
Function DumpLog
  Exch $5
  Push $0
  Push $1
  Push $2
  Push $3
  Push $4
  Push $6
 
  FindWindow $0 "#32770" "" $HWNDPARENT
  GetDlgItem $0 $0 1016
  StrCmp $0 0 exit
  FileOpen $5 $5 "w"
  StrCmp $5 "" exit
    SendMessage $0 ${LVM_GETITEMCOUNT} 0 0 $6
    System::Alloc ${NSIS_MAX_STRLEN}
    Pop $3
    StrCpy $2 0
    System::Call "*(i, i, i, i, i, i, i, i, i) i \
      (0, 0, 0, 0, 0, r3, ${NSIS_MAX_STRLEN}) .r1"
    loop: StrCmp $2 $6 done
      System::Call "User32::SendMessageA(i, i, i, i) i \
        ($0, ${LVM_GETITEMTEXT}, $2, r1)"
      System::Call "*$3(&t${NSIS_MAX_STRLEN} .r4)"
      FileWrite $5 "$4$\r$\n"
      IntOp $2 $2 + 1
      Goto loop
    done:
      FileClose $5
      System::Free $1
      System::Free $3
  exit:
    Pop $6
    Pop $4
    Pop $3
    Pop $2
    Pop $1
    Pop $0
    Exch $5
FunctionEnd

Function showReadme
    Exec '"notepad.exe" "$INSTDIR\README-SMWPLUS.txt"'
FunctionEnd

; Function GetNextIp
; input: head of stack
; format: 'ip1;ip2;ip3;ip4;'
; output: 'ip1' head of stack
;         'ip2;ip3;ip4;' second entry of stack
 
Function GetNextIp
  Exch $0
  Push $1
  Push $2
  Strcpy $2 0             ; Counter
  Loop:
    IntOp $2 $2 + 1
    StrCpy $1 $0 1 $2
    StrCmp $1 '' ExitLoop
    StrCmp $1 ';' '' Loop
    StrCpy $1 $0 $2       ; IP-address
    IntOp $2 $2 + 1
    StrCpy $0 $0 '' $2    ; Remaining string
  ExitLoop:
  Pop $2
  Push $0
  Exch 2
  Pop $0
  Exch $1
FunctionEnd
 
; Function CheckIP
; input: IP-address on stack
; output: additional entry on stack
;         1 - LoopBack IP (localhost, indicates no connection to a LAN or to the internet).
;         2 - Automatic Private IP Address (no DHCP server).
;         3 - Network IP.
;         4 - Internet IP.
; Eg:
; Push '192.168.0.100'
; Call CheckIP
; Pop $0 ; Contains '3'
; Pop $1 ; Contains '192.168.0.100'
 
Function CheckIP
  Exch $0
  Push $1
 
  ; Check 127.x.x.x
  Push '127.0.0.0'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 2 '' Range1     ; IP cannot be in range of LoopBack addresses
  Push '127.255.255.255'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 1 LoopBack      ; We found a LoopBack IP
 
  ; Check 10.x.x.x
  Range1:
  Push '10.0.0.0'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 2 '' Range2     ; IP cannot be in range 1
  Push '10.255.255.255'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 1 LanIp         ; We found a LanIp
 
  ; Check 172.16.x.x to 172.31.x.x
  Range2:
  Push '172.16.0.0'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 2 '' Range3     ; IP cannot be in range 2
  Push '172.31.255.255'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 1 LanIp         ; We found a LanIp
 
  ; Check 192.168.x.x
  Range3:
  Push '192.168.0.0'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 2 '' Range4     ; IP cannot be in range 3
  Push '192.168.255.255'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 1 LanIp         ; We found a LanIp
 
  ; Check 169.254.x.x
  Range4:
  Push '169.254.0.0'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 2 '' InternetIp ; It should be an internet IP
  Push '169.254.255.255'
  Push $0
  Call VersionCheck
  Pop $1
  StrCmp $1 1 APA           ; We found an Automatic Private IP Address
 
  Goto InternetIp           ; Remaining addresses are internet IPs
 
  LoopBack:
  StrCpy $1 1
  Goto Exit
 
  APA:
  StrCpy $1 2
  Goto Exit
 
  LanIp:
  StrCpy $1 3
  Goto Exit
 
  InternetIp:
  StrCpy $1 4
 
  Exit:
  Exch $1
  Exch 1
  Exch $0
  Exch 1
FunctionEnd
 
; Function VersionCheck
; input: 'v1', 'v2' on stack
; output 1 - if number 1 is newer
;        2 - if number 2 is newer
;        0 - if it is the same verion
; Eg:
; Push '3.5.1.4'
; Push '3.5'
; Call VersionCheck
; Pop $0 ; now contains 1
 
Function VersionCheck
  Exch $0 ;second versionnumber
  Exch
  Exch $1 ;first versionnumber
  Push $R0 ;counter for $0
  Push $R1 ;counter for $1
  Push $3 ;temp char
  Push $4 ;temp string for $0
  Push $5 ;temp string for $1
  StrCpy $R0 "-1"
  StrCpy $R1 "-1"
  Start:
  StrCpy $4 ""
  DotLoop0:
  IntOp $R0 $R0 + 1
  StrCpy $3 $0 1 $R0
  StrCmp $3 "" DotFound0
  StrCmp $3 "." DotFound0
  StrCpy $4 $4$3
  Goto DotLoop0
  DotFound0:
  StrCpy $5 ""
  DotLoop1:
  IntOp $R1 $R1 + 1
  StrCpy $3 $1 1 $R1
  StrCmp $3 "" DotFound1
  StrCmp $3 "." DotFound1
  StrCpy $5 $5$3
  Goto DotLoop1
  DotFound1:
  Strcmp $4 "" 0 Not4
    StrCmp $5 "" Equal
    Goto Ver2Less
  Not4:
  StrCmp $5 "" Ver2More
  IntCmp $4 $5 Start Ver2Less Ver2More
  Equal:
  StrCpy $0 "0"
  Goto Finish
  Ver2Less:
  StrCpy $0 "1"
  Goto Finish
  Ver2More:
  StrCpy $0 "2"
  Finish:
  Pop $5
  Pop $4
  Pop $3
  Pop $R1
  Pop $R0
  Pop $1
  Exch $0
FunctionEnd

; Returns windows version
; Usage: ${GetWindowsVersion} $R0
;
; $R0 contains: 95, 98, ME, NT x.x, 2000, XP, 2003, Vista, 7 or '' (for unknown)
Function GetWindowsVersion
 
  Push $R0
  Push $R1
 
  ClearErrors
 
  ReadRegStr $R0 HKLM \
  "SOFTWARE\Microsoft\Windows NT\CurrentVersion" CurrentVersion
 
  IfErrors 0 lbl_winnt
 
  ; we are not NT
  ReadRegStr $R0 HKLM \
  "SOFTWARE\Microsoft\Windows\CurrentVersion" VersionNumber
 
  StrCpy $R1 $R0 1
  StrCmp $R1 '4' 0 lbl_error
 
  StrCpy $R1 $R0 3
 
  StrCmp $R1 '4.0' lbl_win32_95
  StrCmp $R1 '4.9' lbl_win32_ME lbl_win32_98
 
  lbl_win32_95:
    StrCpy $R0 '95'
  Goto lbl_done
 
  lbl_win32_98:
    StrCpy $R0 '98'
  Goto lbl_done
 
  lbl_win32_ME:
    StrCpy $R0 'ME'
  Goto lbl_done
 
  lbl_winnt:
 
  StrCpy $R1 $R0 1
 
  StrCmp $R1 '3' lbl_winnt_x
  StrCmp $R1 '4' lbl_winnt_x
 
  StrCpy $R1 $R0 3
 
  StrCmp $R1 '5.0' lbl_winnt_2000
  StrCmp $R1 '5.1' lbl_winnt_XP
  StrCmp $R1 '5.2' lbl_winnt_2003
  StrCmp $R1 '6.0' lbl_winnt_vista
  StrCmp $R1 '6.1' lbl_winnt_7 lbl_error
 
  lbl_winnt_x:
    StrCpy $R0 "NT $R0" 6
  Goto lbl_done
 
  lbl_winnt_2000:
    Strcpy $R0 '2000'
  Goto lbl_done
 
  lbl_winnt_XP:
    Strcpy $R0 'XP'
  Goto lbl_done
 
  lbl_winnt_2003:
    Strcpy $R0 '2003'
  Goto lbl_done
 
  lbl_winnt_vista:
    Strcpy $R0 'Vista'
  Goto lbl_done
 
  lbl_winnt_7:
    Strcpy $R0 '7'
  Goto lbl_done
 
  lbl_error:
    Strcpy $R0 ''
  lbl_done:
 
  Pop $R1
  Exch $R0
 
FunctionEnd
 
